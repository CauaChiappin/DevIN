<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/php/controllers/Jarvis/JobLanguageAudit.php';

function assertAudit(bool $value, string $label): void
{
    if (!$value) {
        throw new RuntimeException('Falha na triagem: ' . $label);
    }
}

assertAudit(JobLanguageAudit::requested('Verifique vagas com linguagem ofensiva'), 'pedido de triagem');
assertAudit(JobLanguageAudit::requested('Analise vagas discriminatórias'), 'acentos');
assertAudit(!JobLanguageAudit::requested('Quantas vagas temos?'), 'pergunta comum não deve acionar auditoria');

$safe = JobLanguageAudit::reviewText('Desenvolvedor PHP',
    'Contratamos pessoas de qualquer gênero e pessoas com deficiência, sem restrição de idade.');
assertAudit($safe === [], 'anúncio inclusivo');

$cases = [
    ['Buscamos apenas homens', '', 'Possível exclusão por gênero'],
    ['Vaga com idade máxima 25 anos', '', 'Possível exclusão por idade'],
    ['Empresa não aceita PCD', 'Não aceitamos pessoas com deficiência.', 'Possível exclusão por deficiência'],
    ['Vaga para imbecil', '', 'Possível linguagem insultuosa'],
    ['Requisito: boa aparência', '', 'Exigência subjetiva que merece revisão'],
];
foreach ($cases as [$title,$description,$expected]) {
    $hits = JobLanguageAudit::reviewText($title, $description);
    assertAudit(in_array($expected, array_column($hits, 'categoria'), true), $expected);
    foreach ($hits as $hit) {
        assertAudit(mb_strlen($hit['trecho'], 'UTF-8') <= 150, 'trecho limitado');
    }
}

$partialReport = JobLanguageAudit::format([
    'total'=>2300, 'analisadas'=>2000, 'sinalizadas'=>1,
    'amostra'=>[['id'=>31,'titulo'=>'Anúncio de teste','indicadores'=>[
        ['categoria'=>'Possível linguagem insultuosa','campo'=>'descrição','trecho'=>'imbecil']
    ]]],
    'parcial'=>true
]);
assertAudit(str_contains($partialReport, 'varredura parcial'), 'alerta de cobertura');
assertAudit(str_contains($partialReport, 'Nenhuma vaga foi editada'), 'read-only e revisão humana');

echo "Job Language Audit Extended: OK\n";
