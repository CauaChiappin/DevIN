<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

/** Teste local sem MySQL ou chamadas de rede para o contexto enviado ao JARVIS. */
require_once __DIR__ . '/../php/controllers/Jarvis/SupportAiAssistant.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

$detail = [ // Explicação DevIN: Atribui um valor à variável PHP $detail.
    'ticket' => [ // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'category' => 'erro', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'requester_name' => 'NomePrivado', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'requester_email' => 'privado@example.com', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'description' => 'Olá, meu email é teste@example.com e o telefone +55 (11) 98765-4321. Token: segredo123. Não consigo anexar currículo.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ], // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    'replies' => [ // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ['message' => 'Enviei o comprovante para atendimento@example.org'], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    ], // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
$messages = SupportAiAssistant::messages($detail); // Explicação DevIN: Atribui um valor à variável PHP $messages.
if (count($messages) !== 2 // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    || $messages[0]['role'] !== 'system' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    || $messages[1]['role'] !== 'user') { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    throw new RuntimeException('Contrato de mensagens do JARVIS inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$payload = $messages[1]['content']; // Explicação DevIN: Atribui um valor à variável PHP $payload.
foreach (['NomePrivado', 'privado@example.com', 'teste@example.com', 'atendimento@example.org', 'segredo123', '98765-4321'] as $private) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
    if (str_contains($payload, $private)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new RuntimeException('Dado privado não foi ocultado: ' . $private); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
if (!str_contains($payload, '[dado ocultado]') // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    || !str_contains($payload, 'Não consigo anexar currículo.') // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    || !str_contains($messages[0]['content'], 'RASCUNHO')) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    throw new RuntimeException('Contexto anonimizado ou instruções de segurança ausentes.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
echo "SupportAiAssistant: OK\n"; // Explicação DevIN: Produz saída ou conteúdo da resposta do programa.
