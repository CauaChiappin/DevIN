<?php
declare(strict_types=1);

require_once __DIR__ . '/../controllers/Security/AdminStepUp.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');
header('Referrer-Policy: no-referrer');
header('X-Content-Type-Options: nosniff');

function securityJson(int $status, array $data): never
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? '';
if (!in_array($method, ['GET','POST'], true)) {
    header('Allow: GET, POST');
    securityJson(405, ['success'=>false,'message'=>'Operação indisponível.']);
}

try {
    $admin = AdminStepUp::adminId();
    if (!AdminStepUp::enabled()) {
        securityJson(503, ['success'=>false,'message'=>'Verificação indisponível.']);
    }
    if ($method === 'GET') {
        $scope = $_GET['scope'] ?? '';
        if (!is_string($scope) || !in_array($scope, ['site','ai'], true)) {
            securityJson(400, ['success'=>false,'message'=>'Operação inválida.']);
        }
        $ready = AdminStepUp::allowed($scope);
        securityJson(200, [
            'success'=>true,
            'unlocked'=>$ready,
            'next_token'=>$ready ? null : ($scope === 'ai' ? AdminStepUp::nextAiToken() : 2),
        ]);
    }
    if (!verifyCsrfToken($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
        securityJson(403, ['success'=>false,'message'=>'Solicitação inválida.']);
    }
    if ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 1024) {
        securityJson(413, ['success'=>false,'message'=>'Solicitação inválida.']);
    }
    $raw = file_get_contents('php://input', false, null, 0, 1025);
    if (!is_string($raw) || strlen($raw) > 1024) {
        securityJson(413, ['success'=>false,'message'=>'Solicitação inválida.']);
    }
    $body = json_decode($raw, true, 6, JSON_THROW_ON_ERROR);
    if (!is_array($body) || !in_array($body['scope'] ?? null, ['site','ai'], true)
        || !is_string($body['token'] ?? null)) {
        securityJson(400, ['success'=>false,'message'=>'Solicitação inválida.']);
    }
    $scope = $body['scope'];
    if (AdminStepUp::allowed($scope)) {
        securityJson(200, ['success'=>true,'unlocked'=>true,'next_token'=>null]);
    }
    $token = $body['token'];
    $valid = $scope === 'site'
        ? AdminStepUp::verifySite($token)
        : (AdminStepUp::nextAiToken() === 1
            ? AdminStepUp::verifyAiFirst($token)
            : AdminStepUp::verifyAiSecond($token));
    unset($token, $body, $raw);
    if (!$valid) securityJson(401, ['success'=>false,'message'=>'Token inválido.']);
    $unlocked = $scope === 'site' || AdminStepUp::allowed('ai');
    securityJson(200, [
        'success'=>true,'unlocked'=>$unlocked,
        'next_token'=>$unlocked ? null : 2,
    ]);
} catch (JsonException | InvalidArgumentException $exception) {
    securityJson(400, ['success'=>false,'message'=>'Solicitação inválida.']);
} catch (DomainException $exception) {
    securityJson(401, ['success'=>false,'message'=>'Acesso não autorizado.']);
} catch (RuntimeException $exception) {
    error_log('ADMIN stepup unavailable: ' . get_class($exception));
    securityJson(429, ['success'=>false,'message'=>'Verificação temporariamente indisponível.']);
} catch (Throwable $exception) {
    error_log('ADMIN stepup error: ' . get_class($exception));
    securityJson(503, ['success'=>false,'message'=>'Não foi possível verificar.']);
}
