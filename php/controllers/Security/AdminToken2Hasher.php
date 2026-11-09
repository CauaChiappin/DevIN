<?php
declare(strict_types=1);

/**
 * O Token 2 não expira e não possui limite de caracteres na interface.
 * SHA-256 reduz entradas longas a 64 caracteres ANTES do password_hash(),
 * evitando o truncamento silencioso em implementações bcrypt.
 * Há apenas um teto técnico de tamanho de requisição para evitar DoS.
 */
final class AdminToken2Hasher
{
    public const MAX_INPUT_BYTES = 65536;

    private static function digest(string $token): string
    {
        return hash('sha256', "DevIN/admin-token2/v1\0" . $token);
    }

    public static function create(string $token): string
    {
        if ($token === '' || strlen($token) > self::MAX_INPUT_BYTES) {
            throw new InvalidArgumentException('Token 2 inválido.');
        }
        $algorithm = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
        $stored = password_hash(self::digest($token), $algorithm);
        if (!is_string($stored)) throw new RuntimeException('Falha ao proteger credencial.');
        return $stored;
    }

    public static function verify(string $token, ?string $stored): bool
    {
        return is_string($stored) && $stored !== '' && $token !== ''
            && strlen($token) <= self::MAX_INPUT_BYTES
            && password_verify(self::digest($token), $stored);
    }
}
