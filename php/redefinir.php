<?php
// redefinir.php
session_start();

require_once __DIR__ . '/php/config/database.php';

$token = trim($_GET['token'] ?? '');
$tokenValido = false;

if (!empty($token)) {
    $conn = getDatabaseConnection();

    // Valida token e expiração na tabela pessoa
    $stmtCheck = $conn->prepare("
        SELECT id_pessoa FROM pessoa WHERE token_recuperacao = ? AND token_expiracao > NOW()
        UNION
        SELECT id_empresa FROM empresa WHERE token_recuperacao = ? AND token_expiracao > NOW()
    ");
    $stmtCheck->bind_param('ss', $token, $token);
    $stmtCheck->execute();
    
    if ($stmtCheck->get_result()->num_rows > 0) {
        $tokenValido = true;
    }
    $stmtCheck->close();
    $conn->close();
}

$mensagemErro = $_SESSION['erro_redefinir'] ?? '';
unset($_SESSION['erro_redefinir']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Senha - DevIN</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link rel="icon" type="image/png" href="../img/favicon.png">
    <link rel="stylesheet" href="../css/estilo.css">
</head>
<body>
<div class="card">
    <h1>Criar Nova Senha</h1>

    <?php if (!$tokenValido): ?>
        <div class="alert-error">Este link de redefinição é inválido ou já expirou.</div>
        <a href="recuperacao.php" class="btn-submit" style="display:block; text-align:center; text-decoration:none;">Solicitar Novo Link</a>
    <?php else: ?>
        <p class="subtitle">Digite sua nova senha abaixo para atualizar sua conta.</p>

        <?php if ($mensagemErro): ?>
            <div class="alert-error"><?= htmlspecialchars($mensagemErro) ?></div>
        <?php endif; ?>

        <form action="processar.php" method="POST">
            <input type="hidden" name="acao" value="redefinir_senha">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

            <div class="form-group">
                <label for="nova_senha">Nova Senha:</label>
                <input type="password" id="nova_senha" name="nova_senha" placeholder="••••••••" required minlength="6">
            </div>

            <div class="form-group">
                <label for="confirmar_senha">Confirme a Nova Senha:</label>
                <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="••••••••" required minlength="6">
            </div>

            <button type="submit" class="btn-submit">Atualizar Senha</button>
        </form>
    <?php endif; ?>

    <a href="login.php" class="back-link">← Voltar para o Login</a>
</div>
</body>
</html>
