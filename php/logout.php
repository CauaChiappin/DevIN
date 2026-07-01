<?php
session_start();

require_once __DIR__ . '/config/auth.php';

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}

session_destroy();

setcookie(JWT_COOKIE_NAME, '', time() - 3600, '/');

header('Location: login.php');
exit;
