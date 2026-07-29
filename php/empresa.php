<?php
// Inicia ou recupera a sessão do usuário (a "memória" que mantém o usuário logado)
session_start();

// Carrega o controlador de perfil (regras de atualização, busca de dados, etc.)
require_once __DIR__ . '/controllers/ProfileController.php';

// Carrega o arquivo de funções auxiliares (como ícones, formatação de textos e fotos)
require_once __DIR__ . '/helpers.php';

// --- PROTEÇÃO DA PÁGINA ---
// Verifica se o usuário NÃO está logado OU se o tipo de usuário NÃO é 'empresa'.
// Se alguma dessas condições for verdadeira, manda o usuário direto para a tela de login.
if (empty($_SESSION['logado']) || ($_SESSION['usuario_tipo'] ?? '') !== 'empresa') {
    header('Location: login.php'); // Redireciona para o login
    exit; // Encerra o script para não carregar mais nada por segurança
}

// Define o tipo de perfil atual como 'empresa'
$tipo = 'empresa';

// Pega o nome do usuário da sessão (se não existir, usa 'Usuario' como padrão)
$nome = $_SESSION['usuario_nome'] ?? 'Usuario';

// Pega o e-mail do usuário da sessão (se não existir, usa um e-mail padrão)
$email = $_SESSION['usuario_email'] ?? 'email@devin.com';

// Pega a página informada na URL (ex: ?pagina=candidatos). Se não houver nada na URL, carrega 'inicio'
$pagina = $_GET['pagina'] ?? 'inicio';

// --- VERIFICAÇÃO DE PÁGINAS PERMITIDAS (SEGURANÇA) ---
// Lista de páginas que o sistema realmente aceita exibir
$paginasPermitidas = ['inicio', 'candidatos', 'sobre', 'perfil'];

// Se a página vinda da URL não estiver na lista permitida, força o usuário a ir para 'inicio'
if (!in_array($pagina, $paginasPermitidas, true)) {
    $pagina = 'inicio';
}

// --- PROTEÇÃO CONTRA ATAQUES CSRF ---
// Se ainda não existir um token de segurança para esta sessão, gera um código aleatório e seguro
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// --- LÓGICA DE ENVIO DE FORMULÁRIOS (QUANDO É UM POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Valida se o código de segurança do formulário bate com o código da sessão (evita envios maliciosos)
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) exit('Solicitação inválida.');
    
    try {
        // Descobre qual ação o formulário pediu para executar
        $action = $_POST['action'] ?? '';

        // Ação 1: Atualizar o perfil da empresa (dados pessoais e foto)
        if ($action === 'update_profile') {
            updateProfile($tipo, (int) $_SESSION['usuario_id'], $_POST, $_FILES['foto'] ?? null);
            $_SESSION['usuario_nome'] = trim($_POST['nome']);
            $_SESSION['usuario_email'] = trim($_POST['email']);
            $_SESSION['profile_success'] = 'Perfil atualizado com sucesso.';
            header('Location: empresa.php?perfil=meu'); exit;
        }

        // Ação 2: Atualizar as configurações de idioma
        if ($action === 'update_settings') {
            updateLanguage($tipo, (int) $_SESSION['usuario_id'], $_POST['idioma'] ?? 'pt-BR');
            header('Location: empresa.php?configuracoes=1'); exit;
        }

        // Ação 3: Excluir a conta
        if ($action === 'delete_account') {
            deleteProfile($tipo, (int) $_SESSION['usuario_id']);
            session_destroy(); // Destrói todas as informações guardadas na sessão
            header('Location: login.php'); exit;
        }
    } catch (Throwable $exception) {
        // Se acontecer qualquer erro durante os processos acima, guarda a mensagem de erro
        $_SESSION['profile_error'] = $exception->getMessage();
        header('Location: empresa.php?perfil=meu'); exit;
    }
}

// Busca no banco de dados todas as informações da empresa conectada
$perfilAtual = findProfile($tipo, (int) $_SESSION['usuario_id']);

// Se por algum motivo o perfil não existir mais no banco, encerra a sessão
if (!$perfilAtual) { header('Location: logout.php'); exit; }

// --- DADOS SIMULADOS (Para exibição na tela) ---
// Lista de vagas criadas pela empresa
$empresaPosts = [
    ['titulo' => 'Desenvolvedor Front-end', 'resumo' => 'HTML, CSS, JavaScript e portfolio simples.', 'detalhe' => 'Vaga para criar telas responsivas, manter paginas existentes e colaborar com a equipe de design.'],
    ['titulo' => 'Analista de Suporte', 'resumo' => 'Atendimento, redes basicas e organizacao.', 'detalhe' => 'Buscamos uma pessoa comunicativa para registrar chamados, orientar usuarios e resolver problemas iniciais.'],
];

// Lista de talentos/desenvolvedores disponíveis na plataforma
$talentos = [
    ['nome' => 'Marina Santos', 'resumo' => 'React, CSS e comunicacao clara.', 'detalhe' => 'Marina tem interesse em vagas de front-end junior e disponibilidade para conversar esta semana.'],
    ['nome' => 'Lucas Pereira', 'resumo' => 'PHP, MySQL e logica de programacao.', 'detalhe' => 'Lucas procura primeira oportunidade em desenvolvimento web e ja criou projetos escolares com banco de dados.'],
];

// Lista de candidatos que se inscreveram nas vagas da empresa
$candidatos = [
    ['nome' => 'Ana Clara', 'vaga' => 'Desenvolvedor Front-end', 'resumo' => 'Boa base de HTML, CSS e Git.', 'detalhe' => 'A candidata se inscreveu para Front-end e enviou portfolio com paginas responsivas.'],
    ['nome' => 'Pedro Lima', 'vaga' => 'Analista de Suporte', 'resumo' => 'Conhecimento em atendimento e manutencao.', 'detalhe' => 'O candidato descreveu experiencia em suporte tecnico escolar e disponibilidade integral.'],
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevIN | Dashboard Empresa</title>
    <link rel="stylesheet" href="../css/dashboard.css?v=<?= filemtime(__DIR__ . '/../css/dashboard.css') ?>">
</head>
<body>
    <main class="dashboard-shell page-<?= h($pagina) ?>" data-tipo="<?= h($tipo) ?>">
        
        <aside class="sidebar">
            <div class="sidebar-topo">
                <a class="brand" href="empresa.php">
                    <?= dashboardIcon('brand') ?>
                    <span class="brand-text">Dev<span>IN</span></span>
                </a>
                <button class="menu-toggle" type="button" aria-label="Abrir ou fechar menu" aria-expanded="true" data-toggle-menu>
                    <span></span><span></span><span></span>
                </button>
            </div>

            <nav class="menu-principal" aria-label="Menu principal">
                <a class="<?= ativo($pagina, 'inicio') ?>" href="empresa.php?pagina=inicio"><?= dashboardIcon('home') ?><span class="menu-text">Inicio</span></a>
                <a class="<?= ativo($pagina, 'candidatos') ?>" href="empresa.php?pagina=candidatos"><?= dashboardIcon('users') ?><span class="menu-text">Candidatos</span></a>
                <a class="<?= ativo($pagina, 'sobre') ?>" href="empresa.php?pagina=sobre"><?= dashboardIcon('info') ?><span class="menu-text">Sobre nos</span></a>
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
                    <h1>Dashboard Empresa</h1>
                </div>
            </header>

            <form class="busca" action="" method="get">
                <input type="hidden" name="pagina" value="<?= h($pagina) ?>">
                <label>
                    <input type="search" name="q" placeholder="Pesquise candidatos ou vagas">
                </label>
            </form>
            <?php endif; ?>

            <?php if ($pagina === 'sobre'): ?>
                <section class="sobre-intro">
                    <span class="tag">Nossa historia</span>
                    <h1>Conectando talentos ao futuro da tecnologia</h1>
                    <p>A DevIN nasceu para transformar a forma como desenvolvedores encontram oportunidades: simples, rapido e eficiente.</p>
                </section>

            <?php elseif ($pagina === 'perfil'): ?>
                <section class="perfil-card">
                    <a class="fechar-card" href="empresa.php?pagina=inicio">x</a>
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

            <?php else: ?>
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
            <?php else: ?>
                <h2><?= h($pagina === 'candidatos' ? 'Vaga em que o candidato se inscreveu' : 'Explicando tudo sobre a pessoa selecionada') ?></h2>
                <p id="detailText">Selecione um card para ver a explicacao completa aqui.</p>
                <?php if ($pagina === 'candidatos'): ?>
                    <div class="detalhe-botoes">
                        <button class="btn danger" type="button">Nao se encaixa</button>
                        <button class="btn success" type="button">Aprovar</button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </aside>
    </main>

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
            <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">

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
                <label>Celular<input name="telefone" type="tel" value="<?= h($perfilAtual['telefone'] ?? '') ?>" required></label>
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
                    <option value="pt-BR" <?= ($perfilAtual['idioma'] ?? 'pt-BR') === 'pt-BR' ? 'selected' : '' ?>>Português</option>
                    <option value="en" <?= ($perfilAtual['idioma'] ?? '') === 'en' ? 'selected' : '' ?>>Inglês</option>
                    <option value="es" <?= ($perfilAtual['idioma'] ?? '') === 'es' ? 'selected' : '' ?>>Espanhol</option>
                </select>
            </label>
            <input type="hidden" name="action" value="update_settings">
            <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
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