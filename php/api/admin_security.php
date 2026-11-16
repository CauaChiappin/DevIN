<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

require_once __DIR__ . '/../controllers/Security/AdminStepUp.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

header('Content-Type: application/json; charset=utf-8'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
header('Cache-Control: no-store, private'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
header('Referrer-Policy: no-referrer'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
header('X-Content-Type-Options: nosniff'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.

function securityJson(int $status, array $data): never // Explicação DevIN: Declara função/método securityJson, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    http_response_code($status); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
    exit; // Explicação DevIN: Continua expressão ou bloco pertencente a função securityJson.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

$method = $_SERVER['REQUEST_METHOD'] ?? ''; // Explicação DevIN: Atribui um valor à variável PHP $method.
if (!in_array($method, ['GET','POST'], true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    header('Allow: GET, POST'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    securityJson(405, ['success'=>false,'message'=>'Operação indisponível.']); // Explicação DevIN: Continua expressão ou bloco pertencente a função securityJson.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    $admin = AdminStepUp::adminId(); // Explicação DevIN: Atribui um valor à variável PHP $admin.
    if (!AdminStepUp::enabled()) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        securityJson(503, ['success'=>false,'message'=>'Verificação indisponível.']); // Explicação DevIN: Continua expressão ou bloco pertencente a função securityJson.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if ($method === 'GET') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $scope = $_GET['scope'] ?? ''; // Explicação DevIN: Atribui um valor à variável PHP $scope.
        if (!is_string($scope) || !in_array($scope, ['site','ai'], true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            securityJson(400, ['success'=>false,'message'=>'Operação inválida.']); // Explicação DevIN: Continua expressão ou bloco pertencente a função securityJson.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $ready = AdminStepUp::allowed($scope); // Explicação DevIN: Atribui um valor à variável PHP $ready.
        securityJson(200, [ // Explicação DevIN: Continua expressão ou bloco pertencente a função securityJson.
            'success'=>true, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'unlocked'=>$ready, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'next_token'=>$ready ? null : ($scope === 'ai' ? AdminStepUp::nextAiToken() : 2), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ]); // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if (!verifyCsrfToken($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        securityJson(403, ['success'=>false,'message'=>'Solicitação inválida.']); // Explicação DevIN: Continua expressão ou bloco pertencente a função securityJson.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 205000) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        securityJson(413, ['success'=>false,'message'=>'Solicitação inválida.']); // Explicação DevIN: Continua expressão ou bloco pertencente a função securityJson.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    $raw = file_get_contents('php://input', false, null, 0, 205001); // Explicação DevIN: Atribui um valor à variável PHP $raw.
    if (!is_string($raw) || strlen($raw) > 205000) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        securityJson(413, ['success'=>false,'message'=>'Solicitação inválida.']); // Explicação DevIN: Continua expressão ou bloco pertencente a função securityJson.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    $body = json_decode($raw, true, 6, JSON_THROW_ON_ERROR); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
    if (!is_array($body) || !in_array($body['scope'] ?? null, ['site','ai'], true) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        || !is_string($body['token'] ?? null)) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        securityJson(400, ['success'=>false,'message'=>'Solicitação inválida.']); // Explicação DevIN: Continua expressão ou bloco pertencente a função securityJson.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    $scope = $body['scope']; // Explicação DevIN: Atribui um valor à variável PHP $scope.
    if (AdminStepUp::allowed($scope)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        securityJson(200, ['success'=>true,'unlocked'=>true,'next_token'=>null]); // Explicação DevIN: Continua expressão ou bloco pertencente a função securityJson.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    $token = $body['token']; // Explicação DevIN: Atribui um valor à variável PHP $token.
    $valid = $scope === 'site' // Explicação DevIN: Atribui um valor à variável PHP $valid.
        ? AdminStepUp::verifySite($token) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        : (AdminStepUp::nextAiToken() === 1 // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            ? AdminStepUp::verifyAiFirst($token) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            : AdminStepUp::verifyAiSecond($token)); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    unset($token, $body, $raw); // Explicação DevIN: Continua expressão ou bloco pertencente a função securityJson.
    if (!$valid) securityJson(401, ['success'=>false,'message'=>'Token inválido.']); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    $unlocked = $scope === 'site' || AdminStepUp::allowed('ai'); // Explicação DevIN: Atribui um valor à variável PHP $unlocked.
    securityJson(200, [ // Explicação DevIN: Continua expressão ou bloco pertencente a função securityJson.
        'success'=>true,'unlocked'=>$unlocked, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'next_token'=>$unlocked ? null : 2, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ]); // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
} catch (JsonException | InvalidArgumentException $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    securityJson(400, ['success'=>false,'message'=>'Solicitação inválida.']); // Explicação DevIN: Continua expressão ou bloco pertencente a função securityJson.
} catch (DomainException $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    securityJson(401, ['success'=>false,'message'=>'Acesso não autorizado.']); // Explicação DevIN: Continua expressão ou bloco pertencente a função securityJson.
} catch (RuntimeException $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    error_log('ADMIN stepup unavailable: ' . get_class($exception)); // Explicação DevIN: Continua expressão ou bloco pertencente a função securityJson.
    securityJson(429, ['success'=>false,'message'=>'Verificação temporariamente indisponível.']); // Explicação DevIN: Continua expressão ou bloco pertencente a função securityJson.
} catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    error_log('ADMIN stepup error: ' . get_class($exception)); // Explicação DevIN: Continua expressão ou bloco pertencente a função securityJson.
    securityJson(503, ['success'=>false,'message'=>'Não foi possível verificar.']); // Explicação DevIN: Continua expressão ou bloco pertencente a função securityJson.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
