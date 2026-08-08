<?php
ob_start();
session_start();

// Barreira de autenticação
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header('Location: login.php?erro=Faça login para continuar');
    exit;
}

// Garante que apenas usuários do tipo 'pessoa' preencham
if (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] !== 'pessoa') {
    header('Location: login.php');
    exit;
}

$mensagemErro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = "localhost";
    $user = "root";
    $pass = "";
    $dbname = "devin";

    $conn = new mysqli($host, $user, $pass, $dbname);

    if ($conn->connect_error) {
        $mensagemErro = "Falha de conexão com o banco de dados: " . $conn->connect_error;
    } else {
        $idPessoa = $_SESSION['usuario_id'];
        $nome_social = trim($_POST['nome_social'] ?? '');
        $grau_escolaridade = trim($_POST['grau_de_escolaridade'] ?? '');
        $cursos = trim($_POST['cursos'] ?? '');
        $experiencia = trim($_POST['experiencia'] ?? '');
        $idiomas = trim($_POST['idiomas'] ?? '');

        // Prepara a gravação mantendo a integridade com a tabela 'curriculo' do seu MySQL
        $sql = "INSERT INTO curriculo 
                (id_pessoa, nome_social, grau_de_escolaridade, cursos, experiencia, idiomas) 
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("isssss", $idPessoa, $nome_social, $grau_escolaridade, $cursos, $experiencia, $idiomas);

            if ($stmt->execute()) {
                header('Location: dashboard_pessoa.php');
                exit;
            } else {
                $mensagemErro = "Erro ao salvar currículo: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $mensagemErro = "Erro na query SQL: " . $conn->error;
        }

        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevIN | Preencher Currículo</title>
    <link rel="stylesheet" href="../css/curriculo.css">
</head>
<body>

    <div class="curriculo-card">

        <?php if (!empty($mensagemErro)): ?>
            <div class="msg-erro-box">
                <?php echo htmlspecialchars($mensagemErro, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form action="cadastrar_curriculo.php" method="POST">
            
            <div class="form-group">
                <label for="nome_social">Nome social</label>
                <input type="text" id="nome_social" name="nome_social">
            </div>

            <div class="form-group">
                <label for="objetivo_profissional">Objetivo Profissional<span class="asterisk">*</span></label>
                <input type="text" id="objetivo_profissional" name="objetivo_profissional" required>
            </div>

            <div class="form-group">
                <label for="grau_de_escolaridade">Grau de Escolaridade<span class="asterisk">*</span></label>
                <input type="text" id="grau_de_escolaridade" name="grau_de_escolaridade" required>
            </div>

            <div class="form-group">
                <label for="cursos">Cursos</label>
                <input type="text" id="cursos" name="cursos">
            </div>

            <div class="form-group">
                <label for="experiencia">Experiência<span class="asterisk">*</span></label>
                <input type="text" id="experiencia" name="experiencia" required>
            </div>

            <div class="form-group">
                <label for="idiomas">Idiomas</label>
                <input type="text" id="idiomas" name="idiomas">
            </div>

            <div class="button-container">
                <button type="submit" class="btn-enviar">Enviar</button>
            </div>

        </form>
    </div>

    <footer>
        DevIN | Escola Profª Alcina Dantas Feijão | © DevIN 2026. Todos os direitos reservados.
    </footer>

</body>
</html>