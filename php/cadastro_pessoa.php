<?php

ob_start();

require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/config/database.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$erro = '';

/*
|--------------------------------------------------------------------------
| PROCESSAMENTO DO CADASTRO
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome =
        trim($_POST['nome'] ?? '');

    $email =
        trim($_POST['email'] ?? '');

    $cpf =
        preg_replace(
            '/[^0-9]/',
            '',
            $_POST['cpf'] ?? ''
        );

    $telefone =
        preg_replace(
            '/[^0-9]/',
            '',
            $_POST['telefone'] ?? ''
        );

    $cep =
        preg_replace(
            '/[^0-9]/',
            '',
            $_POST['cep'] ?? ''
        );

    $senha =
        $_POST['senha'] ?? '';

    $confirmeSenha =
        $_POST['confirme_senha'] ?? '';

    try {

        /*
        |--------------------------------------------------------------------------
        | VALIDAÇÕES
        |--------------------------------------------------------------------------
        */

        if ($nome === '') {

            throw new Exception(
                'Informe seu nome.'
            );
        }

        if (
            !filter_var(
                $email,
                FILTER_VALIDATE_EMAIL
            )
        ) {

            throw new Exception(
                'Informe um e-mail válido.'
            );
        }

        if (
            strlen($cpf) !== 11
        ) {

            throw new Exception(
                'Informe um CPF válido.'
            );
        }

        if (
            strlen($telefone) < 10
        ) {

            throw new Exception(
                'Informe um telefone válido.'
            );
        }

        if (
            strlen($cep) !== 8
        ) {

            throw new Exception(
                'Informe um CEP válido.'
            );
        }

        if ($senha === '') {

            throw new Exception(
                'Informe uma senha.'
            );
        }

        if ($senha !== $confirmeSenha) {

            throw new Exception(
                'As senhas não coincidem.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | SENHA
        |--------------------------------------------------------------------------
        */

        if (strlen($senha) < 8) {

            throw new Exception(
                'A senha deve ter no mínimo 8 caracteres.'
            );
        }

        if (!preg_match(
            '/[A-Z]/',
            $senha
        )) {

            throw new Exception(
                'A senha deve possuir pelo menos uma letra maiúscula.'
            );
        }

        if (!preg_match(
            '/[^a-zA-Z0-9]/',
            $senha
        )) {

            throw new Exception(
                'A senha deve possuir pelo menos um caractere especial.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | BANCO
        |--------------------------------------------------------------------------
        */

        mysqli_report(
            MYSQLI_REPORT_ERROR |
            MYSQLI_REPORT_STRICT
        );

        $conn =
            getDatabaseConnection();

        /*
        |--------------------------------------------------------------------------
        | HASH
        |--------------------------------------------------------------------------
        */

        $senhaHash =
            password_hash(
                $senha,
                PASSWORD_DEFAULT
            );

        /*
        |--------------------------------------------------------------------------
        | INSERT
        |--------------------------------------------------------------------------
        */

        $stmt =
            $conn->prepare("
                INSERT INTO pessoa
                (
                    nome,
                    email,
                    cpf,
                    telefone,
                    cep,
                    senha_hash,
                    created_at,
                    lembrete_enviado
                )
                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    NOW(),
                    0
                )
            ");

        /*
         * Todos são strings.
         *
         * Isso é especialmente importante
         * para CPF e CEP.
         */

        $stmt->bind_param(
            'ssssss',
            $nome,
            $email,
            $cpf,
            $telefone,
            $cep,
            $senhaHash
        );

        $stmt->execute();

        $idPessoa =
            $stmt->insert_id;

        $stmt->close();

        $conn->close();

        /*
        |--------------------------------------------------------------------------
        | LOGIN AUTOMÁTICO
        |--------------------------------------------------------------------------
        */

        $auth =
            AuthController::login(
                $email,
                $senha
            );

        AuthController::establishSession(
            $auth
        );

        /*
        |--------------------------------------------------------------------------
        | DADOS PARA O CURRÍCULO
        |--------------------------------------------------------------------------
        */

        $_SESSION['id_pessoa'] =
            $idPessoa;

        $_SESSION['pessoa_nome'] =
            $nome;

        /*
         * Também salvamos nas chaves utilizadas
         * pelo cadastrar_curriculo.php.
         */

        $_SESSION['nome_pessoa'] =
            $nome;

        $_SESSION['email_pessoa'] =
            $email;

        /*
        |--------------------------------------------------------------------------
        | REDIRECIONAMENTO
        |--------------------------------------------------------------------------
        */

        header(
            'Location: cadastrar_curriculo.php'
        );

        exit;

    } catch (mysqli_sql_exception $e) {

        error_log(
            'Erro MySQL cadastro pessoa: ' .
            $e->getMessage()
        );

        if ($e->getCode() === 1062) {

            $erro =
                'Este CPF ou e-mail já está cadastrado.';

        } else {

            $erro =
                'Não foi possível realizar o cadastro.';
        }

    } catch (Throwable $e) {

        $erro =
            $e->getMessage();
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        DevIN | Criar Conta Pessoal
    </title>

    <link
        rel="icon"
        type="image/svg+xml"
        href="../img/favicon.svg"
    >

    <link
        rel="icon"
        type="image/png"
        href="../img/favicon.png"
    >

    <link
        rel="stylesheet"
        href="../css/cadastrostyle.css"
    >

</head>

<body>

<div class="main-container">

    <section class="left-side">

        <div class="brand-logo">

            <a href="index.php">
                Dev<span>IN</span>
            </a>

        </div>

        <div class="toggle-container">

            <a
                href="cadastro_pessoa.php"
                class="toggle-btn pessoal active"
            >
                Pessoal
            </a>

            <span class="toggle-divider">
                OU
            </span>

            <a
                href="cadastro_empresa.php"
                class="toggle-btn empresa"
            >
                Empresa
            </a>

        </div>

        <h1 class="page-title">
            Criar conta
        </h1>

        <?php if (!empty($erro)): ?>

            <div
                class="php-toast error-toast"
                style="
                    color: red;
                    font-weight: bold;
                    margin-bottom: 15px;
                "
            >

                <?= htmlspecialchars(
                    $erro,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

            </div>

        <?php endif; ?>

        <form
            action="cadastro_pessoa.php"
            method="POST"
            class="register-form"
            id="formCadastro"
        >

            <div class="form-columns">

                <div class="form-column">

                    <div class="input-group">

                        <label for="nome">
                            Nome:*
                        </label>

                        <input
                            type="text"
                            id="nome"
                            name="nome"
                            required
                            value="<?= htmlspecialchars(
                                $_POST['nome'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                        >

                    </div>

                    <div class="input-group">

                        <label for="cpf">
                            CPF:*
                        </label>

                        <input
                            type="text"
                            id="cpf"
                            name="cpf"
                            placeholder="000.000.000-00"
                            maxlength="14"
                            required
                            value="<?= htmlspecialchars(
                                $_POST['cpf'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                        >

                    </div>

                    <div class="input-group">

                        <label for="cep">
                            CEP:*
                        </label>

                        <input
                            type="text"
                            id="cep"
                            name="cep"
                            placeholder="00000-000"
                            maxlength="9"
                            required
                            value="<?= htmlspecialchars(
                                $_POST['cep'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                        >

                    </div>

                    <div class="input-group password-wrapper">

                        <label for="confirme_senha">
                            Confirme a sua senha:*
                        </label>

                        <div class="input-icon-container">

                            <input
                                type="password"
                                id="confirme_senha"
                                name="confirme_senha"
                                required
                            >

                            <img
                                src="../img/olho_fechado.png"
                                class="toggle-password-eye"
                                onclick="togglePasswordVisibility(
                                    'confirme_senha',
                                    this
                                )"
                                alt="Mostrar ou ocultar senha"
                            >

                        </div>

                        <span
                            id="error-match"
                            class="error-message-text"
                        >
                            Senhas não coincidem
                        </span>

                    </div>

                </div>

                <div class="form-column">

                    <div class="input-group">

                        <label for="email">
                            E-mail:*
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            required
                            value="<?= htmlspecialchars(
                                $_POST['email'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                        >

                    </div>

                    <div class="input-group">

                        <label for="telefone">
                            Telefone:*
                        </label>

                        <input
                            type="tel"
                            id="telefone"
                            name="telefone"
                            placeholder="(00) 00000-0000"
                            required
                            value="<?= htmlspecialchars(
                                $_POST['telefone'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                        >

                    </div>

                    <div class="input-group password-wrapper">

                        <label for="senha">
                            Senha:*
                        </label>

                        <div class="input-icon-container">

                            <input
                                type="password"
                                id="senha"
                                name="senha"
                                required
                            >

                            <img
                                src="../img/olho_fechado.png"
                                class="toggle-password-eye"
                                onclick="togglePasswordVisibility(
                                    'senha',
                                    this
                                )"
                                alt="Mostrar ou ocultar senha"
                            >

                        </div>

                    </div>

                    <div class="password-requirements">

                        <div
                            class="requirement-item req-invalid"
                            id="req-length"
                        >

                            <span class="req-icon">
                                ⚠️
                            </span>

                            No mínimo 8 caracteres

                        </div>

                        <div
                            class="requirement-item req-invalid"
                            id="req-upper"
                        >

                            <span class="req-icon">
                                ⚠️
                            </span>

                            Pelo menos 1 letra maiúscula (A-Z)

                        </div>

                        <div
                            class="requirement-item req-invalid"
                            id="req-special"
                        >

                            <span class="req-icon">
                                ⚠️
                            </span>

                            Pelo menos 1 caractere especial
                            (como ! @ # $)

                        </div>

                    </div>

                </div>

            </div>

            <div class="form-footer-action">

                <button
                    type="submit"
                    class="btn-submit"
                >
                    Cadastrar
                </button>

                <p class="login-redirect">

                    Já tem conta?

                    <a href="login.php">
                        Faça login
                    </a>

                </p>

            </div>

        </form>

        <footer class="page-footer">

            Dev<span>IN</span> |
            Escola Profª Alcina Dantas Feijão |
            © DevIN 2026.
            Todos os direitos reservados.

        </footer>

    </section>

    <section class="right-side">

        <a
            href="login.php"
            class="btn-top-login"
        >
            Login
        </a>

        <div class="mascot-container">

            <img
                src="../img/robocadastro.webp"
                alt="Robô DevIN"
                class="mascot-img"
            >

        </div>

    </section>

</div>

<div id="status-alert-container"></div>

<script src="../js/cadastro.js"></script>

</body>
</html>