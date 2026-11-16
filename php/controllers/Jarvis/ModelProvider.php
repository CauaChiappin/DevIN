<?php
declare(strict_types=1);

require_once __DIR__ . '/ModelRouter.php';

/**
 * Erro classificado sem expor resposta bruta, prompt nem chaves da API.
 * O frontend recebe apenas categoria e HTTP status seguro para diagnóstico.
 */
final class JarvisModelException extends RuntimeException
{
    public function __construct(
        public readonly string $reason,
        public readonly int $httpStatus = 0
    ) {
        $labels = [
            'auth' => 'A chave da OpenRouter foi recusada.',
            'credits' => 'A conta ou a chave da OpenRouter está sem saldo ou orçamento autorizado.',
            'rate_limit' => 'Limite de uso da OpenRouter atingido.',
            'network' => 'O servidor não conseguiu se conectar à OpenRouter.',
            'provider' => 'O serviço de IA está temporariamente indisponível.',
            'model' => 'O modelo solicitado não está disponível.',
            'invalid_response' => 'O provedor retornou uma resposta inválida ou vazia.',
            'configuration' => 'Configuração de IA incompleta ou inválida.',
        ];
        parent::__construct($labels[$reason] ?? 'A IA não conseguiu responder.');
    }
}

/** Endpoint fixo, modelo(s) em lista, credenciais exclusivamente no servidor. */
final class ModelProvider
{
    private static function setting(string $name): string
    {
        return trim((string) ($_ENV[$name] ?? $_SERVER[$name] ?? getenv($name) ?: ''));
    }

    public static function configured(): bool
    {
        $provider = strtolower(self::setting('DEVIN_JARVIS_PROVIDER'));
        return in_array($provider, ['groq', 'openrouter', 'openai'], true)
            && self::setting('DEVIN_JARVIS_MODEL') !== ''
            && self::setting('DEVIN_JARVIS_API_KEY') !== '';
    }

    /** @return array{status:int,body:string} */
    private static function send(string $endpoint, string $key, string $json): array
    {
        if (!function_exists('curl_init')) {
            throw new JarvisModelException('configuration');
        }
        $curl = curl_init($endpoint);
        if ($curl === false) {
            throw new JarvisModelException('network');
        }
        try {
            curl_setopt_array($curl, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $json,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $key,
                    'Content-Type: application/json',
                    'Accept: application/json',
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 25,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_FOLLOWLOCATION => false,
            ]);
            $response = curl_exec($curl);
            return [
                'status' => (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE),
                'body' => is_string($response) ? $response : '',
            ];
        } finally {
            curl_close($curl);
        }
    }

    private static function category(int $status): string
    {
        return match ($status) {
            0, 408 => 'network',
            401, 403 => 'auth',
            402 => 'credits',
            429 => 'rate_limit',
            400, 404, 413, 422 => 'model',
            default => $status >= 500 ? 'provider' : 'provider',
        };
    }

    /**
     * $transport serve exclusivamente à integração testada sem requisição real.
     * Em produção, o argumento não é passado.
     *
     * @param list<array{role:string,content:string}> $messages
     * @return array{reply:string,model:string,provider:string,fallback_used:bool,key_used:string}
     */
    public static function chatWithMeta(array $messages, ?callable $transport = null): array
    {
        $endpoints = [
            'groq' => 'https://api.groq.com/openai/v1/chat/completions',
            'openrouter' => 'https://openrouter.ai/api/v1/chat/completions',
            'openai' => 'https://api.openai.com/v1/chat/completions',
        ];
        $provider = strtolower(self::setting('DEVIN_JARVIS_PROVIDER'));
        $endpoint = $endpoints[$provider] ?? null;
        $primaryKey = self::setting('DEVIN_JARVIS_API_KEY');
        $primary = self::setting('DEVIN_JARVIS_MODEL');

        if (!$endpoint || $primaryKey === '' || $primary === '') {
            throw new JarvisModelException('configuration');
        }
        try {
            $models = ModelRouter::candidates(
                $provider,
                $primary,
                self::setting('DEVIN_JARVIS_FALLBACK_MODEL'),
                self::setting('DEVIN_JARVIS_FALLBACK_MODELS')
            );
        } catch (InvalidArgumentException $exception) {
            throw new JarvisModelException('configuration');
        }
        if (!is_array($messages) || $messages === []) {
            throw new JarvisModelException('configuration');
        }

        $body = [
            'model' => $models[0],
            'messages' => $messages,
            'temperature' => 0.3,
            'max_tokens' => 1024,
            'stream' => false,
        ];
        if ($provider === 'openrouter' && count($models) > 1) {
            // Fallback nativo: 1 solicitação percorre até 11 modelos, sem
            // executar chamadas por modelo nem incorrer em custo de modelo pago.
            $body['models'] = $models;
        }
        $json = json_encode(
            $body,
            JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR
        );

        $keys = [['label' => 'primary', 'key' => $primaryKey]];
        $backup = $provider === 'openrouter'
            ? self::setting('DEVIN_JARVIS_BACKUP_API_KEY')
            : '';
        if ($backup !== '' && !hash_equals($primaryKey, $backup)) {
            $keys[] = ['label' => 'backup', 'key' => $backup];
        }

        $lastStatus = 0;
        $lastReason = 'provider';
        foreach ($keys as $index => $credential) {
            try {
                // Não registrar corpo ou credenciais; logs contêm apenas categorias.
                $result = $transport
                    ? $transport($endpoint, $credential['key'], $body)
                    : self::send($endpoint, $credential['key'], $json);
                $status = (int) ($result['status'] ?? 0);
                $response = (string) ($result['body'] ?? '');
            } catch (Throwable $exception) {
                $status = 0;
                $response = '';
            }

            if ($status >= 200 && $status < 300) {
                try {
                    $data = json_decode($response, true, 20, JSON_THROW_ON_ERROR);
                } catch (JsonException $exception) {
                    $data = null;
                }
                $answer = is_array($data)
                    ? ($data['choices'][0]['message']['content'] ?? null)
                    : null;
                if (is_string($answer) && trim($answer) !== '') {
                    $reportedModel = $data['model'] ?? $models[0];
                    // Retornar exatamente um identificador plausível ou o modelo escolhido.
                    $reportedModel = is_string($reportedModel)
                        && ModelRouter::validModel($reportedModel)
                        ? $reportedModel : $models[0];

                    return [
                        'reply' => mb_substr(trim($answer), 0, 12000, 'UTF-8'),
                        'model' => $reportedModel,
                        'provider' => $provider,
                        'fallback_used' => $reportedModel !== $models[0],
                        'key_used' => $credential['label'],
                    ];
                }
                $lastStatus = $status;
                $lastReason = 'invalid_response';
                error_log('JARVIS OpenRouter: invalid_response, credential=' . $credential['label']);
            } else {
                $lastStatus = $status;
                $lastReason = self::category($status);
                error_log('JARVIS provider=' . $provider . ' status=' . $status
                    . ' reason=' . $lastReason . ' credential=' . $credential['label']);
            }

            if (!isset($keys[$index + 1])) break;
            if ($lastReason !== 'invalid_response'
                && !ModelRouter::shouldTrySecondKey($lastStatus)) break;
        }
        throw new JarvisModelException($lastReason, $lastStatus);
    }
}
