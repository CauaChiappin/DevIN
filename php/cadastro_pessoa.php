<?php
ob_start();

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/controllers/AuthController.php';

if (file_exists(__DIR__ . '/config/database.php')) {
    require_once __DIR__ . '/config/database.php';
} elseif (file_exists(__DIR__ . '/../config/database.php')) {
    require_once __DIR__ . '/../config/database.php';
}

// Função para validar os dígitos verificadores do CPF
function validarCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf) != 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
        return false;
    }
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {
        $conn = getDatabaseConnection();
    } catch (Exception $e) {
        echo "<script>
                alert('Erro ao conectar com o banco de dados.');
                window.history.back();
              </script>";
        exit;
    }

    $nome          = trim($_POST['nome'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $cpf           = trim($_POST['cpf'] ?? '');
    $telefone      = trim($_POST['telefone'] ?? '');
    $cep           = trim($_POST['cep'] ?? '');
    $senha         = $_POST['senha'] ?? '';
    $confirmeSenha = $_POST['confirme_senha'] ?? '';

    if (!validarCPF($cpf)) {
        echo "<script>
                alert('O CPF informado é inválido!');
                window.history.back();
              </script>";
        exit;
    }

    if (!empty($confirmeSenha) && $senha !== $confirmeSenha) {
        echo "<script>
                alert('As senhas não coincidem!');
                window.history.back();
              </script>";
        exit;
    }

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        // Insere salvando a data atual em created_at e definindo lembrete_enviado = 0
        $sql = "INSERT INTO pessoa (nome, email, cpf, telefone, cep, senha_hash, created_at, lembrete_enviado) 
                VALUES (?, ?, ?, ?, ?, ?, NOW(), 0)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $nome, $email, $cpf, $telefone, $cep, $senhaHash);
        $stmt->execute();

        // Pega o ID da pessoa recém-cadastrada e salva na sessão para o preenchimento do currículo
        $_SESSION['id_pessoa']   = $stmt->insert_id;
        $_SESSION['pessoa_nome'] = $nome;

        // Redireciona DIRETO para a página de formulário do currículo
        header("Location: cadastrar_curriculo.php");
        exit;

    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() === 1062) {
            echo "<script>
                    alert('Atenção: Este CPF ou E-mail já está cadastrado no sistema!');
                    window.history.back();
                  </script>";
        } else {
            echo "<script>
                    alert('Erro ao salvar no banco de dados. Tente novamente.');
                    window.history.back();
                  </script>";
        }
        exit;
    } finally {
        if (isset($conn) && $conn instanceof mysqli) {
            $conn->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevIN | Criar Conta Pessoal</title>
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link rel="icon" type="image/png" href="../img/favicon.png">
    <link rel="stylesheet" href="../css/cadastrostyle.css">
</head>
<body>

    <div class="main-container">
        <section class="left-side">
            <div class="brand-logo">
                <a href="../php/index.php">Dev<span>IN</span></a>
            </div>

            <div class="toggle-container">
                <a href="cadastro_pessoa.php" class="toggle-btn pessoal active">Pessoal</a>
                <span class="toggle-divider">OU</span>
                <a href="cadastro_empresa.php" class="toggle-btn empresa">Empresa</a>
            </div>

            <h1 class="page-title">Criar conta</h1>

            <form action="cadastro_pessoa.php" method="POST" class="register-form" id="formCadastro">
                <div class="form-columns">
                    <div class="form-column">
                        <div class="input-group">
                            <label for="nome">Nome:*</label>
                            <input type="text" id="nome" name="nome" required>
                        </div>

                        <div class="input-group">
                            <label for="cpf">CPF:*</label>
                            <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" required>
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
                    <p class="login-redirect">Já tem conta? <a href="login.php">Faça login</a></p>
                </div>
            </form>

            <footer class="page-footer">
                Dev<span>IN</span> | Escola Profª Alcina Dantas Feijão | © DevIN 2026. Todos os direitos reservados.
            </footer>
        </section>

        <section class="right-side">
            <a href="login.php" class="btn-top-login">Login</a>
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $host = "localhost";
    $user = "root";
    $pass = "";
    $dbname = "devin";

    $conn = new mysqli($host, $user, $pass, $dbname);

    if ($conn->connect_error) {
        die("Falha de conexão com o banco de dados: " . $conn->connect_error);
    }

    if ($_POST['senha'] !== $_POST['confirme_senha']) {
        echo "<script>
            document.getElementById('status-alert-container').innerHTML =
            \"<div class='php-toast error-toast'>As senhas não coincidem!</div>\";
        </script>";
        exit();
    }

    $nome = trim($_POST['nome']);
    $cpf = trim($_POST['cpf']);
    $cep = preg_replace('/[^0-9]/', '', $_POST['cep']);
    $telefone = preg_replace('/[^0-9]/', '', $_POST['telefone']);
    $email = trim($_POST['email']);

    $senha_pura = $_POST['senha'];
    $senha_hash = password_hash($senha_pura, PASSWORD_DEFAULT);

    $sql = "INSERT INTO pessoa
            (nome, cpf, cep, email, senha_hash, telefone)
            VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Erro no SQL: " . $conn->error);
    }

    $stmt->bind_param(
        "ssisss",
        $nome,
        $cpf,
        $cep,
        $email,
        $senha_hash,
        $telefone
    );

    if ($stmt->execute()) {

        $auth = AuthController::login($email, $senha_pura);
        AuthController::establishSession($auth);
        
        // MUDANÇA: Redireciona para o formulário de cadastro de currículo
        header('Location: cadastrar_curriculo.php');
        exit;

    } else {

        echo "<script>
            alert('Erro ao cadastrar: " . addslashes($stmt->error) . "');
        </script>";

    }

    $stmt->close();
    $conn->close();
}
?>
</html>
