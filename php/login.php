<html src="/html/login.html"></html>
<?php
require_once __DIR__ . '/controllers/AuthController.php';

session_start();

$erro = $_GET['erro'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $auth = AuthController::login($_POST['email'] ?? '', $_POST['senha'] ?? '');

        $_SESSION['usuario_id'] = $auth['usuario']['id'];
        $_SESSION['usuario_nome'] = $auth['usuario']['nome'];
        $_SESSION['usuario_email'] = $auth['usuario']['email'];
        $_SESSION['usuario_tipo'] = $auth['usuario']['tipo'];
        $_SESSION['jwt'] = $auth['token'];
        $_SESSION['logado'] = true;

        setcookie(JWT_COOKIE_NAME, $auth['token'], [
            'expires' => time() + JWT_EXPIRATION_SECONDS,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        header('Location: ' . AuthController::redirectByUserType($auth['usuario']['tipo']));
        exit;
    } catch (Throwable $exception) {
        $erro = $exception->getMessage();
    }
}
?>
