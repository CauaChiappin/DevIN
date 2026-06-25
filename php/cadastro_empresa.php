
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
    $cnpj = $conn->real_escape_string($_POST['cnpj']);
    $cep = (int) preg_replace('/[^0-9]/', '', $_POST['cep']);
    $telefone = (int) preg_replace('/[^0-9]/', '', $_POST['telefone']);
    $email = $conn->real_escape_string($_POST['email']);
    $senha_hash = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO Empresa (nome, cnpj, cep, email, senha_hash, telefone) VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssisss", $nome, $cnpj, $cep, $email, $senha_hash, $telefone);

    if ($stmt->execute()) {
        echo "<script>
            document.getElementById('status-alert-container').innerHTML = \"<div class='php-toast success-toast'>Empresa cadastrada com sucesso!</div>\";
        </script>";
    } else {
        echo "<script>
            document.getElementById('status-alert-container').innerHTML = \"<div class='php-toast error-toast'>Erro ao cadastrar: CNPJ ou E-mail já existentes.</div>\";
        </script>";
    }

    $stmt->close();
    $conn->close();
}
?>
