<?php

declare(strict_types=1);

require_once __DIR__ . '/config/security.php';

startSecureSession();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');

echo json_encode(
    ['token' => csrfToken()],
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
);
