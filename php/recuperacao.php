<?php
require_once __DIR__ . '/config/security.php';
startSecureSession();

$mensagemSucesso = $_SESSION['sucesso_recuperacao'] ?? '';
$mensagemErro = $_SESSION['erro_recuperacao'] ?? '';
unset($_SESSION['sucesso_recuperacao'], $_SESSION['erro_recuperacao']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperação de senha | DevIN</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link rel="icon" type="image/png" href="../img/favicon.png">
    <link rel="stylesheet" href="../css/recuperacao.css">
</head>
<body>
    <main class="recovery-page">
        <section class="card card-recovery" aria-labelledby="recovery-title">
            <h1 id="recovery-title">Recuperação de senha</h1>

            <div class="recovery-logo" aria-label="DevIN">Dev<span>IN</span></div>

            <?php if ($mensagemSucesso): ?>
                <div class="alert alert-success" role="status"><?= htmlspecialchars($mensagemSucesso, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <?php if ($mensagemErro): ?>
                <div class="alert alert-error" role="alert"><?= htmlspecialchars($mensagemErro, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form action="processar.php" method="POST" id="formRecuperacao">
                <input type="hidden" name="acao" value="solicitar_recuperacao">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">

                <div class="form-group">
                    <label for="email">Email:</label>
                    <div class="input-with-icon">
                        <span class="email-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="5" width="18" height="14" rx="1.5"></rect>
                                <path d="m3 7 9 7 9-7"></path>
                            </svg>
                        </span>
                        <input type="email" id="email" name="email" placeholder="Informe seu email..." autocomplete="email" required>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Enviar</button>
            </form>
        </section>
    </main>

    <footer class="recovery-footer">
        Dev<span>IN</span> | Escola Profª Alcina Dantas Feijão | © DevIN 2026. Todos os direitos reservados.
    </footer>

    <script src="../js/recuperacao.js"></script>
</body>
</html>
