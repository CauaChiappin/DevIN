<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/php/controllers/Jarvis/JarvisSemanticIntent.php';

function semanticCheck(bool $value, string $label): void
{
    if (!$value) throw new RuntimeException('JARVIS semantic: ' . $label);
}
$_ENV['DEVIN_JARVIS_PROVIDER'] = 'openrouter';
$_ENV['DEVIN_JARVIS_MODEL'] = 'openrouter/free';
$_ENV['DEVIN_JARVIS_API_KEY'] = 'sk-or-v1-offline-fixture';
$_ENV['DEVIN_JARVIS_BACKUP_API_KEY'] = '';
$_ENV['DEVIN_JARVIS_FALLBACK_MODEL'] = '';
$_ENV['DEVIN_JARVIS_FALLBACK_MODELS'] = '';

$private = "Mostre candidatos da empresa João Silva, CPF 123.456.789-09. "
    . "Contato joao@exemplo.com, telefone 11999999999. Token sk-or-v1-abcdefghijklm";
$safe = JarvisSemanticIntent::redact($private);
foreach (['123.456.789-09','joao@exemplo.com','11999999999',
    'sk-or-v1-abcdefghijklm','João Silva'] as $secret) {
    semanticCheck(!str_contains($safe, $secret), 'redação omite dado pessoal: ' . $secret);
}

$valid = '{"intent":"support_mine","confidence":0.97}';
semanticCheck(JarvisSemanticIntent::parse($valid) === 'support_mine',
    'intenção permitida');
foreach ([
    '{"intent":"delete","confidence":1}',
    '{"intent":"send","confidence":1}',
    '{"intent":"task_create","confidence":1}',
    '{"intent":"support_mine","confidence":0.72}',
    '{"intent":"support_mine","confidence":2}',
    '{"intent":"support_mine","confidence":1,"sql":"DELETE FROM vagas"}',
    '{"intent":"__proto__","confidence":1}',
    '{"intent":"none","confidence":1}',
    'Qualquer texto',
    '{"intent":"support_mine","confidence":"NAN"}',
] as $invalid) {
    semanticCheck(JarvisSemanticIntent::parse($invalid) === null,
        'modelo devolve opção não confiável ou fora da lista');
}

$calls = 0;
$transport = static function (string $url, string $key, array $body) use (&$calls, $valid): array {
    $calls++;
    semanticCheck($url === 'https://openrouter.ai/api/v1/chat/completions', 'endpoint fixo');
    semanticCheck($key === 'sk-or-v1-offline-fixture', 'chave usada só em memória');
    semanticCheck(($body['model'] ?? null) === 'openrouter/free', 'somente roteador gratuito');
    semanticCheck(count($body['messages']) === 2, 'sem histórico nem banco enviados à IA');
    semanticCheck(!str_contains(json_encode($body), 'joao@exemplo.com'), 'e-mail ausente');
    return [
        'status' => 200,
        'body' => json_encode(['model'=>'openrouter/free',
            'choices'=>[['message'=>['content'=>$valid]]]], JSON_THROW_ON_ERROR),
    ];
};

$intent = JarvisSemanticIntent::resolve(
    'Poderia me mostrar meus atendimentos que estão comigo?', $transport);
semanticCheck($intent === 'support_mine' && $calls === 1,
    'interpretação de consulta com uma chamada simulada');
$intent = JarvisSemanticIntent::resolve('Exclua todas as vagas do banco', $transport);
semanticCheck($intent === null && $calls === 1,
    'intenção destrutiva não chega ao modelo');
echo "JARVIS semantic resolver offline: OK\n";
