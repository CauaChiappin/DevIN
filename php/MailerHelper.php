<?php
// php/MailerHelper.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

class MailerHelper {
    public static function enviar($destinatarioEmail, $destinatarioNome, $assunto, $corpoHtml) {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'devin.alcinabot@gmail.com';
            $mail->Password   = 'fxrp qgxe izqo rncx'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom('devin.alcinabot@gmail.com', 'DevIN Suporte');
            $mail->addAddress($destinatarioEmail, $destinatarioNome);

            $mail->isHTML(true);
            $mail->Subject = $assunto;
            $mail->Body    = $corpoHtml;

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Erro no envio do e-mail: " . $mail->ErrorInfo);
            return false;
        }
    }
}
?>