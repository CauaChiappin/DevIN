<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

/**
 * Contrato de resposta de IA para a revisão orientativa de currículos.
 *
 * A ABNT não estabelece um modelo obrigatório para currículos; este módulo
 * apenas verifica estrutura e tamanho do JSON gerado pelo provedor.
 * A saída continua sendo orientação, não certificação de conformidade.
 */
final class ResumeReviewResult // Explicação DevIN: Declara o tipo ResumeReviewResult e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    public static function normalize(mixed $review): array // Explicação DevIN: Declara função/método normalize, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!is_array($review) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            || !is_string($review['resumo'] ?? null) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            || !is_string($review['conclusao'] ?? null) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            || !is_array($review['pontos'] ?? null)) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            throw new UnexpectedValueException('Formato de revisão inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        // Limita o tamanho de texto devolvido pelo modelo para a interface.
        $summary = mb_substr(trim($review['resumo']), 0, 1800, 'UTF-8'); // Explicação DevIN: Atribui um valor à variável PHP $summary.
        $conclusion = mb_substr(trim($review['conclusao']), 0, 1000, 'UTF-8'); // Explicação DevIN: Atribui um valor à variável PHP $conclusion.
        if ($summary === '' || $conclusion === '') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new UnexpectedValueException('A revisão não contém resumo e conclusão válidos.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        // Cada recomendação precisa ter título e orientação de texto.
        // Itens malformados são descartados sem expor erros do provedor.
        $points = []; // Explicação DevIN: Atribui um valor à variável PHP $points.
        foreach ($review['pontos'] as $item) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            if (!is_array($item) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                || !is_string($item['titulo'] ?? null) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                || !is_string($item['orientacao'] ?? null)) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                continue; // Explicação DevIN: Controla a continuação ou saída de laço/seleção.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $title = mb_substr(trim($item['titulo']), 0, 160, 'UTF-8'); // Explicação DevIN: Atribui um valor à variável PHP $title.
            $guidance = mb_substr(trim($item['orientacao']), 0, 1000, 'UTF-8'); // Explicação DevIN: Atribui um valor à variável PHP $guidance.
            if ($title === '' || $guidance === '') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                continue; // Explicação DevIN: Controla a continuação ou saída de laço/seleção.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $points[] = ['titulo' => $title, 'orientacao' => $guidance]; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            if (count($points) >= 8) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                break; // Explicação DevIN: Controla a continuação ou saída de laço/seleção.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        return ['resumo' => $summary, 'conclusao' => $conclusion, 'pontos' => $points]; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
