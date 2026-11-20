<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

/**
 * Rascunho de suporte JARVIS sob solicitação explícita de um ADM.
 * Endpoint privado: não recebe o texto do chamado do navegador, nunca envia
 * mensagem ao usuário e não grava resposta nem modifica o ticket.
 */
require_once __DIR__ . '/../../controllers/Jarvis/Api.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/../../controllers/Jarvis/ModelProvider.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/../../controllers/Jarvis/SupportAiAssistant.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/../../controllers/SupportDesk.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    header('Allow: POST'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    jarvisJson(405, ['success' => false, 'message' => 'Método não permitido.']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$adminId = jarvisAdmin(); // Explicação DevIN: Atribui um valor à variável PHP $adminId.
jarvisCsrf(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
$payload = jarvisBody(2048); // Explicação DevIN: Atribui um valor à variável PHP $payload.
if (($payload['action'] ?? '') !== 'draft' || ($payload['consent'] ?? null) !== true) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    jarvisJson(400, ['success' => false, 'message' => 'Confirme o envio do contexto anonimizado à IA.']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$ticketId = filter_var($payload['ticket_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]); // Explicação DevIN: Atribui um valor à variável PHP $ticketId.
if ($ticketId === false || $ticketId === null) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    jarvisJson(400, ['success' => false, 'message' => 'Selecione um chamado válido.']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
if (!ModelProvider::configured()) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    jarvisJson(503, ['success' => false, 'message' => 'JARVIS não configurado no servidor. Configure o provedor de IA.']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
jarvisLimit($adminId, 'support-draft', 10); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

// Encerra a sessão antes da chamada remota para não bloquear outras abas do ADM.
session_write_close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    $db = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $db.
    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        $detail = SupportDesk::detail($db, (int) $ticketId, ['tipo' => 'adm', 'id' => $adminId]); // Explicação DevIN: Atribui um valor à variável PHP $detail.
    } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
        $db->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if (($detail['ticket']['status'] ?? '') === 'fechado') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        jarvisJson(409, ['success' => false, 'message' => 'Esse chamado está encerrado. Reabra o atendimento antes de pedir um rascunho.']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} catch (DomainException | InvalidArgumentException $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    jarvisJson(404, ['success' => false, 'message' => 'Chamado não encontrado ou indisponível.']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    error_log('JARVIS suporte: falha na leitura autorizada (' . get_class($exception) . ').'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    jarvisJson(503, ['success' => false, 'message' => 'Não foi possível consultar o chamado.']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    $response = ModelProvider::chatWithMeta(SupportAiAssistant::messages($detail)); // Explicação DevIN: Atribui um valor à variável PHP $response.
    $draft = mb_substr(trim((string) $response['reply']), 0, 2500, 'UTF-8'); // Explicação DevIN: Atribui um valor à variável PHP $draft.
    if ($draft === '') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        jarvisJson(503, ['success' => false, 'message' => 'O JARVIS não retornou uma sugestão utilizável.']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    error_log('JARVIS suporte: sugestão solicitada pelo ADM ' . $adminId . ' para chamado ' . (int) $ticketId . '.'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    jarvisJson(200, [ // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        'success' => true, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'draft' => $draft, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'ticket_id' => (int) $ticketId, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'model' => (string) ($response['model'] ?? 'desconhecido'), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'provider' => (string) ($response['provider'] ?? 'desconhecido'), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'requires_approval' => true, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ]); // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
} catch (JarvisModelException $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    jarvisJson($exception->reason === 'rate_limit' ? 429 : 503, [ // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        'success' => false, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'message' => $exception->getMessage(), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'error_code' => $exception->reason, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ]); // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
} catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    error_log('JARVIS suporte: provedor falhou (' . get_class($exception) . ').'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    jarvisJson(503, ['success' => false, 'message' => 'A IA está indisponível no momento. Tente novamente.']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
