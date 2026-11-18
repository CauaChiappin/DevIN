<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/arcade_unlock.php
 * Finalidade: arcade unlock.
 * Domínio: processamento no servidor.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


require_once __DIR__ . '/middlewares/auth.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/config/security.php'; // Libera a entrada secreta do Arcade após autenticação e CSRF.
require_once __DIR__ . '/controllers/ArcadeAccess.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

header('Content-Type: application/json; charset=utf-8'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
header('Cache-Control: no-store, private'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    http_response_code(405); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    header('Allow: POST'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.'], JSON_UNESCAPED_UNICODE); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
    exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

$usuario = requireWebAuth(); // Explicação DevIN: Atribui um valor à variável PHP $usuario.
requireValidCsrf(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

$sequenciaEsperada = [ // Explicação DevIN: Atribui um valor à variável PHP $sequenciaEsperada.
    'ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'KeyB', 'KeyA', 'Enter', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
$modo = requestString($_POST, 'modo'); // Libera a entrada secreta do Arcade após autenticação e CSRF.
$sequenciaRecebida = json_decode(requestString($_POST, 'sequencia'), true); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.

// O teclado exige Konami; no celular a sequência de cinco toques ativa o modo secreto.
// Nos dois casos, sessão autenticada e CSRF continuam obrigatórios.
if ($modo !== 'toque' && (!is_array($sequenciaRecebida) || $sequenciaRecebida !== $sequenciaEsperada)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    http_response_code(403); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    echo json_encode(['sucesso' => false, 'mensagem' => 'Sequência inválida.'], JSON_UNESCAPED_UNICODE); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
    exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

ArcadeAccess::grant($usuario); // Libera a entrada secreta do Arcade após autenticação e CSRF.

echo json_encode([ // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
    'sucesso' => true, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'url' => APP_BASE_URL . '/php/arcade.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
