<?php

declare(strict_types=1);

require_once __DIR__ . '/middlewares/auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/controllers/ResumeDocument.php';

$usuario = requireWebAuth('pessoa');
if (requestString($_GET, 'formato') !== 'docx') {
    http_response_code(400);
    exit('Formato não suportado.');
}
if (!class_exists(ZipArchive::class)) {
    http_response_code(503);
    exit('A hospedagem não oferece suporte à exportação DOCX.');
}

$connection = getDatabaseConnection();
try {
    $document = ResumeDocument::find($connection, (int) $usuario['id']);
    $resume = json_decode((string) ($document['conteudo_json'] ?? ''), true);
    if (!is_array($resume)) {
        $statement = $connection->prepare('SELECT nome_social, grau_de_escolaridade, cursos, experiencia, idiomas FROM curriculo WHERE id_pessoa = ? LIMIT 1');
        $personId = (int) $usuario['id'];
        $statement->bind_param('i', $personId);
        $statement->execute();
        $legacyResume = $statement->get_result()->fetch_assoc() ?? [];
        $statement->close();
        $resume = [
            'nome' => (string) ($legacyResume['nome_social'] ?? $usuario['nome']),
            'resumo' => '',
            'formacao' => (string) ($legacyResume['grau_de_escolaridade'] ?? ''),
            'experiencia' => (string) ($legacyResume['experiencia'] ?? ''),
            'cursos' => (string) ($legacyResume['cursos'] ?? ''),
            'idiomas' => (string) ($legacyResume['idiomas'] ?? ''),
        ];
    }
} finally {
    $connection->close();
}

$sections = [
    'Resumo profissional' => (string) ($resume['resumo'] ?? ''),
    'Formação' => (string) ($resume['formacao'] ?? ''),
    'Experiência profissional' => (string) ($resume['experiencia'] ?? ''),
    'Cursos e certificações' => (string) ($resume['cursos'] ?? ''),
    'Idiomas' => (string) ($resume['idiomas'] ?? ''),
];
$xmlEscape = static fn (string $value): string => htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
$paragraph = static function (string $text) use ($xmlEscape): string {
    $lines = preg_split('/\R/u', $text) ?: [$text];
    $runs = '';
    foreach ($lines as $index => $line) {
        if ($index > 0) {
            $runs .= '<w:r><w:br/></w:r>';
        }
        $runs .= '<w:r><w:t xml:space="preserve">' . $xmlEscape($line) . '</w:t></w:r>';
    }
    return '<w:p>' . $runs . '</w:p>';
};

$body = '<w:p><w:pPr><w:pStyle w:val="Title"/></w:pPr><w:r><w:t>'
    . $xmlEscape((string) ($resume['nome'] ?? $usuario['nome'])) . '</w:t></w:r></w:p>';
foreach ($sections as $heading => $content) {
    if (trim($content) === '') {
        continue;
    }
    $body .= '<w:p><w:pPr><w:pStyle w:val="Heading1"/></w:pPr><w:r><w:t>' . $xmlEscape($heading) . '</w:t></w:r></w:p>';
    $body .= $paragraph($content);
}
$documentXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>'
    . $body
    . '<w:sectPr><w:pgSz w:w="11906" w:h="16838"/><w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440"/></w:sectPr>'
    . '</w:body></w:document>';
$stylesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
    . '<w:style w:type="paragraph" w:default="1" w:styleId="Normal"><w:name w:val="Normal"/><w:rPr><w:sz w:val="22"/></w:rPr></w:style>'
    . '<w:style w:type="paragraph" w:styleId="Title"><w:name w:val="Title"/><w:rPr><w:b/><w:sz w:val="36"/></w:rPr></w:style>'
    . '<w:style w:type="paragraph" w:styleId="Heading1"><w:name w:val="heading 1"/><w:rPr><w:b/><w:sz w:val="26"/></w:rPr></w:style>'
    . '</w:styles>';
$temporaryPath = tempnam(sys_get_temp_dir(), 'devin-resume-');
if ($temporaryPath === false) {
    http_response_code(500);
    exit('Não foi possível preparar o arquivo.');
}
$archive = new ZipArchive();
if ($archive->open($temporaryPath, ZipArchive::OVERWRITE) !== true) {
    @unlink($temporaryPath);
    http_response_code(500);
    exit('Não foi possível gerar o arquivo DOCX.');
}
$archive->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
    . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
    . '<Default Extension="xml" ContentType="application/xml"/>'
    . '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
    . '<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>'
    . '<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>'
    . '</Types>');
$archive->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
    . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
    . '</Relationships>');
$archive->addFromString('word/document.xml', $documentXml);
$archive->addFromString('word/styles.xml', $stylesXml);
$archive->addFromString('word/_rels/document.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
    . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
    . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
    . '</Relationships>');
$archive->close();

header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
header('Content-Disposition: attachment; filename="curriculo.docx"');
header('Content-Length: ' . (string) filesize($temporaryPath));
header('Cache-Control: private, no-store');
readfile($temporaryPath);
@unlink($temporaryPath);