<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

require_once __DIR__ . '/config/security.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

header('Content-Type: application/json; charset=utf-8'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
header('Cache-Control: no-store, no-cache, must-revalidate'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.

try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    // Entrega token por JSON para chamadas assíncronas e impede que intermediários o armazenem.
    startSecureSession(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

    echo json_encode( // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
        ['sucesso' => true, 'token' => csrfToken()], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} catch (Throwable $e) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    http_response_code(500); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    echo json_encode( // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
        ['sucesso' => false, 'mensagem' => 'Não foi possível gerar o token CSRF.'], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
