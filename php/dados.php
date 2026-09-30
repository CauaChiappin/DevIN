<?php

declare(strict_types=1);

// ============================================
// DevIN - Dados e Configurações da Landing Page
// ============================================

$site = [
    'nome'     => 'DevIN',
    'email'    => 'contato@devin.com.br',
    'telefone' => '(11) 99999-9999',
    'ano'      => (int) date('Y'),
    'escola'   => 'Escola Profª Alcina Dantas Feijão',
];

$nav_links = [
    ['href' => '#sobre',   'label' => 'Conheça o DevIN'],
    ['href' => '#etapas',  'label' => 'Etapas'],
    ['href' => '#contato', 'label' => 'Contato'],
];

$features = [
    [
        'titulo'    => 'Currículo',
        'descricao' => 'Crie o seu currículo na DevIN e use para candidatar-se às vagas das empresas parceiras.',
        'lado'      => 'direita',
    ],
    [
        'titulo'    => 'Feed de vagas',
        'descricao' => 'Explore um feed com inúmeras oportunidades de trabalho, como jovem aprendiz, estágios e empregos.',
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
        'pergunta' => 'Como faço para me candidatar a uma vaga?',
        'resposta' => 'Crie sua conta de candidato, preencha o formulário com seus dados e currículo e acesse a aba de vagas para se candidatar às oportunidades disponíveis.',
    ],
    [
        'pergunta' => 'O DevIN é gratuito para os candidatos?',
        'resposta' => 'Sim! O cadastro na plataforma e a candidatura às vagas publicadas pelas empresas parceiras são 100% gratuitos.',
    ],
    [
        'pergunta' => 'Como as empresas entram em contato comigo?',
        'resposta' => 'As empresas analisam os perfis direto no painel e podem atualizar o status da sua candidatura ou entrar em contato através do e-mail e telefone cadastrados.',
    ],
];

$footer_links = [
    'Conheça o DevIN' => ['#sobre', '#curriculo', '#feed'],
    'Etapas'          => ['#conta', '#curriculo', '#candidatura'],
    'Contato'         => [$site['email'], $site['telefone']],
];