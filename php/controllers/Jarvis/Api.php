<?php
declare(strict_types=1);

require_once __DIR__ . '/../../middlewares/auth.php';
require_once __DIR__ . '/../../config/RateLimiter.php';

function jarvisJson(int $status, array $data): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, private');
    header('X-Content-Type-Options: nosniff');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

/** JSON 401/403, sem redirecionar o fetch do widget à página de login. */
function jarvisAdmin(): int
{
    startSecureSession();
    $now = time();
    $last = (int) ($_SESSION['last_activity'] ?? 0);
    $authTime = (int) ($_SESSION['auth_time'] ?? 0);
    $id = (int) ($_SESSION['usuario_id'] ?? 0);
    if (empty($_SESSION['logado']) || $last <= 0 || $authTime <= 0
        || $now - $last > 1800 || $now - $authTime > 43200) {
        jarvisJson(401, ['success' => false, 'message' => 'Sessão expirada. Faça login novamente.']);
    }
    if (($_SESSION['usuario_tipo'] ?? null) !== 'adm' || $id <= 0) {
        jarvisJson(403, ['success' => false, 'message' => 'Acesso exclusivo para administradores.']);
    }
    // Verifica existência da conta ADM para impedir o uso de uma sessão de conta removida.
    try {
        $db = getDatabaseConnection();
        try {
            $stmt = $db->prepare('SELECT id_administrador FROM administrador WHERE id_administrador = ? LIMIT 1');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $valid = $stmt->get_result()->fetch_row() !== null;
            $stmt->close();
        } finally {
            $db->close();
        }
    } catch (Throwable $exception) {
        error_log('JARVIS admin verification: ' . $exception->getMessage());
        jarvisJson(503, ['success' => false, 'message' => 'Não foi possível validar a conta administrativa.']);
    }
    if (!$valid) {
        jarvisJson(403, ['success' => false, 'message' => 'Conta administrativa não encontrada.']);
    }
    $_SESSION['last_activity'] = $now;
    return $id;
}

function jarvisCsrf(): void
{
    if (!verifyCsrfToken($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
        jarvisJson(403, ['success' => false, 'message' => 'Token de segurança inválido. Atualize a página.']);
    }
}

function jarvisBody(int $maxBytes = 18000): array
{
    $length = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
    if ($length > $maxBytes) {
        jarvisJson(413, ['success' => false, 'message' => 'Solicitação muito grande.']);
    }
    $raw = file_get_contents('php://input', false, null, 0, $maxBytes + 1);
    if (!is_string($raw) || strlen($raw) > $maxBytes) {
        jarvisJson(413, ['success' => false, 'message' => 'Solicitação muito grande.']);
    }
    try {
        $decoded = json_decode($raw, true, 16, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        jarvisJson(400, ['success' => false, 'message' => 'JSON inválido.']);
    }
    if (!is_array($decoded)) {
        jarvisJson(400, ['success' => false, 'message' => 'Corpo inválido.']);
    }
    return $decoded;
}

function jarvisLimit(int $adminId, string $scope, int $limit = 30): void
{
    try {
        if (RateLimiter::consume('jarvis:' . $scope . ':' . $adminId, $limit, 3600) > 0) {
            jarvisJson(429, ['success' => false, 'message' => 'Limite de solicitações atingido.']);
        }
    } catch (Throwable $exception) {
        error_log('JARVIS rate limiter: ' . $exception->getMessage());
        jarvisJson(503, ['success' => false, 'message' => 'Limite de uso temporariamente indisponível.']);
    }
}
