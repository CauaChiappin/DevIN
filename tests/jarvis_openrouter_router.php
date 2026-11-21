<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — tests/jarvis_openrouter_router.php
 * Finalidade: jarvis openrouter router.
 * Domínio: assistência administrativa.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


// Teste offline: NUNCA usa credenciais reais ou faz requisições de rede.
require_once dirname(__DIR__) . '/php/controllers/Jarvis/ModelProvider.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

// Detalhe DevIN: Método assertRouter: encapsula uma responsabilidade reutilizável do módulo no contexto de jarvis openrouter router. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function assertRouter(bool $condition, string $message): void // Explicação DevIN: Declara função/método assertRouter, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if (!$condition) throw new RuntimeException('Roteador OpenRouter: ' . $message); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$primaryKey = 'DUMMY_PRIMARY_NOT_A_REAL_KEY'; // Explicação DevIN: Atribui um valor à variável PHP $primaryKey.
$backupKey = 'DUMMY_BACKUP_NOT_A_REAL_KEY'; // Explicação DevIN: Atribui um valor à variável PHP $backupKey.
$_ENV['DEVIN_JARVIS_PROVIDER'] = 'openrouter'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_ENV['DEVIN_JARVIS_MODEL'] = 'z-ai/glm-5.3-flash:free'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_ENV['DEVIN_JARVIS_API_KEY'] = $primaryKey; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_ENV['DEVIN_JARVIS_BACKUP_API_KEY'] = $backupKey; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_ENV['DEVIN_JARVIS_FALLBACK_MODEL'] = 'sk-or-v1-invalid-field-not-a-real-secret'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
unset($_ENV['DEVIN_JARVIS_FALLBACK_MODELS']); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertRouter.
$messages = [['role'=>'user', 'content'=>'Oi']]; // Explicação DevIN: Atribui um valor à variável PHP $messages.

$calls = []; // Explicação DevIN: Atribui um valor à variável PHP $calls.
$transport = static function (string $url, string $key, array $body) use (&$calls, $primaryKey, $backupKey): array { // Explicação DevIN: Define função anônima usada como operação ou callback.
    $calls[] = ['key' => $key, 'models' => $body['models'] ?? [], 'url' => $url]; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    assertRouter($url === 'https://openrouter.ai/api/v1/chat/completions', 'somente endpoint fixo'); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    assertRouter($body['model'] === 'z-ai/glm-5.3-flash:free', 'principal mantido'); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    assertRouter(count($body['models']) === 3, 'até 2 reservas ativadas por padrão'); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    assertRouter(!str_contains(json_encode($body), 'sk-or-v1-invalid-field'), 'segredo não vira modelo'); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
    if ($key === $primaryKey) return ['status'=>401,'body'=>'{}']; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    if ($key === $backupKey) return ['status'=>200,'body'=>json_encode([ // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        'model'=>'google/gemma-4-31b-it:free', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'choices'=>[['message'=>['content'=>'Resposta de teste']]], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ])]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
    throw new RuntimeException('Chave desconhecida.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
}; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$result = ModelProvider::chatWithMeta($messages, $transport); // Explicação DevIN: Atribui um valor à variável PHP $result.
assertRouter(count($calls) === 2, 'uma chamada por chave, sem 11 chamadas por modelo'); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
assertRouter($result['key_used'] === 'backup', 'chave reserva indicada'); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
assertRouter($result['fallback_used'] === true, 'modelo reserva detectado'); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
assertRouter($result['model'] === 'google/gemma-4-31b-it:free', 'retornar modelo real'); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
assertRouter($result['reply'] === 'Resposta de teste', 'resposta preservada'); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.

$calls = 0; // Explicação DevIN: Atribui um valor à variável PHP $calls.
$result = ModelProvider::chatWithMeta($messages, static function ($url, $key, $body) use (&$calls): array { // Explicação DevIN: Define função anônima usada como operação ou callback.
    $calls++; // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    return ['status'=>200,'body'=>'{"model":"z-ai/glm-5.3-flash:free","choices":[{"message":{"content":"OK"}}]}']; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
assertRouter($calls === 1 && $result['key_used'] === 'primary' // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    && $result['fallback_used'] === false, 'chave principal usada normalmente'); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.

$calls = 0; // Explicação DevIN: Atribui um valor à variável PHP $calls.
try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    ModelProvider::chatWithMeta($messages, static function ($url, $key, $body) use (&$calls): array { // Explicação DevIN: Define função anônima usada como operação ou callback.
        $calls++; // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        return ['status'=>400,'body'=>'{"error":"wrong model"}']; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    throw new RuntimeException('HTTP 400 deveria falhar.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
} catch (JarvisModelException $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    assertRouter($exception->reason === 'model' && $exception->httpStatus === 400, // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        'classificação de erro do modelo'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    assertRouter($calls === 2, 'reconsulta somente uma vez pelo roteador gratuito, sem trocar a chave'); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

$calls = 0; // Explicação DevIN: Atribui um valor à variável PHP $calls.
try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    ModelProvider::chatWithMeta($messages, static function ($url, $key, $body) use (&$calls): array { // Explicação DevIN: Define função anônima usada como operação ou callback.
        $calls++; // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        return ['status'=>429,'body'=>'{"error":"rate limit"}']; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    throw new RuntimeException('HTTP 429 deveria falhar.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
} catch (JarvisModelException $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    assertRouter($exception->reason === 'rate_limit' && $exception->httpStatus === 429, // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        'cota claramente identificada'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    assertRouter($calls === 2, 'faz apenas uma tentativa por chave'); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Um ID gratuito encerrado recebe 404; openrouter/free deve recuperar a conversa.
$_ENV['DEVIN_JARVIS_BACKUP_API_KEY'] = $backupKey; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$calls = []; // Explicação DevIN: Atribui um valor à variável PHP $calls.
$result = ModelProvider::chatWithMeta($messages, // Explicação DevIN: Atribui um valor à variável PHP $result.
    static function ($url, $key, $body) use (&$calls, $primaryKey): array { // Explicação DevIN: Define função anônima usada como operação ou callback.
        $calls[] = $body; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        assertRouter($key === $primaryKey, 'fallback do modelo usa a mesma chave principal'); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        if (count($calls) === 1) return ['status'=>404, 'body'=>'{"error":"No endpoints found"}']; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        assertRouter($body['model'] === 'openrouter/free', 'roteador gratuito usado como recuperação'); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        assertRouter(!isset($body['models']), 'recuperação evita repetir a lista de modelos desativados'); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        return ['status'=>200,'body'=>'{"model":"openrouter/free","choices":[{"message":{"content":"Atendimento recuperado"}}]}']; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
assertRouter(count($calls) === 2, 'no máximo uma recuperação 404 adicional'); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
assertRouter($result['reply'] === 'Atendimento recuperado' && $result['fallback_used'] === true, // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    'retorna texto do modelo de reserva sem custo de modelo pago'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.

$_ENV['DEVIN_JARVIS_MODEL'] = 'openrouter/free'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$calls = 0; // Explicação DevIN: Atribui um valor à variável PHP $calls.
try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    ModelProvider::chatWithMeta($messages, static function ($url, $key, $body) use (&$calls): array { // Explicação DevIN: Define função anônima usada como operação ou callback.
        $calls++; // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        return ['status'=>404,'body'=>'{"error":"Free router unavailable"}']; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    throw new RuntimeException('Roteador gratuito deveria reportar erro.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
} catch (JarvisModelException $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    assertRouter($exception->reason === 'model' && $calls === 1, // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        'não repete o próprio roteador gratuito.'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$_ENV['DEVIN_JARVIS_MODEL'] = 'z-ai/glm-5.3-flash:free'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.

$_ENV['DEVIN_JARVIS_BACKUP_API_KEY'] = $primaryKey; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$calls = 0; // Explicação DevIN: Atribui um valor à variável PHP $calls.
ModelProvider::chatWithMeta($messages, static function ($url, $key, $body) use (&$calls): array { // Explicação DevIN: Define função anônima usada como operação ou callback.
    $calls++; // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    return ['status'=>200,'body'=>'{"choices":[{"message":{"content":"Certo"}}]}']; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
assertRouter($calls === 1, 'não duplica chamada quando as duas chaves são iguais'); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.

$_ENV['DEVIN_JARVIS_FALLBACK_MODELS'] = 'google/gemma-4-31b-it:free,openrouter/free'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_ENV['DEVIN_JARVIS_BACKUP_API_KEY'] = $backupKey; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$calls = 0; // Explicação DevIN: Atribui um valor à variável PHP $calls.
ModelProvider::chatWithMeta($messages, static function ($url, $key, $body) use (&$calls): array { // Explicação DevIN: Define função anônima usada como operação ou callback.
    $calls++; // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    assertRouter(count($body['models']) === 3, 'lista configurada manualmente tem prioridade'); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    return ['status'=>200,'body'=>'{"choices":[{"message":{"content":"Tudo certo"}}]}']; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
assertRouter($calls === 1, 'modelo configurado usa uma chamada'); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.

echo "JARVIS OpenRouter dual-key mock: OK\n"; // Explicação DevIN: Produz saída ou conteúdo da resposta do programa.
