<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

/**
 * Validação independente da interface para recordes do Arcade DevIN.
 *
 * Os pontos chegam do navegador, portanto não são prova de uma partida real.
 * Esta política reduz valores manifestamente inconsistentes; para antitrapaça
 * forte seria preciso validar eventos de jogo ou executar partidas no servidor.
 *
 * A tolerância de três segundos cobre a resolução de time() e atrasos de rede,
 * evitando rejeitar progresso legítimo logo após iniciar a partida.
 */
final class ArcadeProgressPolicy // Explicação DevIN: Declara o tipo ArcadeProgressPolicy e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    private const GRACE_SECONDS = 3; // Explicação DevIN: Define membro, visibilidade ou método de uma classe.

    /**
     * @param array{score_per_second:int,seconds_per_level:int,score_per_level?:int} $limits
     */
    public static function isPlausible( // Explicação DevIN: Declara função/método isPlausible, com seus parâmetros e comportamento.
        int $score, // Explicação DevIN: Continua expressão ou bloco pertencente a função isPlausible.
        int $level, // Explicação DevIN: Continua expressão ou bloco pertencente a função isPlausible.
        int $elapsedSeconds, // Explicação DevIN: Continua expressão ou bloco pertencente a função isPlausible.
        int $previousScore, // Explicação DevIN: Continua expressão ou bloco pertencente a função isPlausible.
        int $previousLevel, // Explicação DevIN: Continua expressão ou bloco pertencente a função isPlausible.
        array $limits // Explicação DevIN: Continua expressão ou bloco pertencente a função isPlausible.
    ): bool { // Explicação DevIN: Continua expressão ou bloco pertencente a função isPlausible.
        // Rejeita valores negativos, reversão de progresso ou valores absurdos.
        if ($score < 0 || $score > 100000000 || $level < 1 || $level > 100000 // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            || $score < $previousScore || $level < $previousLevel // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            || $elapsedSeconds < 0 || $elapsedSeconds > 21600 // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        ) { // Explicação DevIN: Continua expressão ou bloco pertencente a função isPlausible.
            return false; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        // Não aceitar taxas sem sentido fornecidas por engano na configuração.
        $scoreRate = (int) ($limits['score_per_second'] ?? 0); // Explicação DevIN: Atribui um valor à variável PHP $scoreRate.
        $levelPeriod = (int) ($limits['seconds_per_level'] ?? 0); // Explicação DevIN: Atribui um valor à variável PHP $levelPeriod.
        if ($scoreRate <= 0 || $levelPeriod <= 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            return false; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        // A tolerância é pequena e fixa: nunca cresce com a pontuação enviada.
        $effectiveSeconds = $elapsedSeconds + self::GRACE_SECONDS; // Explicação DevIN: Atribui um valor à variável PHP $effectiveSeconds.
        if ($score > $effectiveSeconds * $scoreRate // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            || $level > 1 + (int) floor($effectiveSeconds / $levelPeriod) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        ) { // Explicação DevIN: Continua expressão ou bloco pertencente a função isPlausible.
            return false; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        // Os oito jogos adicionais avançam de nível exclusivamente por pontos.
        // Isso impede, por exemplo, enviar fase 50 com apenas cinco pontos.
        $pointsPerLevel = (int) ($limits['score_per_level'] ?? 0); // Explicação DevIN: Atribui um valor à variável PHP $pointsPerLevel.
        if ($pointsPerLevel > 0 && $level > 1 + (int) floor($score / $pointsPerLevel)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            return false; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        return true; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
