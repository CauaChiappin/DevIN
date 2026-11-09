<?php
declare(strict_types=1);

// Teste offline: NUNCA usa credenciais reais ou faz requisições de rede.
require_once dirname(__DIR__) . '/php/controllers/Jarvis/ModelProvider.php';

function assertRouter(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException('Roteador OpenRouter: ' . $message);
}
$primaryKey = 'DUMMY_PRIMARY_NOT_A_REAL_KEY';
$backupKey = 'DUMMY_BACKUP_NOT_A_REAL_KEY';
$_ENV['DEVIN_JARVIS_PROVIDER'] = 'openrouter';
$_ENV['DEVIN_JARVIS_MODEL'] = 'z-ai/glm-5.3-flash:free';
$_ENV['DEVIN_JARVIS_API_KEY'] = $primaryKey;
$_ENV['DEVIN_JARVIS_BACKUP_API_KEY'] = $backupKey;
$_ENV['DEVIN_JARVIS_FALLBACK_MODEL'] = 'sk-or-v1-invalid-field-not-a-real-secret';
unset($_ENV['DEVIN_JARVIS_FALLBACK_MODELS']);
$messages = [['role'=>'user', 'content'=>'Oi']];

$calls = [];
$transport = static function (string $url, string $key, array $body) use (&$calls, $primaryKey, $backupKey): array {
    $calls[] = ['key' => $key, 'models' => $body['models'] ?? [], 'url' => $url];
    assertRouter($url === 'https://openrouter.ai/api/v1/chat/completions', 'somente endpoint fixo');
    assertRouter($body['model'] === 'z-ai/glm-5.3-flash:free', 'principal mantido');
    assertRouter(count($body['models']) === 11, '10 reservas ativadas por padrão');
    assertRouter(!str_contains(json_encode($body), 'sk-or-v1-invalid-field'), 'segredo não vira modelo');
    if ($key === $primaryKey) return ['status'=>401,'body'=>'{}'];
    if ($key === $backupKey) return ['status'=>200,'body'=>json_encode([
        'model'=>'google/gemma-4-31b-it:free',
        'choices'=>[['message'=>['content'=>'Resposta de teste']]],
    ])];
    throw new RuntimeException('Chave desconhecida.');
};
$result = ModelProvider::chatWithMeta($messages, $transport);
assertRouter(count($calls) === 2, 'uma chamada por chave, sem 11 chamadas por modelo');
assertRouter($result['key_used'] === 'backup', 'chave reserva indicada');
assertRouter($result['fallback_used'] === true, 'modelo reserva detectado');
assertRouter($result['model'] === 'google/gemma-4-31b-it:free', 'retornar modelo real');
assertRouter($result['reply'] === 'Resposta de teste', 'resposta preservada');

$calls = 0;
$result = ModelProvider::chatWithMeta($messages, static function ($url, $key, $body) use (&$calls): array {
    $calls++;
    return ['status'=>200,'body'=>'{"model":"z-ai/glm-5.3-flash:free","choices":[{"message":{"content":"OK"}}]}'];
});
assertRouter($calls === 1 && $result['key_used'] === 'primary'
    && $result['fallback_used'] === false, 'chave principal usada normalmente');

$calls = 0;
try {
    ModelProvider::chatWithMeta($messages, static function ($url, $key, $body) use (&$calls): array {
        $calls++;
        return ['status'=>400,'body'=>'{"error":"wrong model"}'];
    });
    throw new RuntimeException('HTTP 400 deveria falhar.');
} catch (JarvisModelException $exception) {
    assertRouter($exception->reason === 'model' && $exception->httpStatus === 400,
        'classificação de erro do modelo');
    assertRouter($calls === 1, 'não tenta outra chave para erro de request inválido');
}

$calls = 0;
try {
    ModelProvider::chatWithMeta($messages, static function ($url, $key, $body) use (&$calls): array {
        $calls++;
        return ['status'=>429,'body'=>'{"error":"rate limit"}'];
    });
    throw new RuntimeException('HTTP 429 deveria falhar.');
} catch (JarvisModelException $exception) {
    assertRouter($exception->reason === 'rate_limit' && $exception->httpStatus === 429,
        'cota claramente identificada');
    assertRouter($calls === 2, 'faz apenas uma tentativa por chave');
}

$_ENV['DEVIN_JARVIS_BACKUP_API_KEY'] = $primaryKey;
$calls = 0;
ModelProvider::chatWithMeta($messages, static function ($url, $key, $body) use (&$calls): array {
    $calls++;
    return ['status'=>200,'body'=>'{"choices":[{"message":{"content":"Certo"}}]}'];
});
assertRouter($calls === 1, 'não duplica chamada quando as duas chaves são iguais');

$_ENV['DEVIN_JARVIS_FALLBACK_MODELS'] = 'google/gemma-4-31b-it:free,openrouter/free';
$_ENV['DEVIN_JARVIS_BACKUP_API_KEY'] = $backupKey;
$calls = 0;
ModelProvider::chatWithMeta($messages, static function ($url, $key, $body) use (&$calls): array {
    $calls++;
    assertRouter(count($body['models']) === 3, 'lista configurada manualmente tem prioridade');
    return ['status'=>200,'body'=>'{"choices":[{"message":{"content":"Tudo certo"}}]}'];
});
assertRouter($calls === 1, 'modelo configurado usa uma chamada');

echo "JARVIS OpenRouter dual-key mock: OK\n";
