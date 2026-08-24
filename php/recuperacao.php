<?php
// recuperacao.php
session_start();

$mensagemSucesso = $_SESSION['sucesso_recuperacao'] ?? '';
$mensagemErro    = $_SESSION['erro_recuperacao'] ?? '';

unset($_SESSION['sucesso_recuperacao'], $_SESSION['erro_recuperacao']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperação de Senha - DevIN</title>
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body>
<div class="card">
    <h1>Recuperação de Senha</h1>
    <p class="subtitle">Digite seu e-mail cadastrado e enviaremos um link seguro para a redefinição.</p>

    <?php if ($mensagemSucesso): ?>
        <div class="alert-success"><?= htmlspecialchars($mensagemSucesso) ?></div>
    <?php endif; ?>

    <?php if ($mensagemErro): ?>
        <div class="alert-error"><?= htmlspecialchars($mensagemErro) ?></div>
    <?php endif; ?>

    <form action="processar.php" method="POST">
        <input type="hidden" name="acao" value="solicitar_recuperacao">

        <div class="form-group">
            <label for="email">E-mail Cadastrado:</label>
            <input type="email" id="email" name="email" placeholder="seuemail@exemplo.com" required>
        </div>

        <button type="submit" class="btn-submit">Enviar Link de Recuperação</button>
    </form>

    <a href="login.php" class="back-link">← Voltar para o Login</a>
</div>
</body>
</html>