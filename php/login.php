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
            <a href="index.html">Dev<span>IN</span></a>
        </div>

        <nav class="navegacao">
            <ul>
                <li><a href="#conheca">Conheça o DevIN</a></li>
                <li><a href="etapas">Etapas</a></li>
                <li><a href="contatos">Contato</a></li>
            </ul>
        </nav>

        <div class="acoes">
            <a class="botao-azul" href="cadastro_pessoa.html">Cadastrar-se</a>
        </div>
    </header>

    <main class="conteudo-login">
        
        <img class="gif-robo" src="/img/robologin.gif" alt="Robô DevIN">

        <div class="area-login">
            <h1>Login</h1>
            
            <?php if ($erro): ?>
                <p class="mensagem-erro" style="color: red; font-weight: bold; margin-bottom: 10px;"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <form action="/php/login.php" method="POST">
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
                Ao continuar, você reconhece a <a href="#">Política de Privacidade</a> do DevIN.
            </p>
        </div>
        <script src="/js/login.js"></script>
    </main>

</body>
</html>

<?php
// Traz as funções de autenticação para este ficheiro. O "__DIR__" garante que 
// o caminho é absoluto e não falha dependendo de onde o ficheiro é chamado.
require_once __DIR__ . '/controllers/AuthController.php';

// Inicia a sessão do PHP. Isto é obrigatório sempre que queremos usar a variável 
// $_SESSION para guardar dados do utilizador (como o ID e o nome) entre as várias páginas.
session_start();

// Verifica se existe alguma mensagem de erro a vir pelo link (URL, tipo login.php?erro=x).
// Se não existir (??), a variável $erro fica com um texto vazio ('').
$erro = $_GET['erro'] ?? '';

// Verifica se o formulário foi enviado. O botão "Entrar" do HTML usa o método POST.
// Ou seja, o código dentro deste 'if' só corre quando o utilizador tenta fazer login.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // O 'try' tenta executar o código seguinte. Se houver algum problema (ex: senha errada), 
    // ele salta imediatamente para o bloco 'catch' lá em baixo.
    try {
        // Vai buscar o email e a senha que o utilizador digitou no formulário ($_POST).
        // Chama a função login() que vai à base de dados verificar se os dados estão corretos.
        $auth = AuthController::login($_POST['email'] ?? '', $_POST['senha'] ?? '');

        // Se chegou até aqui, a senha está correta! 
        // Agora guardamos os dados do utilizador na memória do servidor (Sessão).
        // Assim, nas outras páginas do site, saberemos quem está logado.
        $_SESSION['usuario_id'] = $auth['usuario']['id'];
        $_SESSION['usuario_nome'] = $auth['usuario']['nome'];
        $_SESSION['usuario_email'] = $auth['usuario']['email'];
        $_SESSION['usuario_tipo'] = $auth['usuario']['tipo'];
        $_SESSION['jwt'] = $auth['token'];
        $_SESSION['logado'] = true; // Flag simples para dizer que está autenticado.

        // Cria um "cookie" (um pequeno ficheiro) no navegador do utilizador com o Token JWT.
        // Isto é uma medida de segurança muito moderna para APIs.
        setcookie(JWT_COOKIE_NAME, $auth['token'], [
            'expires' => time() + JWT_EXPIRATION_SECONDS, // Define a validade do cookie.
            'path' => '/', // O cookie vai funcionar em todo o site.
            'httponly' => true, // Segurança: impede que hackers roubem o cookie via JavaScript (XSS).
            'samesite' => 'Lax', // Segurança: protege contra ataques onde outros sites forçam ações (CSRF).
        ]);

        // Redireciona o utilizador para a página correta (ex: dashboard) 
        // dependendo do seu tipo (ex: aluno, professor, administrador).
        header('Location: ' . AuthController::redirectByUserType($auth['usuario']['tipo']));
        
        // O 'exit' garante que o PHP para imediatamente após o redirecionamento, 
        // poupando recursos do servidor.
        exit;
        
    } catch (Throwable $exception) {
        // Se a função de login() detetar um erro (ex: utilizador não existe ou senha errada),
        // o erro é apanhado aqui. Guardamos a mensagem na variável $erro para que 
        // o HTML mais abaixo consiga mostrar essa mensagem em vermelho no ecrã.
        $erro = $exception->getMessage();
    }
}
?>
