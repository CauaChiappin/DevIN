<?php
declare(strict_types=1);

require_once __DIR__ . '/AdminStepUp.php';

/**
 * Autoinscrição: login ADM válido + Token 1 + código de ativação individual
 * fora de banda. A nova credencial não é devolvida nem armazenada em texto.
 * Reemissão de código serve também para redefinir o Token 2 perdido.
 */
final class AdminToken2Enrollment
{
    public const MIN_CHARACTERS = 14;
    private const MAX_BYTES = 72; // Portabilidade com o limite do bcrypt.

    public static function validateNewToken(string $newToken, string $confirmation): void
    {
        if ($newToken !== $confirmation
            || strlen($newToken) > self::MAX_BYTES
            || mb_strlen($newToken, 'UTF-8') < self::MIN_CHARACTERS
            || trim($newToken) === ''
            || preg_match('/[\x00-\x1F\x7F]/', $newToken) === 1) {
            throw new InvalidArgumentException('O Token 2 não atende aos requisitos ou à confirmação.');
        }
    }

    private static function limit(int $adminId): void
    {
        $ip = RateLimiter::clientIp();
        if (RateLimiter::consume('stepup:admin:' . $adminId, 8, 900) > 0
            || RateLimiter::consume('stepup:ip:' . $ip, 24, 900) > 0
            || RateLimiter::consume('stepup:enroll:' . $adminId, 5, 900) > 0) {
            throw new RuntimeException('Limite de tentativas excedido.');
        }
    }

    /**
     * @return bool false para entrada inválida/expirada, true quando gravou.
     * A conta, o código e a credencial anterior permanecem sob lock durante
     * verificação e gravação; sucesso consome a ativação no mesmo COMMIT.
     */
    public static function enroll(
        int $adminId,
        string $token1,
        string $activationCode,
        string $newToken2,
        string $confirmation
    ): bool {
        self::validateNewToken($newToken2, $confirmation);
        if ($adminId < 1 || strlen($token1) > 512 || strlen($activationCode) > 128) {
            return false;
        }
        self::limit($adminId);

        $db = getDatabaseConnection();
        try {
            $db->begin_transaction();
            $stmt = $db->prepare('SELECT senha_hash FROM administrador WHERE id_administrador = ? LIMIT 1 FOR UPDATE');
            $stmt->bind_param('i', $adminId);
            $stmt->execute();
            $account = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            $stmt = $db->prepare(
                'SELECT activation_hash, (expires_at > UTC_TIMESTAMP(6)) AS valid_now '
                . 'FROM admin_security_enrollments WHERE admin_id = ? LIMIT 1 FOR UPDATE'
            );
            $stmt->bind_param('i', $adminId);
            $stmt->execute();
            $invitation = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$account || !is_string($account['senha_hash']) || !$invitation
                || (int) $invitation['valid_now'] !== 1
                || !password_verify($token1, $account['senha_hash'])
                || !password_verify($activationCode, $invitation['activation_hash'])
                || password_verify($newToken2, $account['senha_hash'])) {
                $db->rollback();
                return false;
            }

            // Apenas quatro ADMs: rejeita escolha idêntica à credencial de outra conta.
            $stmt = $db->prepare('SELECT token2_hash FROM admin_security_credentials WHERE admin_id <> ? FOR UPDATE');
            $stmt->bind_param('i', $adminId);
            $stmt->execute();
            $others = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            foreach ($others as $other) {
                if (password_verify($newToken2, $other['token2_hash'])) {
                    $db->rollback();
                    return false;
                }
            }

            $algorithm = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
            $hash = password_hash($newToken2, $algorithm);
            if (!is_string($hash)) {
                throw new RuntimeException('Não foi possível armazenar a credencial.');
            }
            $stmt = $db->prepare(
                'INSERT INTO admin_security_credentials '
                . '(admin_id,token2_hash,credential_version,assigned_at,rotated_at) '
                . 'VALUES (?, ?, 1, UTC_TIMESTAMP(6), UTC_TIMESTAMP(6)) '
                . 'ON DUPLICATE KEY UPDATE token2_hash=VALUES(token2_hash), '
                . 'credential_version=credential_version+1, rotated_at=UTC_TIMESTAMP(6)'
            );
            $stmt->bind_param('is', $adminId, $hash);
            $stmt->execute();
            $stmt->close();

            $stmt = $db->prepare('DELETE FROM admin_security_enrollments WHERE admin_id = ?');
            $stmt->bind_param('i', $adminId);
            $stmt->execute();
            $stmt->close();
            $db->commit();

            // Rotação invalida sessões de qualquer dispositivo na próxima consulta.
            // A sessão corrente também perde imediatamente as permissões.
            AdminStepUp::clear();
            require_once __DIR__ . '/../Privacy/AuditService.php';
            AuditService::tryRecord('admin_token2_enrolled', 'success', 'adm', $adminId);
            return true;
        } catch (Throwable $exception) {
            $db->rollback();
            throw $exception;
        } finally {
            $db->close();
        }
    }
}
