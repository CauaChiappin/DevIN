<?php

require_once __DIR__ . '/../controllers/AuthController.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Metodo nao permitido.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!is_array($input)) {
    $input = $_POST;
}

$email = $input['email'] ?? '';
$senha = $input['senha'] ?? '';

try {
    $auth = AuthController::login($email, $senha);

    setcookie(JWT_COOKIE_NAME, $auth['token'], [
        'expires' => time() + JWT_EXPIRATION_SECONDS,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    echo json_encode($auth);
} catch (Throwable $exception) {
    http_response_code(401);
    echo json_encode(['erro' => $exception->getMessage()]);
}
