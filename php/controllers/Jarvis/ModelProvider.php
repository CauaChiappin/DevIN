<?php
declare(strict_types=1);

/** Provedores OpenAI-compatible selecionados exclusivamente por configuração do servidor. */
final class ModelProvider
{
    public static function configured(): bool
    {
        return self::setting('DEVIN_JARVIS_PROVIDER') !== ''
            && self::setting('DEVIN_JARVIS_MODEL') !== ''
            && self::setting('DEVIN_JARVIS_API_KEY') !== '';
    }

    private static function setting(string $name): string
    {
        return trim((string) ($_ENV[$name] ?? $_SERVER[$name] ?? getenv($name) ?: ''));
    }

    public static function chat(array $messages): string
    {
        $endpoints = [
            'groq' => 'https://api.groq.com/openai/v1/chat/completions',
            'openrouter' => 'https://openrouter.ai/api/v1/chat/completions',
            'openai' => 'https://api.openai.com/v1/chat/completions',
        ];
        $provider = strtolower(self::setting('DEVIN_JARVIS_PROVIDER'));
        $endpoint = $endpoints[$provider] ?? null;
        $model = self::setting('DEVIN_JARVIS_MODEL');
        $key = self::setting('DEVIN_JARVIS_API_KEY');
        if (!$endpoint || !$key || !$model) {
            throw new RuntimeException('Modelo de IA não configurado no servidor.');
        }
        // IDs como z-ai/glm-5.3-flash:free são permitidos, mas não URLs arbitrárias.
        if (!preg_match('~^[a-zA-Z0-9._:/-]{1,120}$~D', $model)) {
            throw new RuntimeException('ID do modelo inválido.');
        }
        if (!function_exists('curl_init')) {
            throw new RuntimeException('Extensão cURL ausente.');
        }
        $json = json_encode([
            'model' => $model,
            'messages' => $messages,
            'temperature' => 0.3,
            'max_tokens' => 1024,
            'stream' => false,
        ], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR);
        $curl = curl_init($endpoint);
        if ($curl === false) throw new RuntimeException('Falha ao iniciar provedor.');
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
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        if (!is_string($response) || $status < 200 || $status >= 300) {
            error_log('JARVIS provider ' . $provider . ' HTTP ' . $status . ' cURL ' . $error);
            throw new RuntimeException($status === 429 ? 'Limite gratuito do modelo atingido.' : 'Provedor de IA temporariamente indisponível.');
        }
        try {
            $data = json_decode($response, true, 16, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Resposta inválida do provedor.');
        }
        $answer = $data['choices'][0]['message']['content'] ?? null;
        if (!is_string($answer) || trim($answer) === '') {
            throw new RuntimeException('O modelo não retornou texto.');
        }
        return mb_substr(trim($answer), 0, 12000, 'UTF-8');
    }
}
