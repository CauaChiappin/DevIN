<?php

class Jwt
{
    public static function encode(array $payload, string $secret): string
    {
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT',
        ];

        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));
        $signature = hash_hmac('sha256', $headerEncoded . '.' . $payloadEncoded, $secret, true);

        return $headerEncoded . '.' . $payloadEncoded . '.' . self::base64UrlEncode($signature);
    }

    public static function decode(string $token, string $secret): array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new RuntimeException('Token invalido.');
        }

        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;
        $signature = self::base64UrlDecode($signatureEncoded);
        $expectedSignature = hash_hmac('sha256', $headerEncoded . '.' . $payloadEncoded, $secret, true);

        if (!hash_equals($expectedSignature, $signature)) {
            throw new RuntimeException('Assinatura do token invalida.');
        }

        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);

        if (!is_array($payload)) {
            throw new RuntimeException('Payload do token invalido.');
        }

        if (isset($payload['exp']) && time() >= (int) $payload['exp']) {
            throw new RuntimeException('Token expirado.');
        }

        return $payload;
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;

        if ($remainder > 0) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($data, '-_', '+/'));
    }
}
