<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

require_once __DIR__ . '/middlewares/auth.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/config/database.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/controllers/ResumeDocument.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

$usuario = requireWebAuth('pessoa'); // Explicação DevIN: Atribui um valor à variável PHP $usuario.
$connection = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $connection.
try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    $document = ResumeDocument::find($connection, (int) $usuario['id']); // Explicação DevIN: Atribui um valor à variável PHP $document.
} finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
    $connection->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$path = ResumeDocument::absolutePath((string) ($document['arquivo_caminho'] ?? '')); // Explicação DevIN: Atribui um valor à variável PHP $path.
if ($path === null || !is_readable($path)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    http_response_code(404); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    exit('Arquivo não encontrado.'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

$extension = strtolower(pathinfo((string) ($document['arquivo_nome'] ?? ''), PATHINFO_EXTENSION)); // Explicação DevIN: Atribui um valor à variável PHP $extension.
if ($extension === 'pdf') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    $content = file_get_contents($path); // Explicação DevIN: Atribui um valor à variável PHP $content.
    if (!is_string($content) || !str_starts_with($content, '%PDF-')) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        http_response_code(422); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        exit('O arquivo PDF não pôde ser exibido.'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    header('Content-Type: application/pdf'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    header('Content-Length: ' . (string) filesize($path)); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    header('Content-Disposition: inline; filename="curriculo.pdf"'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    header('X-Content-Type-Options: nosniff'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    header('Cache-Control: private, no-store'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    readfile($path); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

$paragraphs = []; // Explicação DevIN: Atribui um valor à variável PHP $paragraphs.
if ($extension === 'docx' && class_exists(ZipArchive::class) && class_exists(DOMDocument::class)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    $archive = new ZipArchive(); // Explicação DevIN: Atribui um valor à variável PHP $archive.
    if ($archive->open($path) === true) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $documentIndex = $archive->locateName('word/document.xml'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        $documentStats = $documentIndex === false ? false : $archive->statIndex($documentIndex); // Explicação DevIN: Atribui um valor à variável PHP $documentStats.
        $xmlContent = is_array($documentStats) && (int) ($documentStats['size'] ?? 0) <= 5 * 1024 * 1024 // Explicação DevIN: Atribui um valor à variável PHP $xmlContent.
            ? $archive->getFromName('word/document.xml') // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            : false; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        $archive->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        if (is_string($xmlContent) && strlen($xmlContent) <= 5 * 1024 * 1024) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $xml = new DOMDocument(); // Explicação DevIN: Atribui um valor à variável PHP $xml.
            $previousLibxmlState = libxml_use_internal_errors(true); // Explicação DevIN: Atribui um valor à variável PHP $previousLibxmlState.
            $loaded = $xml->loadXML($xmlContent, LIBXML_NONET | LIBXML_NOBLANKS); // Explicação DevIN: Atribui um valor à variável PHP $loaded.
            libxml_clear_errors(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            libxml_use_internal_errors($previousLibxmlState); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            if ($loaded) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                $xpath = new DOMXPath($xml); // Explicação DevIN: Atribui um valor à variável PHP $xpath.
                $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                foreach ($xpath->query('//w:p') ?: [] as $paragraph) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                    $parts = []; // Explicação DevIN: Atribui um valor à variável PHP $parts.
                    foreach ($xpath->query('.//w:t', $paragraph) ?: [] as $textNode) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                        $parts[] = $textNode->textContent; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                    $text = trim(implode('', $parts)); // Explicação DevIN: Atribui um valor à variável PHP $text.
                    if ($text !== '') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        $paragraphs[] = $text; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

header('Content-Type: text/html; charset=utf-8'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
header('X-Content-Type-Options: nosniff'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
header('Cache-Control: private, no-store'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.

?>
<!DOCTYPE html> <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
<html lang="pt-BR"> <!-- Explicação DevIN: Declara elemento raiz do documento HTML. -->
<head> <!-- Explicação DevIN: Declara metadados e dependências do documento. -->
    <meta charset="UTF-8"> <!-- Explicação DevIN: Declara metadado de navegação. -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Explicação DevIN: Declara metadado de navegação. -->
    <title>Prévia do currículo enviado</title> <!-- Explicação DevIN: Declara elemento de marcação. -->
    <style>
        body { max-width: 820px; margin: 0 auto; padding: 32px 22px; color: #24394b; font: 16px/1.65 Georgia, serif; } /* Explicação DevIN: Continua uma declaração de seletor ou valor CSS iniciada no trecho anterior. */
        h1 { padding-bottom: 14px; border-bottom: 2px solid #187b72; overflow-wrap: anywhere; } /* Explicação DevIN: Continua uma declaração de seletor ou valor CSS iniciada no trecho anterior. */
        .resume-preview-text { white-space: pre-wrap; } /* Explicação DevIN: Continua uma declaração de seletor ou valor CSS iniciada no trecho anterior. */
        .resume-preview-note { padding: 14px; background: #edf6f4; font-family: Arial, sans-serif; } /* Explicação DevIN: Continua uma declaração de seletor ou valor CSS iniciada no trecho anterior. */
    </style>
</head> <!-- Explicação DevIN: Fecha elemento head do documento. -->
<body> <!-- Explicação DevIN: Declara corpo visível da página. -->
    <h1><?= htmlspecialchars((string) ($document['arquivo_nome'] ?? 'Currículo'), ENT_QUOTES, 'UTF-8') ?></h1> <!-- Explicação DevIN: Declara título principal. Contém dado dinâmico processado pelo PHP. -->
    <?php if ($extension === 'docx' && $paragraphs !== []): ?>
        <?php foreach ($paragraphs as $paragraph): ?>
            <p class="resume-preview-text"><?= htmlspecialchars($paragraph, ENT_QUOTES, 'UTF-8') ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
        <?php endforeach; ?>
    <?php else: ?>
        <p class="resume-preview-note">A prévia deste formato não está disponível no navegador. <a href="curriculo_baixar.php">Baixar arquivo original</a>.</p> <!-- Explicação DevIN: Declara parágrafo. A linha reúne múltiplos elementos. -->
    <?php endif; ?>
    <script src="../js/lenis-global.js?v=<?= filemtime(__DIR__ . '/../js/lenis-global.js') ?>"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
</body> <!-- Explicação DevIN: Fecha elemento body do documento. -->
</html> <!-- Explicação DevIN: Fecha elemento html do documento. -->