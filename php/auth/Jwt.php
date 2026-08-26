<?php

class Jwt
{
    public static function encode(array $payload, string $secret): string
    {
        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT',
        ];

        $headerJson = json_encode(
            $header,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        $payloadJson = json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        if ($headerJson === false || $payloadJson === false) {
            throw new RuntimeException('Erro ao criar o token JWT.');
        }

        $headerEncoded = self::base64UrlEncode($headerJson);
        $payloadEncoded = self::base64UrlEncode($payloadJson);

        $signature = hash_hmac(
            'sha256',
            $headerEncoded . '.' . $payloadEncoded,
            $secret,
            true
        );

        $signatureEncoded = self::base64UrlEncode($signature);

        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }

    public static function decode(string $token, string $secret): array
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new RuntimeException('Token inválido.');
        }

        [
            $headerEncoded,
            $payloadEncoded,
            $signatureEncoded
        ] = $parts;

        if (
            $headerEncoded === '' ||
            $payloadEncoded === '' ||
            $signatureEncoded === ''
        ) {
            throw new RuntimeException('Token JWT inválido.');
        }

        $signature = self::base64UrlDecode($signatureEncoded);

        $expectedSignature = hash_hmac(
            'sha256',
            $headerEncoded . '.' . $payloadEncoded,
            $secret,
            true
        );

        if (!hash_equals($expectedSignature, $signature)) {
            throw new RuntimeException('Assinatura do token inválida.');
        }

        $headerJson = self::base64UrlDecode($headerEncoded);
        $payloadJson = self::base64UrlDecode($payloadEncoded);

        $header = json_decode($headerJson, true);
        $payload = json_decode($payloadJson, true);

        if (!is_array($header)) {
            throw new RuntimeException('Header do token inválido.');
        }

        if (!is_array($payload)) {
            throw new RuntimeException('Payload do token inválido.');
        }

        if (
            !isset($header['alg']) ||
            $header['alg'] !== 'HS256'
        ) {
            throw new RuntimeException('Algoritmo do token inválido.');
        }

        if (
            !isset($header['typ']) ||
            $header['typ'] !== 'JWT'
        ) {
            throw new RuntimeException('Tipo do token inválido.');
        }

        if (
            isset($payload['exp']) &&
            time() >= (int) $payload['exp']
        ) {
            throw new RuntimeException('Token expirado.');
        }

        return $payload;
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(
            strtr(base64_encode($data), '+/', '-_'),
            '='
        );
    }

    private static function base64UrlDecode(string $data): string
    {
        $data = strtr($data, '-_', '+/');

        $remainder = strlen($data) % 4;

        if ($remainder > 0) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        $decoded = base64_decode($data, true);

        if ($decoded === false) {
            throw new RuntimeException('Base64 inválido.');
        }

        return $decoded;
    }
}
