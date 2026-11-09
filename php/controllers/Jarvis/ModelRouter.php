<?php
declare(strict_types=1);

/**
 * Roteamento mínimo do JARVIS DevIN: só modelos explicitamente configurados,
 * sempre no MESMO provedor. Nunca ativa modelos pagos automaticamente.
 */
final class ModelRouter
{
    private static function validModel(string $model): bool
    {
        return preg_match('~^[a-zA-Z0-9._:/-]{1,120}$~D', $model) === 1;
    }

    /** @return list<string> */
    public static function candidates(string $provider, string $primary, ?string $fallback): array
    {
        if (!self::validModel($primary)) {
            throw new RuntimeException('ID de modelo principal inválido.');
        }
        $ordered = [$primary];
        $fallback = trim($fallback ?? '');
        // Fallback só é aceito quando explicitamente configurado e claramente marcado como gratuito.
        // Não alterna credenciais, provedores ou endpoints; não faz descoberta remota.
        if ($provider === 'openrouter' && $fallback !== '' && $fallback !== $primary
            && self::validModel($fallback) && str_ends_with($fallback, ':free')) {
            $ordered[] = $fallback;
        }
        return $ordered;
    }

    /** Falhas transitórias: tentamos um fallback gratuito quando disponível. */
    public static function shouldFallback(int $status): bool
    {
        return $status === 0 || $status === 408 || $status === 429 || ($status >= 500 && $status <= 599);
    }
}
