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

$extension = strtolower(pathinfo((string) ($document['arquivo_nome'] ?? ''), PATHINFO_EXTENSION));
if ($extension === 'pdf') {
    $content = file_get_contents($path);
    if (!is_string($content) || !str_starts_with($content, '%PDF-')) {
        http_response_code(422);
        exit('O arquivo PDF não pôde ser exibido.');
    }
    header('Content-Type: application/pdf');
    header('Content-Length: ' . (string) filesize($path));
    header('Content-Disposition: inline; filename="curriculo.pdf"');
    header('X-Content-Type-Options: nosniff');
    header('Cache-Control: private, no-store');
    readfile($path);
    exit;
}

$paragraphs = [];
if ($extension === 'docx' && class_exists(ZipArchive::class) && class_exists(DOMDocument::class)) {
    $archive = new ZipArchive();
    if ($archive->open($path) === true) {
        $documentIndex = $archive->locateName('word/document.xml');
        $documentStats = $documentIndex === false ? false : $archive->statIndex($documentIndex);
        $xmlContent = is_array($documentStats) && (int) ($documentStats['size'] ?? 0) <= 5 * 1024 * 1024
            ? $archive->getFromName('word/document.xml')
            : false;
        $archive->close();
        if (is_string($xmlContent) && strlen($xmlContent) <= 5 * 1024 * 1024) {
            $xml = new DOMDocument();
            $previousLibxmlState = libxml_use_internal_errors(true);
            $loaded = $xml->loadXML($xmlContent, LIBXML_NONET | LIBXML_NOBLANKS);
            libxml_clear_errors();
            libxml_use_internal_errors($previousLibxmlState);
            if ($loaded) {
                $xpath = new DOMXPath($xml);
                $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
                foreach ($xpath->query('//w:p') ?: [] as $paragraph) {
                    $parts = [];
                    foreach ($xpath->query('.//w:t', $paragraph) ?: [] as $textNode) {
                        $parts[] = $textNode->textContent;
                    }
                    $text = trim(implode('', $parts));
                    if ($text !== '') {
                        $paragraphs[] = $text;
                    }
                }
            }
        }
    }
}

header('Content-Type: text/html; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prévia do currículo enviado</title>
    <style>
        body { max-width: 820px; margin: 0 auto; padding: 32px 22px; color: #24394b; font: 16px/1.65 Georgia, serif; }
        h1 { padding-bottom: 14px; border-bottom: 2px solid #187b72; overflow-wrap: anywhere; }
        .resume-preview-text { white-space: pre-wrap; }
        .resume-preview-note { padding: 14px; background: #edf6f4; font-family: Arial, sans-serif; }
    </style>
</head>
<body>
    <h1><?= htmlspecialchars((string) ($document['arquivo_nome'] ?? 'Currículo'), ENT_QUOTES, 'UTF-8') ?></h1>
    <?php if ($extension === 'docx' && $paragraphs !== []): ?>
        <?php foreach ($paragraphs as $paragraph): ?>
            <p class="resume-preview-text"><?= htmlspecialchars($paragraph, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="resume-preview-note">A prévia deste formato não está disponível no navegador. <a href="curriculo_baixar.php">Baixar arquivo original</a>.</p>
    <?php endif; ?>
</body>
</html>