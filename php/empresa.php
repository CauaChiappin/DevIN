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
    
    $action = $_POST['action'] ?? '';

    try {
        // Descobre qual ação o formulário pediu para executar
        $action = $_POST['action'] ?? '';

        if ($action === 'update_application_status') {
            $idCandidatura = (int) ($_POST['id_candidatura'] ?? 0);
            $status = $_POST['status'] ?? '';

            if ($idCandidatura <= 0 || !in_array($status, ['aprovado', 'recusado'], true)) {
                throw new InvalidArgumentException('Dados da candidatura invalidos.');
            }

            $conn = getDatabaseConnection();
            // Confirma pelo JOIN que esta candidatura pertence a uma vaga desta empresa.
            $stmt = $conn->prepare(
                'UPDATE candidatura c
                 INNER JOIN vagas v ON v.id_vaga = c.id_vaga
                 SET c.status = ?
                 WHERE c.id_candidatura = ? AND v.id_empresa = ?'
            );

            if (!$stmt) {
                throw new RuntimeException('Nao foi possivel atualizar a candidatura: ' . $conn->error);
            }

            $empresaId = (int) $_SESSION['usuario_id'];
            $stmt->bind_param('sii', $status, $idCandidatura, $empresaId);
            $stmt->execute();

            if ($stmt->affected_rows !== 1) {
                throw new RuntimeException('Candidatura nao encontrada ou sem permissao para altera-la.');
            }

            $stmt->close();
            $conn->close();
            $_SESSION['candidate_success'] = 'Candidatura ' . ($status === 'aprovado' ? 'aprovada' : 'recusada') . ' com sucesso.';
            header('Location: empresa.php?pagina=candidatos');
            exit;
        }

        // Cria uma vaga real para a empresa atualmente logada.
        if ($action === 'create_job') {
            $titulo = trim($_POST['titulo'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');

            if ($titulo === '' || $descricao === '' || strlen($titulo) > 25 || strlen($descricao) > 255) {
                throw new InvalidArgumentException('Informe titulo (ate 25 caracteres) e descricao (ate 255 caracteres).');
            }

            $conn = getDatabaseConnection();
            $stmt = $conn->prepare('INSERT INTO vagas (titulo, descricao, tempo_vaga, id_empresa) VALUES (?, ?, CURDATE(), ?)');
            if (!$stmt) {
                throw new RuntimeException('Nao foi possivel criar a vaga: ' . $conn->error);
            }

            $empresaId = (int) $_SESSION['usuario_id'];
            $stmt->bind_param('ssi', $titulo, $descricao, $empresaId);
            $stmt->execute();
            $stmt->close();
            $conn->close();

            $_SESSION['job_success'] = 'Vaga publicada com sucesso.';
            header('Location: empresa.php?pagina=inicio');
            exit;
        }

        // Atualiza uma vaga, mas somente se ela pertencer a empresa logada.
        if ($action === 'update_job') {
            $vagaId = (int) ($_POST['id_vaga'] ?? 0);
            $titulo = trim($_POST['titulo'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');

            if ($vagaId <= 0 || $titulo === '' || $descricao === '' || strlen($titulo) > 25 || strlen($descricao) > 255) {
                throw new InvalidArgumentException('Dados da vaga invalidos.');
            }

            $conn = getDatabaseConnection();
            $stmt = $conn->prepare('UPDATE vagas SET titulo = ?, descricao = ? WHERE id_vaga = ? AND id_empresa = ?');
            if (!$stmt) {
                throw new RuntimeException('Nao foi possivel editar a vaga: ' . $conn->error);
            }

            $empresaId = (int) $_SESSION['usuario_id'];
            $stmt->bind_param('ssii', $titulo, $descricao, $vagaId, $empresaId);
            $stmt->execute();
            $stmt->close();
            $conn->close();

            $_SESSION['job_success'] = 'Vaga atualizada com sucesso.';
            header('Location: empresa.php?pagina=inicio');
            exit;
        }

        // Exclui uma vaga da empresa. As candidaturas relacionadas sao apagadas pelo banco.
        if ($action === 'delete_job') {
            $vagaId = (int) ($_POST['id_vaga'] ?? 0);
            if ($vagaId <= 0) {
                throw new InvalidArgumentException('Vaga invalida.');
            }

            $conn = getDatabaseConnection();
            $stmt = $conn->prepare('DELETE FROM vagas WHERE id_vaga = ? AND id_empresa = ?');
            if (!$stmt) {
                throw new RuntimeException('Nao foi possivel excluir a vaga: ' . $conn->error);
            }

            $empresaId = (int) $_SESSION['usuario_id'];
            $stmt->bind_param('ii', $vagaId, $empresaId);
            $stmt->execute();
            if ($stmt->affected_rows !== 1) {
                throw new RuntimeException('Vaga nao encontrada ou sem permissao para exclui-la.');
            }
            $stmt->close();
            $conn->close();

            $_SESSION['job_success'] = 'Vaga excluida com sucesso.';
            header('Location: empresa.php?pagina=inicio');
            exit;
        }

        // Gera candidatos locais de teste para permitir testar aprovar e recusar.
        if ($action === 'create_test_candidates') {
            $empresaId = (int) $_SESSION['usuario_id'];
            $conn = getDatabaseConnection();
            $conn->begin_transaction();

            $vagaStmt = $conn->prepare('SELECT id_vaga FROM vagas WHERE id_empresa = ? ORDER BY id_vaga ASC LIMIT 1');
            $vagaStmt->bind_param('i', $empresaId);
            $vagaStmt->execute();
            $vaga = $vagaStmt->get_result()->fetch_assoc();
            $vagaStmt->close();

            if (!$vaga) {
                throw new RuntimeException('Publique uma vaga antes de criar candidatos de teste.');
            }

            $buscarPessoa = $conn->prepare('SELECT id_pessoa FROM pessoa WHERE email = ? LIMIT 1');
            $criarPessoa = $conn->prepare('INSERT INTO pessoa (nome, cpf, cep, email, senha_hash, telefone) VALUES (?, ?, ?, ?, ?, ?)');
            $buscarCandidatura = $conn->prepare('SELECT id_candidatura FROM candidatura WHERE id_pessoa = ? AND id_vaga = ? LIMIT 1');
            $criarCandidatura = $conn->prepare('INSERT INTO candidatura (data_candidatura, status, id_pessoa, id_vaga) VALUES (CURDATE(), "pendente", ?, ?)');
            $resetarCandidatura = $conn->prepare('UPDATE candidatura SET status = "pendente" WHERE id_candidatura = ?');

            if (!$buscarPessoa || !$criarPessoa || !$buscarCandidatura || !$criarCandidatura || !$resetarCandidatura) {
                throw new RuntimeException('Nao foi possivel preparar os dados de teste: ' . $conn->error);
            }

            foreach ([1 => 'Ana Teste', 2 => 'Bruno Teste'] as $indice => $nomeTeste) {
                $emailTeste = 'candidato.teste.' . $empresaId . '.' . $indice . '@devin.local';
                $cpfTeste = sprintf('%011d', 90000000000 + ($empresaId * 10) + $indice);
                $cepTeste = '01001000';
                $telefoneTeste = '1199999000' . $indice;
                $senhaTeste = password_hash('teste123', PASSWORD_DEFAULT);

                $buscarPessoa->bind_param('s', $emailTeste);
                $buscarPessoa->execute();
                $pessoa = $buscarPessoa->get_result()->fetch_assoc();

                if ($pessoa) {
                    $pessoaId = (int) $pessoa['id_pessoa'];
                } else {
                    $criarPessoa->bind_param('ssssss', $nomeTeste, $cpfTeste, $cepTeste, $emailTeste, $senhaTeste, $telefoneTeste);
                    $criarPessoa->execute();
                    $pessoaId = $conn->insert_id;
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
            $conn->close();

            $_SESSION['candidate_success'] = 'Dois candidatos de teste foram criados para a sua primeira vaga.';
            header('Location: empresa.php?pagina=candidatos');
            exit;
        }

        // Ação 1: Atualizar o perfil da empresa (dados pessoais e foto)
        if ($action === 'update_profile') {
            updateProfile($tipo, (int) $_SESSION['usuario_id'], $_POST, $_FILES['foto'] ?? null);
            // O nome da empresa e fixo; por isso a sessao tambem nao recebe nome vindo do formulario.
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
        if (in_array($action, ['update_application_status', 'create_test_candidates'], true)) {
            $_SESSION['candidate_error'] = $exception->getMessage();
            header('Location: empresa.php?pagina=candidatos'); exit;
        }

        if (in_array($action, ['create_job', 'update_job', 'delete_job'], true)) {
            $_SESSION['job_error'] = $exception->getMessage();
            header('Location: empresa.php?pagina=inicio'); exit;
        }

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
$conn = getDatabaseConnection();
$hasDescricao = (bool) $conn->query("SHOW COLUMNS FROM vagas LIKE 'descricao'")->num_rows;
$descricaoSql = $hasDescricao ? 'COALESCE(descricao, "")' : '""';
$stmtVagas = $conn->prepare("SELECT id_vaga, titulo, $descricaoSql AS descricao, tempo_vaga FROM vagas WHERE id_empresa = ? ORDER BY id_vaga DESC");
$empresaId = (int) $_SESSION['usuario_id'];
$stmtVagas->bind_param('i', $empresaId);
$stmtVagas->execute();
$empresaPosts = $stmtVagas->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtVagas->close();
$conn->close();

foreach ($empresaPosts as &$post) {
    $post['resumo'] = 'Publicada em ' . date('d/m/Y', strtotime($post['tempo_vaga']));
    $post['detalhe'] = $post['descricao'];
}
unset($post);

// Pessoas de exemplo mantidas no inicio para a empresa visualizar a tela como no layout original.
$talentos = [
    ['nome' => 'Marina Santos', 'resumo' => 'React, CSS e comunicacao clara.', 'detalhe' => 'Marina tem interesse em vagas de front-end junior e disponibilidade para conversar esta semana.'],
    ['nome' => 'Lucas Pereira', 'resumo' => 'PHP, MySQL e logica de programacao.', 'detalhe' => 'Lucas procura primeira oportunidade em desenvolvimento web e ja criou projetos escolares com banco de dados.'],
];

// Lista de talentos/desenvolvedores disponíveis na plataforma

// Lista de candidatos que se inscreveram nas vagas da empresa
// Busca no banco somente quem se candidatou a vagas desta empresa.
$conn = getDatabaseConnection();
$sqlCandidatos = 'SELECT
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
ORDER BY c.data_candidatura DESC, c.id_candidatura DESC';
$stmtCandidatos = $conn->prepare($sqlCandidatos);

if (!$stmtCandidatos) {
    throw new RuntimeException('Nao foi possivel carregar as candidaturas: ' . $conn->error);
}

$empresaId = (int) $_SESSION['usuario_id'];
$stmtCandidatos->bind_param('i', $empresaId);
$stmtCandidatos->execute();
$candidatos = $stmtCandidatos->get_result()->fetch_all(MYSQLI_ASSOC);
$stmtCandidatos->close();
$conn->close();

// Textos montados para o card e para o painel de detalhes da candidatura.
foreach ($candidatos as &$candidato) {
    $candidato['resumo'] = 'Vaga: ' . $candidato['vaga'] . ' | Status: ' . ucfirst($candidato['status']);
    $candidato['detalhe'] = 'E-mail: ' . $candidato['email']
        . '. Cursos: ' . ($candidato['cursos'] !== '' ? $candidato['cursos'] : 'nao informado')
        . '. Experiencia: ' . ($candidato['experiencia'] !== '' ? $candidato['experiencia'] : 'nao informada');
}
unset($candidato);
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
    <main class="dashboard-shell empresa-dashboard page-<?= h($pagina) ?>" data-tipo="<?= h($tipo) ?>">
        
        <aside class="sidebar">
            <div class="sidebar-topo">
                <a class="brand" href="empresa.php">
                    <?= dashboardIcon('brand') ?>
                    <span class="brand-text">Dev<span>IN</span></span>
                </a>
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
                    <span><?= $pagina === 'candidatos' ? 'GESTAO DE TALENTOS' : 'MINHAS VAGAS' ?></span>
                    <h1><?= $pagina === 'candidatos' ? 'Candidatos' : 'Vagas publicadas' ?></h1>
                </div>
                <?php if ($pagina === 'inicio'): ?><span class="result-count"><?= count($empresaPosts) ?> vagas</span><?php endif; ?>
            </header>

            <form class="busca" action="" method="get">
                <input type="hidden" name="pagina" value="<?= h($pagina) ?>">
                <label>
                    <input type="search" name="q" placeholder="Pesquise candidatos ou vagas">
                </label>
            </form>
            <?php endif; ?>

            <?php if ($pagina === 'sobre'): ?>
                <?= aboutPage() ?>

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
                <?php if (!empty($_SESSION['candidate_error'])): ?>
                    <p class="form-error"><?= h($_SESSION['candidate_error']); unset($_SESSION['candidate_error']); ?></p>
                <?php endif; ?>
                <?php if (!empty($_SESSION['candidate_success'])): ?>
                    <p class="form-success"><?= h($_SESSION['candidate_success']); unset($_SESSION['candidate_success']); ?></p>
                <?php endif; ?>

                <?php if (!$candidatos): ?>
                    <p class="empty-state">Ainda nao ha candidaturas para as suas vagas.</p>
                <?php endif; ?>
                <form method="post" class="test-candidates-form">
                    <input type="hidden" name="action" value="create_test_candidates">
                    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                    <button class="btn primary" type="submit">Criar candidatos de teste</button>
                    <small>Cria ou reinicia dois candidatos pendentes na primeira vaga da empresa.</small>
                </form>
                <?php foreach ($candidatos as $candidato): ?>
                    <article class="item-card" data-detail="<?= h($candidato['detalhe']) ?>">
                        <span class="card-avatar"><?= dashboardIcon('user') ?></span>
                        <div>
                            <h2><?= h($candidato['nome']) ?></h2>
                            <p><?= h($candidato['resumo']) ?></p>
                        </div>
                        <div class="acoes-card">
                            <?php if ($candidato['status'] === 'pendente'): ?>
                                <form method="post" class="candidate-actions">
                                    <input type="hidden" name="action" value="update_application_status">
                                    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                                    <input type="hidden" name="id_candidatura" value="<?= (int) $candidato['id_candidatura'] ?>">
                                    <button class="btn danger" name="status" value="recusado" type="submit">Nao se encaixa</button>
                                    <button class="btn success" name="status" value="aprovado" type="submit">Aprovar</button>
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
                        <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                        <label>Titulo da vaga
                            <input name="titulo" type="text" maxlength="25" placeholder="Ex.: Desenvolvedor PHP" required>
                        </label>
                        <label>Descricao
                            <textarea name="descricao" maxlength="255" placeholder="Descreva as atividades e requisitos da vaga." required></textarea>
                        </label>
                        <button class="btn primary" type="submit">Publicar vaga</button>
                    </form>
                </details>

                <?php if (!$empresaPosts): ?>
                    <p class="empty-state">Voce ainda nao publicou nenhuma vaga.</p>
                <?php endif; ?>

                <?php foreach ($empresaPosts as $post): ?>
                    <article class="item-card job-card" data-detail="<?= h($post['descricao'] ?: 'Sem descricao informada.') ?>" data-job-title="<?= h($post['titulo']) ?>">
                        <span class="card-avatar"><?= dashboardIcon('briefcase') ?></span>
                        <div>
                            <h2><?= h($post['titulo']) ?></h2>
                            <p><?= h($post['descricao'] ?: 'Sem descricao informada.') ?></p>
                        </div>
                        <div class="post-tools">
                            <details class="job-editor">
                                <summary class="edit" aria-label="Editar vaga" title="Editar vaga">
                                    <?= dashboardIcon('edit') ?>
                                </summary>
                                <form method="post" class="edit-job-form">
                                    <input type="hidden" name="action" value="update_job">
                                    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                                    <input type="hidden" name="id_vaga" value="<?= (int) $post['id_vaga'] ?>">
                                    <label>Titulo
                                        <input name="titulo" type="text" maxlength="25" value="<?= h($post['titulo']) ?>" required>
                                    </label>
                                    <label>Descricao
                                        <textarea name="descricao" maxlength="255" required><?= h($post['descricao']) ?></textarea>
                                    </label>
                                    <button class="btn primary" type="submit">Salvar</button>
                                </form>
                            </details>
                            <form method="post" onsubmit="return confirm('Excluir esta vaga e as candidaturas dela?');">
                                <input type="hidden" name="action" value="delete_job">
                                <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                                <input type="hidden" name="id_vaga" value="<?= (int) $post['id_vaga'] ?>">
                                <button class="delete" type="submit" aria-label="Excluir vaga" title="Excluir vaga">
                                    <?= dashboardIcon('trash') ?>
                                </button>
                            </form>
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
                <h2 id="detailTitle"><?= h($pagina === 'candidatos' ? 'Vaga em que o candidato se inscreveu' : 'Detalhes da vaga') ?></h2>
                <p id="detailText">Selecione uma vaga para ver sua descricao completa aqui.</p>
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
                <!-- Nome exibido apenas para consulta; ele nao e enviado nem pode ser editado. -->
                <label>Nome da empresa<input type="text" value="<?= h($perfilAtual['nome']) ?>" readonly aria-readonly="true"></label>
                <label>E-mail account<input name="email" type="email" value="<?= h($perfilAtual['email']) ?>" required></label>
                <label>Celular<input name="telefone" type="tel" value="<?= h($perfilAtual['telefone'] ?? '') ?>" required></label>
                <label>CEP<input name="cep" type="text" value="<?= h($perfilAtual['cep'] ?? '') ?>" required></label>
            </div>
            <button class="profile-save" type="submit">Save</button>
        </form>
    </dialog>

    <dialog class="settings-modal job-modal" id="jobModal" aria-labelledby="jobModalTitle">
        <form method="post" class="modal-form">
            <button class="modal-close" type="button" data-close-modal aria-label="Fechar">×</button>
            <h2 id="jobModalTitle">Criar vaga</h2>
            <input type="hidden" name="action" value="create_job" data-job-action>
            <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="id_vaga" value="" data-job-id>
            <label>Titulo da vaga<input name="titulo" type="text" maxlength="120" required data-job-title-input></label>
            <label>Descricao<textarea name="descricao" rows="6" maxlength="2000" data-job-description-input></textarea></label>
            <button class="btn primary" type="submit" data-job-submit>Publicar vaga</button>
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
