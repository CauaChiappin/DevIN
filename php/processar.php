<?php

declare(strict_types=1);

require_once __DIR__ . '/config/security.php';
startSecureSession();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/MailerHelper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

requireValidCsrf();

$acao = requestString($_POST, 'acao');

/*
|--------------------------------------------------------------------------
| SOLICITAR RECUPERAÇÃO DE SENHA (GERAR CÓDIGO DE 8 DÍGITOS)
|--------------------------------------------------------------------------
*/

if ($acao === 'solicitar_recuperacao') {
    $email = trim(requestString($_POST, 'email'));

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['erro_recuperacao'] = 'Por favor, informe um e-mail válido.';
        header('Location: recuperacao.php');
        exit;
    }

    // Cooldown de 2 minutos entre solicitações
    $agora = time();
    $ultimoPedido = $_SESSION['ultimo_pedido_recuperacao'] ?? 0;
    if (($agora - $ultimoPedido) < 120) {
        $tempoRestante = 120 - ($agora - $ultimoPedido);
        $_SESSION['erro_recuperacao'] = "Aguarde {$tempoRestante} segundos antes de solicitar um novo código.";
        header('Location: recuperacao.php');
        exit;
    }
    $_SESSION['ultimo_pedido_recuperacao'] = $agora;

    $conn = getDatabaseConnection();

    try {
        $usuario = null;
        $tabelas = [
            ['table' => 'pessoa',        'idCol' => 'id_pessoa',        'type' => 'pessoa'],
            ['table' => 'empresa',       'idCol' => 'id_empresa',       'type' => 'empresa'],
            ['table' => 'administrador', 'idCol' => 'id_administrador', 'type' => 'adm'],
        ];

        foreach ($tabelas as $tab) {
            $stmt = $conn->prepare("
                SELECT {$tab['idCol']} AS id, nome, email, '{$tab['type']}' AS tipo
                FROM {$tab['table']}
                WHERE email = ?
                LIMIT 1
            ");

            if ($stmt) {
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $usuario = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if ($usuario) {
                    break;
                }
            }
        }

        if ($usuario) {
            // Gera código numérico de 8 dígitos
            $codigo = sprintf('%08d', random_int(0, 99999999));
            $codigoHash = hash('sha256', $codigo);

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
        $_SESSION['sucesso_recuperacao'] = 'Se o e-mail estiver correto, enviamos um código de 8 dígitos.';

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

    if (strlen($novaSenha) < 8) {
        $_SESSION['erro_redefinir'] = 'A senha deve ter no mínimo 8 caracteres.';
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
                $stmt->execute();
                $afetados = $stmt->affected_rows;
                $stmt->close();

                if ($afetados > 0) {
                    break;
                }
            }
        }

        if ($afetados > 0) {
            unset($_SESSION['email_recuperacao']);
            session_regenerate_id(true);

            $_SESSION['sucesso_login'] = 'Senha redefinida com sucesso! Faça seu login.';
            header('Location: login.php');
            exit;
        }

        $_SESSION['erro_redefinir'] = 'Código de verificação incorreto ou expirado. Tente novamente.';
        header('Location: redefinir.php');
        exit;

    } catch (Throwable $e) {
        error_log('Erro ao redefinir senha: ' . $e->getMessage());
        $_SESSION['erro_redefinir'] = 'Não foi possível redefinir a senha. Tente novamente.';
        header('Location: redefinir.php');
        exit;
    } finally {
        $conn->close();
    }
}

header('Location: index.php');
exit;   