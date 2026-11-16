<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

require_once __DIR__ . '/ModelRouter.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

/**
 * Erro classificado sem expor resposta bruta, prompt nem chaves da API.
 * O frontend recebe apenas categoria e HTTP status seguro para diagnóstico.
 */
final class JarvisModelException extends RuntimeException // Explicação DevIN: Declara o tipo JarvisModelException e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    public function __construct( // Explicação DevIN: Declara função/método __construct, com seus parâmetros e comportamento.
        public readonly string $reason, // Explicação DevIN: Define membro, visibilidade ou método de uma classe.
        public readonly int $httpStatus = 0 // Explicação DevIN: Define membro, visibilidade ou método de uma classe.
    ) { // Explicação DevIN: Continua expressão ou bloco pertencente a função __construct.
        $labels = [ // Explicação DevIN: Atribui um valor à variável PHP $labels.
            'auth' => 'A chave da OpenRouter foi recusada.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'credits' => 'A conta ou a chave da OpenRouter está sem saldo ou orçamento autorizado.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'rate_limit' => 'Limite de uso da OpenRouter atingido.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'network' => 'O servidor não conseguiu se conectar à OpenRouter.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'provider' => 'O serviço de IA está temporariamente indisponível.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'model' => 'O modelo solicitado não está disponível.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'invalid_response' => 'O provedor retornou uma resposta inválida ou vazia.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'configuration' => 'Configuração de IA incompleta ou inválida.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
        parent::__construct($labels[$reason] ?? 'A IA não conseguiu responder.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função __construct.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

/** Endpoint fixo, modelo(s) em lista, credenciais exclusivamente no servidor. */
final class ModelProvider // Explicação DevIN: Declara o tipo ModelProvider e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    private static function setting(string $name): string // Explicação DevIN: Declara função/método setting, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return trim((string) ($_ENV[$name] ?? $_SERVER[$name] ?? getenv($name) ?: '')); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function configured(): bool // Explicação DevIN: Declara função/método configured, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $provider = strtolower(self::setting('DEVIN_JARVIS_PROVIDER')); // Explicação DevIN: Atribui um valor à variável PHP $provider.
        return in_array($provider, ['groq', 'openrouter', 'openai'], true) // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            && self::setting('DEVIN_JARVIS_MODEL') !== '' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            && self::setting('DEVIN_JARVIS_API_KEY') !== ''; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /** @return array{status:int,body:string} */
    private static function send(string $endpoint, string $key, string $json): array // Explicação DevIN: Declara função/método send, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!function_exists('curl_init')) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new JarvisModelException('configuration'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $curl = curl_init($endpoint); // Explicação DevIN: Atribui um valor à variável PHP $curl.
        if ($curl === false) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new JarvisModelException('network'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            curl_setopt_array($curl, [ // Explicação DevIN: Continua expressão ou bloco pertencente a função send.
                CURLOPT_POST => true, // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                CURLOPT_POSTFIELDS => $json, // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                CURLOPT_HTTPHEADER => [ // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    'Authorization: Bearer ' . $key, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    'Content-Type: application/json', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    'Accept: application/json', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                ], // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                CURLOPT_RETURNTRANSFER => true, // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                CURLOPT_CONNECTTIMEOUT => 5, // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                CURLOPT_TIMEOUT => 25, // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                CURLOPT_SSL_VERIFYPEER => true, // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                CURLOPT_SSL_VERIFYHOST => 2, // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                CURLOPT_FOLLOWLOCATION => false, // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            ]); // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
            $response = curl_exec($curl); // Explicação DevIN: Atribui um valor à variável PHP $response.
            return [ // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
                'status' => (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'body' => is_string($response) ? $response : '', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
        } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
            curl_close($curl); // Explicação DevIN: Continua expressão ou bloco pertencente a função send.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    private static function category(int $status): string // Explicação DevIN: Declara função/método category, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return match ($status) { // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            0, 408 => 'network', // Explicação DevIN: Continua expressão ou bloco pertencente a função category.
            401, 403 => 'auth', // Explicação DevIN: Continua expressão ou bloco pertencente a função category.
            402 => 'credits', // Explicação DevIN: Continua expressão ou bloco pertencente a função category.
            429 => 'rate_limit', // Explicação DevIN: Continua expressão ou bloco pertencente a função category.
            400, 404, 413, 422 => 'model', // Explicação DevIN: Continua expressão ou bloco pertencente a função category.
            default => $status >= 500 ? 'provider' : 'provider', // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /**
     * $transport serve exclusivamente à integração testada sem requisição real.
     * Em produção, o argumento não é passado.
     *
     * @param list<array{role:string,content:string}> $messages
     * @return array{reply:string,model:string,provider:string,fallback_used:bool,key_used:string}
     */
    public static function chatWithMeta(array $messages, ?callable $transport = null): array // Explicação DevIN: Declara função/método chatWithMeta, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $endpoints = [ // Explicação DevIN: Atribui um valor à variável PHP $endpoints.
            'groq' => 'https://api.groq.com/openai/v1/chat/completions', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'openrouter' => 'https://openrouter.ai/api/v1/chat/completions', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'openai' => 'https://api.openai.com/v1/chat/completions', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
        $provider = strtolower(self::setting('DEVIN_JARVIS_PROVIDER')); // Explicação DevIN: Atribui um valor à variável PHP $provider.
        $endpoint = $endpoints[$provider] ?? null; // Explicação DevIN: Atribui um valor à variável PHP $endpoint.
        $primaryKey = self::setting('DEVIN_JARVIS_API_KEY'); // Explicação DevIN: Atribui um valor à variável PHP $primaryKey.
        $primary = self::setting('DEVIN_JARVIS_MODEL'); // Explicação DevIN: Atribui um valor à variável PHP $primary.

        if (!$endpoint || $primaryKey === '' || $primary === '') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new JarvisModelException('configuration'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            $models = ModelRouter::candidates( // Explicação DevIN: Atribui um valor à variável PHP $models.
                $provider, // Explicação DevIN: Continua expressão ou bloco pertencente a função chatWithMeta.
                $primary, // Explicação DevIN: Continua expressão ou bloco pertencente a função chatWithMeta.
                self::setting('DEVIN_JARVIS_FALLBACK_MODEL'), // Explicação DevIN: Continua expressão ou bloco pertencente a função chatWithMeta.
                self::setting('DEVIN_JARVIS_FALLBACK_MODELS') // Explicação DevIN: Continua expressão ou bloco pertencente a função chatWithMeta.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } catch (InvalidArgumentException $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            throw new JarvisModelException('configuration'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!is_array($messages) || $messages === []) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new JarvisModelException('configuration'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $body = [ // Explicação DevIN: Atribui um valor à variável PHP $body.
            'model' => $models[0], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'messages' => $messages, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'temperature' => 0.3, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'max_tokens' => 1024, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'stream' => false, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
        if ($provider === 'openrouter' && count($models) > 1) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            // Fallback nativo: 1 solicitação percorre até 11 modelos, sem
            // executar chamadas por modelo nem incorrer em custo de modelo pago.
            $body['models'] = $models; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $json = json_encode( // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
            $body, // Explicação DevIN: Continua expressão ou bloco pertencente a função chatWithMeta.
            JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR // Explicação DevIN: Continua expressão ou bloco pertencente a função chatWithMeta.
        ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $keys = [['label' => 'primary', 'key' => $primaryKey]]; // Explicação DevIN: Atribui um valor à variável PHP $keys.
        $backup = $provider === 'openrouter' // Explicação DevIN: Atribui um valor à variável PHP $backup.
            ? self::setting('DEVIN_JARVIS_BACKUP_API_KEY') // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            : ''; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        if ($backup !== '' && !hash_equals($primaryKey, $backup)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $keys[] = ['label' => 'backup', 'key' => $backup]; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $lastStatus = 0; // Explicação DevIN: Atribui um valor à variável PHP $lastStatus.
        $lastReason = 'provider'; // Explicação DevIN: Atribui um valor à variável PHP $lastReason.
        foreach ($keys as $index => $credential) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                // Não registrar corpo ou credenciais; logs contêm apenas categorias.
                $result = $transport // Explicação DevIN: Atribui um valor à variável PHP $result.
                    ? $transport($endpoint, $credential['key'], $body) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    : self::send($endpoint, $credential['key'], $json); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                $status = (int) ($result['status'] ?? 0); // Explicação DevIN: Atribui um valor à variável PHP $status.
                $response = (string) ($result['body'] ?? ''); // Explicação DevIN: Atribui um valor à variável PHP $response.
            } catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
                $status = 0; // Explicação DevIN: Atribui um valor à variável PHP $status.
                $response = ''; // Explicação DevIN: Atribui um valor à variável PHP $response.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            if ($status >= 200 && $status < 300) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                    $data = json_decode($response, true, 20, JSON_THROW_ON_ERROR); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
                } catch (JsonException $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
                    $data = null; // Explicação DevIN: Atribui um valor à variável PHP $data.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                $answer = is_array($data) // Explicação DevIN: Atribui um valor à variável PHP $answer.
                    ? ($data['choices'][0]['message']['content'] ?? null) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    : null; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                if (is_string($answer) && trim($answer) !== '') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    $reportedModel = $data['model'] ?? $models[0]; // Explicação DevIN: Atribui um valor à variável PHP $reportedModel.
                    // Retornar exatamente um identificador plausível ou o modelo escolhido.
                    $reportedModel = is_string($reportedModel) // Explicação DevIN: Atribui um valor à variável PHP $reportedModel.
                        && ModelRouter::validModel($reportedModel) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                        ? $reportedModel : $models[0]; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.

                    return [ // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
                        'reply' => mb_substr(trim($answer), 0, 12000, 'UTF-8'), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                        'model' => $reportedModel, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                        'provider' => $provider, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                        'fallback_used' => $reportedModel !== $models[0], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                        'key_used' => $credential['label'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                $lastStatus = $status; // Explicação DevIN: Atribui um valor à variável PHP $lastStatus.
                $lastReason = 'invalid_response'; // Explicação DevIN: Atribui um valor à variável PHP $lastReason.
                error_log('JARVIS OpenRouter: invalid_response, credential=' . $credential['label']); // Explicação DevIN: Continua expressão ou bloco pertencente a função chatWithMeta.
            } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
                $lastStatus = $status; // Explicação DevIN: Atribui um valor à variável PHP $lastStatus.
                $lastReason = self::category($status); // Explicação DevIN: Atribui um valor à variável PHP $lastReason.
                error_log('JARVIS provider=' . $provider . ' status=' . $status // Explicação DevIN: Continua expressão ou bloco pertencente a função chatWithMeta.
                    . ' reason=' . $lastReason . ' credential=' . $credential['label']); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            if (!isset($keys[$index + 1])) break; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            if ($lastReason !== 'invalid_response' // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                && !ModelRouter::shouldTrySecondKey($lastStatus)) break; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        throw new JarvisModelException($lastReason, $lastStatus); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
