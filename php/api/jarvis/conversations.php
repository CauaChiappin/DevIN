<?php
declare(strict_types=1);

require_once __DIR__ . '/../../controllers/Jarvis/Api.php';
require_once __DIR__ . '/../../controllers/Jarvis/ConversationRepository.php';

$adminId = jarvisAdmin();
$method = $_SERVER['REQUEST_METHOD'] ?? '';
if ($method === 'POST') {
    jarvisCsrf();
    jarvisLimit($adminId, 'conversations-write', 60);
    $body = jarvisBody();
}
if (!in_array($method, ['GET', 'POST'], true)) {
    header('Allow: GET, POST');
    jarvisJson(405, ['success' => false, 'message' => 'Método não permitido.']);
}
session_write_close();
try {
    $db = getDatabaseConnection();
    try {
        if ($method === 'GET') {
            $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
            if ($id !== null && $id !== false && $id > 0) {
                $messages = ConversationRepository::latest($db, $adminId, $id);
                jarvisJson(200, ['success' => true, 'conversation_id' => $id, 'messages' => $messages]);
            }
            $search = is_string($_GET['q'] ?? null) ? $_GET['q'] : '';
            $list = ConversationRepository::list($db, $adminId, $search);
            jarvisJson(200, ['success' => true, 'conversations' => $list]);
        }
        $action = $body['action'] ?? null;
        if ($action === 'create') {
            $id = ConversationRepository::create($db, $adminId);
            jarvisJson(200, ['success' => true, 'conversation_id' => $id]);
        }
        if ($action === 'delete') {
            $id = filter_var($body['conversation_id'] ?? null, FILTER_VALIDATE_INT);
            if (!$id || $id < 1 || !ConversationRepository::delete($db, $adminId, $id)) {
                jarvisJson(404, ['success' => false, 'message' => 'Conversa não encontrada.']);
            }
            jarvisJson(200, ['success' => true]);
        }
        jarvisJson(400, ['success' => false, 'message' => 'Ação desconhecida.']);
    } finally {
        $db->close();
    }
} catch (InvalidArgumentException $exception) {
    jarvisJson(404, ['success' => false, 'message' => 'Conversa não encontrada.']);
} catch (Throwable $exception) {
    error_log('JARVIS conversations: ' . $exception->getMessage());
    jarvisJson(503, ['success' => false, 'message' => 'Histórico indisponível. A migração SQL já foi aplicada?']);
}
