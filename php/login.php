<?php

declare(strict_types=1);

require_once __DIR__ . '/controllers/AuthController.php';

startSecureSession();

// Se o usuário já estiver logado, redireciona diretamente para o dashboard apropriado
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !empty($_SESSION['logado'])) {
    header('Location: ' . AuthController::redirectByUserType($_SESSION['usuario_tipo'] ?? ''));
    exit;
}

$erro = requestString($_GET, 'erro');
$emailDigitado = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        requireValidCsrf();

        $emailDigitado = requestString($_POST, 'email');
        $senhaDigitada = requestString($_POST, 'senha');

        $auth = AuthController::login($emailDigitado, $senhaDigitada);
        AuthController::establishSession($auth);

        // Redirecionamento especial para candidato sem currículo cadastrado
        if ($auth['usuario']['tipo'] === 'pessoa') {
            $conn = getDatabaseConnection();
            $idPessoa = (int) $auth['usuario']['id'];

            $stmt = $conn->prepare('SELECT id_curriculo FROM curriculo WHERE id_pessoa = ? LIMIT 1');
            if ($stmt) {
                $stmt->bind_param('i', $idPessoa);
                $stmt->execute();
                $resultado = $stmt->get_result();
                $temCurriculo = $resultado && $resultado->num_rows > 0;
                $stmt->close();
                $conn->close();

                if ($temCurriculo) {
                    header('Location: pessoa.php');
                } else {
                    header('Location: cadastrar_curriculo.php');
                }
                exit;
            }
            $conn->close();
        }

        header('Location: ' . AuthController::redirectByUserType($auth['usuario']['tipo']));
        exit;
    } catch (Throwable $exception) {
        $erro = $exception->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/login.css">
    <link rel="stylesheet" href="../css/site-navigation.css">
    <title>DevIN | Login</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
</head>

<body>

    <header class="cabecalho-site">
        <div class="logo">
            <a href="../index.php">Dev<span>IN</span></a>
        </div>
        <button class="site-menu-toggle" type="button" aria-label="Abrir menu" aria-controls="site-menu" aria-expanded="false" data-site-menu-toggle>
            <span aria-hidden="true"></span><span aria-hidden="true"></span><span aria-hidden="true"></span>
        </button>

        <nav class="navegacao">
            <ul>
                <li><a href="../index.php#conheca">Conheça o DevIN</a></li>
                <li><a href="../index.php#etapas">Etapas</a></li>
                <li><a href="../index.php#contato">Contato</a></li>
            </ul>
        </nav>

        <div class="acoes">
            <a class="botao-azul" href="cadastro_pessoa.php">Cadastrar-se</a>
        </div>
    </header>

    <main class="conteudo-login">

        <img class="gif-robo" src="../img/robologin.gif" alt="Robô DevIN">

        <div class="area-login">
            <h1>Login</h1>

            <?php if (!empty($erro)): ?>
                <p class="mensagem-erro" role="alert">
                    <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
                </p>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">

                <div class="grupo-campo">
                    <label for="email">E-mail:</label>
                    <input type="email" id="email" name="email" placeholder="Seu e-mail..." value="<?= htmlspecialchars($emailDigitado, ENT_QUOTES, 'UTF-8') ?>" required autocomplete="email">
                </div>

                <div class="grupo-campo campo-senha input-container">
                    <label for="senha">Senha:</label>
                    <input type="password" id="senha" name="senha" placeholder="Sua senha..." required autocomplete="current-password">

                    <button type="button" id="btn-mostrar" aria-label="Mostrar senha">
                        <img id="img-olho" src="../img/olho_fechado.png" alt="Mostrar Senha">
                    </button>
                </div>

                <a href="recuperacao.php" class="link-esqueceu">Esqueceu a Senha?</a>

                <button type="submit" class="botao-entrar">Entrar</button>
            </form>

            <p class="texto-politica">
                Ao continuar, você reconhece a <a href="politica_privacidade.php">Política de Privacidade</a> do DevIN.
            </p>
        </div>
        <script src="../js/login.js"></script>
        <script src="../js/site-navigation.js"></script>
    </main>

</body>

</html>