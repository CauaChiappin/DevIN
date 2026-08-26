<?php
require_once __DIR__ . '/middlewares/auth.php';
require_once __DIR__ . '/controllers/ProfileController.php';
require_once __DIR__ . '/helpers.php';

$usuarioAtual = requireWebAuth('adm');

$tipo = 'adm';
$nome = $_SESSION['usuario_nome'] ?? 'Usuario';
$email = $_SESSION['usuario_email'] ?? 'email@devin.com';
$pagina = $_GET['pagina'] ?? 'inicio';

$paginasPermitidas = ['inicio', 'candidatos', 'sobre', 'perfil'];
if (!in_array($pagina, $paginasPermitidas, true)) {
    $pagina = 'inicio';
}

if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrf();
    try {
        $action = $_POST['action'] ?? '';
        if (in_array($action, ['admin_delete_pessoa', 'admin_delete_empresa', 'admin_delete_vaga'], true)) {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new InvalidArgumentException('Registro inválido.');
            }

            $targets = [
                'admin_delete_pessoa' => ['pessoa', 'id_pessoa'],
                'admin_delete_empresa' => ['empresa', 'id_empresa'],
                'admin_delete_vaga' => ['vagas', 'id_vaga'],
            ];

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
            header('Location: adm.php?pagina=' . ($action === 'admin_delete_vaga' ? 'inicio' : 'candidatos'));
            exit;
        }

        if ($action === 'update_profile') {
            updateProfile($tipo, (int) $_SESSION['usuario_id'], $_POST, $_FILES['foto'] ?? null);
            $_SESSION['usuario_nome'] = trim($_POST['nome']);
            $_SESSION['usuario_email'] = trim($_POST['email']);
            $_SESSION['profile_success'] = 'Perfil atualizado com sucesso.';
            header('Location: adm.php?perfil=meu'); exit;
        }
        if ($action === 'update_settings') {
            updateLanguage($tipo, (int) $_SESSION['usuario_id'], $_POST['idioma'] ?? 'pt-BR');
            header('Location: adm.php?configuracoes=1'); exit;
        }
        if ($action === 'delete_account') {
            deleteProfile($tipo, (int) $_SESSION['usuario_id']);
            session_destroy();
            header('Location: login.php'); exit;
        }
    } catch (Throwable $exception) {
        error_log('Erro no dashboard ADM: ' . $exception->getMessage());
        if (str_starts_with($action, 'admin_delete_')) {
            $_SESSION['admin_error'] = 'Não foi possível excluir o registro. Ele pode possuir dados relacionados.';
            header('Location: adm.php?pagina=' . ($action === 'admin_delete_vaga' ? 'inicio' : 'candidatos'));
        } else {
            $_SESSION['profile_error'] = 'Não foi possível concluir a operação. Tente novamente.';
            header('Location: adm.php?perfil=meu');
        }
        exit;
    }
}

$perfilAtual = findProfile($tipo, (int) $_SESSION['usuario_id']);
if (!$perfilAtual) { header('Location: logout.php'); exit; }

// Dados reais para moderação.
$empresasAdmin = [];
$usuariosAdmin = [];
$vagasAdmin = [];

try {
    $conn = getDatabaseConnection();

    $result = $conn->query('SELECT id_empresa AS id, nome, email, cnpj FROM empresa ORDER BY nome');
    $empresasAdmin = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

    $result = $conn->query('SELECT id_pessoa AS id, nome, email, cpf FROM pessoa ORDER BY nome');
    $usuariosAdmin = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

    $result = $conn->query("SELECT v.id_vaga AS id, v.titulo, COALESCE(v.descricao, '') AS descricao, e.nome AS empresa FROM vagas v INNER JOIN empresa e ON e.id_empresa = v.id_empresa ORDER BY v.id_vaga DESC");
    $vagasAdmin = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];

    $conn->close();
} catch (Throwable $exception) {
    error_log('Erro ao carregar moderação ADM: ' . $exception->getMessage());
    $_SESSION['admin_error'] = 'Não foi possível carregar os registros agora.';
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevIN | Dashboard Administrativo</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link rel="icon" type="image/png" href="../img/favicon.png">
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
                <a class="<?= ativo($pagina, 'candidatos') ?>" href="adm.php?pagina=candidatos"><?= dashboardIcon('user') ?><span class="menu-text">Candidatos</span></a>
                <a class="<?= ativo($pagina, 'sobre') ?>" href="adm.php?pagina=sobre"><?= dashboardIcon('info') ?><span class="menu-text">Sobre nos</span></a>
            </nav>

            <div class="conta">
                <details class="perfil-dropdown">
                    <summary class="perfil-link"><?= profileAvatar($perfilAtual, 'avatar-mini') ?><span class="menu-text">Perfil</span></summary>
                    <div class="perfil-menu">
                        <button type="button" data-open-profile><?= profileAvatar($perfilAtual, 'avatar-foto') ?><span class="menu-text">Meu perfil</span></button>
                        <button type="button" data-open-settings><?= dashboardIcon('settings') ?><span class="menu-text">Configuracoes</span></button>
                    </div>
                </details>
                <a class="sair" href="logout.php" data-confirm-logout="Tem certeza que deseja sair da sua conta?"><?= dashboardIcon('logout') ?><span class="menu-text">Sair da Conta</span></a>
            </div>
        </aside>

        <section class="lista-area">
            <?php if (!empty($_SESSION['admin_error'])): ?><p class="form-error"><?= h($_SESSION['admin_error']); unset($_SESSION['admin_error']); ?></p><?php endif; ?>
            <?php if (!empty($_SESSION['admin_success'])): ?><p class="form-success"><?= h($_SESSION['admin_success']); unset($_SESSION['admin_success']); ?></p><?php endif; ?>
            <?php if ($pagina !== 'sobre'): ?>
            <header class="dashboard-header">
                <div>
                    <span>Painel DevIN</span>
                    <h1>Moderação ADM</h1>
                </div>
            </header>

            <form class="busca" action="" method="get">
                <input type="hidden" name="pagina" value="<?= h($pagina) ?>">
                <label>
                    <input type="search" name="q" placeholder="Pesquisar registros">
                </label>
            </form>
            <?php endif; ?>

            <?php if ($pagina === 'sobre'): ?>
                <?= aboutPage() ?>
            <?php elseif ($pagina === 'perfil'): ?>
                <section class="perfil-card">
                    <a class="fechar-card" href="adm.php?pagina=inicio">x</a>
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
                    <article class="item-card" data-detail="<?= h('E-mail: ' . $usuario['email'] . ' | CPF: ' . $usuario['cpf']) ?>">
                        <span class="card-avatar"><?= dashboardIcon('user') ?></span>
                        <div>
                            <h2><?= h($usuario['nome']) ?></h2>
                            <p><?= h($usuario['email']) ?></p>
                        </div>
                        <form method="post" onsubmit="return confirm('Excluir este candidato permanentemente?');">
                            <input type="hidden" name="action" value="admin_delete_pessoa">
                            <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="id" value="<?= (int) $usuario['id'] ?>">
                            <button class="btn danger" type="submit">Excluir perfil</button>
                        </form>
                    </article>
                <?php endforeach; ?>
            <?php else: ?>
                <?php if (!$empresasAdmin && !$vagasAdmin): ?><p class="empty-state">Nenhum registro disponível.</p><?php endif; ?>
                <?php foreach ($empresasAdmin as $empresa): ?>
                    <article class="item-card" data-detail="<?= h('E-mail: ' . $empresa['email'] . ' | CNPJ: ' . $empresa['cnpj']) ?>">
                        <span class="card-avatar"><?= dashboardIcon('building') ?></span>
                        <div>
                            <h2><?= h($empresa['nome']) ?></h2>
                            <p><?= h($empresa['email']) ?></p>
                        </div>
                        <form method="post" onsubmit="return confirm('Excluir esta empresa permanentemente?');">
                            <input type="hidden" name="action" value="admin_delete_empresa">
                            <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="id" value="<?= (int) $empresa['id'] ?>">
                            <button class="btn danger" type="submit">Excluir empresa</button>
                        </form>
                    </article>
                <?php endforeach; ?>
                <?php foreach ($vagasAdmin as $vaga): ?>
                    <article class="item-card" data-detail="<?= h($vaga['descricao'] ?: 'Sem descrição informada.') ?>" data-job-title="<?= h($vaga['titulo']) ?>">
                        <span class="card-avatar"><?= dashboardIcon('briefcase') ?></span>
                        <div>
                            <h2><?= h($vaga['empresa']) ?></h2>
                            <p><?= h($vaga['titulo']) ?> · <?= h($vaga['descricao'] ?: 'Sem descrição informada.') ?></p>
                        </div>
                        <form method="post" onsubmit="return confirm('Excluir esta vaga?');">
                            <input type="hidden" name="action" value="admin_delete_vaga">
                            <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="id" value="<?= (int) $vaga['id'] ?>">
                            <button class="btn danger" type="submit">Excluir vaga</button>
                        </form>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <aside class="detalhe-area">
            <?php if ($pagina === 'sobre'): ?>
                <h2>Contato</h2>
                <p>Equipe DevIN - Acesso Restrito ADM.</p>
            <?php elseif ($pagina === 'perfil'): ?>
                <h2>Seu Perfil</h2>
                <p>Controles de segurança da conta.</p>
            <?php else: ?>
                <h2>Explicando tudo sobre o post selecionado</h2>
                <p id="detailText">Selecione um post ou candidato para analisar as informacoes.</p>
                <button class="btn danger fixed-action" type="button">Excluir Registro</button>
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
            <input type="hidden" name="action" value="update_settings"><input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
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
