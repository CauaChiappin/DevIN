<?php
declare(strict_types=1);

require_once __DIR__ . '/ModelRouter.php';

/** Conector mínimo: provedores e modelos definidos no servidor, nunca pelo navegador. */
final class ModelProvider
{
    private static function setting(string $name): string
    {
        return trim((string) ($_ENV[$name] ?? $_SERVER[$name] ?? getenv($name) ?: ''));
    }

    public static function configured(): bool
    {
        return in_array(strtolower(self::setting('DEVIN_JARVIS_PROVIDER')), ['groq', 'openrouter', 'openai'], true)
            && self::setting('DEVIN_JARVIS_MODEL') !== ''
            && self::setting('DEVIN_JARVIS_API_KEY') !== '';
    }

    /** @return array{reply:string,model:string,provider:string,fallback_used:bool} */
    public static function chatWithMeta(array $messages): array
    {
        $endpoints = [
            'groq' => 'https://api.groq.com/openai/v1/chat/completions',
            'openrouter' => 'https://openrouter.ai/api/v1/chat/completions',
            'openai' => 'https://api.openai.com/v1/chat/completions',
        ];
        $provider = strtolower(self::setting('DEVIN_JARVIS_PROVIDER'));
        $endpoint = $endpoints[$provider] ?? null;
        $key = self::setting('DEVIN_JARVIS_API_KEY');
        $primary = self::setting('DEVIN_JARVIS_MODEL');
        if (!$endpoint || $key === '' || $primary === '') {
            throw new RuntimeException('Modelo de IA não configurado no servidor.');
        }
        if (!function_exists('curl_init')) {
            throw new RuntimeException('Extensão cURL indisponível no servidor.');
        }
        $candidateModels = ModelRouter::candidates(
            $provider,
            $primary,
            self::setting('DEVIN_JARVIS_FALLBACK_MODEL')
        );
        foreach ($candidateModels as $index => $model) {
            $json = json_encode([
                'model' => $model,
                'messages' => $messages,
                'temperature' => 0.3,
                'max_tokens' => 1024,
                'stream' => false,
            ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);
            $curl = curl_init($endpoint);
            if ($curl === false) {
                throw new RuntimeException('Não foi possível inicializar o provedor.');
            }
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
            $httpStatus = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
            curl_close($curl);

            if (is_string($response) && $httpStatus >= 200 && $httpStatus < 300) {
                try {
                    $data = json_decode($response, true, 16, JSON_THROW_ON_ERROR);
                } catch (JsonException $exception) {
                    throw new RuntimeException('Resposta inválida do provedor.');
                }
                $answer = $data['choices'][0]['message']['content'] ?? null;
                if (!is_string($answer) || trim($answer) === '') {
                    throw new RuntimeException('O provedor não retornou uma resposta de texto.');
                }
                return [
                    'reply' => mb_substr(trim($answer), 0, 12000, 'UTF-8'),
                    'model' => $model,
                    'provider' => $provider,
                    'fallback_used' => $index > 0,
                ];
            }
            // Logs não incluem payload, mensagens, CPF ou credenciais.
            error_log('JARVIS provider ' . $provider . ': HTTP ' . $httpStatus . '; attempt ' . ($index + 1));
            if (!ModelRouter::shouldFallback($httpStatus) || !isset($candidateModels[$index + 1])) {
                break;
            }
        }
        throw new RuntimeException('Modelo indisponível ou limite do provedor atingido.');
    }
}
