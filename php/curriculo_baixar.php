<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/curriculo_baixar.php
 * Finalidade: curriculo baixar.
 * Domínio: processamento no servidor.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


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

$fileName = (string) ($document['arquivo_nome'] ?? 'curriculo'); // Explicação DevIN: Atribui um valor à variável PHP $fileName.
$fallbackName = preg_replace('/[^A-Za-z0-9._-]/', '_', iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $fileName) ?: 'curriculo'); // Explicação DevIN: Atribui um valor à variável PHP $fallbackName.
header('Content-Type: ' . (string) ($document['mime_type'] ?? 'application/octet-stream')); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
header('Content-Length: ' . (string) filesize($path)); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
header('Content-Disposition: attachment; filename="' . $fallbackName . '"; filename*=UTF-8\'\'' . rawurlencode($fileName)); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
header('X-Content-Type-Options: nosniff'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
header('Cache-Control: private, no-store'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
readfile($path); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.