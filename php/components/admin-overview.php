<?php // Explicação DevIN: Inicializa o componente administrativo de estatísticas.
declare(strict_types=1); // Explicação DevIN: Reforça tipagem de dados.
require_once __DIR__ . '/../controllers/AdminOverview.php'; // Explicação DevIN: Inclui consultas agregadas do painel.
$adminOverview = []; // Explicação DevIN: Reserva dados para o template.
try { // Explicação DevIN: Isola falhas de banco sem interromper a navegação de outras áreas.
    $adminDb = getDatabaseConnection(); // Explicação DevIN: Abre conexão exclusivamente para métricas.
    try { $adminOverview = devinAdminOverview($adminDb); } finally { $adminDb->close(); } // Explicação DevIN: Fecha conexão mesmo quando há erro.
} catch (Throwable $error) { // Explicação DevIN: Não exibe informações internas de infraestrutura ao visitante.
    error_log('DevIN ADM: estatísticas indisponíveis (' . get_class($error) . ').'); // Explicação DevIN: Registra falha de maneira segura.
} // Explicação DevIN: Finaliza carregamento das métricas.
$words = [ // Explicação DevIN: Localiza legendas sem depender de rede externa.
    'pt-BR' => ['title'=>'Visão geral do DevIN', 'intro'=>'Dados atualizados ao abrir esta página; informações sem migration aparecem como indisponíveis.', 'companies'=>'Empresas', 'people'=>'Pessoas', 'jobs'=>'Vagas', 'applications'=>'Candidaturas', 'hired'=>'Contratações confirmadas', 'reports'=>'Denúncias pendentes', 'support'=>'Suporte aberto', 'rank'=>'Empresas com mais vagas', 'none'=>'Nenhuma empresa cadastrada.', 'missing'=>'Indisponível'],
    'en' => ['title'=>'DevIN overview', 'intro'=>'Data is refreshed when this page opens; missing database tables appear as unavailable.', 'companies'=>'Companies', 'people'=>'People', 'jobs'=>'Jobs', 'applications'=>'Applications', 'hired'=>'Confirmed hires', 'reports'=>'Pending reports', 'support'=>'Open support tickets', 'rank'=>'Companies with the most jobs', 'none'=>'No companies registered.', 'missing'=>'Unavailable'],
    'es' => ['title'=>'Resumen de DevIN', 'intro'=>'Los datos se actualizan al abrir esta página; las tablas ausentes aparecen como no disponibles.', 'companies'=>'Empresas', 'people'=>'Personas', 'jobs'=>'Empleos', 'applications'=>'Postulaciones', 'hired'=>'Contrataciones confirmadas', 'reports'=>'Denuncias pendientes', 'support'=>'Soporte abierto', 'rank'=>'Empresas con más empleos', 'none'=>'No hay empresas registradas.', 'missing'=>'No disponible'],
]; // Explicação DevIN: Encerra traduções das métricas.
$labels = $words[$idiomaAtual] ?? $words['pt-BR']; // Explicação DevIN: Seleciona idioma da conta ADM.
$metrics = [ // Explicação DevIN: Escolhe os indicadores mais úteis da operação.
    ['companies', 'empresas'], ['people', 'pessoas'], ['jobs', 'vagas'], ['applications', 'candidaturas'],
    ['hired', 'contratacoes'], ['reports', 'denuncias_pendentes'], ['support', 'suporte_aberto'],
]; // Explicação DevIN: Define ordem dos indicadores.
?>
<section class="admin-overview" aria-label="<?= h($labels['title']) ?>"> <!-- Explicação DevIN: Agrupa métricas de gestão de acesso restrito. -->
    <header class="admin-overview-header"> <!-- Explicação DevIN: Mostra o objetivo da visão geral. -->
        <h1><?= h($labels['title']) ?></h1> <!-- Explicação DevIN: Exibe título no idioma da conta. -->
        <p><?= h($labels['intro']) ?></p> <!-- Explicação DevIN: Explica o caráter instantâneo das métricas. -->
    </header> <!-- Explicação DevIN: Finaliza cabeçalho. -->
    <div class="admin-overview-grid"> <!-- Explicação DevIN: Organiza os cards de métricas em grade responsiva. -->
        <?php foreach ($metrics as [$caption, $key]): ?> <!-- Explicação DevIN: Desenha somente métricas previamente permitidas. -->
            <article class="admin-metric"> <!-- Explicação DevIN: Exibe um indicador agregado de forma acessível. -->
                <h2><?= h($labels[$caption]) ?></h2> <!-- Explicação DevIN: Identifica o indicador sem dados pessoais. -->
                <strong><?= isset($adminOverview[$key]) ? number_format((int) $adminOverview[$key], 0, ',', '.') : h($labels['missing']) ?></strong> <!-- Explicação DevIN: Não trata tabela ausente como contagem zero. -->
            </article> <!-- Explicação DevIN: Finaliza cartão. -->
        <?php endforeach; ?> <!-- Explicação DevIN: Conclui renderização das métricas. -->
    </div> <!-- Explicação DevIN: Finaliza grade principal. -->
    <section class="admin-overview-ranking"> <!-- Explicação DevIN: Exibe ranking apenas de nomes empresariais. -->
        <h2><?= h($labels['rank']) ?></h2> <!-- Explicação DevIN: Identifica as vagas por organização. -->
        <?php if (empty($adminOverview['top_empresas'])): ?> <!-- Explicação DevIN: Exibe estado vazio se não houver dados. -->
            <p><?= h($labels['none']) ?></p> <!-- Explicação DevIN: Explica ausência de registros. -->
        <?php else: ?> <!-- Explicação DevIN: Renderiza ranking sem identidades pessoais.
            --> <ol> <!-- Explicação DevIN: Ordena os maiores publicadores. -->
            <?php foreach ($adminOverview['top_empresas'] as $row): ?> <!-- Explicação DevIN: Percorre o resultado limitado a oito empresas. -->
                <li><span><?= h((string) $row['nome']) ?></span><strong><?= (int) $row['vagas'] ?> <?= h($labels['jobs']) ?></strong></li> <!-- Explicação DevIN: Exibe nome escapado e quantidade. -->
            <?php endforeach; ?> <!-- Explicação DevIN: Finaliza ranking. -->
            </ol> <!-- Explicação DevIN: Encerra lista ordenada. -->
        <?php endif; ?> <!-- Explicação DevIN: Finaliza estado alternativo. -->
    </section> <!-- Explicação DevIN: Finaliza ranking empresarial. -->
</section> <!-- Explicação DevIN: Finaliza painel de análise. -->
