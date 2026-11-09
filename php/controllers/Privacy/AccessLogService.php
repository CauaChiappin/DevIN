<?php
declare(strict_types=1);

require_once __DIR__ . '/PrivacyContext.php';

/**
 * Marco Civil: registro de acesso à aplicação não equivale a log de auditoria.
 * Preserva IP recuperável (inet6_aton) quando o art. 15 for aplicável.
 * Não é ativado globalmente nesta fase; configurar middleware na fase 4.
 */
final class AccessLogService
{
    public static function cleanPath(string $requestUri): string
    {
        $path = parse_url($requestUri, PHP_URL_PATH);
        if (!is_string($path) || $path === '') return '/';
        // Sem query string, cookies, fragmentos nem dados de formulários.
        $path = preg_replace('/[\x00-\x1F\x7F]/', '', $path);
        return substr((string) $path, 0, 255) ?: '/';
    }

    public static function record(
        mysqli $db,
        string $clientIp,
        string $method,
        string $requestUri,
        int $status,
        ?string $actorType = null,
        ?int $actorId = null
    ): int {
        if (!filter_var($clientIp, FILTER_VALIDATE_IP)) {
            throw new InvalidArgumentException('Endereço de acesso inválido.');
        }
        $method = strtoupper($method);
        if (!in_array($method, ['GET','HEAD','POST','PUT','PATCH','DELETE','OPTIONS'], true)
            || $status < 100 || $status > 599) {
            throw new InvalidArgumentException('Método ou status inválido.');
        }
        if ($actorType !== null) PrivacyContext::validateAccount($actorType, $actorId ?? 0);
        elseif ($actorId !== null) throw new InvalidArgumentException('Ator inválido.');

        $path = self::cleanPath($requestUri);
        $stmt = $db->prepare(
            'INSERT INTO app_access_logs '
            . '(client_ip,accessed_at,method,path,http_status,actor_type,actor_id,delete_after) '
            . 'VALUES (INET6_ATON(?),UTC_TIMESTAMP(6),?,?,?,?,?,DATE_ADD(UTC_TIMESTAMP(6),INTERVAL 6 MONTH))'
        );
        $stmt->bind_param('sssis i', $clientIp, $method, $path, $status, $actorType, $actorId);
        $stmt->execute();
        $insertId = (int) $db->insert_id;
        $stmt->close();
        return $insertId;
    }

    /** Usar somente REMOTE_ADDR do servidor, nunca cabeçalhos não confiáveis. */
    public static function recordCurrent(mysqli $db, int $status, ?string $type = null, ?int $id = null): ?int
    {
        $ip = PrivacyContext::clientIp();
        if ($ip === null) return null;
        return self::record(
            $db, $ip, (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'),
            (string) ($_SERVER['REQUEST_URI'] ?? '/'), $status, $type, $id
        );
    }
}
