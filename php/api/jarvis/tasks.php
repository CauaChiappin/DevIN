<?php
declare(strict_types=1);

require_once __DIR__ . '/../../controllers/Jarvis/Api.php';
require_once __DIR__ . '/../../controllers/Jarvis/AdminTaskQueue.php';

$adminId = jarvisAdmin();
$method = $_SERVER['REQUEST_METHOD'] ?? '';
if ($method === 'POST') {
    jarvisCsrf();
    jarvisLimit($adminId, 'tasks-write', 60);
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
            jarvisJson(200, ['success' => true, 'tasks' => AdminTaskQueue::list($db, $adminId)]);
        }
        $action = $body['action'] ?? null;
        if ($action === 'create') {
            $title = $body['title'] ?? null;
            $description = $body['description'] ?? '';
            if (!is_string($title) || !is_string($description)) {
                jarvisJson(400, ['success' => false, 'message' => 'Informe texto válido.']);
            }
            $id = AdminTaskQueue::create($db, $adminId, $title, $description);
            jarvisJson(200, ['success' => true, 'task_id' => $id]);
        }
        if (in_array($action, ['start', 'complete', 'cancel'], true)) {
            $taskId = filter_var($body['task_id'] ?? null, FILTER_VALIDATE_INT);
            if (!$taskId || $taskId < 1) {
                jarvisJson(400, ['success' => false, 'message' => 'ID de atividade inválido.']);
            }
            // Somente atualiza o andamento na fila; não remove vagas, perfis nem outros dados DevIN.
            $state = AdminTaskQueue::change($db, $adminId, $taskId, $action);
            jarvisJson(200, ['success' => true, 'status' => $state]);
        }
        jarvisJson(400, ['success' => false, 'message' => 'Ação não permitida.']);
    } finally {
        $db->close();
    }
} catch (InvalidArgumentException $exception) {
    jarvisJson(409, ['success' => false, 'message' => $exception->getMessage()]);
} catch (Throwable $exception) {
    error_log('JARVIS tasks: ' . $exception->getMessage());
    jarvisJson(503, ['success' => false, 'message' => 'Fila indisponível. A migração SQL já foi aplicada?']);
}
