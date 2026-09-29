<?php

declare(strict_types=1);

require_once __DIR__ . '/config/security.php';
startSecureSession();

if (empty($_SESSION['recuperacao_verificada'])) {
    header('Location: recuperacao.php');
    exit;
}

$erro = $_SESSION['erro_redefinir'] ?? '';
unset($_SESSION['erro_redefinir']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevIN | Redefinir Senha</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link rel="stylesheet" href="../css/recuperacao.css">
</head>
<body>
    <main class="recovery-wrapper">
        <section class="card-box" aria-labelledby="reset-title">
            <a class="brand-link" href="../index.php">Dev<span>IN</span></a>
            <h1 id="reset-title" class="sr-only">Recuperação de senha</h1>

            <?php if ($erro): ?>
                <div class="alert alert-error"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form action="processar.php" method="POST" id="formReset">
                <input type="hidden" name="acao" value="redefinir_senha">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">

                <div class="input-block">
                    <label for="nova_senha">Senha:</label>
                    <div class="password-wrapper">
                        <input type="password" id="nova_senha" name="nova_senha" required autocomplete="new-password">
                        <button type="button" class="eye-toggle" data-toggle="nova_senha" aria-label="Ver senha">
                            <span class="eye-icon">👁</span>
                        </button>
                    </div>

                    <div class="checklist">
                        <div class="check-item check-invalid" id="req-len">
                            <span class="icon">ⓘ</span> No mínimo 8 caracteres
                        </div>
                        <div class="check-item check-invalid" id="req-upper">
                            <span class="icon">ⓘ</span> Pelo menos 1 letra maiúscula (A-Z)
                        </div>
                        <div class="check-item check-invalid" id="req-special">
                            <span class="icon">ⓘ</span> Pelo menos 1 caracter especial (como ! @ # $)
                        </div>
                    </div>
                </div>

                <div class="input-block">
                    <label for="confirmar_senha">Confirmar Senha:</label>
                    <div class="password-wrapper">
                        <input type="password" id="confirmar_senha" name="confirmar_senha" required autocomplete="new-password">
                        <button type="button" class="eye-toggle" data-toggle="confirmar_senha" aria-label="Ver senha">
                            <span class="eye-icon">👁</span>
                        </button>
                    </div>
                </div>

                <div class="btn-single-group">
                    <button type="submit" class="btn btn-primary btn-full">Cadastrar</button>
                </div>
            </form>
        </section>
    </main>
    <footer class="recovery-footer">
        Dev<span>IN</span> | Escola Profª Alcina Dantas Feijão | DevIN 2026. Todos os direitos reservados.
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const novaSenha = document.getElementById('nova_senha');
            const reqLen = document.getElementById('req-len');
            const reqUpper = document.getElementById('req-upper');
            const reqSpecial = document.getElementById('req-special');

            novaSenha.addEventListener('input', () => {
                const val = novaSenha.value;

                // Mínimo 8 caracteres
                if (val.length >= 8) {
                    reqLen.className = 'check-item check-valid';
                    reqLen.querySelector('.icon').textContent = '✓';
                } else {
                    reqLen.className = 'check-item check-invalid';
                    reqLen.querySelector('.icon').textContent = 'ⓘ';
                }

                // Maiúscula
                if (/[A-Z]/.test(val)) {
                    reqUpper.className = 'check-item check-valid';
                    reqUpper.querySelector('.icon').textContent = '✓';
                } else {
                    reqUpper.className = 'check-item check-invalid';
                    reqUpper.querySelector('.icon').textContent = 'ⓘ';
                }

                // Especial
                if (/[^a-zA-Z0-9]/.test(val)) {
                    reqSpecial.className = 'check-item check-valid';
                    reqSpecial.querySelector('.icon').textContent = '✓';
                } else {
                    reqSpecial.className = 'check-item check-invalid';
                    reqSpecial.querySelector('.icon').textContent = 'ⓘ';
                }
            });

            // Mostrar / Ocultar Senha
            document.querySelectorAll('[data-toggle]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const targetId = btn.getAttribute('data-toggle');
                    const input = document.getElementById(targetId);
                    if (input) {
                        const isPass = input.type === 'password';
                        input.type = isPass ? 'text' : 'password';
                        btn.classList.toggle('active', isPass);
                    }
                });
            });
        });
    </script>
</body>
</html>