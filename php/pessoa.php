<?php

declare(strict_types=1);

require_once __DIR__ . '/middlewares/auth.php';
require_once __DIR__ . '/controllers/ProfileController.php';
require_once __DIR__ . '/helpers.php';

$usuarioAtual = requirePessoaComCurriculo();

$tipo          = 'pessoa';
$idPessoaAtual = (int) $usuarioAtual['id'];
$nome          = $_SESSION['usuario_nome']  ?? $usuarioAtual['nome']  ?? 'Usuário';
$email         = $_SESSION['usuario_email'] ?? $usuarioAtual['email'] ?? 'email@devin.com';
$pagina        = requestString($_GET, 'pagina');

$paginasPermitidas = ['inicio', 'vagas', 'sobre', 'perfil'];
if (!in_array($pagina, $paginasPermitidas, true)) {
    $pagina = 'inicio';
}

/*
|--------------------------------------------------------------------------
| PROCESSAMENTO DE AÇÕES (POST)
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrf();

    try {
        $action = requestString($_POST, 'action');

        if ($action === 'apply_job') {
            $vagaId = (int) requestString($_POST, 'id_vaga');
            if ($vagaId <= 0) {
                throw new InvalidArgumentException('Vaga inválida.');
            }

            $conn = getDatabaseConnection();
            try {
                $check = $conn->prepare('SELECT id_vaga FROM vagas WHERE id_vaga = ? LIMIT 1');
                $check->bind_param('i', $vagaId);
                $check->execute();
                $exists = $check->get_result()->num_rows === 1;
                $check->close();

                if (!$exists) {
                    throw new InvalidArgumentException('A vaga não está mais disponível.');
                }

                $duplicate = $conn->prepare('SELECT id_candidatura FROM candidatura WHERE id_pessoa = ? AND id_vaga = ? LIMIT 1');
                $duplicate->bind_param('ii', $idPessoaAtual, $vagaId);
                $duplicate->execute();
                $alreadyApplied = $duplicate->get_result()->num_rows > 0;
                $duplicate->close();

                if ($alreadyApplied) {
                    throw new InvalidArgumentException('Você já se candidatou a esta vaga.');
                }

                $stmt = $conn->prepare('INSERT INTO candidatura (data_candidatura, status, id_pessoa, id_vaga) VALUES (CURDATE(), ?, ?, ?)');
                $status = 'pendente';
                $stmt->bind_param('sii', $status, $idPessoaAtual, $vagaId);
                $stmt->execute();
                $stmt->close();
            } finally {
                $conn->close();
            }

            $_SESSION['job_success'] = 'Candidatura enviada com sucesso.';
            header('Location: pessoa.php?pagina=vagas');
            exit;
        }

        if ($action === 'update_profile') {
            updateProfile($tipo, $idPessoaAtual, $_POST, $_FILES['foto'] ?? null);
            $_SESSION['usuario_nome']  = trim(requestString($_POST, 'nome'));
            $_SESSION['usuario_email'] = trim(requestString($_POST, 'email'));
            $_SESSION['profile_success'] = 'Perfil atualizado com sucesso.';
            header('Location: pessoa.php?pagina=perfil');
            exit;
        }

        if ($action === 'update_settings') {
            updateLanguage($tipo, $idPessoaAtual, requestString($_POST, 'idioma') ?: 'pt-BR');
            header('Location: pessoa.php?configuracoes=1');
            exit;
        }

        if ($action === 'delete_account') {
            deleteProfile($tipo, $idPessoaAtual);
            header('Location: logout.php');
            exit;
        }
    } catch (Throwable $exception) {
        error_log('Erro no dashboard pessoa: ' . $exception->getMessage());
        if ($action === 'apply_job') {
            $_SESSION['job_error'] = $exception instanceof InvalidArgumentException
                ? $exception->getMessage()
                : 'Não foi possível enviar a candidatura. Tente novamente.';
            header('Location: pessoa.php?pagina=inicio');
        } else {
            $_SESSION['profile_error'] = 'Não foi possível concluir a operação. Tente novamente.';
            header('Location: pessoa.php?pagina=perfil');
        }
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| CONSULTA DE DADOS E VAGAS
|--------------------------------------------------------------------------
*/

$perfilAtual = findProfile($tipo, $idPessoaAtual);
if (!$perfilAtual) {
    header('Location: logout.php');
    exit;
}
$idiomaAtual = $_SESSION['idioma'] ?? 'pt-BR';

$vagasDisponiveis  = [];
$minhasCandidaturas = [];

try {
    $conn = getDatabaseConnection();
    try {
        $stmt = $conn->prepare("
            SELECT v.id_vaga, v.titulo, COALESCE(v.descricao, '') AS descricao, v.tempo_vaga, e.nome AS empresa
            FROM vagas v
            INNER JOIN empresa e ON e.id_empresa = v.id_empresa
            ORDER BY v.id_vaga DESC
        ");
        $stmt->execute();
        $vagasDisponiveis = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $stmt = $conn->prepare("
            SELECT c.id_candidatura, c.status, c.data_candidatura, v.id_vaga, v.titulo, COALESCE(v.descricao, '') AS descricao, e.nome AS empresa
            FROM candidatura c
            INNER JOIN vagas v ON v.id_vaga = c.id_vaga
            INNER JOIN empresa e ON e.id_empresa = v.id_empresa
            WHERE c.id_pessoa = ?
            ORDER BY c.id_candidatura DESC
        ");
        $stmt->bind_param('i', $idPessoaAtual);
        $stmt->execute();
        $minhasCandidaturas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } finally {
        $conn->close();
    }
} catch (Throwable $exception) {
    error_log('Erro ao carregar vagas da pessoa: ' . $exception->getMessage());
    $_SESSION['job_error'] = 'Não foi possível carregar as vagas agora.';
}

foreach ($vagasDisponiveis as &$vaga) {
    $vaga['detalhe'] = $vaga['descricao'] !== '' ? $vaga['descricao'] : 'Esta vaga ainda não possui uma descrição detalhada.';
}
unset($vaga);

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevIN | Dashboard Candidato</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link rel="stylesheet" href="../css/dashboard.css?v=<?= filemtime(__DIR__ . '/../css/dashboard.css') ?>">
</head>

<body>
    <main class="dashboard-shell page-<?= h($pagina) ?>" data-tipo="<?= h($tipo) ?>">
        <aside class="sidebar">
            <div class="sidebar-topo">
                <a class="brand" href="pessoa.php">
                    <span class="brand-text">Dev<span>IN</span></span>
                </a>
                <button class="menu-toggle" type="button" aria-label="Abrir ou fechar menu" aria-expanded="true" data-toggle-menu>
                    <span></span><span></span><span></span>
                </button>
            </div>

            <nav class="menu-principal" aria-label="Menu principal">
                <a class="<?= ativo($pagina, 'inicio') ?>" href="pessoa.php?pagina=inicio">
                    <?= dashboardIcon('home') ?><span class="menu-text">Início</span>
                </a>
                <a class="<?= ativo($pagina, 'vagas') ?>" href="pessoa.php?pagina=vagas">
                    <?= dashboardIcon('briefcase') ?><span class="menu-text">Minhas Vagas</span>
                </a>
                <a class="<?= ativo($pagina, 'sobre') ?>" href="pessoa.php?pagina=sobre">
                    <?= dashboardIcon('info') ?><span class="menu-text">Sobre nós</span>
                </a>
            </nav>

            <div class="conta">
                <details class="perfil-dropdown">
                    <summary class="perfil-link">
                        <?= profileAvatar($perfilAtual, 'avatar-mini') ?>
                        <span class="menu-text">Perfil</span>
                    </summary>
                    <div class="perfil-menu">
                        <button type="button" data-open-profile>
                            <?= profileAvatar($perfilAtual, 'avatar-foto') ?>
                            <span class="menu-text">Meu perfil</span>
                        </button>
                        <button type="button" data-open-settings>
                            <?= dashboardIcon('settings') ?>
                            <span class="menu-text">Configurações</span>
                        </button>
                    </div>
                </details>
                <a class="sair" href="logout.php" data-confirm-logout="Tem certeza que deseja sair da sua conta?">
                    <?= dashboardIcon('logout') ?><span class="menu-text">Sair da Conta</span>
                </a>
            </div>
        </aside>

        <section class="lista-area">
            <?php if ($pagina !== 'sobre'): ?>
                <?= dashboardListHeader(
                    $pagina,
                    $pagina === 'vagas' ? 'Minhas candidaturas' : ($pagina === 'perfil' ? 'Perfil' : 'Vagas'),
                    'Pesquisar por nome ou habilidade...',
                    $pagina === 'vagas' ? count($minhasCandidaturas) . ' vagas' : ($pagina === 'inicio' ? count($vagasDisponiveis) . ' vagas' : '')
                ) ?>
                <?php if (in_array($pagina, ['inicio', 'vagas'], true)): ?>
                    <p class="list-section-label">Disponíveis</p>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($pagina === 'sobre'): ?>
                <?= aboutPage() ?>
            <?php elseif ($pagina === 'perfil'): ?>
                <section class="perfil-card">
                    <a class="fechar-card" href="pessoa.php?pagina=inicio">×</a>
                    <div class="perfil-topo">
                        <?= profileAvatar($perfilAtual, 'avatar-grande') ?>
                        <div>
                            <strong><?= h($nome) ?></strong>
                            <small><?= h($email) ?></small>
                        </div>
                    </div>
                    <button class="btn primary" type="button" data-open-profile>Editar perfil</button>
                </section>
            <?php elseif ($pagina === 'vagas'): ?>
                <?php if (!empty($_SESSION['job_error'])): ?>
                    <p class="form-error"><?= h($_SESSION['job_error']); unset($_SESSION['job_error']); ?></p>
                <?php endif; ?>
                <?php if (!empty($_SESSION['job_success'])): ?>
                    <p class="form-success"><?= h($_SESSION['job_success']); unset($_SESSION['job_success']); ?></p>
                <?php endif; ?>
                <?php if (!$minhasCandidaturas): ?>
                    <p class="empty-state">Você ainda não se candidatou a nenhuma vaga.</p>
                <?php endif; ?>
                <?php foreach ($minhasCandidaturas as $vaga): ?>
                    <article class="item-card" data-detail="<?= h($vaga['descricao'] ?: 'Sem descrição informada.') ?>" data-job-title="<?= h($vaga['titulo']) ?>" data-detail-role="<?= h('Candidatura para ' . $vaga['empresa']) ?>" data-detail-tags="Candidatura|<?= h(ucfirst($vaga['status'])) ?>|<?= h($vaga['empresa']) ?>" data-detail-experience="<?= h('Candidatura::Status ' . ucfirst($vaga['status'])) ?>">
                        <span class="card-avatar"><?= dashboardIcon('briefcase') ?></span>
                        <div>
                            <h2><?= h($vaga['empresa']) ?> · <?= h($vaga['titulo']) ?></h2>
                            <p><?= h($vaga['descricao'] ?: 'Sem descrição informada.') ?></p>
                            <?= dashboardCardTags('Candidatura|' . ucfirst($vaga['status']) . '|' . $vaga['empresa']) ?>
                        </div>
                        <span class="status <?= $vaga['status'] === 'aprovado' ? 'aprovado' : ($vaga['status'] === 'recusado' ? 'reprovado' : 'analise') ?>"><?= h(ucfirst($vaga['status'])) ?></span>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <?php if (!empty($_SESSION['job_error'])): ?>
                    <p class="form-error"><?= h($_SESSION['job_error']); unset($_SESSION['job_error']); ?></p>
                <?php endif; ?>
                <?php if (!empty($_SESSION['job_success'])): ?>
                    <p class="form-success"><?= h($_SESSION['job_success']); unset($_SESSION['job_success']); ?></p>
                <?php endif; ?>
                <?php if (!$vagasDisponiveis): ?>
                    <p class="empty-state">Ainda não há vagas publicadas.</p>
                <?php endif; ?>
                <?php foreach ($vagasDisponiveis as $vaga): ?>
                    <article class="item-card" data-detail="<?= h($vaga['detalhe']) ?>" data-job-title="<?= h($vaga['titulo']) ?>" data-detail-role="<?= h($vaga['empresa']) ?>" data-detail-tags="Vaga disponível|<?= h($vaga['empresa']) ?>" data-detail-experience="Vaga publicada::Confira os requisitos e envie sua candidatura" data-detail-action-label="Candidatar-se">
                        <span class="card-avatar"><?= dashboardIcon('briefcase') ?></span>
                        <div>
                            <h2><?= h($vaga['empresa']) ?></h2>
                            <p><?= h($vaga['titulo']) ?> · <?= h($vaga['descricao'] ?: 'Sem descrição informada.') ?></p>
                            <?= dashboardCardTags('Vaga disponível|' . $vaga['empresa']) ?>
                        </div>
                        <form method="post">
                            <input type="hidden" name="action" value="apply_job">
                            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                            <input type="hidden" name="id_vaga" value="<?= (int) $vaga['id_vaga'] ?>">
                            <button class="btn primary" type="submit" data-detail-action-target>Candidatar-se</button>
                        </form>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <?php if ($pagina !== 'sobre' && $pagina !== 'perfil'): ?>
            <?= dashboardDetailPanel() ?>
        <?php else: ?>
            <aside class="detalhe-area">
                <?php if ($pagina === 'sobre'): ?>
                    <h2>Contato</h2>
                    <p>Fale com a equipe DevIN para conhecer melhor o projeto.</p>
                <?php elseif ($pagina === 'perfil'): ?>
                    <h2 id="detailTitle">Detalhes da vaga</h2>
                    <p>Use este espaço para visualizar detalhes da vaga.</p>
                    <button class="btn primary fixed-action" type="button">Candidatar-se</button>
                <?php endif; ?>
            </aside>
        <?php endif; ?>
    </main>

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
                <label class="profile-photo" aria-label="Alterar foto de perfil">
                    <?= profileAvatar($perfilAtual, 'profile-photo-preview') ?>
                    <span class="photo-edit" aria-hidden="true">✎</span>
                    <input name="foto" type="file" accept="image/png,image/jpeg,image/webp">
                </label>
                <div>
                    <strong><?= h($perfilAtual['nome']) ?></strong>
                    <small><?= h($perfilAtual['email']) ?></small>
                </div>
            </div>
            <div class="profile-fields">
                <label>Nome<input name="nome" type="text" value="<?= h($perfilAtual['nome']) ?>" required></label>
                <label>E-mail da conta<input name="email" type="email" value="<?= h($perfilAtual['email']) ?>" required></label>
                <label>Celular<input name="telefone" type="tel" value="<?= h($perfilAtual['telefone'] ?? '') ?>" required></label>
                <label>CEP<input name="cep" type="text" value="<?= h($perfilAtual['cep'] ?? '') ?>" required></label>
            </div>
            <button class="profile-save" type="submit">Salvar</button>
        </form>
    </dialog>

    <!-- Modal Configurações -->
    <dialog class="settings-modal" id="settingsModal">
        <form method="post" class="modal-form">
            <button class="modal-close" value="close" aria-label="Fechar">×</button>
            <h2>Configurações</h2>
            <label>Idioma
                <select name="idioma">
                    <option value="pt-BR" <?= $idiomaAtual === 'pt-BR' ? 'selected' : '' ?>>Português</option>
                    <option value="en" <?= $idiomaAtual === 'en' ? 'selected' : '' ?>>Inglês</option>
                    <option value="es" <?= $idiomaAtual === 'es' ? 'selected' : '' ?>>Espanhol</option>
                </select>
            </label>
            <input type="hidden" name="action" value="update_settings">
            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
            <button class="btn primary" type="submit">Salvar idioma</button>
        </form>
        <form method="post" class="modal-form account-delete-form">
            <input type="hidden" name="action" value="delete_account">
            <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
            <button class="btn danger" type="submit" data-delete-account>Excluir conta</button>
        </form>
    </dialog>

    <script src="../js/dashboard-menu.js"></script>
    <script src="../js/dashboard.js"></script>
</body>

</html>