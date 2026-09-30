<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$envDirectory = dirname(__DIR__);
if (is_file($envDirectory . '/.env')) {
    Dotenv\Dotenv::createImmutable($envDirectory)->safeLoad();
}

final class MailerHelper
{
    private static function environmentValue(string $name, ?string $default = null): ?string
    {
        $value = $_ENV[$name] ?? $_SERVER[$name] ?? getenv($name);

        if ($value === false || $value === null || trim((string) $value) === '') {
            return $default;
        }

        return trim((string) $value);
    }

    private static function getMailer(): PHPMailer
    {
        $host = self::environmentValue('SMTP_HOST', 'smtp.gmail.com');
        $port = (int) self::environmentValue('SMTP_PORT', '587');
        $username = self::environmentValue('SMTP_USER');
        $password = self::environmentValue('SMTP_PASS');

        if ($username === null || $password === null) {
            throw new RuntimeException('Configure SMTP_USER e SMTP_PASS no ambiente.');
        }
        // Senhas de app do Gmail são exibidas em grupos; o servidor recebe os 16 caracteres sem espaços.
        $password = (string) preg_replace('/\s+/', '', $password);

        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->Port = $port;
        $mail->SMTPAuth = true;
        $mail->Username = $username;
        $mail->Password = $password;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->CharSet = PHPMailer::CHARSET_UTF8;

        $environment = strtolower(self::environmentValue('APP_ENV', self::environmentValue('DEVIN_APP_ENV', 'production')));
        if (in_array($environment, ['local', 'development'], true)) {
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ];
        }

        $fromEmail = self::environmentValue('SMTP_FROM_EMAIL', $username);
        $fromName = self::environmentValue('SMTP_FROM_NAME', 'DevIN');
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

            $codigoFormatado = htmlspecialchars(substr($codigo, 0, 3) . '-' . substr($codigo, 3, 3), ENT_QUOTES, 'UTF-8');
            $nomeSeguro = htmlspecialchars($nomeDestino, ENT_QUOTES, 'UTF-8');
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f6f9;'>
                    <div style='max-width: 480px; margin: 0 auto; background: #ffffff; padding: 25px; border-radius: 12px; text-align: center;'>
                        <h2 style='color: #004aad; margin-bottom: 10px;'>Dev<span style='color: #000;'>IN</span></h2>
                        <h3 style='color: #333;'>Recuperação de senha</h3>
                        <p style='color: #555;'>Olá, <strong>{$nomeSeguro}</strong>!</p>
                        <p style='color: #555;'>Use o código abaixo para redefinir sua senha:</p>
                        <p style='background: #004aad; color: #fff; padding: 12px; font-size: 26px; letter-spacing: 5px;'><strong>{$codigoFormatado}</strong></p>
                        <p style='color: #777;'>Este código expira em 15 minutos e pode ser usado uma única vez.</p>
                        <p style='color: #aaa; font-size: 11px;'>Se você não solicitou este código, ignore esta mensagem.</p>
                    </div>
                </div>
            ";
            $mail->AltBody = "Olá, {$nomeDestino}. Seu código de recuperação é {$codigo}. Ele expira em 15 minutos.";

            return $mail->send();
        } catch (Throwable $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public static function enviarConfirmacaoCadastroCurriculo(string $emailDestino, string $nomeDestino): bool
    {
        try {
            $mail = self::getMailer();
            $mail->addAddress($emailDestino, $nomeDestino);
            $mail->isHTML(true);
            $mail->Subject = 'DevIN | Currículo cadastrado com sucesso';

            $nomeSeguro = htmlspecialchars($nomeDestino, ENT_QUOTES, 'UTF-8');
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f6f9;'>
                    <div style='max-width: 500px; margin: 0 auto; background: #ffffff; padding: 25px; border-radius: 12px; text-align: center;'>
                        <h2 style='color: #004aad; margin-bottom: 10px;'>Dev<span style='color: #000;'>IN</span></h2>
                        <h3 style='color: #333;'>Currículo recebido!</h3>
                        <p style='color: #555;'>Olá, <strong>{$nomeSeguro}</strong>!</p>
                        <p style='color: #555;'>Seu currículo foi cadastrado ou atualizado com sucesso na plataforma DevIN.</p>
                        <p style='color: #555;'>As empresas cadastradas poderão consultar suas qualificações.</p>
                        <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                        <p style='color: #888; font-size: 12px;'>Esta é uma mensagem automática. Não responda a este e-mail.</p>
                    </div>
                </div>
            ";
            $mail->AltBody = "Olá, {$nomeDestino}. Seu currículo foi cadastrado ou atualizado com sucesso na DevIN.";

            return $mail->send();
        } catch (Throwable $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public static function enviarConfirmacaoCadastro(string $emailDestino, string $nomeDestino): bool
    {
        try {
            $mail = self::getMailer();
            $mail->addAddress($emailDestino, $nomeDestino);
            $mail->isHTML(true);
            $mail->Subject = 'DevIN | Cadastro confirmado';

            $nomeSeguro = htmlspecialchars($nomeDestino, ENT_QUOTES, 'UTF-8');
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f6f9;'>
                    <div style='max-width: 500px; margin: 0 auto; background: #ffffff; padding: 25px; border-radius: 12px;'>
                        <h2 style='color: #004aad;'>Dev<span style='color: #000;'>IN</span></h2>
                        <h3 style='color: #333;'>Cadastro confirmado!</h3>
                        <p style='color: #555;'>Olá, <strong>{$nomeSeguro}</strong>.</p>
                        <p style='color: #555;'>Sua conta foi criada com sucesso na plataforma DevIN. Você já pode acessar sua conta e aproveitar os recursos da plataforma.</p>
                        <p style='color: #888; font-size: 12px;'>Esta é uma mensagem automática. Não responda a este e-mail.</p>
                    </div>
                </div>
            ";
            $mail->AltBody = "Olá, {$nomeDestino}. Seu cadastro na plataforma DevIN foi confirmado com sucesso.";

            return $mail->send();
        } catch (Throwable $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public static function enviarAvisoAlteracaoSenha(string $emailDestino, string $nomeDestino): bool
    {
        try {
            $mail = self::getMailer();
            $mail->addAddress($emailDestino, $nomeDestino);
            $mail->isHTML(true);
            $mail->Subject = 'DevIN | Sua senha foi alterada';

            $nomeSeguro = htmlspecialchars($nomeDestino, ENT_QUOTES, 'UTF-8');
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f6f9;'>
                    <div style='max-width: 500px; margin: 0 auto; background: #ffffff; padding: 25px; border-radius: 12px;'>
                        <h2 style='color: #004aad;'>Dev<span style='color: #000;'>IN</span></h2>
                        <h3 style='color: #333;'>Senha alterada</h3>
                        <p style='color: #555;'>Olá, <strong>{$nomeSeguro}</strong>.</p>
                        <p style='color: #555;'>A senha da sua conta DevIN foi alterada com sucesso.</p>
                        <p style='color: #555;'>Se você não fez essa alteração, recupere o acesso à sua conta e entre em contato com o suporte.</p>
                        <p style='color: #888; font-size: 12px;'>Esta é uma mensagem automática. Não responda a este e-mail.</p>
                    </div>
                </div>
            ";
            $mail->AltBody = "Olá, {$nomeDestino}. A senha da sua conta DevIN foi alterada. Se não foi você, recupere o acesso e entre em contato com o suporte.";

            return $mail->send();
        } catch (Throwable $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}
