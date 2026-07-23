<?php
session_start();
require_once __DIR__ . '/controllers/ProfileController.php';

if (empty($_SESSION['logado'])) {
    header('Location: login.php');
    exit;
}

$tipo = $_SESSION['usuario_tipo'] ?? 'pessoa';
$nome = $_SESSION['usuario_nome'] ?? 'Usuario';
$email = $_SESSION['usuario_email'] ?? 'email@devin.com';
$pagina = $_GET['pagina'] ?? 'inicio';

if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) exit('Solicitação inválida.');
    try {
        $action = $_POST['action'] ?? '';
        if ($action === 'update_profile') {
            updateProfile($tipo, (int) $_SESSION['usuario_id'], $_POST, $_FILES['foto'] ?? null);
            $_SESSION['usuario_nome'] = trim($_POST['nome']);
            $_SESSION['usuario_email'] = trim($_POST['email']);
            $_SESSION['profile_success'] = 'Perfil atualizado com sucesso.';
            header('Location: dashboard.php?perfil=meu'); exit;
        }
        if ($action === 'update_settings') {
            updateLanguage($tipo, (int) $_SESSION['usuario_id'], $_POST['idioma'] ?? 'pt-BR');
            header('Location: dashboard.php?configuracoes=1'); exit;
        }
        if ($action === 'delete_account') {
            deleteProfile($tipo, (int) $_SESSION['usuario_id']);
            session_destroy();
            header('Location: login.php'); exit;
        }
    } catch (Throwable $exception) {
        $_SESSION['profile_error'] = $exception->getMessage();
        header('Location: dashboard.php?perfil=meu'); exit;
    }
}

$perfilAtual = findProfile($tipo, (int) $_SESSION['usuario_id']);
if (!$perfilAtual) { header('Location: logout.php'); exit; }

$paginasPermitidas = [
    'empresa' => ['inicio', 'candidatos', 'sobre', 'perfil'],
    'pessoa' => ['inicio', 'vagas', 'sobre', 'perfil'],
    'adm' => ['inicio', 'candidatos', 'sobre', 'perfil'],
];

if (!in_array($pagina, $paginasPermitidas[$tipo] ?? $paginasPermitidas['pessoa'], true)) {
    $pagina = 'inicio';
}

$empresaPosts = [
    ['titulo' => 'Desenvolvedor Front-end', 'resumo' => 'HTML, CSS, JavaScript e portfolio simples.', 'detalhe' => 'Vaga para criar telas responsivas, manter paginas existentes e colaborar com a equipe de design.'],
    ['titulo' => 'Analista de Suporte', 'resumo' => 'Atendimento, redes basicas e organizacao.', 'detalhe' => 'Buscamos uma pessoa comunicativa para registrar chamados, orientar usuarios e resolver problemas iniciais.'],
];

$talentos = [
    ['nome' => 'Marina Santos', 'resumo' => 'React, CSS e comunicacao clara.', 'detalhe' => 'Marina tem interesse em vagas de front-end junior e disponibilidade para conversar esta semana.'],
    ['nome' => 'Lucas Pereira', 'resumo' => 'PHP, MySQL e logica de programacao.', 'detalhe' => 'Lucas procura primeira oportunidade em desenvolvimento web e ja criou projetos escolares com banco de dados.'],
];

$candidatos = [
    ['nome' => 'Ana Clara', 'vaga' => 'Desenvolvedor Front-end', 'resumo' => 'Boa base de HTML, CSS e Git.', 'detalhe' => 'A candidata se inscreveu para Front-end e enviou portfolio com paginas responsivas.'],
    ['nome' => 'Pedro Lima', 'vaga' => 'Analista de Suporte', 'resumo' => 'Conhecimento em atendimento e manutencao.', 'detalhe' => 'O candidato descreveu experiencia em suporte tecnico escolar e disponibilidade integral.'],
];

$vagasPessoa = [
    ['empresa' => 'Empresa X', 'titulo' => 'Estagio em Desenvolvimento', 'resumo' => 'Logica, HTML, CSS e vontade de aprender.', 'detalhe' => 'A vaga oferece mentoria, atividades de interface e apoio em projetos internos.', 'status' => 'Em analise'],
    ['empresa' => 'Empresa Y', 'titulo' => 'Assistente de TI', 'resumo' => 'Suporte, organizacao e comunicacao.', 'detalhe' => 'Atuacao com chamados, inventario de equipamentos e apoio aos colaboradores.', 'status' => 'Reprovado'],
];

$postsAdmin = [
    ['empresa' => 'Empresa Alfa', 'titulo' => 'Post da vaga', 'detalhe' => 'Revise a descricao da vaga, requisitos, salario informado e aderencia as diretrizes do site.'],
    ['empresa' => 'Empresa Beta', 'titulo' => 'Post da vaga', 'detalhe' => 'Este post foi sinalizado para avaliacao do administrador antes de continuar visivel.'],
];

$usuariosAdmin = [
    ['nome' => 'Carlos Souza', 'resumo' => 'Perfil de candidato em verificacao.', 'detalhe' => 'Verifique foto, e-mail, dados basicos e comportamento do perfil antes de remover a conta.'],
    ['nome' => 'Bianca Rocha', 'resumo' => 'Conta com dados incompletos.', 'detalhe' => 'A conta precisa ser avaliada por suspeita de informacoes falsas.'],
];

$tituloLateral = [
    'empresa' => 'Explicando tudo sobre a pessoa selecionada',
    'pessoa' => 'Explicando sobre a vaga da empresa',
    'adm' => 'Explicando tudo sobre o post da vaga selecionada',
];

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
        'brand' => '<path d="M4 5.5h16v13H4z"/><path d="M12 5.5v13"/>',
        'home' => '<path d="m3 10 9-7 9 7v10H3z"/><path d="M9 20v-6h6v6"/>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'building' => '<path d="M3 21h18M5 21V5h10v16M15 9h4v12M8 9h4M8 13h4M8 17h4"/>',
        'briefcase' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M3 12h18"/>',
        'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.12 2.12-.06-.06a1.7 1.7 0 0 0-1.88-.34 1.7 1.7 0 0 0-1.03 1.56V20h-3v-.08A1.7 1.7 0 0 0 10.68 18.36a1.7 1.7 0 0 0-1.88.34l-.06.06-2.12-2.12.06-.06A1.7 1.7 0 0 0 7.02 14.7 1.7 1.7 0 0 0 5.46 13.7H5v-3h.08a1.7 1.7 0 0 0 1.56-1.03A1.7 1.7 0 0 0 6.3 7.8l-.06-.06 2.12-2.12.06.06a1.7 1.7 0 0 0 1.88.34A1.7 1.7 0 0 0 11.3 4.46V4h3v.08a1.7 1.7 0 0 0 1.03 1.56 1.7 1.7 0 0 0 1.88-.34l.06-.06 2.12 2.12-.06.06a1.7 1.7 0 0 0-.34 1.88 1.7 1.7 0 0 0 1.56 1.03H20v3h-.08A1.7 1.7 0 0 0 18.36 14.36z"/>',
        'logout' => '<path d="M10 17l5-5-5-5M15 12H3M21 19V5a2 2 0 0 0-2-2h-6"/>',
        'edit' => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4z"/>',
        'trash' => '<path d="M3 6h18M8 6V4h8v2M19 6l-1 15H6L5 6M10 11v6M14 11v6"/>',
        default => '',
    };

    return '<svg class="ui-icon ui-icon-' . h($name) . '" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $paths . '</svg>';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevIN | Dashboard</title>
    <link rel="stylesheet" href="../css/dashboard.css?v=<?= filemtime(__DIR__ . '/../css/dashboard.css') ?>">
</head>
<body>
    <main class="dashboard-shell page-<?= h($pagina) ?>" data-tipo="<?= h($tipo) ?>">
        <aside class="sidebar">
            <div class="sidebar-topo">
                <a class="brand" href="dashboard.php">
                    <?= dashboardIcon('brand') ?>
                    <span class="brand-text">Dev<span>IN</span></span>
                </a>
                <button class="menu-toggle" type="button" aria-label="Abrir ou fechar menu" aria-expanded="true" data-toggle-menu>
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>

            <nav class="menu-principal" aria-label="Menu principal">
                <?php if ($tipo === 'empresa'): ?>
                    <a class="<?= ativo($pagina, 'inicio') ?>" href="?pagina=inicio"><?= dashboardIcon('home') ?><span class="menu-text">Inicio</span></a>
                    <a class="<?= ativo($pagina, 'candidatos') ?>" href="?pagina=candidatos"><?= dashboardIcon('users') ?><span class="menu-text">Candidatos</span></a>
                <?php elseif ($tipo === 'adm'): ?>
                    <a class="<?= ativo($pagina, 'inicio') ?>" href="?pagina=inicio"><?= dashboardIcon('building') ?><span class="menu-text">Empresas</span></a>
                    <a class="<?= ativo($pagina, 'candidatos') ?>" href="?pagina=candidatos"><?= dashboardIcon('users') ?><span class="menu-text">Candidatos</span></a>
                <?php else: ?>
                    <a class="<?= ativo($pagina, 'inicio') ?>" href="?pagina=inicio"><?= dashboardIcon('home') ?><span class="menu-text">Inicio</span></a>
                    <a class="<?= ativo($pagina, 'vagas') ?>" href="?pagina=vagas"><?= dashboardIcon('briefcase') ?><span class="menu-text">Vagas</span></a>
                <?php endif; ?>
                <a class="<?= ativo($pagina, 'sobre') ?>" href="?pagina=sobre"><?= dashboardIcon('info') ?><span class="menu-text">Sobre nos</span></a>
            </nav>

            <div class="conta">
                <details class="perfil-dropdown">
                    <summary class="perfil-link"><?= profileAvatar($perfilAtual, 'avatar-mini') ?><span class="menu-text">Perfil</span></summary>
                    <div class="perfil-menu">
                        <button type="button" data-open-profile><?= profileAvatar($perfilAtual, 'avatar-foto') ?><span class="menu-text">Meu perfil</span></button>
                        <button type="button" data-open-settings><?= dashboardIcon('settings') ?><span class="menu-text">Configuracoes</span></button>
                    </div>
                </details>
                <a class="sair" href="logout.php"><?= dashboardIcon('logout') ?><span class="menu-text">Sair da Conta</span></a>
            </div>
        </aside>

        <section class="lista-area">
            <?php if ($pagina !== 'sobre'): ?>
            <header class="dashboard-header">
                <div>
                    <span>Painel DevIN</span>
                    <h1>Dashboard</h1>
                </div>
            </header>

            <form class="busca" action="" method="get">
                <input type="hidden" name="pagina" value="<?= h($pagina) ?>">
                <label>
                    <span></span>
                    <input type="search" name="q" placeholder="Pesquise">
                </label>
            </form>
            <?php endif; ?>

            <?php if ($pagina === 'sobre'): ?>
                <section class="sobre-intro">
                    <span class="tag">Nossa historia</span>
                    <h1>Conectando talentos ao futuro da tecnologia</h1>
                    <p>A DevIN nasceu para transformar a forma como desenvolvedores encontram oportunidades: simples, rapido e eficiente.</p>
                </section>
                <div class="sobre-cards">
                    <article>
                        <span class="card-icon"></span>
                        <h2>Visao</h2>
                        <p>Ser referencia na conexao entre talentos de tecnologia e empresas, promovendo crescimento profissional e inovacao.</p>
                    </article>
                    <article>
                        <span class="card-icon"></span>
                        <h2>Missao</h2>
                        <p>Conectar desenvolvedores de todos os niveis a oportunidades de trabalho, tornando o recrutamento mais simples.</p>
                    </article>
                </div>
                <section class="fundadores">
                    <div>
                        <h2>Quem somos nos?</h2>
                        <p>A DevIN nasceu com o proposito de transformar a forma como profissionais de tecnologia encontram oportunidades.</p>
                        <p>Hoje, oferecemos um ambiente moderno onde empresas podem divulgar vagas e gerenciar candidatos.</p>
                    </div>
                    <div class="time-card">
                        <h2>Time fundador</h2>
                        <p><strong>Caua Chiappin de Lima</strong><br>Co-fundador</p>
                        <p><strong>Enzo Vasconcelos de Camargo</strong><br>Co-fundador</p>
                        <p><strong>Joao Vitor da Silva e Souza</strong><br>Co-fundador</p>
                    </div>
                </section>
            <?php elseif ($pagina === 'perfil'): ?>
                <section class="perfil-card">
                    <a class="fechar-card" href="?pagina=inicio">x</a>
                    <div class="perfil-topo">
                        <?= profileAvatar($perfilAtual, 'avatar-grande') ?>
                        <div>
                            <strong><?= h($nome) ?></strong>
                            <small><?= h($email) ?></small>
                        </div>
                    </div>
                    <button class="btn primary" type="button" data-open-profile>Editar perfil</button>
                </section>
            <?php elseif ($tipo === 'empresa' && $pagina === 'candidatos'): ?>
                <?php foreach ($candidatos as $candidato): ?>
                    <article class="item-card" data-detail="<?= h($candidato['detalhe']) ?>">
                        <span class="card-avatar"><?= dashboardIcon('user') ?></span>
                        <div>
                            <h2><?= h($candidato['nome']) ?></h2>
                            <p><?= h($candidato['resumo']) ?></p>
                        </div>
                        <div class="acoes-card">
                            <button class="btn danger" type="button">Nao se encaixa</button>
                            <button class="btn success" type="button">Aprovar</button>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php elseif ($tipo === 'empresa'): ?>
                <button class="criar-post" type="button">Criar post</button>
                <?php foreach ($empresaPosts as $post): ?>
                    <article class="item-card" data-detail="<?= h($post['detalhe']) ?>">
                        <span class="card-avatar"><?= dashboardIcon('user') ?></span>
                        <div>
                            <h2><?= h($post['titulo']) ?></h2>
                            <p><?= h($post['resumo']) ?></p>
                        </div>
                        <div class="post-tools">
                            <button class="edit" type="button" aria-label="Editar post"><?= dashboardIcon('edit') ?></button>
                            <button class="delete" type="button" aria-label="Excluir post"><?= dashboardIcon('trash') ?></button>
                        </div>
                    </article>
                <?php endforeach; ?>
                <?php foreach ($talentos as $talento): ?>
                    <article class="item-card" data-detail="<?= h($talento['detalhe']) ?>">
                        <span class="card-avatar"><?= dashboardIcon('user') ?></span>
                        <div>
                            <h2><?= h($talento['nome']) ?></h2>
                            <p><?= h($talento['resumo']) ?></p>
                        </div>
                        <button class="btn primary" type="button">Conversar</button>
                    </article>
                <?php endforeach; ?>
            <?php elseif ($tipo === 'adm' && $pagina === 'candidatos'): ?>
                <?php foreach ($usuariosAdmin as $usuario): ?>
                    <article class="item-card" data-detail="<?= h($usuario['detalhe']) ?>">
                        <span class="card-avatar"><?= dashboardIcon('user') ?></span>
                        <div>
                            <h2><?= h($usuario['nome']) ?></h2>
                            <p><?= h($usuario['resumo']) ?></p>
                        </div>
                        <button class="btn danger" type="button">Excluir perfil</button>
                    </article>
                <?php endforeach; ?>
            <?php elseif ($tipo === 'adm'): ?>
                <?php foreach ($postsAdmin as $post): ?>
                    <article class="item-card" data-detail="<?= h($post['detalhe']) ?>">
                        <span class="card-avatar"><?= dashboardIcon('user') ?></span>
                        <div>
                            <h2><?= h($post['empresa']) ?></h2>
                            <p><?= h($post['titulo']) ?></p>
                            <a href="#">Saiba Mais...</a>
                        </div>
                        <button class="btn danger" type="button">Excluir Post</button>
                    </article>
                <?php endforeach; ?>
            <?php elseif ($pagina === 'vagas'): ?>
                <?php foreach ($vagasPessoa as $vaga): ?>
                    <article class="item-card" data-detail="<?= h($vaga['detalhe']) ?>">
                        <span class="card-avatar"><?= dashboardIcon('user') ?></span>
                        <div>
                            <h2><?= h($vaga['empresa']) ?></h2>
                            <p><?= h($vaga['resumo']) ?></p>
                        </div>
                        <span class="status <?= $vaga['status'] === 'Reprovado' ? 'reprovado' : 'analise' ?>"><?= h($vaga['status']) ?></span>
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
                <p>Fale com a equipe DevIN para conhecer melhor o projeto, enviar sugestoes ou pedir suporte.</p>
                <p><strong>E-mail:</strong> contato@devin.com.br</p>
            <?php elseif ($pagina === 'perfil'): ?>
                <h2>Explicando tudo sobre a vaga selecionada</h2>
                <p>Use este espaco para visualizar detalhes da vaga, pessoa ou post escolhido no painel.</p>
                <button class="btn primary fixed-action" type="button">Candidatar-se</button>
            <?php else: ?>
                <h2><?= h($pagina === 'candidatos' ? 'Vaga em que o candidato se inscreveu' : $tituloLateral[$tipo]) ?></h2>
                <p id="detailText"><?= h($tipo === 'adm' ? 'Selecione um post ou candidato para analisar as informacoes.' : 'Selecione um card para ver a explicacao completa aqui.') ?></p>
                <?php if ($tipo === 'empresa' && $pagina === 'candidatos'): ?>
                    <div class="detalhe-botoes">
                        <button class="btn danger" type="button">Nao se encaixa</button>
                        <button class="btn success" type="button">Aprovar</button>
                    </div>
                <?php elseif ($tipo === 'adm'): ?>
                    <button class="btn danger fixed-action" type="button">Excluir Post</button>
                <?php elseif ($tipo === 'pessoa' && $pagina === 'vagas'): ?>
                    <span class="status aprovado fixed-action">Aprovado</span>
                <?php endif; ?>
            <?php endif; ?>
        </aside>
    </main>

    <dialog class="settings-modal profile-modal" id="profileModal" aria-labelledby="profileModalTitle">
        <form method="post" class="modal-form profile-form" enctype="multipart/form-data">
            <button class="modal-close" type="button" data-close-modal aria-label="Fechar">×</button>
            <h2 class="sr-only" id="profileModalTitle">Meu perfil</h2>
            <?php if (!empty($_SESSION['profile_error'])): ?><p class="form-error"><?= h($_SESSION['profile_error']); unset($_SESSION['profile_error']); ?></p><?php endif; ?>
            <?php if (!empty($_SESSION['profile_success'])): ?><p class="form-success"><?= h($_SESSION['profile_success']); unset($_SESSION['profile_success']); ?></p><?php endif; ?>
            <input type="hidden" name="action" value="update_profile"><input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
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
                <label>E-mail account<input name="email" type="email" value="<?= h($perfilAtual['email']) ?>" required></label>
                <?php if ($tipo !== 'adm'): ?>
                    <label>Celular<input name="telefone" type="tel" value="<?= h($perfilAtual['telefone']) ?>" required></label>
                    <label>CEP<input name="cep" type="text" value="<?= h($perfilAtual['cep']) ?>" required></label>
                <?php endif; ?>
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
                    <option value="pt-BR" <?= $perfilAtual['idioma'] === 'pt-BR' ? 'selected' : '' ?>>Português</option>
                    <option value="en" <?= $perfilAtual['idioma'] === 'en' ? 'selected' : '' ?>>Inglês</option>
                    <option value="es" <?= $perfilAtual['idioma'] === 'es' ? 'selected' : '' ?>>Espanhol</option>
                </select>
            </label>
            <input type="hidden" name="action" value="update_settings"><input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
            <button class="btn primary" type="submit">Salvar idioma</button>
        </form>
        <form method="post" class="modal-form account-delete-form"><input type="hidden" name="action" value="delete_account"><input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>"><button class="btn danger" type="submit" data-delete-account>Excluir conta</button></form>
    </dialog>

    <script src="../js/dashboard.js"></script>
</body>
</html>
