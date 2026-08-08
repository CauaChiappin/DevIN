<?php
// processar.php

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "devin";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Falha de conexão com o banco de dados.");
}

$acao = isset($_GET['acao']) ? $_GET['acao'] : '';

// ==========================================
// AÇÃO 1: SOLICITAR RECUPERAÇÃO DE SENHA
// ==========================================
if ($acao === 'solicitar' && $_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    
    $tabela_usuario = "";
    $nome_usuario = "";
    $campo_id_tabela = ""; // Guardará se o ID é id_pessoa ou id_empresa

    // 1. Procura primeiro na tabela Pessoa
    // IMPORTANTE: Ajuste 'id_pessoa' e 'nome' para os nomes reais das colunas na sua tabela Pessoa
    $sql_pessoa = "SELECT id_pessoa, nome FROM Pessoa WHERE email = ?";
    $stmt = $conn->prepare($sql_pessoa);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res_pessoa = $stmt->get_result();

    if ($res_pessoa->num_rows > 0) {
        $usuario = $res_pessoa->fetch_assoc();
        $tabela_usuario = "Pessoa";
        $nome_usuario = $usuario['nome'];
        $campo_id_tabela = "id_pessoa"; // Nome da coluna de ID na tabela Pessoa
    } else {
        // 2. Se não achar em Pessoa, procura na tabela Empresa
        // IMPORTANTE: Ajuste 'id_empresa' e 'nome' para os nomes reais das colunas na sua tabela Empresa
        $sql_empresa = "SELECT id_empresa, nome FROM Empresa WHERE email = ?"; 
        $stmt_emp = $conn->prepare($sql_empresa);
        $stmt_emp->bind_param("s", $email);
        $stmt_emp->execute();
        $res_empresa = $stmt_emp->get_result();

        if ($res_empresa->num_rows > 0) {
            $usuario = $res_empresa->fetch_assoc();
            $tabela_usuario = "Empresa";
            $nome_usuario = $usuario['nome'];
            $campo_id_tabela = "id_empresa"; // Nome da coluna de ID na tabela Empresa
        }
    }

    // Se não achou o e-mail em NENHUMA das tabelas
    if (empty($tabela_usuario)) {
        echo "<script>
            alert('O e-mail pode estar incorreto ou o usuário/empresa não possui cadastro.');
            window.history.back();
        </script>";
        exit();
    }
    
    // Gera um token único e define uma expiração de 30 minutos
    $token = bin2hex(random_bytes(32));
    $expiracao = date("Y-m-d H:i:s", strtotime("+30 minutes"));

    // Salva o token na tabela correspondente (Pessoa ou Empresa)
    $sql_update = "UPDATE $tabela_usuario SET token_recuperacao = ?, token_expiracao = ? WHERE email = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sss", $token, $expiracao, $email);
    $stmt_update->execute();

    // Link que o usuário vai clicar
    $link_redefinicao = "http://localhost/DevIN/php/redefinir.html?token=" . $token;

    // --- ENVIO DO EMAIL ---
    $para = $email;
    $assunto = "Recuperacao de Senha - DevIN";
    $mensagem = "Olá, " . $nome_usuario . ".\n\nClique no link abaixo para criar uma nova senha para sua conta:\n" . $link_redefinicao;
    $headers = "From: no-reply@devin.com";

    @mail($para, $assunto, $mensagem, $headers);

    // Fallback visual para testes no XAMPP local
    echo "<div style='font-family:sans-serif; text-align:center; margin-top:50px;'>
            <h3>Solicitação de ($tabela_usuario) processada com sucesso!</h3>
            <p>Se você estiver em ambiente real, um e-mail foi enviado para <b>$email</b>.</p>
            <div style='background:#f4f4f4; padding:15px; display:inline-block; border-radius:8px; border:1px solid #ccc;'>
                <strong>Link de recuperação gerado para testes locais:</strong><br><br>
                <a href='$link_redefinicao'>$link_redefinicao</a>
            </div>
          </div>";
    exit();
}

// ==========================================
// AÇÃO 2: SALVAR A NOVA SENHA
// ==========================================
if ($acao === 'salvar' && $_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $conn->real_escape_string($_POST['token']);
    $nova_senha = $_POST['senha'];

    if (empty($token)) {
        die("Token ausente ou inválido.");
    }

    $agora = date("Y-m-d H:i:s");
    $tabela_alvo = "";
    $campo_id_alvo = "";
    $id_usuario = 0;

    // 1. Procura o token na tabela Pessoa
    $sql_p = "SELECT id_pessoa FROM Pessoa WHERE token_recuperacao = ? AND token_expiracao > ?";
    $stmt_p = $conn->prepare($sql_p);
    $stmt_p->bind_param("ss", $token, $agora);
    $stmt_p->execute();
    $res_p = $stmt_p->get_result();

    if ($res_p->num_rows > 0) {
        $user_p = $res_p->fetch_assoc();
        $tabela_alvo = "Pessoa";
        $campo_id_alvo = "id_pessoa";
        $id_usuario = $user_p['id_pessoa'];
    } else {
        // 2. Se não achar na Pessoa, procura na Empresa
        $sql_e = "SELECT id_empresa FROM Empresa WHERE token_recuperacao = ? AND token_expiracao > ?";
        $stmt_e = $conn->prepare($sql_e);
        $stmt_e->bind_param("ss", $token, $agora);
        $stmt_e->execute();
        $res_e = $stmt_e->get_result();

        if ($res_e->num_rows > 0) {
            $user_e = $res_e->fetch_assoc();
            $tabela_alvo = "Empresa";
            $campo_id_alvo = "id_empresa";
            $id_usuario = $user_e['id_empresa'];
        }
    }

    // Se o token não for achado ou já tiver expirado nas duas tabelas
    if (empty($tabela_alvo)) {
        echo "<script>
            alert('Este link de recuperação é inválido ou já expirou!');
            window.location.href = 'recuperacao.php';
        </script>";
        exit();
    }

    // Gera o Hash seguro da nova senha
    $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

    // Atualiza a senha na tabela correta e limpa os tokens de segurança
    $sql_final = "UPDATE $tabela_alvo SET senha = ?, token_recuperacao = NULL, token_expiracao = NULL WHERE $campo_id_alvo = ?";
    $stmt_final = $conn->prepare($sql_final);
    $stmt_final->bind_param("si", $nova_senha_hash, $id_usuario);

    if ($stmt_final->execute()) {
        echo "<script>
            alert('Senha de " . ($tabela_alvo == "Pessoa" ? "Usuário" : "Empresa") . " atualizada com sucesso! A senha antiga foi invalidada.');
            window.location.href = 'login.html'; 
        </script>";
    } else {
        echo "<script>
            alert('Erro ao atualizar a senha no banco de dados.');
            window.history.back();
        </script>";
    }

    $conn->close();
}
?>