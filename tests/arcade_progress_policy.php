<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

require_once dirname(__DIR__) . '/php/controllers/ArcadeProgressPolicy.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

// Casos representativos: sem acesso ao banco e sem alterar recordes reais.
function assertArcade(bool $condition, string $message): void // Explicação DevIN: Declara função/método assertArcade, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if (!$condition) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new RuntimeException('Falha arcade: ' . $message); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

$games = [ // Explicação DevIN: Atribui um valor à variável PHP $games.
    'pong' => [1200,3,125], 'dodge' => [120,5,150], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'flappy' => [900,7,120], 'target' => [1800,1,140], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'collector' => [1800,1,150], 'racer' => [300,2,200], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'simon' => [1400,5,120], 'basket' => [1800,4,140], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.

foreach ($games as $game => [$rate,$period,$threshold]) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
    $limits = ['score_per_second'=>$rate,'seconds_per_level'=>$period,'score_per_level'=>$threshold]; // Explicação DevIN: Atribui um valor à variável PHP $limits.
    assertArcade(ArcadeProgressPolicy::isPlausible(0,1,0,0,1,$limits), $game . ' início'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertArcade.
    assertArcade(ArcadeProgressPolicy::isPlausible($threshold,2,30,0,1,$limits), // Explicação DevIN: Continua expressão ou bloco pertencente a função assertArcade.
        $game . ' primeira evolução legítima'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertArcade.
    assertArcade(!ArcadeProgressPolicy::isPlausible(0,2,30,0,1,$limits), // Explicação DevIN: Continua expressão ou bloco pertencente a função assertArcade.
        $game . ' nível sem pontos'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertArcade.
    assertArcade(!ArcadeProgressPolicy::isPlausible(2000000,1,1,0,1,$limits), // Explicação DevIN: Continua expressão ou bloco pertencente a função assertArcade.
        $game . ' pontuação impossível'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertArcade.
    assertArcade(!ArcadeProgressPolicy::isPlausible($threshold-1,1,10,$threshold,1,$limits), // Explicação DevIN: Continua expressão ou bloco pertencente a função assertArcade.
        $game . ' regressão de pontos'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertArcade.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
assertArcade(!ArcadeProgressPolicy::isPlausible(50,1,0,0,1, // Explicação DevIN: Continua expressão ou bloco pertencente a função assertArcade.
    ['score_per_second'=>0,'seconds_per_level'=>1]),'configuração inválida'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertArcade.
assertArcade(!ArcadeProgressPolicy::isPlausible(50,1,30000,0,1, // Explicação DevIN: Continua expressão ou bloco pertencente a função assertArcade.
    ['score_per_second'=>200,'seconds_per_level'=>1]),'sessão excessiva'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertArcade.

echo "Arcade Progress Policy: OK\n"; // Explicação DevIN: Produz saída ou conteúdo da resposta do programa.
