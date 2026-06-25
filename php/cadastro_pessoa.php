



<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $host = "localhost";
    $user = "root";
    $pass = "";
    $dbname = "devin";

    $conn = new mysqli($host, $user, $pass, $dbname);
    if ($conn->connect_error) {
        die("Falha de conexão com o banco de dados.");
    }

    if ($_POST['senha'] !== $_POST['confirme_senha']) {
        echo "<script>
            document.getElementById('status-alert-container').innerHTML = \"<div class='php-toast error-toast'>As senhas não coincidem!</div>\";
        </script>";
        exit();
    }

    $nome = $conn->real_escape_string($_POST['nome']);
    $cpf = $conn->real_escape_string($_POST['cpf']);
    $cep = (int) preg_replace('/[^0-9]/', '', $_POST['cep']);
    $telefone = (int) preg_replace('/[^0-9]/', '', $_POST['telefone']);
    $email = $conn->real_escape_string($_POST['email']);
    
    // Guardamos a senha pura em uma variável antes de criptografar para fazer a validação do Admin
    $senha_pura = $_POST['senha_hash'];
    $senha_hash = password_hash($senha_pura, PASSWORD_DEFAULT);

    $sql = "INSERT INTO Pessoa (nome, cpf, cep, email, senha_hash, telefone) VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssisss", $nome, $cpf, $cep, $email, $senha_hash, $telefone);

    if ($stmt->execute()) {
        // VERIFICAÇÃO SE A SENHA CADASTRADA É A DO ADMIN
        if ($senha_pura === "admin@CAJE") {
            // Se for a senha secreta, mostra o Toast de sucesso e redireciona direto para a página do ADM após 1.5 segundos
            echo "<script>
                document.getElementById('status-alert-container').innerHTML = \"<div class='php-toast success-toast'>Conta de Administrador criada com sucesso! Redirecionando...</div>\";
                setTimeout(function() {
                    window.location.href = 'pagina_adm.php';
                }, 1500);
            </script>";
        } else {
            // Se for um usuário comum, mostra apenas o Toast de sucesso normal
            echo "<script>
                document.getElementById('status-alert-container').innerHTML = \"<div class='php-toast success-toast'>Conta criada com sucesso!</div>\";
            </script>";
        }
    } else {
        echo "<script>
            document.getElementById('status-alert-container').innerHTML = \"<div class='php-toast error-toast'>Erro ao cadastrar: CPF ou E-mail já existentes.</div>\";
        </script>";
    }

    $stmt->close();
    $conn->close();
}
?>
    
