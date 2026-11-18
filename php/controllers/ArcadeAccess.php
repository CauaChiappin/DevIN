<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Gerencia a validade temporal e a identidade da sessão do Arcade.

/**
 * Liberação temporária do Arcade vinculada à sessão autenticada.
 * Os gestos secretos são recursos de descoberta e não substituem o login.
 */
final class ArcadeAccess // Gerencia a validade temporal e a identidade da sessão do Arcade.
{ // Gerencia a validade temporal e a identidade da sessão do Arcade.
    private const MAX_AGE_SECONDS = 7200; // Gerencia a validade temporal e a identidade da sessão do Arcade.

    public static function grant(array $usuario): void // Gerencia a validade temporal e a identidade da sessão do Arcade.
    { // Gerencia a validade temporal e a identidade da sessão do Arcade.
        $_SESSION['arcade_unlock'] = [ // Gerencia a validade temporal e a identidade da sessão do Arcade.
            'id' => (int) $usuario['id'], // Gerencia a validade temporal e a identidade da sessão do Arcade.
            'tipo' => (string) $usuario['tipo'], // Gerencia a validade temporal e a identidade da sessão do Arcade.
            'liberado_em' => time(), // Gerencia a validade temporal e a identidade da sessão do Arcade.
        ]; // Gerencia a validade temporal e a identidade da sessão do Arcade.
    } // Gerencia a validade temporal e a identidade da sessão do Arcade.

    public static function isUnlocked(array $usuario): bool // Gerencia a validade temporal e a identidade da sessão do Arcade.
    { // Gerencia a validade temporal e a identidade da sessão do Arcade.
        $unlock = $_SESSION['arcade_unlock'] ?? null; // Gerencia a validade temporal e a identidade da sessão do Arcade.
        $timestamp = is_array($unlock) ? ($unlock['liberado_em'] ?? null) : null; // Gerencia a validade temporal e a identidade da sessão do Arcade.
        $elapsed = is_int($timestamp) ? time() - $timestamp : -1; // Gerencia a validade temporal e a identidade da sessão do Arcade.
        $valid = is_array($unlock) // Gerencia a validade temporal e a identidade da sessão do Arcade.
            && (int) ($unlock['id'] ?? 0) === (int) $usuario['id'] // Gerencia a validade temporal e a identidade da sessão do Arcade.
            && (string) ($unlock['tipo'] ?? '') === (string) $usuario['tipo'] // Gerencia a validade temporal e a identidade da sessão do Arcade.
            && $elapsed >= 0 // Gerencia a validade temporal e a identidade da sessão do Arcade.
            && $elapsed < self::MAX_AGE_SECONDS; // Gerencia a validade temporal e a identidade da sessão do Arcade.
        if (!$valid) { // Gerencia a validade temporal e a identidade da sessão do Arcade.
            unset($_SESSION['arcade_unlock']); // Gerencia a validade temporal e a identidade da sessão do Arcade.
        } // Gerencia a validade temporal e a identidade da sessão do Arcade.
        return $valid; // Gerencia a validade temporal e a identidade da sessão do Arcade.
    } // Gerencia a validade temporal e a identidade da sessão do Arcade.
} // Gerencia a validade temporal e a identidade da sessão do Arcade.
