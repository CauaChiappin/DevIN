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
            <a class="brand-link" href="index.php">Dev<span>IN</span></a>
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
                <input type="hidden" name="codigo" id="codigo-final">

           <div class="code-container">
    <input type="text" id="code-1" name="digit_1" class="code-input" maxlength="1" pattern="[a-zA-Z0-9]" required autocomplete="off">
    <input type="text" id="code-2" name="digit_2" class="code-input" maxlength="1" pattern="[a-zA-Z0-9]" required autocomplete="off">
    <input type="text" id="code-3" name="digit_3" class="code-input" maxlength="1" pattern="[a-zA-Z0-9]" required autocomplete="off">
    <span class="code-separator">-</span>
    <input type="text" id="code-4" name="digit_4" class="code-input" maxlength="1" pattern="[a-zA-Z0-9]" required autocomplete="off">
    <input type="text" id="code-5" name="digit_5" class="code-input" maxlength="1" pattern="[a-zA-Z0-9]" required autocomplete="off">
    <input type="text" id="code-6" name="digit_6" class="code-input" maxlength="1" pattern="[a-zA-Z0-9]" required autocomplete="off">
</div>
                <div class="resend-block">
                    <span>Não recebeu o código? </span>
                    <button type="button" id="btnReenviar" class="link-resend" <?= $cooldownRestante > 0 ? 'disabled' : '' ?>>
                        reenviar código <?= $cooldownRestante > 0 ? "({$cooldownRestante}s)" : '' ?>
                    </button>
                </div>

                <div class="btn-group">
                    <a href="recuperacao.php" class="btn btn-secondary">Voltar</a>
                    <button type="submit" class="btn btn-primary" id="btnEntrar">entrar</button>
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
            const inputs = document.querySelectorAll('.code-input');
            const hiddenInput = document.getElementById('codigo-final');
            const btnReenviar = document.getElementById('btnReenviar');
            const formReenviar = document.getElementById('formReenviar');
            const formCodigo = document.getElementById('formCodigo');
            const btnEntrar = document.getElementById('btnEntrar');
            let cooldown = <?= $cooldownRestante ?>;

            function updateHiddenInput() {
                let fullCode = '';
                inputs.forEach(input => fullCode += input.value.toUpperCase());
                hiddenInput.value = fullCode;
            }

            inputs.forEach((input, index) => {
                input.addEventListener('input', (e) => {
                    e.target.value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
                    if (e.target.value.length === 1 && index < inputs.length - 1) {
                        inputs[index + 1].focus();
                    }
                    updateHiddenInput();
                });

                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Backspace' && !e.target.value && index > 0) {
                        inputs[index - 1].focus();
                    }
                });

                input.addEventListener('paste', (e) => {
                    e.preventDefault();
                    const pasteData = (e.clipboardData || window.clipboardData).getData('text').trim().toUpperCase().replace(/[^A-Z0-9]/g, '');
                    if (pasteData.length === 6) {
                        pasteData.split('').forEach((char, i) => {
                            if (inputs[i]) inputs[i].value = char;
                        });
                        inputs[inputs.length - 1].focus();
                        updateHiddenInput();
                    }
                });
            });

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
                        btnReenviar.setAttribute('disabled', 'true');
                        formReenviar.submit();
                    }
                });
            }

            if (formCodigo && btnEntrar) {
                formCodigo.addEventListener('submit', (e) => {
                    updateHiddenInput();
                    if (hiddenInput.value.length !== 6) {
                        e.preventDefault();
                        alert('Preencha os 6 caracteres do código.');
                        return;
                    }
                    btnEntrar.disabled = true;
                    btnEntrar.textContent = 'validando...';
                });
            }
        });
    </script>
</body>
</html>
