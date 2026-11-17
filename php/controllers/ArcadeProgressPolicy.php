<?php
declare(strict_types=1);

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
final class ArcadeProgressPolicy
{
    private const GRACE_SECONDS = 3;

    /**
     * @param array{score_per_second:int,seconds_per_level:int,score_per_level?:int} $limits
     */
    public static function isPlausible(
        int $score,
        int $level,
        int $elapsedSeconds,
        int $previousScore,
        int $previousLevel,
        array $limits
    ): bool {
        // Rejeita valores negativos, reversão de progresso ou valores absurdos.
        if ($score < 0 || $score > 100000000 || $level < 1 || $level > 100000
            || $score < $previousScore || $level < $previousLevel
            || $elapsedSeconds < 0 || $elapsedSeconds > 21600
        ) {
            return false;
        }

        // Não aceitar taxas sem sentido fornecidas por engano na configuração.
        $scoreRate = (int) ($limits['score_per_second'] ?? 0);
        $levelPeriod = (int) ($limits['seconds_per_level'] ?? 0);
        if ($scoreRate <= 0 || $levelPeriod <= 0) {
            return false;
        }

        // A tolerância é pequena e fixa: nunca cresce com a pontuação enviada.
        $effectiveSeconds = $elapsedSeconds + self::GRACE_SECONDS;
        if ($score > $effectiveSeconds * $scoreRate
            || $level > 1 + (int) floor($effectiveSeconds / $levelPeriod)
        ) {
            return false;
        }

        // Os oito jogos adicionais avançam de nível exclusivamente por pontos.
        // Isso impede, por exemplo, enviar fase 50 com apenas cinco pontos.
        $pointsPerLevel = (int) ($limits['score_per_level'] ?? 0);
        if ($pointsPerLevel > 0 && $level > 1 + (int) floor($score / $pointsPerLevel)) {
            return false;
        }

        return true;
    }
}
