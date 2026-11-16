<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

require_once __DIR__ . '/middlewares/auth.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/config/database.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/controllers/ResumeDocument.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

$usuario = requireWebAuth('pessoa'); // Explicação DevIN: Atribui um valor à variável PHP $usuario.
if (requestString($_GET, 'formato') !== 'docx') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    http_response_code(400); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    exit('Formato não suportado.'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
if (!class_exists(ZipArchive::class)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    http_response_code(503); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    exit('A hospedagem não oferece suporte à exportação DOCX.'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

$connection = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $connection.
try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    $document = ResumeDocument::find($connection, (int) $usuario['id']); // Explicação DevIN: Atribui um valor à variável PHP $document.
    $resume = json_decode((string) ($document['conteudo_json'] ?? ''), true); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
    if (!is_array($resume)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $statement = $connection->prepare('SELECT nome_social, grau_de_escolaridade, cursos, experiencia, idiomas FROM curriculo WHERE id_pessoa = ? LIMIT 1'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $personId = (int) $usuario['id']; // Explicação DevIN: Atribui um valor à variável PHP $personId.
        $statement->bind_param('i', $personId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $statement->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $legacyResume = $statement->get_result()->fetch_assoc() ?? []; // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $statement->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        $resume = [ // Explicação DevIN: Atribui um valor à variável PHP $resume.
            'nome' => (string) ($legacyResume['nome_social'] ?? $usuario['nome']), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'resumo' => '', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'formacao' => (string) ($legacyResume['grau_de_escolaridade'] ?? ''), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'experiencia' => (string) ($legacyResume['experiencia'] ?? ''), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'cursos' => (string) ($legacyResume['cursos'] ?? ''), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'idiomas' => (string) ($legacyResume['idiomas'] ?? ''), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
    $connection->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

$sections = [ // Explicação DevIN: Atribui um valor à variável PHP $sections.
    'Resumo profissional' => (string) ($resume['resumo'] ?? ''), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'Formação' => (string) ($resume['formacao'] ?? ''), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'Experiência profissional' => (string) ($resume['experiencia'] ?? ''), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'Cursos e certificações' => (string) ($resume['cursos'] ?? ''), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'Idiomas' => (string) ($resume['idiomas'] ?? ''), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
$xmlEscape = static fn (string $value): string => htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8'); // Explicação DevIN: Atribui um valor à variável PHP $xmlEscape.
$paragraph = static function (string $text) use ($xmlEscape): string { // Explicação DevIN: Define função anônima usada como operação ou callback.
    $lines = preg_split('/\R/u', $text) ?: [$text]; // Explicação DevIN: Atribui um valor à variável PHP $lines.
    $runs = ''; // Explicação DevIN: Atribui um valor à variável PHP $runs.
    foreach ($lines as $index => $line) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
        if ($index > 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $runs .= '<w:r><w:br/></w:r>'; // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $runs .= '<w:r><w:t xml:space="preserve">' . $xmlEscape($line) . '</w:t></w:r>'; // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    return '<w:p>' . $runs . '</w:p>'; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
}; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

$body = '<w:p><w:pPr><w:pStyle w:val="Title"/></w:pPr><w:r><w:t>' // Explicação DevIN: Atribui um valor à variável PHP $body.
    . $xmlEscape((string) ($resume['nome'] ?? $usuario['nome'])) . '</w:t></w:r></w:p>'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
foreach ($sections as $heading => $content) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
    if (trim($content) === '') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        continue; // Explicação DevIN: Controla a continuação ou saída de laço/seleção.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    $body .= '<w:p><w:pPr><w:pStyle w:val="Heading1"/></w:pPr><w:r><w:t>' . $xmlEscape($heading) . '</w:t></w:r></w:p>'; // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    $body .= $paragraph($content); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$documentXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' // Explicação DevIN: Atribui um valor à variável PHP $documentXml.
    . '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body>' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    . $body // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    . '<w:sectPr><w:pgSz w:w="11906" w:h="16838"/><w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440"/></w:sectPr>' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    . '</w:body></w:document>'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
$stylesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' // Explicação DevIN: Atribui um valor à variável PHP $stylesXml.
    . '<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    . '<w:style w:type="paragraph" w:default="1" w:styleId="Normal"><w:name w:val="Normal"/><w:rPr><w:sz w:val="22"/></w:rPr></w:style>' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    . '<w:style w:type="paragraph" w:styleId="Title"><w:name w:val="Title"/><w:rPr><w:b/><w:sz w:val="36"/></w:rPr></w:style>' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    . '<w:style w:type="paragraph" w:styleId="Heading1"><w:name w:val="heading 1"/><w:rPr><w:b/><w:sz w:val="26"/></w:rPr></w:style>' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    . '</w:styles>'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
$temporaryPath = tempnam(sys_get_temp_dir(), 'devin-resume-'); // Explicação DevIN: Atribui um valor à variável PHP $temporaryPath.
if ($temporaryPath === false) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    http_response_code(500); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    exit('Não foi possível preparar o arquivo.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$archive = new ZipArchive(); // Explicação DevIN: Atribui um valor à variável PHP $archive.
if ($archive->open($temporaryPath, ZipArchive::OVERWRITE) !== true) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    @unlink($temporaryPath); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    http_response_code(500); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    exit('Não foi possível gerar o arquivo DOCX.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$archive->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    . '<Default Extension="xml" ContentType="application/xml"/>' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    . '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>' // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    . '<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    . '<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    . '</Types>'); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
$archive->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>' // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    . '</Relationships>'); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
$archive->addFromString('word/document.xml', $documentXml); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
$archive->addFromString('word/styles.xml', $stylesXml); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
$archive->addFromString('word/_rels/document.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    . '</Relationships>'); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
$archive->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.

header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
header('Content-Disposition: attachment; filename="curriculo.docx"'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
header('Content-Length: ' . (string) filesize($temporaryPath)); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
header('Cache-Control: private, no-store'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
readfile($temporaryPath); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
@unlink($temporaryPath); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.