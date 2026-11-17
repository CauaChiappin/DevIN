<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

require_once dirname(__DIR__) . '/php/controllers/Jarvis/JobLanguageAudit.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

function assertAudit(bool $value, string $label): void // Explicação DevIN: Declara função/método assertAudit, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if (!$value) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new RuntimeException('Falha na triagem: ' . $label); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

assertAudit(JobLanguageAudit::requested('Verifique vagas com linguagem ofensiva'), 'pedido de triagem'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertAudit.
assertAudit(JobLanguageAudit::requested('Analise vagas discriminatórias'), 'acentos'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertAudit.
assertAudit(!JobLanguageAudit::requested('Quantas vagas temos?'), 'pergunta comum não deve acionar auditoria'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertAudit.

$safe = JobLanguageAudit::reviewText('Desenvolvedor PHP', // Explicação DevIN: Atribui um valor à variável PHP $safe.
    'Contratamos pessoas de qualquer gênero e pessoas com deficiência, sem restrição de idade.'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
assertAudit($safe === [], 'anúncio inclusivo'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertAudit.

$cases = [ // Explicação DevIN: Atribui um valor à variável PHP $cases.
    ['Buscamos apenas homens', '', 'Possível exclusão por gênero'], // Explicação DevIN: Continua expressão ou bloco pertencente a função assertAudit.
    ['Vaga com idade máxima 25 anos', '', 'Possível exclusão por idade'], // Explicação DevIN: Continua expressão ou bloco pertencente a função assertAudit.
    ['Empresa não aceita PCD', 'Não aceitamos pessoas com deficiência.', 'Possível exclusão por deficiência'], // Explicação DevIN: Continua expressão ou bloco pertencente a função assertAudit.
    ['Vaga para imbecil', '', 'Possível linguagem insultuosa'], // Explicação DevIN: Continua expressão ou bloco pertencente a função assertAudit.
    ['Requisito: boa aparência', '', 'Exigência subjetiva que merece revisão'], // Explicação DevIN: Continua expressão ou bloco pertencente a função assertAudit.
]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
foreach ($cases as [$title,$description,$expected]) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
    $hits = JobLanguageAudit::reviewText($title, $description); // Explicação DevIN: Atribui um valor à variável PHP $hits.
    assertAudit(in_array($expected, array_column($hits, 'categoria'), true), $expected); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertAudit.
    foreach ($hits as $hit) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
        assertAudit(mb_strlen($hit['trecho'], 'UTF-8') <= 150, 'trecho limitado'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertAudit.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

$partialReport = JobLanguageAudit::format([ // Explicação DevIN: Atribui um valor à variável PHP $partialReport.
    'total'=>2300, 'analisadas'=>2000, 'sinalizadas'=>1, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'amostra'=>[['id'=>31,'titulo'=>'Anúncio de teste','indicadores'=>[ // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ['categoria'=>'Possível linguagem insultuosa','campo'=>'descrição','trecho'=>'imbecil'] // Explicação DevIN: Continua expressão ou bloco pertencente a função assertAudit.
    ]]], // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
    'parcial'=>true // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
]); // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
assertAudit(str_contains($partialReport, 'varredura parcial'), 'alerta de cobertura'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertAudit.
assertAudit(str_contains($partialReport, 'Nenhuma vaga foi editada'), 'read-only e revisão humana'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertAudit.

echo "Job Language Audit Extended: OK\n"; // Explicação DevIN: Produz saída ou conteúdo da resposta do programa.
