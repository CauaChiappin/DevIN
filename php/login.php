<?php
// Traz as funções de autenticação para este ficheiro. O "__DIR__" garante que 
// o caminho é absoluto e não falha dependendo de onde o ficheiro é chamado.
require_once __DIR__ . '/controllers/AuthController.php';

// Inicia a sessão do PHP. Isto é obrigatório sempre que queremos usar a variável 
// $_SESSION para guardar dados do utilizador (como o ID e o nome) entre as várias páginas.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !empty($_SESSION['logado'])) {
    header('Location: ' . AuthController::redirectByUserType($_SESSION['usuario_tipo'] ?? ''));
    exit;
}

// Verifica se existe alguma mensagem de erro a vir pelo link (URL, tipo login.php?erro=x).
// Se não existir (??), a variável $erro fica com um texto vazio ('').
$erro = $_GET['erro'] ?? '';

// Verifica se o formulário foi enviado. O botão "Entrar" do HTML usa o método POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {
        // Vai buscar o email e a senha que o utilizador digitou no formulário ($_POST).
        // Chama a função login() que vai à base de dados verificar se os dados estão corretos.
        $auth = AuthController::login($_POST['email'] ?? '', $_POST['senha'] ?? '');

        // Guarda os dados do utilizador na memória do servidor (Sessão).
        $auth = AuthController::login(
            $_POST['email'] ?? '',
            $_POST['senha'] ?? ''
        );

        AuthController::establishSession($auth);
        // Cria um cookie no navegador do utilizador com o Token JWT.
        setcookie(JWT_COOKIE_NAME, $auth['token'], [
            'expires' => time() + JWT_EXPIRATION_SECONDS,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        // VERIFICAÇÃO NA TABELA 'CURRICULO' SE O USUÁRIO FOR DO TIPO 'PESSOA'
        if ($auth['usuario']['tipo'] === 'pessoa') {
            $host = "localhost";
            $user = "root";
            $pass = "";
            $dbname = "devin";

            $conn = new mysqli($host, $user, $pass, $dbname);
            if (!$conn->connect_error) {
                $idPessoa = $auth['usuario']['id'];

                // Busca se já existe um currículo cadastrado para esta pessoa
                $sql = "SELECT id_curriculo FROM curriculo WHERE id_pessoa = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $idPessoa);
                $stmt->execute();
                $res = $stmt->get_result();

                // Se encontrou um currículo cadastrado, manda para o Dashboard
                if ($res && $res->num_rows > 0) {
                    header('Location:pessoa.php');
                } else {
                    // Se não tiver currículo registrado, obriga a cadastrar
                    header('Location: cadastrar_curriculo.php');
                }
                $stmt->close();
                $conn->close();
                exit;
            }
        }

        // Se for outro tipo de usuário (ex: empresa), usa o redirecionamento padrão
        header('Location: ' . AuthController::redirectByUserType($auth['usuario']['tipo']));
        exit;

    } catch (Throwable $exception) {
        // Se houver erro no login, guarda a mensagem na variável $erro
        $erro = $exception->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/login.css">
    <title>Devin | Login</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link rel="icon" type="image/png" href="../img/favicon.png">
</head>

<body>

    <header class="cabecalho-site">
        <div class="logo">
            <a href="../html/index.html">Dev<span>IN</span></a>
        </div>

        <nav class="navegacao">
            <ul>
                <li><a href="#conheca">Conheça o DevIN</a></li>
                <li><a href="etapas">Etapas</a></li>
                <li><a href="contatos">Contato</a></li>
            </ul>
        </nav>

        <div class="acoes">
            <a class="botao-azul" href="../php/cadastro_pessoa.php">Cadastrar-se</a>
        </div>
    </header>

    <main class="conteudo-login">

        <img class="gif-robo" src="../img/robologin.gif" alt="Robô DevIN">

        <div class="area-login">
            <h1>Login</h1>

            <?php if (!empty($erro)): ?>
                <p class="mensagem-erro" style="color: red; font-weight: bold; margin-bottom: 10px;">
                    <?php echo htmlspecialchars($erro, ENT_QUOTES, 'UTF-8'); ?>
                </p>
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
                        <img id="img-olho" src="../img/olho_fechado.png" alt="Mostrar Senha">
                    </button>
                </div>

                <a href="../php/recuperacao.php" class="link-esqueceu">Esqueceu a Senha?</a>

                <button type="submit" class="botao-entrar">Entrar</button>
            </form>

            <p class="texto-politica">
                Ao continuar, você reconhece a <a href="#">Política de Privacidade</a> do DevIN.
            </p>
        </div>
        <script src="../js/login.js"></script>
    </main>

</body>

</html>