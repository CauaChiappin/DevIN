<?php

declare(strict_types=1);

require_once __DIR__ . '/middlewares/auth.php';
require_once __DIR__ . '/controllers/ProfileController.php';
require_once __DIR__ . '/controllers/JobSchema.php';
require_once __DIR__ . '/controllers/ResumeDocument.php';
require_once __DIR__ . '/helpers.php';

$usuarioAtual = requireWebAuth('pessoa');

$tipo          = 'pessoa';
$idPessoaAtual = (int) $usuarioAtual['id'];
$nome          = $_SESSION['usuario_nome']  ?? $usuarioAtual['nome']  ?? 'Usuário';
$email         = $_SESSION['usuario_email'] ?? $usuarioAtual['email'] ?? 'email@devin.com';
$mensagemSenhaAlterada = (string) ($_SESSION['sucesso_login'] ?? '');
$mensagemEmailSenha = (string) ($_SESSION['erro_email_senha'] ?? '');
$mensagemCadastro = (string) ($_SESSION['sucesso_cadastro'] ?? '');
$mensagemEmailCadastro = (string) ($_SESSION['erro_email_cadastro'] ?? '');
$mensagemCurriculoSalvo = (string) ($_SESSION['sucesso_curriculo'] ?? '');
$mensagemEmailCurriculo = (string) ($_SESSION['erro_curriculo'] ?? '');
$mensagemCurriculoEditado = (string) ($_SESSION['resume_content_success'] ?? '');
$mensagemCurriculoErro = (string) ($_SESSION['resume_content_error'] ?? '');
unset(
    $_SESSION['sucesso_login'],
    $_SESSION['erro_email_senha'],
    $_SESSION['sucesso_cadastro'],
    $_SESSION['erro_email_cadastro'],
    $_SESSION['sucesso_curriculo'],
    $_SESSION['erro_curriculo'],
    $_SESSION['resume_content_success'],
    $_SESSION['resume_content_error']
);
$pagina        = requestString($_GET, 'pagina');

$paginasPermitidas = ['inicio', 'vagas', 'curriculo', 'sobre', 'perfil', 'mensagens'];
if (!in_array($pagina, $paginasPermitidas, true)) {
    // Aceita apenas as seções válidas do painel do candidato.
    $pagina = 'inicio';
}
$gerarCurriculoAutomaticamente = $pagina === 'curriculo'
    && requestString($_GET, 'gerar') === '1'
    && ($_SESSION['resume_generate_on_dashboard'] ?? false) === true;
if ($gerarCurriculoAutomaticamente) {
    unset($_SESSION['resume_generate_on_dashboard']);
}

/*
|--------------------------------------------------------------------------
| PROCESSAMENTO DE AÇÕES (POST)
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Valida CSRF antes de aceitar candidatura ou alteração de perfil.
    requireValidCsrf();

    try {
        $action = requestString($_POST, 'action');

        if ($action === 'apply_job') {
            // A candidatura pertence ao ID da sessão; o cliente envia apenas qual vaga deseja solicitar.
            $vagaId = (int) requestString($_POST, 'id_vaga');
            if ($vagaId <= 0) {
                throw new InvalidArgumentException('Vaga inválida.');
            }

            $conn = getDatabaseConnection();
            try {
                ensureJobCompensationSchema($conn);
                if (!ResumeDocument::hasResume($conn, $idPessoaAtual)) {
                    throw new InvalidArgumentException('Você precisa adicionar um currículo para se candidatar. Envie um arquivo ou crie um currículo com IA em “Meu currículo”.');
                }

                $conn->begin_transaction();
                try {
                    // Bloqueia a vaga enquanto valida o encerramento e registra a candidatura.
                    $check = $conn->prepare("
                        SELECT id_vaga
                        FROM vagas
                        WHERE id_vaga = ?
                          AND encerrada_em IS NULL
                          AND (
                              criterio_encerramento NOT IN ('prazo', 'qualquer')
                              OR data_encerramento > CURRENT_TIMESTAMP
                          )
                        LIMIT 1
                        FOR UPDATE
                    ");
                    $check->bind_param('i', $vagaId);
                    $check->execute();
                    $exists = $check->get_result()->num_rows === 1;
                    $check->close();

                    if (!$exists) {
                        throw new InvalidArgumentException('A vaga não está mais disponível.');
                    }

                    /* A consulta evita uma candidatura repetida antes do INSERT; ambos os IDs vêm
                     * da sessão e do formulário validado, e são enviados como parâmetros inteiros. */
                    $duplicate = $conn->prepare('SELECT id_candidatura FROM candidatura WHERE id_pessoa = ? AND id_vaga = ? LIMIT 1');
                    $duplicate->bind_param('ii', $idPessoaAtual, $vagaId);
                    $duplicate->execute();
                    $alreadyApplied = $duplicate->get_result()->num_rows > 0;
                    $duplicate->close();

                    if ($alreadyApplied) {
                        throw new InvalidArgumentException('Você já se candidatou a esta vaga.');
                    }

                    // Cria o registro inicial como pendente para análise da empresa responsável.
                    $stmt = $conn->prepare('INSERT INTO candidatura (data_candidatura, status, id_pessoa, id_vaga) VALUES (CURDATE(), ?, ?, ?)');
                    $status = 'pendente';
                    $stmt->bind_param('sii', $status, $idPessoaAtual, $vagaId);
                    $stmt->execute();
                    $stmt->close();
                    $conn->commit();
                } catch (Throwable $exception) {
                    $conn->rollback();
                    throw $exception;
                }
            } finally {
                $conn->close();
            }

            $_SESSION['job_success'] = 'Candidatura enviada com sucesso.';
            header('Location: pessoa.php?pagina=inicio');
            exit;
        }

        if ($action === 'update_resume_content') {
            $submittedResume = is_array($_POST['resume'] ?? null) ? $_POST['resume'] : [];
            $resumeContentToSave = ['nome' => $nome];
            foreach (['resumo', 'formacao', 'experiencia', 'cursos', 'idiomas'] as $field) {
                $value = requestString($submittedResume, $field);
                if (strlen($value) > 6000) {
                    throw new InvalidArgumentException('Uma seção do currículo excede o limite de 6.000 caracteres.');
                }
                $resumeContentToSave[$field] = $value;
            }
            if (count(array_filter($resumeContentToSave, static fn ($value): bool => is_string($value) && trim($value) !== '')) < 2) {
                throw new InvalidArgumentException('Preencha ao menos uma seção antes de salvar.');
            }

            $conn = getDatabaseConnection();
            try {
                ResumeDocument::saveGenerated($conn, $idPessoaAtual, $resumeContentToSave);
            } finally {
                $conn->close();
            }
            $_SESSION['resume_content_success'] = 'Currículo atualizado.';
            header('Location: pessoa.php?pagina=curriculo');
            exit;
        }

        if ($action === 'update_resume_content') {
            $submittedResume = is_array($_POST['resume'] ?? null) ? $_POST['resume'] : [];
            $resumeContentToSave = ['nome' => $nome];
            foreach (['resumo', 'formacao', 'experiencia', 'cursos', 'idiomas'] as $field) {
                $value = requestString($submittedResume, $field);
                if (strlen($value) > 6000) {
                    throw new InvalidArgumentException('Uma seção do currículo excede o limite de 6.000 caracteres.');
                }
                $resumeContentToSave[$field] = $value;
            }
            if (count(array_filter($resumeContentToSave, static fn ($value): bool => is_string($value) && trim($value) !== '')) < 2) {
                throw new InvalidArgumentException('Preencha ao menos uma seção antes de salvar.');
            }

            $conn = getDatabaseConnection();
            try {
                ResumeDocument::saveGenerated($conn, $idPessoaAtual, $resumeContentToSave);
            } finally {
                $conn->close();
            }
            $_SESSION['resume_content_success'] = 'Currículo atualizado.';
            header('Location: pessoa.php?pagina=curriculo');
            exit;
        }

        if ($action === 'update_profile') {
            updateProfile($tipo, $idPessoaAtual, $_POST, $_FILES['foto'] ?? null);
            $_SESSION['usuario_nome']  = trim(requestString($_POST, 'nome'));
            $_SESSION['usuario_email'] = trim(requestString($_POST, 'email'));
            $_SESSION['profile_success'] = 'Perfil atualizado com sucesso.';
            header('Location: pessoa.php?pagina=' . rawurlencode($pagina));
            exit;
        }

        if ($action === 'update_settings') {
            updateLanguage($tipo, $idPessoaAtual, requestString($_POST, 'idioma') ?: 'pt-BR');
            $_SESSION['settings_success'] = 'Idioma salvo com sucesso.';
            header('Location: pessoa.php?pagina=' . rawurlencode($pagina) . '&configuracoes=1');
            exit;
        }

        if ($action === 'delete_account') {
            deleteProfile($tipo, $idPessoaAtual);
            header('Location: logout.php?destino=index');
            exit;
        }
    } catch (Throwable $exception) {
        error_log('Erro no dashboard pessoa: ' . $exception->getMessage());
        if ($action === 'apply_job') {
            $_SESSION['job_error'] = $exception instanceof InvalidArgumentException
                ? $exception->getMessage()
                : 'Não foi possível enviar a candidatura. Tente novamente.';
            header('Location: pessoa.php?pagina=vagas');
        } elseif ($action === 'update_resume_content') {
            $_SESSION['resume_content_error'] = $exception instanceof InvalidArgumentException
                ? $exception->getMessage()
                : 'Não foi possível salvar o currículo agora.';
            header('Location: pessoa.php?pagina=curriculo');
        } elseif ($action === 'update_profile') {
            $_SESSION['profile_error'] = 'Não foi possível atualizar o perfil. Tente novamente.';
            header('Location: pessoa.php?pagina=' . rawurlencode($pagina));
        } elseif ($action === 'update_settings') {
            $_SESSION['settings_error'] = 'Não foi possível salvar o idioma. Tente novamente.';
            header('Location: pessoa.php?pagina=' . rawurlencode($pagina) . '&configuracoes=1');
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
$idiomaAtual = getProfileLanguage($tipo, $idPessoaAtual);

$vagasDisponiveis  = [];
$minhasCandidaturas = [];
$vagasCandidatadas = [];
$curriculoAtual = [];
$resumeDocument = [];
$resumeDocument = [];

try {
    $conn = getDatabaseConnection();
    try {
        ensureJobCompensationSchema($conn);
        // Relaciona cada vaga à empresa e restringe candidaturas ao usuário autenticado.
        // Carrega vagas com seus dados de empresa para a lista de oportunidades disponíveis.
        $stmt = $conn->prepare("
            SELECT v.id_vaga, v.titulo, COALESCE(v.descricao, '') AS descricao,
                   v.salario_mensal, v.limite_aprovados, v.tempo_vaga,
                   v.criterio_encerramento, v.data_encerramento, v.encerrada_em,
                   e.id_empresa, e.nome AS empresa,
                   COALESCE(contagens.aprovadas, 0) AS candidaturas_aprovadas,
                   COALESCE(contagens.recusadas, 0) AS candidaturas_recusadas,
                   COALESCE(contagens.em_analise, 0) AS candidaturas_em_analise
            FROM vagas v
            INNER JOIN empresa e ON e.id_empresa = v.id_empresa
            LEFT JOIN (
                SELECT id_vaga,
                       COUNT(CASE WHEN status = 'aprovado' THEN 1 END) AS aprovadas,
                       COUNT(CASE WHEN status = 'recusado' THEN 1 END) AS recusadas,
                       COUNT(CASE WHEN status NOT IN ('aprovado', 'recusado') THEN 1 END) AS em_analise
                FROM candidatura
                GROUP BY id_vaga
            ) contagens ON contagens.id_vaga = v.id_vaga
            ORDER BY v.id_vaga DESC
        ");
        $stmt->execute();
        $vagasDisponiveis = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Busca apenas candidaturas deste usuário, junto ao status e aos dados da vaga.
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

        foreach ($minhasCandidaturas as $candidatura) {
            $vagasCandidatadas[(int) $candidatura['id_vaga']] = true;
        }

        $stmt = $conn->prepare('SELECT id_curriculo, nome_social, grau_de_escolaridade, cursos, experiencia, idiomas FROM curriculo WHERE id_pessoa = ? LIMIT 1');
        $stmt->bind_param('i', $idPessoaAtual);
        $stmt->execute();
        $curriculoAtual = $stmt->get_result()->fetch_assoc() ?? [];
        $stmt->close();
        $resumeDocument = ResumeDocument::find($conn, $idPessoaAtual);

        $stmt = $conn->prepare('SELECT id_curriculo, nome_social, grau_de_escolaridade, cursos, experiencia, idiomas FROM curriculo WHERE id_pessoa = ? LIMIT 1');
        $stmt->bind_param('i', $idPessoaAtual);
        $stmt->execute();
        $curriculoAtual = $stmt->get_result()->fetch_assoc() ?? [];
        $stmt->close();
        $resumeDocument = ResumeDocument::find($conn, $idPessoaAtual);
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
$resumeContent = json_decode((string) ($resumeDocument['conteudo_json'] ?? ''), true);
$resumeContent = is_array($resumeContent) ? $resumeContent : [];
$possuiCurriculo = !empty($curriculoAtual['id_curriculo'])
    || !empty($resumeDocument['arquivo_caminho'])
    || !empty($resumeDocument['conteudo_json']);
if ($resumeContent === [] && !empty($curriculoAtual['id_curriculo'])) {
    $resumeContent = [
        'nome' => (string) ($curriculoAtual['nome_social'] ?? $nome),
        'resumo' => '',
        'formacao' => (string) ($curriculoAtual['grau_de_escolaridade'] ?? ''),
        'experiencia' => (string) ($curriculoAtual['experiencia'] ?? ''),
        'cursos' => (string) ($curriculoAtual['cursos'] ?? ''),
        'idiomas' => (string) ($curriculoAtual['idiomas'] ?? ''),
    ];
}
$resumeContent['nome'] = (string) ($resumeContent['nome'] ?? $nome);
$hasResumeText = count(array_filter(
    ['resumo', 'formacao', 'experiencia', 'cursos', 'idiomas'],
    static fn (string $field): bool => trim((string) ($resumeContent[$field] ?? '')) !== ''
)) > 0;
$hasResumePreview = $hasResumeText || !empty($resumeDocument['arquivo_nome']);
$resumeContent = json_decode((string) ($resumeDocument['conteudo_json'] ?? ''), true);
$resumeContent = is_array($resumeContent) ? $resumeContent : [];
$possuiCurriculo = !empty($curriculoAtual['id_curriculo'])
    || !empty($resumeDocument['arquivo_caminho'])
    || !empty($resumeDocument['conteudo_json']);

?>
<!DOCTYPE html>
<html lang="<?= h($idiomaAtual) ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevIN | Dashboard Candidato</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link rel="stylesheet" href="../css/dashboard.css?v=<?= filemtime(__DIR__ . '/../css/dashboard.css') ?>">
    <?php if ($pagina === 'mensagens'): ?>
        <link rel="stylesheet" href="../css/chat.css?v=<?= filemtime(__DIR__ . '/../css/chat.css') ?>">
    <?php endif; ?>
</head>

<body>
    <main class="dashboard-shell page-<?= h($pagina) ?>" data-tipo="<?= h($tipo) ?>">
        <?php if ($mensagemSenhaAlterada !== '' || $mensagemCadastro !== '' || $mensagemEmailSenha !== '' || $mensagemEmailCadastro !== '' || $mensagemCurriculoSalvo !== '' || $mensagemEmailCurriculo !== '' || $mensagemCurriculoEditado !== '' || $mensagemCurriculoErro !== ''): ?>
            <div class="dashboard-toast" role="status" aria-live="polite">
                <?php if ($mensagemSenhaAlterada !== ''): ?>
                    <p class="dashboard-toast__msg dashboard-toast__msg--success"><?= h($mensagemSenhaAlterada) ?></p>
                <?php endif; ?>
                <?php if ($mensagemCadastro !== ''): ?>
                    <p class="dashboard-toast__msg dashboard-toast__msg--success"><?= h($mensagemCadastro) ?></p>
                <?php endif; ?>
                <?php if ($mensagemEmailSenha !== ''): ?>
                    <p class="dashboard-toast__msg dashboard-toast__msg--error" role="alert"><?= h($mensagemEmailSenha) ?></p>
                <?php endif; ?>
                <?php if ($mensagemEmailCadastro !== ''): ?>
                    <p class="dashboard-toast__msg dashboard-toast__msg--error" role="alert"><?= h($mensagemEmailCadastro) ?></p>
                <?php endif; ?>
                <?php if ($mensagemCurriculoSalvo !== ''): ?>
                    <p class="dashboard-toast__msg dashboard-toast__msg--success"><?= h($mensagemCurriculoSalvo) ?></p>
                <?php endif; ?>
                <?php if ($mensagemCurriculoEditado !== ''): ?>
                    <p class="dashboard-toast__msg dashboard-toast__msg--success"><?= h($mensagemCurriculoEditado) ?></p>
                <?php endif; ?>
                <?php if ($mensagemCurriculoErro !== ''): ?>
                    <p class="dashboard-toast__msg dashboard-toast__msg--error" role="alert"><?= h($mensagemCurriculoErro) ?></p>
                <?php endif; ?>
                <?php if ($mensagemEmailCurriculo !== ''): ?>
                    <p class="dashboard-toast__msg dashboard-toast__msg--error" role="alert"><?= h($mensagemEmailCurriculo) ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
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
                <a class="<?= ativo($pagina, 'curriculo') ?>" href="pessoa.php?pagina=curriculo">
                    <?= dashboardIcon('file') ?><span class="menu-text">Meu currículo</span>
                </a>
                <a class="<?= ativo($pagina, 'mensagens') ?>" href="pessoa.php?pagina=mensagens" data-messages-api="../php/mensagens_api.php">
                    <?= dashboardIcon('users') ?><span class="menu-text">Mensagens</span><span class="message-unread-badge" data-message-unread-badge hidden></span>
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
            <?php if (!in_array($pagina, ['sobre', 'mensagens', 'curriculo'], true)): ?>
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
            <?php elseif ($pagina === 'curriculo'): ?>
                <section class="dashboard-resume">
                    <header class="dashboard-resume__header">
                        <div>
                            <p class="dashboard-resume__eyebrow">MEU CURRÍCULO</p>
                            <h1><?= $possuiCurriculo ? 'Seu currículo' : 'Adicione um currículo para se candidatar' ?></h1>
                            <p><?= $possuiCurriculo ? 'Revise, edite e baixe seu documento. Você pode substituir o arquivo enviado quando quiser.' : 'Você pode enviar um arquivo pronto ou criar um currículo com ajuda da IA.' ?></p>
                        </div>
                    </header>

                    <?php if (!$possuiCurriculo): ?>
                        <section class="dashboard-resume__missing" aria-labelledby="resume-missing-title">
                            <h2 id="resume-missing-title">Ainda não há currículo nesta conta</h2>
                            <p>Para se candidatar às vagas, envie um PDF/Word ou preencha seus dados para criar um currículo.</p>
                            <a class="dashboard-resume__button dashboard-resume__button--primary" href="curriculo_onboarding.php">Escolher como continuar</a>
                        </section>
                    <?php else: ?>
                    <div class="dashboard-resume__content">
                        <section class="dashboard-resume__generator" aria-labelledby="resume-generator-title" data-resume-csrf="<?= h(csrfToken()) ?>" data-resume-auto-generate="<?= $gerarCurriculoAutomaticamente ? 'true' : 'false' ?>">
                            <div class="dashboard-resume__generator-icon" aria-hidden="true">✦</div>
                            <h2 id="resume-generator-title">Editar ou melhorar</h2>
                            <p>Usaremos seu nome social, escolaridade, cursos, experiência e idiomas para criar um currículo claro e profissional. A IA não deve inventar dados; confira o texto antes de usar.</p>
                            <label class="dashboard-resume__consent">
                                <input type="checkbox" data-resume-consent>
                                <span>Entendo que, ao gerar, os dados do meu currículo serão enviados ao Google Gemini para criar o texto. A chave da API fica protegida no servidor.</span>
                            </label>
                            <div class="dashboard-resume__actions">
                                <?php if (!empty($curriculoAtual['id_curriculo'])): ?>
                                    <button class="dashboard-resume__button dashboard-resume__button--primary" type="button" data-generate-resume disabled>✦ Gerar com IA</button>
                                <?php endif; ?>
                                <?php if ($hasResumeText): ?>
                                    <button class="dashboard-resume__button dashboard-resume__button--secondary" type="button" data-print-resume>Baixar / Imprimir PDF</button>
                                    <a class="dashboard-resume__button dashboard-resume__button--secondary" href="curriculo_exportar.php?formato=docx">Baixar DOCX</a>
                                <?php endif; ?>
                                <?php if (!empty($resumeDocument['arquivo_caminho'])): ?>
                                    <a class="dashboard-resume__button dashboard-resume__button--secondary" href="curriculo_baixar.php">Baixar arquivo original</a>
                                <?php endif; ?>
                                <a class="dashboard-resume__button dashboard-resume__button--secondary" href="curriculo_upload.php">Substituir currículo</a>
                                <a class="dashboard-resume__button dashboard-resume__button--secondary" href="cadastrar_curriculo.php">Editar nome e dados</a>
                            </div>
                            <p class="dashboard-resume__status" data-resume-status role="status" aria-live="polite">Seus dados só serão enviados quando você confirmar e solicitar a geração.</p>
                            <details class="dashboard-resume__editor">
                                <summary>Editar conteúdo do currículo</summary>
                                <form method="post">
                                    <input type="hidden" name="action" value="update_resume_content">
                                    <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                                    <?php foreach (['resumo' => 'Resumo profissional', 'formacao' => 'Formação', 'experiencia' => 'Experiência profissional', 'cursos' => 'Cursos e certificações', 'idiomas' => 'Idiomas'] as $field => $label): ?>
                                        <label><?= h($label) ?><textarea name="resume[<?= h($field) ?>]" maxlength="6000"><?= h((string) ($resumeContent[$field] ?? '')) ?></textarea></label>
                                    <?php endforeach; ?>
                                    <button class="dashboard-resume__button dashboard-resume__button--primary" type="submit">Salvar alterações</button>
                                </form>
                            </details>
                            <p class="dashboard-resume__privacy">A geração envia os dados ao Google Gemini somente após consentimento e pode consumir cota da chave configurada pelo DevIN.</p>
                        </section>

                        <article class="dashboard-resume__paper<?= $hasResumePreview ? '' : ' dashboard-resume__paper--empty' ?>" data-resume-output aria-label="Prévia do currículo" aria-live="polite">
                            <div class="dashboard-resume__empty-state" data-resume-empty <?= $hasResumePreview ? 'hidden' : '' ?>>
                                <span aria-hidden="true">▤</span>
                                <h2>Seu currículo aparecerá aqui</h2>
                                <p>Confirme o envio dos dados e clique em <strong>Gerar currículo com IA</strong>.</p>
                            </div>
                            <div data-resume-content <?= $hasResumePreview ? '' : 'hidden' ?>>
                                <h2><?= h($resumeContent['nome']) ?></h2>
                                <?php foreach (['resumo' => 'Resumo profissional', 'formacao' => 'Formação', 'experiencia' => 'Experiência profissional', 'cursos' => 'Cursos e certificações', 'idiomas' => 'Idiomas'] as $field => $label): ?>
                                    <?php if (trim((string) ($resumeContent[$field] ?? '')) !== ''): ?>
                                        <section class="dashboard-resume__section">
                                            <h3><?= h($label) ?></h3>
                                            <p><?= h((string) $resumeContent[$field]) ?></p>
                                        </section>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                                <?php if (!empty($resumeDocument['arquivo_nome'])): ?>
                                    <section class="dashboard-resume__section dashboard-resume__file">
                                        <h3>Arquivo enviado</h3>
                                        <p><?= h((string) $resumeDocument['arquivo_nome']) ?></p>
                                        <a href="curriculo_baixar.php">Abrir ou baixar arquivo original</a>
                                        <iframe class="dashboard-resume__uploaded-preview" src="curriculo_visualizar.php" title="Prévia do currículo enviado" loading="lazy"></iframe>
                                    </section>
                                <?php endif; ?>
                            </div>
                        </article>
                    </div>
                    <?php endif; ?>
                </section>
            <?php elseif ($pagina === 'sobre'): ?>
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
                    <p class="form-error"><?= h($_SESSION['job_error']); unset($_SESSION['job_error']); ?> <a href="pessoa.php?pagina=curriculo">Abrir Meu currículo</a></p>
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
                    <?php $jaSeCandidatou = isset($vagasCandidatadas[(int) $vaga['id_vaga']]); ?>
                    <?php $vagaEncerrada = !empty($vaga['encerrada_em']); ?>
                    <?php $statusVaga = $vagaEncerrada ? 'Vaga encerrada' : ($jaSeCandidatou ? 'Candidatura enviada' : 'Vaga disponível'); ?>
                    <?php $salarioExibido = (float) $vaga['salario_mensal'] > 0 ? 'R$ ' . number_format((float) $vaga['salario_mensal'], 2, ',', '.') : 'A combinar'; ?>
                    <?php $limiteExibido = (int) $vaga['limite_aprovados'] > 0 ? (string) $vaga['limite_aprovados'] : 'Sem limite'; ?>
                    <?php $prazoVagaExibido = !empty($vaga['data_encerramento']) ? date('d/m/Y H:i', strtotime($vaga['data_encerramento'])) : ''; ?>
                    <article class="item-card" data-detail="<?= h($vaga['detalhe']) ?>" data-job-title="<?= h($vaga['titulo']) ?>" data-detail-role="<?= h($vaga['empresa']) ?>" data-detail-tags="<?= h($statusVaga) ?>|<?= h($vaga['empresa']) ?>" data-detail-experience="<?= h(($vagaEncerrada ? 'Vaga encerrada' : ($jaSeCandidatou ? 'Candidatura enviada::Você já se candidatou a esta vaga' : 'Vaga publicada::Confira os requisitos e envie sua candidatura')) . '|Salário mensal::' . $salarioExibido . '|Limite de aprovados::' . $limiteExibido . '|Aprovadas::' . (int) $vaga['candidaturas_aprovadas'] . '|Recusadas::' . (int) $vaga['candidaturas_recusadas'] . '|Em análise::' . (int) $vaga['candidaturas_em_analise'] . ($prazoVagaExibido !== '' ? '|Data de encerramento::' . $prazoVagaExibido : '')) ?>" data-application-summary="true" data-report-job-id="<?= (int) $vaga['id_vaga'] ?>" data-detail-action-label="Candidatar-se" data-chat-type="empresa" data-chat-id="<?= (int) $vaga['id_empresa'] ?>">
                        <span class="card-avatar"><?= dashboardIcon('briefcase') ?></span>
                        <div>
                            <h2><?= h($vaga['empresa']) ?></h2>
                            <p><?= h($vaga['titulo']) ?> · <?= h($vaga['descricao'] ?: 'Sem descrição informada.') ?></p>
                            <p class="job-compensation"><span>Salário mensal:</span> <strong><?= h($salarioExibido) ?></strong> · <span>Máximo de aprovados:</span> <strong><?= h($limiteExibido) ?></strong></p>
                            <?php if ($prazoVagaExibido !== ''): ?><p class="job-deadline">Candidaturas até: <?= h($prazoVagaExibido) ?></p><?php endif; ?>
                            <?= dashboardCardTags($statusVaga . '|' . $vaga['empresa']) ?>
                        </div>
                        <div class="card-actions">
                            <form method="post">
                                <input type="hidden" name="action" value="apply_job">
                                <input type="hidden" name="csrf_token" value="<?= h(csrfToken()) ?>">
                                <input type="hidden" name="id_vaga" value="<?= (int) $vaga['id_vaga'] ?>">
                                <?php if ($vagaEncerrada): ?>
                                    <button class="btn applied" type="button" disabled>Vaga encerrada</button>
                                <?php elseif ($jaSeCandidatou): ?>
                                    <button class="btn applied" type="button" disabled>Candidatou-se</button>
                                <?php elseif (!$possuiCurriculo): ?>
                                    <button class="btn applied" type="button" disabled>Adicione um currículo</button>
                                <?php else: ?>
                                    <button class="btn primary" type="submit" data-detail-action-target>Candidatar-se</button>
                                <?php endif; ?>
                            </form>
                            <button class="btn primary" type="button" data-start-chat data-chat-type="empresa" data-chat-id="<?= (int) $vaga['id_empresa'] ?>">Conversar</button>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>

        <?php if (!in_array($pagina, ['sobre', 'perfil', 'mensagens', 'curriculo'], true)): ?>
            <?= dashboardDetailPanel() ?>
        <?php elseif ($pagina !== 'mensagens'): ?>
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
    <?php if ($pagina === 'curriculo'): ?>
        <script src="../js/curriculo.js?v=<?= filemtime(__DIR__ . '/../js/curriculo.js') ?>" defer></script>
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
