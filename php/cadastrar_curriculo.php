<?php
// cadastrar_curriculo.php

session_start();
require_once __DIR__ . '/php/config/database.php';
require_once __DIR__ . '/MailerHelper.php';

// Verifica se o usuário está logado como pessoa física
if (!isset($_SESSION['id_pessoa'])) {
    header('Location: login.php');
    exit;
}

$idPessoa    = (int) $_SESSION['id_pessoa'];
$nomePessoa  = $_SESSION['nome_pessoa'] ?? 'Candidato';
$emailPessoa = $_SESSION['email_pessoa'] ?? '';

$mensagemSucesso = '';
$mensagemErro    = '';

$conn = getDatabaseConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomeSocial       = trim($_POST['nome_social'] ?? '');
    $grauEscolaridade = trim($_POST['grau_de_escolaridade'] ?? '');
    $cursos           = trim($_POST['cursos'] ?? '');
    $experiencia      = trim($_POST['experiencia'] ?? '');
    $idiomas          = trim($_POST['idiomas'] ?? '');

    // Verifica se a pessoa já possui currículo cadastrado
    $stmtCheck = $conn->prepare("SELECT id_curriculo FROM curriculo WHERE id_pessoa = ?");
    $stmtCheck->bind_param('i', $idPessoa);
    $stmtCheck->execute();
    $resCheck = $stmtCheck->get_result();

    if ($resCheck->num_rows > 0) {
        // Atualiza o currículo existente
        $stmtUpdate = $conn->prepare("
            UPDATE curriculo 
            SET nome_social = ?, grau_de_escolaridade = ?, cursos = ?, experiencia = ?, idiomas = ?
            WHERE id_pessoa = ?
        ");
        $stmtUpdate->bind_param('sssssi', $nomeSocial, $grauEscolaridade, $cursos, $experiencia, $idiomas, $idPessoa);
        $executou = $stmtUpdate->execute();
        $stmtUpdate->close();
    } else {
        // Insere novo currículo
        $stmtInsert = $conn->prepare("
            INSERT INTO curriculo (id_pessoa, nome_social, grau_de_escolaridade, cursos, experiencia, idiomas)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmtInsert->bind_param('isssss', $idPessoa, $nomeSocial, $grauEscolaridade, $cursos, $experiencia, $idiomas);
        $executou = $stmtInsert->execute();
        $stmtInsert->close();
    }

    $stmtCheck->close();

    if ($executou) {
        // 1. Envia o e-mail de confirmação completa para o candidato
        if (!empty($emailPessoa)) {
            MailerHelper::enviarConfirmacaoCadastroCurriculo($emailPessoa, $nomePessoa);
        }

        // 2. Notifica as empresas cadastradas sobre o novo candidato
        MailerHelper::notificarEmpresasNovoCandidato($conn, $nomePessoa);

        $mensagemSucesso = "Currículo salvo com sucesso! Um e-mail de confirmação foi enviado para você.";
    } else {
        $mensagemErro = "Erro ao salvar o currículo. Tente novamente.";
    }
}

// Busca os dados do currículo existente para preencher o formulário automaticamente
$stmtFetch = $conn->prepare("SELECT nome_social, grau_de_escolaridade, cursos, experiencia, idiomas FROM curriculo WHERE id_pessoa = ?");
$stmtFetch->bind_param('i', $idPessoa);
$stmtFetch->execute();
$dadosCurriculo = $stmtFetch->get_result()->fetch_assoc();
$stmtFetch->close();
$conn->close();

$cNomeSocial       = $dadosCurriculo['nome_social'] ?? '';
$cGrauEscolaridade = $dadosCurriculo['grau_de_escolaridade'] ?? '';
$cCursos           = $dadosCurriculo['cursos'] ?? '';
$cExperiencia      = $dadosCurriculo['experiencia'] ?? '';
$cIdiomas          = $dadosCurriculo['idiomas'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevIN | Preencher Currículo</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link rel="icon" type="image/png" href="../img/favicon.png">
    <link rel="stylesheet" href="../css/curriculo.css">
    <link rel="stylesheet" href="../css/cadastrostyle.css">
</head>
<body>

<div class="main-container">
    <div class="left-side">
        <div class="brand-logo">
            <a href="index.php">Dev<span>IN</span></a>
        </div>

        <?php if ($mensagemSucesso): ?>
            <div class="php-toast success-toast"><?= htmlspecialchars($mensagemSucesso) ?></div>
        <?php endif; ?>

        <?php if ($mensagemErro): ?>
            <div class="php-toast error-toast"><?= htmlspecialchars($mensagemErro) ?></div>
        <?php endif; ?>

        <form method="POST" action="cadastrar_curriculo.php" class="register-form">
            <h2 class="title-curriculo">Preenchimento de Currículo</h2>

            <div class="form-columns">
                <div class="form-column">
                    <div class="input-group">
                        <label for="nome_social">Nome Social / Como prefere ser chamado(a):</label>
                        <input type="text" id="nome_social" name="nome_social" placeholder="Ex: Alex Silva" value="<?= htmlspecialchars($cNomeSocial) ?>" required>
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
                        <input type="text" id="idiomas" name="idiomas" placeholder="Ex: Português Nativo, Inglês Intermediário" value="<?= htmlspecialchars($cIdiomas) ?>">
                    </div>
                </div>

                <div class="form-column">
                    <div class="input-group">
                        <label for="cursos">Cursos e Certificações:</label>
                        <textarea id="cursos" name="cursos" placeholder="Ex: Curso de PHP Avançado, HTML5/CSS3, MySQL"><?= htmlspecialchars($cCursos) ?></textarea>
                    </div>

                    <div class="input-group">
                        <label for="experiencia">Experiência Profissional:</label>
                        <textarea id="experiencia" name="experiencia" placeholder="Ex: Desenvolvedor Web na Empresa X (2022 - Atual)"><?= htmlspecialchars($cExperiencia) ?></textarea>
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
            <img src="img/mascote.png" alt="Mascote DevIN" class="mascot-img">
        </div>
    </div>
</div>

</body>
</html>
