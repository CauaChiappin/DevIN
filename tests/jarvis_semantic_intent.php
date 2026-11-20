<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

require_once dirname(__DIR__) . '/php/controllers/Jarvis/JarvisSemanticIntent.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

function semanticCheck(bool $value, string $label): void // Explicação DevIN: Declara função/método semanticCheck, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if (!$value) throw new RuntimeException('JARVIS semantic: ' . $label); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$_ENV['DEVIN_JARVIS_PROVIDER'] = 'openrouter'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_ENV['DEVIN_JARVIS_MODEL'] = 'openrouter/free'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_ENV['DEVIN_JARVIS_API_KEY'] = 'sk-or-v1-offline-fixture'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_ENV['DEVIN_JARVIS_BACKUP_API_KEY'] = ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_ENV['DEVIN_JARVIS_FALLBACK_MODEL'] = ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_ENV['DEVIN_JARVIS_FALLBACK_MODELS'] = ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.

$private = "Mostre candidatos da empresa João Silva, CPF 123.456.789-09. " // Explicação DevIN: Atribui um valor à variável PHP $private.
    . "Contato joao@exemplo.com, telefone 11999999999. Token sk-or-v1-abcdefghijklm"; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
$safe = JarvisSemanticIntent::redact($private); // Explicação DevIN: Atribui um valor à variável PHP $safe.
foreach (['123.456.789-09','joao@exemplo.com','11999999999', // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
    'sk-or-v1-abcdefghijklm','João Silva'] as $secret) { // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    semanticCheck(!str_contains($safe, $secret), 'redação omite dado pessoal: ' . $secret); // Explicação DevIN: Continua expressão ou bloco pertencente a função semanticCheck.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

$valid = '{"intent":"support_mine","confidence":0.97}'; // Explicação DevIN: Atribui um valor à variável PHP $valid.
semanticCheck(JarvisSemanticIntent::parse($valid) === 'support_mine', // Explicação DevIN: Continua expressão ou bloco pertencente a função semanticCheck.
    'intenção permitida'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
foreach ([ // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
    '{"intent":"delete","confidence":1}', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '{"intent":"send","confidence":1}', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '{"intent":"task_create","confidence":1}', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '{"intent":"support_mine","confidence":0.72}', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '{"intent":"support_mine","confidence":2}', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '{"intent":"support_mine","confidence":1,"sql":"DELETE FROM vagas"}', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '{"intent":"__proto__","confidence":1}', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '{"intent":"none","confidence":1}', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'Qualquer texto', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '{"intent":"support_mine","confidence":"NAN"}', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
] as $invalid) { // Explicação DevIN: Continua expressão ou bloco pertencente a função semanticCheck.
    semanticCheck(JarvisSemanticIntent::parse($invalid) === null, // Explicação DevIN: Continua expressão ou bloco pertencente a função semanticCheck.
        'modelo devolve opção não confiável ou fora da lista'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

$calls = 0; // Explicação DevIN: Atribui um valor à variável PHP $calls.
$transport = static function (string $url, string $key, array $body) use (&$calls, $valid): array { // Explicação DevIN: Define função anônima usada como operação ou callback.
    $calls++; // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    semanticCheck($url === 'https://openrouter.ai/api/v1/chat/completions', 'endpoint fixo'); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    semanticCheck($key === 'sk-or-v1-offline-fixture', 'chave usada só em memória'); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    semanticCheck(($body['model'] ?? null) === 'openrouter/free', 'somente roteador gratuito'); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    semanticCheck(count($body['messages']) === 2, 'sem histórico nem banco enviados à IA'); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    semanticCheck(!str_contains(json_encode($body), 'joao@exemplo.com'), 'e-mail ausente'); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
    return [ // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        'status' => 200, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'body' => json_encode(['model'=>'openrouter/free', // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
            'choices'=>[['message'=>['content'=>$valid]]]], JSON_THROW_ON_ERROR), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
}; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

$intent = JarvisSemanticIntent::resolve( // Explicação DevIN: Atribui um valor à variável PHP $intent.
    'Poderia me mostrar meus atendimentos que estão comigo?', $transport); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
semanticCheck($intent === 'support_mine' && $calls === 1, // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    'interpretação de consulta com uma chamada simulada'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
$intent = JarvisSemanticIntent::resolve('Exclua todas as vagas do banco', $transport); // Explicação DevIN: Atribui um valor à variável PHP $intent.
semanticCheck($intent === null && $calls === 1, // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    'intenção destrutiva não chega ao modelo'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
echo "JARVIS semantic resolver offline: OK\n"; // Explicação DevIN: Produz saída ou conteúdo da resposta do programa.
