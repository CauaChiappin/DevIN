<?php
ob_start();

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Verifica se existe o ID da pessoa na sessão
if (!isset($_SESSION['id_pessoa'])) {
    echo "<script>
            alert('Sessão expirada ou cadastro não iniciado. Por favor, crie sua conta primeiro.');
            window.location.href = 'cadastro_pessoa.php';
          </script>";
    exit;
}

// 1. Inclui a conexão com o banco de dados
if (file_exists(__DIR__ . '/config/database.php')) {
    require_once __DIR__ . '/config/database.php';
} elseif (file_exists(__DIR__ . '/../config/database.php')) {
    require_once __DIR__ . '/../config/database.php';
}

$idPessoa = $_SESSION['id_pessoa'];
$nomeUsuario = $_SESSION['pessoa_nome'] ?? 'Candidato';

// 2. Processamento do Formulário via POST
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

    // Recebe e sanitiza os campos do currículo
    $nomeSocial   = trim($_POST['nome_social'] ?? '');
    $escolaridade = trim($_POST['grau_de_escolaridade'] ?? '');
    $cursos       = trim($_POST['cursos'] ?? '');
    $experiencia  = trim($_POST['experiencia'] ?? '');
    $idiomas      = trim($_POST['idiomas'] ?? '');

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        // Insere o currículo associado ao id_pessoa
        $sql = "INSERT INTO curriculo (id_pessoa, nome_social, grau_de_escolaridade, cursos, experiencia, idiomas) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssss", $idPessoa, $nomeSocial, $escolaridade, $cursos, $experiencia, $idiomas);
        $stmt->execute();

        // Limpa a sessão temporária de cadastro
        unset($_SESSION['id_pessoa']);
        unset($_SESSION['pessoa_nome']);

        // Exibe o alerta final de sucesso e redireciona para a tela de login
        echo "<script>
                alert('Cadastro concluído com sucesso! Faça login para acessar sua conta.');
                window.location.href = 'login.php';
              </script>";
        exit;

    } catch (mysqli_sql_exception $e) {
        echo "<script>
                alert('Erro ao salvar o currículo no banco de dados. Tente novamente.');
                window.history.back();
              </script>";
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
    <title>DevIN | Cadastrar Currículo</title>
    <link rel="stylesheet" href="../css/curriculo.css">
    <link rel="stylesheet" href="../css/cadastrostyle.css">
</head>
<body>

    <div class="main-container">
        
        <section class="left-side">
            
            <div class="brand-logo">
                <a href="../php/index.php">Dev<span>IN</span></a>
            </div>

            <h1 class="page-title">Cadastrar Currículo</h1>
            <p class="subtitle">Olá, <strong><?= htmlspecialchars($nomeUsuario) ?></strong>! Preencha as informações do seu currículo para finalizar.</p>

            <form action="cadastrar_curriculo.php" method="POST" class="register-form" id="formCurriculo">
                
                <div class="form-columns">
                    <div class="form-column">
                        
                        <div class="input-group">
                            <label for="nome_social">Nome Social (Opcional):</label>
                            <input type="text" id="nome_social" name="nome_social" placeholder="Como prefere ser chamado(a)">
                        </div>

                        <div class="input-group">
                            <label for="grau_de_escolaridade">Grau de Escolaridade:*</label>
                            <select id="grau_de_escolaridade" name="grau_de_escolaridade" required>
                                <option value="">Selecione...</option>
                                <option value="Ensino Médio Incompleto">Ensino Médio Incompleto</option>
                                <option value="Ensino Médio Completo">Ensino Médio Completo</option>
                                <option value="Ensino Técnico">Ensino Técnico</option>
                                <option value="Superior Incompleto">Superior Incompleto</option>
                                <option value="Superior Completo">Superior Completo</option>
                                <option value="Pós-graduação / Especialização">Pós-graduação / Especialização</option>
                            </select>
                        </div>

                        <div class="input-group">
                            <label for="idiomas">Idiomas:</label>
                            <input type="text" id="idiomas" name="idiomas" placeholder="Ex: Inglês (Intermediário), Espanhol (Básico)">
                        </div>

                    </div>

                    <div class="form-column">
                        
                        <div class="input-group">
                            <label for="cursos">Cursos e Certificações:</label>
                            <textarea id="cursos" name="cursos" rows="3" placeholder="Descreva seus cursos relevantes..."></textarea>
                        </div>

                        <div class="input-group">
                            <label for="experiencia">Experiência Profissional:</label>
                            <textarea id="experiencia" name="experiencia" rows="3" placeholder="Descreva suas experiências de trabalho..."></textarea>
                        </div>

                    </div>
                </div>

                <div class="form-footer-action">
                    <button type="submit" class="btn-submit">Finalizar Cadastro</button>
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

</body>
</html>