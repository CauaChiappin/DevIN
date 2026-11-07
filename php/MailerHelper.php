<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/controllers/ProfileLanguagePreference.php';

class MailerHelper
{
    private const CURRICULO_URL = APP_BASE_URL . '/php/cadastrar_curriculo.php';
    private const DASHBOARD_PESSOA_URL = APP_BASE_URL . '/php/pessoa.php';

    private static function mensagem(string $idioma, string $pt, string $en, string $es): string
    {
        if ($idioma === 'en') {
            return $en;
        }
        if ($idioma === 'es') {
            return $es;
        }
        return $pt;
    }

    /**
     * Cria e configura a conexão SMTP do PHPMailer utilizando o .env
     */
    private static function getMailer(): PHPMailer
    {
        // Centraliza a configuração SMTP para que todos os modelos usem o mesmo transporte seguro.
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

        // Falha cedo se faltarem credenciais ou se a configuração de transporte não for válida.
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
        // Valida o destino antes de criar a conexão e registra erros sem interromper a página.
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

    public static function enviarConfirmacaoDenuncia(
        string $destinatarioEmail,
        string $destinatarioNome,
        string $tipoDenuncia,
        string $alvo,
        string $idioma = 'pt-BR'
    ): bool {
        $nomeSeguro = htmlspecialchars($destinatarioNome, ENT_QUOTES, 'UTF-8');
        $alvoSeguro = htmlspecialchars($alvo, ENT_QUOTES, 'UTF-8');
        $tipoPt = $tipoDenuncia === 'perfil' ? 'o perfil' : 'a vaga';
        $tipoEn = $tipoDenuncia === 'perfil' ? 'profile' : 'job';
        $tipoEs = $tipoDenuncia === 'perfil' ? 'el perfil' : 'el empleo';
        $assunto = self::mensagem(
            $idioma,
            'Recebemos sua denúncia - DevIN',
            'We received your report - DevIN',
            'Recibimos tu denuncia - DevIN'
        );
        $corpo = '<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;padding:24px;color:#243b53">'
            . '<h2>' . self::mensagem($idioma, 'Olá, ', 'Hello, ', 'Hola, ') . $nomeSeguro . '!</h2>'
            . '<p>' . self::mensagem(
                $idioma,
                'Recebemos sua denúncia sobre ' . $tipoPt . ' <strong>' . $alvoSeguro . '</strong>. Nossa equipe está analisando o caso.',
                'We received your report about the ' . $tipoEn . ' <strong>' . $alvoSeguro . '</strong>. Our team is reviewing it.',
                'Recibimos tu denuncia sobre ' . $tipoEs . ' <strong>' . $alvoSeguro . '</strong>. Nuestro equipo está revisando el caso.'
            ) . '</p><p>' . self::mensagem($idioma, 'Você receberá uma mensagem na plataforma quando a análise for concluída.', 'You will receive a message on the platform when the review is complete.', 'Recibirás un mensaje en la plataforma cuando finalice la revisión.') . '</p>'
            . '<p>Support DevIN</p></div>';

        return self::enviar($destinatarioEmail, $destinatarioNome, $assunto, $corpo);
    }

    /**
     * Envia e-mail após o cadastro do currículo.
     */
    public static function enviarConfirmacaoCadastroCurriculo(
        string $emailCandidato,
        string $nomeCandidato,
        string $idioma = 'pt-BR'
    ): bool {
        // Escapa dados variáveis antes de inseri-los nos modelos de e-mail HTML.
        $nomeSeguro = htmlspecialchars($nomeCandidato, ENT_QUOTES, 'UTF-8');
        $assunto = self::mensagem($idioma, 'Cadastro e Currículo Concluídos - DevIN', 'Registration and Resume Completed - DevIN', 'Registro y currículum completados - DevIN');

        $corpo = "
            <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f6f9;'>
                <div style='max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; padding: 30px;'>
                    <h2 style='color: #2b56f5;'>" . self::mensagem($idioma, "Olá, {$nomeSeguro}!", "Hello, {$nomeSeguro}!", "¡Hola, {$nomeSeguro}!") . "</h2>
                    <p>" . self::mensagem($idioma, 'Parabéns! Seu cadastro e currículo foram concluídos com sucesso no <strong>DevIN</strong>.', 'Congratulations! Your registration and resume have been completed successfully on <strong>DevIN</strong>.', '¡Felicidades! Tu registro y currículum se completaron correctamente en <strong>DevIN</strong>.') . "</p>
                    <p>" . self::mensagem($idioma, 'Seu perfil agora poderá ser encontrado por empresas cadastradas na plataforma.', 'Companies registered on the platform can now find your profile.', 'Las empresas registradas en la plataforma ya pueden encontrar tu perfil.') . "</p>
                    <p>" . self::mensagem($idioma, 'Boa sorte na sua busca por oportunidades!', 'Good luck with your search for opportunities!', '¡Mucha suerte en tu búsqueda de oportunidades!') . "</p>
                    <p style='text-align:center; margin-top:24px;'>
                        <a href='" . self::DASHBOARD_PESSOA_URL . "' style='background:#00549f;color:#fff;padding:12px 20px;border-radius:8px;text-decoration:none;font-weight:bold;'>
                            " . self::mensagem($idioma, 'Acessar meu perfil', 'Access my profile', 'Acceder a mi perfil') . "
                        </a>
                    </p>
                </div>
            </div>
        ";

        return self::enviar($emailCandidato, $nomeCandidato, $assunto, $corpo);
    }

    public static function enviarConfirmacaoCadastroEmpresa(string $email, string $nome, string $idioma = 'pt-BR'): bool
    {
        $nomeSeguro = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
        $corpo = "<div style='font-family:Arial,sans-serif;padding:24px'>"
            . '<h2>' . self::mensagem($idioma, "Olá, {$nomeSeguro}!", "Hello, {$nomeSeguro}!", "¡Hola, {$nomeSeguro}!") . '</h2>'
            . '<p>' . self::mensagem($idioma, 'O cadastro da sua empresa foi concluído com sucesso.', 'Your company registration was completed successfully.', 'El registro de tu empresa se completó correctamente.') . '</p>'
            . '<p>' . self::mensagem($idioma, 'Agora você pode publicar vagas e acompanhar as candidaturas pela plataforma.', 'You can now post jobs and manage applications on the platform.', 'Ahora puedes publicar empleos y gestionar las solicitudes en la plataforma.') . '</p>'
            . "<p><a href='" . APP_BASE_URL . "/php/empresa.php'>" . self::mensagem($idioma, 'Acessar minha conta', 'Access my account', 'Acceder a mi cuenta') . '</a></p></div>';

        return self::enviar($email, $nome, self::mensagem($idioma, 'Cadastro da empresa concluído - DevIN', 'Company registration completed - DevIN', 'Registro de empresa completado - DevIN'), $corpo);
    }

    public static function enviarConfirmacaoCadastro(string $email, string $nome, string $idioma = 'pt-BR'): bool
    {
        $nomeSeguro = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
        $corpo = "<div style='font-family:Arial,sans-serif;padding:24px'>"
            . '<h2>' . self::mensagem($idioma, "Olá, {$nomeSeguro}!", "Hello, {$nomeSeguro}!", "¡Hola, {$nomeSeguro}!") . '</h2>'
            . '<p>' . self::mensagem($idioma, 'Sua conta foi criada com sucesso na plataforma DevIN.', 'Your account was successfully created on the DevIN platform.', 'Tu cuenta se creó correctamente en la plataforma DevIN.') . '</p>'
            . '<p>' . self::mensagem($idioma, 'Conclua seu currículo para que empresas possam encontrar seu perfil.', 'Complete your resume so companies can find your profile.', 'Completa tu currículum para que las empresas puedan encontrar tu perfil.') . '</p></div>';

        return self::enviar($email, $nome, self::mensagem($idioma, 'Cadastro confirmado - DevIN', 'Registration confirmed - DevIN', 'Registro confirmado - DevIN'), $corpo);
    }

    public static function enviarConfirmacaoExclusao(string $email, string $nome, string $tipo, string $idioma = 'pt-BR'): bool
    {
        if (!in_array($tipo, ['pessoa', 'empresa'], true)) {
            return false;
        }

        $nomeSeguro = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
        $corpo = "<div style='font-family:Arial,sans-serif;padding:24px'>"
            . '<h2>' . self::mensagem($idioma, "Olá, {$nomeSeguro}.", "Hello, {$nomeSeguro}.", "Hola, {$nomeSeguro}.") . '</h2>'
            . '<p>' . self::mensagem($idioma, 'A exclusão da sua conta no DevIN foi concluída.', 'Your DevIN account has been deleted.', 'La eliminación de tu cuenta de DevIN se completó.') . '</p>'
            . '<p>' . self::mensagem($idioma, 'Se você não solicitou essa ação, entre em contato com a equipe do DevIN.', 'If you did not request this, contact the DevIN team.', 'Si no solicitaste esta acción, ponte en contacto con el equipo de DevIN.') . '</p></div>';

        return self::enviar($email, $nome, self::mensagem($idioma, 'Exclusão da conta concluída - DevIN', 'Account deletion completed - DevIN', 'Eliminación de cuenta completada - DevIN'), $corpo);
    }

    public static function enviarAvisoAlteracaoSenha(string $email, string $nome, string $idioma = 'pt-BR'): bool
    {
        $nomeSeguro = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
        $corpo = "<div style='font-family:Arial,sans-serif;padding:24px'>"
            . '<h2>' . self::mensagem($idioma, "Olá, {$nomeSeguro}.", "Hello, {$nomeSeguro}.", "Hola, {$nomeSeguro}.") . '</h2>'
            . '<p>' . self::mensagem($idioma, 'A senha da sua conta DevIN foi alterada com sucesso.', 'Your DevIN account password has been changed successfully.', 'La contraseña de tu cuenta DevIN se cambió correctamente.') . '</p>'
            . '<p>' . self::mensagem($idioma, 'Se você não fez essa alteração, recupere o acesso à sua conta e entre em contato com o suporte.', 'If you did not make this change, recover access to your account and contact support.', 'Si no realizaste este cambio, recupera el acceso a tu cuenta y ponte en contacto con soporte.') . '</p></div>';

        return self::enviar($email, $nome, self::mensagem($idioma, 'Sua senha foi alterada - DevIN', 'Your password has been changed - DevIN', 'Tu contraseña fue cambiada - DevIN'), $corpo);
    }

    public static function enviarCodigoRecuperacao6(string $email, string $nome, string $codigo, string $idioma = 'pt-BR'): bool
    {
        // Aceita somente seis caracteres alfanuméricos antes de montar a mensagem de recuperação.
        $codigo = strtoupper($codigo);
        if (!preg_match('/^[A-Z0-9]{6}$/', $codigo)) {
            return false;
        }

        $codigoSeguro = htmlspecialchars(substr($codigo, 0, 3) . ' ' . substr($codigo, 3), ENT_QUOTES, 'UTF-8');
        $nomeSeguro   = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');

        $corpo = "
            <div style='font-family:Arial,sans-serif;background-color:#f4f6f9;padding:32px 16px;'>
                <div style='max-width:560px;margin:0 auto;background:#ffffff;border-radius:12px;padding:36px 32px;box-shadow:0 2px 8px rgba(0,0,0,.08);'>

                    <h2 style='margin:0 0 8px;color:#1a1a2e;font-size:22px;'>" . self::mensagem($idioma, "Olá, {$nomeSeguro}!", "Hello, {$nomeSeguro}!", "¡Hola, {$nomeSeguro}!") . "</h2>
                    <p style='margin:0 0 28px;color:#555;font-size:15px;line-height:1.5;'>
                        " . self::mensagem($idioma, 'Recebemos uma solicitação para recuperar o acesso à sua conta no <strong>DevIN</strong>.<br>Use o código abaixo para continuar:', 'We received a request to recover access to your <strong>DevIN</strong> account.<br>Use the code below to continue:', 'Recibimos una solicitud para recuperar el acceso a tu cuenta de <strong>DevIN</strong>.<br>Usa el siguiente código para continuar:') . "
                    </p>

                    <p style='text-align:center;margin:0 0 28px;font-size:15px;color:#555;'>
                        " . self::mensagem($idioma, 'Seu código de verificação:', 'Your verification code:', 'Tu código de verificación:') . "
                        <span style='font-size:32px;font-weight:700;color:#1a1a2e;letter-spacing:8px;font-family:\"Courier New\",Courier,monospace;background:#eef2ff;padding:4px 16px;border-radius:6px;vertical-align:middle;'>
                            {$codigoSeguro}
                        </span>
                    </p>

                    <p style='margin:0 0 24px;color:#888;font-size:13px;text-align:center;'>
                        &#9200; " . self::mensagem($idioma, 'Este código expira em <strong>15 minutos</strong> e é válido para uso único.', 'This code expires in <strong>15 minutes</strong> and can only be used once.', 'Este código vence en <strong>15 minutos</strong> y solo se puede usar una vez.') . "
                    </p>

                    <hr style='border:none;border-top:1px solid #ebebeb;margin:0 0 20px;'>

                    <p style='margin:0;color:#aaa;font-size:12px;text-align:center;'>
                        " . self::mensagem($idioma, 'Se você não solicitou essa recuperação, ignore este e-mail.<br>Sua senha permanece inalterada.', 'If you did not request this recovery, ignore this email.<br>Your password remains unchanged.', 'Si no solicitaste esta recuperación, ignora este correo.<br>Tu contraseña permanece sin cambios.') . "
                    </p>
                </div>
            </div>
        ";

        return self::enviar($email, $nome, self::mensagem($idioma, 'Código de recuperação de senha - DevIN', 'Password recovery code - DevIN', 'Código de recuperación de contraseña - DevIN'), $corpo);
    }


    /**
     * Envia lembrete para pessoa que ainda não criou o currículo.
     */
    public static function enviarLembreteCurriculoPendente(
        string $emailCandidato,
        string $nomeCandidato,
        string $idioma = 'pt-BR'
    ): bool {
        $nomeSeguro = htmlspecialchars($nomeCandidato, ENT_QUOTES, 'UTF-8');
        $assunto = self::mensagem($idioma, 'Falta pouco! Complete seu currículo no DevIN', 'Almost there! Complete your resume on DevIN', '¡Ya casi! Completa tu currículum en DevIN');

        $corpo = "
            <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f6f9;'>
                <div style='max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; padding: 30px;'>
                    <h2 style='color: #e67e22;'>" . self::mensagem($idioma, "Olá, {$nomeSeguro}!", "Hello, {$nomeSeguro}!", "¡Hola, {$nomeSeguro}!") . "</h2>
                    <p>" . self::mensagem($idioma, 'Você iniciou seu cadastro há mais de 1 hora.', 'You started your registration more than an hour ago.', 'Iniciaste tu registro hace más de una hora.') . "</p>
                    <p>" . self::mensagem($idioma, 'Seu currículo ainda não foi cadastrado na plataforma.', 'You have not added a resume to the platform yet.', 'Aún no has añadido tu currículum a la plataforma.') . "</p>
                    <p>" . self::mensagem($idioma, 'Finalize seu currículo para liberar seu perfil para as empresas.', 'Complete your resume to make your profile available to companies.', 'Completa tu currículum para que las empresas puedan ver tu perfil.') . "</p>
                    <p>" . self::mensagem($idioma, 'Complete suas informações e aumente suas chances de encontrar uma oportunidade no <strong>DevIN</strong>.', 'Complete your information to improve your chances of finding an opportunity on <strong>DevIN</strong>.', 'Completa tu información para aumentar tus posibilidades de encontrar una oportunidad en <strong>DevIN</strong>.') . "</p>
                    <p style='text-align:center; margin-top:24px;'>
                        <a href='" . self::CURRICULO_URL . "' style='background:#00549f;color:#fff;padding:12px 20px;border-radius:8px;text-decoration:none;font-weight:bold;'>
                            " . self::mensagem($idioma, 'Finalizar currículo', 'Complete your resume', 'Completar currículum') . "
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
        // Busca destinos cadastrados uma vez; cada endereço é validado antes do envio individual.
        $query = "
            SELECT e.id_empresa, e.nome, e.email
            FROM empresa e
            WHERE e.email IS NOT NULL AND e.email <> ''
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
            $idioma = ProfileLanguagePreference::load($conn, 'empresa', (int) $empresa['id_empresa']);

            if ($emailEmpresa === '' || !filter_var($emailEmpresa, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $assunto = self::mensagem($idioma, 'Novo Candidato Disponível - DevIN', 'New Candidate Available - DevIN', 'Nuevo candidato disponible - DevIN');

            $corpo = "
                <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f6f9;'>
                    <div style='max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; padding: 30px;'>
                        <h2 style='color: #2b56f5;'>" . self::mensagem($idioma, "Olá, {$nomeEmpresa}!", "Hello, {$nomeEmpresa}!", "¡Hola, {$nomeEmpresa}!") . "</h2>
                        <p>" . self::mensagem($idioma, 'Um novo candidato está disponível na plataforma <strong>DevIN</strong>.', 'A new candidate is available on the <strong>DevIN</strong> platform.', 'Hay un nuevo candidato disponible en la plataforma <strong>DevIN</strong>.') . "</p>
                        <p>" . self::mensagem($idioma, "O candidato <strong>{$nomeSeguro}</strong> acabou de concluir o currículo na plataforma.", "Candidate <strong>{$nomeSeguro}</strong> has just completed their resume on the platform.", "El candidato <strong>{$nomeSeguro}</strong> acaba de completar su currículum en la plataforma.") . "</p>
                        <p>" . self::mensagem($idioma, 'Acesse a plataforma para consultar os candidatos disponíveis.', 'Visit the platform to view available candidates.', 'Accede a la plataforma para consultar los candidatos disponibles.') . "</p>
                    </div>
                </div>
            ";

            self::enviar($emailEmpresa, $nomeEmpresaOriginal, $assunto, $corpo);
        }

        $result->free();
    }
}
