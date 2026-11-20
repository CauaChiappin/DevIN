<?php
declare(strict_types=1);

/**
 * Rascunho de suporte JARVIS sob solicitação explícita de um ADM.
 * Endpoint privado: não recebe o texto do chamado do navegador, nunca envia
 * mensagem ao usuário e não grava resposta nem modifica o ticket.
 */
require_once __DIR__ . '/../../controllers/Jarvis/Api.php';
require_once __DIR__ . '/../../controllers/Jarvis/ModelProvider.php';
require_once __DIR__ . '/../../controllers/Jarvis/SupportAiAssistant.php';
require_once __DIR__ . '/../../controllers/SupportDesk.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    jarvisJson(405, ['success' => false, 'message' => 'Método não permitido.']);
}
$adminId = jarvisAdmin();
jarvisCsrf();
$payload = jarvisBody(2048);
if (($payload['action'] ?? '') !== 'draft' || ($payload['consent'] ?? null) !== true) {
    jarvisJson(400, ['success' => false, 'message' => 'Confirme o envio do contexto anonimizado à IA.']);
}
$ticketId = filter_var($payload['ticket_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($ticketId === false || $ticketId === null) {
    jarvisJson(400, ['success' => false, 'message' => 'Selecione um chamado válido.']);
}
if (!ModelProvider::configured()) {
    jarvisJson(503, ['success' => false, 'message' => 'JARVIS não configurado no servidor. Configure o provedor de IA.']);
}
jarvisLimit($adminId, 'support-draft', 10);

// Encerra a sessão antes da chamada remota para não bloquear outras abas do ADM.
session_write_close();

try {
    $db = getDatabaseConnection();
    try {
        $detail = SupportDesk::detail($db, (int) $ticketId, ['tipo' => 'adm', 'id' => $adminId]);
    } finally {
        $db->close();
    }
    if (($detail['ticket']['status'] ?? '') === 'fechado') {
        jarvisJson(409, ['success' => false, 'message' => 'Esse chamado está encerrado. Reabra o atendimento antes de pedir um rascunho.']);
    }
} catch (DomainException | InvalidArgumentException $exception) {
    jarvisJson(404, ['success' => false, 'message' => 'Chamado não encontrado ou indisponível.']);
} catch (Throwable $exception) {
    error_log('JARVIS suporte: falha na leitura autorizada (' . get_class($exception) . ').');
    jarvisJson(503, ['success' => false, 'message' => 'Não foi possível consultar o chamado.']);
}

try {
    $response = ModelProvider::chatWithMeta(SupportAiAssistant::messages($detail));
    $draft = mb_substr(trim((string) $response['reply']), 0, 2500, 'UTF-8');
    if ($draft === '') {
        jarvisJson(503, ['success' => false, 'message' => 'O JARVIS não retornou uma sugestão utilizável.']);
    }
    error_log('JARVIS suporte: sugestão solicitada pelo ADM ' . $adminId . ' para chamado ' . (int) $ticketId . '.');
    jarvisJson(200, [
        'success' => true,
        'draft' => $draft,
        'ticket_id' => (int) $ticketId,
        'model' => (string) ($response['model'] ?? 'desconhecido'),
        'provider' => (string) ($response['provider'] ?? 'desconhecido'),
        'requires_approval' => true,
    ]);
} catch (JarvisModelException $exception) {
    jarvisJson($exception->reason === 'rate_limit' ? 429 : 503, [
        'success' => false,
        'message' => $exception->getMessage(),
        'error_code' => $exception->reason,
    ]);
} catch (Throwable $exception) {
    error_log('JARVIS suporte: provedor falhou (' . get_class($exception) . ').');
    jarvisJson(503, ['success' => false, 'message' => 'A IA está indisponível no momento. Tente novamente.']);
}
