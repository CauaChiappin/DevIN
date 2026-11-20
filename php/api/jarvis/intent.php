<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

/** Classificação semântica de consultas ADM: nenhuma operação de escrita. */
require_once __DIR__ . '/../../controllers/Jarvis/Api.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/../../controllers/Jarvis/JarvisSemanticIntent.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    header('Allow: POST'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    jarvisJson(405, ['success' => false, 'message' => 'Método não permitido.']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$adminId = jarvisAdmin(); // Explicação DevIN: Atribui um valor à variável PHP $adminId.
jarvisCsrf(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
jarvisLimit($adminId, 'semantic-intent', 18); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
$body = jarvisBody(2400); // Explicação DevIN: Atribui um valor à variável PHP $body.
$message = $body['message'] ?? null; // Explicação DevIN: Atribui um valor à variável PHP $message.
if (!is_string($message) || mb_strlen(trim($message), 'UTF-8') < 8 // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    || mb_strlen($message, 'UTF-8') > 500) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    jarvisJson(400, ['success' => false, 'message' => 'Descreva a consulta em até 500 caracteres.']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
session_write_close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

if (!ModelProvider::configured()) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    jarvisJson(200, ['success' => true, 'intent' => null, 'status' => 'unavailable']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    $intent = JarvisSemanticIntent::resolve($message); // Explicação DevIN: Atribui um valor à variável PHP $intent.
    jarvisJson(200, ['success' => true, 'intent' => $intent, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        'status' => $intent === null ? 'unclassified' : 'suggested']); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
} catch (JarvisModelException $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    error_log('JARVIS semantic intent: model reason=' . $exception->reason // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        . ' status=' . $exception->httpStatus); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    jarvisJson(200, ['success' => true, 'intent' => null, 'status' => 'unavailable']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    error_log('JARVIS semantic intent: ' . get_class($exception)); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    jarvisJson(200, ['success' => true, 'intent' => null, 'status' => 'unavailable']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
