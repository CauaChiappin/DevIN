<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/MailerHelper.php
 * Finalidade: mailer helper.
 * Domínio: processamento no servidor.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


use PHPMailer\PHPMailer\PHPMailer; // Explicação DevIN: Importa ou exporta dependência, nome, classe ou módulo.
use PHPMailer\PHPMailer\Exception; // Explicação DevIN: Importa ou exporta dependência, nome, classe ou módulo.

require_once __DIR__ . '/config/auth.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/controllers/ProfileLanguagePreference.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

class MailerHelper // Explicação DevIN: Declara o tipo MailerHelper e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    private const CURRICULO_URL = APP_BASE_URL . '/php/cadastrar_curriculo.php'; // Explicação DevIN: Define membro, visibilidade ou método de uma classe.
    private const DASHBOARD_PESSOA_URL = APP_BASE_URL . '/php/pessoa.php'; // Explicação DevIN: Define membro, visibilidade ou método de uma classe.

    // Detalhe DevIN: Método mensagem: encapsula uma responsabilidade reutilizável do módulo no contexto de mailer helper. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    private static function mensagem(string $idioma, string $pt, string $en, string $es): string // Explicação DevIN: Declara função/método mensagem, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if ($idioma === 'en') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            return $en; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if ($idioma === 'es') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            return $es; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return $pt; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /**
     * Cria e configura a conexão SMTP do PHPMailer utilizando o .env
     */
    // Detalhe DevIN: Método getMailer: consulta e organiza dados sem presumir que o resultado seja único no contexto de mailer helper. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    private static function getMailer(): PHPMailer // Explicação DevIN: Declara função/método getMailer, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // Centraliza a configuração SMTP para que todos os modelos usem o mesmo transporte seguro.
        $mail = new PHPMailer(true); // Explicação DevIN: Atribui um valor à variável PHP $mail.

        $environmentValue = static function (string $name, ?string $default = null): ?string { // Explicação DevIN: Define função anônima usada como operação ou callback.
            $value = $_ENV[$name] ?? $_SERVER[$name] ?? getenv($name); // Explicação DevIN: Atribui um valor à variável PHP $value.
            if ($value === false || $value === null || trim((string) $value) === '') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                return $default; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            return trim((string) $value); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $host       = $environmentValue('DEVIN_SMTP_HOST', ''); // Explicação DevIN: Atribui um valor à variável PHP $host.
        $port       = (int) $environmentValue('DEVIN_SMTP_PORT', '587'); // Explicação DevIN: Atribui um valor à variável PHP $port.
        $encryption = strtolower((string) $environmentValue('DEVIN_SMTP_ENCRYPTION', 'tls')); // Explicação DevIN: Atribui um valor à variável PHP $encryption.
        $username   = $environmentValue('DEVIN_SMTP_USERNAME', ''); // Explicação DevIN: Atribui um valor à variável PHP $username.
        $password   = $environmentValue('DEVIN_SMTP_PASSWORD', ''); // Explicação DevIN: Atribui um valor à variável PHP $password.
        $fromEmail  = $environmentValue('DEVIN_SMTP_FROM_EMAIL', $username); // Explicação DevIN: Atribui um valor à variável PHP $fromEmail.
        $fromName   = $environmentValue('DEVIN_SMTP_FROM_NAME', 'Plataforma DevIN'); // Explicação DevIN: Atribui um valor à variável PHP $fromName.

        // Falha cedo se faltarem credenciais ou se a configuração de transporte não for válida.
        if ($host === '' || $username === '' || $password === '' // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            || !filter_var($fromEmail, FILTER_VALIDATE_EMAIL) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            || !in_array($encryption, ['tls', 'ssl'], true) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            || $port < 1 || $port > 65535 // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        ) { // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
            throw new RuntimeException('As configurações SMTP estão incompletas ou inválidas.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $mail->isSMTP(); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        $mail->Host       = $host; // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        $mail->SMTPAuth   = true; // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        $mail->Username   = $username; // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        $mail->Password   = $password; // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.

        // Configuração dinâmica de criptografia (TLS/SSL)
        if ($encryption === 'ssl') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $mail->Port    = $port; // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        $mail->CharSet = 'UTF-8'; // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        $mail->Timeout = 8; // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        $mail->SMTPKeepAlive = false; // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.

        // Remetente configurável via .env
        $mail->setFrom($fromEmail, $fromName); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.

        return $mail; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /**
     * Envia um e-mail HTML.
     */
    // Detalhe DevIN: Método enviar: encapsula uma responsabilidade reutilizável do módulo no contexto de mailer helper. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function enviar( // Explicação DevIN: Declara função/método enviar, com seus parâmetros e comportamento.
        string $destinatarioEmail, // Explicação DevIN: Continua expressão ou bloco pertencente a função enviar.
        string $destinatarioNome, // Explicação DevIN: Continua expressão ou bloco pertencente a função enviar.
        string $assunto, // Explicação DevIN: Continua expressão ou bloco pertencente a função enviar.
        string $corpoHtml // Explicação DevIN: Continua expressão ou bloco pertencente a função enviar.
    ): bool { // Explicação DevIN: Continua expressão ou bloco pertencente a função enviar.
        // Valida o destino antes de criar a conexão e registra erros sem interromper a página.
        if (!filter_var($destinatarioEmail, FILTER_VALIDATE_EMAIL)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            error_log('E-mail inválido: ' . $destinatarioEmail); // Explicação DevIN: Continua expressão ou bloco pertencente a função enviar.
            return false; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            $mail = self::getMailer(); // Explicação DevIN: Atribui um valor à variável PHP $mail.

            $mail->addAddress( // Explicação DevIN: Continua expressão ou bloco pertencente a função enviar.
                $destinatarioEmail, // Explicação DevIN: Continua expressão ou bloco pertencente a função enviar.
                $destinatarioNome // Explicação DevIN: Continua expressão ou bloco pertencente a função enviar.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $mail->isHTML(true); // Explicação DevIN: Continua expressão ou bloco pertencente a função enviar.
            $mail->Subject = $assunto; // Explicação DevIN: Continua expressão ou bloco pertencente a função enviar.
            $mail->Body    = $corpoHtml; // Explicação DevIN: Continua expressão ou bloco pertencente a função enviar.

            /*
             * Versão em texto simples para clientes
             * de e-mail que não exibem HTML.
             */
            $mail->AltBody = strip_tags($corpoHtml); // Explicação DevIN: Continua expressão ou bloco pertencente a função enviar.

            return $mail->send(); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.

        } catch (Exception $e) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            error_log('Erro do PHPMailer ao enviar e-mail: ' . $e->getMessage()); // Explicação DevIN: Continua expressão ou bloco pertencente a função enviar.
            return false; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } catch (\Throwable $e) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            error_log('Erro inesperado ao enviar e-mail: ' . $e->getMessage()); // Explicação DevIN: Continua expressão ou bloco pertencente a função enviar.
            return false; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Método enviarConfirmacaoDenuncia: encapsula uma responsabilidade reutilizável do módulo no contexto de mailer helper. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function enviarConfirmacaoDenuncia( // Explicação DevIN: Declara função/método enviarConfirmacaoDenuncia, com seus parâmetros e comportamento.
        string $destinatarioEmail, // Explicação DevIN: Continua expressão ou bloco pertencente a função enviarConfirmacaoDenuncia.
        string $destinatarioNome, // Explicação DevIN: Continua expressão ou bloco pertencente a função enviarConfirmacaoDenuncia.
        string $tipoDenuncia, // Explicação DevIN: Continua expressão ou bloco pertencente a função enviarConfirmacaoDenuncia.
        string $alvo, // Explicação DevIN: Continua expressão ou bloco pertencente a função enviarConfirmacaoDenuncia.
        string $idioma = 'pt-BR' // Explicação DevIN: Continua expressão ou bloco pertencente a função enviarConfirmacaoDenuncia.
    ): bool { // Explicação DevIN: Continua expressão ou bloco pertencente a função enviarConfirmacaoDenuncia.
        $nomeSeguro = htmlspecialchars($destinatarioNome, ENT_QUOTES, 'UTF-8'); // Explicação DevIN: Atribui um valor à variável PHP $nomeSeguro.
        $alvoSeguro = htmlspecialchars($alvo, ENT_QUOTES, 'UTF-8'); // Explicação DevIN: Atribui um valor à variável PHP $alvoSeguro.
        $tipoPt = $tipoDenuncia === 'perfil' ? 'o perfil' : 'a vaga'; // Explicação DevIN: Atribui um valor à variável PHP $tipoPt.
        $tipoEn = $tipoDenuncia === 'perfil' ? 'profile' : 'job'; // Explicação DevIN: Atribui um valor à variável PHP $tipoEn.
        $tipoEs = $tipoDenuncia === 'perfil' ? 'el perfil' : 'el empleo'; // Explicação DevIN: Atribui um valor à variável PHP $tipoEs.
        $assunto = self::mensagem( // Explicação DevIN: Atribui um valor à variável PHP $assunto.
            $idioma, // Explicação DevIN: Continua expressão ou bloco pertencente a função enviarConfirmacaoDenuncia.
            'Recebemos sua denúncia - DevIN', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'We received your report - DevIN', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'Recibimos tu denuncia - DevIN' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $corpo = '<div style="font-family:Arial,sans-serif;max-width:600px;margin:0 auto;padding:24px;color:#243b53">' // Explicação DevIN: Atribui um valor à variável PHP $corpo.
            . '<h2>' . self::mensagem($idioma, 'Olá, ', 'Hello, ', 'Hola, ') . $nomeSeguro . '!</h2>' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            . '<p>' . self::mensagem( // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                $idioma, // Explicação DevIN: Continua expressão ou bloco pertencente a função enviarConfirmacaoDenuncia.
                'Recebemos sua denúncia sobre ' . $tipoPt . ' <strong>' . $alvoSeguro . '</strong>. Nossa equipe está analisando o caso.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'We received your report about the ' . $tipoEn . ' <strong>' . $alvoSeguro . '</strong>. Our team is reviewing it.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'Recibimos tu denuncia sobre ' . $tipoEs . ' <strong>' . $alvoSeguro . '</strong>. Nuestro equipo está revisando el caso.' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            ) . '</p><p>' . self::mensagem($idioma, 'Você receberá uma mensagem na plataforma quando a análise for concluída.', 'You will receive a message on the platform when the review is complete.', 'Recibirás un mensaje en la plataforma cuando finalice la revisión.') . '</p>' // Explicação DevIN: Continua expressão ou bloco pertencente a função enviarConfirmacaoDenuncia.
            . '<p>Support DevIN</p></div>'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.

        return self::enviar($destinatarioEmail, $destinatarioNome, $assunto, $corpo); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /**
     * Envia e-mail após o cadastro do currículo.
     */
    // Detalhe DevIN: Método enviarConfirmacaoCadastroCurriculo: encapsula uma responsabilidade reutilizável do módulo no contexto de mailer helper. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function enviarConfirmacaoCadastroCurriculo( // Explicação DevIN: Declara função/método enviarConfirmacaoCadastroCurriculo, com seus parâmetros e comportamento.
        string $emailCandidato, // Explicação DevIN: Continua expressão ou bloco pertencente a função enviarConfirmacaoCadastroCurriculo.
        string $nomeCandidato, // Explicação DevIN: Continua expressão ou bloco pertencente a função enviarConfirmacaoCadastroCurriculo.
        string $idioma = 'pt-BR' // Explicação DevIN: Continua expressão ou bloco pertencente a função enviarConfirmacaoCadastroCurriculo.
    ): bool { // Explicação DevIN: Continua expressão ou bloco pertencente a função enviarConfirmacaoCadastroCurriculo.
        // Escapa dados variáveis antes de inseri-los nos modelos de e-mail HTML.
        $nomeSeguro = htmlspecialchars($nomeCandidato, ENT_QUOTES, 'UTF-8'); // Explicação DevIN: Atribui um valor à variável PHP $nomeSeguro.
        $assunto = self::mensagem($idioma, 'Cadastro e Currículo Concluídos - DevIN', 'Registration and Resume Completed - DevIN', 'Registro y currículum completados - DevIN'); // Explicação DevIN: Atribui um valor à variável PHP $assunto.

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
        "; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.

        return self::enviar($emailCandidato, $nomeCandidato, $assunto, $corpo); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Método enviarConfirmacaoCadastroEmpresa: encapsula uma responsabilidade reutilizável do módulo no contexto de mailer helper. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function enviarConfirmacaoCadastroEmpresa(string $email, string $nome, string $idioma = 'pt-BR'): bool // Explicação DevIN: Declara função/método enviarConfirmacaoCadastroEmpresa, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $nomeSeguro = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'); // Explicação DevIN: Atribui um valor à variável PHP $nomeSeguro.
        $corpo = "<div style='font-family:Arial,sans-serif;padding:24px'>" // Explicação DevIN: Atribui um valor à variável PHP $corpo.
            . '<h2>' . self::mensagem($idioma, "Olá, {$nomeSeguro}!", "Hello, {$nomeSeguro}!", "¡Hola, {$nomeSeguro}!") . '</h2>' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            . '<p>' . self::mensagem($idioma, 'O cadastro da sua empresa foi concluído com sucesso.', 'Your company registration was completed successfully.', 'El registro de tu empresa se completó correctamente.') . '</p>' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            . '<p>' . self::mensagem($idioma, 'Agora você pode publicar vagas e acompanhar as candidaturas pela plataforma.', 'You can now post jobs and manage applications on the platform.', 'Ahora puedes publicar empleos y gestionar las solicitudes en la plataforma.') . '</p>' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            . "<p><a href='" . APP_BASE_URL . "/php/empresa.php'>" . self::mensagem($idioma, 'Acessar minha conta', 'Access my account', 'Acceder a mi cuenta') . '</a></p></div>'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.

        return self::enviar($email, $nome, self::mensagem($idioma, 'Cadastro da empresa concluído - DevIN', 'Company registration completed - DevIN', 'Registro de empresa completado - DevIN'), $corpo); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Método enviarConfirmacaoCadastro: encapsula uma responsabilidade reutilizável do módulo no contexto de mailer helper. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function enviarConfirmacaoCadastro(string $email, string $nome, string $idioma = 'pt-BR'): bool // Explicação DevIN: Declara função/método enviarConfirmacaoCadastro, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $nomeSeguro = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'); // Explicação DevIN: Atribui um valor à variável PHP $nomeSeguro.
        $corpo = "<div style='font-family:Arial,sans-serif;padding:24px'>" // Explicação DevIN: Atribui um valor à variável PHP $corpo.
            . '<h2>' . self::mensagem($idioma, "Olá, {$nomeSeguro}!", "Hello, {$nomeSeguro}!", "¡Hola, {$nomeSeguro}!") . '</h2>' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            . '<p>' . self::mensagem($idioma, 'Sua conta foi criada com sucesso na plataforma DevIN.', 'Your account was successfully created on the DevIN platform.', 'Tu cuenta se creó correctamente en la plataforma DevIN.') . '</p>' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            . '<p>' . self::mensagem($idioma, 'Conclua seu currículo para que empresas possam encontrar seu perfil.', 'Complete your resume so companies can find your profile.', 'Completa tu currículum para que las empresas puedan encontrar tu perfil.') . '</p></div>'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.

        return self::enviar($email, $nome, self::mensagem($idioma, 'Cadastro confirmado - DevIN', 'Registration confirmed - DevIN', 'Registro confirmado - DevIN'), $corpo); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Método enviarConfirmacaoExclusao: encapsula uma responsabilidade reutilizável do módulo no contexto de mailer helper. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function enviarConfirmacaoExclusao(string $email, string $nome, string $tipo, string $idioma = 'pt-BR'): bool // Explicação DevIN: Declara função/método enviarConfirmacaoExclusao, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!in_array($tipo, ['pessoa', 'empresa'], true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            return false; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $nomeSeguro = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'); // Explicação DevIN: Atribui um valor à variável PHP $nomeSeguro.
        $corpo = "<div style='font-family:Arial,sans-serif;padding:24px'>" // Explicação DevIN: Atribui um valor à variável PHP $corpo.
            . '<h2>' . self::mensagem($idioma, "Olá, {$nomeSeguro}.", "Hello, {$nomeSeguro}.", "Hola, {$nomeSeguro}.") . '</h2>' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            . '<p>' . self::mensagem($idioma, 'A exclusão da sua conta no DevIN foi concluída.', 'Your DevIN account has been deleted.', 'La eliminación de tu cuenta de DevIN se completó.') . '</p>' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            . '<p>' . self::mensagem($idioma, 'Se você não solicitou essa ação, entre em contato com a equipe do DevIN.', 'If you did not request this, contact the DevIN team.', 'Si no solicitaste esta acción, ponte en contacto con el equipo de DevIN.') . '</p></div>'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.

        return self::enviar($email, $nome, self::mensagem($idioma, 'Exclusão da conta concluída - DevIN', 'Account deletion completed - DevIN', 'Eliminación de cuenta completada - DevIN'), $corpo); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Método enviarAvisoAlteracaoSenha: encapsula uma responsabilidade reutilizável do módulo no contexto de mailer helper. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function enviarAvisoAlteracaoSenha(string $email, string $nome, string $idioma = 'pt-BR'): bool // Explicação DevIN: Declara função/método enviarAvisoAlteracaoSenha, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $nomeSeguro = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'); // Explicação DevIN: Atribui um valor à variável PHP $nomeSeguro.
        $corpo = "<div style='font-family:Arial,sans-serif;padding:24px'>" // Explicação DevIN: Atribui um valor à variável PHP $corpo.
            . '<h2>' . self::mensagem($idioma, "Olá, {$nomeSeguro}.", "Hello, {$nomeSeguro}.", "Hola, {$nomeSeguro}.") . '</h2>' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            . '<p>' . self::mensagem($idioma, 'A senha da sua conta DevIN foi alterada com sucesso.', 'Your DevIN account password has been changed successfully.', 'La contraseña de tu cuenta DevIN se cambió correctamente.') . '</p>' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            . '<p>' . self::mensagem($idioma, 'Se você não fez essa alteração, recupere o acesso à sua conta e entre em contato com o suporte.', 'If you did not make this change, recover access to your account and contact support.', 'Si no realizaste este cambio, recupera el acceso a tu cuenta y ponte en contacto con soporte.') . '</p></div>'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.

        return self::enviar($email, $nome, self::mensagem($idioma, 'Sua senha foi alterada - DevIN', 'Your password has been changed - DevIN', 'Tu contraseña fue cambiada - DevIN'), $corpo); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Método enviarCodigoRecuperacao6: encapsula uma responsabilidade reutilizável do módulo no contexto de mailer helper. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function enviarCodigoRecuperacao6(string $email, string $nome, string $codigo, string $idioma = 'pt-BR'): bool // Explicação DevIN: Declara função/método enviarCodigoRecuperacao6, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // Aceita somente seis caracteres alfanuméricos antes de montar a mensagem de recuperação.
        $codigo = strtoupper($codigo); // Explicação DevIN: Atribui um valor à variável PHP $codigo.
        if (!preg_match('/^[A-Z0-9]{6}$/', $codigo)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            return false; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $codigoSeguro = htmlspecialchars(substr($codigo, 0, 3) . ' ' . substr($codigo, 3), ENT_QUOTES, 'UTF-8'); // Explicação DevIN: Atribui um valor à variável PHP $codigoSeguro.
        $nomeSeguro   = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'); // Explicação DevIN: Atribui um valor à variável PHP $nomeSeguro.

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
        "; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.

        return self::enviar($email, $nome, self::mensagem($idioma, 'Código de recuperação de senha - DevIN', 'Password recovery code - DevIN', 'Código de recuperación de contraseña - DevIN'), $corpo); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.


    /**
     * Envia lembrete para pessoa que ainda não criou o currículo.
     */
    // Detalhe DevIN: Método enviarLembreteCurriculoPendente: encapsula uma responsabilidade reutilizável do módulo no contexto de mailer helper. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function enviarLembreteCurriculoPendente( // Explicação DevIN: Declara função/método enviarLembreteCurriculoPendente, com seus parâmetros e comportamento.
        string $emailCandidato, // Explicação DevIN: Continua expressão ou bloco pertencente a função enviarLembreteCurriculoPendente.
        string $nomeCandidato, // Explicação DevIN: Continua expressão ou bloco pertencente a função enviarLembreteCurriculoPendente.
        string $idioma = 'pt-BR' // Explicação DevIN: Continua expressão ou bloco pertencente a função enviarLembreteCurriculoPendente.
    ): bool { // Explicação DevIN: Continua expressão ou bloco pertencente a função enviarLembreteCurriculoPendente.
        $nomeSeguro = htmlspecialchars($nomeCandidato, ENT_QUOTES, 'UTF-8'); // Explicação DevIN: Atribui um valor à variável PHP $nomeSeguro.
        $assunto = self::mensagem($idioma, 'Falta pouco! Complete seu currículo no DevIN', 'Almost there! Complete your resume on DevIN', '¡Ya casi! Completa tu currículum en DevIN'); // Explicação DevIN: Atribui um valor à variável PHP $assunto.

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
        "; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.

        return self::enviar($emailCandidato, $nomeCandidato, $assunto, $corpo); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /**
     * Notifica as empresas sobre um novo candidato que concluiu o currículo.
     */
    // Detalhe DevIN: Método notificarEmpresasNovoCandidato: encapsula uma responsabilidade reutilizável do módulo no contexto de mailer helper. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function notificarEmpresasNovoCandidato( // Explicação DevIN: Declara função/método notificarEmpresasNovoCandidato, com seus parâmetros e comportamento.
        mysqli $conn, // Explicação DevIN: Continua expressão ou bloco pertencente a função notificarEmpresasNovoCandidato.
        string $nomeCandidato // Explicação DevIN: Continua expressão ou bloco pertencente a função notificarEmpresasNovoCandidato.
    ): void { // Explicação DevIN: Continua expressão ou bloco pertencente a função notificarEmpresasNovoCandidato.
        // Busca destinos cadastrados uma vez; cada endereço é validado antes do envio individual.
        $query = "
            SELECT e.id_empresa, e.nome, e.email
            FROM empresa e
            WHERE e.email IS NOT NULL AND e.email <> ''
        "; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.

        $result = $conn->query($query); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.

        if ($result === false) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            error_log('Erro ao buscar empresas para notificação: ' . $conn->error); // Explicação DevIN: Continua expressão ou bloco pertencente a função notificarEmpresasNovoCandidato.
            return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if ($result->num_rows === 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $nomeSeguro = htmlspecialchars($nomeCandidato, ENT_QUOTES, 'UTF-8'); // Explicação DevIN: Atribui um valor à variável PHP $nomeSeguro.

        while ($empresa = $result->fetch_assoc()) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            $nomeEmpresaOriginal = $empresa['nome'] ?? 'Empresa'; // Explicação DevIN: Atribui um valor à variável PHP $nomeEmpresaOriginal.
            $nomeEmpresa         = htmlspecialchars($nomeEmpresaOriginal, ENT_QUOTES, 'UTF-8'); // Explicação DevIN: Atribui um valor à variável PHP $nomeEmpresa.
            $emailEmpresa        = trim($empresa['email'] ?? ''); // Explicação DevIN: Atribui um valor à variável PHP $emailEmpresa.
            $idioma = ProfileLanguagePreference::load($conn, 'empresa', (int) $empresa['id_empresa']); // Explicação DevIN: Atribui um valor à variável PHP $idioma.

            if ($emailEmpresa === '' || !filter_var($emailEmpresa, FILTER_VALIDATE_EMAIL)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                continue; // Explicação DevIN: Controla a continuação ou saída de laço/seleção.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $assunto = self::mensagem($idioma, 'Novo Candidato Disponível - DevIN', 'New Candidate Available - DevIN', 'Nuevo candidato disponible - DevIN'); // Explicação DevIN: Atribui um valor à variável PHP $assunto.

            $corpo = "
                <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f6f9;'>
                    <div style='max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; padding: 30px;'>
                        <h2 style='color: #2b56f5;'>" . self::mensagem($idioma, "Olá, {$nomeEmpresa}!", "Hello, {$nomeEmpresa}!", "¡Hola, {$nomeEmpresa}!") . "</h2>
                        <p>" . self::mensagem($idioma, 'Um novo candidato está disponível na plataforma <strong>DevIN</strong>.', 'A new candidate is available on the <strong>DevIN</strong> platform.', 'Hay un nuevo candidato disponible en la plataforma <strong>DevIN</strong>.') . "</p>
                        <p>" . self::mensagem($idioma, "O candidato <strong>{$nomeSeguro}</strong> acabou de concluir o currículo na plataforma.", "Candidate <strong>{$nomeSeguro}</strong> has just completed their resume on the platform.", "El candidato <strong>{$nomeSeguro}</strong> acaba de completar su currículum en la plataforma.") . "</p>
                        <p>" . self::mensagem($idioma, 'Acesse a plataforma para consultar os candidatos disponíveis.', 'Visit the platform to view available candidates.', 'Accede a la plataforma para consultar los candidatos disponibles.') . "</p>
                    </div>
                </div>
            "; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.

            self::enviar($emailEmpresa, $nomeEmpresaOriginal, $assunto, $corpo); // Explicação DevIN: Continua expressão ou bloco pertencente a função notificarEmpresasNovoCandidato.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $result->free(); // Explicação DevIN: Continua expressão ou bloco pertencente a função notificarEmpresasNovoCandidato.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
