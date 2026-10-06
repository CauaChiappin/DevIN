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

<<<<<<< HEAD
        // Busca as configurações do arquivo .env com fallbacks de segurança
        $host       = getenv('DEVIN_SMTP_HOST') ?: '';
        $port       = (int)(getenv('DEVIN_SMTP_PORT') ?: 587);
        $encryption = strtolower(getenv('DEVIN_SMTP_ENCRYPTION') ?: 'tls');
        $username   = getenv('DEVIN_SMTP_USERNAME') ?: '';
        $password   = getenv('DEVIN_SMTP_PASSWORD') ?: '';
        $fromEmail  = getenv('DEVIN_SMTP_FROM_EMAIL') ?: $username;
        $fromName   = getenv('DEVIN_SMTP_FROM_NAME') ?: 'Plataforma DevIN';

        if ($host === '' || $username === '' || $password === ''
            || !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)
            || !in_array($encryption, ['tls', 'ssl'], true)
            || $port < 1 || $port > 65535
        ) {
            throw new RuntimeException('As configurações SMTP estão incompletas ou inválidas.');
        }

        $mail->isSMTP();
        $mail->Host       = $host;
        $mail->SMTPAuth   = true;
        $mail->Username   = $username;
        $mail->Password   = $password;

        // Configuração dinâmica de criptografia (TLS/SSL)
        if ($encryption === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }

        $mail->Port    = $port;
        $mail->CharSet = 'UTF-8';
        $mail->Timeout = 8;
        $mail->SMTPKeepAlive = false;
=======
        if ($value === false || $value === null || trim((string) $value) === '') {
            return $default;
        }

        return trim((string) $value);
    }
>>>>>>> bb0413abcd2a8c17f9c53b600f5a5acb10a41c97

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

<<<<<<< HEAD
            /*
             * Versão em texto simples para clientes
             * de e-mail que não exibem HTML.
             */
            $mail->AltBody = strip_tags($corpoHtml);

            return $mail->send();

        } catch (Exception $e) {
            error_log('Erro do PHPMailer ao enviar e-mail: ' . $e->getMessage());
            return false;
        } catch (\Throwable $e) {
            error_log('Erro inesperado ao enviar e-mail: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Envia e-mail após o cadastro do currículo.
     */
    public static function enviarConfirmacaoCadastroCurriculo(
        string $emailCandidato,
        string $nomeCandidato
    ): bool {
        $nomeSeguro = htmlspecialchars($nomeCandidato, ENT_QUOTES, 'UTF-8');
        $assunto    = 'Cadastro e Currículo Concluídos - DevIN';

        $corpo = "
            <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f6f9;'>
                <div style='max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; padding: 30px;'>
                    <h2 style='color: #2b56f5;'>Olá, {$nomeSeguro}!</h2>
                    <p>Parabéns! Seu cadastro e currículo foram concluídos com sucesso no <strong>DevIN</strong>.</p>
                    <p>Seu perfil agora poderá ser encontrado por empresas cadastradas na plataforma.</p>
                    <p>Boa sorte na sua busca por oportunidades!</p>
                    <p style='text-align:center; margin-top:24px;'>
                        <a href='" . self::DASHBOARD_PESSOA_URL . "' style='background:#00549f;color:#fff;padding:12px 20px;border-radius:8px;text-decoration:none;font-weight:bold;'>
                            Acessar meu perfil
                        </a>
                    </p>
                </div>
            </div>
        ";

        return self::enviar($emailCandidato, $nomeCandidato, $assunto, $corpo);
    }

    public static function enviarConfirmacaoCadastroEmpresa(string $email, string $nome): bool
    {
        $nomeSeguro = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
        $corpo = "<div style='font-family:Arial,sans-serif;padding:24px'>"
            . "<h2>Olá, {$nomeSeguro}!</h2>"
            . '<p>O cadastro da sua empresa foi concluído com sucesso.</p>'
            . '<p>Agora você pode publicar vagas e acompanhar as candidaturas pela plataforma.</p>'
            . "<p><a href='" . APP_BASE_URL . "/php/empresa.php'>Acessar minha conta</a></p></div>";

        return self::enviar($email, $nome, 'Cadastro da empresa concluído - DevIN', $corpo);
    }

    public static function enviarConfirmacaoExclusao(string $email, string $nome, string $tipo): bool
    {
        if (!in_array($tipo, ['pessoa', 'empresa'], true)) {
            return false;
        }

        $nomeSeguro = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
        $corpo = "<div style='font-family:Arial,sans-serif;padding:24px'>"
            . "<h2>Olá, {$nomeSeguro}.</h2>"
            . '<p>A exclusão da sua conta no DevIN foi concluída.</p>'
            . '<p>Se você não solicitou essa ação, entre em contato com a equipe do DevIN.</p></div>';

        return self::enviar($email, $nome, 'Exclusão da conta concluída - DevIN', $corpo);
    }

    /**
     * Envia lembrete para pessoa que ainda não criou o currículo.
     */
    public static function enviarLembreteCurriculoPendente(
        string $emailCandidato,
        string $nomeCandidato
    ): bool {
        $nomeSeguro = htmlspecialchars($nomeCandidato, ENT_QUOTES, 'UTF-8');
        $assunto    = 'Falta pouco! Complete seu currículo no DevIN';

        $corpo = "
            <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f6f9;'>
                <div style='max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; padding: 30px;'>
                    <h2 style='color: #e67e22;'>Olá, {$nomeSeguro}!</h2>
                    <p>Você iniciou seu cadastro há mais de 1 hora.</p>
                    <p>Seu currículo ainda não foi cadastrado na plataforma.</p>
                    <p>Finalize seu currículo para liberar seu perfil para as empresas.</p>
                    <p>Complete suas informações e aumente suas chances de encontrar uma oportunidade no <strong>DevIN</strong>.</p>
                    <p style='text-align:center; margin-top:24px;'>
                        <a href='" . self::CURRICULO_URL . "' style='background:#00549f;color:#fff;padding:12px 20px;border-radius:8px;text-decoration:none;font-weight:bold;'>
                            Finalizar currículo
                        </a>
                    </p>
                </div>
            </div>
        ";

        return self::enviar($emailCandidato, $nomeCandidato, $assunto, $corpo);
    }

    /**
     * Notifica as empresas sobre um novo candidato que concluiu o currículo.
     */
    public static function notificarEmpresasNovoCandidato(
        mysqli $conn,
        string $nomeCandidato
    ): void {
        $query = "
            SELECT nome, email 
            FROM empresa 
            WHERE email IS NOT NULL AND email <> ''
        ";

        $result = $conn->query($query);

        if ($result === false) {
            error_log('Erro ao buscar empresas para notificação: ' . $conn->error);
            return;
        }

        if ($result->num_rows === 0) {
            return;
        }

        $nomeSeguro = htmlspecialchars($nomeCandidato, ENT_QUOTES, 'UTF-8');

        while ($empresa = $result->fetch_assoc()) {
            $nomeEmpresaOriginal = $empresa['nome'] ?? 'Empresa';
            $nomeEmpresa         = htmlspecialchars($nomeEmpresaOriginal, ENT_QUOTES, 'UTF-8');
            $emailEmpresa        = trim($empresa['email'] ?? '');

            if ($emailEmpresa === '' || !filter_var($emailEmpresa, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $assunto = 'Novo Candidato Disponível - DevIN';

            $corpo = "
=======
            $codigoFormatado = htmlspecialchars(substr($codigo, 0, 3) . '-' . substr($codigo, 3, 3), ENT_QUOTES, 'UTF-8');
            $nomeSeguro = htmlspecialchars($nomeDestino, ENT_QUOTES, 'UTF-8');
            $mail->Body = "
>>>>>>> bb0413abcd2a8c17f9c53b600f5a5acb10a41c97
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
<<<<<<< HEAD
=======

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
>>>>>>> bb0413abcd2a8c17f9c53b600f5a5acb10a41c97
}
