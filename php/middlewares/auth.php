<?php

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../auth/Jwt.php';

function getBearerToken(): ?string
{
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $authorization = $headers['Authorization'] ?? $headers['authorization'] ?? '';

    if (preg_match('/Bearer\s+(.+)/', $authorization, $matches)) {
        return trim($matches[1]);
    }

    return $_COOKIE[JWT_COOKIE_NAME] ?? null;
}

function authUser(): array
{
    $token = getBearerToken();

    if (!$token) {
        throw new RuntimeException('Token nao informado.');
    }

    return Jwt::decode($token, JWT_SECRET);
}

function requireAuth(): array // metodo utilizado para proteger rotas, caso o usuario nao esteja autenticado, retorna 401
{
    try {
        return authUser();
    } catch (Throwable $exception) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['erro' => 'Nao autorizado.']);
        exit;
    }
}
