<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/php/controllers/ArcadeProgressPolicy.php';

// Casos representativos: sem acesso ao banco e sem alterar recordes reais.
function assertArcade(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException('Falha arcade: ' . $message);
    }
}

$games = [
    'pong' => [1200,3,125], 'dodge' => [120,5,150],
    'flappy' => [900,7,120], 'target' => [1800,1,140],
    'collector' => [1800,1,150], 'racer' => [300,2,200],
    'simon' => [1400,5,120], 'basket' => [1800,4,140],
];

foreach ($games as $game => [$rate,$period,$threshold]) {
    $limits = ['score_per_second'=>$rate,'seconds_per_level'=>$period,'score_per_level'=>$threshold];
    assertArcade(ArcadeProgressPolicy::isPlausible(0,1,0,0,1,$limits), $game . ' início');
    assertArcade(ArcadeProgressPolicy::isPlausible($threshold,2,30,0,1,$limits),
        $game . ' primeira evolução legítima');
    assertArcade(!ArcadeProgressPolicy::isPlausible(0,2,30,0,1,$limits),
        $game . ' nível sem pontos');
    assertArcade(!ArcadeProgressPolicy::isPlausible(2000000,1,1,0,1,$limits),
        $game . ' pontuação impossível');
    assertArcade(!ArcadeProgressPolicy::isPlausible($threshold-1,1,10,$threshold,1,$limits),
        $game . ' regressão de pontos');
}
assertArcade(!ArcadeProgressPolicy::isPlausible(50,1,0,0,1,
    ['score_per_second'=>0,'seconds_per_level'=>1]),'configuração inválida');
assertArcade(!ArcadeProgressPolicy::isPlausible(50,1,30000,0,1,
    ['score_per_second'=>200,'seconds_per_level'=>1]),'sessão excessiva');

echo "Arcade Progress Policy: OK\n";
