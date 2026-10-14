<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

function isSecureRequest(): bool
{
    // Considera proxy reverso para que cookies Secure funcionem quando HTTPS termina antes do PHP.
    return APP_ENV !== 'development'
        || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
}

/**
 * Inicializa a sessão com cookies protegidos.
 */
function startSecureSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    // Salva sessões em diretório próprio, fora das pastas públicas do site.
    $sessionPath = dirname(__DIR__, 2) . '/storage/sessions';
    if (!is_dir($sessionPath)) {
        @mkdir($sessionPath, 0700, true);
    }

    if (is_dir($sessionPath) && is_writable($sessionPath)) {
        session_save_path($sessionPath);
    }

    /* Restringe a sessão a cookies e ativa atributos de proteção contra roubo e envio cross-site.
     * O early return acima evita tentar redefinir parâmetros depois que a sessão já começou. */
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => isSecureRequest(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

function requestString(array $source, string $key): string
{
    $value = $source[$key] ?? '';
    return is_string($value) ? trim($value) : '';
}

function csrfToken(): string
{
    startSecureSession();

    if (empty($_SESSION['csrf_token'])) {
        // Gera token imprevisível e o mantém na sessão para comparar no envio do formulário.
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(?string $token): bool
{
    startSecureSession();

    // hash_equals compara em tempo constante para não revelar o token por diferenças de tempo.
    return is_string($token)
        && $token !== ''
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function requireValidCsrf(): void
{
    // Tenta obter o token do POST ou dos cabeçalhos HTTP (para solicitações AJAX)
    $token = $_POST['csrf_token'] 
        ?? $_SERVER['HTTP_X_CSRF_TOKEN'] 
        ?? $_SERVER['HTTP_X_XSRF_TOKEN'] 
        ?? null;

    if (!is_string($token) || !verifyCsrfToken($token)) {
        http_response_code(403);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Solicitação inválida ou token CSRF expirado. Atualize a página e tente novamente.'
        ]);
        exit;
    }
}

function secureSessionRegenerate(): void
{
    startSecureSession();
    session_regenerate_id(true);
}
