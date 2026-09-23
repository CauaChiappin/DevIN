<?php

declare(strict_types=1);

ob_start();

require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/security.php';

startSecureSession();

$erro = '';

/*
|--------------------------------------------------------------------------
| PROCESSAMENTO DO CADASTRO
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireValidCsrf();

    $nome          = trim(requestString($_POST, 'nome'));
    $email         = trim(requestString($_POST, 'email'));
    $cpf           = preg_replace('/[^0-9]/', '', requestString($_POST, 'cpf'));
    $telefone      = preg_replace('/[^0-9]/', '', requestString($_POST, 'telefone'));
    $cep           = preg_replace('/[^0-9]/', '', requestString($_POST, 'cep'));
    $senha         = requestString($_POST, 'senha');
    $confirmeSenha = requestString($_POST, 'confirme_senha');

    try {
        /*
        |--------------------------------------------------------------------------
        | VALIDAÇÕES DOS CAMPOS
        |--------------------------------------------------------------------------
        */

        if ($nome === '') {
            throw new Exception('Informe seu nome.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Informe um e-mail válido.');
        }

        if (strlen($cpf) !== 11) {
            throw new Exception('Informe um CPF válido com 11 dígitos.');
        }

        if (strlen($telefone) < 10 || strlen($telefone) > 11) {
            throw new Exception('Informe um telefone válido (10 ou 11 dígitos com DDD).');
        }

        if (strlen($cep) !== 8) {
            throw new Exception('Informe um CEP válido com 8 dígitos.');
        }

        if ($senha === '') {
            throw new Exception('Informe uma senha.');
        }

        if ($senha !== $confirmeSenha) {
            throw new Exception('As senhas não coincidem.');
        }

        /*
        |--------------------------------------------------------------------------
        | REGRAS DE SEGURANÇA DA SENHA
        |--------------------------------------------------------------------------
        */

        if (strlen($senha) < 8) {
            throw new Exception('A senha deve ter no mínimo 8 caracteres.');
        }

        if (!preg_match('/[A-Z]/', $senha)) {
            throw new Exception('A senha deve possuir pelo menos uma letra maiúscula (A-Z).');
        }

        if (!preg_match('/[^a-zA-Z0-9]/', $senha)) {
            throw new Exception('A senha deve possuir pelo menos um caractere especial (ex: ! @ # $).');
        }

        /*
        |--------------------------------------------------------------------------
        | VERIFICAÇÕES NO BANCO DE DADOS
        |--------------------------------------------------------------------------
        */

        $conn = getDatabaseConnection();

        try {
            // 1. E-mail único entre todos os tipos de conta
            foreach (['pessoa', 'empresa', 'administrador'] as $tabelaEmail) {
                $stmtEmail = $conn->prepare("SELECT email FROM {$tabelaEmail} WHERE email = ? LIMIT 1");

                if (!$stmtEmail) {
                    throw new RuntimeException('Não foi possível validar o e-mail.');
                }

                $stmtEmail->bind_param('s', $email);
                $stmtEmail->execute();
                $emailExiste = $stmtEmail->get_result();
                $existe = $emailExiste && $emailExiste->num_rows > 0;
                $stmtEmail->close();

                if ($existe) {
                    throw new Exception('Este e-mail já está cadastrado. Use outro e-mail ou faça login.');
                }
            }

            // 2. CPF único na tabela pessoa
            $stmtCpf = $conn->prepare('SELECT id_pessoa FROM pessoa WHERE cpf = ? LIMIT 1');
            if ($stmtCpf) {
                $stmtCpf->bind_param('s', $cpf);
                $stmtCpf->execute();
                $cpfExiste = $stmtCpf->get_result();
                $existeCpf = $cpfExiste && $cpfExiste->num_rows > 0;
                $stmtCpf->close();

                if ($existeCpf) {
                    throw new Exception('Este CPF já está cadastrado em nosso sistema.');
                }
            }

            /*
            |--------------------------------------------------------------------------
            | HASH DA SENHA E INSERÇÃO
            |--------------------------------------------------------------------------
            */

            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            if ($senhaHash === false) {
                throw new RuntimeException('Não foi possível proteger a senha.');
            }

            $stmtInsert = $conn->prepare("
                INSERT INTO pessoa
                (nome, email, cpf, telefone, cep, senha_hash, foto, created_at, lembrete_enviado)
                VALUES
                (?, ?, ?, ?, ?, ?, ?, NOW(), 0)
            ");

            if (!$stmtInsert) {
                throw new RuntimeException('Erro ao preparar o cadastro.');
            }

            $foto = ''; // Foto inicial vazia
            $stmtInsert->bind_param('sssssss', $nome, $email, $cpf, $telefone, $cep, $senhaHash, $foto);
            $stmtInsert->execute();
            $idPessoa = (int) $stmtInsert->insert_id;
            $stmtInsert->close();

        } finally {
            $conn->close();
        }

        /*
        |--------------------------------------------------------------------------
        | LOGIN AUTOMÁTICO E SESSÃO DO CURRÍCULO
        |--------------------------------------------------------------------------
        */

        $auth = AuthController::login($email, $senha);
        AuthController::establishSession($auth);

        $_SESSION['id_pessoa']    = $idPessoa;
        $_SESSION['pessoa_nome']  = $nome;
        $_SESSION['nome_pessoa']  = $nome;
        $_SESSION['email_pessoa'] = $email;

        header('Location: cadastrar_curriculo.php');
        exit;

    } catch (mysqli_sql_exception $e) {
        error_log('Erro MySQL cadastro pessoa: ' . $e->getMessage());

        if ($e->getCode() === 1062) {
            $erro = 'Este CPF ou e-mail já está cadastrado em nosso sistema.';
        } else {
            $erro = 'Não foi possível realizar o cadastro. Tente novamente.';
        }
    } catch (Throwable $e) {
        $erro = $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevIN | Criar Conta Pessoal</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link rel="stylesheet" href="../css/cadastrostyle.css">
    <link rel="stylesheet" href="../css/site-navigation.css">
</head>

<body>

<div class="main-container">

    <section class="left-side">

        <header class="cadastro-header">
            <div class="brand-logo">
                <a href="../index.php">
                    Dev<span>IN</span>
                </a>
            </div>

            <button class="site-menu-toggle" type="button" aria-label="Abrir menu" aria-controls="site-menu" aria-expanded="false" data-site-menu-toggle>
                <span aria-hidden="true"></span><span aria-hidden="true"></span><span aria-hidden="true"></span>
            </button>
            <div class="site-menu" id="site-menu" data-site-menu>
                <a href="login.php" class="header-action">Login</a>
            </div>
        </header>

        <div class="toggle-container">
            <a href="cadastro_pessoa.php" class="toggle-btn pessoal active">
                Pessoal
            </a>
            <span class="toggle-divider">OU</span>
            <a href="cadastro_empresa.php" class="toggle-btn empresa">
                Empresa
            </a>
        </div>

        <h1 class="page-title">Criar conta</h1>

        <?php if (!empty($erro)): ?>
            <div class="php-toast error-toast" role="alert">
                <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <form action="cadastro_pessoa.php" method="POST" class="register-form" id="formCadastro">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">

            <div class="form-columns">

                <div class="form-column">

                    <div class="input-group">
                        <label for="nome">Nome:*</label>
                        <input type="text" id="nome" name="nome" required value="<?= htmlspecialchars(requestString($_POST, 'nome'), ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="input-group">
                        <label for="cpf">CPF:*</label>
                        <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" maxlength="14" required value="<?= htmlspecialchars(requestString($_POST, 'cpf'), ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="input-group">
                        <label for="cep">CEP:*</label>
                        <input type="text" id="cep" name="cep" placeholder="00000-000" maxlength="9" required value="<?= htmlspecialchars(requestString($_POST, 'cep'), ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="input-group password-wrapper">
                        <label for="confirme_senha">Confirme a sua senha:*</label>
                        <div class="input-icon-container">
                            <input type="password" id="confirme_senha" name="confirme_senha" required autocomplete="new-password">
                            <img src="../img/olho_fechado.png" class="toggle-password-eye" onclick="togglePasswordVisibility('confirme_senha', this)" alt="Mostrar ou ocultar senha">
                        </div>
                        <span id="error-match" class="error-message-text">Senhas não coincidem</span>
                    </div>

                </div>

                <div class="form-column">

                    <div class="input-group">
                        <label for="email">E-mail:*</label>
                        <input type="email" id="email" name="email" required autocomplete="email" value="<?= htmlspecialchars(requestString($_POST, 'email'), ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="input-group">
                        <label for="telefone">Telefone:*</label>
                        <input type="tel" id="telefone" name="telefone" placeholder="(00) 00000-0000" required value="<?= htmlspecialchars(requestString($_POST, 'telefone'), ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="input-group password-wrapper">
                        <label for="senha">Senha:*</label>
                        <div class="input-icon-container">
                            <input type="password" id="senha" name="senha" required autocomplete="new-password">
                            <img src="../img/olho_fechado.png" class="toggle-password-eye" onclick="togglePasswordVisibility('senha', this)" alt="Mostrar ou ocultar senha">
                        </div>
                    </div>

                    <div class="password-requirements">
                        <div class="requirement-item req-invalid" id="req-length">
                            <span class="req-icon">⚠️</span> No mínimo 8 caracteres
                        </div>
                        <div class="requirement-item req-invalid" id="req-upper">
                            <span class="req-icon">⚠️</span> Pelo menos 1 letra maiúscula (A-Z)
                        </div>
                        <div class="requirement-item req-invalid" id="req-special">
                            <span class="req-icon">⚠️</span> Pelo menos 1 caractere especial (ex: ! @ # $)
                        </div>
                    </div>

                </div>

            </div>

            <div class="form-footer-action">
                <button type="submit" class="btn-submit">Cadastrar</button>
                <p class="login-redirect">
                    Já tem conta? <a href="login.php">Faça login</a>
                </p>
            </div>

        </form>

        <footer class="page-footer">
            Dev<span>IN</span> | Escola Profª Alcina Dantas Feijão | © DevIN 2026. Todos os direitos reservados.
        </footer>

    </section>

    <section class="right-side">
        <div class="mascot-container">
            <img src="../img/robocadastro.webp" alt="Robô DevIN" class="mascot-img">
        </div>
    </section>

</div>

<div id="status-alert-container"></div>

<script src="../js/cadastro.js"></script>
<script src="../js/site-navigation.js"></script>

</body>
</html>