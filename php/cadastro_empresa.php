<?php

declare(strict_types=1);

require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/security.php';

startSecureSession();

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrf();

    $nome = trim(requestString($_POST, 'nome'));
    $cnpj = preg_replace('/\D/', '', requestString($_POST, 'cnpj'));
    $cep = preg_replace('/\D/', '', requestString($_POST, 'cep'));
    $email = trim(requestString($_POST, 'email'));
    $telefone = preg_replace('/\D/', '', requestString($_POST, 'telefone'));
    $senha = requestString($_POST, 'senha');
    $confirmeSenha = requestString($_POST, 'confirme_senha');

    try {
        if ($nome === '') {
            throw new InvalidArgumentException('Informe o nome da empresa.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Informe um e-mail válido.');
        }

        if (strlen($cnpj) !== 14) {
            throw new InvalidArgumentException('Informe um CNPJ válido.');
        }

        if (strlen($cep) !== 8) {
            throw new InvalidArgumentException('Informe um CEP válido.');
        }

        if (strlen($telefone) < 10) {
            throw new InvalidArgumentException('Informe um telefone válido.');
        }

        if ($senha === '') {
            throw new InvalidArgumentException('Informe uma senha.');
        }

        if ($senha !== $confirmeSenha) {
            throw new InvalidArgumentException('As senhas não coincidem.');
        }

        if (strlen($senha) < 8) {
            throw new InvalidArgumentException('A senha deve ter no mínimo 8 caracteres.');
        }

        if (!preg_match('/[A-Z]/', $senha)) {
            throw new InvalidArgumentException('A senha deve possuir pelo menos uma letra maiúscula.');
        }

        if (!preg_match('/[^a-zA-Z0-9]/', $senha)) {
            throw new InvalidArgumentException('A senha deve possuir pelo menos um caractere especial.');
        }

        $conn = getDatabaseConnection();

        try {
            foreach (['pessoa', 'empresa', 'administrador'] as $tabelaEmail) {
                $stmtEmail = $conn->prepare(
                    "SELECT email FROM {$tabelaEmail} WHERE email = ? LIMIT 1"
                );
                $stmtEmail->bind_param('s', $email);
                $stmtEmail->execute();
                $existe = $stmtEmail->get_result()->num_rows > 0;
                $stmtEmail->close();

                if ($existe) {
                    throw new InvalidArgumentException(
                        'Este e-mail já está cadastrado. Use outro e-mail ou faça login.'
                    );
                }
            }

            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            if ($senhaHash === false) {
                throw new RuntimeException('Não foi possível proteger a senha.');
            }

            $stmt = $conn->prepare(
                'INSERT INTO empresa (nome, cnpj, cep, email, senha_hash, telefone)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->bind_param(
                'ssssss',
                $nome,
                $cnpj,
                $cep,
                $email,
                $senhaHash,
                $telefone
            );
            $stmt->execute();
            $stmt->close();
        } finally {
            $conn->close();
        }

        $auth = AuthController::login($email, $senha);
        AuthController::establishSession($auth);

        header('Location: ' . AuthController::redirectByUserType($auth['usuario']['tipo']));
        exit;
    } catch (mysqli_sql_exception $exception) {
        error_log('Erro MySQL no cadastro de empresa: ' . $exception->getMessage());
        $erro = $exception->getCode() === 1062
            ? 'Este CNPJ ou e-mail já está cadastrado.'
            : 'Não foi possível cadastrar a empresa.';
    } catch (Throwable $exception) {
        $erro = $exception->getMessage();
    }
}

$csrf = csrfToken();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevIN | Criar conta de empresa</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link rel="stylesheet" href="../css/cadastrostyle.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="main-container">
        <section class="left-side">
            <header class="cadastro-header">
                <a class="brand-logo" href="index.php">Dev<span>IN</span></a>
            </header>

            <div class="toggle-container">
                <a href="cadastro_pessoa.php" class="toggle-btn pessoal">Pessoal</a>
                <span class="toggle-divider">OU</span>
                <a href="cadastro_empresa.php" class="toggle-btn empresa active">Empresa</a>
            </div>

            <h1 class="page-title">Criar conta</h1>

            <?php if ($erro !== ''): ?>
                <div class="php-toast error-toast" role="alert"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form action="cadastro_empresa.php" method="POST" class="register-form" id="formCadastro">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">

                <div class="form-columns">
                    <div class="form-column">
                        <div class="input-group">
                            <label for="nome">Nome da empresa:*</label>
                            <input type="text" id="nome" name="nome" value="<?= htmlspecialchars(requestString($_POST, 'nome'), ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>

                        <div class="input-group">
                            <label for="cnpj">CNPJ:*</label>
                            <input type="text" id="cnpj" name="cnpj" placeholder="00.000.000/0000-00" maxlength="18" value="<?= htmlspecialchars(requestString($_POST, 'cnpj'), ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>

                        <div class="input-group">
                            <label for="cep">CEP:*</label>
                            <input type="text" id="cep" name="cep" placeholder="00000-000" maxlength="9" value="<?= htmlspecialchars(requestString($_POST, 'cep'), ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>

                        <div class="input-group password-wrapper">
                            <label for="confirme_senha">Confirme a sua senha:*</label>
                            <div class="input-icon-container">
                                <input type="password" id="confirme_senha" name="confirme_senha" required>
                                <img src="../img/olho_fechado.png" class="toggle-password-eye" onclick="togglePasswordVisibility('confirme_senha', this)" alt="Mostrar ou ocultar senha">
                            </div>
                            <span id="error-match" class="error-message-text">Senhas não coincidem</span>
                        </div>
                    </div>

                    <div class="form-column">
                        <div class="input-group">
                            <label for="email">E-mail:*</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars(requestString($_POST, 'email'), ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>

                        <div class="input-group">
                            <label for="telefone">Telefone:*</label>
                            <input type="tel" id="telefone" name="telefone" placeholder="(00) 00000-0000" maxlength="15" value="<?= htmlspecialchars(requestString($_POST, 'telefone'), ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>

                        <div class="input-group password-wrapper">
                            <label for="senha">Senha:*</label>
                            <div class="input-icon-container">
                                <input type="password" id="senha" name="senha" required>
                                <img src="../img/olho_fechado.png" class="toggle-password-eye" onclick="togglePasswordVisibility('senha', this)" alt="Mostrar ou ocultar senha">
                            </div>
                        </div>

                        <div class="password-requirements" aria-live="polite">
                            <div class="requirement-item req-invalid" id="req-length">
                                <span class="req-icon">!</span> No mínimo 8 caracteres
                            </div>
                            <div class="requirement-item req-invalid" id="req-upper">
                                <span class="req-icon">!</span> Pelo menos 1 letra maiúscula (A-Z)
                            </div>
                            <div class="requirement-item req-invalid" id="req-special">
                                <span class="req-icon">!</span> Pelo menos 1 caractere especial (como ! @ # $)
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-footer-action">
                    <button type="submit" class="btn-submit">Cadastrar</button>
                    <p class="login-redirect">Já tem conta? <a href="login.php">Faça login</a></p>
                </div>
            </form>

           <footer class="page-footer">

            Dev<span>IN</span> |
            Escola Profª Alcina Dantas Feijão |
            © DevIN 2026.
            Todos os direitos reservados<a
            href="../html/jogos/doom.html"
            class="secret-doom"
            aria-label="."
            title=""
        >.</a>

        </footer>
        </section>

        <section class="right-side">
            <a href="login.php" class="btn-top-login">Login</a>
            <div class="mascot-container">
                <img src="../img/robocadastro.webp" alt="Robô DevIN" class="mascot-img">
            </div>
        </section>
    </div>

    <script src="../js/cadastro.js"></script>
</body>
</html>
