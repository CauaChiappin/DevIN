<?php

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
     * Cria e configura a conexão SMTP do PHPMailer.
     */
    private static function getMailer(): PHPMailer
    {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        $smtpUsername = trim((string) (getenv('DEVIN_SMTP_USERNAME') ?: ''));
        $smtpPassword = (string) (getenv('DEVIN_SMTP_PASSWORD') ?: '');

        if ($smtpUsername === '' || $smtpPassword === '') {
            throw new RuntimeException('Configure DEVIN_SMTP_USERNAME e DEVIN_SMTP_PASSWORD para habilitar e-mails.');
        }

        /*
         * IMPORTANTE:
         *
         * Substitua o e-mail abaixo pelo e-mail utilizado
         * pelo sistema DevIN.
         *
         * A senha deve ser uma SENHA DE APP do Google,
         * e não a senha normal da conta.
         */
        $mail->Username = $smtpUsername;
        $mail->Password = $smtpPassword;

        /*
         * Configuração de segurança do Gmail.
         */
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        /*
         * Permite o envio de caracteres acentuados
         * corretamente nos e-mails.
         */
        $mail->CharSet = 'UTF-8';

        /*
         * Remetente padrão dos e-mails.
         */
        $mail->setFrom(
            (string) (getenv('DEVIN_SMTP_FROM_EMAIL') ?: $smtpUsername),
            'Plataforma DevIN'
        );

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
        /*
         * Verifica se o endereço de e-mail é válido
         * antes de tentar enviá-lo.
         */
        if (!filter_var($destinatarioEmail, FILTER_VALIDATE_EMAIL)) {
            error_log(
                'E-mail inválido: ' . $destinatarioEmail
            );

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
            $mail->Body = $corpoHtml;

            /*
             * Versão em texto simples para clientes
             * de e-mail que não exibem HTML.
             */
            $mail->AltBody = strip_tags($corpoHtml);

            return $mail->send();

        } catch (Exception $e) {
            error_log(
                'Erro do PHPMailer ao enviar e-mail: ' .
                $e->getMessage()
            );

            return false;

        } catch (\Throwable $e) {
            error_log(
                'Erro inesperado ao enviar e-mail: ' .
                $e->getMessage()
            );

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
        $nomeSeguro = htmlspecialchars(
            $nomeCandidato,
            ENT_QUOTES,
            'UTF-8'
        );

        $assunto = 'Cadastro e Currículo Concluídos - DevIN';

        $corpo = "
            <div style='
                font-family: Arial, sans-serif;
                padding: 20px;
                background-color: #f4f6f9;
            '>

                <div style='
                    max-width: 600px;
                    margin: 0 auto;
                    background: #ffffff;
                    border-radius: 8px;
                    padding: 30px;
                '>

                    <h2 style='color: #2b56f5;'>
                        Olá, {$nomeSeguro}!
                    </h2>

                    <p>
                        Parabéns! Seu cadastro e currículo
                        foram concluídos com sucesso no
                        <strong>DevIN</strong>.
                    </p>

                    <p>
                        Seu perfil agora poderá ser encontrado
                        por empresas cadastradas na plataforma.
                    </p>

                    <p>
                        Boa sorte na sua busca por oportunidades!
                    </p>

                    <p style='text-align:center; margin-top:24px;'>
                        <a href='" . self::DASHBOARD_PESSOA_URL . "' style='background:#00549f;color:#fff;padding:12px 20px;border-radius:8px;text-decoration:none;font-weight:bold;'>
                            Acessar meu perfil
                        </a>
                    </p>

                </div>

            </div>
        ";

        return self::enviar(
            $emailCandidato,
            $nomeCandidato,
            $assunto,
            $corpo
        );
    }

    /**
     * Envia lembrete para pessoa que ainda não criou
     * o currículo.
     */
    public static function enviarLembreteCurriculoPendente(
        string $emailCandidato,
        string $nomeCandidato
    ): bool {
        $nomeSeguro = htmlspecialchars(
            $nomeCandidato,
            ENT_QUOTES,
            'UTF-8'
        );

        $assunto = 'Falta pouco! Complete seu currículo no DevIN';

        $corpo = "
            <div style='
                font-family: Arial, sans-serif;
                padding: 20px;
                background-color: #f4f6f9;
            '>

                <div style='
                    max-width: 600px;
                    margin: 0 auto;
                    background: #ffffff;
                    border-radius: 8px;
                    padding: 30px;
                '>

                    <h2 style='color: #e67e22;'>
                        Olá, {$nomeSeguro}!
                    </h2>

                    <p>
                        Você iniciou seu cadastro há mais de
                        1 hora.
                    </p>

                    <p>
                        Seu currículo ainda não foi cadastrado
                        na plataforma.
                    </p>

                    <p>
                        Finalize seu currículo para liberar
                        seu perfil para as empresas.
                    </p>

                    <p>
                        Complete suas informações e aumente
                        suas chances de encontrar uma oportunidade
                        no <strong>DevIN</strong>.
                    </p>

                    <p style='text-align:center; margin-top:24px;'>
                        <a href='" . self::CURRICULO_URL . "' style='background:#00549f;color:#fff;padding:12px 20px;border-radius:8px;text-decoration:none;font-weight:bold;'>
                            Finalizar currículo
                        </a>
                    </p>

                </div>

            </div>
        ";

        return self::enviar(
            $emailCandidato,
            $nomeCandidato,
            $assunto,
            $corpo
        );
    }

    /**
     * Notifica as empresas sobre um novo candidato
     * que concluiu o currículo.
     */
    public static function notificarEmpresasNovoCandidato(
        mysqli $conn,
        string $nomeCandidato
    ): void {
        $query = "
            SELECT
                nome,
                email
            FROM empresa
            WHERE email IS NOT NULL
              AND email <> ''
        ";

        $result = $conn->query($query);

        if ($result === false) {
            error_log(
                'Erro ao buscar empresas para notificação: ' .
                $conn->error
            );

            return;
        }

        if ($result->num_rows === 0) {
            return;
        }

        $nomeSeguro = htmlspecialchars(
            $nomeCandidato,
            ENT_QUOTES,
            'UTF-8'
        );

        while ($empresa = $result->fetch_assoc()) {
            $nomeEmpresaOriginal = $empresa['nome'] ?? 'Empresa';

            $nomeEmpresa = htmlspecialchars(
                $nomeEmpresaOriginal,
                ENT_QUOTES,
                'UTF-8'
            );

            $emailEmpresa = trim(
                $empresa['email'] ?? ''
            );

            /*
             * Ignora empresas sem e-mail válido.
             */
            if (
                $emailEmpresa === '' ||
                !filter_var(
                    $emailEmpresa,
                    FILTER_VALIDATE_EMAIL
                )
            ) {
                continue;
            }

            $assunto = 'Novo Candidato Disponível - DevIN';

            $corpo = "
                <div style='
                    font-family: Arial, sans-serif;
                    padding: 20px;
                    background-color: #f4f6f9;
                '>

                    <div style='
                        max-width: 600px;
                        margin: 0 auto;
                        background: #ffffff;
                        border-radius: 8px;
                        padding: 30px;
                    '>

                        <h2 style='color: #2b56f5;'>
                            Olá, {$nomeEmpresa}!
                        </h2>

                        <p>
                            Um novo candidato está disponível
                            na plataforma <strong>DevIN</strong>.
                        </p>

                        <p>
                            O candidato
                            <strong>{$nomeSeguro}</strong>
                            acabou de concluir o currículo
                            na plataforma.
                        </p>

                        <p>
                            Acesse a plataforma para consultar
                            os candidatos disponíveis.
                        </p>

                    </div>

                </div>
            ";

            self::enviar(
                $emailEmpresa,
                $nomeEmpresaOriginal,
                $assunto,
                $corpo
            );
        }

        $result->free();
    }
}
