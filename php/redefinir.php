<?php

declare(strict_types=1);

require_once __DIR__ . '/config/security.php';
startSecureSession();

<<<<<<< HEAD
$emailRecuperacao = requestString($_SESSION, 'email_recuperacao');
$mensagemSucesso = requestString($_SESSION, 'sucesso_recuperacao');
$mensagemErro = requestString($_SESSION, 'erro_redefinir');
unset($_SESSION['sucesso_recuperacao'], $_SESSION['erro_redefinir']);
=======
if (empty($_SESSION['recuperacao_verificada'])) {
    header('Location: recuperacao.php');
    exit;
}

$erro = $_SESSION['erro_redefinir'] ?? '';
unset($_SESSION['erro_redefinir']);
>>>>>>> bb0413abcd2a8c17f9c53b600f5a5acb10a41c97
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
            <h1 id="reset-title" class="title-page">Recuperação de senha</h1>

<<<<<<< HEAD
            <?php if ($mensagemSucesso): ?>
                <div class="alert alert-success" role="status"><?= htmlspecialchars($mensagemSucesso, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <?php if ($mensagemErro): ?>
                <div class="alert alert-error" role="alert"><?= htmlspecialchars($mensagemErro, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <?php if ($emailRecuperacao === ''): ?>
                <div class="alert alert-error" role="alert">Solicite primeiro um código de recuperação.</div>
                <a href="recuperacao.php" class="btn-submit btn-link">Solicitar código</a>
            <?php else: ?>
                <form action="processar.php" method="POST" id="formRedefinir">
                    <input type="hidden" name="acao" value="redefinir_senha">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($emailRecuperacao, ENT_QUOTES, 'UTF-8') ?>">

                    <div class="form-group">
                        <label for="codigo">Código enviado por e-mail:</label>
                        <input type="text" id="codigo" name="codigo" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9 ]{8,9}" maxlength="9" required>
                    </div>

                    <div class="form-group">
                        <label for="nova_senha">Senha:</label>
                        <div class="password-field">
                            <input type="password" id="nova_senha" name="nova_senha" autocomplete="new-password" required minlength="12">
                            <button type="button" class="password-toggle" data-password-toggle="nova_senha" aria-label="Mostrar senha">
                                <img src="../img/olho_fechado.png" alt="Mostrar senha">
                            </button>
                        </div>
                        <div class="password-requirements" aria-live="polite">
                            <div class="req-item req-invalid" id="req-length"><span class="req-icon">ⓘ</span><span>No mínimo 12 caracteres</span></div>
                            <div class="req-item req-invalid" id="req-upper"><span class="req-icon">ⓘ</span><span>Pelo menos 1 letra maiúscula (A-Z)</span></div>
                            <div class="req-item req-invalid" id="req-special"><span class="req-icon">ⓘ</span><span>Pelo menos 1 caractere especial (como ! @ # $)</span></div>
                        </div>
                    </div>

                    <div class="form-group confirm-group">
                        <label for="confirmar_senha">Confirmar senha:</label>
                        <div class="password-field">
                            <input type="password" id="confirmar_senha" name="confirmar_senha" autocomplete="new-password" required minlength="12">
                            <button type="button" class="password-toggle" data-password-toggle="confirmar_senha" aria-label="Mostrar senha">
                                <img src="../img/olho_fechado.png" alt="Mostrar senha">
                            </button>
                        </div>
                        <p class="match-error" id="match-error">As senhas não coincidem.</p>
                    </div>

                    <button type="submit" class="btn-submit">Redefinir senha</button>
                </form>
=======
            <?php if ($erro): ?>
                <div class="alert alert-error"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
>>>>>>> bb0413abcd2a8c17f9c53b600f5a5acb10a41c97
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
                    <button type="submit" class="btn btn-primary btn-cadastrar" id="btnCadastrar">Cadastrar</button>
                </div>
            </form>
        </section>
    </main>

    <footer class="recovery-footer">
        Dev<span>IN</span> | Escola Profª Alcina Dantas Feijão | DevIN 2026. Todos os direitos reservados.
    </footer>

 <footer class="recovery-footer">
        Dev<span>IN</span> | Escola Profª Alcina Dantas Feijão | DevIN 2026. Todos os direitos reservados.
    </footer>

    <script src="../js/redefinir.js"></script>
</body>
</html>
</body>
</html>
