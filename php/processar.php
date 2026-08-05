<?php
// processar.php
session_start();

require_once __DIR__ . '/php/config/database.php';
require_once __DIR__ . '/MailerHelper.php';

$acao = $_POST['acao'] ?? '';

if ($acao === 'solicitar_recuperacao') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['erro_recuperacao'] = "Por favor, informe um e-mail válido.";
        header('Location: recuperacao.php');
        exit;
    }

    $conn = getDatabaseConnection();
    
    // Procura na tabela pessoa
    $stmtPessoa = $conn->prepare("SELECT id_pessoa AS id, nome, 'pessoa' AS tipo FROM pessoa WHERE email = ?");
    $stmtPessoa->bind_param('s', $email);
    $stmtPessoa->execute();
    $usuario = $stmtPessoa->get_result()->fetch_assoc();
    $stmtPessoa->close();

    // Se não encontrou em pessoa, procura em empresa
    if (!$usuario) {
        $stmtEmpresa = $conn->prepare("SELECT id_empresa AS id, nome, 'empresa' AS tipo FROM empresa WHERE email = ?");
        $stmtEmpresa->bind_param('s', $email);
        $stmtEmpresa->execute();
        $usuario = $stmtEmpresa->get_result()->fetch_assoc();
        $stmtEmpresa->close();
    }

    if ($usuario) {
        // Gera um token seguro
        $token = bin2hex(random_bytes(32));
        
        $tabela   = ($usuario['tipo'] === 'pessoa') ? 'pessoa' : 'empresa';
        $colunaId = ($usuario['tipo'] === 'pessoa') ? 'id_pessoa' : 'id_empresa';

        // Salva o token com 1 hora de validade
        $stmtToken = $conn->prepare("
            UPDATE {$tabela} 
            SET token_recuperacao = ?, token_expiracao = NOW() + INTERVAL 1 HOUR 
            WHERE {$colunaId} = ?
        ");
        $stmtToken->bind_param('si', $token, $usuario['id']);
        $stmtToken->execute();
        $stmtToken->close();

        // Monta o e-mail
        $linkRedefinicao = "http://localhost/DevIN/redefinir.php?token=" . $token;
        $assunto = "🔐 Redefinição de Senha - DevIN";
        $corpoHtml = "
            <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f6f9;'>
                <div style='max-width: 500px; margin: 0 auto; background: #ffffff; padding: 25px; border-radius: 8px;'>
                    <h2 style='color: #2b56f5;'>Olá, " . htmlspecialchars($usuario['nome']) . "!</h2>
                    <p>Recebemos uma solicitação para redefinir a senha da sua conta no <strong>DevIN</strong>.</p>
                    <p>Clique no botão abaixo para criar uma nova senha (este link expira em 1 hora):</p>
                    <div style='text-align: center; margin: 25px 0;'>
                        <a href='{$linkRedefinicao}' style='background-color: #2b56f5; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;'>Redefinir Minha Senha</a>
                    </div>
                    <p style='color: #777; font-size: 12px;'>Se você não solicitou esta alteração, desconsidere este e-mail.</p>
                </div>
            </div>
        ";

        MailerHelper::enviar($email, $usuario['nome'], $assunto, $corpoHtml);
    }

    $conn->close();

    // Mensagem genérica para proteção de privacidade
    $_SESSION['sucesso_recuperacao'] = "Se o e-mail informado estiver cadastrado, você receberá o link de redefinição em instantes.";
    header('Location: recuperacao.php');
    exit;

} elseif ($acao === 'redefinir_senha') {
    $token     = trim($_POST['token'] ?? '');
    $novaSenha = $_POST['nova_senha'] ?? '';
    $confSenha = $_POST['confirmar_senha'] ?? '';

    if (empty($token) || empty($novaSenha) || $novaSenha !== $confSenha) {
        $_SESSION['erro_redefinir'] = "As senhas não coincidem ou os dados são inválidos.";
        header("Location: redefinir.php?token=" . urlencode($token));
        exit;
    }

    $conn = getDatabaseConnection();
    $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);

    // Tenta atualizar a senha na tabela pessoa
    $stmtPessoa = $conn->prepare("
        UPDATE pessoa 
        SET senha = ?, token_recuperacao = NULL, token_expiracao = NULL 
        WHERE token_recuperacao = ? AND token_expiracao > NOW()
    ");
    $stmtPessoa->bind_param('ss', $senhaHash, $token);
    $stmtPessoa->execute();
    $afetados = $stmtPessoa->affected_rows;
    $stmtPessoa->close();

    if ($afetados === 0) {
        // Tenta atualizar a senha na tabela empresa
        $stmtEmpresa = $conn->prepare("
            UPDATE empresa 
            SET senha = ?, token_recuperacao = NULL, token_expiracao = NULL 
            WHERE token_recuperacao = ? AND token_expiracao > NOW()
        ");
        $stmtEmpresa->bind_param('ss', $senhaHash, $token);
        $stmtEmpresa->execute();
        $afetados = $stmtEmpresa->affected_rows;
        $stmtEmpresa->close();
    }

    $conn->close();

    if ($afetados > 0) {
        $_SESSION['sucesso_login'] = "Senha redefinida com sucesso! Faça seu login.";
        header('Location: login.php');
        exit;
    } else {
        $_SESSION['erro_redefinir'] = "O link de redefinição é inválido ou já expirou. Solicite um novo.";
        header('Location: recuperacao.php');
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}