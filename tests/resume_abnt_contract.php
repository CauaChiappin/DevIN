<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/php/controllers/ResumeReviewResult.php';

function assertReview(bool $value, string $case): void
{
    if (!$value) {
        throw new RuntimeException('Falha em revisão ABNT: ' . $case);
    }
}

$original = [
    'resumo' => '  Currículo estruturado  ',
    'conclusao' => '  Sugestões gerais, não certificação ABNT  ',
    'pontos' => array_fill(0, 13, ['titulo' => 'Título', 'orientacao' => 'Orientação']),
];
$normalized = ResumeReviewResult::normalize($original);
assertReview($normalized['resumo'] === 'Currículo estruturado', 'normalização do resumo');
assertReview($normalized['conclusao'] === 'Sugestões gerais, não certificação ABNT', 'conclusão');
assertReview(count($normalized['pontos']) === 8, 'limite de oito sugestões');

$filtered = ResumeReviewResult::normalize([
    'resumo' => str_repeat('R', 2400),
    'conclusao' => 'Orientação',
    'pontos' => [['titulo' => '', 'orientacao' => 'X'], ['titulo' => 'Bom', 'orientacao' => 'Continue']],
]);
assertReview(mb_strlen($filtered['resumo'], 'UTF-8') === 1800, 'limite de texto');
assertReview(count($filtered['pontos']) === 1, 'descarte de itens vazios');

foreach ([null, [], ['resumo'=>'','conclusao'=>'x','pontos'=>[]],
    ['resumo'=>'x','conclusao'=>'y','pontos'=>'texto']] as $invalid) {
    try {
        ResumeReviewResult::normalize($invalid);
        throw new RuntimeException('Resposta inválida aceita pelo validador.');
    } catch (UnexpectedValueException $expected) {
        // As respostas incompletas do provedor não devem ser enviadas à interface.
    }
}

$controller = file_get_contents(dirname(__DIR__) . '/php/analisar_curriculo_abnt_api.php');
foreach (['requireWebAuth(\'pessoa\')', 'verifyCsrfToken', 'resume_abnt_consent',
    'RateLimiter::consume', 'LIBXML_NONET', 'CURLOPT_SSL_VERIFYPEER',
    'ResumeReviewResult::normalize', "'application/pdf'", "'docx'"] as $required) {
    assertReview(str_contains($controller, $required), 'proteção ausente: ' . $required);
}
echo "Resume ABNT Contract: OK\n";
