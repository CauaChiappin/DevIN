<?php

declare(strict_types=1);

// 1. Carrega automaticamente as variáveis do arquivo .env na raiz do projeto
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (str_contains($line, '=')) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }
    }
}

// 2. Configurações de Ambiente e JWT
$environment = (string) (getenv('DEVIN_APP_ENV') ?: 'development');
$jwtSecret = (string) (getenv('DEVIN_JWT_SECRET') ?: '');

if ($jwtSecret === '') {
    if ($environment !== 'development') {
        throw new RuntimeException('Configure DEVIN_JWT_SECRET antes de iniciar a aplicação.');
    }

    $jwtSecret = hash('sha256', __DIR__ . '|devin-local-secret');
}

$appBaseUrl = rtrim(
    (string) (getenv('DEVIN_APP_BASE_URL') ?: 'http://localhost:8080/DevIN'),
    '/'
);

// 3. Definição das constantes globais
define('JWT_SECRET', $jwtSecret);
define('JWT_ISSUER', 'DevIN');
define('JWT_EXPIRATION_SECONDS', 3600);
define('JWT_COOKIE_NAME', 'devin_token');
define('APP_BASE_URL', $appBaseUrl);
define('APP_ENV', $environment);