<?php
require_once __DIR__ . '/middlewares/auth.php';
require_once __DIR__ . '/controllers/ProfileController.php';
require_once __DIR__ . '/helpers.php';

$usuarioAtual = requirePessoaComCurriculo();

$tipo = 'pessoa';
$nome = $_SESSION['usuario_nome'] ?? 'Usuario';
$email = $_SESSION['usuario_email'] ?? 'email@devin.com';
$pagina = $_GET['pagina'] ?? 'inicio';

$paginasPermitidas = ['inicio', 'vagas', 'sobre', 'perfil'];
if (!in_array($pagina, $paginasPermitidas, true)) {
    $pagina = 'inicio';
}

if (empty($_SESSION['csrf_token']))
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrf();
    try {
        $action = $_POST['action'] ?? '';
        if ($action === 'apply_job') {
            $vagaId = (int) ($_POST['id_vaga'] ?? 0);
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
                $idPessoa = (int) $_SESSION['usuario_id'];
                $duplicate->bind_param('ii', $idPessoa, $vagaId);
                $duplicate->execute();
                $alreadyApplied = $duplicate->get_result()->num_rows > 0;
                $duplicate->close();

                if ($alreadyApplied) {
                    throw new InvalidArgumentException('Você já se candidatou a esta vaga.');
                }

                $stmt = $conn->prepare('INSERT INTO candidatura (data_candidatura, status, id_pessoa, id_vaga) VALUES (CURDATE(), ?, ?, ?)');
                $status = 'pendente';
                $stmt->bind_param('sii', $status, $idPessoa, $vagaId);
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
            updateProfile($tipo, (int) $_SESSION['usuario_id'], $_POST, $_FILES['foto'] ?? null);
            $_SESSION['usuario_nome'] = trim($_POST['nome']);
            $_SESSION['usuario_email'] = trim($_POST['email']);
            $_SESSION['profile_success'] = 'Perfil atualizado com sucesso.';
            header('Location: pessoa.php?perfil=meu');
            exit;
        }
        if ($action === 'update_settings') {
            updateLanguage($tipo, (int) $_SESSION['usuario_id'], $_POST['idioma'] ?? 'pt-BR');
            header('Location: pessoa.php?configuracoes=1');
            exit;
        }
        if ($action === 'delete_account') {
            deleteProfile($tipo, (int) $_SESSION['usuario_id']);
            session_destroy();
            header('Location: login.php');
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
            header('Location: pessoa.php?perfil=meu');
        }
        exit;
    }
}

$perfilAtual = findProfile($tipo, (int) $_SESSION['usuario_id']);
if (!$perfilAtual) {
    header('Location: logout.php');
    exit;
}

// Dados reais da plataforma: vagas abertas e candidaturas da pessoa logada.
$vagasDisponiveis = [];
$minhasCandidaturas = [];

try {
    $conn = getDatabaseConnection();
    $stmt = $conn->prepare("SELECT v.id_vaga, v.titulo, COALESCE(v.descricao, '') AS descricao, v.tempo_vaga, e.nome AS empresa FROM vagas v INNER JOIN empresa e ON e.id_empresa = v.id_empresa ORDER BY v.id_vaga DESC");
    $stmt->execute();
    $vagasDisponiveis = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $idPessoaAtual = (int) $_SESSION['usuario_id'];
    $stmt = $conn->prepare("SELECT c.id_candidatura, c.status, c.data_candidatura, v.id_vaga, v.titulo, COALESCE(v.descricao, '') AS descricao, e.nome AS empresa FROM candidatura c INNER JOIN vagas v ON v.id_vaga = c.id_vaga INNER JOIN empresa e ON e.id_empresa = v.id_empresa WHERE c.id_pessoa = ? ORDER BY c.id_candidatura DESC");
    $stmt->bind_param('i', $idPessoaAtual);
    $stmt->execute();
    $minhasCandidaturas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $conn->close();
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
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevIN | Dashboard Candidato</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link rel="icon" type="image/png" href="../img/favicon.png">
    <link rel="stylesheet" href="../css/dashboard.css?v=<?= filemtime(__DIR__ . '/../css/dashboard.css') ?>">
</head>

<body>
    <main class="dashboard-shell page-<?= h($pagina) ?>" data-tipo="<?= h($tipo) ?>">
        <aside class="sidebar">
            <div class="sidebar-topo">
                <a class="brand" href="pessoa.php">
                    <span class="brand-text">Dev<span>IN</span></span>
                </a>
                <button class="menu-toggle" type="button" aria-label="Abrir ou fechar menu" aria-expanded="true"
                    data-toggle-menu>
                    <span></span><span></span><span></span>
                </button>
            </div>

            <nav class="menu-principal" aria-label="Menu principal">
                <a class="<?= ativo($pagina, 'inicio') ?>"
                    href="pessoa.php?pagina=inicio"><?= dashboardIcon('home') ?><span
                        class="menu-text">Inicio</span></a>
                <a class="<?= ativo($pagina, 'vagas') ?>"
                    href="pessoa.php?pagina=vagas"><?= dashboardIcon('briefcase') ?><span class="menu-text">Minhas
                        Vagas</span></a>
                <a class="<?= ativo($pagina, 'sobre') ?>"
                    href="pessoa.php?pagina=sobre"><?= dashboardIcon('info') ?><span class="menu-text">Sobre
                        nos</span></a>
            </nav>

            <div class="conta">
                <details class="perfil-dropdown">
                    <summary class="perfil-link"><?= profileAvatar($perfilAtual, 'avatar-mini') ?><span
                            class="menu-text">Perfil</span></summary>
                    <div class="perfil-menu">
                        <button type="button" data-open-profile><?= profileAvatar($perfilAtual, 'avatar-foto') ?><span
                                class="menu-text">Meu perfil</span></button>
                        <button type="button" data-open-settings><?= dashboardIcon('settings') ?><span
                                class="menu-text">Configuracoes</span></button>
                    </div>
                </details>
                <a class="sair" href="logout.php" data-confirm-logout="Tem certeza que deseja sair da sua conta?"><?= dashboardIcon('logout') ?><span class="menu-text">Sair da
                        Conta</span></a>
            </div>
        </aside>

        <section class="lista-area">
            <?php if ($pagina !== 'sobre'): ?>
                <header class="dashboard-header">
                    <div>
                        <span>Painel DevIN</span>
                        <h1>Dashboard Candidato</h1>
                    </div>
                </header>

                <form class="busca" action="" method="get">
                    <input type="hidden" name="pagina" value="<?= h($pagina) ?>">
                    <label>
                        <input type="search" name="q" placeholder="Pesquise vagas">
                    </label>
                </form>
            <?php endif; ?>

            <?php if ($pagina === 'sobre'): ?><?= aboutPage() ?><?php elseif ($pagina === 'sobre'): ?>
                <div class="sobre-container">
                    <!-- Parte Superior (Bege) -->
                    <div class="sobre-top-section">
                        <span class="badge-historia">Nossa história</span>
                        <h1 class="sobre-title">Conectando talentos ao <span class="text-blue">futuro</span><br>da tecnologia</h1>
                        <p class="sobre-subtitle">A DevIN nasceu para transformar a forma como desenvolvedores<br>encontram oportunidades — simples, rápido e eficiente.</p>
                        
                        <div class="sobre-cards">
                            <div class="sobre-card">
                                <div class="icon-placeholder"></div>
                                <h3>Visão</h3>
                                <p>Ser referência na conexão entre talentos de tecnologia e empresas, promovendo crescimento profissional e inovação no mercado digital.</p>
                            </div>
                            <div class="sobre-card">
                                <div class="icon-placeholder"></div>
                                <h3>Missão</h3>
                                <p>Conectar desenvolvedores de todos os níveis a oportunidades de trabalho, tornando o processo de recrutamento mais simples e eficiente.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Parte Inferior (Branca) -->
                    <div class="sobre-bottom-section">
                        <div class="sobre-about-text">
                            <h2>Quem somos <span class="text-blue">nós?</span></h2>
                            <p>A DevIN nasceu com o propósito de transformar a forma como profissionais de tecnologia encontram oportunidades. Desenvolvida no ambiente educacional da Escola Profª Alcina Dantas Feijão.</p>
                            <p>Hoje, a DevIN oferece um ambiente moderno onde empresas podem divulgar vagas e gerenciar candidatos, enquanto usuários criam perfis, buscam empregos, estágios e programas de aprendizagem.</p>
                        </div>
                        
                        <div class="sobre-team-card">
                            <div class="team-header">Time fundador</div>
                            <ul class="team-list">
                                <li>
                                    <div class="team-avatar"></div>
                                    <div class="team-info">
                                        <strong>Cauã Chiappin de Lima</strong>
                                        <span>Cofundador • caua.lima@scseduca.com.br</span>
                                    </div>
                                </li>
                                <li>
                                    <div class="team-avatar"></div>
                                    <div class="team-info">
                                        <strong>Enzo Vasconcelos de Camargo</strong>
                                        <span>Cofundador • enzo.camargo@scseduca.com.br</span>
                                    </div>
                                </li>
                                <li>
                                    <div class="team-avatar"></div>
                                    <div class="team-info">
                                        <strong>João Vitor da Silva e Sousa</strong>
                                        <span>Cofundador • joao.sousa2@scseduca.com.br</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php elseif ($pagina === 'perfil'): ?>
                <section class="perfil-card">
                    <a class="fechar-card" href="pessoa.php?pagina=inicio">x</a>
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
                    <article class="item-card" data-detail="<?= h($vaga['descricao'] ?: 'Sem descrição informada.') ?>" data-job-title="<?= h($vaga['titulo']) ?>">
                        <span class="card-avatar"><?= dashboardIcon('briefcase') ?></span>
                        <div>
                            <h2><?= h($vaga['empresa']) ?> · <?= h($vaga['titulo']) ?></h2>
                            <p><?= h($vaga['descricao'] ?: 'Sem descrição informada.') ?></p>
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
                    <article class="item-card" data-detail="<?= h($vaga['detalhe']) ?>" data-job-title="<?= h($vaga['titulo']) ?>">
                        <span class="card-avatar"><?= dashboardIcon('briefcase') ?></span>
                        <div>
                            <h2><?= h($vaga['empresa']) ?></h2>
                            <p><?= h($vaga['titulo']) ?> · <?= h($vaga['descricao'] ?: 'Sem descrição informada.') ?></p>
                        </div>
                        <form method="post">
                            <input type="hidden" name="action" value="apply_job">
                            <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="id_vaga" value="<?= (int) $vaga['id_vaga'] ?>">
                            <button class="btn primary" type="submit">Candidatar-se</button>
                        </form>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <aside class="detalhe-area">
            <?php if ($pagina === 'sobre'): ?>
                <h2>Contato</h2>
                <p>Fale com a equipe DevIN para conhecer melhor o projeto.</p>
            <?php elseif ($pagina === 'perfil'): ?>
                <h2 id="detailTitle">Detalhes da vaga</h2>
                <p>Use este espaco para visualizar detalhes da vaga.</p>
                <button class="btn primary fixed-action" type="button">Candidatar-se</button>
            <?php else: ?>
                <h2>Explicando sobre a vaga da empresa</h2>
                <p id="detailText">Selecione um card para ver a explicacao completa aqui.</p>
                <?php if ($pagina === 'vagas'): ?>
                    <span class="status aprovado fixed-action">Aprovado</span>
                <?php endif; ?>
            <?php endif; ?>
        </aside>
    </main>

    <dialog class="settings-modal profile-modal" id="profileModal" aria-labelledby="profileModalTitle">
        <form method="post" class="modal-form profile-form" enctype="multipart/form-data">
            <button class="modal-close" type="button" data-close-modal aria-label="Fechar">×</button>
            <h2 class="sr-only" id="profileModalTitle">Meu perfil</h2>
            <?php if (!empty($_SESSION['profile_error'])): ?>
                <p class="form-error"><?= h($_SESSION['profile_error']);
                unset($_SESSION['profile_error']); ?></p>
            <?php endif; ?>
            <?php if (!empty($_SESSION['profile_success'])): ?>
                <p class="form-success"><?= h($_SESSION['profile_success']);
                unset($_SESSION['profile_success']); ?></p>
            <?php endif; ?>
            <input type="hidden" name="action" value="update_profile"><input type="hidden" name="csrf_token"
                value="<?= h($_SESSION['csrf_token']) ?>">
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
                <label>E-mail account<input name="email" type="email" value="<?= h($perfilAtual['email']) ?>"
                        required></label>
                <label>Celular<input name="telefone" type="tel" value="<?= h($perfilAtual['telefone'] ?? '') ?>"
                        required></label>
                <label>CEP<input name="cep" type="text" value="<?= h($perfilAtual['cep'] ?? '') ?>" required></label>
            </div>
            <button class="profile-save" type="submit">Save</button>
        </form>
    </dialog>

    <dialog class="settings-modal" id="settingsModal">
        <form method="post" class="modal-form">
            <button class="modal-close" value="close" aria-label="Fechar">x</button>
            <h2>Configuracoes</h2>
            <label>Idioma
                <select name="idioma">
                    <option value="pt-BR" <?= ($perfilAtual['idioma'] ?? 'pt-BR') === 'pt-BR' ? 'selected' : '' ?>>
                        Português</option>
                    <option value="en" <?= ($perfilAtual['idioma'] ?? '') === 'en' ? 'selected' : '' ?>>Inglês</option>
                    <option value="es" <?= ($perfilAtual['idioma'] ?? '') === 'es' ? 'selected' : '' ?>>Espanhol</option>
                </select>
            </label>
            <input type="hidden" name="action" value="update_settings"><input type="hidden" name="csrf_token"
                value="<?= h($_SESSION['csrf_token']) ?>">
            <button class="btn primary" type="submit">Salvar idioma</button>
        </form>
        <form method="post" class="modal-form account-delete-form">
            <input type="hidden" name="action" value="delete_account">
            <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
            <button class="btn danger" type="submit" data-delete-account>Excluir conta</button>
        </form>
    </dialog>

    <script src="../js/dashboard.js"></script>
</body>

</html>
