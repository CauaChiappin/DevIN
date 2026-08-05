<?php
ob_start();
require_once __DIR__ . '/controllers/AuthController.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevIN | Criar Conta</title>
    <link rel="stylesheet" href="../css/cadastrostyle.css" >
</head>
<body>

    <div class="main-container">
        
        <section class="left-side">
            
             <div class="brand-logo">
          <a href="../php/index.php">Dev<span>IN</span></a>
            </div>

            <div class="toggle-container">
                <a href="cadastro_pessoa.php" class="toggle-btn pessoal">Pessoal</a>
                <span class="toggle-divider">OU</span>
                <a href="cadastro_empresa.php" class="toggle-btn empresa active">Empresa</a>
            </div>

            <h1 class="page-title">Criar conta</h1>

            <form action="../php/cadastro_empresa.php" method="POST" class="register-form" id="formCadastro">
                
                <div class="form-columns">
                    <div class="form-column">
                        <div class="input-group">
                            <label for="nome">Nome:*</label>
                            <input type="text" id="nome" name="nome" required>
                        </div>

                        <div class="input-group">
                            <label for="cnpj">CNPJ:*</label>
                            <input type="text" id="cnpj" name="cnpj" placeholder="00.000.000/0000-00" required>
                        </div>

                        <div class="input-group">
                            <label for="cep">CEP:*</label>
                            <input type="text" id="cep" name="cep" placeholder="00000-000" required>
                        </div>

                        <div class="input-group password-wrapper">
                            <label for="confirme_senha">Confirme a sua senha:*</label>
                            <div class="input-icon-container">
                                <input type="password" id="confirme_senha" name="confirme_senha" required>
                                <img src="../img/olho_fechado.png" class="toggle-password-eye" onclick="togglePasswordVisibility('confirme_senha', this)" alt="Ocultar/Mostrar Senha">
                            </div>
                            <span id="error-match" class="error-message-text">Senhas não coincidem</span>
                        </div>
                    </div>

                    <div class="form-column">
                        <div class="input-group">
                            <label for="email">E-mail:*</label>
                            <input type="email" id="email" name="email" required>
                        </div>

                        <div class="input-group">
                            <label for="telefone">Telefone:*</label>
                            <input type="tel" id="telefone" name="telefone" placeholder="(00) 00000-0000" required>
                        </div>

                        <div class="input-group password-wrapper">
                            <label for="senha">Senha:*</label>
                            <div class="input-icon-container">
                                <input type="password" id="senha" name="senha" required>
                                <img src="../img/olho_fechado.png" class="toggle-password-eye" onclick="togglePasswordVisibility('senha', this)" alt="Ocultar/Mostrar Senha">
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
                                <span class="req-icon">⚠️</span> Pelo menos 1 caracter especial (como ! @ # $)
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-footer-action">
                    <button type="submit" class="btn-submit">Cadastrar</button>
                    <p class="login-redirect">Já tem conta? <a href="../php/login.php">Faça login</a></p>
                </div>

            </form>

            <footer class="page-footer">
                Dev<span>IN</span> | Escola Profª Alcina Dantas Feijão | © DevIN 2026. Todos os direitos reservados.
            </footer>

        </section>

        <section class="right-side">
            <a href="../php/login.php" class="btn-top-login">Login</a>
            
            <div class="mascot-container">
                <img src="../img/robocadastro.webp" alt="Robô DevIN" class="mascot-img">
            </div>
        </section>

    </div>

    <div id="status-alert-container"></div>

    <script src="../js/cadastro.js"></script>

</body>
</html>

<?php
// Função para validar os dígitos verificadores do CNPJ
function validarCNPJ($cnpj) {
    $cnpj = preg_replace('/[^0-9]/', '',$cnpj);
    if (strlen($cnpj) != 14 || preg_match('/^(\d)\1{13}$/',$cnpj)) {
        return false;
    }
    for ($i = 0, $j = 5,$soma = 0; $i < 12; $i++) {
        $soma +=$cnpj[$i] *$j;
        $j = ($j == 2) ? 9 :$j - 1;
    }
    $resto =$soma % 11;
    if ($cnpj[12] != ($resto < 2 ? 0 : 11 -$resto)) return false;

    for ($i = 0, $j = 6,$soma = 0; $i < 13; $i++) {
        $soma +=$cnpj[$i] *$j;
        $j = ($j == 2) ? 9 :$j - 1;
    }
    $resto =$soma % 11;
    return $cnpj[13] == ($resto < 2 ? 0 : 11 -$resto);
}

// Função para verificar se o CNPJ existe na base da Receita Federal
function cnpjExisteNaReceita($cnpj) {
    $cnpj = preg_replace('/[^0-9]/', '',$cnpj);
    $url = "https://brasilapi.com.br/api/cnpj/v1/" . $cnpj;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($httpCode === 200);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $host = "localhost";
    $user = "root";
    $pass = "";
    $dbname = "devin";

    $conn = new mysqli($host,$user, $pass,$dbname);

    if ($conn->connect_error) {
        die("Falha de conexão com o banco de dados.");
    }

    if ($_POST['senha'] !==$_POST['confirme_senha']) {
        echo "<script>
            document.getElementById('status-alert-container').innerHTML = \"<div class='php-toast error-toast'>As senhas não coincidem!</div>\";
        </script>";
        exit();
    }

    $cnpj = preg_replace('/[^0-9]/', '',$_POST['cnpj'] ?? '');

    if (!validarCNPJ($cnpj)) {
        echo "<script>
            document.getElementById('status-alert-container').innerHTML = \"<div class='php-toast error-toast'>CNPJ inválido! Verifique os dígitos inseridos.</div>\";
        </script>";
        exit();
    }

    if (!cnpjExisteNaReceita($cnpj)) {
        echo "<script>
            document.getElementById('status-alert-container').innerHTML = \"<div class='php-toast error-toast'>CNPJ não encontrado na base de dados da Receita Federal!</div>\";
        </script>";
        exit();
    }

    $nome = trim($_POST['nome']);
    $cep = preg_replace('/[^0-9]/', '',$_POST['cep']);
    $telefone = preg_replace('/[^0-9]/', '',$_POST['telefone']);
    $email = trim($_POST['email']);
    $senha_hash = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO Empresa (nome, cnpj, cep, email, senha_hash, telefone) VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Erro no SQL: " . $conn->error);
    }

    $stmt->bind_param("ssisss", $nome,$cnpj, $cep,$email, $senha_hash,$telefone);

    if ($stmt->execute()) {$auth = AuthController::login($email,$_POST['senha']);
        AuthController::establishSession($auth);
        header('Location: ' . AuthController::redirectByUserType('empresa'));
        exit;
    } else {
        echo "<script>
            document.getElementById('status-alert-container').innerHTML = \"<div class='php-toast error-toast'>Erro ao cadastrar: CNPJ ou E-mail já existentes.</div>\";
        </script>";
    }

    $stmt->close();$conn->close();
}
?>