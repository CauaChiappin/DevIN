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

$idPessoa   = (int) $_SESSION['id_pessoa'];
$nomePessoa = $_SESSION['nome_pessoa'] ?? 'Candidato';
$emailPessoa= $_SESSION['email_pessoa'] ?? '';

$mensagemSucesso = '';
$mensagemErro    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomeSocial         = trim($_POST['nome_social'] ?? '');
    $grauEscolaridade   = trim($_POST['grau_de_escolaridade'] ?? '');
    $cursos             = trim($_POST['cursos'] ?? '');
    $experiencia        = trim($_POST['experiencia'] ?? '');
    $idiomas            = trim($_POST['idiomas'] ?? '');

    $conn = getDatabaseConnection();

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

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Currículo - DevIN</title>
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f0f2f5; margin: 0; padding: 40px 20px; display: flex; justify-content: center; }
        .container { background: #fff; width: 100%; max-width: 650px; border-radius: 10px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h1 { margin-top: 0; color: #1a1a1a; font-size: 24px; text-align: center; }
        .alert-success { background-color: #d4edda; color: #155724; padding: 12px; border-radius: 6px; margin-bottom: 20px; text-align: center; }
        .alert-error { background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 20px; text-align: center; }
        .form-group { margin-bottom: 18px; }
        label { display: block; font-weight: 600; margin-bottom: 6px; color: #444; }
        input[type="text"], select, textarea { width: 100%; padding: 10px 14px; border: 1px solid #ccc; border-radius: 6px; font-size: 15px; }
        textarea { resize: vertical; min-height: 90px; }
        .btn-submit { width: 100%; background-color: #2b56f5; color: white; border: none; padding: 12px; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; transition: background 0.2s; }
        .btn-submit:hover { background-color: #1e3ec7; }
    </style>
</head>
<body>
<div class="container">
    <h1>Preenchimento de Currículo</h1>

    <?php if ($mensagemSucesso): ?>
        <div class="alert-success"><?= htmlspecialchars($mensagemSucesso) ?></div>
    <?php endif; ?>

    <?php if ($mensagemErro): ?>
        <div class="alert-error"><?= htmlspecialchars($mensagemErro) ?></div>
    <?php endif; ?>

    <form method="POST" action="cadastrar_curriculo.php">
        <div class="form-group">
            <label for="nome_social">Nome Social / Como prefere ser chamado(a):</label>
            <input type="text" id="nome_social" name="nome_social" placeholder="Ex: Alex Silva" required>
        </div>

        <div class="form-group">
            <label for="grau_de_escolaridade">Grau de Escolaridade:</label>
            <select id="grau_de_escolaridade" name="grau_de_escolaridade" required>
                <option value="">Selecione...</option>
                <option value="Ensino Médio Incompleto">Ensino Médio Incompleto</option>
                <option value="Ensino Médio Completo">Ensino Médio Completo</option>
                <option value="Ensino Superior Incompleto">Ensino Superior Incompleto</option>
                <option value="Ensino Superior Completo">Ensino Superior Completo</option>
                <option value="Pós-graduação / Mapeamento">Pós-graduação / Especialização</option>
            </select>
        </div>

        <div class="form-group">
            <label for="cursos">Cursos e Certificações:</label>
            <textarea id="cursos" name="cursos" placeholder="Ex: Curso de PHP Avançado, HTML5/CSS3, MySQL"></textarea>
        </div>

        <div class="form-group">
            <label for="experiencia">Experiência Profissional:</label>
            <textarea id="experiencia" name="experiencia" placeholder="Ex: Desenvolvedor Web na Empresa X (2022 - Atual)"></textarea>
        </div>

        <div class="form-group">
            <label for="idiomas">Idiomas:</label>
            <input type="text" id="idiomas" name="idiomas" placeholder="Ex: Português Nativo, Inglês Intermediário">
        </div>

        <button type="submit" class="btn-submit">Finalizar e Salvar Currículo</button>
    </form>
</div>
</body>
</html>