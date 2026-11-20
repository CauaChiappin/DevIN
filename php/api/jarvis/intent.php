<?php
declare(strict_types=1);

/** Classificação semântica de consultas ADM: nenhuma operação de escrita. */
require_once __DIR__ . '/../../controllers/Jarvis/Api.php';
require_once __DIR__ . '/../../controllers/Jarvis/JarvisSemanticIntent.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    jarvisJson(405, ['success' => false, 'message' => 'Método não permitido.']);
}
$adminId = jarvisAdmin();
jarvisCsrf();
jarvisLimit($adminId, 'semantic-intent', 18);
$body = jarvisBody(2400);
$message = $body['message'] ?? null;
if (!is_string($message) || mb_strlen(trim($message), 'UTF-8') < 8
    || mb_strlen($message, 'UTF-8') > 500) {
    jarvisJson(400, ['success' => false, 'message' => 'Descreva a consulta em até 500 caracteres.']);
}
session_write_close();

if (!ModelProvider::configured()) {
    jarvisJson(200, ['success' => true, 'intent' => null, 'status' => 'unavailable']);
}
try {
    $intent = JarvisSemanticIntent::resolve($message);
    jarvisJson(200, ['success' => true, 'intent' => $intent,
        'status' => $intent === null ? 'unclassified' : 'suggested']);
} catch (JarvisModelException $exception) {
    error_log('JARVIS semantic intent: model reason=' . $exception->reason
        . ' status=' . $exception->httpStatus);
    jarvisJson(200, ['success' => true, 'intent' => null, 'status' => 'unavailable']);
} catch (Throwable $exception) {
    error_log('JARVIS semantic intent: ' . get_class($exception));
    jarvisJson(200, ['success' => true, 'intent' => null, 'status' => 'unavailable']);
}
