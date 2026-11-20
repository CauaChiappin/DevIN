<?php
declare(strict_types=1);

/**
 * Responsável único pelo atendimento; toda a equipe ADM pode ler a fila.
 * Migração 008 aditiva. Até a aplicação dela, a operação antiga permanece ativa.
 */
final class SupportRepresentative
{
    public static function ready(mysqli $db): bool
    {
        try {
            $db->query('SELECT assigned_admin_id,assigned_at FROM support_tickets LIMIT 0');
            $db->query('SELECT id FROM support_assignment_events LIMIT 0');
            return true;
        } catch (mysqli_sql_exception $exception) {
            if (in_array((int) $exception->getCode(), [1054, 1146], true)) return false;
            throw $exception;
        }
    }

    private static function log(
        mysqli $db, int $ticketId, ?int $oldId, ?int $newId, int $adminId, string $event
    ): void {
        $stmt = $db->prepare('INSERT INTO support_assignment_events
            (ticket_id,old_admin_id,new_admin_id,changed_by,event_type)
            VALUES (?,?,?,?,?)');
        $stmt->bind_param('iiiis', $ticketId, $oldId, $newId, $adminId, $event);
        $stmt->execute();
        $stmt->close();
    }

    public static function listAdmin(mysqli $db, ?int $adminId = null): array
    {
        $columns = self::ready($db)
            ? 'assigned_admin_id,assigned_at'
            : 'NULL AS assigned_admin_id,NULL AS assigned_at';
        $rows = $db->query('SELECT id,requester_name,requester_email,requester_type,
            category,description,status,attachment_name,' . $columns . ',created_at,updated_at
            FROM support_tickets ORDER BY FIELD(status,"aberto","respondido","fechado"),
            updated_at DESC LIMIT 150')->fetch_all(MYSQLI_ASSOC);
        foreach ($rows as &$row) {
            $row['assigned_to_me'] = $adminId !== null
                && (int) ($row['assigned_admin_id'] ?? 0) === $adminId;
        }
        unset($row);
        return $rows;
    }

    public static function myTickets(mysqli $db, int $adminId): array
    {
        if ($adminId < 1 || !self::ready($db)) return [];
        $stmt = $db->prepare('SELECT id,requester_name,requester_email,requester_type,
            category,description,status,attachment_name,assigned_admin_id,assigned_at,
            created_at,updated_at FROM support_tickets WHERE assigned_admin_id=?
            ORDER BY FIELD(status,"aberto","respondido","fechado"),updated_at DESC LIMIT 150');
        $stmt->bind_param('i', $adminId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        foreach ($rows as &$row) $row['assigned_to_me'] = true;
        unset($row);
        return $rows;
    }

    /** Chamada após SELECT FOR UPDATE dentro da transação de reply ou close. */
    public static function verifyOwnerOrClaim(mysqli $db, array $ticket, int $adminId): void
    {
        if (!self::ready($db)) return;
        if ($adminId < 1 || !isset($ticket['id'])) throw new DomainException('ADM inválido.');
        $owner = $ticket['assigned_admin_id'] === null ? null : (int) $ticket['assigned_admin_id'];
        if ($owner !== null && $owner !== $adminId) {
            throw new DomainException('Outro ADM representa este chamado. Assuma o atendimento antes de responder ou encerrar.');
        }
        if ($owner === null) {
            $id = (int) $ticket['id'];
            $stmt = $db->prepare('UPDATE support_tickets
                SET assigned_admin_id=?, assigned_at=NOW() WHERE id=?');
            $stmt->bind_param('ii', $adminId, $id);
            $stmt->execute();
            $stmt->close();
            self::log($db, $id, null, $adminId, $adminId, 'claimed');
        }
    }

    public static function assign(mysqli $db, int $ticketId, int $adminId, string $action): array
    {
        if ($ticketId < 1 || $adminId < 1
            || !in_array($action, ['claim', 'takeover', 'release'], true)) {
            throw new InvalidArgumentException('Operação de representação inválida.');
        }
        if (!self::ready($db)) {
            throw new DomainException('Execute a migração 008 para habilitar representantes do suporte.');
        }
        $stmt = $db->prepare('SELECT id_administrador FROM administrador
            WHERE id_administrador=? LIMIT 1');
        $stmt->bind_param('i', $adminId);
        $stmt->execute();
        $verified = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$verified) throw new DomainException('O administrador não existe.');

        $db->begin_transaction();
        try {
            $stmt = $db->prepare('SELECT id,status,assigned_admin_id
                FROM support_tickets WHERE id=? FOR UPDATE');
            $stmt->bind_param('i', $ticketId);
            $stmt->execute();
            $ticket = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$ticket || $ticket['status'] === 'fechado') {
                throw new DomainException('Chamado inexistente ou encerrado.');
            }
            $old = $ticket['assigned_admin_id'] === null
                ? null : (int) $ticket['assigned_admin_id'];

            if ($action === 'release') {
                if ($old !== $adminId) throw new DomainException('Somente o titular pode liberar o chamado.');
                $stmt = $db->prepare('UPDATE support_tickets
                    SET assigned_admin_id=NULL,assigned_at=NULL WHERE id=?');
                $stmt->bind_param('i', $ticketId);
                $next = null;
                $event = 'released';
            } else {
                if ($action === 'claim' && $old !== null && $old !== $adminId) {
                    throw new DomainException('Chamado já possui responsável. Escolha assumir atendimento.');
                }
                if ($action === 'takeover' && $old === null) {
                    throw new DomainException('O chamado está livre. Escolha atender chamado.');
                }
                if ($old === $adminId) {
                    $db->commit();
                    return ['assigned_admin_id'=>$adminId,'assigned_to_me'=>true,'changed'=>false];
                }
                $stmt = $db->prepare('UPDATE support_tickets
                    SET assigned_admin_id=?,assigned_at=NOW() WHERE id=?');
                $stmt->bind_param('ii', $adminId, $ticketId);
                $next = $adminId;
                $event = $old === null ? 'claimed' : 'taken_over';
            }
            $stmt->execute();
            $stmt->close();
            self::log($db, $ticketId, $old, $next, $adminId, $event);
            $db->commit();
            return ['assigned_admin_id'=>$next,
                'assigned_to_me'=>$next === $adminId, 'changed'=>true];
        } catch (Throwable $exception) {
            $db->rollback();
            throw $exception;
        }
    }

    /** Encerramento protegido e atômico para o endpoint autenticado. */
    public static function close(mysqli $db, int $ticketId, int $adminId): void
    {
        if ($ticketId < 1 || $adminId < 1) throw new InvalidArgumentException('Chamado inválido.');
        $db->begin_transaction();
        try {
            $columns = self::ready($db) ? 'id,status,assigned_admin_id' : 'id,status';
            $stmt = $db->prepare('SELECT ' . $columns .
                ' FROM support_tickets WHERE id=? FOR UPDATE');
            $stmt->bind_param('i', $ticketId);
            $stmt->execute();
            $ticket = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$ticket || $ticket['status'] === 'fechado') {
                throw new DomainException('Chamado já encerrado ou inexistente.');
            }
            self::verifyOwnerOrClaim($db, $ticket, $adminId);
            $stmt = $db->prepare('UPDATE support_tickets SET status="fechado" WHERE id=?');
            $stmt->bind_param('i', $ticketId);
            $stmt->execute();
            $stmt->close();
            $db->commit();
        } catch (Throwable $exception) {
            $db->rollback();
            throw $exception;
        }
    }
}
