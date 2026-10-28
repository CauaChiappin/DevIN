<?php

declare(strict_types=1);

require_once __DIR__ . '/middlewares/auth.php';
require_once __DIR__ . '/controllers/ProfileController.php';
require_once __DIR__ . '/controllers/JobReports.php';
require_once __DIR__ . '/controllers/ProfileReports.php';
require_once __DIR__ . '/helpers.php';

// Garante que apenas uma sessão administrativa alcance ações de moderação.
$usuarioAtual = requireWebAuth('adm');

$tipo    = 'adm';
$nome    = $_SESSION['usuario_nome']  ?? 'Administrador';
$email   = $_SESSION['usuario_email'] ?? 'adm@devin.com';
$pagina  = requestString($_GET, 'pagina');
$categoriaReport = requestString($_GET, 'categoria');
$categoriasReport = ['vagas', 'empresas', 'pessoas'];
if (!in_array($categoriaReport, $categoriasReport, true)) {
    $categoriaReport = 'vagas';
}

$paginasPermitidas = ['inicio', 'candidatos', 'posts', 'reports', 'sobre', 'perfil'];
if (!in_array($pagina, $paginasPermitidas, true)) {
    // Usa uma lista permitida para evitar que a URL selecione páginas arbitrárias.
    $pagina = 'inicio';
}

/*
|--------------------------------------------------------------------------
| PROCESSAMENTO DE AÇÕES DE MODERAÇÃO E PERFIL (POST)
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Toda alteração administrativa precisa trazer o token associado à sessão atual.
    requireValidCsrf();

    try {
        $action = requestString($_POST, 'action');

        // A mesma ação trata exclusões de três tipos; um mapa fechado escolhe tabela e chave primária.
        if (in_array($action, ['admin_delete_pessoa', 'admin_delete_empresa', 'admin_delete_vaga'], true)) {
            $id = (int) requestString($_POST, 'id');
            if ($id <= 0) {
                throw new InvalidArgumentException('Registro inválido.');
            }

            $targets = [
                'admin_delete_pessoa'  => ['pessoa', 'id_pessoa'],
                'admin_delete_empresa' => ['empresa', 'id_empresa'],
                'admin_delete_vaga'    => ['vagas', 'id_vaga'],
            ];

            /* A lista fechada limita quais tabelas/colunas podem ser usadas no SQL dinâmico.
             * O identificador do registro permanece como parâmetro preparado. */
            [$table, $column] = $targets[$action];
            $conn = getDatabaseConnection();

            try {
                $stmt = $conn->prepare("DELETE FROM {$table} WHERE {$column} = ?");
                $stmt->bind_param('i', $id);
                $stmt->execute();

                if ($stmt->affected_rows !== 1) {
                    throw new RuntimeException('Registro não encontrado.');
                }
                $stmt->close();
            } finally {
                $conn->close();
            }

            $_SESSION['admin_success'] = 'Registro excluído com sucesso.';
            header('Location: adm.php?pagina=' . ($action === 'admin_delete_vaga' ? 'posts' : 'candidatos'));
            exit;
        }

        if ($action === 'resolve_job_report') {
            $reportId = filter_var($_POST['id_report'] ?? null, FILTER_VALIDATE_INT);
            $verdict = requestString($_POST, 'veredito');
            if ($reportId === false) {
                throw new InvalidArgumentException('Denúncia inválida.');
            }

            $conn = getDatabaseConnection();
            try {
                ensureJobReportsSchema($conn);
                $notificationsDelivered = decideJobReport($conn, $reportId, $verdict, (int) $_SESSION['usuario_id']);
            } finally {
                $conn->close();
            }

            $_SESSION['admin_success'] = $notificationsDelivered
                ? 'Denúncia analisada. As notificações foram encaminhadas para a aba de conversas.'
                : 'Decisão registrada; algumas notificações estão pendentes e serão reenviadas automaticamente.';
            header('Location: adm.php?pagina=reports');
            exit;
        }

        if ($action === 'resolve_profile_report') {
            $reportId = filter_var($_POST['id_report'] ?? null, FILTER_VALIDATE_INT);
            $categoria = requestString($_POST, 'categoria');
            $verdict = requestString($_POST, 'veredito');
            if ($reportId === false || !in_array($categoria, $categoriasReport, true) || !in_array($verdict, ['manter', 'excluir'], true)) {
                throw new InvalidArgumentException('Denúncia inválida.');
            }

            $conn = getDatabaseConnection();
            try {
                ensureProfileReportsSchema($conn);
                decideProfileReport($conn, $reportId, $verdict, (int) $_SESSION['usuario_id']);
            } finally {
                $conn->close();
            }

            $_SESSION['admin_success'] = $verdict === 'excluir'
                ? 'Denúncia aceita. O perfil denunciado foi removido da plataforma.'
                : 'Denúncia analisada. O perfil foi mantido na plataforma.';
            header('Location: adm.php?pagina=reports&categoria=' . rawurlencode($categoria));
            exit;
        }

        if ($action === 'update_profile') {
            updateProfile($tipo, (int) $_SESSION['usuario_id'], $_POST, $_FILES['foto'] ?? null);
            $_SESSION['usuario_nome']    = trim(requestString($_POST, 'nome'));
            $_SESSION['usuario_email']   = trim(requestString($_POST, 'email'));
            $_SESSION['profile_success'] = 'Perfil atualizado com sucesso.';
            header('Location: adm.php?pagina=' . rawurlencode($pagina));
            exit;
        }

        if ($action === 'update_settings') {
            updateLanguage($tipo, (int) $_SESSION['usuario_id'], requestString($_POST, 'idioma') ?: 'pt-BR');
            $_SESSION['settings_success'] = 'Idioma salvo com sucesso.';
            header('Location: adm.php?pagina=' . rawurlencode($pagina) . '&configuracoes=1');
            exit;
        }

        if ($action === 'delete_account') {
            deleteProfile($tipo, (int) $_SESSION['usuario_id']);
            header('Location: logout.php?destino=index');
            exit;
        }
    } catch (Throwable $exception) {
        error_log('Erro no dashboard ADM: ' . $exception->getMessage());

        if ($action === 'update_settings') {
            $_SESSION['settings_error'] = 'Não foi possível salvar o idioma. Tente novamente.';
            header('Location: adm.php?pagina=' . rawurlencode($pagina) . '&configuracoes=1');
        } elseif (str_starts_with($action, 'admin_delete_')) {
            $_SESSION['admin_error'] = 'Não foi possível excluir o registro. Ele pode possuir dados vinculados no sistema.';
            header('Location: adm.php?pagina=' . ($action === 'admin_delete_vaga' ? 'posts' : 'candidatos'));
        } elseif ($action === 'resolve_job_report') {
            $_SESSION['admin_error'] = $exception instanceof InvalidArgumentException
                ? $exception->getMessage()
                : 'Não foi possível registrar a decisão da denúncia.';
            header('Location: adm.php?pagina=reports');
        } elseif ($action === 'resolve_profile_report') {
            $_SESSION['admin_error'] = $exception instanceof InvalidArgumentException
                ? $exception->getMessage()
                : 'Não foi possível concluir a denúncia.';
            $categoria = requestString($_POST, 'categoria');
            if (!in_array($categoria, $categoriasReport, true)) {
                $categoria = 'vagas';
            }
            header('Location: adm.php?pagina=reports&categoria=' . rawurlencode($categoria));
        } elseif ($action === 'update_profile') {
            $_SESSION['profile_error'] = 'Não foi possível atualizar o perfil. Tente novamente.';
            header('Location: adm.php?pagina=' . rawurlencode($pagina));
        } else {
            $_SESSION['profile_error'] = 'Não foi possível concluir a operação. Tente novamente.';
            header('Location: adm.php?pagina=perfil');
        }
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| CONSULTA DE DADOS PARA MODERAÇÃO
|--------------------------------------------------------------------------
*/

$admId       = (int) $_SESSION['usuario_id'];
$perfilAtual = findProfile($tipo, $admId);

if (!$perfilAtual) {
    header('Location: logout.php');
    exit;
}
$idiomaAtual = getProfileLanguage($tipo, $admId);

$empresasAdmin = [];
$usuariosAdmin = [];
$vagasAdmin    = [];
$reportsAdmin  = [];
$profileReportsAdmin = [];
$reportCounts = ['vagas' => 0, 'empresas' => 0, 'pessoas' => 0];
$totalReportsAdmin = 0;

try {
    $conn = getDatabaseConnection();
    try {
        // A consulta reúne os dados necessários para montar a lista de empresas moderáveis.
        $resultEmpresas = $conn->query('SELECT id_empresa AS id, nome, email, cnpj FROM empresa ORDER BY nome');
        $empresasAdmin  = $resultEmpresas ? $resultEmpresas->fetch_all(MYSQLI_ASSOC) : [];

        // LEFT JOIN mantém candidatos sem currículo na lista e COALESCE evita valores nulos na interface.
        $resultPessoas  = $conn->query('
            SELECT p.id_pessoa AS id, p.nome, p.email, p.cpf,
                COALESCE(cu.nome_social, "") AS nome_social,
                COALESCE(cu.grau_de_escolaridade, "") AS grau_de_escolaridade,
                COALESCE(cu.cursos, "") AS cursos,
                COALESCE(cu.experiencia, "") AS experiencia,
                COALESCE(cu.idiomas, "") AS idiomas
            FROM pessoa p
            LEFT JOIN curriculo cu ON cu.id_pessoa = p.id_pessoa
            ORDER BY p.nome
        ');
        $usuariosAdmin  = $resultPessoas ? $resultPessoas->fetch_all(MYSQLI_ASSOC) : [];

        // INNER JOIN associa cada vaga ao nome da empresa que a publicou.
        $resultVagas    = $conn->query("
            SELECT v.id_vaga AS id, v.titulo, COALESCE(v.descricao, '') AS descricao, e.nome AS empresa
            FROM vagas v
            INNER JOIN empresa e ON e.id_empresa = v.id_empresa
            ORDER BY v.id_vaga DESC
        ");
        $vagasAdmin     = $resultVagas ? $resultVagas->fetch_all(MYSQLI_ASSOC) : [];

        ensureJobReportsSchema($conn);
        try {
            if (!dispatchJobReportNotifications($conn)) {
                $_SESSION['admin_error'] = 'Algumas notificações de análises anteriores ainda estão pendentes de entrega.';
            }
        } catch (Throwable $notificationException) {
            error_log('Não foi possível reenviar notificações de denúncias: ' . $notificationException->getMessage());
            $_SESSION['admin_error'] = 'Algumas notificações de análises anteriores ainda estão pendentes de entrega.';
        }
        $resultReports = $conn->query('
            SELECT r.*,
                   COALESCE(NULLIF(r.descricao_vaga, \'\'), v.descricao, \'\') AS descricao_exibicao,
                   CASE WHEN r.descricao_vaga IS NULL THEN COALESCE(v.salario_mensal, r.salario_mensal) ELSE r.salario_mensal END AS salario_exibicao,
                   CASE WHEN r.descricao_vaga IS NULL THEN COALESCE(v.limite_aprovados, r.limite_aprovados) ELSE r.limite_aprovados END AS limite_exibicao
            FROM reports_vagas r
            LEFT JOIN vagas v ON v.id_vaga = r.id_vaga
            WHERE r.status = "pendente"
               OR r.analisado_em >= DATE_SUB(NOW(), INTERVAL 1 DAY)
            ORDER BY FIELD(r.status, "pendente", "mantida", "excluida"), r.criado_em DESC, r.id DESC
        ');
        $reportsAdmin = $resultReports->fetch_all(MYSQLI_ASSOC);

        if ($pagina === 'reports') {
            ensureProfileReportsSchema($conn);
            $resultProfileReports = $conn->query('
                SELECT *
                FROM reports_perfis
                WHERE status = "pendente"
                   OR analisado_em >= DATE_SUB(NOW(), INTERVAL 1 DAY)
                ORDER BY FIELD(status, "pendente", "concluida"), criado_em DESC, id DESC
            ');
            $profileReportsAdmin = $resultProfileReports->fetch_all(MYSQLI_ASSOC);
            $reportCounts['vagas'] = count($reportsAdmin);
            foreach ($profileReportsAdmin as $profileReport) {
                $category = $profileReport['tipo_denunciado'] === 'empresa' ? 'empresas' : 'pessoas';
                $reportCounts[$category]++;
            }
            $totalReportsAdmin = $reportCounts[$categoriaReport];
        }
    } finally {
        $conn->close();
    }
} catch (Throwable $exception) {
    error_log('Erro ao carregar moderação ADM: ' . $exception->getMessage());
    $_SESSION['admin_error'] = 'Não foi possível carregar os registros de moderação.';
}

?>
<!DOCTYPE html>
<html lang="<?= h($idiomaAtual) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevIN | Dashboard Administrativo</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link rel="stylesheet" href="../css/dashboard.css?v=<?= filemtime(__DIR__ . '/../css/dashboard.css') ?>">
</head>
<body>
    <main class="dashboard-shell page-<?= h($pagina) ?>" data-tipo="<?= h($tipo) ?>">
        <aside class="sidebar">
            <div class="sidebar-topo">
                <a class="brand" href="adm.php">
                    <span class="brand-text">Dev<span>IN</span></span>
                </a>
                <button class="menu-toggle" type="button" aria-label="Abrir ou fechar menu" aria-expanded="true" data-toggle-menu>
                    <span></span><span></span><span></span>
                </button>
            </div>

            <nav class="menu-principal" aria-label="Menu principal">
                <a class="<?= ativo($pagina, 'inicio') ?>" href="adm.php?pagina=inicio"><?= dashboardIcon('building') ?><span class="menu-text">Empresas</span></a>
                <a class="<?= ativo($pagina, 'posts') ?>" href="adm.php?pagina=posts"><?= dashboardIcon('briefcase') ?><span class="menu-text">Posts das empresas</span></a>
                <a class="<?= ativo($pagina, 'candidatos') ?>" href="adm.php?pagina=candidatos"><?= dashboardIcon('user') ?><span class="menu-text">Pessoas</span></a>
                <a class="<?= ativo($pagina, 'reports') ?>" href="adm.php?pagina=reports"><?= dashboardIcon('info') ?><span class="menu-text">Reports</span></a>
                <a class="<?= ativo($pagina, 'sobre') ?>" href="adm.php?pagina=sobre"><?= dashboardIcon('info') ?><span class="menu-text">Sobre nós</span></a>
            </nav>

            <div class="conta">
                <details class="perfil-dropdown">
                    <summary class="perfil-link"><?= profileAvatar($perfilAtual, 'avatar-mini') ?><span class="menu-text">Perfil</span></summary>
                    <div class="perfil-menu">
                        <button type="button" data-open-profile><?= profileAvatar($perfilAtual, 'avatar-foto') ?><span class="menu-text">Meu perfil</span></button>
                        <button type="button" data-open-settings><?= dashboardIcon('settings') ?><span class="menu-text">Configurações</span></button>
                    </div>
                </details>
                <a class="sair" href="logout.php" data-confirm-logout="Tem certeza que deseja sair da sua conta?"><?= dashboardIcon('logout') ?><span class="menu-text">Sair da Conta</span></a>
            </div>
        </aside>

        <section class="lista-area">
            <?php if (!empty($_SESSION['admin_error'])): ?>
                <p class="form-error"><?= h($_SESSION['admin_error']); unset($_SESSION['admin_error']); ?></p>
            <?php endif; ?>
            <?php if (!empty($_SESSION['admin_success'])): ?>
                <p class="form-success"><?= h($_SESSION['admin_success']); unset($_SESSION['admin_success']); ?></p>
            <?php endif; ?>

            <?php if ($pagina !== 'sobre'): ?>
                <?= dashboardListHeader(
                    $pagina,
                    $pagina === 'candidatos' ? 'Candidatos' : ($pagina === 'posts' ? 'Posts das empresas' : ($pagina === 'reports' ? 'Reports de ' . $categoriaReport : ($pagina === 'perfil' ? 'Perfil' : 'Empresas'))),
                    'Pesquisar por nome ou habilidade...',
                    $pagina === 'candidatos' ? count($usuariosAdmin) . ' candidatos' : ($pagina === 'posts' ? count($vagasAdmin) . ' posts' : ($pagina === 'reports' ? $totalReportsAdmin . ' reports' : ($pagina === 'inicio' ? (count($empresasAdmin) + count($vagasAdmin)) . ' registros' : '')))
                ) ?>
                <?php if (in_array($pagina, ['inicio', 'candidatos', 'posts', 'reports'], true)): ?>
                    <?php if ($pagina === 'reports'): ?>
                        <nav class="report-folders" aria-label="Categorias de denúncias">
                            <a class="<?= $categoriaReport === 'vagas' ? 'ativo' : '' ?>" href="adm.php?pagina=reports&categoria=vagas">
                                <?= dashboardIcon('briefcase') ?><span>Vagas</span><strong><?= $reportCounts['vagas'] ?></strong>
                            </a>
                            <a class="<?= $categoriaReport === 'empresas' ? 'ativo' : '' ?>" href="adm.php?pagina=reports&categoria=empresas">
                                <?= dashboardIcon('building') ?><span>Empresas</span><strong><?= $reportCounts['empresas'] ?></strong>
                            </a>
                            <a class="<?= $categoriaReport === 'pessoas' ? 'ativo' : '' ?>" href="adm.php?pagina=reports&categoria=pessoas">
                                <?= dashboardIcon('user') ?><span>Pessoas</span><strong><?= $reportCounts['pessoas'] ?></strong>
                            </a>
                        </nav>
                        <p class="list-section-label"><?= $categoriaReport === 'vagas' ? 'Denúncias de vagas' : ($categoriaReport === 'empresas' ? 'Denúncias de empresas' : 'Denúncias de pessoas') ?></p>
                    <?php else: ?>
                        <p class="list-section-label">Disponíveis</p>
                    <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($pagina === 'sobre'): ?>
                <?= aboutPage() ?>
            <?php elseif ($pagina === 'perfil'): ?>
                <section class="perfil-card">
                    <a class="fechar-card" href="adm.php?pagina=inicio">×</a>
                    <div class="perfil-topo">
                        <?= profileAvatar($perfilAtual, 'avatar-grande') ?>
                        <div>
                            <strong><?= h($nome) ?></strong>
                            <small><?= h($email) ?></small>
                        </div>
                    </div>
                    <button class="btn primary" type="button" data-open-profile>Editar perfil</button>
                </section>
            <?php elseif ($pagina === 'candidatos'): ?>
                <?php if (!$usuariosAdmin): ?><p class="empty-state">Nenhum candidato cadastrado.</p><?php endif; ?>
                <?php foreach ($usuariosAdmin as $usuario): ?>
                    <?php $detalhesCandidato = dashboardCandidateDetails($usuario); ?>
                    <article class="item-card" data-detail="<?= h($detalhesCandidato['summary']) ?>" data-detail-name="<?= h($detalhesCandidato['name']) ?>" data-detail-summary="<?= h($detalhesCandidato['summary']) ?>" data-detail-role="Candidato DevIN" data-detail-tags="<?= h($detalhesCandidato['tags']) ?>" data-detail-experience="<?= h($detalhesCandidato['experience']) ?>" data-detail-action-label="Excluir registro">
                        <span class="card-avatar"><?= dashboardIcon('user') ?></span>
                        <div>
                            <h2><?= h($usuario['nome']) ?></h2>
                            <p><?= h($usuario['email']) ?></p>
                            <?= dashboardCardTags('Candidato|Perfil ativo') ?>
                        </div>
                        <form method="post" onsubmit="return confirm('Excluir este candidato permanentemente?');">
                            <input type="hidden" name="action" value="admin_delete_pessoa">
                            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                            <input type="hidden" name="id" value="<?= (int) $usuario['id'] ?>">
                            <button class="btn danger" type="submit" data-detail-action-target>Excluir perfil</button>
                        </form>
                    </article>
                <?php endforeach; ?>
            <?php elseif ($pagina === 'posts'): ?>
                <?php if (!$vagasAdmin): ?><p class="empty-state">Nenhuma vaga publicada.</p><?php endif; ?>
                <?php foreach ($vagasAdmin as $vaga): ?>
                    <article class="item-card" data-detail="<?= h($vaga['descricao'] ?: 'Sem descrição informada.') ?>" data-job-title="<?= h($vaga['titulo']) ?>" data-detail-role="<?= h('Vaga publicada por ' . $vaga['empresa']) ?>" data-detail-tags="Vaga|<?= h($vaga['empresa']) ?>" data-detail-experience="Publicação::Registro da vaga na plataforma" data-detail-action-label="Excluir vaga">
                        <span class="card-avatar"><?= dashboardIcon('briefcase') ?></span>
                        <div>
                            <h2><?= h($vaga['titulo']) ?></h2>
                            <p><?= h($vaga['empresa']) ?> · <?= h($vaga['descricao'] ?: 'Sem descrição informada.') ?></p>
                            <?= dashboardCardTags('Vaga|' . $vaga['empresa']) ?>
                        </div>
                        <form method="post" onsubmit="return confirm('Excluir esta vaga?');">
                            <input type="hidden" name="action" value="admin_delete_vaga">
                            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                            <input type="hidden" name="id" value="<?= (int) $vaga['id'] ?>">
                            <button class="btn danger" type="submit" data-detail-action-target>Excluir vaga</button>
                        </form>
                    </article>
                <?php endforeach; ?>
            <?php elseif ($pagina === 'reports'): ?>
                <?php if ($categoriaReport === 'vagas'): ?>
                    <?php if (!$reportsAdmin): ?><p class="empty-state">Nenhuma vaga foi denunciada.</p><?php endif; ?>
                    <?php foreach ($reportsAdmin as $report): ?>
                    <?php
                    if ($report['status'] === 'mantida') {
                        $statusReport = 'Vaga mantida';
                    } elseif ($report['status'] === 'excluida') {
                        $statusReport = 'Vaga excluída';
                    } else {
                        $statusReport = 'Aguardando análise';
                    }
                    $motivosReport = [
                        'fraude' => 'Fraude ou golpe',
                        'informacao_falsa' => 'Informações falsas ou enganosas',
                        'discriminacao' => 'Discriminação',
                        'conteudo_inadequado' => 'Conteúdo inadequado',
                        'vaga_expirada' => 'Vaga expirada ou indisponível',
                        'outro' => 'Outro',
                    ];
                    $descricaoVaga = trim((string) ($report['descricao_exibicao'] ?? ''));
                    $salarioVaga = (float) ($report['salario_exibicao'] ?? 0) > 0
                        ? 'R$ ' . number_format((float) $report['salario_exibicao'], 2, ',', '.')
                        : 'A combinar';
                    $limiteAprovados = (int) ($report['limite_exibicao'] ?? 0) > 0
                        ? (string) $report['limite_exibicao']
                        : 'Sem limite';
                    $analiseReport = $report['status'] === 'pendente'
                        ? 'Aguardando decisão'
                        : (string) $report['parecer'];
                    ?>
                    <article class="item-card report-card" data-job-report-card data-detail="<?= h($descricaoVaga ?: 'Sem descrição informada.') ?>" data-report-job-description="<?= h($descricaoVaga ?: 'Sem descrição informada.') ?>" data-report-job-salary="<?= h($salarioVaga) ?>" data-report-job-limit="<?= h($limiteAprovados) ?>" data-detail-name="<?= h($report['titulo_vaga']) ?>" data-report-company="<?= h($report['nome_empresa']) ?>" data-report-reporter="<?= h($report['nome_denunciante']) ?>" data-report-reason="<?= h($motivosReport[$report['motivo']] ?? $report['motivo']) ?>" data-report-message="<?= h($report['detalhes']) ?>" data-report-analysis="<?= h($analiseReport) ?>" data-report-decision="<?= $report['status'] === 'pendente' ? 'true' : 'false' ?>">
                        <span class="card-avatar"><?= dashboardIcon('info') ?></span>
                        <div class="report-card-content">
                            <h2><?= h($report['titulo_vaga']) ?></h2>
                            <p>Empresa: <?= h($report['nome_empresa']) ?> · Denunciante: <?= h($report['nome_denunciante']) ?></p>
                            <p>Motivo: <?= h($motivosReport[$report['motivo']] ?? $report['motivo']) ?></p>
                            <p><?= h($report['detalhes']) ?></p>
                            <?= dashboardCardTags($statusReport) ?>
                        </div>
                        <?php if ($report['status'] === 'pendente'): ?>
                            <div class="report-card-actions">
                                <button class="btn primary" type="button" data-open-report-analysis>Fazer análise</button>
                            </div>
                            <form hidden method="post" data-report-keep-form>
                                <input type="hidden" name="action" value="resolve_job_report">
                                <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                                <input type="hidden" name="id_report" value="<?= (int) $report['id'] ?>">
                                <input type="hidden" name="veredito" value="manter">
                            </form>
                            <form hidden method="post" data-report-delete-form data-report-exclusion-form>
                                <input type="hidden" name="action" value="resolve_job_report">
                                <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                                <input type="hidden" name="id_report" value="<?= (int) $report['id'] ?>">
                                <input type="hidden" name="veredito" value="excluir">
                            </form>
                        <?php endif; ?>
                    </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php
                    $tipoPerfilReport = $categoriaReport === 'empresas' ? 'empresa' : 'pessoa';
                    $perfilReportsCategoria = array_values(array_filter(
                        $profileReportsAdmin,
                        static fn (array $report): bool => $report['tipo_denunciado'] === $tipoPerfilReport
                    ));
                    $rotuloPerfilReport = $tipoPerfilReport === 'empresa' ? 'Empresa' : 'Pessoa';
                    $motivosPerfilReport = [
                        'assedio' => 'Assédio ou comportamento abusivo',
                        'fraude' => 'Fraude ou golpe',
                        'falsidade' => 'Informações falsas',
                        'conteudo_inadequado' => 'Conteúdo inadequado',
                        'outro' => 'Outro',
                    ];
                    ?>
                    <?php if (!$perfilReportsCategoria): ?><p class="empty-state">Nenhum perfil foi denunciado.</p><?php endif; ?>
                    <?php foreach ($perfilReportsCategoria as $report): ?>
                        <?php
                        $statusPerfilReport = $report['status'] === 'pendente' ? 'Aguardando análise' : 'Concluída';
                        $tipoDenunciante = $report['tipo_denunciante'] === 'empresa' ? 'Empresa' : 'Pessoa';
                        ?>
                        <article class="item-card report-card profile-report-card"
                            data-detail="<?= h($report['detalhes']) ?>"
                            data-detail-name="<?= h($report['nome_denunciado']) ?>"
                            data-detail-role="<?= h('Perfil denunciado · ' . $rotuloPerfilReport) ?>"
                            data-detail-tags="<?= h($rotuloPerfilReport . '|' . $statusPerfilReport) ?>"
                            data-detail-experience="<?= h('Denunciante: ' . $report['nome_denunciante'] . ' (' . $tipoDenunciante . ')') ?>"
                            data-report-decision="<?= $report['status'] === 'pendente' ? 'true' : 'false' ?>">
                            <span class="card-avatar"><?= dashboardIcon($tipoPerfilReport === 'empresa' ? 'building' : 'user') ?></span>
                            <div class="report-card-content">
                                <h2><?= h($report['nome_denunciado']) ?></h2>
                                <p><?= h($rotuloPerfilReport) ?> · Denunciante: <?= h($report['nome_denunciante']) ?> (<?= h($tipoDenunciante) ?>)</p>
                                <p>Motivo: <?= h($motivosPerfilReport[$report['motivo']] ?? $report['motivo']) ?></p>
                                <p><?= h($report['detalhes']) ?></p>
                                <?= dashboardCardTags($statusPerfilReport) ?>
                            </div>
                            <?php if ($report['status'] === 'pendente'): ?>
                                <div class="report-card-actions">
                                    <button class="btn primary" type="button" data-open-report-analysis>Fazer análise</button>
                                </div>
                                <form hidden method="post" data-report-keep-form>
                                    <input type="hidden" name="action" value="resolve_profile_report">
                                    <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                                    <input type="hidden" name="id_report" value="<?= (int) $report['id'] ?>">
                                    <input type="hidden" name="categoria" value="<?= h($categoriaReport) ?>">
                                    <input type="hidden" name="veredito" value="manter">
                                </form>
                                <form hidden method="post" data-report-delete-form>
                                    <input type="hidden" name="action" value="resolve_profile_report">
                                    <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                                    <input type="hidden" name="id_report" value="<?= (int) $report['id'] ?>">
                                    <input type="hidden" name="categoria" value="<?= h($categoriaReport) ?>">
                                    <input type="hidden" name="veredito" value="excluir">
                                </form>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php else: ?>
                <?php if (!$empresasAdmin && !$vagasAdmin): ?><p class="empty-state">Nenhum registro disponível.</p><?php endif; ?>
                <?php foreach ($empresasAdmin as $empresa): ?>
                    <article class="item-card" data-detail="<?= h('E-mail: ' . $empresa['email'] . ' | CNPJ: ' . $empresa['cnpj']) ?>" data-detail-role="Empresa cadastrada" data-detail-tags="Empresa|Perfil ativo" data-detail-experience="<?= h('Cadastro na plataforma::CNPJ ' . $empresa['cnpj']) ?>" data-detail-action-label="Excluir registro">
                        <span class="card-avatar"><?= dashboardIcon('building') ?></span>
                        <div>
                            <h2><?= h($empresa['nome']) ?></h2>
                            <p><?= h($empresa['email']) ?></p>
                            <?= dashboardCardTags('Empresa|Perfil ativo') ?>
                        </div>
                        <form method="post" onsubmit="return confirm('Excluir esta empresa permanentemente?');">
                            <input type="hidden" name="action" value="admin_delete_empresa">
                            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                            <input type="hidden" name="id" value="<?= (int) $empresa['id'] ?>">
                            <button class="btn danger" type="submit" data-detail-action-target>Excluir empresa</button>
                        </form>
                    </article>
                <?php endforeach; ?>
                <?php foreach ($vagasAdmin as $vaga): ?>
                    <article class="item-card" data-detail="<?= h($vaga['descricao'] ?: 'Sem descrição informada.') ?>" data-job-title="<?= h($vaga['titulo']) ?>" data-detail-role="<?= h('Vaga publicada por ' . $vaga['empresa']) ?>" data-detail-tags="Vaga|<?= h($vaga['empresa']) ?>" data-detail-experience="Publicação::Registro da vaga na plataforma" data-detail-action-label="Excluir vaga">
                        <span class="card-avatar"><?= dashboardIcon('briefcase') ?></span>
                        <div>
                            <h2><?= h($vaga['empresa']) ?></h2>
                            <p><?= h($vaga['titulo']) ?> · <?= h($vaga['descricao'] ?: 'Sem descrição informada.') ?></p>
                            <?= dashboardCardTags('Vaga|' . $vaga['empresa']) ?>
                        </div>
                        <form method="post" onsubmit="return confirm('Excluir esta vaga?');">
                            <input type="hidden" name="action" value="admin_delete_vaga">
                            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                            <input type="hidden" name="id" value="<?= (int) $vaga['id'] ?>">
                            <button class="btn danger" type="submit" data-detail-action-target>Excluir vaga</button>
                        </form>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <?php if ($pagina === 'reports'): ?>
            <dialog class="settings-modal report-confirm-modal" data-report-confirm-dialog aria-labelledby="reportConfirmTitle">
                <div class="modal-form">
                    <span class="report-confirm-icon" aria-hidden="true"><?= dashboardIcon('info') ?></span>
                    <h2 id="reportConfirmTitle">Excluir vaga denunciada?</h2>
                    <p class="settings-description">A vaga será removida e a empresa e a pessoa denunciante receberão uma mensagem de conclusão.</p>
                    <div class="report-confirm-actions">
                        <button class="btn secondary" type="button" data-cancel-report-exclusion>Cancelar</button>
                        <button class="btn danger" type="button" data-confirm-report-exclusion>Excluir vaga</button>
                    </div>
                </div>
            </dialog>
        <?php endif; ?>

        <?php if ($pagina !== 'sobre' && $pagina !== 'perfil'): ?>
            <?= dashboardDetailPanel() ?>
        <?php else: ?>
        <aside class="detalhe-area">
            <?php if ($pagina === 'sobre'): ?>
                <h2>Contato</h2>
                <p>Equipe DevIN - Acesso Restrito ADM.</p>
            <?php elseif ($pagina === 'perfil'): ?>
                <h2>Seu Perfil</h2>
                <p>Controles de segurança da conta.</p>
            <?php else: ?>
                <h2>Explicando tudo sobre o post selecionado</h2>
                <p id="detailText">Selecione um post ou candidato para analisar as informações.</p>
                <button class="btn danger fixed-action" type="button">Excluir Registro</button>
            <?php endif; ?>
        </aside>
        <?php endif; ?>
    </main>
    <?php require __DIR__ . '/components/dashboard-footer.php'; ?>

    <!-- Modal Editar Perfil -->
    <dialog class="settings-modal profile-modal" id="profileModal" aria-labelledby="profileModalTitle">
        <form method="post" class="modal-form profile-form" enctype="multipart/form-data">
            <button class="modal-close" type="button" data-close-modal aria-label="Fechar">×</button>
            <h2 class="sr-only" id="profileModalTitle">Meu perfil</h2>
            <?php if (!empty($_SESSION['profile_error'])): ?>
                <p class="form-error"><?= h($_SESSION['profile_error']); unset($_SESSION['profile_error']); ?></p>
            <?php endif; ?>
            <?php if (!empty($_SESSION['profile_success'])): ?>
                <p class="form-success"><?= h($_SESSION['profile_success']); unset($_SESSION['profile_success']); ?></p>
            <?php endif; ?>
            <input type="hidden" name="action" value="update_profile">
            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
            <div class="profile-summary">
                <div class="profile-photo" aria-label="Foto de perfil">
                    <?= profileAvatar($perfilAtual, 'profile-photo-preview') ?>
                </div>
                <div>
                    <strong><?= h($perfilAtual['nome']) ?></strong>
                    <small><?= h($perfilAtual['email']) ?></small>
                </div>
            </div>
            <div class="profile-fields">
                <label>Nome<input name="nome" type="text" value="<?= h($perfilAtual['nome']) ?>" required></label>
                <label>E-mail<input name="email" type="email" value="<?= h($perfilAtual['email']) ?>" required></label>
            </div>
            <button class="profile-save" type="submit">Salvar</button>
        </form>
        <form method="post" class="modal-form account-delete-form">
            <input type="hidden" name="action" value="delete_account">
            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
            <button class="btn danger" type="submit" data-delete-account>Excluir conta</button>
        </form>
    </dialog>

    <dialog class="settings-modal" id="settingsModal" aria-labelledby="settingsModalTitle">
        <form method="post" class="modal-form settings-form">
            <button class="modal-close" type="button" data-close-modal aria-label="Fechar">×</button>
            <h2 id="settingsModalTitle">Configurações</h2>
            <p class="settings-description">Personalize o idioma da interface da sua conta.</p>
            <?php if (!empty($_SESSION['settings_error'])): ?>
                <p class="form-error" role="alert"><?= h($_SESSION['settings_error']); unset($_SESSION['settings_error']); ?></p>
            <?php endif; ?>
            <?php if (!empty($_SESSION['settings_success'])): ?>
                <p class="form-success" role="status"><?= h($_SESSION['settings_success']); unset($_SESSION['settings_success']); ?></p>
            <?php endif; ?>
            <label for="settingsLanguage">Idioma
                <select id="settingsLanguage" name="idioma">
                    <option value="pt-BR" <?= $idiomaAtual === 'pt-BR' ? 'selected' : '' ?>>Português</option>
                    <option value="en" <?= $idiomaAtual === 'en' ? 'selected' : '' ?>>Inglês</option>
                    <option value="es" <?= $idiomaAtual === 'es' ? 'selected' : '' ?>>Espanhol</option>
                </select>
            </label>
            <input type="hidden" name="action" value="update_settings">
            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
            <button class="btn primary settings-save" type="submit">Salvar alterações</button>
        </form>
        <section class="settings-danger-zone">
            <div>
                <h3>Excluir conta</h3>
                <p>Essa ação é permanente e não pode ser desfeita.</p>
            </div>
            <form method="post" class="account-delete-form">
                <input type="hidden" name="action" value="delete_account">
                <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                <button class="btn danger" type="submit" data-delete-account>Excluir conta</button>
            </form>
        </section>
    </dialog>

    <script src="../js/site-navigation.js"></script>
    <script src="../js/dashboard-menu.js"></script>
    <script src="../js/dashboard-i18n.js?v=<?= filemtime(__DIR__ . '/../js/dashboard-i18n.js') ?>"></script>
    <script src="../js/dashboard.js?v=<?= filemtime(__DIR__ . '/../js/dashboard.js') ?>"></script>
    <script src="../js/konami-arcade.js?v=<?= filemtime(__DIR__ . '/../js/konami-arcade.js') ?>"></script>
    <script src="../js/lenis-global.js?v=<?= filemtime(__DIR__ . '/../js/lenis-global.js') ?>"></script>
</body>
</html>
