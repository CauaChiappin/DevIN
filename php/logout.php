<?php

declare(strict_types=1);

require_once __DIR__ . '/config/security.php';
startSecureSession();

require_once __DIR__ . '/config/auth.php';

// 1. Limpa todas as variáveis de sessão
$_SESSION = [];

// 2. Invalida o cookie de sessão do navegador
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        [
            'expires'  => time() - 42000,
            'path'     => $params['path'] ?: '/',
            'domain'   => $params['domain'] ?? '',
            'secure'   => (bool) $params['secure'],
            'httponly' => (bool) $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Lax',
        ]
    );
}

// 3. Destrói a sessão no servidor
session_destroy();

// 4. Invalida o cookie JWT (utiliza detecção completa de HTTPS)
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';

setcookie(
    JWT_COOKIE_NAME,
    '',
    [
        'expires'  => time() - 3600,
        'path'     => '/',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]
);

// 5. Redireciona para a página de login
$loginUrl = defined('APP_BASE_URL') ? APP_BASE_URL . '/php/login.php' : 'login.php';
header('Location: ' . $loginUrl);
exit;