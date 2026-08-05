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
    <title>Criar Nova Senha - DevIN</title>
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f0f2f5; margin: 0; padding: 40px 20px; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .card { background: #ffffff; width: 100%; max-width: 450px; padding: 35px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h1 { margin-top: 0; font-size: 24px; color: #1a1a1a; text-align: center; }
        p.subtitle { text-align: center; color: #666; font-size: 14px; margin-bottom: 25px; }
        .alert-error { background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; text-align: center; }
        .form-group { margin-bottom: 18px; }
        label { display: block; font-weight: 600; margin-bottom: 8px; color: #444; }
        input[type="password"] { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 15px; }
        .btn-submit { width: 100%; background-color: #2b56f5; color: #ffffff; border: none; padding: 12px; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; transition: background 0.2s; }
        .btn-submit:hover { background-color: #1e3ec7; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #2b56f5; text-decoration: none; font-size: 14px; }
    </style>
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