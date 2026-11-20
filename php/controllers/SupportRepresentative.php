<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

/**
 * Responsável único pelo atendimento; toda a equipe ADM pode ler a fila.
 * Migração 008 aditiva. Até a aplicação dela, a operação antiga permanece ativa.
 */
final class SupportRepresentative // Explicação DevIN: Declara o tipo SupportRepresentative e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    public static function ready(mysqli $db): bool // Explicação DevIN: Declara função/método ready, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            $db->query('SELECT assigned_admin_id,assigned_at FROM support_tickets LIMIT 0'); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            $db->query('SELECT id FROM support_assignment_events LIMIT 0'); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            return true; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } catch (mysqli_sql_exception $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            if (in_array((int) $exception->getCode(), [1054, 1146], true)) return false; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw $exception; // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    private static function log( // Explicação DevIN: Declara função/método log, com seus parâmetros e comportamento.
        mysqli $db, int $ticketId, ?int $oldId, ?int $newId, int $adminId, string $event // Explicação DevIN: Continua expressão ou bloco pertencente a função log.
    ): void { // Explicação DevIN: Continua expressão ou bloco pertencente a função log.
        $stmt = $db->prepare('INSERT INTO support_assignment_events
            (ticket_id,old_admin_id,new_admin_id,changed_by,event_type)
            VALUES (?,?,?,?,?)'); // Explicação DevIN: Continua expressão ou bloco pertencente a função log.
        $stmt->bind_param('iiiis', $ticketId, $oldId, $newId, $adminId, $event); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função log.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function listAdmin(mysqli $db, ?int $adminId = null): array // Explicação DevIN: Declara função/método listAdmin, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $columns = self::ready($db) // Explicação DevIN: Atribui um valor à variável PHP $columns.
            ? 'assigned_admin_id,assigned_at' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            : 'NULL AS assigned_admin_id,NULL AS assigned_at'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        $rows = $db->query('SELECT id,requester_name,requester_email,requester_type,
            category,description,status,attachment_name,' . $columns . ',created_at,updated_at
            FROM support_tickets ORDER BY FIELD(status,"aberto","respondido","fechado"),
            updated_at DESC LIMIT 150')->fetch_all(MYSQLI_ASSOC); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        foreach ($rows as &$row) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            $row['assigned_to_me'] = $adminId !== null // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                && (int) ($row['assigned_admin_id'] ?? 0) === $adminId; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        unset($row); // Explicação DevIN: Continua expressão ou bloco pertencente a função listAdmin.
        return $rows; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function myTickets(mysqli $db, int $adminId): array // Explicação DevIN: Declara função/método myTickets, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if ($adminId < 1 || !self::ready($db)) return []; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $stmt = $db->prepare('SELECT id,requester_name,requester_email,requester_type,
            category,description,status,attachment_name,assigned_admin_id,assigned_at,
            created_at,updated_at FROM support_tickets WHERE assigned_admin_id=?
            ORDER BY FIELD(status,"aberto","respondido","fechado"),updated_at DESC LIMIT 150'); // Explicação DevIN: Continua expressão ou bloco pertencente a função myTickets.
        $stmt->bind_param('i', $adminId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função myTickets.
        foreach ($rows as &$row) $row['assigned_to_me'] = true; // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
        unset($row); // Explicação DevIN: Continua expressão ou bloco pertencente a função myTickets.
        return $rows; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /** Chamada após SELECT FOR UPDATE dentro da transação de reply ou close. */
    public static function verifyOwnerOrClaim(mysqli $db, array $ticket, int $adminId): void // Explicação DevIN: Declara função/método verifyOwnerOrClaim, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!self::ready($db)) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if ($adminId < 1 || !isset($ticket['id'])) throw new DomainException('ADM inválido.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $owner = $ticket['assigned_admin_id'] === null ? null : (int) $ticket['assigned_admin_id']; // Explicação DevIN: Atribui um valor à variável PHP $owner.
        if ($owner !== null && $owner !== $adminId) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new DomainException('Outro ADM representa este chamado. Assuma o atendimento antes de responder ou encerrar.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if ($owner === null) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $id = (int) $ticket['id']; // Explicação DevIN: Atribui um valor à variável PHP $id.
            $stmt = $db->prepare('UPDATE support_tickets
                SET assigned_admin_id=?, assigned_at=NOW() WHERE id=?'); // Explicação DevIN: Continua expressão ou bloco pertencente a função verifyOwnerOrClaim.
            $stmt->bind_param('ii', $adminId, $id); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função verifyOwnerOrClaim.
            self::log($db, $id, null, $adminId, $adminId, 'claimed'); // Explicação DevIN: Continua expressão ou bloco pertencente a função verifyOwnerOrClaim.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function assign(mysqli $db, int $ticketId, int $adminId, string $action): array // Explicação DevIN: Declara função/método assign, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if ($ticketId < 1 || $adminId < 1 // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            || !in_array($action, ['claim', 'takeover', 'release'], true)) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            throw new InvalidArgumentException('Operação de representação inválida.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!self::ready($db)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new DomainException('Execute a migração 008 para habilitar representantes do suporte.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $stmt = $db->prepare('SELECT id_administrador FROM administrador
            WHERE id_administrador=? LIMIT 1'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assign.
        $stmt->bind_param('i', $adminId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $verified = $stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função assign.
        if (!$verified) throw new DomainException('O administrador não existe.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

        $db->begin_transaction(); // Explicação DevIN: Continua expressão ou bloco pertencente a função assign.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            $stmt = $db->prepare('SELECT id,status,assigned_admin_id
                FROM support_tickets WHERE id=? FOR UPDATE'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assign.
            $stmt->bind_param('i', $ticketId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $ticket = $stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função assign.
            if (!$ticket || $ticket['status'] === 'fechado') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new DomainException('Chamado inexistente ou encerrado.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $old = $ticket['assigned_admin_id'] === null // Explicação DevIN: Atribui um valor à variável PHP $old.
                ? null : (int) $ticket['assigned_admin_id']; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.

            if ($action === 'release') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                if ($old !== $adminId) throw new DomainException('Somente o titular pode liberar o chamado.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                $stmt = $db->prepare('UPDATE support_tickets
                    SET assigned_admin_id=NULL,assigned_at=NULL WHERE id=?'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assign.
                $stmt->bind_param('i', $ticketId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $next = null; // Explicação DevIN: Atribui um valor à variável PHP $next.
                $event = 'released'; // Explicação DevIN: Atribui um valor à variável PHP $event.
            } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
                if ($action === 'claim' && $old !== null && $old !== $adminId) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    throw new DomainException('Chamado já possui responsável. Escolha assumir atendimento.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                if ($action === 'takeover' && $old === null) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    throw new DomainException('O chamado está livre. Escolha atender chamado.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                if ($old === $adminId) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    $db->commit(); // Explicação DevIN: Continua expressão ou bloco pertencente a função assign.
                    return ['assigned_admin_id'=>$adminId,'assigned_to_me'=>true,'changed'=>false]; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                $stmt = $db->prepare('UPDATE support_tickets
                    SET assigned_admin_id=?,assigned_at=NOW() WHERE id=?'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assign.
                $stmt->bind_param('ii', $adminId, $ticketId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $next = $adminId; // Explicação DevIN: Atribui um valor à variável PHP $next.
                $event = $old === null ? 'claimed' : 'taken_over'; // Explicação DevIN: Atribui um valor à variável PHP $event.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função assign.
            self::log($db, $ticketId, $old, $next, $adminId, $event); // Explicação DevIN: Continua expressão ou bloco pertencente a função assign.
            $db->commit(); // Explicação DevIN: Continua expressão ou bloco pertencente a função assign.
            return ['assigned_admin_id'=>$next, // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
                'assigned_to_me'=>$next === $adminId, 'changed'=>true]; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        } catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            $db->rollback(); // Explicação DevIN: Continua expressão ou bloco pertencente a função assign.
            throw $exception; // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /** Encerramento protegido e atômico para o endpoint autenticado. */
    public static function close(mysqli $db, int $ticketId, int $adminId): void // Explicação DevIN: Declara função/método close, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if ($ticketId < 1 || $adminId < 1) throw new InvalidArgumentException('Chamado inválido.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $db->begin_transaction(); // Explicação DevIN: Continua expressão ou bloco pertencente a função close.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            $columns = self::ready($db) ? 'id,status,assigned_admin_id' : 'id,status'; // Explicação DevIN: Atribui um valor à variável PHP $columns.
            $stmt = $db->prepare('SELECT ' . $columns . // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                ' FROM support_tickets WHERE id=? FOR UPDATE'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            $stmt->bind_param('i', $ticketId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $ticket = $stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função close.
            if (!$ticket || $ticket['status'] === 'fechado') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new DomainException('Chamado já encerrado ou inexistente.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            self::verifyOwnerOrClaim($db, $ticket, $adminId); // Explicação DevIN: Continua expressão ou bloco pertencente a função close.
            $stmt = $db->prepare('UPDATE support_tickets SET status="fechado" WHERE id=?'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->bind_param('i', $ticketId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função close.
            $db->commit(); // Explicação DevIN: Continua expressão ou bloco pertencente a função close.
        } catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            $db->rollback(); // Explicação DevIN: Continua expressão ou bloco pertencente a função close.
            throw $exception; // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
