<?php

declare(strict_types=1);

require_once __DIR__ . '/config/security.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

try {
    startSecureSession();

    echo json_encode(
        ['sucesso' => true, 'token' => csrfToken()],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(
        ['sucesso' => false, 'mensagem' => 'Não foi possível gerar o token CSRF.'],
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
}