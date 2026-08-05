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
    <title>Recuperar Senha - DevIN</title>
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background-color: #f0f2f5; margin: 0; padding: 40px 20px; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .card { background: #ffffff; width: 100%; max-width: 450px; padding: 35px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h1 { margin-top: 0; font-size: 24px; color: #1a1a1a; text-align: center; }
        p.subtitle { text-align: center; color: #666; font-size: 14px; margin-bottom: 25px; }
        .alert-success { background-color: #d4edda; color: #155724; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; text-align: center; }
        .alert-error { background-color: #f8d7da; color: #721c24; padding: 12px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; text-align: center; }
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: 600; margin-bottom: 8px; color: #444; }
        input[type="email"] { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 15px; }
        .btn-submit { width: 100%; background-color: #2b56f5; color: #ffffff; border: none; padding: 12px; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; transition: background 0.2s; }
        .btn-submit:hover { background-color: #1e3ec7; }
        .back-link { display: block; text-align: center; margin-top: 20px; color: #2b56f5; text-decoration: none; font-size: 14px; font-weight: 500; }
        .back-link:hover { text-decoration: underline; }
    </style>
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