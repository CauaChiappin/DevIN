<?php

ob_start();

require_once __DIR__ . '/controllers/AuthController.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}


// =====================================================
// VALIDAR CPF
// =====================================================

function validarCPF($cpf)
{
    $cpf = preg_replace('/[^0-9]/', '', $cpf);

    // CPF precisa ter 11 dígitos
    if (
        strlen($cpf) != 11 ||
        preg_match('/^(\d)\1{10}$/', $cpf)
    ) {
        return false;
    }

    // Validação dos dois dígitos verificadores
    for ($t = 9; $t < 11; $t++) {

        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }

        $d = ((10 * $d) % 11) % 10;

        if ($cpf[$c] != $d) {
            return false;
        }
    }

    return true;
}


// =====================================================
// PROCESSAMENTO DO CADASTRO
// =====================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        // ---------------------------------------------
        // RECEBER DADOS DO FORMULÁRIO
        // ---------------------------------------------

        $nome = trim($_POST['nome'] ?? '');

        $email = trim($_POST['email'] ?? '');

        $cpf = preg_replace(
            '/[^0-9]/',
            '',
            $_POST['cpf'] ?? ''
        );

        $telefone = preg_replace(
            '/[^0-9]/',
            '',
            $_POST['telefone'] ?? ''
        );

        $cep = preg_replace(
            '/[^0-9]/',
            '',
            $_POST['cep'] ?? ''
        );

        $senha = $_POST['senha'] ?? '';

        $confirmeSenha = $_POST['confirme_senha'] ?? '';


        // ---------------------------------------------
        // VALIDAR CAMPOS
        // ---------------------------------------------

        if ($nome === '') {
            throw new Exception('Informe seu nome.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Informe um e-mail válido.');
        }

        if (!validarCPF($cpf)) {
            throw new Exception('O CPF informado é inválido.');
        }

        if ($telefone === '') {
            throw new Exception('Informe seu telefone.');
        }

        if ($cep === '') {
            throw new Exception('Informe seu CEP.');
        }

        if ($senha === '') {
            throw new Exception('Informe uma senha.');
        }

        if ($senha !== $confirmeSenha) {
            throw new Exception('As senhas não coincidem.');
        }


        // ---------------------------------------------
        // VALIDAR SENHA
        // ---------------------------------------------

        if (strlen($senha) < 8) {
            throw new Exception(
                'A senha deve ter no mínimo 8 caracteres.'
            );
        }

        if (!preg_match('/[A-Z]/', $senha)) {
            throw new Exception(
                'A senha deve possuir pelo menos uma letra maiúscula.'
            );
        }

        if (!preg_match('/[^a-zA-Z0-9]/', $senha)) {
            throw new Exception(
                'A senha deve possuir pelo menos um caractere especial.'
            );
        }


        // ---------------------------------------------
        // CONEXÃO COM O BANCO
        // ---------------------------------------------

        $conn = getDatabaseConnection();


        // ---------------------------------------------
        // CONFIGURAR ERROS DO MYSQL
        // ---------------------------------------------

        mysqli_report(
            MYSQLI_REPORT_ERROR |
            MYSQLI_REPORT_STRICT
        );


        // ---------------------------------------------
        // CRIPTOGRAFAR SENHA
        // ---------------------------------------------

        $senhaHash = password_hash(
            $senha,
            PASSWORD_DEFAULT
        );


        // ---------------------------------------------
        // INSERIR PESSOA
        // ---------------------------------------------

        $sql = "
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
        ";


        $stmt = $conn->prepare($sql);


        /*
         * TODOS os campos são tratados como string.
         *
         * Principalmente o CEP, porque ele pode começar
         * com zero.
         */
        $stmt->bind_param(
            "ssssss",
            $nome,
            $email,
            $cpf,
            $telefone,
            $cep,
            $senhaHash
        );


        // Executa INSERT
        $stmt->execute();


        // Guarda o ID criado
        $idPessoa = $stmt->insert_id;


        $stmt->close();
        $conn->close();


        // =================================================
        // LOGIN AUTOMÁTICO
        // =================================================

        /*
         * Agora que a pessoa foi criada, usamos o próprio
         * AuthController para fazer o login.
         */

        $auth = AuthController::login(
            $email,
            $senha
        );


        /*
         * Cria:
         *
         * $_SESSION['usuario_id']
         * $_SESSION['usuario_nome']
         * $_SESSION['usuario_email']
         * $_SESSION['usuario_tipo']
         * $_SESSION['jwt']
         * $_SESSION['logado']
         *
         * e também o cookie JWT.
         */
        AuthController::establishSession($auth);


        /*
         * Guarda também o ID da pessoa para o cadastro
         * do currículo.
         */
        $_SESSION['id_pessoa'] = $idPessoa;
        $_SESSION['pessoa_nome'] = $nome;


        // ---------------------------------------------
        // VAI DIRETO PARA O CURRÍCULO
        // ---------------------------------------------

        header('Location: cadastrar_curriculo.php');
        exit;


    } catch (mysqli_sql_exception $e) {

        /*
         * Código 1062 = chave duplicada.
         *
         * Normalmente significa CPF ou e-mail já cadastrado.
         */

        if ($e->getCode() === 1062) {

            $erro = 'Este CPF ou e-mail já está cadastrado.';

        } else {

            $erro =
                'Erro ao salvar no banco de dados: ' .
                $e->getMessage();
        }


    } catch (Throwable $e) {

        $erro = $e->getMessage();
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

    <title>DevIN | Criar Conta Pessoal</title>

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


    <!-- ========================================= -->
    <!-- LADO ESQUERDO                            -->
    <!-- ========================================= -->

    <section class="left-side">


        <!-- LOGO -->

        <div class="brand-logo">

            <a href="../php/index.php">
                Dev<span>IN</span>
            </a>

        </div>


        <!-- ===================================== -->
        <!-- ALTERNAR TIPO DE CADASTRO              -->
        <!-- ===================================== -->

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


        <!-- ===================================== -->
        <!-- MENSAGEM DE ERRO                      -->
        <!-- ===================================== -->

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


        <!-- ===================================== -->
        <!-- FORMULÁRIO                             -->
        <!-- ===================================== -->

        <form
            action="cadastro_pessoa.php"
            method="POST"
            class="register-form"
            id="formCadastro"
        >


            <div class="form-columns">


                <!-- ================================= -->
                <!-- PRIMEIRA COLUNA                    -->
                <!-- ================================= -->

                <div class="form-column">


                    <!-- NOME -->

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


                    <!-- CPF -->

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


                    <!-- CEP -->

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


                    <!-- CONFIRMAR SENHA -->

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
                                alt="Ocultar/Mostrar Senha"
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


                <!-- ================================= -->
                <!-- SEGUNDA COLUNA                    -->
                <!-- ================================= -->

                <div class="form-column">


                    <!-- EMAIL -->

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


                    <!-- TELEFONE -->

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


                    <!-- SENHA -->

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
                                alt="Ocultar/Mostrar Senha"
                            >

                        </div>

                    </div>


                    <!-- REQUISITOS DA SENHA -->

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

                            Pelo menos 1 caracter especial
                            (como ! @ # $)

                        </div>


                    </div>


                </div>

            </div>


            <!-- ===================================== -->
            <!-- BOTÃO CADASTRAR                       -->
            <!-- ===================================== -->

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


        <!-- ===================================== -->
        <!-- RODAPÉ                                -->
        <!-- ===================================== -->

        <footer class="page-footer">

            Dev<span>IN</span> |
            Escola Profª Alcina Dantas Feijão |
            © DevIN 2026.
            Todos os direitos reservados.

        </footer>


    </section>


    <!-- ========================================= -->
    <!-- LADO DIREITO                             -->
    <!-- ========================================= -->

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