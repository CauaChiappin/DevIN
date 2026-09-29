<?php

declare(strict_types=1);

require_once __DIR__ . '/config/security.php';
startSecureSession();

if (empty($_SESSION['recuperacao_email'])) {
    header('Location: recuperacao.php');
    exit;
}

$erro =$_SESSION['erro_codigo'] ?? '';
$sucesso =$_SESSION['sucesso_codigo'] ?? '';
unset($_SESSION['erro_codigo'],$_SESSION['sucesso_codigo']);

$ultimoEnvio =$_SESSION['ultimo_envio_codigo'] ?? 0;
$tempoPassado = time() -$ultimoEnvio;
$cooldownRestante = max(0, 60 -$tempoPassado);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevIN | Inserir Código</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link rel="stylesheet" href="../css/recuperacao.css">
</head>
<body>
    <main class="recovery-wrapper">
        <section class="card-box" aria-labelledby="code-title">
            <a class="brand-link" href="../index.php">Dev<span>IN</span></a>
            <h1 id="code-title">Recuperação de senha</h1>
            <p class="subtitle">insira o código:</p>

            <?php if ($erro): ?>
                <div class="alert alert-error"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>
            <?php if ($sucesso): ?>
                <div class="alert alert-success"><?= htmlspecialchars($sucesso, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form action="processar.php" method="POST" id="formCodigo">
                <input type="hidden" name="acao" value="validar_codigo">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">

                <div class="otp-container">
                    <input type="text" id="codigo" name="codigo" maxlength="7" placeholder="000-000" required autocomplete="off" autofocus class="input-code-mask">
                </div>

                <div class="resend-block">
                    <span>Não recebeu o código? </span>
                    <button type="button" id="btnReenviar" class="link-resend" <?= $cooldownRestante > 0 ? 'disabled' : '' ?>>
                        reenviar código <?= $cooldownRestante > 0 ? "({$cooldownRestante}s)" : '' ?>
                    </button>
                </div>

                <div class="btn-group">
                    <a href="recuperacao.php" class="btn btn-secondary">Voltar</a>
                    <button type="submit" class="btn btn-primary">entrar</button>
                </div>
            </form>

            <form action="processar.php" method="POST" id="formReenviar">
                <input type="hidden" name="acao" value="reenviar_codigo">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
            </form>
        </section>
    </main>
    <footer class="recovery-footer">
        Dev<span>IN</span> | Escola Profª Alcina Dantas Feijão | DevIN 2026. Todos os direitos reservados.
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const inputCodigo = document.getElementById('codigo');
            const btnReenviar = document.getElementById('btnReenviar');
            const formReenviar = document.getElementById('formReenviar');
            let cooldown = <?= $cooldownRestante ?>;

            // Formata a máscara 000-000 e força letras maiúsculas
            inputCodigo.addEventListener('input', (e) => {
                let val = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
                if (val.length > 3) {
                    val = val.substring(0, 3) + '-' + val.substring(3, 6);
                }
                e.target.value = val;
            });

            // Timer de 60 segundos do reenviar
            if (cooldown > 0 && btnReenviar) {
                const interval = setInterval(() => {
                    cooldown--;
                    if (cooldown <= 0) {
                        clearInterval(interval);
                        btnReenviar.removeAttribute('disabled');
                        btnReenviar.textContent = 'reenviar código';
                    } else {
                        btnReenviar.textContent = `reenviar código (${cooldown}s)`;
                    }
                }, 1000);
            }

            if (btnReenviar) {
                btnReenviar.addEventListener('click', () => {
                    if (!btnReenviar.hasAttribute('disabled')) {
                        formReenviar.submit();
                    }
                });
            }
        });
    </script>
</body>
</html>