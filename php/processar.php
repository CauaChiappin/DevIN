<?php
// Limpa saídas indesejadas que quebram o JSON
ob_start();
error_reporting(0);
ini_set('display_errors', 0);
mysqli_report(MYSQLI_REPORT_OFF);

header('Content-Type: application/json; charset=utf-8');

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "devin";

$conn = @new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    ob_clean();
    echo json_encode(["success" => false, "message" => "Erro de conexão com o banco de dados."]);
    exit();
}

$acao = $_GET['acao'] ?? '';
$dadosInput = json_decode(file_get_contents('php://input'), true);

// ==========================================
// AÇÃO 1: SOLICITAR RECUPERAÇÃO DE SENHA
// ==========================================
if ($acao === 'solicitar' && $_SERVER["REQUEST_METHOD"] === "POST") {
    $email = filter_var($dadosInput['email'] ?? '', FILTER_VALIDATE_EMAIL);

    if (!$email) {
        ob_clean();
        echo json_encode(["success" => false, "message" => "Informe um e-mail válido."]);
        exit();
    }

    $tabela_usuario = "";
    $nome_usuario = "";

    // Busca na tabela Pessoa
    $stmt = $conn->prepare("SELECT id_pessoa, nome FROM Pessoa WHERE email = ?");
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res_pessoa = $stmt->get_result();

        if ($res_pessoa && $res_pessoa->num_rows > 0) {
            $usuario = $res_pessoa->fetch_assoc();
            $tabela_usuario = "Pessoa";
            $nome_usuario = $usuario['nome'];
        }
    }

    // Se não encontrou em Pessoa, busca em Empresa
    if (empty($tabela_usuario)) {
        $stmt_emp = $conn->prepare("SELECT id_empresa, nome FROM Empresa WHERE email = ?");
        if ($stmt_emp) {
            $stmt_emp->bind_param("s", $email);
            $stmt_emp->execute();
            $res_empresa = $stmt_emp->get_result();

            if ($res_empresa && $res_empresa->num_rows > 0) {
                $usuario = $res_empresa->fetch_assoc();
                $tabela_usuario = "Empresa";
                $nome_usuario = $usuario['nome'];
            }
        }
    }

    if (empty($tabela_usuario)) {
        ob_clean();
        echo json_encode(["success" => false, "message" => "O e-mail informado não possui cadastro."]);
        exit();
    }

    $token = bin2hex(random_bytes(32));
    $expiracao = date("Y-m-d H:i:s", strtotime("+30 minutes"));

    $sql_update = "UPDATE $tabela_usuario SET token_recuperacao = ?, token_expiracao = ? WHERE email = ?";
    $stmt_update = $conn->prepare($sql_update);
    
    if (!$stmt_update) {
        ob_clean();
        echo json_encode(["success" => false, "message" => "Erro: As colunas 'token_recuperacao' ou 'token_expiracao' não existem na tabela $tabela_usuario."]);
        exit();
    }

    $stmt_update->bind_param("sss", $token, $expiracao, $email);
    $stmt_update->execute();

    $protocolo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $host_servidor = $_SERVER['HTTP_HOST']; 
    $link_redefinicao = "{$protocolo}://{$host_servidor}/DevIN/php/redefinir.php?token=" . $token;

    $para = $email;
    $assunto = "Recuperacao de Senha - DevIN";
    $mensagem = "Olá, " . $nome_usuario . ".\n\nClique no link abaixo para criar uma nova senha:\n" . $link_redefinicao;
    $headers = "From: no-reply@devin.com";
    @mail($para, $assunto, $mensagem, $headers);

    ob_clean();
    echo json_encode([
        "success" => true,
        "message" => "Instruções enviadas com sucesso!",
        "link_teste" => $link_redefinicao
    ]);
    exit();
}

// ==========================================
// AÇÃO 2: SALVAR A NOVA SENHA
// ==========================================
if ($acao === 'salvar' && $_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $dadosInput['token'] ?? '';
    $nova_senha = $dadosInput['senha'] ?? '';

    if (empty($token) || empty($nova_senha)) {
        ob_clean();
        echo json_encode(["success" => false, "message" => "Token ou nova senha ausentes."]);
        exit();
    }

    $agora = date("Y-m-d H:i:s");
    $tabela_alvo = "";
    $campo_id_alvo = "";
    $id_usuario = 0;

    // Busca token em Pessoa
    $stmt_p = $conn->prepare("SELECT id_pessoa FROM Pessoa WHERE token_recuperacao = ? AND token_expiracao > ?");
    if ($stmt_p) {
        $stmt_p->bind_param("ss", $token, $agora);
        $stmt_p->execute();
        $res_p = $stmt_p->get_result();

        if ($res_p && $res_p->num_rows > 0) {
            $user_p = $res_p->fetch_assoc();
            $tabela_alvo = "Pessoa";
            $campo_id_alvo = "id_pessoa";
            $id_usuario = $user_p['id_pessoa'];
        }
    }

    // Se não encontrou em Pessoa, busca em Empresa
    if (empty($tabela_alvo)) {
        $stmt_e = $conn->prepare("SELECT id_empresa FROM Empresa WHERE token_recuperacao = ? AND token_expiracao > ?");
        if ($stmt_e) {
            $stmt_e->bind_param("ss", $token, $agora);
            $stmt_e->execute();
            $res_e = $stmt_e->get_result();

            if ($res_e && $res_e->num_rows > 0) {
                $user_e = $res_e->fetch_assoc();
                $tabela_alvo = "Empresa";
                $campo_id_alvo = "id_empresa";
                $id_usuario = $user_e['id_empresa'];
            }
        }
    }

    if (empty($tabela_alvo)) {
        ob_clean();
        echo json_encode(["success" => false, "message" => "Este link de recuperação é inválido ou já expirou."]);
        exit();
    }

    $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

    // Tenta atualizar usando a coluna 'senha_hash' (usada no cadastro.php)
    $sql_final = "UPDATE $tabela_alvo SET senha_hash = ?, token_recuperacao = NULL, token_expiracao = NULL WHERE $campo_id_alvo = ?";
    $stmt_final = $conn->prepare($sql_final);

    // Se 'senha_hash' não existir no banco, tenta a coluna 'senha'
    if (!$stmt_final) {
        $sql_final = "UPDATE $tabela_alvo SET senha = ?, token_recuperacao = NULL, token_expiracao = NULL WHERE $campo_id_alvo = ?";
        $stmt_final = $conn->prepare($sql_final);
    }

    if (!$stmt_final) {
        ob_clean();
        echo json_encode(["success" => false, "message" => "Erro na estrutura do banco: coluna 'senha_hash' ou 'senha' não encontrada."]);
        exit();
    }

    $stmt_final->bind_param("si", $nova_senha_hash, $id_usuario);

    ob_clean();
    if ($stmt_final->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Senha atualizada com sucesso! Redirecionando..."
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Erro ao atualizar a senha no banco de dados."]);
    }

    $conn->close();
    exit();
}

ob_clean();
echo json_encode(["success" => false, "message" => "Ação inválida."]);