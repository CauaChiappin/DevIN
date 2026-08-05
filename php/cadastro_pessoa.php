<?php
// cadastro_pessoa.php

session_start();
require_once __DIR__ . '/php/config/database.php';

$mensagemErro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome     = trim($_POST['nome'] ?? '');
    $cpf      = trim($_POST['cpf'] ?? '');
    $cep      = trim($_POST['cep'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $senha    = $_POST['senha'] ?? '';
    $telefone = trim($_POST['telefone'] ?? '');

    if (!empty($nome) && !empty($cpf) && !empty($email) && !empty($senha)) {
        $conn = getDatabaseConnection();

        // Verifica se e-mail ou CPF já existem
        $stmtCheck = $conn->prepare("SELECT id_pessoa FROM pessoa WHERE email = ? OR cpf = ?");
        $stmtCheck->bind_param('ss', $email, $cpf);
        $stmtCheck->execute();
        
        if ($stmtCheck->get_result()->num_rows > 0) {
            $mensagemErro = "E-mail ou CPF já cadastrado no sistema.";
        } else {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

            // Grava com created_at = NOW() e lembrete_enviado = 0
            $stmtInsert = $conn->prepare("
                INSERT INTO pessoa (nome, cpf, cep, email, senha_hash, telefone, created_at, lembrete_enviado)
                VALUES (?, ?, ?, ?, ?, ?, NOW(), 0)
            ");
            $stmtInsert->bind_param('ssssss', $nome, $cpf, $cep, $email, $senhaHash, $telefone);

            if ($stmtInsert->execute()) {
                $novoId = $stmtInsert->insert_id;

                $_SESSION['id_pessoa']    = $novoId;
                $_SESSION['nome_pessoa']  = $nome;
                $_SESSION['email_pessoa'] = $email;

                // Redireciona imediatamente para o preenchimento do currículo
                header('Location: cadastrar_curriculo.php');
                exit;
            } else {
                $mensagemErro = "Erro ao cadastrar. Tente novamente.";
            }
            $stmtInsert->close();
        }
        $stmtCheck->close();
        $conn->close();
    } else {
        $mensagemErro = "Por favor, preencha todos os campos obrigatórios.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Pessoa Física | DevIN</title>
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f0f2f5; margin: 0; padding: 40px 20px; display: flex; justify-content: center; }
        .container { background: #fff; width: 100%; max-width: 500px; border-radius: 10px; padding: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h1 { margin-top: 0; color: #1a1a1a; font-size: 24px; text-align: center; }
        .alert-error { background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 20px; text-align: center; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: 600; margin-bottom: 5px; color: #444; }
        input { width: 100%; padding: 10px 14px; border: 1px solid #ccc; border-radius: 6px; font-size: 15px; }
        .btn-submit { width: 100%; background-color: #2b56f5; color: white; border: none; padding: 12px; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; margin-top: 10px; }
        .btn-submit:hover { background-color: #1e3ec7; }
    </style>
</head>
<body>
<div class="container">
    <h1>Criar Conta (Pessoa Física)</h1>

    <?php if ($mensagemErro): ?>
        <div class="alert-error"><?= htmlspecialchars($mensagemErro) ?></div>
    <?php endif; ?>

    <form method="POST" action="cadastro_pessoa.php">
        <div class="form-group">
            <label for="nome">Nome Completo:</label>
            <input type="text" id="nome" name="nome" required>
        </div>

        <div class="form-group">
            <label for="cpf">CPF:</label>
            <input type="text" id="cpf" name="cpf" maxlength="14" placeholder="000.000.000-00" required>
        </div>

        <div class="form-group">
            <label for="cep">CEP:</label>
            <input type="text" id="cep" name="cep" maxlength="9" placeholder="00000-000">
        </div>

        <div class="form-group">
            <label for="telefone">Telefone:</label>
            <input type="text" id="telefone" name="telefone" placeholder="(00) 00000-0000">
        </div>

        <div class="form-group">
            <label for="email">E-mail:</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" required>
        </div>

        <button type="submit" class="btn-submit">Avançar para o Currículo</button>
    </form>
</div>
</body>
</html>