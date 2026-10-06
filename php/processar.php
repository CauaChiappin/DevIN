<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

$envDirectory = dirname(__DIR__);
if (is_file($envDirectory . '/.env')) {
    Dotenv\Dotenv::createImmutable($envDirectory)->safeLoad();
}

require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middlewares/auth.php';
require_once __DIR__ . '/MailerHelper.php';
require_once __DIR__ . '/config/RateLimiter.php';

startSecureSession();

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: ../index.php');
    exit;
}

$acao = requestString($_POST, 'acao');
if (!verifyCsrfToken(requestString($_POST, 'csrf_token'))) {
    if ($acao === 'cadastrar_curriculo') {
        $_SESSION['erro_curriculo'] = 'Sessão inválida ou expirada. Atualize a página e tente novamente.';
        header('Location: cadastrar_curriculo.php');
    } else {
        $_SESSION['erro_recuperacao'] = 'Sessão inválida ou expirada. Atualize a página e tente novamente.';
        header('Location: recuperacao.php');
    }
    exit;
}

<<<<<<< HEAD
    $ipBlocked = RateLimiter::consume('recovery:ip:' . RateLimiter::clientIp(), 10, 3600);
    $emailBlocked = RateLimiter::consume('recovery:email:' . strtolower($email), 3, 3600);
    if ($ipBlocked > 0 || $emailBlocked > 0) {
        $_SESSION['email_recuperacao'] = $email;
        $_SESSION['sucesso_recuperacao'] = 'Se houver uma conta com esse e-mail, enviaremos as instruções de redefinição.';
        header('Location: redefinir.php');
        exit;
    }
=======
switch ($acao) {
    case 'solicitar_recuperacao':
        $email = requestString($_POST, 'email');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['erro_recuperacao'] = 'Informe um e-mail válido.';
            header('Location: recuperacao.php');
            exit;
        }
>>>>>>> bb0413abcd2a8c17f9c53b600f5a5acb10a41c97

        $conn = null;
        try {
            $conn = getDatabaseConnection();
            $usuario = null;
            $tabelas = [
                ['tabela' => 'pessoa', 'id' => 'id_pessoa', 'tipo' => 'pessoa'],
                ['tabela' => 'empresa', 'id' => 'id_empresa', 'tipo' => 'empresa'],
            ];

            foreach ($tabelas as $item) {
                $sql = "SELECT {$item['id']} AS id, nome, email FROM {$item['tabela']} WHERE email = ? LIMIT 1";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $usuario = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if ($usuario !== null) {
                    $usuario['tipo'] = $item['tipo'];
                    $usuario['tabela'] = $item['tabela'];
                    $usuario['id_coluna'] = $item['id'];
                    break;
                }
            }

            if ($usuario === null) {
                $_SESSION['erro_recuperacao'] = 'E-mail não encontrado no sistema.';
                header('Location: recuperacao.php');
                exit;
            }

            $codigo = '';
            $alfabeto = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            for ($i = 0; $i < 6; $i++) {
                $codigo .= $alfabeto[random_int(0, strlen($alfabeto) - 1)];
            }
            $codigoHash = hash('sha256', $codigo);
            $stmt = $conn->prepare("UPDATE {$usuario['tabela']} SET token_recuperacao = ?, token_expiracao = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE {$usuario['id_coluna']} = ?");
            $idUsuario = (int) $usuario['id'];
            $stmt->bind_param('si', $codigoHash, $idUsuario);
            $stmt->execute();
            $stmt->close();

<<<<<<< HEAD
            $mapaTabelas = [
                'pessoa'  => ['tabela' => 'pessoa',        'idCol' => 'id_pessoa'],
                'empresa' => ['tabela' => 'empresa',       'idCol' => 'id_empresa'],
                'adm'     => ['tabela' => 'administrador', 'idCol' => 'id_administrador'],
            ];

            $info = $mapaTabelas[$usuario['tipo']];

            // Código válido por 15 minutos
            $sql = "
                UPDATE {$info['tabela']}
                SET
                    token_recuperacao = ?,
                    token_expiracao = DATE_ADD(NOW(), INTERVAL 15 MINUTE)
                WHERE {$info['idCol']} = ?
            ";

            $stmtToken = $conn->prepare($sql);
            if ($stmtToken) {
                $stmtToken->bind_param('si', $codigoHash, $usuario['id']);
                $stmtToken->execute();
                $stmtToken->close();
            }

            // Formatação legível do código (ex: 1234 5678)
            $codigoFormatado = substr($codigo, 0, 4) . ' ' . substr($codigo, 4, 4);
            $nomeSeguro = htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8');
            $assunto = 'Seu Código de Recuperação - DevIN';

            $corpoHtml = "
                <div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f4f6f9;'>
                    <div style='max-width: 500px; margin: 0 auto; background: #ffffff; padding: 25px; border-radius: 8px;'>
                        <h2 style='color: #2b56f5;'>Olá, {$nomeSeguro}!</h2>
                        <p>Recebemos uma solicitação para redefinir a senha da sua conta no <strong>DevIN</strong>.</p>
                        <p>Utilize o código de verificação abaixo para criar uma nova senha:</p>
                        <div style='text-align: center; margin: 25px 0;'>
                            <span style='background-color: #2b56f5; color: #ffffff; padding: 14px 28px; border-radius: 6px; font-size: 28px; font-weight: bold; letter-spacing: 4px; display: inline-block;'>
                                {$codigoFormatado}
                            </span>
                        </div>
                        <p>Este código expira em <strong>15 minutos</strong>.</p>
                        <p style='color: #777; font-size: 12px;'>Se você não solicitou esta alteração, desconsidere este e-mail.</p>
                    </div>
                </div>
            ";

            if (!MailerHelper::enviar($email, $usuario['nome'], $assunto, $corpoHtml)) {
                error_log('Falha ao enviar e-mail de recuperação para ' . $email);
            }
        }

        // Armazena e-mail na sessão para a página redefinir.php
        $_SESSION['email_recuperacao'] = $email;
        $_SESSION['sucesso_recuperacao'] = 'Se houver uma conta com esse e-mail, enviaremos as instruções de redefinição.';

        header('Location: redefinir.php');
        exit;

    } catch (Throwable $e) {
        error_log('Erro na recuperação de senha: ' . $e->getMessage());
        $_SESSION['erro_recuperacao'] = 'Não foi possível processar a solicitação. Tente novamente.';
        header('Location: recuperacao.php');
        exit;
    } finally {
        $conn->close();
    }
}

/*
|--------------------------------------------------------------------------
| REDEFINIR SENHA COM CÓDIGO DE 8 DÍGITOS
|--------------------------------------------------------------------------
*/

if ($acao === 'redefinir_senha') {
    $email     = trim(requestString($_POST, 'email'));
    $codigo    = preg_replace('/[^0-9]/', '', requestString($_POST, 'codigo'));
    $novaSenha = requestString($_POST, 'nova_senha');
    $confSenha = requestString($_POST, 'confirmar_senha');

    $ipBlocked = RateLimiter::consume('reset:ip:' . RateLimiter::clientIp(), 20, 3600);
    $emailBlocked = RateLimiter::consume('reset:email:' . strtolower($email), 10, 3600);
    if ($ipBlocked > 0 || $emailBlocked > 0) {
        $_SESSION['erro_redefinir'] = 'Muitas tentativas. Aguarde antes de tentar novamente.';
        header('Location: redefinir.php');
        exit;
    }

    if ($email === '' || !hash_equals((string) ($_SESSION['email_recuperacao'] ?? ''), $email)) {
        $_SESSION['erro_redefinir'] = 'Solicite um novo código de recuperação.';
        header('Location: recuperacao.php');
        exit;
    }

    if (empty($email) || empty($codigo) || empty($novaSenha) || empty($confSenha)) {
        $_SESSION['erro_redefinir'] = 'Preencha todos os campos.';
        header('Location: redefinir.php');
        exit;
    }

    if (strlen($codigo) !== 8) {
        $_SESSION['erro_redefinir'] = 'O código deve possuir exatamente 8 dígitos.';
        header('Location: redefinir.php');
        exit;
    }

    if ($novaSenha !== $confSenha) {
        $_SESSION['erro_redefinir'] = 'As senhas não coincidem.';
        header('Location: redefinir.php');
        exit;
    }

    if (strlen($novaSenha) < 12) {
        $_SESSION['erro_redefinir'] = 'A senha deve ter no mínimo 12 caracteres.';
        header('Location: redefinir.php');
        exit;
    }

    if (!preg_match('/[A-Z]/', $novaSenha)) {
        $_SESSION['erro_redefinir'] = 'A senha deve possuir pelo menos uma letra maiúscula.';
        header('Location: redefinir.php');
        exit;
    }

    if (!preg_match('/[^a-zA-Z0-9]/', $novaSenha)) {
        $_SESSION['erro_redefinir'] = 'A senha deve possuir pelo menos um caractere especial.';
        header('Location: redefinir.php');
        exit;
    }

    $conn = getDatabaseConnection();

    try {
        $senhaHash  = password_hash($novaSenha, PASSWORD_DEFAULT);
        $codigoHash = hash('sha256', $codigo);
        $afetados   = 0;
        $tabelas    = ['pessoa', 'empresa', 'administrador'];

        foreach ($tabelas as $tabela) {
            $stmt = $conn->prepare("
                UPDATE {$tabela}
                SET
                    senha_hash = ?,
                    token_recuperacao = NULL,
                    token_expiracao = NULL
                WHERE
                    email = ?
                    AND token_recuperacao = ?
                    AND token_expiracao > NOW()
            ");

            if ($stmt) {
                $stmt->bind_param('sss', $senhaHash, $email, $codigoHash);
=======
            if (!MailerHelper::enviarCodigoRecuperacao6($email, (string) $usuario['nome'], $codigo)) {
                $stmt = $conn->prepare("UPDATE {$usuario['tabela']} SET token_recuperacao = NULL, token_expiracao = NULL WHERE {$usuario['id_coluna']} = ?");
                $stmt->bind_param('i', $idUsuario);
>>>>>>> bb0413abcd2a8c17f9c53b600f5a5acb10a41c97
                $stmt->execute();
                $stmt->close();
                $_SESSION['erro_recuperacao'] = 'Não foi possível enviar o e-mail. Confira a configuração SMTP e tente novamente.';
                header('Location: recuperacao.php');
                exit;
            }

            $_SESSION['recuperacao_email'] = $email;
            $_SESSION['recuperacao_tipo'] = $usuario['tipo'];
            $_SESSION['recuperacao_id'] = $idUsuario;
            $_SESSION['recuperacao_nome'] = (string) $usuario['nome'];
            $_SESSION['codigo_recuperacao_hash'] = $codigoHash;
            $_SESSION['ultimo_envio_codigo'] = time();
            unset($_SESSION['recuperacao_verificada']);
            $_SESSION['sucesso_codigo'] = 'Enviamos um código para o e-mail informado.';
            header('Location: codigo-senha.php');
        } catch (Throwable $e) {
            error_log($e->getMessage());
            $_SESSION['erro_recuperacao'] = 'Não foi possível processar a recuperação agora. Tente novamente.';
            header('Location: recuperacao.php');
        } finally {
            if ($conn instanceof mysqli) {
                $conn->close();
            }
        }
        exit;

<<<<<<< HEAD
        if ($afetados > 0) {
            unset($_SESSION['email_recuperacao']);
            RateLimiter::clear('reset:ip:' . RateLimiter::clientIp());
            RateLimiter::clear('reset:email:' . strtolower($email));
            session_regenerate_id(true);
=======
    case 'validar_codigo':
        $codigoDigitado = strtoupper((string) preg_replace('/[^a-zA-Z0-9]/', '', requestString($_POST, 'codigo')));
        $email = (string) ($_SESSION['recuperacao_email'] ?? '');
        $hashSessao = (string) ($_SESSION['codigo_recuperacao_hash'] ?? '');
        $codigoCorrespondeASessao = strlen($codigoDigitado) === 6
            && $hashSessao !== ''
            && hash_equals($hashSessao, hash('sha256', $codigoDigitado));
>>>>>>> bb0413abcd2a8c17f9c53b600f5a5acb10a41c97

        if ($email === '') {
            $_SESSION['erro_recuperacao'] = 'Sessão expirada. Inicie a recuperação novamente.';
            header('Location: recuperacao.php');
            exit;
        }
        if (!$codigoCorrespondeASessao) {
            $_SESSION['erro_codigo'] = 'Código incorreto ou expirado.';
            header('Location: codigo-senha.php');
            exit;
        }

        $conn = null;
        try {
            $conn = getDatabaseConnection();
            $codigoHash = hash('sha256', $codigoDigitado);
            $tabelas = [
                ['tabela' => 'pessoa', 'id' => 'id_pessoa', 'tipo' => 'pessoa'],
                ['tabela' => 'empresa', 'id' => 'id_empresa', 'tipo' => 'empresa'],
            ];
            $usuario = null;
            foreach ($tabelas as $item) {
                $stmt = $conn->prepare("SELECT {$item['id']} AS id, nome, email FROM {$item['tabela']} WHERE email = ? AND token_recuperacao = ? AND token_expiracao > NOW() LIMIT 1");
                $stmt->bind_param('ss', $email, $codigoHash);
                $stmt->execute();
                $usuario = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if ($usuario !== null) {
                    $usuario['tipo'] = $item['tipo'];
                    $usuario['tabela'] = $item['tabela'];
                    $usuario['id_coluna'] = $item['id'];
                    break;
                }
            }

            if ($usuario === null) {
                unset($_SESSION['codigo_recuperacao_hash']);
                $_SESSION['erro_codigo'] = 'Código incorreto ou expirado.';
                header('Location: codigo-senha.php');
                exit;
            }

            $idUsuario = (int) $usuario['id'];
            $stmt = $conn->prepare("UPDATE {$usuario['tabela']} SET token_recuperacao = NULL, token_expiracao = NULL WHERE {$usuario['id_coluna']} = ?");
            $stmt->bind_param('i', $idUsuario);
            $stmt->execute();
            $stmt->close();

            $_SESSION['recuperacao_verificada'] = true;
            $_SESSION['recuperacao_id'] = $idUsuario;
            $_SESSION['recuperacao_tipo'] = $usuario['tipo'];
            $_SESSION['recuperacao_nome'] = (string) $usuario['nome'];
            $_SESSION['recuperacao_email'] = (string) $usuario['email'];
            unset($_SESSION['codigo_recuperacao_hash']);
            header('Location: redefinir.php');
        } catch (Throwable $e) {
            error_log($e->getMessage());
            $_SESSION['erro_codigo'] = 'Não foi possível validar o código agora. Tente novamente.';
            header('Location: codigo-senha.php');
        } finally {
            if ($conn instanceof mysqli) {
                $conn->close();
            }
        }
        exit;

    case 'reenviar_codigo':
        $email = (string) ($_SESSION['recuperacao_email'] ?? '');
        $idUsuario = (int) ($_SESSION['recuperacao_id'] ?? 0);
        $tipo = (string) ($_SESSION['recuperacao_tipo'] ?? '');
        $nome = (string) ($_SESSION['recuperacao_nome'] ?? '');
        $ultimoEnvio = (int) ($_SESSION['ultimo_envio_codigo'] ?? 0);

        if ($email === '' || $idUsuario < 1 || !in_array($tipo, ['pessoa', 'empresa'], true)) {
            $_SESSION['erro_recuperacao'] = 'Sessão expirada. Inicie a recuperação novamente.';
            header('Location: recuperacao.php');
            exit;
        }
        if (time() - $ultimoEnvio < 60) {
            $restante = 60 - (time() - $ultimoEnvio);
            $_SESSION['erro_codigo'] = "Aguarde {$restante}s antes de solicitar outro código.";
            header('Location: codigo-senha.php');
            exit;
        }

        $tabela = $tipo === 'empresa' ? 'empresa' : 'pessoa';
        $idColuna = $tipo === 'empresa' ? 'id_empresa' : 'id_pessoa';
        $codigo = '';
        $alfabeto = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for ($i = 0; $i < 6; $i++) {
            $codigo .= $alfabeto[random_int(0, strlen($alfabeto) - 1)];
        }
        $codigoHash = hash('sha256', $codigo);
        $conn = null;
        try {
            $conn = getDatabaseConnection();
            $stmt = $conn->prepare("UPDATE {$tabela} SET token_recuperacao = ?, token_expiracao = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE {$idColuna} = ?");
            $stmt->bind_param('si', $codigoHash, $idUsuario);
            $stmt->execute();
            $stmt->close();

            if (!MailerHelper::enviarCodigoRecuperacao6($email, $nome, $codigo)) {
                $stmt = $conn->prepare("UPDATE {$tabela} SET token_recuperacao = NULL, token_expiracao = NULL WHERE {$idColuna} = ?");
                $stmt->bind_param('i', $idUsuario);
                $stmt->execute();
                $stmt->close();
                unset($_SESSION['codigo_recuperacao_hash']);
                $_SESSION['erro_codigo'] = 'Não foi possível reenviar o código. Tente novamente mais tarde.';
                header('Location: codigo-senha.php');
                exit;
            }

            $_SESSION['codigo_recuperacao_hash'] = $codigoHash;
            $_SESSION['ultimo_envio_codigo'] = time();
            $_SESSION['sucesso_codigo'] = 'Novo código enviado com sucesso.';
            header('Location: codigo-senha.php');
        } catch (Throwable $e) {
            error_log($e->getMessage());
            $_SESSION['erro_codigo'] = 'Não foi possível reenviar o código agora. Tente novamente.';
            header('Location: codigo-senha.php');
        } finally {
            if ($conn instanceof mysqli) {
                $conn->close();
            }
        }
        exit;

    case 'redefinir_senha':
        if (empty($_SESSION['recuperacao_verificada'])) {
            header('Location: recuperacao.php');
            exit;
        }

        $novaSenha = requestString($_POST, 'nova_senha');
        $confirmarSenha = requestString($_POST, 'confirmar_senha');
        $idUsuario = (int) ($_SESSION['recuperacao_id'] ?? 0);
        $tipo = (string) ($_SESSION['recuperacao_tipo'] ?? '');
        if ($idUsuario < 1 || !in_array($tipo, ['pessoa', 'empresa'], true)) {
            unset($_SESSION['recuperacao_verificada']);
            $_SESSION['erro_recuperacao'] = 'Sessão expirada. Inicie a recuperação novamente.';
            header('Location: recuperacao.php');
            exit;
        }
        if ($novaSenha !== $confirmarSenha) {
            $_SESSION['erro_redefinir'] = 'As senhas não coincidem.';
            header('Location: redefinir.php');
            exit;
        }
        if (strlen($novaSenha) < 8 || !preg_match('/[A-Z]/', $novaSenha) || !preg_match('/[^a-zA-Z0-9]/', $novaSenha)) {
            $_SESSION['erro_redefinir'] = 'A senha não preenche todos os requisitos de segurança.';
            header('Location: redefinir.php');
            exit;
        }

        $tabela = $tipo === 'empresa' ? 'empresa' : 'pessoa';
        $idColuna = $tipo === 'empresa' ? 'id_empresa' : 'id_pessoa';
        $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
        $conn = null;
        try {
            $conn = getDatabaseConnection();
            $stmt = $conn->prepare("UPDATE {$tabela} SET senha_hash = ? WHERE {$idColuna} = ?");
            $stmt->bind_param('si', $senhaHash, $idUsuario);
            $stmt->execute();
            if ($stmt->affected_rows < 1) {
                throw new RuntimeException('A senha não foi atualizada.');
            }
            $stmt->close();

            $nomeUsuario = (string) ($_SESSION['recuperacao_nome'] ?? 'Usuário');
            $emailUsuario = (string) ($_SESSION['recuperacao_email'] ?? '');
            $emailAvisoEnviado = filter_var($emailUsuario, FILTER_VALIDATE_EMAIL)
                && MailerHelper::enviarAvisoAlteracaoSenha($emailUsuario, $nomeUsuario);

            secureSessionRegenerate();
            $_SESSION['logado'] = true;
            $_SESSION['usuario_id'] = $idUsuario;
            $_SESSION['usuario_tipo'] = $tipo;
            $_SESSION['usuario_nome'] = $nomeUsuario;
            $_SESSION['usuario_email'] = $emailUsuario;
            unset($_SESSION['recuperacao_verificada'], $_SESSION['recuperacao_id'], $_SESSION['recuperacao_tipo'], $_SESSION['recuperacao_email'], $_SESSION['recuperacao_nome']);
            $_SESSION['sucesso_login'] = 'Senha alterada com sucesso.';
            if (!$emailAvisoEnviado) {
                $_SESSION['erro_email_senha'] = 'A senha foi alterada, mas não foi possível enviar o aviso por e-mail.';
            }
            header('Location: ' . ($tipo === 'empresa' ? 'empresa.php' : 'pessoa.php'));
        } catch (Throwable $e) {
            error_log($e->getMessage());
            $_SESSION['erro_redefinir'] = 'Não foi possível atualizar a senha agora. Tente novamente.';
            header('Location: redefinir.php');
        } finally {
            if ($conn instanceof mysqli) {
                $conn->close();
            }
        }
        exit;

    case 'cadastrar_curriculo':
        $conn = null;
        try {
            $usuario = requireWebAuth('pessoa');
            $idPessoa = (int) $usuario['id'];
            $nomePessoa = (string) ($usuario['nome'] ?? 'Candidato');
            $emailPessoa = (string) ($usuario['email'] ?? '');
            $nomeSocial = requestString($_POST, 'nome_social');
            $grauEscolaridade = requestString($_POST, 'grau_de_escolaridade');
            $cursos = requestString($_POST, 'cursos');
            $experiencia = requestString($_POST, 'experiencia');
            $idiomas = requestString($_POST, 'idiomas');

            if ($nomeSocial === '' || $grauEscolaridade === '') {
                throw new InvalidArgumentException('Informe o nome social e o grau de escolaridade.');
            }

            $conn = getDatabaseConnection();
            $stmt = $conn->prepare('SELECT id_curriculo FROM curriculo WHERE id_pessoa = ? LIMIT 1');
            $stmt->bind_param('i', $idPessoa);
            $stmt->execute();
            $jaExiste = $stmt->get_result()->num_rows > 0;
            $stmt->close();

            if ($jaExiste) {
                $stmt = $conn->prepare('UPDATE curriculo SET nome_social = ?, grau_de_escolaridade = ?, cursos = ?, experiencia = ?, idiomas = ? WHERE id_pessoa = ?');
                $stmt->bind_param('sssssi', $nomeSocial, $grauEscolaridade, $cursos, $experiencia, $idiomas, $idPessoa);
            } else {
                $stmt = $conn->prepare('INSERT INTO curriculo (id_pessoa, nome_social, grau_de_escolaridade, cursos, experiencia, idiomas) VALUES (?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('isssss', $idPessoa, $nomeSocial, $grauEscolaridade, $cursos, $experiencia, $idiomas);
            }
            $stmt->execute();
            $stmt->close();

            $stmtLembrete = $conn->prepare('UPDATE pessoa SET lembrete_enviado = 0 WHERE id_pessoa = ?');
            $stmtLembrete->bind_param('i', $idPessoa);
            $stmtLembrete->execute();
            $stmtLembrete->close();

            if ($emailPessoa !== '' && filter_var($emailPessoa, FILTER_VALIDATE_EMAIL)
                && MailerHelper::enviarConfirmacaoCadastroCurriculo($emailPessoa, $nomePessoa)) {
                $_SESSION['sucesso_curriculo'] = 'Currículo salvo. Enviamos uma confirmação para seu e-mail.';
            } else {
                $_SESSION['erro_curriculo'] = 'Currículo salvo, mas não foi possível enviar o e-mail de confirmação.';
            }
            header('Location: cadastrar_curriculo.php');
        } catch (Throwable $e) {
            error_log($e->getMessage());
            $_SESSION['erro_curriculo'] = $e instanceof InvalidArgumentException
                ? $e->getMessage()
                : 'Não foi possível salvar o currículo agora. Tente novamente.';
            header('Location: cadastrar_curriculo.php');
        } finally {
            if ($conn instanceof mysqli) {
                $conn->close();
            }
        }
        exit;

    default:
        header('Location: ../index.php');
        exit;
}
<<<<<<< HEAD

header('Location: index.php');
exit;
=======
>>>>>>> bb0413abcd2a8c17f9c53b600f5a5acb10a41c97
