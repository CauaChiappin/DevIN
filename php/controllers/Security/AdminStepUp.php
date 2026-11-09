<?php
declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/security.php';
require_once __DIR__ . '/../../config/RateLimiter.php';

/**
 * Verificação adicional ADM. Nenhuma credencial é exposta ao navegador nem ao modelo de IA.
 * O número do token é apenas um marcador de etapa, não revela sua natureza na interface.
 */
final class AdminStepUp
{
    private const MAX_IDLE_SECONDS = 1800;
    private const MAX_LIFETIME_SECONDS = 28800;
    private const STAGE_TTL_SECONDS = 300;

    public static function enabled(): bool
    {
        $value = $_ENV['DEVIN_ADMIN_STEPUP_ENABLED'] ?? $_SERVER['DEVIN_ADMIN_STEPUP_ENABLED'] ?? getenv('DEVIN_ADMIN_STEPUP_ENABLED');
        return (string) $value === '1';
    }

    /** Chave de sessão pertence ao ADM autenticado, jamais aceita ID fornecido pelo browser. */
    public static function adminId(): int
    {
        startSecureSession();
        $now = time();
        $id = (int) ($_SESSION['usuario_id'] ?? 0);
        $last = (int) ($_SESSION['last_activity'] ?? 0);
        $auth = (int) ($_SESSION['auth_time'] ?? 0);
        if (empty($_SESSION['logado']) || ($_SESSION['usuario_tipo'] ?? '') !== 'adm' || $id < 1
            || $last <= 0 || $auth <= 0 || $now - $last > 1800 || $now - $auth > 43200) {
            throw new DomainException('Sessão não autorizada.');
        }
        return $id;
    }

    /** Retorna apenas revisões/segredos já protegidos por hash armazenados no banco. */
    private static function credentials(mysqli $db, int $id): ?array
    {
        $stmt = $db->prepare(
            'SELECT a.senha_hash AS password_hash, c.token2_hash, c.credential_version '
            . 'FROM administrador a LEFT JOIN admin_security_credentials c '
            . 'ON c.admin_id = a.id_administrador '
            . 'WHERE a.id_administrador = ? LIMIT 1'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc() ?: null;
        $stmt->close();
        if (!$row || !is_string($row['password_hash']) || $row['password_hash'] === '') return null;
        return $row;
    }

    private static function revision(array $credentials): string
    {
        return hash('sha256', $credentials['password_hash'] . "\0" . (string) ($credentials['credential_version'] ?? '0'));
    }

    private static function sessionKey(string $scope): string
    {
        if (!in_array($scope, ['site','ai'], true)) throw new InvalidArgumentException('Escopo inválido.');
        return 'admin_stepup_grant_' . $scope;
    }

    /** Compara dados atuais do banco, então rotação de credencial revoga imediatamente. */
    public static function allowed(string $scope): bool
    {
        if (!self::enabled()) return false;
        $id = self::adminId();
        $key = self::sessionKey($scope);
        $grant = $_SESSION[$key] ?? null;
        if (!is_array($grant)) return false;
        $now = time();
        if ((int) ($grant['admin_id'] ?? 0) !== $id
            || (int) ($grant['auth_time'] ?? 0) !== (int) ($_SESSION['auth_time'] ?? 0)
            || $now - (int) ($grant['issued_at'] ?? 0) > self::MAX_LIFETIME_SECONDS
            || $now - (int) ($grant['used_at'] ?? 0) > self::MAX_IDLE_SECONDS) {
            unset($_SESSION[$key]);
            return false;
        }
        $db = getDatabaseConnection();
        try {
            $credentials = self::credentials($db, $id);
        } finally {
            $db->close();
        }
        if (!$credentials || !$credentials['token2_hash']
            || !hash_equals((string) ($grant['revision'] ?? ''), self::revision($credentials))) {
            unset($_SESSION[$key]);
            return false;
        }
        $_SESSION[$key]['used_at'] = $now;
        return true;
    }

    public static function requireAllowed(string $scope): void
    {
        if (!self::allowed($scope)) throw new DomainException('Verificação adicional necessária.');
    }

    /**
     * Identificador apenas de etapa; o frontend só mostra o campo designado.
     * Não retorna hash, senha, hash de sessão nem motivo detalhado de falha.
     */
    public static function nextAiToken(): int
    {
        $id = self::adminId();
        $stage = $_SESSION['admin_stepup_ai_stage'] ?? null;
        if (!is_array($stage)
            || ($stage['admin_id'] ?? null) !== $id
            || time() - (int) ($stage['at'] ?? 0) > self::STAGE_TTL_SECONDS) {
            unset($_SESSION['admin_stepup_ai_stage']);
            return 1;
        }
        $db = getDatabaseConnection();
        try {
            $credentials = self::credentials($db, $id);
        } finally {
            $db->close();
        }
        if (!$credentials
            || !hash_equals((string) ($stage['revision'] ?? ''), self::revision($credentials))) {
            unset($_SESSION['admin_stepup_ai_stage']);
            return 1;
        }
        return 2;
    }

    private static function limit(int $id): void
    {
        $ip = RateLimiter::clientIp();
        // Bloqueio combinado por conta e rede, nunca por token fornecido pelo cliente.
        if (RateLimiter::consume('stepup:admin:' . $id, 8, 900) > 0
            || RateLimiter::consume('stepup:ip:' . $ip, 24, 900) > 0) {
            throw new RuntimeException('Limite de tentativas excedido.');
        }
    }

    /** Usado apenas no desbloqueio do site, sem passar pelo modelo externo. */
    public static function verifySite(string $token): bool
    {
        $id = self::adminId();
        self::limit($id);
        if ($token === '' || strlen($token) > 512) return false;
        $db = getDatabaseConnection();
        try {
            $credentials = self::credentials($db, $id);
        } finally {
            $db->close();
        }
        if (!$credentials || !is_string($credentials['token2_hash'])
            || $credentials['token2_hash'] === ''
            || !password_verify($token, $credentials['token2_hash'])) {
            return false;
        }
        self::grant('site', $id, $credentials);
        return true;
    }

    /** Somente Token 1 pode criar o estágio intermediário da IA. */
    public static function verifyAiFirst(string $token): bool
    {
        $id = self::adminId();
        self::limit($id);
        unset($_SESSION['admin_stepup_ai_stage']);
        if ($token === '' || strlen($token) > 512) return false;
        $db = getDatabaseConnection();
        try {
            $credentials = self::credentials($db, $id);
        } finally {
            $db->close();
        }
        if (!$credentials || !$credentials['token2_hash']
            || !password_verify($token, $credentials['password_hash'])) return false;
        $_SESSION['admin_stepup_ai_stage'] = [
            'admin_id' => $id, 'at' => time(), 'revision' => self::revision($credentials),
        ];
        return true;
    }

    /** Token 2 somente é aceito após estágio 1 válido e ainda vigente. */
    public static function verifyAiSecond(string $token): bool
    {
        if (self::nextAiToken() !== 2) return false;
        $id = self::adminId();
        self::limit($id);
        if ($token === '' || strlen($token) > 512) return false;
        $db = getDatabaseConnection();
        try {
            $credentials = self::credentials($db, $id);
        } finally {
            $db->close();
        }
        if (!$credentials || !$credentials['token2_hash']
            || !password_verify($token, $credentials['token2_hash'])) return false;
        unset($_SESSION['admin_stepup_ai_stage']);
        self::grant('ai', $id, $credentials);
        return true;
    }

    private static function grant(string $scope, int $id, array $credentials): void
    {
        $key = self::sessionKey($scope);
        // Não guarda os tokens; troca o identificador PHP após elevação de privilégio.
        secureSessionRegenerate();
        $_SESSION[$key] = [
            'admin_id' => $id,
            'auth_time' => (int) ($_SESSION['auth_time'] ?? 0),
            'revision' => self::revision($credentials),
            'issued_at' => time(), 'used_at' => time(),
        ];
    }

    /** Invocar após troca de credenciais ou suspeita de sessão comprometida. */
    public static function clear(): void
    {
        startSecureSession();
        unset($_SESSION['admin_stepup_grant_site'], $_SESSION['admin_stepup_grant_ai'], $_SESSION['admin_stepup_ai_stage']);
    }
}
