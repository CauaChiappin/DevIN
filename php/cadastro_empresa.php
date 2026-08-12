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
| VALIDA CNPJ
|--------------------------------------------------------------------------
*/

function validarCNPJ(string $cnpj): bool
{
    $cnpj = preg_replace(
        '/[^0-9]/',
        '',
        $cnpj
    );

    if (
        strlen($cnpj) !== 14 ||
        preg_match(
            '/^(\d)\1{13}$/',
            $cnpj
        )
    ) {
        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | PRIMEIRO DÍGITO
    |--------------------------------------------------------------------------
    */

    $soma = 0;
    $peso = 5;

    for ($i = 0; $i < 12; $i++) {

        $soma +=
            ((int) $cnpj[$i]) * $peso;

        $peso--;

        if ($peso === 1) {
            $peso = 9;
        }
    }

    $resto =
        $soma % 11;

    $digito =
        $resto < 2
            ? 0
            : 11 - $resto;

    if (
        (int) $cnpj[12] !== $digito
    ) {
        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | SEGUNDO DÍGITO
    |--------------------------------------------------------------------------
    */

    $soma = 0;
    $peso = 6;

    for ($i = 0; $i < 13; $i++) {

        $soma +=
            ((int) $cnpj[$i]) * $peso;

        $peso--;

        if ($peso === 1) {
            $peso = 9;
        }
    }

    $resto =
        $soma % 11;

    $digito =
        $resto < 2
            ? 0
            : 11 - $resto;

    return (
        (int) $cnpj[13] === $digito
    );
}

/*
|--------------------------------------------------------------------------
| CONSULTA CNPJ NA BRASIL API
|--------------------------------------------------------------------------
*/

function cnpjExisteNaReceita(string $cnpj): bool
{
    $cnpj =
        preg_replace(
            '/[^0-9]/',
            '',
            $cnpj
        );

    $url =
        'https://brasilapi.com.br/api/cnpj/v1/' .
        $cnpj;

    $ch =
        curl_init();

    curl_setopt_array(
        $ch,
        [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json'
            ]
        ]
    );

    $response =
        curl_exec($ch);

    $httpCode =
        curl_getinfo(
            $ch,
            CURLINFO_HTTP_CODE
        );

    curl_close($ch);

    return (
        $response !== false &&
        $httpCode === 200
    );
}

/*
|--------------------------------------------------------------------------
| PROCESSAMENTO
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nome =
        trim($_POST['nome'] ?? '');

    $cnpj =
        preg_replace(
            '/[^0-9]/',
            '',
            $_POST['cnpj'] ?? ''
        );

    $cep =
        preg_replace(
            '/[^0-9]/',
            '',
            $_POST['cep'] ?? ''
        );

    $email =
        trim($_POST['email'] ?? '');

    $telefone =
        preg_replace(
            '/[^0-9]/',
            '',
            $_POST['telefone'] ?? ''
        );

    $senha =
        $_POST['senha'] ?? '';

    $confirmeSenha =
        $_POST['confirme_senha'] ?? '';

    try {

        /*
        |--------------------------------------------------------------------------
        | CAMPOS
        |--------------------------------------------------------------------------
        */

        if ($nome === '') {
            throw new Exception(
                'Informe o nome da empresa.'
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
            strlen($cep) !== 8
        ) {
            throw new Exception(
                'Informe um CEP válido.'
            );
        }

        if (
            strlen($telefone) < 10
        ) {
            throw new Exception(
                'Informe um telefone válido.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | SENHA
        |--------------------------------------------------------------------------
        */

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
        | CNPJ
        |--------------------------------------------------------------------------
        */

        if (!validarCNPJ($cnpj)) {
            throw new Exception(
                'CNPJ inválido! Verifique os dígitos inseridos.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | CONSULTA BRASIL API
        |--------------------------------------------------------------------------
        */

        if (!cnpjExisteNaReceita($cnpj)) {

            throw new Exception(
                'CNPJ não encontrado na base de dados consultada.'
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
        | SENHA HASH
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
                INSERT INTO empresa
                (
                    nome,
                    cnpj,
                    cep,
                    email,
                    senha_hash,
                    telefone
                )
                VALUES
                (?, ?, ?, ?, ?, ?)
            ");

        /*
         * TODOS são strings.
         *
         * Principalmente o CEP.
         */

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

        header(
            'Location: ' .
            AuthController::redirectByUserType(
                $auth['usuario']['tipo']
            )
        );

        exit;

    } catch (mysqli_sql_exception $e) {

        error_log(
            'Erro MySQL cadastro empresa: ' .
            $e->getMessage()
        );

        if ($e->getCode() === 1062) {

            $erro =
                'Este CNPJ ou e-mail já está cadastrado.';

        } else {

            $erro =
                'Não foi possível cadastrar a empresa.';
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
        DevIN | Criar Conta
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

            <a
                href="cadastro_pessoa.php"
                class="toggle-btn pessoal"
            >
                Pessoal
            </a>

            <span class="toggle-divider">
                OU
            </span>

            <a
                href="cadastro_empresa.php"
                class="toggle-btn empresa active"
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
            action="cadastro_empresa.php"
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

                        <label for="cnpj">
                            CNPJ:*
                        </label>

                        <input
                            type="text"
                            id="cnpj"
                            name="cnpj"
                            placeholder="00.000.000/0000-00"
                            required
                            value="<?= htmlspecialchars(
                                $_POST['cnpj'] ?? '',
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
        <a href="login.php" class="header-action header-action--outside">Login</a>

        <div class="mascot-container">

            <img
                src="../img/robocadastro.webp"
                alt="Robô DevIN"
                class="mascot-img"
            >

        </div>

    </section>

</div>

<script src="../js/cadastro.js"></script>

</body>
</html>
