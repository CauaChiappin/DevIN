<?php

declare(strict_types=1);

require_once __DIR__ . '/middlewares/auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/controllers/ResumeDocument.php';

$usuario = requireWebAuth('pessoa');
$connection = getDatabaseConnection();
try {
    $document = ResumeDocument::find($connection, (int) $usuario['id']);
} finally {
    $connection->close();
}

$path = ResumeDocument::absolutePath((string) ($document['arquivo_caminho'] ?? ''));
if ($path === null || !is_readable($path)) {
    http_response_code(404);
    exit('Arquivo não encontrado.');
}

$fileName = (string) ($document['arquivo_nome'] ?? 'curriculo');
$fallbackName = preg_replace('/[^A-Za-z0-9._-]/', '_', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $fileName) ?: 'curriculo');
header('Content-Type: ' . (string) ($document['mime_type'] ?? 'application/octet-stream'));
header('Content-Length: ' . (string) filesize($path));
header('Content-Disposition: attachment; filename="' . $fallbackName . '"; filename*=UTF-8\'\'' . rawurlencode($fileName));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');
readfile($path);