<?php

declare(strict_types=1);

require_once __DIR__ . '/middlewares/auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/MailerHelper.php';
require_once __DIR__ . '/config/security.php';

/*
|--------------------------------------------------------------------------
| VERIFICA LOGIN E TIPO DE CONTA
|--------------------------------------------------------------------------
*/

$usuario = requireWebAuth('pessoa');

/*
|--------------------------------------------------------------------------
| IDENTIFICA A PESSOA
|--------------------------------------------------------------------------
*/

$idPessoa    = (int) $usuario['id'];
$nomePessoa  = $_SESSION['nome_pessoa']  ?? $usuario['nome']  ?? 'Candidato';
$emailPessoa = $_SESSION['email_pessoa'] ?? $usuario['email'] ?? '';

$mensagemSucesso = '';
$mensagemErro    = '';

/*
|--------------------------------------------------------------------------
| SALVAR / ATUALIZAR CURRÍCULO (POST)
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrf();

    $nomeSocial       = trim(requestString($_POST, 'nome_social'));
    $grauEscolaridade = trim(requestString($_POST, 'grau_de_escolaridade'));
    $cursos           = trim(requestString($_POST, 'cursos'));
    $experiencia      = trim(requestString($_POST, 'experiencia'));
    $idiomas          = trim(requestString($_POST, 'idiomas'));

    try {
        if ($nomeSocial === '') {
            throw new InvalidArgumentException('Informe seu nome social.');
        }

        if ($grauEscolaridade === '') {
            throw new InvalidArgumentException('Selecione seu grau de escolaridade.');
        }

        $conn = getDatabaseConnection();

        try {
            // Verifica se o candidato já possui um currículo cadastrado
            $stmtCheck = $conn->prepare('SELECT id_curriculo FROM curriculo WHERE id_pessoa = ? LIMIT 1');
            $stmtCheck->bind_param('i', $idPessoa);
            $stmtCheck->execute();
            $resCheck = $stmtCheck->get_result();
            $jaExiste = $resCheck && $resCheck->num_rows > 0;
            $stmtCheck->close();

            if ($jaExiste) {
                $stmt = $conn->prepare("
                    UPDATE curriculo
                    SET
                        nome_social = ?,
                        grau_de_escolaridade = ?,
                        cursos = ?,
                        experiencia = ?,
                        idiomas = ?
                    WHERE id_pessoa = ?
                ");
                $stmt->bind_param('sssssi', $nomeSocial, $grauEscolaridade, $cursos, $experiencia, $idiomas, $idPessoa);
            } else {
                $stmt = $conn->prepare("
                    INSERT INTO curriculo
                    (id_pessoa, nome_social, grau_de_escolaridade, cursos, experiencia, idiomas)
                    VALUES
                    (?, ?, ?, ?, ?, ?)
                ");
                $stmt->bind_param('isssss', $idPessoa, $nomeSocial, $grauEscolaridade, $cursos, $experiencia, $idiomas);
            }

            $executou = $stmt->execute();
            $stmt->close();

            if ($executou) {
                // Notificações por e-mail apenas na criação inicial do currículo
                if (!$jaExiste) {
                    if (!empty($emailPessoa) && filter_var($emailPessoa, FILTER_VALIDATE_EMAIL)) {
                        MailerHelper::enviarConfirmacaoCadastroCurriculo($emailPessoa, $nomePessoa);
                    }
                    MailerHelper::notificarEmpresasNovoCandidato($conn, $nomePessoa);
                }

                // Desativa a flag de lembrete do e-mail de pendência
                $stmtLembrete = $conn->prepare('UPDATE pessoa SET lembrete_enviado = 0 WHERE id_pessoa = ?');
                if ($stmtLembrete) {
                    $stmtLembrete->bind_param('i', $idPessoa);
                    $stmtLembrete->execute();
                    $stmtLembrete->close();
                }

                header('Location: pessoa.php');
                exit;
            }

            $mensagemErro = 'Erro ao salvar o currículo. Tente novamente.';

        } finally {
            $conn->close();
        }

    } catch (Throwable $e) {
        error_log('Erro ao salvar currículo: ' . $e->getMessage());
        $mensagemErro = $e->getMessage();
    }
}

/*
|--------------------------------------------------------------------------
| BUSCA DADOS ATUAIS DO CURRÍCULO (GET)
|--------------------------------------------------------------------------
*/

$dadosCurriculo = [];

try {
    $conn = getDatabaseConnection();
    try {
        $stmtFetch = $conn->prepare("
            SELECT nome_social, grau_de_escolaridade, cursos, experiencia, idiomas
            FROM curriculo
            WHERE id_pessoa = ?
            LIMIT 1
        ");
        if ($stmtFetch) {
            $stmtFetch->bind_param('i', $idPessoa);
            $stmtFetch->execute();
            $dadosCurriculo = $stmtFetch->get_result()->fetch_assoc() ?? [];
            $stmtFetch->close();
        }
    } finally {
        $conn->close();
    }
} catch (Throwable $e) {
    error_log('Erro ao buscar currículo: ' . $e->getMessage());
}

$cNomeSocial       = $dadosCurriculo['nome_social']          ?? '';
$cGrauEscolaridade = $dadosCurriculo['grau_de_escolaridade'] ?? '';
$cCursos           = $dadosCurriculo['cursos']               ?? '';
$cExperiencia      = $dadosCurriculo['experiencia']          ?? '';
$cIdiomas          = $dadosCurriculo['idiomas']              ?? '';

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevIN | Preencher Currículo</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link rel="stylesheet" href="../css/cadastrostyle.css">
    <link rel="stylesheet" href="../css/site-navigation.css">
    <link rel="stylesheet" href="../css/curriculo.css">
</head>

<body class="curriculo-page">

<div class="main-container">

    <div class="left-side">

        <header class="cadastro-header">
            <div class="brand-logo">
                <a href="../index.php">
                    Dev<span>IN</span>
                </a>
            </div>

            <button class="site-menu-toggle" type="button" aria-label="Abrir menu" aria-controls="site-menu" aria-expanded="false" data-site-menu-toggle>
                <span aria-hidden="true"></span><span aria-hidden="true"></span><span aria-hidden="true"></span>
            </button>
            <div class="site-menu" id="site-menu" data-site-menu>
                <a href="logout.php" class="header-action">Sair</a>
            </div>
        </header>

        <?php if ($mensagemSucesso): ?>
            <div class="php-toast success-toast" role="status">
                <?= htmlspecialchars($mensagemSucesso, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <?php if ($mensagemErro): ?>
            <div class="php-toast error-toast" role="alert">
                <?= htmlspecialchars($mensagemErro, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="cadastrar_curriculo.php" class="register-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">

            <h2 class="title-curriculo">Preenchimento de Currículo</h2>

            <div class="form-columns">

                <div class="form-column">

                    <div class="input-group">
                        <label for="nome_social">Nome Social / Como prefere ser chamado(a):</label>
                        <input type="text" id="nome_social" name="nome_social" placeholder="Ex: Alex Silva" value="<?= htmlspecialchars($cNomeSocial, ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <div class="input-group">
                        <label for="grau_de_escolaridade">Grau de Escolaridade:</label>
                        <select id="grau_de_escolaridade" name="grau_de_escolaridade" required>
                            <option value="">Selecione...</option>
                            <option value="Ensino Médio Incompleto" <?= $cGrauEscolaridade === 'Ensino Médio Incompleto' ? 'selected' : '' ?>>Ensino Médio Incompleto</option>
                            <option value="Ensino Médio Completo" <?= $cGrauEscolaridade === 'Ensino Médio Completo' ? 'selected' : '' ?>>Ensino Médio Completo</option>
                            <option value="Ensino Superior Incompleto" <?= $cGrauEscolaridade === 'Ensino Superior Incompleto' ? 'selected' : '' ?>>Ensino Superior Incompleto</option>
                            <option value="Ensino Superior Completo" <?= $cGrauEscolaridade === 'Ensino Superior Completo' ? 'selected' : '' ?>>Ensino Superior Completo</option>
                            <option value="Pós-graduação / Especialização" <?= $cGrauEscolaridade === 'Pós-graduação / Especialização' ? 'selected' : '' ?>>Pós-graduação / Especialização</option>
                        </select>
                    </div>

                    <div class="input-group">
                        <label for="idiomas">Idiomas:</label>
                        <input type="text" id="idiomas" name="idiomas" placeholder="Ex: Português Nativo, Inglês Intermediário" value="<?= htmlspecialchars($cIdiomas, ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                </div>

                <div class="form-column">

                    <div class="input-group">
                        <label for="cursos">Cursos e Certificações:</label>
                        <textarea id="cursos" name="cursos" placeholder="Ex: Curso de PHP Avançado, HTML5/CSS3, MySQL"><?= htmlspecialchars($cCursos, ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <div class="input-group">
                        <label for="experiencia">Experiência Profissional:</label>
                        <textarea id="experiencia" name="experiencia" placeholder="Ex: Desenvolvedor Web na Empresa X (2022 - Atual)"><?= htmlspecialchars($cExperiencia, ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                </div>

            </div>

            <div class="form-footer-action">
                <button type="submit" class="btn-submit">Finalizar e Salvar Currículo</button>
                <div class="login-redirect">
                    Deseja sair do sistema? <a href="logout.php">Clique aqui</a>
                </div>
            </div>

        </form>

        <div class="page-footer">
            © <?= date('Y') ?> <span>DevIN</span>. Todos os direitos reservados.
        </div>

    </div>

    <div class="right-side">
        <a href="logout.php" class="btn-top-login">Sair</a>
        <div class="mascot-container">
            <img src="../img/robocadastro.webp" alt="Mascote DevIN" class="mascot-img">
        </div>
    </div>

</div>

<script src="../js/site-navigation.js"></script>
</body>
</html>