<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

class MailerHelper
{
    private static function getMailer(): PHPMailer
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST']       ?? getenv('SMTP_HOST')       ?: 'smtp.gmail.com';
        $mail->Port       = (int) ($_ENV['SMTP_PORT'] ?? getenv('SMTP_PORT')       ?: 587);
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USER']       ?? getenv('SMTP_USER')       ?: '';
        $mail->Password   = $_ENV['SMTP_PASS']       ?? getenv('SMTP_PASS')       ?: '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->CharSet    = 'UTF-8';

        $fromEmail = $_ENV['SMTP_FROM_EMAIL'] ?? getenv('SMTP_FROM_EMAIL') ?: 'contato@devin.com.br';
        $fromName  = $_ENV['SMTP_FROM_NAME']  ?? getenv('SMTP_FROM_NAME')  ?: 'DevIN';
        $mail->setFrom($fromEmail, $fromName);

        return $mail;
    }

    public static function enviarCodigoRecuperacao6(string $emailDestino, string $nomeDestino, string $codigo): bool
    {
        try {
            $mail = self::getMailer();
            $mail->addAddress($emailDestino, $nomeDestino);
            $mail->isHTML(true);
            $mail->Subject = 'DevIN | Código de Recuperação de Senha';

            $codigoFormatado = substr($codigo, 0, 3) . '-' . substr($codigo, 3, 3);
            $nomeSeguro = htmlspecialchars($nomeDestino, ENT_QUOTES, 'UTF-8');

            $mail->Body = "
                <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f6f9;'>
                    <div style='max-width: 480px; margin: 0 auto; background: #ffffff; padding: 25px; border-radius: 12px; text-align: center;'>
                        <h2 style='color: #004aad; margin-bottom: 10px;'>Dev<span style='color: #000;'>IN</span></h2>
                        <h3 style='color: #333;'>Recuperação de Senha</h3>
                        <p style='color: #555;'>Olá, <strong>{$nomeSeguro}</strong>!</p>
                        <p style='color: #555;'>Utilize o código de verificação abaixo para redefinir sua senha:</p>
                        <div style='margin: 25px 0;'>
                            <span style='background-color: #004aad; color: #ffffff; padding: 12px 24px; border-radius: 8px; font-size: 26px; font-weight: bold; letter-spacing: 5px; display: inline-block;'>
                                {$codigoFormatado}
                            </span>
                        </div>
                        <p style='color: #777; font-size: 13px;'>Este código é de utilização única e expira em <strong>15 minutos</strong>.</p>
                        <p style='color: #aaa; font-size: 11px; margin-top: 20px;'>Se você não solicitou este código, ignore esta mensagem.</p>
                    </div>
                </div>
            ";

            return $mail->send();
        } catch (Exception $e) {
            error_log('Erro ao enviar e-mail de código: ' . $e->getMessage());
            return false;
        }
    }
}