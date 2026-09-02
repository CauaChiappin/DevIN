<?php

declare(strict_types=1);

/**
 * Inicializa a sessão com cookies protegidos.
 */
function startSecureSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $sessionPath = dirname(__DIR__, 2) . '/storage/sessions';
    if (!is_dir($sessionPath)) {
        @mkdir($sessionPath, 0700, true);
    }
    if (is_dir($sessionPath) && is_writable($sessionPath)) {
        session_save_path($sessionPath);
    }

    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

function requestString(array $source, string $key): string
{
    $value = $source[$key] ?? '';
    return is_string($value) ? $value : '';
}

function csrfToken(): string
{
    startSecureSession();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(?string $token): bool
{
    startSecureSession();

    return is_string($token)
        && $token !== ''
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function requireValidCsrf(): void
{
    $token = $_POST['csrf_token'] ?? null;
    if (!is_string($token) || !verifyCsrfToken($token)) {
        http_response_code(403);
        exit('Solicitação inválida. Atualize a página e tente novamente.');
    }
}

function secureSessionRegenerate(): void
{
    startSecureSession();
    session_regenerate_id(true);
}
