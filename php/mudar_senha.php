<?php
require_once __DIR__ . '/config/database.php';

// EDITAR DADOS AQUI:
$email = 'joao.sousa2@scseduca.com';
$novaSenha = '452830pt';

try {
    $conn = getDatabaseConnection();
    
    // Gera o novo hash da senha
    $novoHash = password_hash($novaSenha, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("UPDATE administrador SET senha_hash = ? WHERE email = ?");
    $stmt->bind_param('ss', $novoHash, $email);
    $stmt->execute();
    
    if ($stmt->affected_rows > 0) {
        echo "<h2 style='color: green;'>Senha alterada com sucesso para {$email}!</h2>";
    } else {
        echo "<h2 style='color: orange;'>Nenhum registro alterado. Verifique se o e-mail está correto no banco.</h2>";
    }
    
    $stmt->close();
    $conn->close();
    echo "<a href='login.php'>Ir para o Login</a>";
    
} catch (Throwable $e) {
    echo "<h2 style='color: red;'>Erro: " . $e->getMessage() . "</h2>";
}