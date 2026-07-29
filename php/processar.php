<?php
// processar.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 1. Inclusão das bibliotecas do PHPMailer na pasta renomeada
require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

// 2. Conexão com o Banco de Dados MySQL
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
if ($acao === 'solicitar' && $_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    
    $tabela_usuario = "";
    $nome_usuario = "";
    $campo_id_tabela = "";

    // Procura primeiro na tabela 'pessoa'
    $sql_pessoa = "SELECT id_pessoa, nome FROM pessoa WHERE email = ?";
    $stmt = $conn->prepare($sql_pessoa);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res_pessoa = $stmt->get_result();

    if ($res_pessoa->num_rows > 0) {
        $usuario = $res_pessoa->fetch_assoc();
        $tabela_usuario = "pessoa";
        $nome_usuario = $usuario['nome'];
        $campo_id_tabela = "id_pessoa";
    } else {
        // Se não encontrar em pessoa, procura na tabela 'empresa'
        $sql_empresa = "SELECT id_empresa, nome FROM empresa WHERE email = ?"; 
        $stmt_emp = $conn->prepare($sql_empresa);
        $stmt_emp->bind_param("s", $email);
        $stmt_emp->execute();
        $res_empresa = $stmt_emp->get_result();

        if ($res_empresa->num_rows > 0) {
            $usuario = $res_empresa->fetch_assoc();
            $tabela_usuario = "empresa";
            $nome_usuario = $usuario['nome'];
            $campo_id_tabela = "id_empresa";
        }
    }

    // Se o e-mail não estiver em nenhuma das duas tabelas
    if (empty($tabela_usuario)) {
        echo "<script>
            alert('O e-mail informado não está cadastrado no sistema.');
            window.history.back();
        </script>";
        exit();
    }
    
    // Gera o token único de segurança e define o prazo de 30 minutos
    $token = bin2hex(random_bytes(32));
    $expiracao = date("Y-m-d H:i:s", strtotime("+30 minutes"));

    // Registra o token no banco de dados na conta correspondente
    $sql_update = "UPDATE $tabela_usuario SET token_recuperacao = ?, token_expiracao = ? WHERE email = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sss", $token, $expiracao, $email);
    $stmt_update->execute();

    // Monta o link para o formulário de redefinição de senha
    $link_redefinicao = "http://localhost/DevIN/php/redefinir.php?token=" . $token;

    // --- DISPARO DO E-MAIL VIA PHPMailer ---
    $mail = new PHPMailer(true);

    try {
        // Configurações do servidor SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        
        // ⚙️ Altere aqui para o e-mail do bot e a Senha de App gerada no Google
        $mail->Username   = 'devinalcina@gmail.com';
        $mail->Password   = 'Devin3D123456@'; 
        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        // Nome e e-mail exibidos ao destinatário
        $mail->setFrom('devinalcina@gmail.com', 'DevIN Suporte');
        $mail->addAddress($email, $nome_usuario);

        // Assunto e layout HTML da mensagem
        $mail->isHTML(true);
        $mail->Subject = 'Recuperação de Senha - DevIN';
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; max-width: 500px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 12px; padding: 25px;'>
                <h2 style='color: #004595; text-align: center;'>Dev<span style='color:#000;'>IN</span></h2>
                <hr style='border: 0; border-top: 1px solid #eee; margin: 15px 0;'>
                <p>Olá, <strong>{$nome_usuario}</strong>!</p>
                <p>Recebemos uma solicitação para redefinir a senha da sua conta na plataforma <strong>DevIN</strong>.</p>
                <p style='text-align: center; margin: 25px 0;'>
                    <a href='{$link_redefinicao}' style='background-color: #004595; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 20px; font-weight: bold; display: inline-block;'>
                        Redefinir Minha Senha
                    </a>
                </p>
                <p style='font-size: 12px; color: #777;'>
                    Se você não solicitou essa alteração, basta ignorar este e-mail. Este link é válido por 30 minutos.
                </p>
            </div>
        ";

        $mail->send();

        echo "<script>
            alert('Um e-mail de recuperação foi enviado para $email!');
            window.location.href = 'login.php';
        </script>";

    } catch (Exception $e) {
        // Fallback local: exibe o link direto na tela caso o SMTP ainda não esteja configurado
        echo "<div style='font-family:sans-serif; text-align:center; margin-top:50px;'>
                <h3>Solicitação realizada com sucesso!</h3>
                <p>Ambiente local detectado. Clique no link abaixo para realizar o teste de redefinição:</p>
                <div style='background:#f4f4f4; padding:15px; display:inline-block; border-radius:8px; border:1px solid #ccc; margin-top:10px;'>
                    <a href='$link_redefinicao'>$link_redefinicao</a>
                </div>
              </div>";
    }
    exit();
}

// ==========================================
// AÇÃO 2: SALVAR A NOVA SENHA
// ==========================================
if ($acao === 'salvar' && $_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $conn->real_escape_string($_POST['token']);
    $nova_senha = $_POST['senha'];

    if (empty($token)) {
        die("Token ausente ou inválido.");
    }

    $agora = date("Y-m-d H:i:s");
    $tabela_alvo = "";
    $campo_id_alvo = "";
    $id_usuario = 0;

    // 1. Procura o token válido na tabela 'pessoa'
    $sql_p = "SELECT id_pessoa FROM pessoa WHERE token_recuperacao = ? AND token_expiracao > ?";
    $stmt_p = $conn->prepare($sql_p);
    $stmt_p->bind_param("ss", $token, $agora);
    $stmt_p->execute();
    $res_p = $stmt_p->get_result();

    if ($res_p->num_rows > 0) {
        $user_p = $res_p->fetch_assoc();
        $tabela_alvo = "pessoa";
        $campo_id_alvo = "id_pessoa";
        $id_usuario = $user_p['id_pessoa'];
    } else {
        // 2. Se não estiver em pessoa, procura na tabela 'empresa'
        $sql_e = "SELECT id_empresa FROM empresa WHERE token_recuperacao = ? AND token_expiracao > ?";
        $stmt_e = $conn->prepare($sql_e);
        $stmt_e->bind_param("ss", $token, $agora);
        $stmt_e->execute();
        $res_e = $stmt_e->get_result();

        if ($res_e->num_rows > 0) {
            $user_e = $res_e->fetch_assoc();
            $tabela_alvo = "empresa";
            $campo_id_alvo = "id_empresa";
            $id_usuario = $user_e['id_empresa'];
        }
    }

    // Se o token for inválido ou já estiver expirado
    if (empty($tabela_alvo)) {
        echo "<script>
            alert('Este link de recuperação é inválido ou expirou!');
            window.location.href = 'recuperacao.php';
        </script>";
        exit();
    }

    // Gera o Hash seguro com BCRYPT
    $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

    // Salva na coluna 'senha_hash' e remove o token para não ser reutilizado
    $sql_final = "UPDATE $tabela_alvo SET senha_hash = ?, token_recuperacao = NULL, token_expiracao = NULL WHERE $campo_id_alvo = ?";
    $stmt_final = $conn->prepare($sql_final);
    $stmt_final->bind_param("si", $nova_senha_hash, $id_usuario);

    if ($stmt_final->execute()) {
        echo "<script>
            alert('Senha alterada com sucesso! Você já pode entrar com sua nova senha.');
            window.location.href = 'login.php'; 
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