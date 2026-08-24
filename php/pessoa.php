<?php
session_start();
require_once __DIR__ . '/controllers/ProfileController.php';

function ativo(string $paginaAtual, string $pagina): string
{
    return $paginaAtual === $pagina ? 'ativo' : '';
}

function h(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}

function profileAvatar(array $perfil, string $classes): string
{
    $foto = $perfil['foto'] ?? '';
    $imagem = is_string($foto) && str_starts_with($foto, 'uploads/')
        ? '<img src="' . h($foto) . '" alt="Foto de perfil">'
        : '';

    return '<span class="' . h($classes) . ' current-user-avatar">' . $imagem . '</span>';
}

function dashboardIcon(string $name): string
{
    $paths = match ($name) {
        'home' => '<path d="m3 10 9-7 9 7v10H3z"/><path d="M9 20v-6h6v6"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'briefcase' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 12h18"/>',
        'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.12 2.12-.06-.06a1.7 1.7 0 0 0-1.88-.34 1.7 1.7 0 0 0-1.03 1.56V20h-3v-.08A1.7 1.7 0 0 0 10.68 18.36a1.7 1.7 0 0 0-1.88.34l-.06.06-2.12-2.12.06-.06A1.7 1.7 0 0 0 7.02 14.7 1.7 1.7 0 0 0 5.46 13.7H5v-3h.08a1.7 1.7 0 0 0 1.56-1.03A1.7 1.7 0 0 0 6.3 7.8l-.06-.06 2.12-2.12.06.06a1.7 1.7 0 0 0 1.88.34A1.7 1.7 0 0 0 11.3 4.46V4h3v.08a1.7 1.7 0 0 0 1.03 1.56 1.7 1.7 0 0 0 1.88-.34l.06-.06 2.12 2.12-.06.06a1.7 1.7 0 0 0-.34 1.88 1.7 1.7 0 0 0 1.56 1.03H20v3h-.08a1.7 1.7 0 0 0-1.56 1.36z"/>',
        'logout' => '<path d="M10 17l5-5-5-5M15 12H3M21 19V5a2 2 0 0 0-2-2h-6"/>',
        default => '',
    };

    return '<svg class="ui-icon ui-icon-' . h($name) . '" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $paths . '</svg>';
}

// Proteção da página
if (empty($_SESSION['logado']) || ($_SESSION['usuario_tipo'] ?? '') !== 'pessoa') {
    header('Location: login.php');
    exit;
}

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
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? ''))
        exit('Solicitação inválida.');
    try {
        $action = $_POST['action'] ?? '';
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
        $_SESSION['profile_error'] = $exception->getMessage();
        header('Location: pessoa.php?perfil=meu');
        exit;
    }
}

$perfilAtual = findProfile($tipo, (int) $_SESSION['usuario_id']);
if (!$perfilAtual) {
    header('Location: logout.php');
    exit;
}

// Dados simulados da Pessoa
$vagasPessoa = [
    ['empresa' => 'Empresa X', 'titulo' => 'Estagio em Desenvolvimento', 'resumo' => 'Logica, HTML, CSS e vontade de aprender.', 'detalhe' => 'A vaga oferece mentoria, atividades de interface e apoio em projetos internos.', 'status' => 'Em analise'],
    ['empresa' => 'Empresa Y', 'titulo' => 'Assistente de TI', 'resumo' => 'Suporte, organizacao e comunicacao.', 'detalhe' => 'Atuacao com chamados, inventario de equipamentos e apoio aos colaboradores.', 'status' => 'Reprovado'],
];
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
                <a class="sair" href="logout.php"><?= dashboardIcon('logout') ?><span class="menu-text">Sair da
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

                <?php if (!empty($_SESSION['curriculo_success'])): ?>
                    <p class="dashboard-flash form-success">
                        <?= h($_SESSION['curriculo_success']) ?>
                    </p>
                    <?php unset($_SESSION['curriculo_success']); ?>
                <?php endif; ?>

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
                <?php foreach ($vagasPessoa as $vaga): ?>
                    <article class="item-card" data-detail="<?= h($vaga['detalhe']) ?>">
                        <span class="card-avatar"><?= dashboardIcon('user') ?></span>
                        <div>
                            <h2><?= h($vaga['empresa']) ?></h2>
                            <p><?= h($vaga['resumo']) ?></p>
                        </div>
                        <span
                            class="status <?= $vaga['status'] === 'Reprovado' ? 'reprovado' : 'analise' ?>"><?= h($vaga['status']) ?></span>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <?php foreach ($vagasPessoa as $vaga): ?>
                    <article class="item-card" data-detail="<?= h($vaga['detalhe']) ?>">
                        <span class="card-avatar"><?= dashboardIcon('user') ?></span>
                        <div>
                            <h2><?= h($vaga['empresa']) ?></h2>
                            <p><?= h($vaga['resumo']) ?></p>
                        </div>
                        <button class="btn primary" type="button">Candidatar-se</button>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <aside class="detalhe-area">
            <?php if ($pagina === 'sobre'): ?>
                <h2>Contato</h2>
                <p>Fale com a equipe DevIN para conhecer melhor o projeto.</p>
            <?php elseif ($pagina === 'perfil'): ?>
                <h2>Explicando tudo sobre a vaga selecionada</h2>
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
