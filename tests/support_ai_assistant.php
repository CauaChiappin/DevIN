<?php
declare(strict_types=1);

/** Teste local sem MySQL ou chamadas de rede para o contexto enviado ao JARVIS. */
require_once __DIR__ . '/../php/controllers/Jarvis/SupportAiAssistant.php';

$detail = [
    'ticket' => [
        'category' => 'erro',
        'requester_name' => 'NomePrivado',
        'requester_email' => 'privado@example.com',
        'description' => 'Olá, meu email é teste@example.com e o telefone +55 (11) 98765-4321. Token: segredo123. Não consigo anexar currículo.',
    ],
    'replies' => [
        ['message' => 'Enviei o comprovante para atendimento@example.org'],
    ],
];
$messages = SupportAiAssistant::messages($detail);
if (count($messages) !== 2
    || $messages[0]['role'] !== 'system'
    || $messages[1]['role'] !== 'user') {
    throw new RuntimeException('Contrato de mensagens do JARVIS inválido.');
}
$payload = $messages[1]['content'];
foreach (['NomePrivado', 'privado@example.com', 'teste@example.com', 'atendimento@example.org', 'segredo123', '98765-4321'] as $private) {
    if (str_contains($payload, $private)) {
        throw new RuntimeException('Dado privado não foi ocultado: ' . $private);
    }
}
if (!str_contains($payload, '[dado ocultado]')
    || !str_contains($payload, 'Não consigo anexar currículo.')
    || !str_contains($messages[0]['content'], 'RASCUNHO')) {
    throw new RuntimeException('Contexto anonimizado ou instruções de segurança ausentes.');
}
echo "SupportAiAssistant: OK\n";
