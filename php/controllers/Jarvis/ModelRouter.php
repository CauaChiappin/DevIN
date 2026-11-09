<?php
declare(strict_types=1);

/**
 * Roteador de modelos do JARVIS Admin. Para OpenRouter somente modelos gratuitos,
 * com no máximo 10 reservas em ordem, e fallback nativo em uma chamada HTTP.
 */
final class ModelRouter
{
    private const DEFAULT_FREE_BACKUPS = [
        'google/gemma-4-26b-a4b-it:free',
        'google/gemma-4-31b-it:free',
        'nvidia/nemotron-3.5-lightning:free',
        'nvidia/nemotron-3.5-super-vl:free',
        'dots-studio/dots-3-note-preview:free',
        'cohere/north-mini-code:free',
        'recursive/flint-v1.0:free',
        'liquid/lfm-3-1.7b:free',
        'liquid/lfm-2.5-2.6b:free',
        'openrouter/free',
    ];

    public static function validModel(string $id): bool
    {
        return preg_match('~^[a-zA-Z0-9._:/-]{1,120}$~D', $id) === 1;
    }

    public static function isFree(string $id): bool
    {
        return $id === 'openrouter/free' || str_ends_with($id, ':free');
    }

    /**
     * @return list<string> 1 modelo principal + no máximo 10 backups.
     * $legacy é compatível com a variável antiga DEVIN_JARVIS_FALLBACK_MODEL.
     * O CSV novo tem prioridade sobre o legado.
     */
    public static function candidates(
        string $provider,
        string $primary,
        ?string $legacy = null,
        ?string $csv = null
    ): array {
        if (!self::validModel($primary)) {
            throw new InvalidArgumentException('Modelo principal inválido.');
        }
        if ($provider !== 'openrouter') {
            return [$primary];
        }
        if (!self::isFree($primary)) {
            throw new InvalidArgumentException('Modelo principal precisa ser gratuito.');
        }
        $ids = self::DEFAULT_FREE_BACKUPS;
        if (is_string($csv) && trim($csv) !== '') {
            $ids = preg_split('/[,\s]+/', trim($csv), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        } elseif (is_string($legacy) && trim($legacy) !== '') {
            $old = trim($legacy);
            if (self::validModel($old) && self::isFree($old)) {
                $ids = [$old];
            }
            // Valores inválidos (inclusive segredos inseridos por engano)
            // são ignorados; mantém os backups seguros padrão.
        }

        $result = [$primary];
        foreach ($ids as $id) {
            $id = trim((string) $id);
            if (!self::validModel($id) || !self::isFree($id)
                || in_array($id, $result, true)) continue;
            $result[] = $id;
            if (count($result) >= 11) break;
        }
        return $result;
    }

    /** A chave de reserva só é tentada em falhas recuperáveis ou da chave primária. */
    public static function shouldTrySecondKey(int $status): bool
    {
        return $status === 0 || $status === 401 || $status === 402
            || $status === 403 || $status === 408 || $status === 429
            || ($status >= 500 && $status <= 599);
    }

    /** Mantém compatibilidade com código/testes da versão antiga. */
    public static function shouldFallback(int $status): bool
    {
        return self::shouldTrySecondKey($status);
    }
}
