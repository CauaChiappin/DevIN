<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/config/auth.php';

require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

class MailerHelper
{
    private const CURRICULO_URL = APP_BASE_URL . '/php/cadastrar_curriculo.php';
    private const DASHBOARD_PESSOA_URL = APP_BASE_URL . '/php/pessoa.php';

    /**
     * Cria e configura a conexão SMTP do PHPMailer utilizando o .env
     */
    private static function getMailer(): PHPMailer
    {
        $mail = new PHPMailer(true);

        $environmentValue = static function (string $name, ?string $default = null): ?string {
            $value = $_ENV[$name] ?? $_SERVER[$name] ?? getenv($name);
            if ($value === false || $value === null || trim((string) $value) === '') {
                return $default;
            }

            return trim((string) $value);
        };

        $host       = $environmentValue('DEVIN_SMTP_HOST', '');
        $port       = (int) $environmentValue('DEVIN_SMTP_PORT', '587');
        $encryption = strtolower((string) $environmentValue('DEVIN_SMTP_ENCRYPTION', 'tls'));
        $username   = $environmentValue('DEVIN_SMTP_USERNAME', '');
        $password   = $environmentValue('DEVIN_SMTP_PASSWORD', '');
        $fromEmail  = $environmentValue('DEVIN_SMTP_FROM_EMAIL', $username);
        $fromName   = $environmentValue('DEVIN_SMTP_FROM_NAME', 'Plataforma DevIN');

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

        // Remetente configurável via .env
        $mail->setFrom($fromEmail, $fromName);

        return $mail;
    }

    /**
     * Envia um e-mail HTML.
     */
    public static function enviar(
        string $destinatarioEmail,
        string $destinatarioNome,
        string $assunto,
        string $corpoHtml
    ): bool {
        if (!filter_var($destinatarioEmail, FILTER_VALIDATE_EMAIL)) {
            error_log('E-mail inválido: ' . $destinatarioEmail);
            return false;
        }

        try {
            $mail = self::getMailer();

            $mail->addAddress(
                $destinatarioEmail,
                $destinatarioNome
            );

            $mail->isHTML(true);
            $mail->Subject = $assunto;
            $mail->Body    = $corpoHtml;

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

    public static function enviarConfirmacaoCadastro(string $email, string $nome): bool
    {
        $nomeSeguro = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
        $corpo = "<div style='font-family:Arial,sans-serif;padding:24px'>"
            . "<h2>Olá, {$nomeSeguro}!</h2>"
            . '<p>Sua conta foi criada com sucesso na plataforma DevIN.</p>'
            . '<p>Conclua seu currículo para que empresas possam encontrar seu perfil.</p></div>';

        return self::enviar($email, $nome, 'Cadastro confirmado - DevIN', $corpo);
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

    public static function enviarAvisoAlteracaoSenha(string $email, string $nome): bool
    {
        $nomeSeguro = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
        $corpo = "<div style='font-family:Arial,sans-serif;padding:24px'>"
            . "<h2>Olá, {$nomeSeguro}.</h2>"
            . '<p>A senha da sua conta DevIN foi alterada com sucesso.</p>'
            . '<p>Se você não fez essa alteração, recupere o acesso à sua conta e entre em contato com o suporte.</p></div>';

        return self::enviar($email, $nome, 'Sua senha foi alterada - DevIN', $corpo);
    }

    public static function enviarCodigoRecuperacao6(string $email, string $nome, string $codigo): bool
    {
        $codigo = strtoupper($codigo);
        if (!preg_match('/^[A-Z0-9]{6}$/', $codigo)) {
            return false;
        }

        $codigoSeguro = htmlspecialchars(substr($codigo, 0, 3) . '-' . substr($codigo, 3), ENT_QUOTES, 'UTF-8');
        $nomeSeguro = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
        $corpo = "<div style='font-family:Arial,sans-serif;padding:24px'>"
            . "<h2>Olá, {$nomeSeguro}.</h2>"
            . "<p>Seu código de recuperação é <strong>{$codigoSeguro}</strong>.</p>"
            . '<p>Ele expira em 15 minutos e pode ser usado uma única vez.</p></div>';

        return self::enviar($email, $nome, 'Código de recuperação de senha - DevIN', $corpo);
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
                <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f6f9;'>
                    <div style='max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; padding: 30px;'>
                        <h2 style='color: #2b56f5;'>Olá, {$nomeEmpresa}!</h2>
                        <p>Um novo candidato está disponível na plataforma <strong>DevIN</strong>.</p>
                        <p>O candidato <strong>{$nomeSeguro}</strong> acabou de concluir o currículo na plataforma.</p>
                        <p>Acesse a plataforma para consultar os candidatos disponíveis.</p>
                    </div>
                </div>
            ";

            self::enviar($emailEmpresa, $nomeEmpresaOriginal, $assunto, $corpo);
        }

        $result->free();
    }
}
