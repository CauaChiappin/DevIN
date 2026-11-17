<?php
declare(strict_types=1);

/**
 * Contrato de resposta de IA para a revisão orientativa de currículos.
 *
 * A ABNT não estabelece um modelo obrigatório para currículos; este módulo
 * apenas verifica estrutura e tamanho do JSON gerado pelo provedor.
 * A saída continua sendo orientação, não certificação de conformidade.
 */
final class ResumeReviewResult
{
    public static function normalize(mixed $review): array
    {
        if (!is_array($review)
            || !is_string($review['resumo'] ?? null)
            || !is_string($review['conclusao'] ?? null)
            || !is_array($review['pontos'] ?? null)) {
            throw new UnexpectedValueException('Formato de revisão inválido.');
        }

        // Limita o tamanho de texto devolvido pelo modelo para a interface.
        $summary = mb_substr(trim($review['resumo']), 0, 1800, 'UTF-8');
        $conclusion = mb_substr(trim($review['conclusao']), 0, 1000, 'UTF-8');
        if ($summary === '' || $conclusion === '') {
            throw new UnexpectedValueException('A revisão não contém resumo e conclusão válidos.');
        }

        // Cada recomendação precisa ter título e orientação de texto.
        // Itens malformados são descartados sem expor erros do provedor.
        $points = [];
        foreach ($review['pontos'] as $item) {
            if (!is_array($item)
                || !is_string($item['titulo'] ?? null)
                || !is_string($item['orientacao'] ?? null)) {
                continue;
            }
            $title = mb_substr(trim($item['titulo']), 0, 160, 'UTF-8');
            $guidance = mb_substr(trim($item['orientacao']), 0, 1000, 'UTF-8');
            if ($title === '' || $guidance === '') {
                continue;
            }
            $points[] = ['titulo' => $title, 'orientacao' => $guidance];
            if (count($points) >= 8) {
                break;
            }
        }

        return ['resumo' => $summary, 'conclusao' => $conclusion, 'pontos' => $points];
    }
}
