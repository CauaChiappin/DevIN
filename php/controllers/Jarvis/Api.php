<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

require_once __DIR__ . '/../../middlewares/auth.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/../../config/RateLimiter.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/../Security/AdminStepUp.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

function jarvisJson(int $status, array $data): never // Explicação DevIN: Declara função/método jarvisJson, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    http_response_code($status); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    header('Content-Type: application/json; charset=utf-8'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    header('Cache-Control: no-store, private'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    header('X-Content-Type-Options: nosniff'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
    exit; // Explicação DevIN: Continua expressão ou bloco pertencente a função jarvisJson.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

/** JSON 401/403, sem redirecionar o fetch do widget à página de login. */
function jarvisAdmin(): int // Explicação DevIN: Declara função/método jarvisAdmin, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    startSecureSession(); // Explicação DevIN: Continua expressão ou bloco pertencente a função jarvisAdmin.
    $now = time(); // Explicação DevIN: Atribui um valor à variável PHP $now.
    $last = (int) ($_SESSION['last_activity'] ?? 0); // Explicação DevIN: Atribui um valor à variável PHP $last.
    $authTime = (int) ($_SESSION['auth_time'] ?? 0); // Explicação DevIN: Atribui um valor à variável PHP $authTime.
    $id = (int) ($_SESSION['usuario_id'] ?? 0); // Explicação DevIN: Atribui um valor à variável PHP $id.
    if (empty($_SESSION['logado']) || $last <= 0 || $authTime <= 0 // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        || $now - $last > 1800 || $now - $authTime > 43200) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        jarvisJson(401, ['success' => false, 'message' => 'Sessão expirada. Faça login novamente.']); // Explicação DevIN: Continua expressão ou bloco pertencente a função jarvisAdmin.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if (($_SESSION['usuario_tipo'] ?? null) !== 'adm' || $id <= 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        jarvisJson(403, ['success' => false, 'message' => 'Acesso exclusivo para administradores.']); // Explicação DevIN: Continua expressão ou bloco pertencente a função jarvisAdmin.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    // Verifica existência da conta ADM para impedir o uso de uma sessão de conta removida.
    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        $db = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $db.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            $stmt = $db->prepare('SELECT id_administrador FROM administrador WHERE id_administrador = ? LIMIT 1'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->bind_param('i', $id); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $valid = $stmt->get_result()->fetch_row() !== null; // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função jarvisAdmin.
        } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
            $db->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função jarvisAdmin.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        error_log('JARVIS admin verification: ' . $exception->getMessage()); // Explicação DevIN: Continua expressão ou bloco pertencente a função jarvisAdmin.
        jarvisJson(503, ['success' => false, 'message' => 'Não foi possível validar a conta administrativa.']); // Explicação DevIN: Continua expressão ou bloco pertencente a função jarvisAdmin.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if (!$valid) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        jarvisJson(403, ['success' => false, 'message' => 'Conta administrativa não encontrada.']); // Explicação DevIN: Continua expressão ou bloco pertencente a função jarvisAdmin.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    // Segundo fator obrigatório somente após provisionamento e ativação.
    // Verificação feita por solicitação no servidor, nunca apenas na interface.
    if (AdminStepUp::enabled()) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            if (!AdminStepUp::allowed('ai')) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                jarvisJson(403, [ // Explicação DevIN: Continua expressão ou bloco pertencente a função jarvisAdmin.
                    'success' => false, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    'message' => 'Verificação adicional necessária para o JARVIS. Abra Desbloquear IA no painel.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    'error_code' => 'stepup_required', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                ]); // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            error_log('JARVIS step-up check: ' . get_class($exception)); // Explicação DevIN: Continua expressão ou bloco pertencente a função jarvisAdmin.
            jarvisJson(503, ['success' => false, 'message' => 'Verificação adicional temporariamente indisponível.']); // Explicação DevIN: Continua expressão ou bloco pertencente a função jarvisAdmin.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    $_SESSION['last_activity'] = $now; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    // A sessão ADM, a existência da conta e os limites de uso já foram validados.
    return $id; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

function jarvisCsrf(): void // Explicação DevIN: Declara função/método jarvisCsrf, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if (!verifyCsrfToken($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        jarvisJson(403, ['success' => false, 'message' => 'Token de segurança inválido. Atualize a página.']); // Explicação DevIN: Continua expressão ou bloco pertencente a função jarvisCsrf.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

function jarvisBody(int $maxBytes = 18000): array // Explicação DevIN: Declara função/método jarvisBody, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    $length = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0); // Explicação DevIN: Atribui um valor à variável PHP $length.
    if ($length > $maxBytes) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        jarvisJson(413, ['success' => false, 'message' => 'Solicitação muito grande.']); // Explicação DevIN: Continua expressão ou bloco pertencente a função jarvisBody.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    $raw = file_get_contents('php://input', false, null, 0, $maxBytes + 1); // Explicação DevIN: Atribui um valor à variável PHP $raw.
    if (!is_string($raw) || strlen($raw) > $maxBytes) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        jarvisJson(413, ['success' => false, 'message' => 'Solicitação muito grande.']); // Explicação DevIN: Continua expressão ou bloco pertencente a função jarvisBody.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        $decoded = json_decode($raw, true, 16, JSON_THROW_ON_ERROR); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
    } catch (JsonException $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        jarvisJson(400, ['success' => false, 'message' => 'JSON inválido.']); // Explicação DevIN: Continua expressão ou bloco pertencente a função jarvisBody.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if (!is_array($decoded)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        jarvisJson(400, ['success' => false, 'message' => 'Corpo inválido.']); // Explicação DevIN: Continua expressão ou bloco pertencente a função jarvisBody.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    return $decoded; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

function jarvisLimit(int $adminId, string $scope, int $limit = 30): void // Explicação DevIN: Declara função/método jarvisLimit, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        if (RateLimiter::consume('jarvis:' . $scope . ':' . $adminId, $limit, 3600) > 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            jarvisJson(429, ['success' => false, 'message' => 'Limite de solicitações atingido.']); // Explicação DevIN: Continua expressão ou bloco pertencente a função jarvisLimit.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        error_log('JARVIS rate limiter: ' . $exception->getMessage()); // Explicação DevIN: Continua expressão ou bloco pertencente a função jarvisLimit.
        jarvisJson(503, ['success' => false, 'message' => 'Limite de uso temporariamente indisponível.']); // Explicação DevIN: Continua expressão ou bloco pertencente a função jarvisLimit.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
