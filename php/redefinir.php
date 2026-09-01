<?php
require_once __DIR__ . '/config/security.php';
startSecureSession();
require_once __DIR__ . '/config/database.php';

$token = trim($_GET['token'] ?? '');
$tokenValido = false;

if ($token !== '') {
    try {
        $conn = getDatabaseConnection();

        $stmtPessoa = $conn->prepare('SELECT id_pessoa FROM pessoa WHERE token_recuperacao = ? AND token_expiracao > NOW() LIMIT 1');
        $stmtPessoa->bind_param('s', $token);
        $stmtPessoa->execute();
        $resultadoPessoa = $stmtPessoa->get_result();
        $tokenValido = $resultadoPessoa && $resultadoPessoa->num_rows > 0;
        $stmtPessoa->close();

        if (!$tokenValido) {
            $stmtEmpresa = $conn->prepare('SELECT id_empresa FROM empresa WHERE token_recuperacao = ? AND token_expiracao > NOW() LIMIT 1');
            $stmtEmpresa->bind_param('s', $token);
            $stmtEmpresa->execute();
            $resultadoEmpresa = $stmtEmpresa->get_result();
            $tokenValido = $resultadoEmpresa && $resultadoEmpresa->num_rows > 0;
            $stmtEmpresa->close();
        }

        $conn->close();
    } catch (Throwable $e) {
        error_log('Erro ao validar token de recuperação: ' . $e->getMessage());
        $tokenValido = false;
    }
}

$mensagemErro = $_SESSION['erro_redefinir'] ?? '';
unset($_SESSION['erro_redefinir']);
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
        <section class="card card-reset" aria-labelledby="reset-title">
            <h1 id="reset-title">Recuperação de senha</h1>

            <?php if (!$tokenValido): ?>
                <div class="alert alert-error" role="alert">Este link de redefinição é inválido ou já expirou.</div>
                <a href="recuperacao.php" class="btn-submit btn-link">Solicitar novo link</a>
            <?php else: ?>
                <?php if ($mensagemErro): ?>
                    <div class="alert alert-error" role="alert"><?= htmlspecialchars($mensagemErro, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <form action="processar.php" method="POST" id="formRedefinir">
                    <input type="hidden" name="acao" value="redefinir_senha">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">

                    <div class="form-group">
                        <label for="nova_senha">Senha:</label>
                        <div class="password-field">
                            <input type="password" id="nova_senha" name="nova_senha" placeholder="••••••••" autocomplete="new-password" required minlength="8">
                            <button type="button" class="password-toggle" data-password-toggle="nova_senha" aria-label="Mostrar senha">
                                <img src="../img/olho_fechado.png" alt="Mostrar senha">
                            </button>
                        </div>

                        <div class="password-requirements" aria-live="polite">
                            <div class="req-item req-invalid" id="req-length"><span class="req-icon">ⓘ</span><span>No mínimo 8 caracteres</span></div>
                            <div class="req-item req-invalid" id="req-upper"><span class="req-icon">ⓘ</span><span>Pelo menos 1 letra maiúscula (A-Z)</span></div>
                            <div class="req-item req-invalid" id="req-special"><span class="req-icon">ⓘ</span><span>Pelo menos 1 caractere especial (como ! @ # $)</span></div>
                        </div>
                    </div>

                    <div class="form-group confirm-group">
                        <label for="confirmar_senha">Confirmar Senha:</label>
                        <div class="password-field">
                            <input type="password" id="confirmar_senha" name="confirmar_senha" placeholder="••••••••" autocomplete="new-password" required minlength="8">
                            <button type="button" class="password-toggle" data-password-toggle="confirmar_senha" aria-label="Mostrar senha">
                                <img src="../img/olho_fechado.png" alt="Mostrar senha">
                            </button>
                        </div>
                        <p class="match-error" id="match-error">As senhas não coincidem.</p>
                    </div>

                    <button type="submit" class="btn-submit">Cadastrar</button>
                </form>
            <?php endif; ?>
        </section>
    </main>

    <footer class="recovery-footer">
        Dev<span>IN</span> | Escola Profª Alcina Dantas Feijão | © DevIN 2026. Todos os direitos reservados.
    </footer>

    <script src="../js/recuperacao.js"></script>
</body>
</html>
