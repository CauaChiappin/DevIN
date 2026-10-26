<?php

declare(strict_types=1);

require_once __DIR__ . '/middlewares/auth.php';
require_once __DIR__ . '/config/security.php';
startSecureSession();

require_once __DIR__ . '/controllers/ProfileController.php';
require_once __DIR__ . '/controllers/JobSchema.php';
require_once __DIR__ . '/helpers.php';

$usuarioAtual = requireWebAuth('empresa');

$tipo   = 'empresa';
$nome   = $_SESSION['usuario_nome']  ?? 'Empresa';
$email  = $_SESSION['usuario_email'] ?? 'empresa@devin.com';
$mensagemSenhaAlterada = (string) ($_SESSION['sucesso_login'] ?? '');
$mensagemEmailSenha = (string) ($_SESSION['erro_email_senha'] ?? '');
$mensagemCadastro = (string) ($_SESSION['sucesso_cadastro'] ?? '');
$mensagemEmailCadastro = (string) ($_SESSION['erro_email_cadastro'] ?? '');
unset($_SESSION['sucesso_login'], $_SESSION['erro_email_senha'], $_SESSION['sucesso_cadastro'], $_SESSION['erro_email_cadastro']);
$pagina = requestString($_GET, 'pagina');

$paginasPermitidas = ['inicio', 'candidatos', 'sobre', 'perfil', 'mensagens'];
if (!in_array($pagina, $paginasPermitidas, true)) {
    // Limita a navegação a seções implementadas no painel da empresa.
    $pagina = 'inicio';
}

/*
|--------------------------------------------------------------------------
| PROCESSAMENTO DE AÇÕES (POST)
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Toda ação que altera vaga, candidatura ou perfil exige token CSRF válido.
    requireValidCsrf();

    $action = requestString($_POST, 'action');

    try {
        if ($action === 'update_application_status') {
            $idCandidatura = (int) requestString($_POST, 'id_candidatura');
            $status        = requestString($_POST, 'status');

            if ($idCandidatura <= 0 || !in_array($status, ['aprovado', 'recusado'], true)) {
                throw new InvalidArgumentException('Dados da candidatura inválidos.');
            }

            $conn = getDatabaseConnection();
            try {
                ensureJobCompensationSchema($conn);
                $conn->begin_transaction();
                /* Atualiza apenas se a vaga associada pertence à empresa autenticada.
                 * Isso aplica autorização no próprio SQL, além de validar o status permitido acima. */
                $empresaId = (int) $_SESSION['usuario_id'];
                $stmt = $conn->prepare('
                    SELECT c.status, v.id_vaga, v.limite_aprovados
                    FROM candidatura c
                    INNER JOIN vagas v ON v.id_vaga = c.id_vaga
                    WHERE c.id_candidatura = ? AND v.id_empresa = ?
                    FOR UPDATE
                ');
                $stmt->bind_param('ii', $idCandidatura, $empresaId);
                $stmt->execute();
                $candidatura = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if (!$candidatura) {
                    throw new RuntimeException('Candidatura não encontrada ou sem permissão para alterá-la.');
                }

                if ($status === 'aprovado' && $candidatura['status'] !== 'aprovado') {
                    $vagaId = (int) $candidatura['id_vaga'];
                    $stmt = $conn->prepare('SELECT COUNT(*) AS total FROM candidatura WHERE id_vaga = ? AND status = "aprovado"');
                    $stmt->bind_param('i', $vagaId);
                    $stmt->execute();
                    $aprovados = (int) $stmt->get_result()->fetch_assoc()['total'];
                    $stmt->close();

                    $limiteAprovados = (int) $candidatura['limite_aprovados'];
                    if ($limiteAprovados > 0 && $aprovados >= $limiteAprovados) {
                        throw new RuntimeException('O limite de candidatos aprovados para esta vaga já foi atingido.');
                    }
                }

                $stmt = $conn->prepare('UPDATE candidatura SET status = ? WHERE id_candidatura = ?');
                $stmt->bind_param('si', $status, $idCandidatura);
                $stmt->execute();
                if ($stmt->affected_rows !== 1) {
                    throw new RuntimeException('Não foi possível atualizar a candidatura.');
                }
                $stmt->close();
                $conn->commit();
            } catch (Throwable $exception) {
                $conn->rollback();
                throw $exception;
            } finally {
                $conn->close();
            }

            $_SESSION['candidate_success'] = 'Candidatura ' . ($status === 'aprovado' ? 'aprovada' : 'recusada') . ' com sucesso.';
            header('Location: empresa.php?pagina=candidatos');
            exit;
        }

        if ($action === 'create_job') {
            $titulo    = trim(requestString($_POST, 'titulo'));
            $descricao = trim(requestString($_POST, 'descricao'));
            $salarioMensal = validarSalarioMensal(requestString($_POST, 'salario_mensal'));
            $limiteAprovados = validarLimiteAprovados(requestString($_POST, 'limite_aprovados'));

            if ($titulo === '' || $descricao === '' || mb_strlen($titulo) > 50 || mb_strlen($descricao) > 1000) {
                throw new InvalidArgumentException('Informe título (até 50 caracteres) e descrição (até 1000 caracteres).');
            }

            $conn = getDatabaseConnection();
            try {
                ensureJobCompensationSchema($conn);
                // O ID da empresa vem da sessão autenticada, não de um campo do formulário.
                $stmt = $conn->prepare('INSERT INTO vagas (titulo, descricao, salario_mensal, limite_aprovados, tempo_vaga, id_empresa) VALUES (?, ?, ?, ?, CURDATE(), ?)');
                if (!$stmt) {
                    throw new RuntimeException('Não foi possível criar a vaga.');
                }

                $empresaId = (int) $_SESSION['usuario_id'];
                $stmt->bind_param('sssii', $titulo, $descricao, $salarioMensal, $limiteAprovados, $empresaId);
                $stmt->execute();
                $stmt->close();
            } finally {
                $conn->close();
            }

            $_SESSION['job_success'] = 'Vaga publicada com sucesso.';
            header('Location: empresa.php?pagina=inicio');
            exit;
        }

        if ($action === 'update_job') {
            $vagaId    = (int) requestString($_POST, 'id_vaga');
            $titulo    = trim(requestString($_POST, 'titulo'));
            $descricao = trim(requestString($_POST, 'descricao'));
            $salarioMensal = validarSalarioMensal(requestString($_POST, 'salario_mensal'));
            $limiteAprovados = validarLimiteAprovados(requestString($_POST, 'limite_aprovados'), true);

            if ($vagaId <= 0 || $titulo === '' || $descricao === '' || mb_strlen($titulo) > 50 || mb_strlen($descricao) > 1000) {
                throw new InvalidArgumentException('Dados da vaga inválidos.');
            }

            $conn = getDatabaseConnection();
            try {
                ensureJobCompensationSchema($conn);
                $conn->begin_transaction();
                $stmt = $conn->prepare('SELECT id_vaga FROM vagas WHERE id_vaga = ? AND id_empresa = ? FOR UPDATE');
                $empresaId = (int) $_SESSION['usuario_id'];
                $stmt->bind_param('ii', $vagaId, $empresaId);
                $stmt->execute();
                $vagaEncontrada = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if (!$vagaEncontrada) {
                    throw new RuntimeException('Vaga não encontrada ou sem permissão para editá-la.');
                }

                $stmt = $conn->prepare('SELECT COUNT(*) AS total FROM candidatura WHERE id_vaga = ? AND status = "aprovado"');
                $stmt->bind_param('i', $vagaId);
                $stmt->execute();
                $aprovados = (int) $stmt->get_result()->fetch_assoc()['total'];
                $stmt->close();
                if ($limiteAprovados > 0 && $limiteAprovados < $aprovados) {
                    throw new InvalidArgumentException('O limite não pode ser menor que a quantidade de candidatos já aprovados (' . $aprovados . ').');
                }

                // Inclui o proprietário no WHERE para impedir edição de vaga de outra empresa.
                $stmt = $conn->prepare('UPDATE vagas SET titulo = ?, descricao = ?, salario_mensal = ?, limite_aprovados = ? WHERE id_vaga = ? AND id_empresa = ?');
                if (!$stmt) {
                    throw new RuntimeException('Não foi possível editar a vaga.');
                }

                $stmt->bind_param('sssiii', $titulo, $descricao, $salarioMensal, $limiteAprovados, $vagaId, $empresaId);
                $stmt->execute();
                $stmt->close();
                $conn->commit();
            } catch (Throwable $exception) {
                $conn->rollback();
                throw $exception;
            } finally {
                $conn->close();
            }

            $_SESSION['job_success'] = 'Vaga atualizada com sucesso.';
            header('Location: empresa.php?pagina=inicio');
            exit;
        }

        if ($action === 'delete_job') {
            $vagaId = (int) requestString($_POST, 'id_vaga');
            if ($vagaId <= 0) {
                throw new InvalidArgumentException('Vaga inválida.');
            }

            $conn = getDatabaseConnection();
            try {
                // A condição de proprietário impede que uma empresa exclua vagas de outra.
                $stmt = $conn->prepare('DELETE FROM vagas WHERE id_vaga = ? AND id_empresa = ?');
                if (!$stmt) {
                    throw new RuntimeException('Não foi possível excluir a vaga.');
                }

                $empresaId = (int) $_SESSION['usuario_id'];
                $stmt->bind_param('ii', $vagaId, $empresaId);
                $stmt->execute();
                if ($stmt->affected_rows !== 1) {
                    throw new RuntimeException('Vaga não encontrada ou sem permissão para excluí-la.');
                }
                $stmt->close();
            } finally {
                $conn->close();
            }

            $_SESSION['job_success'] = 'Vaga excluída com sucesso.';
            header('Location: empresa.php?pagina=inicio');
            exit;
        }

        // Operações de perfil reutilizam validações comuns e mantêm os dados da sessão atualizados.
        if ($action === 'update_profile') {
            updateProfile($tipo, (int) $_SESSION['usuario_id'], $_POST, $_FILES['foto'] ?? null);
            $_SESSION['usuario_email']   = trim(requestString($_POST, 'email'));
            $_SESSION['profile_success'] = 'Perfil atualizado com sucesso.';
            header('Location: empresa.php?pagina=' . rawurlencode($pagina));
            exit;
        }

        if ($action === 'update_settings') {
            updateLanguage($tipo, (int) $_SESSION['usuario_id'], requestString($_POST, 'idioma') ?: 'pt-BR');
            $_SESSION['settings_success'] = 'Idioma salvo com sucesso.';
            header('Location: empresa.php?pagina=' . rawurlencode($pagina) . '&configuracoes=1');
            exit;
        }

        if ($action === 'delete_account') {
            deleteProfile($tipo, (int) $_SESSION['usuario_id']);
            header('Location: logout.php?destino=index');
            exit;
        }
    } catch (Throwable $exception) {
        error_log('Erro no dashboard empresa: ' . $exception->getMessage());

        if ($action === 'update_profile') {
            $_SESSION['profile_error'] = 'Não foi possível atualizar o perfil. Tente novamente.';
            header('Location: empresa.php?pagina=' . rawurlencode($pagina));
            exit;
        }

        if ($action === 'update_settings') {
            $_SESSION['settings_error'] = 'Não foi possível salvar o idioma. Tente novamente.';
            header('Location: empresa.php?pagina=' . rawurlencode($pagina) . '&configuracoes=1');
            exit;
        }

        if ($action === 'update_application_status') {
            $_SESSION['candidate_error'] = $exception instanceof mysqli_sql_exception
                ? 'Não foi possível concluir a ação. Tente novamente.'
                : $exception->getMessage();
            header('Location: empresa.php?pagina=candidatos');
            exit;
        }

        if (in_array($action, ['create_job', 'update_job', 'delete_job'], true)) {
            $_SESSION['job_error'] = $exception->getMessage();
            header('Location: empresa.php?pagina=inicio');
            exit;
        }

        $_SESSION['profile_error'] = $exception->getMessage();
        header('Location: empresa.php?pagina=perfil');
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| CARREGAMENTO DE DADOS E VAGAS
|--------------------------------------------------------------------------
*/

$empresaId   = (int) $_SESSION['usuario_id'];
$perfilAtual = findProfile($tipo, $empresaId);

if (!$perfilAtual) {
    header('Location: logout.php');
    exit;
}
$idiomaAtual = getProfileLanguage($tipo, $empresaId);

$empresaPosts = [];
$candidatos   = [];

try {
    $conn = getDatabaseConnection();
    try {
        ensureJobCompensationSchema($conn);
        // 1. Busca Vagas Criadas pela Empresa
        // Carrega somente vagas da empresa autenticada para a área de gerenciamento.
        $stmtVagas = $conn->prepare("
            SELECT v.id_vaga, v.titulo, COALESCE(v.descricao, '') AS descricao,
                   v.salario_mensal, v.limite_aprovados, v.tempo_vaga,
                   COALESCE(contagens.aprovadas, 0) AS candidaturas_aprovadas
            FROM vagas v
            LEFT JOIN (
                SELECT id_vaga, COUNT(*) AS aprovadas
                FROM candidatura
                WHERE status = 'aprovado'
                GROUP BY id_vaga
            ) contagens ON contagens.id_vaga = v.id_vaga
            WHERE v.id_empresa = ?
            ORDER BY v.id_vaga DESC
        ");
        if ($stmtVagas) {
            $stmtVagas->bind_param('i', $empresaId);
            $stmtVagas->execute();
            $empresaPosts = $stmtVagas->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmtVagas->close();
        }

        // 2. Busca Candidatos que se aplicaram às Vagas
        $sqlCandidatos = '
            SELECT
                c.id_candidatura,
                p.id_pessoa,
                v.id_vaga,
                c.status,
                c.data_candidatura,
                p.nome,
                p.email,
                v.titulo AS vaga,
                COALESCE(cu.nome_social, "") AS nome_social,
                COALESCE(cu.grau_de_escolaridade, "") AS grau_de_escolaridade,
                COALESCE(cu.cursos, "") AS cursos,
                COALESCE(cu.experiencia, "") AS experiencia,
                COALESCE(cu.idiomas, "") AS idiomas
            FROM candidatura c
            INNER JOIN vagas v ON v.id_vaga = c.id_vaga
            INNER JOIN pessoa p ON p.id_pessoa = c.id_pessoa
            LEFT JOIN curriculo cu ON cu.id_pessoa = p.id_pessoa
            WHERE v.id_empresa = ?
            ORDER BY c.data_candidatura DESC, c.id_candidatura DESC
        ';
        // Monta candidatos relacionados às vagas da empresa usando consultas preparadas.
        $stmtCandidatos = $conn->prepare($sqlCandidatos);
        if ($stmtCandidatos) {
            $stmtCandidatos->bind_param('i', $empresaId);
            $stmtCandidatos->execute();
            $candidatos = $stmtCandidatos->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmtCandidatos->close();
        }
    } finally {
        $conn->close();
    }
} catch (Throwable $exception) {
    error_log('Erro ao carregar dados do dashboard empresa: ' . $exception->getMessage());
}

foreach ($empresaPosts as &$post) {
    $post['resumo']  = 'Publicada em ' . date('d/m/Y', strtotime($post['tempo_vaga']));
    $post['detalhe'] = $post['descricao'];
}
unset($post);

foreach ($candidatos as &$candidato) {
    $candidato['resumo']  = 'Vaga: ' . $candidato['vaga'] . ' | Status: ' . ucfirst($candidato['status']);
    $candidato['detalhe'] = 'E-mail: ' . $candidato['email']
        . '. Cursos: ' . ($candidato['cursos'] !== '' ? $candidato['cursos'] : 'não informado')
        . '. Experiência: ' . ($candidato['experiencia'] !== '' ? $candidato['experiencia'] : 'não informada');
}
unset($candidato);

$candidatosPorVaga = [];
foreach ($candidatos as $candidato) {
    $candidatosPorVaga[(int) $candidato['id_vaga']][] = $candidato;
}

?>
<!DOCTYPE html>
<html lang="<?= h($idiomaAtual) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevIN | Dashboard Empresa</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link rel="stylesheet" href="../css/dashboard.css?v=<?= filemtime(__DIR__ . '/../css/dashboard.css') ?>">
    <?php if ($pagina === 'mensagens'): ?>
        <link rel="stylesheet" href="../css/chat.css?v=<?= filemtime(__DIR__ . '/../css/chat.css') ?>">
    <?php endif; ?>
</head>
<body>
    <main class="dashboard-shell empresa-dashboard page-<?= h($pagina) ?>" data-tipo="<?= h($tipo) ?>">
        <?php if ($mensagemSenhaAlterada !== '' || $mensagemCadastro !== '' || $mensagemEmailSenha !== '' || $mensagemEmailCadastro !== ''): ?>
            <div class="dashboard-toast" role="status" aria-live="polite">
                <?php if ($mensagemSenhaAlterada !== ''): ?>
                    <p class="dashboard-toast__msg dashboard-toast__msg--success"><?= h($mensagemSenhaAlterada) ?></p>
                <?php endif; ?>
                <?php if ($mensagemCadastro !== ''): ?>
                    <p class="dashboard-toast__msg dashboard-toast__msg--success"><?= h($mensagemCadastro) ?></p>
                <?php endif; ?>
                <?php if ($mensagemEmailSenha !== ''): ?>
                    <p class="dashboard-toast__msg dashboard-toast__msg--error"><?= h($mensagemEmailSenha) ?></p>
                <?php endif; ?>
                <?php if ($mensagemEmailCadastro !== ''): ?>
                    <p class="dashboard-toast__msg dashboard-toast__msg--error"><?= h($mensagemEmailCadastro) ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <aside class="sidebar">
            <div class="sidebar-topo">
                <a class="brand" href="empresa.php">
                    <span class="brand-text">Dev<span>IN</span></span>
                </a>
                <button class="menu-toggle" type="button" aria-label="Abrir ou fechar menu" aria-expanded="true" data-toggle-menu>
                    <span></span><span></span><span></span>
                </button>
            </div>

            <nav class="menu-principal" aria-label="Menu principal">
                <a class="<?= ativo($pagina, 'inicio') ?>" href="empresa.php?pagina=inicio"><?= dashboardIcon('home') ?><span class="menu-text">Início</span></a>
                <a class="<?= ativo($pagina, 'candidatos') ?>" href="empresa.php?pagina=candidatos"><?= dashboardIcon('user') ?><span class="menu-text">Candidatos</span></a>
                <a class="<?= ativo($pagina, 'mensagens') ?>" href="empresa.php?pagina=mensagens" data-messages-api="../php/mensagens_api.php"><?= dashboardIcon('users') ?><span class="menu-text">Mensagens</span><span class="message-unread-badge" data-message-unread-badge hidden></span></a>
                <a class="<?= ativo($pagina, 'sobre') ?>" href="empresa.php?pagina=sobre"><?= dashboardIcon('info') ?><span class="menu-text">Sobre nós</span></a>
            </nav>

            <div class="conta">
                <details class="perfil-dropdown">
                    <summary class="perfil-link"><?= profileAvatar($perfilAtual, 'avatar-mini') ?><span class="menu-text">Perfil</span></summary>
                    <div class="perfil-menu">
                        <button type="button" data-open-profile><?= profileAvatar($perfilAtual, 'avatar-foto') ?><span class="menu-text">Meu perfil</span></button>
                        <button type="button" data-open-settings><?= dashboardIcon('settings') ?><span class="menu-text">Configurações</span></button>
                    </div>
                </details>
                <a class="sair" href="logout.php"><?= dashboardIcon('logout') ?><span class="menu-text">Sair da Conta</span></a>
            </div>
        </aside>

        <section class="lista-area">
            <?php if (!in_array($pagina, ['sobre', 'mensagens'], true)): ?>
                <?= dashboardListHeader(
                    $pagina,
                    $pagina === 'candidatos' ? 'Candidatos' : ($pagina === 'perfil' ? 'Perfil' : 'Vagas publicadas'),
                    $pagina === 'candidatos' ? 'Pesquisar por nome ou e-mail...' : 'Pesquisar por nome ou habilidade...',
                    $pagina === 'candidatos' ? count($candidatos) . ' candidatos' : ($pagina === 'inicio' ? count($empresaPosts) . ' vagas' : ''),
                    $pagina === 'candidatos'
                ) ?>
                <?php if (in_array($pagina, ['inicio', 'candidatos'], true)): ?>
                    <p class="list-section-label">Disponíveis</p>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($pagina === 'mensagens'): ?>
                <section class="chat-app" data-chat-app data-api="../php/mensagens_api.php" data-csrf="<?= h(csrfToken()) ?>">
                    <aside class="chat-contacts">
                        <div class="chat-contacts-heading">
                            <h1>Mensagens</h1>
                            <span class="chat-unread-total" data-chat-unread-total hidden></span>
                        </div>
                        <p class="chat-notification" data-chat-notification role="status" aria-live="polite"></p>
                        <label class="chat-search">
                            <span class="sr-only">Buscar pessoas e empresas</span>
                            <input type="search" data-chat-search placeholder="Buscar pessoas e empresas">
                        </label>
                        <p class="chat-status" data-chat-contacts-status role="status">Carregando contatos...</p>
                        <nav class="chat-contact-list" data-chat-contacts aria-label="Contatos"></nav>
                    </aside>
                    <section class="chat-conversation" aria-label="Conversa">
                        <header class="chat-conversation-header" data-chat-header>
                            <button class="chat-back-to-contacts" type="button" data-chat-back aria-label="Voltar para contatos">←</button>
                            <div class="chat-header-contact">
                                <span class="chat-header-avatar" data-chat-avatar aria-hidden="true"></span>
                                <span class="chat-header-copy">
                                    <strong data-chat-title>Escolha alguém para conversar</strong>
                                    <span data-chat-subtitle>Mensagens privadas entre usuários da DevIN</span>
                                </span>
                            </div>
                            <details class="chat-conversation-menu">
                                <summary data-chat-menu-toggle aria-label="Opções da conversa" aria-expanded="false">⋮</summary>
                                <div class="chat-conversation-menu-items">
                                    <button class="chat-delete-conversation" type="button" data-chat-delete-conversation disabled>Excluir conversa</button>
                                </div>
                            </details>
                        </header>
                        <div class="chat-messages" data-chat-messages role="log" aria-live="polite" aria-relevant="additions text">
                            <p class="chat-empty">Selecione um contato para iniciar ou continuar uma conversa.</p>
                        </div>
                        <form class="chat-composer" data-chat-form>
                            <label class="sr-only" for="chat-message">Sua mensagem</label>
                            <textarea id="chat-message" name="message" maxlength="2000" placeholder="Escreva uma mensagem..." required disabled data-chat-input></textarea>
                            <button type="submit" disabled data-chat-send>Enviar</button>
                        </form>
                        <p class="chat-status chat-send-status" data-chat-send-status role="status"></p>
                    </section>
                </section>
            <?php elseif ($pagina === 'sobre'): ?>
                <?= aboutPage() ?>

            <?php elseif ($pagina === 'perfil'): ?>
                <section class="perfil-card">
                    <a class="fechar-card" href="empresa.php?pagina=inicio">×</a>
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
                <?php if (!empty($_SESSION['candidate_error'])): ?>
                    <p class="form-error"><?= h($_SESSION['candidate_error']); unset($_SESSION['candidate_error']); ?></p>
                <?php endif; ?>
                <?php if (!empty($_SESSION['candidate_success'])): ?>
                    <p class="form-success"><?= h($_SESSION['candidate_success']); unset($_SESSION['candidate_success']); ?></p>
                <?php endif; ?>

                <?php if (!$empresaPosts): ?>
                    <p class="empty-state">Você ainda não publicou nenhuma vaga.</p>
                <?php endif; ?>
                <?php foreach ($empresaPosts as $post): ?>
                    <?php $candidatosDaVaga = $candidatosPorVaga[(int) $post['id_vaga']] ?? []; ?>
                    <?php
                    $escolaridades = array_values(array_unique(array_filter(array_column($candidatosDaVaga, 'grau_de_escolaridade'))));
                    sort($escolaridades, SORT_NATURAL | SORT_FLAG_CASE);
                    ?>
                    <details class="candidate-vacancy-panel">
                        <summary>
                            <span class="candidate-vacancy-info">
                                <strong><?= h($post['titulo']) ?></strong>
                                <small><?= h($post['resumo']) ?></small>
                            </span>
                            <span class="candidate-vacancy-count">
                                <?= count($candidatosDaVaga) ?> <?= count($candidatosDaVaga) === 1 ? 'candidato' : 'candidatos' ?>
                            </span>
                        </summary>
                        <div class="candidate-vacancy-list">
                            <?php if ($candidatosDaVaga): ?>
                                <div class="candidate-filters">
                                    <label>
                                        <span>Pesquisar por nome ou e-mail</span>
                                        <input type="search" data-candidate-search placeholder="Nome ou e-mail">
                                    </label>
                                    <label>
                                        <span>Escolaridade</span>
                                        <select data-candidate-education>
                                            <option value="">Todos os níveis</option>
                                            <?php foreach ($escolaridades as $escolaridade): ?>
                                                <option value="<?= h($escolaridade) ?>"><?= h($escolaridade) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </label>
                                    <label>
                                        <span>Cursos</span>
                                        <select data-candidate-course aria-label="Filtrar por curso">
                                            <option value="">Todos os cursos</option>
                                        </select>
                                    </label>
                                    <label>
                                        <span>Habilidades</span>
                                        <select data-candidate-skill aria-label="Filtrar por habilidade">
                                            <option value="">Todas as habilidades</option>
                                        </select>
                                    </label>
                                    <label>
                                        <span>Experiência</span>
                                        <select data-candidate-experience aria-label="Filtrar por experiência">
                                            <option value="">Todas as experiências</option>
                                        </select>
                                    </label>
                                </div>
                                <p class="candidate-filter-empty" data-candidate-filter-empty role="status" hidden>Nenhum candidato corresponde aos filtros.</p>
                            <?php endif; ?>
                            <?php if (!$candidatosDaVaga): ?>
                                <p class="empty-state">Ainda não há candidatos para esta vaga.</p>
                            <?php endif; ?>
                            <?php foreach ($candidatosDaVaga as $candidato): ?>
                                <article class="item-card" data-detail="<?= h($candidato['detalhe']) ?>" data-detail-role="<?= h('Candidato para ' . $candidato['vaga']) ?>" data-detail-tags="Candidatura|<?= h(ucfirst($candidato['status'])) ?>" data-detail-experience="<?= h('Candidatura recebida::' . date('d/m/Y', strtotime($candidato['data_candidatura']))) ?>" data-candidate-name-email="<?= h($candidato['nome'] . ' ' . $candidato['email'] . ' ' . $candidato['nome_social']) ?>" data-candidate-education="<?= h($candidato['grau_de_escolaridade']) ?>" data-candidate-courses="<?= h($candidato['cursos']) ?>" data-candidate-skills="<?= h(implode('|', array_filter([$candidato['cursos'], $candidato['idiomas']]))) ?>" data-candidate-experiences="<?= h($candidato['experiencia']) ?>" data-candidate-curriculum="<?= h(implode(' ', [$candidato['grau_de_escolaridade'], $candidato['cursos'], $candidato['experiencia'], $candidato['idiomas']])) ?>" data-detail-action-label="Aprovar candidato" data-chat-type="pessoa" data-chat-id="<?= (int) $candidato['id_pessoa'] ?>">
                                    <span class="card-avatar"><?= dashboardIcon('user') ?></span>
                                    <div>
                                        <h2><?= h($candidato['nome']) ?></h2>
                                        <p><?= h($candidato['resumo']) ?></p>
                                        <?= dashboardCardTags('Candidatura|' . ucfirst($candidato['status'])) ?>
                                    </div>
                                    <div class="acoes-card">
                                        <button class="btn primary" type="button" data-start-chat data-chat-type="pessoa" data-chat-id="<?= (int) $candidato['id_pessoa'] ?>">Conversar</button>
                                        <?php if ($candidato['status'] === 'pendente'): ?>
                                            <div class="candidate-actions">
                                                <form method="post">
                                                    <input type="hidden" name="action" value="update_application_status">
                                                    <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                                                    <input type="hidden" name="id_candidatura" value="<?= (int) $candidato['id_candidatura'] ?>">
                                                    <input type="hidden" name="status" value="recusado">
                                                    <button class="btn danger" type="submit">Não se encaixa</button>
                                                </form>
                                                <form method="post">
                                                    <input type="hidden" name="action" value="update_application_status">
                                                    <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                                                    <input type="hidden" name="id_candidatura" value="<?= (int) $candidato['id_candidatura'] ?>">
                                                    <input type="hidden" name="status" value="aprovado">
                                                    <button class="btn success" type="submit" data-detail-action-target>Aprovar</button>
                                                </form>
                                            </div>
                                        <?php else: ?>
                                            <span class="status <?= $candidato['status'] === 'aprovado' ? 'aprovado' : 'reprovado' ?>">
                                                <?= h(ucfirst($candidato['status'])) ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </details>
                <?php endforeach; ?>

            <?php else: ?>
                <?php if (!empty($_SESSION['job_error'])): ?>
                    <p class="form-error"><?= h($_SESSION['job_error']); unset($_SESSION['job_error']); ?></p>
                <?php endif; ?>
                <?php if (!empty($_SESSION['job_success'])): ?>
                    <p class="form-success"><?= h($_SESSION['job_success']); unset($_SESSION['job_success']); ?></p>
                <?php endif; ?>

                <details class="create-job-panel">
                    <summary class="criar-post">Publicar nova vaga</summary>
                    <form method="post" class="create-job-form">
                        <input type="hidden" name="action" value="create_job">
                        <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                        <label>Título da vaga
                            <input name="titulo" type="text" maxlength="50" placeholder="Ex.: Desenvolvedor PHP" required>
                        </label>
                        <label>Descrição
                            <textarea name="descricao" maxlength="1000" placeholder="Descreva as atividades e requisitos da vaga." required></textarea>
                        </label>
                                        <label>Salário mensal (R$)
                                            <input name="salario_mensal" type="number" min="0" max="99999999.99" step="0.01" value="0.00" required>
                                        </label>
                                        <label>Máximo de candidatos aprovados
                                            <input name="limite_aprovados" type="number" min="1" max="2147483647" step="1" required>
                                        </label>
                                        <p class="job-form-hint">Informe 0 no salário para exibir "A combinar".</p>
                                        <button class="btn primary" type="submit">Publicar vaga</button>
                    </form>
                </details>

                <?php if (!$empresaPosts): ?>
                    <p class="empty-state">Você ainda não publicou nenhuma vaga.</p>
                <?php endif; ?>

                <?php foreach ($empresaPosts as $post): ?>
                    <?php $salarioExibido = (float) $post['salario_mensal'] > 0 ? 'R$ ' . number_format((float) $post['salario_mensal'], 2, ',', '.') : 'A combinar'; ?>
                    <?php $limiteExibido = (int) $post['limite_aprovados'] > 0 ? (string) $post['limite_aprovados'] : 'Sem limite'; ?>
                    <article class="item-card job-card" data-detail="<?= h($post['descricao'] ?: 'Sem descrição informada.') ?>" data-job-title="<?= h($post['titulo']) ?>" data-detail-role="Vaga publicada" data-detail-tags="Vaga|Publicada" data-detail-experience="<?= h('Publicação::' . date('d/m/Y', strtotime($post['tempo_vaga'])) . '|Salário mensal::' . $salarioExibido . '|Máximo de aprovados::' . $limiteExibido) ?>">
                        <span class="card-avatar"><?= dashboardIcon('briefcase') ?></span>
                        <div>
                            <h2><?= h($post['titulo']) ?></h2>
                            <p><?= h($post['descricao'] ?: 'Sem descrição informada.') ?></p>
                            <p class="job-compensation"><span>Salário mensal:</span> <strong><?= h($salarioExibido) ?></strong> · <span>Máximo de aprovados:</span> <strong><?= h($limiteExibido) ?></strong></p>
                            <?= dashboardCardTags('Vaga|Publicada') ?>
                        </div>
                        <div class="post-tools">
                            <details class="job-editor">
                                <summary class="edit" aria-label="Editar vaga" title="Editar vaga">
                                    <?= dashboardIcon('edit') ?>
                                </summary>
                                <form method="post" class="edit-job-form">
                                    <input type="hidden" name="action" value="update_job">
                                    <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                                    <input type="hidden" name="id_vaga" value="<?= (int) $post['id_vaga'] ?>">
                                    <label>Título
                                        <input name="titulo" type="text" maxlength="50" value="<?= h($post['titulo']) ?>" required>
                                    </label>
                                    <label>Descrição
                                        <textarea name="descricao" maxlength="1000" required><?= h($post['descricao']) ?></textarea>
                                    </label>
                                    <label>Salário mensal (R$)
                                        <input name="salario_mensal" type="number" min="0" max="99999999.99" step="0.01" value="<?= h(number_format((float) $post['salario_mensal'], 2, '.', '')) ?>" required>
                                    </label>
                                    <label>Máximo de candidatos aprovados
                                        <input name="limite_aprovados" type="number" min="0" max="2147483647" step="1" value="<?= (int) $post['limite_aprovados'] > 0 ? max((int) $post['candidaturas_aprovadas'], (int) $post['limite_aprovados']) : 0 ?>" required>
                                    </label>
                                    <p class="job-form-hint">0 deixa a vaga sem limite de aprovados.</p>
                                    <button class="btn primary" type="submit">Salvar</button>
                                </form>
                            </details>
                            <form method="post" onsubmit="return confirm('Excluir esta vaga e as candidaturas dela?');">
                                <input type="hidden" name="action" value="delete_job">
                                <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                                <input type="hidden" name="id_vaga" value="<?= (int) $post['id_vaga'] ?>">
                                <button class="delete" type="submit" aria-label="Excluir vaga" title="Excluir vaga">
                                    <?= dashboardIcon('trash') ?>
                                </button>
                            </form>
                        </div>
                    </article>
                <?php endforeach; ?>

            <?php endif; ?>
        </section>

        <?php if (!in_array($pagina, ['sobre', 'perfil', 'mensagens'], true)): ?>
            <?= dashboardDetailPanel() ?>
        <?php elseif ($pagina !== 'mensagens'): ?>
        <aside class="detalhe-area">
            <?php if ($pagina === 'sobre'): ?>
                <h2>Contato</h2>
                <p>Fale com a equipe DevIN para conhecer melhor o projeto, enviar sugestões ou pedir suporte.</p>
                <p><strong>E-mail:</strong> contato@devin.com.br</p>
            <?php elseif ($pagina === 'perfil'): ?>
                <h2>Explicando tudo sobre a vaga selecionada</h2>
                <p>Use este espaço para visualizar detalhes da vaga, pessoa ou post escolhido no painel.</p>
            <?php else: ?>
                <h2 id="detailTitle"><?= h($pagina === 'candidatos' ? 'Vaga em que o candidato se inscreveu' : 'Detalhes da vaga') ?></h2>
                <p id="detailText">Selecione uma vaga para ver sua descrição completa aqui.</p>
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
                <label>Nome da empresa<input name="nome" type="text" value="<?= h($perfilAtual['nome']) ?>" readonly aria-readonly="true"></label>
                <label>E-mail da conta<input name="email" type="email" value="<?= h($perfilAtual['email']) ?>" required></label>
                <label>Celular<input name="telefone" type="tel" value="<?= h($perfilAtual['telefone'] ?? '') ?>" required></label>
                <label>CEP<input name="cep" type="text" value="<?= h($perfilAtual['cep'] ?? '') ?>" required></label>
            </div>
            <button class="profile-save" type="submit">Salvar</button>
        </form>
    </dialog>

    <!-- Modal Configurações -->
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
                <small>Escolha o idioma usado na sua experiência na DevIN.</small>
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
    <?php if ($pagina === 'mensagens'): ?>
        <script src="../js/chat.js?v=<?= filemtime(__DIR__ . '/../js/chat.js') ?>" defer></script>
    <?php endif; ?>
    <script>
        (function () {
            var toast = document.querySelector('.dashboard-toast');
            if (!toast) return;
            setTimeout(function () {
                toast.style.transition = 'opacity .4s ease';
                toast.style.opacity = '0';
                setTimeout(function () { toast.remove(); }, 420);
            }, 4000);
        })();
    </script>
</body>
</html>
