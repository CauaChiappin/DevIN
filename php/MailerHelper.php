<?php
// MailerHelper.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/php/PHPMailer/src/Exception.php';
require_once __DIR__ . '/php/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/php/PHPMailer/src/SMTP.php';

class MailerHelper
{
    private static function getMailer(): PHPMailer
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'seu_email_devin@gmail.com'; // Altere para seu e-mail
        $mail->Password   = 'sua_senha_de_app';        // Altere para sua senha de app
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        $mail->setFrom('seu_email_devin@gmail.com', 'Plataforma DevIN');

        return $mail;
    }

    /**
     * Método genérico para envio de e-mails
     */
    public static function enviar(string $destinatarioEmail, string $destinatarioNome, string $assunto, string $corpoHtml): bool
    {
        try {
            $mail = self::getMailer();
            $mail->addAddress($destinatarioEmail, $destinatarioNome);
            $mail->isHTML(true);
            $mail->Subject = $assunto;
            $mail->Body    = $corpoHtml;

            return $mail->send();
        } catch (Exception $e) {
            error_log("Erro ao enviar e-mail: " . $e->getMessage());
            return false;
        }
    }

    public static function enviarConfirmacaoCadastroCurriculo(string $emailCandidato, string $nomeCandidato): bool
    {
        $assunto = '🎉 Cadastro e Currículo Concluídos - DevIN';
        $corpo = "
            <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f6f9;'>
                <div style='max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; padding: 30px;'>
                    <h2 style='color: #2b56f5;'>Olá, {$nomeCandidato}!</h2>
                    <p>Parabéns! Seu cadastro e currículo foram concluídos com sucesso no <strong>DevIN</strong>.</p>
                </div>
            </div>";
        return self::enviar($emailCandidato, $nomeCandidato, $assunto, $corpo);
    }

    public static function enviarLembreteCurriculoPendente(string $emailCandidato, string $nomeCandidato): bool
    {
        $assunto = '⏳ Falta pouco! Complete seu currículo no DevIN';
        $corpo = "
            <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f6f9;'>
                <div style='max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; padding: 30px;'>
                    <h2 style='color: #e67e22;'>Olá, {$nomeCandidato}!</h2>
                    <p>Você iniciou seu cadastro há mais de 1 hora. Finalize seu currículo para liberar seu perfil para as empresas!</p>
                </div>
            </div>";
        return self::enviar($emailCandidato, $nomeCandidato, $assunto, $corpo);
    }

    public static function notificarEmpresasNovoCandidato(mysqli $conn, string $nomeCandidato): void
    {
        $query = "SELECT nome, email FROM empresa";
        $result = $conn->query($query);

        if ($result && $result->num_rows > 0) {
            while ($empresa = $result->fetch_assoc()) {
                $assunto = '🔔 Novo Candidato Disponível - DevIN';
                $corpo = "
                    <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f6f9;'>
                        <div style='max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; padding: 30px;'>
                            <h2 style='color: #2b56f5;'>Olá, {$empresa['nome']}!</h2>
                            <p>O candidato <strong>{$nomeCandidato}</strong> acabou de concluir o currículo na plataforma.</p>
                        </div>
                    </div>";
                self::enviar($empresa['email'], $empresa['nome'], $assunto, $corpo);
            }
        }
    }
}