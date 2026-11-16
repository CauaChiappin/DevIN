<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

/**
 * Roteador de modelos do JARVIS Admin. Para OpenRouter somente modelos gratuitos,
 * com no máximo 10 reservas em ordem, e fallback nativo em uma chamada HTTP.
 */
final class ModelRouter // Explicação DevIN: Declara o tipo ModelRouter e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    private const DEFAULT_FREE_BACKUPS = [ // Explicação DevIN: Define membro, visibilidade ou método de uma classe.
        'google/gemma-4-26b-a4b-it:free', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'google/gemma-4-31b-it:free', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'nvidia/nemotron-3.5-lightning:free', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'nvidia/nemotron-3.5-super-vl:free', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'dots-studio/dots-3-note-preview:free', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'cohere/north-mini-code:free', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'recursive/flint-v1.0:free', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'liquid/lfm-3-1.7b:free', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'nvidia/nemotron-3-ultra-550b-a55b:free', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'openrouter/free', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.

    public static function validModel(string $id): bool // Explicação DevIN: Declara função/método validModel, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return preg_match('~^[a-zA-Z0-9._:/-]{1,120}$~D', $id) === 1; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function isFree(string $id): bool // Explicação DevIN: Declara função/método isFree, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return $id === 'openrouter/free' || str_ends_with($id, ':free'); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /**
     * @return list<string> 1 modelo principal + no máximo 10 backups.
     * $legacy é compatível com a variável antiga DEVIN_JARVIS_FALLBACK_MODEL.
     * O CSV novo tem prioridade sobre o legado.
     */
    public static function candidates( // Explicação DevIN: Declara função/método candidates, com seus parâmetros e comportamento.
        string $provider, // Explicação DevIN: Continua expressão ou bloco pertencente a função candidates.
        string $primary, // Explicação DevIN: Continua expressão ou bloco pertencente a função candidates.
        ?string $legacy = null, // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        ?string $csv = null // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    ): array { // Explicação DevIN: Continua expressão ou bloco pertencente a função candidates.
        if (!self::validModel($primary)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Modelo principal inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if ($provider !== 'openrouter') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            return [$primary]; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!self::isFree($primary)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Modelo principal precisa ser gratuito.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $ids = self::DEFAULT_FREE_BACKUPS; // Explicação DevIN: Atribui um valor à variável PHP $ids.
        if (is_string($csv) && trim($csv) !== '') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $ids = preg_split('/[,\s]+/', trim($csv), -1, PREG_SPLIT_NO_EMPTY) ?: []; // Explicação DevIN: Atribui um valor à variável PHP $ids.
        } elseif (is_string($legacy) && trim($legacy) !== '') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $old = trim($legacy); // Explicação DevIN: Atribui um valor à variável PHP $old.
            if (self::validModel($old) && self::isFree($old)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                $ids = [$old]; // Explicação DevIN: Atribui um valor à variável PHP $ids.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            // Valores inválidos (inclusive segredos inseridos por engano)
            // são ignorados; mantém os backups seguros padrão.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $result = [$primary]; // Explicação DevIN: Atribui um valor à variável PHP $result.
        foreach ($ids as $id) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            $id = trim((string) $id); // Explicação DevIN: Atribui um valor à variável PHP $id.
            if (!self::validModel($id) || !self::isFree($id) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                || in_array($id, $result, true)) continue; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            $result[] = $id; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            if (count($result) >= 11) break; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return $result; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /** A chave de reserva só é tentada em falhas recuperáveis ou da chave primária. */
    public static function shouldTrySecondKey(int $status): bool // Explicação DevIN: Declara função/método shouldTrySecondKey, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return $status === 0 || $status === 401 || $status === 402 // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            || $status === 403 || $status === 408 || $status === 429 // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            || ($status >= 500 && $status <= 599); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /** Mantém compatibilidade com código/testes da versão antiga. */
    public static function shouldFallback(int $status): bool // Explicação DevIN: Declara função/método shouldFallback, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return self::shouldTrySecondKey($status); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
