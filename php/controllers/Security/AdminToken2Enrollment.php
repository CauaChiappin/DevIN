<?php
declare(strict_types=1);

require_once __DIR__ . '/AdminStepUp.php';
require_once __DIR__ . '/AdminToken2Hasher.php';

/**
 * Token 2 permanente, cadastrado SOMENTE pelo próprio ADM autenticado.
 * O primeiro cadastro exige Token 1; toda troca exige Token 1 + Token 2 atual.
 * Não são usados códigos temporários ou dados de terceiros fornecidos no POST.
 */
final class AdminToken2Enrollment
{
    public static function configured(): bool
    {
        $id = AdminStepUp::adminId();
        $db = getDatabaseConnection();
        try {
            $stmt = $db->prepare(
                'SELECT token2_hash FROM administrador WHERE id_administrador = ? LIMIT 1'
            );
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return is_array($row) && is_string($row['token2_hash'])
                && $row['token2_hash'] !== '';
        } finally {
            $db->close();
        }
    }

    private static function limit(int $id): void
    {
        $ip = RateLimiter::clientIp();
        if (RateLimiter::consume('stepup:admin:' . $id, 8, 900) > 0
            || RateLimiter::consume('stepup:ip:' . $ip, 24, 900) > 0
            || RateLimiter::consume('stepup:enroll:' . $id, 6, 900) > 0) {
            throw new RuntimeException('Limite de tentativas excedido.');
        }
    }

    public static function validateNewToken(string $newToken2, string $confirmation): void
    {
        // Sem limite ARBITRÁRIO de caracteres. Teto técnico 64 KiB evita negação
        // de serviço por formulários imensos; a interface não impõe maxlength.
        if ($newToken2 !== $confirmation || $newToken2 === ''
            || strlen($newToken2) > AdminToken2Hasher::MAX_INPUT_BYTES
            || preg_match('//u', $newToken2) !== 1
            || preg_match('/[\x00-\x1F\x7F]/', $newToken2) === 1
            || preg_match_all('/./us', $newToken2) < 12) {
            throw new InvalidArgumentException('Token 2 inválido ou confirmação diferente.');
        }
    }

    /**
     * true: cadastrado ou alterado; false: verificação recusada.
     * Dados sensíveis nunca são gravados em logs nem devolvidos ao chamador.
     */
    public static function save(
        string $token1, ?string $currentToken2,
        string $newToken2, string $confirmation
    ): bool {
        $id = AdminStepUp::adminId();
        self::validateNewToken($newToken2, $confirmation);
        if ($token1 === '' || strlen($token1) > 512) return false;
        self::limit($id);

        $db = getDatabaseConnection();
        try {
            $db->begin_transaction();
            $stmt = $db->prepare(
                'SELECT senha_hash, token2_hash FROM administrador '
                . 'WHERE id_administrador = ? LIMIT 1 FOR UPDATE'
            );
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $account = $stmt->get_result()->fetch_assoc() ?: null;
            $stmt->close();

            if (!$account || !is_string($account['senha_hash'])
                || !password_verify($token1, $account['senha_hash'])
                || password_verify($newToken2, $account['senha_hash'])) {
                $db->rollback();
                return false;
            }
            $hasPrevious = is_string($account['token2_hash'])
                && $account['token2_hash'] !== '';
            if ($hasPrevious && (
                $currentToken2 === null
                || !AdminToken2Hasher::verify($currentToken2, $account['token2_hash'])
                || AdminToken2Hasher::verify($newToken2, $account['token2_hash'])
            )) {
                $db->rollback();
                return false;
            }

            // Poucos ADMs: impedir reutilização da mesma chave por outro ADM.
            $stmt = $db->prepare(
                'SELECT token2_hash FROM administrador WHERE id_administrador <> ? '
                . 'AND token2_hash IS NOT NULL'
            );
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $others = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            foreach ($others as $other) {
                if (AdminToken2Hasher::verify($newToken2, $other['token2_hash'])) {
                    $db->rollback();
                    return false;
                }
            }

            $hash = AdminToken2Hasher::create($newToken2);
            $stmt = $db->prepare(
                'UPDATE administrador SET token2_hash = ?, '
                . 'token2_version = token2_version + 1, '
                . 'token2_configured_at = UTC_TIMESTAMP(6) '
                . 'WHERE id_administrador = ?'
            );
            $stmt->bind_param('si', $hash, $id);
            $stmt->execute();
            $stmt->close();
            $db->commit();

            // As autorizações de site e IA desta sessão perdem validade agora;
            // outras sessões serão invalidadas pela mudança de versão/hash.
            AdminStepUp::clear();
            RateLimiter::clear('stepup:admin:' . $id);
            RateLimiter::clear('stepup:enroll:' . $id);
            require_once __DIR__ . '/../Privacy/AuditService.php';
            AuditService::tryRecord('admin_token2_enrolled', 'success', 'adm', $id);
            return true;
        } catch (Throwable $exception) {
            $db->rollback();
            throw $exception;
        } finally {
            $db->close();
        }
    }
}
