<?php

declare(strict_types=1);

$environment = (string) (getenv('DEVIN_APP_ENV') ?: 'development');
$jwtSecret = (string) (getenv('DEVIN_JWT_SECRET') ?: '');

if ($jwtSecret === '') {
    if ($environment !== 'development') {
        throw new RuntimeException('Configure DEVIN_JWT_SECRET antes de iniciar a aplicação.');
    }

    $jwtSecret = hash('sha256', __DIR__ . '|devin-local-secret');
}

$appBaseUrl = rtrim(
    (string) (getenv('DEVIN_APP_BASE_URL') ?: 'http://localhost/DevIN'),
    '/'
);

define('JWT_SECRET', $jwtSecret);
define('JWT_ISSUER', 'DevIN');
define('JWT_EXPIRATION_SECONDS', 3600);
define('JWT_COOKIE_NAME', 'devin_token');
define('APP_BASE_URL', $appBaseUrl);
define('APP_ENV', $environment);

