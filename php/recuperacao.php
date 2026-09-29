<?php

declare(strict_types=1);

require_once __DIR__ . '/config/security.php';
startSecureSession();

$erro = $_SESSION['erro_recuperacao'] ?? '';
unset($_SESSION['erro_recuperacao']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevIN | Recuperação de Senha</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link rel="stylesheet" href="../css/recuperacao.css">
</head>
<body>
    <main class="recovery-wrapper">
        <section class="card-box" aria-labelledby="rec-title">
            <a class="brand-link" href="../index.php">Dev<span>IN</span></a>
            <h1 id="rec-title">Recuperação de senha</h1>

            <?php if ($erro): ?>
                <div class="alert alert-error"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form action="processar.php" method="POST" id="formRecuperacao">
                <input type="hidden" name="acao" value="solicitar_recuperacao">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">

                <div class="input-block">
                    <label for="email">Email:</label>
                    <div class="input-icon-field">
              
                        <input type="email" id="email" name="email" required placeholder="✉ Informe seu email..." autocomplete="email">
                    </div>
                </div>

                <div class="btn-group">
                    <a href="login.php" class="btn btn-secondary">Voltar</a>
                    <button type="submit" class="btn btn-primary" id="btnEnviar">Enviar</button>
                </div>
            </form>
        </section>
    </main>

    <footer class="recovery-footer">
        Dev<span>IN</span> | Escola Profª Alcina Dantas Feijão | DevIN 2026. Todos os direitos reservados.
    </footer>

    <script src="../js/recuperacao.js"></script>
</body>
</html>