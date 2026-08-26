<?php

require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middlewares/auth.php';
require_once __DIR__ . '/config/security.php';

startSecureSession();

/*
|--------------------------------------------------------------------------
| SE JÁ ESTÁ LOGADO
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] !== 'POST' &&
    !empty($_SESSION['logado'])
) {

    $tipoSessao = $_SESSION['usuario_tipo'] ?? '';

    if ($tipoSessao === 'pessoa') {
        try {
            $conn = getDatabaseConnection();
            $idPessoaSessao = (int) ($_SESSION['usuario_id'] ?? 0);
            $stmt = $conn->prepare(
                'SELECT id_curriculo FROM curriculo WHERE id_pessoa = ? LIMIT 1'
            );
            $stmt->bind_param('i', $idPessoaSessao);
            $stmt->execute();
            $possuiCurriculoSessao = $stmt->get_result()->num_rows > 0;
            $stmt->close();
            $conn->close();
        } catch (Throwable $exception) {
            error_log('Erro ao verificar curriculo no login: ' . $exception->getMessage());
            $possuiCurriculoSessao = false;
        }

        header('Location: ' . ($possuiCurriculoSessao ? 'pessoa.php' : 'cadastrar_curriculo.php'));
    } else {
        header(
            'Location: ' .
            AuthController::redirectByUserType($tipoSessao)
        );
    }

    exit;
}

$erro = '';

/*
|--------------------------------------------------------------------------
| MENSAGEM DE SUCESSO
|--------------------------------------------------------------------------
*/

$sucesso =
    $_SESSION['sucesso_login'] ?? '';

unset(
    $_SESSION['sucesso_login']
);

/*
|--------------------------------------------------------------------------
| LOGIN
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    requireValidCsrf();

    try {

        $email =
            trim($_POST['email'] ?? '');

        $senha =
            $_POST['senha'] ?? '';

        /*
        |--------------------------------------------------------------------------
        | AUTENTICA APENAS UMA VEZ
        |--------------------------------------------------------------------------
        */

        $auth =
            AuthController::login(
                $email,
                $senha
            );

        /*
        |--------------------------------------------------------------------------
        | CRIA SESSÃO
        |--------------------------------------------------------------------------
        */

        AuthController::establishSession(
            $auth
        );

        /*
        |--------------------------------------------------------------------------
        | SE FOR PESSOA
        |--------------------------------------------------------------------------
        */

        if (
            $auth['usuario']['tipo'] === 'pessoa'
        ) {

            $conn =
                getDatabaseConnection();

            $idPessoa =
                (int) $auth['usuario']['id'];

            /*
            |--------------------------------------------------------------------------
            | VERIFICA CURRÍCULO
            |--------------------------------------------------------------------------
            */

            $stmt =
                $conn->prepare("
                    SELECT id_curriculo
                    FROM curriculo
                    WHERE id_pessoa = ?
                    LIMIT 1
                ");

            $stmt->bind_param(
                'i',
                $idPessoa
            );

            $stmt->execute();

            $resultado =
                $stmt->get_result();

            $possuiCurriculo =
                $resultado &&
                $resultado->num_rows > 0;

            $stmt->close();

            $conn->close();

            /*
            |--------------------------------------------------------------------------
            | SALVA DADOS PARA O CURRÍCULO
            |--------------------------------------------------------------------------
            */

            $_SESSION['id_pessoa'] =
                $idPessoa;

            $_SESSION['nome_pessoa'] =
                $auth['usuario']['nome'];

            $_SESSION['email_pessoa'] =
                $auth['usuario']['email'];

            /*
            |--------------------------------------------------------------------------
            | REDIRECIONAMENTO
            |--------------------------------------------------------------------------
            */

            if ($possuiCurriculo) {

                header(
                    'Location: pessoa.php'
                );

            } else {

                header(
                    'Location: cadastrar_curriculo.php'
                );
            }

            exit;
        }

        /*
        |--------------------------------------------------------------------------
        | EMPRESA OU ADMINISTRADOR
        |--------------------------------------------------------------------------
        */

        header(
            'Location: ' .
            AuthController::redirectByUserType(
                $auth['usuario']['tipo']
            )
        );

        exit;

    } catch (Throwable $exception) {

        error_log(
            'Erro no login: ' .
            $exception->getMessage()
        );

        $erro = 'E-mail ou senha inválidos. Verifique os dados e tente novamente.';
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

    <link
        rel="stylesheet"
        href="../css/login.css"
    >

    <title>DevIN | Login</title>

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

</head>

<body>

<header class="cabecalho-site">

    <div class="logo">

        <a href="index.php">
            Dev<span>IN</span>
        </a>

    </div>

    <nav class="navegacao">

        <ul>

            <li>
                <a href="index.php">
                    Conheça o DevIN
                </a>
            </li>

            <li>
                <a href="index.php#etapas">
                    Etapas
                </a>
            </li>

            <li>
                <a href="index.php#contatos">
                    Contato
                </a>
            </li>

        </ul>

    </nav>

    <div class="acoes">

        <a
            class="botao-azul"
            href="cadastro_pessoa.php"
        >
            Cadastrar-se
        </a>

    </div>

</header>

<main class="conteudo-login">

    <img
        class="gif-robo"
        src="../img/robologin.gif"
        alt="Robô DevIN"
    >

    <div class="area-login">

        <h1>
            Login
        </h1>

        <?php if (!empty($sucesso)): ?>

            <p
                class="mensagem-sucesso"
                style="
                    color: green;
                    font-weight: bold;
                    margin-bottom: 10px;
                "
            >
                <?= htmlspecialchars(
                    $sucesso,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>
            </p>

        <?php endif; ?>

        <?php if (!empty($erro)): ?>

            <p
                class="mensagem-erro"
                style="
                    color: red;
                    font-weight: bold;
                    margin-bottom: 10px;
                "
            >

                <?= htmlspecialchars(
                    $erro,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

            </p>

        <?php endif; ?>

        <form
            action="login.php"
            method="POST"
        >
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">

            <div class="grupo-campo">

                <label for="email">
                    Email:
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Seu email..."
                    value="<?= htmlspecialchars(
                        $_POST['email'] ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>"
                    required
                >

            </div>

            <div
                class="grupo-campo campo-senha input-container"
            >

                <label for="senha">
                    Senha:
                </label>

                <input
                    type="password"
                    id="senha"
                    name="senha"
                    placeholder="Sua senha..."
                    required
                >

                <button
                    type="button"
                    id="btn-mostrar"
                    aria-label="Mostrar ou ocultar senha"
                >

                    <img
                        id="img-olho"
                        src="../img/olho_fechado.png"
                        alt="Mostrar senha"
                    >

                </button>

            </div>

            <a
                href="recuperacao.php"
                class="link-esqueceu"
            >
                Esqueceu a Senha?
            </a>

            <button
                type="submit"
                class="botao-entrar"
            >
                Entrar
            </button>

        </form>

        <p class="texto-politica">

            Ao continuar, você reconhece a

            <a href="#">
                Política de Privacidade
            </a>

            do DevIN.

        </p>

    </div>

</main>

<script src="../js/login.js"></script>

</body>
</html>