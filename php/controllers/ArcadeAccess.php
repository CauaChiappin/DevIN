<?php
declare(strict_types=1);

/**
 * Liberação temporária do Arcade vinculada à sessão autenticada.
 * Os gestos secretos são recursos de descoberta e não substituem o login.
 */
final class ArcadeAccess
{
    private const MAX_AGE_SECONDS = 7200;

    public static function grant(array $usuario): void
    {
        $_SESSION['arcade_unlock'] = [
            'id' => (int) $usuario['id'],
            'tipo' => (string) $usuario['tipo'],
            'liberado_em' => time(),
        ];
    }

    public static function isUnlocked(array $usuario): bool
    {
        $unlock = $_SESSION['arcade_unlock'] ?? null;
        $timestamp = is_array($unlock) ? ($unlock['liberado_em'] ?? null) : null;
        $elapsed = is_int($timestamp) ? time() - $timestamp : -1;
        $valid = is_array($unlock)
            && (int) ($unlock['id'] ?? 0) === (int) $usuario['id']
            && (string) ($unlock['tipo'] ?? '') === (string) $usuario['tipo']
            && $elapsed >= 0
            && $elapsed < self::MAX_AGE_SECONDS;
        if (!$valid) {
            unset($_SESSION['arcade_unlock']);
        }
        return $valid;
    }
}
