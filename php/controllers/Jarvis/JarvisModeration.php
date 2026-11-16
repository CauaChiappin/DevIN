<?php
declare(strict_types=1);

/** Fluxo de exclusão supervisionado por ADM, nunca pelo modelo externo. */
final class JarvisModeration
{
    private const MAP = [
        'vaga' => ['vagas', 'id_vaga', 'titulo'],
        'pessoa' => ['pessoa', 'id_pessoa', 'nome'],
        'empresa' => ['empresa', 'id_empresa', 'nome'],
    ];

    public static function validKind(string $kind): bool
    {
        return isset(self::MAP[$kind]);
    }

    public static function target(mysqli $db, string $kind, int $id, bool $lock = false): ?array
    {
        if (!self::validKind($kind) || $id < 1) {
            throw new InvalidArgumentException('Alvo inválido.');
        }
        [$table, $primary, $title] = self::MAP[$kind];
        $query = "SELECT {$primary} AS id, {$title} AS title FROM {$table} "
            . "WHERE {$primary}=? LIMIT 1" . ($lock ? " FOR UPDATE" : "");
        $stmt = $db->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc() ?: null;
        $stmt->close();
        return $row === null ? null : [
            'id' => (int) $row['id'],
            'kind' => $kind,
            'title' => mb_substr((string) $row['title'], 0, 120, 'UTF-8'),
        ];
    }

    /**
     * Gerar proposta vinculada à sessão e ao objeto atual, com prazo curto.
     * O título só é exibido; ele nunca é executado como instrução.
     */
    public static function prepare(mysqli $db, int $admin, string $kind, int $id): array
    {
        $record = self::target($db, $kind, $id);
        if ($record === null) {
            throw new DomainException('Registro não encontrado.');
        }
        $nonce = bin2hex(random_bytes(24));
        $_SESSION['jarvis_delete_pending'] = [
            'nonce_hash' => hash('sha256', $nonce),
            'admin' => $admin,
            'kind' => $kind,
            'id' => $id,
            'title_hash' => hash('sha256', $record['title']),
            'expires_at' => time() + 180,
        ];
        return [
            'record' => $record,
            'confirmation' => $nonce,
            'expires_in' => 180,
            'warning' => 'Exclusão definitiva: pode afetar registros relacionados. '
                . 'Confira o cadastro e mantenha backup antes de confirmar.',
        ];
    }

    public static function execute(
        mysqli $db,
        int $admin,
        string $kind,
        int $id,
        string $nonce,
        string $approval
    ): array {
        $pending = $_SESSION['jarvis_delete_pending'] ?? null;
        unset($_SESSION['jarvis_delete_pending']);
        if (!is_array($pending) || $approval !== 'EXCLUIR'
            || !preg_match('/^[a-f0-9]{48}$/D', $nonce)
            || !hash_equals((string) ($pending['nonce_hash'] ?? ''), hash('sha256', $nonce))
            || (int) ($pending['expires_at'] ?? 0) < time()
            || (int) ($pending['admin'] ?? 0) !== $admin
            || ($pending['kind'] ?? null) !== $kind
            || (int) ($pending['id'] ?? 0) !== $id) {
            throw new DomainException('Confirmação expirada ou inválida. Solicite nova prévia.');
        }

        [$table, $primary] = self::MAP[$kind] ?? [null, null];
        if ($table === null || $id < 1) {
            throw new InvalidArgumentException('Alvo inválido.');
        }
        $db->begin_transaction();
        try {
            $record = self::target($db, $kind, $id, true);
            if ($record === null
                || !hash_equals((string) $pending['title_hash'], hash('sha256', $record['title']))) {
                throw new DomainException('Registro alterado ou indisponível. Faça nova consulta.');
            }
            $stmt = $db->prepare("DELETE FROM {$table} WHERE {$primary}=?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $affected = $stmt->affected_rows;
            $stmt->close();
            if ($affected !== 1) {
                throw new DomainException('Não foi possível excluir o registro selecionado.');
            }
            $stmt = $db->prepare(
                'INSERT INTO jarvis_moderation_audit '
                . '(admin_id,target_type,target_id,action_code) VALUES (?,?,?,?)'
            );
            $action = 'deleted';
            $stmt->bind_param('isis', $admin, $kind, $id, $action);
            $stmt->execute();
            $stmt->close();
            $db->commit();
            return ['success' => true, 'message' => 'Registro excluído após confirmação do administrador.',
                'record' => $record];
        } catch (Throwable $error) {
            $db->rollback();
            throw $error;
        }
    }
}
