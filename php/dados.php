<?php
// ============================================
// DevIN - Dados e Configurações
// ============================================

$site = [
    'nome'     => 'DevIN',
    'email'    => 'devin@gmail.com',
    'telefone' => '11 xxxxx-xxxx',
    'ano'      => date('Y'),
    'escola'   => 'Escola Profª Alcina Dantas Feijão',
];

$nav_links = [
    ['href' => '#sobre',    'label' => 'Conheça o DevIN'],
    ['href' => '#etapas',   'label' => 'Etapas'],
    ['href' => '#contato',  'label' => 'Contato'],
];

$features = [
    [
        'titulo'    => 'Currículo',
        'descricao' => 'Crie o seu currículo na DevIN e use para candidatar-se para vagas de empresas',
        'lado'      => 'direita',
    ],
    [
        'titulo'    => 'Feed de vagas',
        'descricao' => 'Explore por um feed de vagas, com inúmeras oportunidades de trabalho, como jovem aprendiz, estágios e empregos.',
        'lado'      => 'esquerda',
    ],
];

$etapas = [
    ['numero' => 1, 'label' => 'Crie sua conta', 'ativo' => false],
    ['numero' => 2, 'label' => 'Currículo',       'ativo' => false],
    ['numero' => 3, 'label' => 'Candidatar-se',   'ativo' => true],
];

$empresas = [
    ['tipo' => 'nu',      'nome' => 'Nubank'],
    ['tipo' => 'itau',    'nome' => 'Itaú'],
    ['tipo' => 'generic', 'nome' => 'EmpresaIA'],
    ['tipo' => 'generic', 'nome' => 'Empresa'],
    ['tipo' => 'generic', 'nome' => 'Empresa'],
    ['tipo' => 'generic', 'nome' => 'Startup'],
    ['tipo' => 'generic', 'nome' => 'TechCorp'],
];

$logos_small = [
    'Spark', 'Gale', 'Lumen', 'Trajector', 'Kindle',
    'Apogee', 'Stellar', 'Zephyr', 'Tome', 'Summit',
    'Evergreen', 'Bedrock', 'Heartwood', 'Alpine', 'Cairn', 'Reservoir',
];

$faqs = [
    [
        'pergunta' => 'Dúvida 1',
        'resposta' => 'Aqui vai a resposta detalhada para a dúvida 1. Nosso time está pronto para ajudar você a encontrar a melhor vaga.',
    ],
    [
        'pergunta' => 'Dúvida 2',
        'resposta' => 'Aqui vai a resposta detalhada para a dúvida 2. O DevIN facilita todo o processo de candidatura.',
    ],
    [
        'pergunta' => 'Dúvida 3',
        'resposta' => 'Aqui vai a resposta detalhada para a dúvida 3. Entre em contato conosco caso precise de mais informações.',
    ],
];

$footer_links = [
    'Conheça o DevIN' => ['#sobre', '#curriculo', '#feed'],
    'Etapas'          => ['#conta', '#curriculo', '#candidatura'],
    'Contato'         => [$site['email'], $site['telefone']],
];
?>