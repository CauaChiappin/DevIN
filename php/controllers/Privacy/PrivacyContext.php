<?php
declare(strict_types=1);

/**
 * Helper puro: nunca confia em X-Forwarded-For, cookies ou ids de cliente.
 * A chave HMAC é separada da senha JWT e não pode aparecer no banco ou no Git.
 */
final class PrivacyContext
{
    private const ACCOUNT_TABLES = [
        'pessoa' => ['pessoa', 'id_pessoa'],
        'empresa' => ['empresa', 'id_empresa'],
        'adm' => ['administrador', 'id_administrador'],
    ];
    private const PURPOSES = ['marketing_email','analytics_cookies','ai_resume_transfer','ai_admin_history'];

    public static function env(string $name): string
    {
        return trim((string) ($_ENV[$name] ?? $_SERVER[$name] ?? getenv($name) ?: ''));
    }

    public static function validateAccount(string $type, int $id): void
    {
        if (!array_key_exists($type, self::ACCOUNT_TABLES) || $id < 1) {
            throw new InvalidArgumentException('Conta inválida.');
        }
    }

    public static function requireAccount(mysqli $db, string $type, int $id, bool $lock = false): void
    {
        self::validateAccount($type, $id);
        [$table, $key] = self::ACCOUNT_TABLES[$type];
        $stmt = $db->prepare("SELECT {$key} FROM {$table} WHERE {$key} = ? LIMIT 1" . ($lock ? ' FOR UPDATE' : ''));
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_row() !== null;
        $stmt->close();
        if (!$exists) throw new InvalidArgumentException('Conta não encontrada.');
    }

    public static function validatePurpose(string $purpose): void
    {
        if (!in_array($purpose, self::PURPOSES, true)) {
            throw new InvalidArgumentException('Finalidade não autorizada.');
        }
    }

    public static function clientIp(?array $server = null): ?string
    {
        $source = $server ?? $_SERVER;
        $ip = $source['REMOTE_ADDR'] ?? null;
        return is_string($ip) && filter_var($ip, FILTER_VALIDATE_IP) ? $ip : null;
    }

    public static function ipHash(?string $ip, ?string $overrideKey = null): ?string
    {
        if ($ip === null || !filter_var($ip, FILTER_VALIDATE_IP)) return null;
        $key = $overrideKey ?? self::env('DEVIN_LGPD_IP_HASH_KEY');
        if (strlen($key) < 32) {
            throw new RuntimeException('Chave HMAC de privacidade ausente ou fraca.');
        }
        // inet_pton normaliza IPv4 e IPv6. HMAC evita hash público reversível por dicionário.
        $packed = inet_pton($ip);
        if ($packed === false) return null;
        return hash_hmac('sha256', $packed, $key);
    }

    public static function userAgent(?string $value = null): ?string
    {
        $value = $value ?? ($_SERVER['HTTP_USER_AGENT'] ?? null);
        if (!is_string($value) || trim($value) === '') return null;
        // Remove caracteres de controle e limita a largura para a coluna de auditoria.
        $safe = preg_replace('/[\x00-\x1F\x7F]/', '', $value);
        return substr((string) $safe, 0, 512);
    }

    public static function requestId(): string
    {
        $hex = bin2hex(random_bytes(16));
        return substr($hex,0,8) . '-' . substr($hex,8,4) . '-' . substr($hex,12,4)
            . '-' . substr($hex,16,4) . '-' . substr($hex,20,12);
    }
}
