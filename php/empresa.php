<?php

declare(strict_types=1);

require_once __DIR__ . '/middlewares/auth.php';
require_once __DIR__ . '/config/security.php';
startSecureSession();

require_once __DIR__ . '/controllers/ProfileController.php';
require_once __DIR__ . '/helpers.php';

$usuarioAtual = requireWebAuth('empresa');

$tipo   = 'empresa';
$nome   = $_SESSION['usuario_nome']  ?? 'Empresa';
$email  = $_SESSION['usuario_email'] ?? 'empresa@devin.com';
$pagina = requestString($_GET, 'pagina');

$paginasPermitidas = ['inicio', 'candidatos', 'sobre', 'perfil'];
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
                $stmt = $conn->prepare('
                    UPDATE candidatura c
                    INNER JOIN vagas v ON v.id_vaga = c.id_vaga
                    SET c.status = ?
                    WHERE c.id_candidatura = ? AND v.id_empresa = ?
                ');

                if (!$stmt) {
                    throw new RuntimeException('Não foi possível atualizar a candidatura.');
                }

                $empresaId = (int) $_SESSION['usuario_id'];
                $stmt->bind_param('sii', $status, $idCandidatura, $empresaId);
                $stmt->execute();

                if ($stmt->affected_rows !== 1) {
                    throw new RuntimeException('Candidatura não encontrada ou sem permissão para alterá-la.');
                }

                $stmt->close();
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

            if ($titulo === '' || $descricao === '' || mb_strlen($titulo) > 25 || mb_strlen($descricao) > 255) {
                throw new InvalidArgumentException('Informe título (até 25 caracteres) e descrição (até 255 caracteres).');
            }

            $conn = getDatabaseConnection();
            try {
                $stmt = $conn->prepare('INSERT INTO vagas (titulo, descricao, tempo_vaga, id_empresa) VALUES (?, ?, CURDATE(), ?)');
                if (!$stmt) {
                    throw new RuntimeException('Não foi possível criar a vaga.');
                }

                $empresaId = (int) $_SESSION['usuario_id'];
                $stmt->bind_param('ssi', $titulo, $descricao, $empresaId);
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

            if ($vagaId <= 0 || $titulo === '' || $descricao === '' || mb_strlen($titulo) > 25 || mb_strlen($descricao) > 255) {
                throw new InvalidArgumentException('Dados da vaga inválidos.');
            }

            $conn = getDatabaseConnection();
            try {
                $stmt = $conn->prepare('UPDATE vagas SET titulo = ?, descricao = ? WHERE id_vaga = ? AND id_empresa = ?');
                if (!$stmt) {
                    throw new RuntimeException('Não foi possível editar a vaga.');
                }

                $empresaId = (int) $_SESSION['usuario_id'];
                $stmt->bind_param('ssii', $titulo, $descricao, $vagaId, $empresaId);
                $stmt->execute();
                $stmt->close();
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

        if ($action === 'create_test_candidates') {
            $empresaId = (int) $_SESSION['usuario_id'];
            $conn = getDatabaseConnection();

            try {
                $conn->begin_transaction();

                $vagaStmt = $conn->prepare('SELECT id_vaga FROM vagas WHERE id_empresa = ? ORDER BY id_vaga ASC LIMIT 1');
                $vagaStmt->bind_param('i', $empresaId);
                $vagaStmt->execute();
                $vaga = $vagaStmt->get_result()->fetch_assoc();
                $vagaStmt->close();

                if (!$vaga) {
                    throw new RuntimeException('Publique uma vaga antes de criar candidatos de teste.');
                }

                $buscarPessoa       = $conn->prepare('SELECT id_pessoa FROM pessoa WHERE email = ? LIMIT 1');
                $criarPessoa        = $conn->prepare('INSERT INTO pessoa (nome, cpf, cep, email, senha_hash, telefone, created_at, lembrete_enviado) VALUES (?, ?, ?, ?, ?, ?, NOW(), 0)');
                $buscarCandidatura  = $conn->prepare('SELECT id_candidatura FROM candidatura WHERE id_pessoa = ? AND id_vaga = ? LIMIT 1');
                $criarCandidatura   = $conn->prepare('INSERT INTO candidatura (data_candidatura, status, id_pessoa, id_vaga) VALUES (CURDATE(), "pendente", ?, ?)');
                $resetarCandidatura = $conn->prepare('UPDATE candidatura SET status = "pendente" WHERE id_candidatura = ?');

                if (!$buscarPessoa || !$criarPessoa || !$buscarCandidatura || !$criarCandidatura || !$resetarCandidatura) {
                    throw new RuntimeException('Não foi possível preparar os dados de teste.');
                }

                foreach ([1 => 'Ana Teste', 2 => 'Bruno Teste'] as $indice => $nomeTeste) {
                    $emailTeste    = 'candidato.teste.' . $empresaId . '.' . $indice . '@devin.local';
                    $cpfTeste      = sprintf('%011d', 90000000000 + ($empresaId * 10) + $indice);
                    $cepTeste      = '01001000';
                    $telefoneTeste = '1199999000' . $indice;
                    $senhaTeste    = password_hash('teste123', PASSWORD_DEFAULT);

                    $buscarPessoa->bind_param('s', $emailTeste);
                    $buscarPessoa->execute();
                    $pessoa = $buscarPessoa->get_result()->fetch_assoc();

                    if ($pessoa) {
                        $pessoaId = (int) $pessoa['id_pessoa'];
                    } else {
                        $criarPessoa->bind_param('ssssss', $nomeTeste, $cpfTeste, $cepTeste, $emailTeste, $senhaTeste, $telefoneTeste);
                        $criarPessoa->execute();
                        $pessoaId = (int) $conn->insert_id;
                    }

                    $vagaId = (int) $vaga['id_vaga'];
                    $buscarCandidatura->bind_param('ii', $pessoaId, $vagaId);
                    $buscarCandidatura->execute();
                    $candidatura = $buscarCandidatura->get_result()->fetch_assoc();

                    if ($candidatura) {
                        $candidaturaId = (int) $candidatura['id_candidatura'];
                        $resetarCandidatura->bind_param('i', $candidaturaId);
                        $resetarCandidatura->execute();
                    } else {
                        $criarCandidatura->bind_param('ii', $pessoaId, $vagaId);
                        $criarCandidatura->execute();
                    }
                }

                $buscarPessoa->close();
                $criarPessoa->close();
                $buscarCandidatura->close();
                $criarCandidatura->close();
                $resetarCandidatura->close();

                $conn->commit();
            } catch (Throwable $e) {
                $conn->rollback();
                throw $e;
            } finally {
                $conn->close();
            }

            $_SESSION['candidate_success'] = 'Dois candidatos de teste foram criados para a sua primeira vaga.';
            header('Location: empresa.php?pagina=candidatos');
            exit;
        }

        if ($action === 'update_profile') {
            updateProfile($tipo, (int) $_SESSION['usuario_id'], $_POST, $_FILES['foto'] ?? null);
            $_SESSION['usuario_email']   = trim(requestString($_POST, 'email'));
            $_SESSION['profile_success'] = 'Perfil atualizado com sucesso.';
            header('Location: empresa.php?pagina=perfil');
            exit;
        }

        if ($action === 'update_settings') {
            updateLanguage($tipo, (int) $_SESSION['usuario_id'], requestString($_POST, 'idioma') ?: 'pt-BR');
            header('Location: empresa.php?configuracoes=1');
            exit;
        }

        if ($action === 'delete_account') {
            deleteProfile($tipo, (int) $_SESSION['usuario_id']);
            header('Location: logout.php');
            exit;
        }
    } catch (Throwable $exception) {
        error_log('Erro no dashboard empresa: ' . $exception->getMessage());

        if (in_array($action, ['update_application_status', 'create_test_candidates'], true)) {
            $_SESSION['candidate_error'] = $exception->getMessage();
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
$idiomaAtual = $_SESSION['idioma'] ?? 'pt-BR';

$empresaPosts = [];
$candidatos   = [];

try {
    $conn = getDatabaseConnection();
    try {
        // 1. Busca Vagas Criadas pela Empresa
        $stmtVagas = $conn->prepare("
            SELECT id_vaga, titulo, COALESCE(descricao, '') AS descricao, tempo_vaga
            FROM vagas
            WHERE id_empresa = ?
            ORDER BY id_vaga DESC
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
                c.status,
                c.data_candidatura,
                p.nome,
                p.email,
                v.titulo AS vaga,
                COALESCE(cu.cursos, "") AS cursos,
                COALESCE(cu.experiencia, "") AS experiencia
            FROM candidatura c
            INNER JOIN vagas v ON v.id_vaga = c.id_vaga
            INNER JOIN pessoa p ON p.id_pessoa = c.id_pessoa
            LEFT JOIN curriculo cu ON cu.id_pessoa = p.id_pessoa
            WHERE v.id_empresa = ?
            ORDER BY c.data_candidatura DESC, c.id_candidatura DESC
        ';
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

$talentos = [
    ['nome' => 'Marina Santos', 'resumo' => 'React, CSS e comunicação clara.', 'detalhe' => 'Marina tem interesse em vagas de front-end júnior e disponibilidade para conversar esta semana.'],
    ['nome' => 'Lucas Pereira', 'resumo' => 'PHP, MySQL e lógica de programação.', 'detalhe' => 'Lucas procura primeira oportunidade em desenvolvimento web e já criou projetos escolares com banco de dados.'],
];

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevIN | Dashboard Empresa</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link rel="stylesheet" href="../css/dashboard.css?v=<?= filemtime(__DIR__ . '/../css/dashboard.css') ?>">
</head>
<body>
    <main class="dashboard-shell empresa-dashboard page-<?= h($pagina) ?>" data-tipo="<?= h($tipo) ?>">
        
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
            <?php if ($pagina !== 'sobre'): ?>
                <?= dashboardListHeader(
                    $pagina,
                    $pagina === 'candidatos' ? 'Candidatos' : ($pagina === 'perfil' ? 'Perfil' : 'Vagas publicadas'),
                    'Pesquisar por nome ou habilidade...',
                    $pagina === 'candidatos' ? count($candidatos) . ' candidatos' : ($pagina === 'inicio' ? count($empresaPosts) . ' vagas' : '')
                ) ?>
                <?php if (in_array($pagina, ['inicio', 'candidatos'], true)): ?>
                    <p class="list-section-label">Disponíveis</p>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($pagina === 'sobre'): ?>
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

                <?php if (!$candidatos): ?>
                    <p class="empty-state">Ainda não há candidaturas para as suas vagas.</p>
                <?php endif; ?>
                <form method="post" class="test-candidates-form">
                    <input type="hidden" name="action" value="create_test_candidates">
                    <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                    <button class="btn primary" type="submit">Criar candidatos de teste</button>
                    <small>Cria ou reinicia dois candidatos pendentes na primeira vaga da empresa.</small>
                </form>
                <?php foreach ($candidatos as $candidato): ?>
                    <article class="item-card" data-detail="<?= h($candidato['detalhe']) ?>" data-detail-role="<?= h('Candidato para ' . $candidato['vaga']) ?>" data-detail-tags="Candidatura|<?= h(ucfirst($candidato['status'])) ?>" data-detail-experience="<?= h('Candidatura recebida::' . date('d/m/Y', strtotime($candidato['data_candidatura']))) ?>" data-detail-action-label="Aprovar candidato">
                        <span class="card-avatar"><?= dashboardIcon('user') ?></span>
                        <div>
                            <h2><?= h($candidato['nome']) ?></h2>
                            <p><?= h($candidato['resumo']) ?></p>
                            <?= dashboardCardTags('Candidatura|' . ucfirst($candidato['status'])) ?>
                        </div>
                        <div class="acoes-card">
                            <?php if ($candidato['status'] === 'pendente'): ?>
                                <form method="post" class="candidate-actions">
                                    <input type="hidden" name="action" value="update_application_status">
                                    <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                                    <input type="hidden" name="id_candidatura" value="<?= (int) $candidato['id_candidatura'] ?>">
                                    <button class="btn danger" name="status" value="recusado" type="submit">Não se encaixa</button>
                                    <button class="btn success" name="status" value="aprovado" type="submit" data-detail-action-target>Aprovar</button>
                                </form>
                            <?php else: ?>
                                <span class="status <?= $candidato['status'] === 'aprovado' ? 'aprovado' : 'reprovado' ?>">
                                    <?= h(ucfirst($candidato['status'])) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </article>
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
                            <input name="titulo" type="text" maxlength="25" placeholder="Ex.: Desenvolvedor PHP" required>
                        </label>
                        <label>Descrição
                            <textarea name="descricao" maxlength="255" placeholder="Descreva as atividades e requisitos da vaga." required></textarea>
                        </label>
                        <button class="btn primary" type="submit">Publicar vaga</button>
                    </form>
                </details>

                <?php if (!$empresaPosts): ?>
                    <p class="empty-state">Você ainda não publicou nenhuma vaga.</p>
                <?php endif; ?>

                <?php foreach ($empresaPosts as $post): ?>
                    <article class="item-card job-card" data-detail="<?= h($post['descricao'] ?: 'Sem descrição informada.') ?>" data-job-title="<?= h($post['titulo']) ?>" data-detail-role="Vaga publicada" data-detail-tags="Vaga|Publicada" data-detail-experience="<?= h('Publicação::' . date('d/m/Y', strtotime($post['tempo_vaga']))) ?>">
                        <span class="card-avatar"><?= dashboardIcon('briefcase') ?></span>
                        <div>
                            <h2><?= h($post['titulo']) ?></h2>
                            <p><?= h($post['descricao'] ?: 'Sem descrição informada.') ?></p>
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
                                        <input name="titulo" type="text" maxlength="25" value="<?= h($post['titulo']) ?>" required>
                                    </label>
                                    <label>Descrição
                                        <textarea name="descricao" maxlength="255" required><?= h($post['descricao']) ?></textarea>
                                    </label>
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

                <?php foreach ($talentos as $talento): ?>
                    <article class="item-card" data-detail="<?= h($talento['detalhe']) ?>" data-detail-role="Talento disponível" data-detail-tags="Talento|Disponível" data-detail-experience="Perfil DevIN::Disponível para novas oportunidades">
                        <span class="card-avatar"><?= dashboardIcon('user') ?></span>
                        <div>
                            <h2><?= h($talento['nome']) ?></h2>
                            <p><?= h($talento['resumo']) ?></p>
                            <?= dashboardCardTags('Talento|Disponível') ?>
                        </div>
                        <button class="btn primary" type="button">Conversar</button>
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