<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/dados.php
 * Finalidade: dados.
 * Domínio: processamento no servidor.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


// ============================================
// DevIN - Dados e Configurações da Landing Page
// ============================================

// Dados compartilhados pela landing page para manter textos de contato em um único lugar.
$site = [ // Explicação DevIN: Atribui um valor à variável PHP $site.
    'nome'     => 'DevIN', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'email'    => 'contato@devin.com.br', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'telefone' => '(11) 99999-9999', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'ano'      => (int) date('Y'), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'escola'   => 'Escola Profª Alcina Dantas Feijão', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.

$nav_links = [ // Explicação DevIN: Atribui um valor à variável PHP $nav_links.
    ['href' => '#sobre',   'label' => 'Conheça o DevIN'], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    ['href' => '#etapas',  'label' => 'Etapas'], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    ['href' => '#contato', 'label' => 'Contato'], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.

$features = [ // Explicação DevIN: Atribui um valor à variável PHP $features.
    [ // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        'titulo'    => 'Currículo', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'descricao' => 'Crie o seu currículo na DevIN e use para candidatar-se às vagas das empresas parceiras.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'lado'      => 'direita', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ], // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    [ // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        'titulo'    => 'Feed de vagas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'descricao' => 'Explore um feed com inúmeras oportunidades de trabalho, como jovem aprendiz, estágios e empregos.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'lado'      => 'esquerda', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ], // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.

$etapas = [ // Explicação DevIN: Atribui um valor à variável PHP $etapas.
    ['numero' => 1, 'label' => 'Crie sua conta', 'ativo' => false], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    ['numero' => 2, 'label' => 'Currículo',       'ativo' => false], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    ['numero' => 3, 'label' => 'Candidatar-se',   'ativo' => true], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.

$empresas = [ // Explicação DevIN: Atribui um valor à variável PHP $empresas.
    ['tipo' => 'nu',      'nome' => 'Nubank'], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    ['tipo' => 'itau',    'nome' => 'Itaú'], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    ['tipo' => 'generic', 'nome' => 'EmpresaIA'], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    ['tipo' => 'generic', 'nome' => 'Empresa'], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    ['tipo' => 'generic', 'nome' => 'Empresa'], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    ['tipo' => 'generic', 'nome' => 'Startup'], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    ['tipo' => 'generic', 'nome' => 'TechCorp'], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.

$logos_small = [ // Explicação DevIN: Atribui um valor à variável PHP $logos_small.
    'Spark', 'Gale', 'Lumen', 'Trajector', 'Kindle', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'Apogee', 'Stellar', 'Zephyr', 'Tome', 'Summit', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'Evergreen', 'Bedrock', 'Heartwood', 'Alpine', 'Cairn', 'Reservoir', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.

$faqs = [ // Explicação DevIN: Atribui um valor à variável PHP $faqs.
    [ // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        'pergunta' => 'Como faço para me candidatar a uma vaga?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'resposta' => 'Crie sua conta de candidato, preencha o formulário com seus dados e currículo e acesse a aba de vagas para se candidatar às oportunidades disponíveis.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ], // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    [ // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        'pergunta' => 'O DevIN é gratuito para os candidatos?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'resposta' => 'Sim! O cadastro na plataforma e a candidatura às vagas publicadas pelas empresas parceiras são 100% gratuitos.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ], // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    [ // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        'pergunta' => 'Como as empresas entram em contato comigo?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'resposta' => 'As empresas analisam os perfis direto no painel e podem atualizar o status da sua candidatura ou entrar em contato através do e-mail e telefone cadastrados.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ], // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.

$footer_links = [ // Explicação DevIN: Atribui um valor à variável PHP $footer_links.
    'Conheça o DevIN' => ['#sobre', '#curriculo', '#feed'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'Etapas'          => ['#conta', '#curriculo', '#candidatura'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'Contato'         => [$site['email'], $site['telefone']], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
