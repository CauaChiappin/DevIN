<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
require_once __DIR__ . '/../controllers/AdminOverview.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
$adminOverview = []; // Explicação DevIN: Atribui um valor à variável PHP $adminOverview.
try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    $adminDb = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $adminDb.
    try { $adminOverview = devinAdminOverview($adminDb); } finally { $adminDb->close(); } // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
} catch (Throwable $error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    error_log('DevIN ADM: estatísticas indisponíveis (' . get_class($error) . ').'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$words = [ // Explicação DevIN: Atribui um valor à variável PHP $words.
    'pt-BR' => ['title'=>'Visão geral do DevIN', 'intro'=>'Indicadores atualizados ao abrir a página. Recursos que ainda não estão configurados aparecem como indisponíveis.', 'companies'=>'Empresas', 'people'=>'Pessoas', 'jobs'=>'Vagas', 'applications'=>'Candidaturas', 'hired'=>'Contratações confirmadas', 'reports'=>'Denúncias pendentes', 'support'=>'Suporte aberto', 'rank'=>'Empresas com mais vagas', 'none'=>'Nenhuma empresa cadastrada.', 'missing'=>'Indisponível'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'en' => ['title'=>'DevIN overview', 'intro'=>'Indicators refresh when you open this page. Features not yet configured are marked unavailable.', 'companies'=>'Companies', 'people'=>'People', 'jobs'=>'Jobs', 'applications'=>'Applications', 'hired'=>'Confirmed hires', 'reports'=>'Pending reports', 'support'=>'Open support tickets', 'rank'=>'Companies with the most jobs', 'none'=>'No companies registered.', 'missing'=>'Unavailable'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'es' => ['title'=>'Resumen de DevIN', 'intro'=>'Los indicadores se actualizan al abrir esta página. Los recursos sin configurar se muestran como no disponibles.', 'companies'=>'Empresas', 'people'=>'Personas', 'jobs'=>'Empleos', 'applications'=>'Postulaciones', 'hired'=>'Contrataciones confirmadas', 'reports'=>'Denuncias pendientes', 'support'=>'Soporte abierto', 'rank'=>'Empresas con más empleos', 'none'=>'No hay empresas registradas.', 'missing'=>'No disponible'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
$labels = $words[$idiomaAtual] ?? $words['pt-BR']; // Explicação DevIN: Atribui um valor à variável PHP $labels.
$metrics = [ // Explicação DevIN: Atribui um valor à variável PHP $metrics.
    ['companies', 'empresas'], ['people', 'pessoas'], ['jobs', 'vagas'], ['applications', 'candidaturas'], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    ['hired', 'contratacoes'], ['reports', 'denuncias_pendentes'], ['support', 'suporte_aberto'], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
?>
<section class="admin-overview" aria-label="<?= h($labels['title']) ?>"> <!-- Explicação DevIN: Declara seção temática. Inclui atributos de acessibilidade. Contém dado dinâmico processado pelo PHP. -->
    <header class="admin-overview-header"> <!-- Explicação DevIN: Declara cabeçalho da seção ou página. -->
        <h1><?= h($labels['title']) ?></h1> <!-- Explicação DevIN: Declara título principal. Contém dado dinâmico processado pelo PHP. -->
        <p><?= h($labels['intro']) ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
    </header> <!-- Explicação DevIN: Fecha elemento header do documento. -->
    <div class="admin-overview-grid"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
        <?php foreach ($metrics as [$caption, $key]): ?>
            <?php $metricAvailable = isset($adminOverview[$key]); // Distingue métrica ausente de valor numérico zero. ?>
            <article class="admin-metric"> // Explicação DevIN: Declara conteúdo independente.
                <h2><?= h($labels[$caption]) ?></h2>
                <?php if ($metricAvailable): ?>
                    <strong class="admin-metric__value"><?= number_format((int) $adminOverview[$key], 0, ',', '.') ?></strong> <!-- Explicação DevIN: Declara elemento de marcação. Contém dado dinâmico processado pelo PHP. -->
                <?php else: ?>
                    <span class="admin-metric__unavailable" role="status"><?= h($labels['missing']) ?></span> <!-- Explicação DevIN: Declara trecho em linha. Contém dado dinâmico processado pelo PHP. -->
                <?php endif; ?>
            </article> <!-- Explicação DevIN: Fecha elemento article do documento. -->
        <?php endforeach; ?>
    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
    <section class="admin-overview-ranking"> <!-- Explicação DevIN: Declara seção temática. -->
        <h2><?= h($labels['rank']) ?></h2> <!-- Explicação DevIN: Declara subtítulo de segundo nível. Contém dado dinâmico processado pelo PHP. -->
        <?php if (empty($adminOverview['top_empresas'])): ?>
            <p><?= h($labels['none']) ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
        <?php else: ?>
            <ol> <!-- Explicação DevIN: Declara elemento de marcação. -->
            <?php foreach ($adminOverview['top_empresas'] as $row): ?>
                <li><span><?= h((string) $row['nome']) ?></span><strong><?= (int) $row['vagas'] ?> <?= h($labels['jobs']) ?></strong></li> <!-- Explicação DevIN: Declara item da lista. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
            <?php endforeach; ?>
            </ol> <!-- Explicação DevIN: Fecha elemento ol do documento. -->
        <?php endif; ?>
    </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->
</section> <!-- Explicação DevIN: Fecha elemento section do documento. -->
