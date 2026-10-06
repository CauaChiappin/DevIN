<?php

declare(strict_types=1);

// Carrega o .env da raiz apenas quando ele existe; variáveis do sistema têm prioridade.
$projectRoot = dirname(__DIR__, 2);
require_once $projectRoot . '/vendor/autoload.php';
if (is_file($projectRoot . '/.env')) {
    Dotenv\Dotenv::createImmutable($projectRoot)->safeLoad();
}

$environmentValue = static function (string $name, ?string $legacyName = null): ?string {
    $value = $_ENV[$name] ?? $_SERVER[$name] ?? getenv($name);
    if (($value === false || $value === null || $value === '') && $legacyName !== null) {
        $value = $_ENV[$legacyName] ?? $_SERVER[$legacyName] ?? getenv($legacyName);
    }

    return $value === false || $value === null || $value === '' ? null : (string) $value;
};

// 2. Configurações de Ambiente e JWT
$environment = $environmentValue('APP_ENV', 'DEVIN_APP_ENV') ?? 'development';
ini_set('display_errors', '0');
ini_set('log_errors', '1');
$jwtSecret = $environmentValue('DEVIN_JWT_SECRET') ?? '';

if ($jwtSecret === '') {
    if ($environment !== 'development') {
        throw new RuntimeException('Configure DEVIN_JWT_SECRET antes de iniciar a aplicação.');
    }

    $jwtSecret = hash('sha256', __DIR__ . '|devin-local-secret');
}

$appBaseUrl = rtrim(
    $environmentValue('APP_URL', 'DEVIN_APP_BASE_URL') ?? 'http://localhost:8080/DevIN',
    '/'
);

// 3. Definição das constantes globais
define('JWT_SECRET', $jwtSecret);
define('JWT_ISSUER', 'DevIN');
define('JWT_EXPIRATION_SECONDS', 3600);
define('JWT_COOKIE_NAME', 'devin_token');
define('APP_BASE_URL', $appBaseUrl);
define('APP_ENV', $environment);
