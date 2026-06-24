<?php
require_once __DIR__ . '/controllers/AuthController.php';

session_start();

$erro = $_GET['erro'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $auth = AuthController::login($_POST['email'] ?? '', $_POST['senha'] ?? '');

        $_SESSION['usuario_id'] = $auth['usuario']['id'];
        $_SESSION['usuario_nome'] = $auth['usuario']['nome'];
        $_SESSION['usuario_email'] = $auth['usuario']['email'];
        $_SESSION['usuario_tipo'] = $auth['usuario']['tipo'];
        $_SESSION['jwt'] = $auth['token'];
        $_SESSION['logado'] = true;

        setcookie(JWT_COOKIE_NAME, $auth['token'], [
            'expires' => time() + JWT_EXPIRATION_SECONDS,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        header('Location: ' . AuthController::redirectByUserType($auth['usuario']['tipo']));
        exit;
    } catch (Throwable $exception) {
        $erro = $exception->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/login.css">
    <title>Devin|Login</title>
</head>
<body>

    <header class="cabecalho-site">
        <div class="logo">
            <a href="#index">Dev<span>IN</span></a>
        </div>

        <nav class="navegacao">
            <ul>
                <li><a href="#conheca">Conheca o DevIN</a></li>
                <li><a href="etapas">Etapas</a></li>
                <li><a href="contatos">Contato</a></li>
            </ul>
        </nav>

        <div class="acoes">
            <a class="botao-azul" href="cadastro_pessoa.php">Cadastrar-se</a>
        </div>
    </header>

    <main class="conteudo-login">
        <img class="gif-robo" src="/img/robologin.gif" alt="Robo DevIN">

        <div class="area-login">
            <h1>Login</h1>

            <?php if ($erro): ?>
                <p class="mensagem-erro"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="grupo-campo">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" placeholder="Seu email..." required>
                </div>

                <div class="grupo-campo campo-senha input-container">
                    <label for="senha">Senha:</label>
                    <input type="password" id="senha" name="senha" placeholder="Sua senha..." required>

                    <button type="button" id="btn-mostrar">
                        <img id="img-olho" src="/img/olho_fechado.png" alt="Mostrar Senha">
                    </button>
                </div>

                <a href="#" class="link-esqueceu">Esqueceu a Senha?</a>

                <button type="submit" class="botao-entrar">Entrar</button>
            </form>

            <p class="texto-politica">
                Ao continuar, voce reconhece a <a href="#">Politica de Privacidade</a> do DevIN.
            </p>
        </div>
        <script src="/js/login.js"></script>
    </main>

</body>
</html>
