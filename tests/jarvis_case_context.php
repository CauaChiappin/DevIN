<?php
declare(strict_types=1);

require_once __DIR__ . '/../php/controllers/Jarvis/SupportAiAssistant.php';
require_once __DIR__ . '/../php/controllers/Jarvis/JarvisCaseDesk.php';

$case = [
    'type' => 'support',
    'category' => 'erro',
    'description' => 'Meu e-mail é joao@example.com. Minha senha: minhaSenha123. '
        . 'Meu telefone +55 (11) 91234-5678. Erro ao anexar currículo.',
    'previous_replies' => ['Mensagem anterior', 'Falei com ajuda@example.org'],
];
$messages = JarvisCaseDesk::aiMessages($case, 'Como orientar o usuário?', true);
if (($messages[0]['role'] ?? '') !== 'system' || ($messages[1]['role'] ?? '') !== 'user') {
    throw new RuntimeException('Contrato do assistente inválido.');
}
$text = $messages[1]['content'];
foreach (['joao@example.com', 'minhaSenha123', '91234-5678', 'ajuda@example.org'] as $private) {
    if (str_contains($text, $private)) throw new RuntimeException('Dados pessoais não ocultados.');
}
if (!str_contains($text, 'Erro ao anexar currículo')
    || !str_contains($messages[0]['content'], 'PARECER')
    || !JarvisCaseDesk::pending(['type'=>'support', 'status'=>'aberto'])
    || JarvisCaseDesk::pending(['type'=>'profile_report', 'status'=>'concluida'])) {
    throw new RuntimeException('Contrato de contexto ou estados inválido.');
}
echo "Jarvis case context: OK\n";
