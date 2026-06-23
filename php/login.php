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
            <form>
                <div class="grupo-campo">
                    <label for="email">Email:</label>
                    <input type="email" id="email" placeholder="Seu email...">
                </div>
                
                <div class="grupo-campo campo-senha input-container">
                    <label for="senha">Senha:</label>
                    <input type="password" id="senha" placeholder="Sua senha...">
                    
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
// ==========================================
// CONFIGURAÇÃO DO BANCO DE DADOS
// ==========================================
$host = "localhost";       
$usuario_db = "root";      
$senha_db = "";            
$nome_db = "devin_db"; // Ajuste para o nome do seu banco de dados

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $conn = new mysqli($host, $usuario_db, $senha_db, $nome_db);

    if ($conn->connect_error) {
        die("Falha na conexão com o banco de dados: " . $conn->connect_error);
    }

    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'];

    if (!empty($email) && !empty($senha)) {
        
        // --------------------------------------------------
        // 1ª TENTATIVA: Procurar na tabela de Administradores
        // --------------------------------------------------
        // IMPORTANTE: Ajuste o nome da tabela (ex: 'administradores') e das colunas conforme seu banco
        $sql_administrador = "SELECT id, nome, senha FROM adm WHERE email = ? LIMIT 1";
        $stmt = $conn->prepare($sql_adm);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {
            $usuario = $resultado->fetch_assoc();
            if (password_verify($senha, $usuario['senha']) || $senha === $usuario['senha']) {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_tipo'] = 'adm';
                $_SESSION['logado'] = true;
                
                header("Location: dashboard_adm.php");
                exit();
            } else {
                header("Location: login.html?erro=senha_incorreta");
                exit();
            }
        }
        $stmt->close();

        // --------------------------------------------------
        // 2ª TENTATIVA: Se não achou no ADM, procura em Empresa
        // --------------------------------------------------
        // IMPORTANTE: Ajuste o nome da tabela (ex: 'empresas') e das colunas (ex: se usa 'razao_social' em vez de 'nome')
        $sql_empresa = "SELECT id, nome, senha FROM empresa WHERE email = ? LIMIT 1";
        $stmt = $conn->prepare($sql_empresa);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {
            $usuario = $resultado->fetch_assoc();
            if (password_verify($senha, $usuario['senha']) || $senha === $usuario['senha']) {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_tipo'] = 'empresa';
                $_SESSION['logado'] = true;
                
                header("Location: dashboard_empresa.php");
                exit();
            } else {
                header("Location: login.html?erro=senha_incorreta");
                exit();
            }
        }
        $stmt->close();

        // --------------------------------------------------
        // 3ª TENTATIVA: Se não achou nos outros, procura em Pessoa
        // --------------------------------------------------
        // IMPORTANTE: Ajuste o nome da tabela (ex: 'pessoas' ou 'usuarios') conforme seu banco
        $sql_pessoa = "SELECT id, nome, senha FROM pessoa WHERE email = ? LIMIT 1";
        $stmt = $conn->prepare($sql_pessoa);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {
            $usuario = $resultado->fetch_assoc();
            if (password_verify($senha, $usuario['senha']) || $senha === $usuario['senha']) {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_tipo'] = 'pessoa';
                $_SESSION['logado'] = true;
                
                header("Location: dashboard_pessoa.php");
                exit();
            } else {
                header("Location: login.html?erro=senha_incorreta");
                exit();
            }
        }
        $stmt->close();

        // --------------------------------------------------
        // FIM DO FLUXO: Se chegou aqui, não existe em nenhuma tabela
        // --------------------------------------------------
        header("Location: login.html?erro=usuario_nao_encontrado");
        exit();

    } else {
        header("Location: login.html?erro=campos_vazios");
        exit();
    }
    $conn->close();
} else {
    header("Location: login.html");
    exit();
}
?>
