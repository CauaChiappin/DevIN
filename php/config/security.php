<?php

declare(strict_types=1);

/**
 * Configuração central de sessão, CSRF e respostas HTTP.
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

    $isHttps = !empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off';

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

function applySecurityHeaders(): void
{
    if (headers_sent()) {
        return;
    }

    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
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
        && hash_equals((string) $_SESSION['csrf_token'], $token);
}

function isAjaxRequest(): bool
{
    $requestedWith = strtolower(requestString($_SERVER, 'HTTP_X_REQUESTED_WITH'));
    $accept = strtolower(requestString($_SERVER, 'HTTP_ACCEPT'));

    return $requestedWith === 'xmlhttprequest'
        || str_contains($accept, 'application/json');
}

function jsonResponse(array $payload, int $status = 200): never
{
    if (!headers_sent()) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, max-age=0');
    }

    echo json_encode(
        $payload,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    exit;
}

function requireValidCsrf(): void
{
    $token = $_POST['csrf_token'] ?? null;

    if (!is_string($token) || !verifyCsrfToken($token)) {
        if (isAjaxRequest()) {
            jsonResponse([
                'success' => false,
                'error' => 'csrf_invalido',
                'message' => 'Sua sessão expirou. Atualize a página e tente novamente.',
            ], 403);
        }

        http_response_code(403);
        exit('Solicitação inválida. Atualize a página e tente novamente.');
    }
}

function secureSessionRegenerate(): void
{
    startSecureSession();
    session_regenerate_id(true);
}

applySecurityHeaders();
