<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

require_once __DIR__ . '/../php/controllers/Jarvis/SupportAiAssistant.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/../php/controllers/Jarvis/JarvisCaseDesk.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

$case = [ // Explicação DevIN: Atribui um valor à variável PHP $case.
    'type' => 'support', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'category' => 'erro', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'description' => 'Meu e-mail é joao@example.com. Minha senha: minhaSenha123. ' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        . 'Meu telefone +55 (11) 91234-5678. Erro ao anexar currículo.', // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    'previous_replies' => ['Mensagem anterior', 'Falei com ajuda@example.org'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
$messages = JarvisCaseDesk::aiMessages($case, 'Como orientar o usuário?', true); // Explicação DevIN: Atribui um valor à variável PHP $messages.
if (($messages[0]['role'] ?? '') !== 'system' || ($messages[1]['role'] ?? '') !== 'user') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    throw new RuntimeException('Contrato do assistente inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$text = $messages[1]['content']; // Explicação DevIN: Atribui um valor à variável PHP $text.
foreach (['joao@example.com', 'minhaSenha123', '91234-5678', 'ajuda@example.org'] as $private) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
    if (str_contains($text, $private)) throw new RuntimeException('Dados pessoais não ocultados.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
if (!str_contains($text, 'Erro ao anexar currículo') // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    || !str_contains($messages[0]['content'], 'PARECER') // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    || !JarvisCaseDesk::pending(['type'=>'support', 'status'=>'aberto']) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    || JarvisCaseDesk::pending(['type'=>'profile_report', 'status'=>'concluida'])) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    throw new RuntimeException('Contrato de contexto ou estados inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
echo "Jarvis case context: OK\n"; // Explicação DevIN: Produz saída ou conteúdo da resposta do programa.
