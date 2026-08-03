<?php
// cron_lembrete_curriculo.php
require_once __DIR__ . '/config/database.php';

// Importe as classes do PHPMailer (ajuste o caminho conforme sua pasta)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

$conn = getDatabaseConnection();

// Busca pessoas criadas há mais de 1 hora, sem currículo e que ainda não receberam o lembrete
$sql = "SELECT p.id_pessoa, p.nome, p.email 
        FROM pessoa p
        LEFT JOIN curriculo c ON p.id_pessoa = c.id_pessoa
        WHERE c.id_pessoa IS NULL 
          AND p.created_at <= NOW() - INTERVAL 1 HOUR
          AND (p.lembrete_enviado IS NULL OR p.lembrete_enviado = 0)";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($pessoa = $result->fetch_assoc()) {
        $mail = new PHPMailer(true);

        try {
            // Configurações do Servidor SMTP (ajuste com os dados do seu e-mail)
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com'; 
            $mail->SMTPAuth   = true;
            $mail->Username   = 'seu_email@gmail.com'; 
            $mail->Password   = 'sua_senha_de_app';    
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            // Destinatário
            $mail->setFrom('seu_email@gmail.com', 'DevIN Suporte');
            $mail->addAddress($pessoa['email'], $pessoa['nome']);

            // Conteúdo do E-mail
            $mail->isHTML(true);
            $mail->Subject = 'DevIN | Conclua o seu cadastro de currículo!';
            $mail->Body    = "Olá, <b>" . htmlspecialchars($pessoa['nome']) . "</b>!<br><br>" .
                             "Notamos que você iniciou seu cadastro na plataforma DevIN, mas ainda não cadastrou seu currículo.<br>" .
                             "Complete seu perfil para ter acesso às melhores vagas disponíveis!<br><br>" .
                             "<a href='http://localhost:8080/DevIN/php/login.php'>Clique aqui para fazer login e completar</a>";

            $mail->send();

            // Marca no banco que o e-mail de lembrete já foi enviado para não enviar duplicado
            $updateSql = "UPDATE pessoa SET lembrete_enviado = 1 WHERE id_pessoa = ?";
            $stmtUpdate = $conn->prepare($updateSql);
            $stmtUpdate->bind_param("i", $pessoa['id_pessoa']);
            $stmtUpdate->execute();

            echo "Lembrete enviado com sucesso para: " . $pessoa['email'] . "<br>";

        } catch (Exception $e) {
            echo "Erro ao enviar e-mail para {$pessoa['email']}: {$mail->ErrorInfo}<br>";
        }
    }
} else {
    echo "Nenhum cadastro pendente de lembrete no momento.";
}

$conn->close();
?>