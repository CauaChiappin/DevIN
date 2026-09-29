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

/**
 * Gera código de 6 caracteres alfanuméricos maiúsculos
 */
function gerarCodigo6(): string {
    $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = '';
    for ($i = 0; $i < 6; $i++) {
        $code .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $code;
}

/*
|--------------------------------------------------------------------------
| PÁGINA 5: SOLICITAR CÓDIGO POR E-MAIL
|--------------------------------------------------------------------------
*/
if ($acao === 'solicitar_recuperacao') {
    $email = trim(requestString($_POST, 'email'));

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['erro_recuperacao'] = 'Por favor, informe um e-mail válido.';
        header('Location: recuperacao.php');
        exit;
    }

    $conn = getDatabaseConnection();

    try {
        $usuario = null;
        $tabelas = [
            ['table' => 'pessoa',  'idCol' => 'id_pessoa',  'type' => 'pessoa'],
            ['table' => 'empresa', 'idCol' => 'id_empresa', 'type' => 'empresa'],
        ];

        foreach ($tabelas as $tab) {
            $stmt = $conn->prepare("SELECT {$tab['idCol']} AS id, nome, email, '{$tab['type']}' AS tipo FROM {$tab['table']} WHERE email = ? LIMIT 1");
            if ($stmt) {
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $usuario = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if ($usuario) break;
            }
        }

        // Se o e-mail não estiver cadastrado em NENHUMA tabela, devolve erro SEM ativar o mailer
        if (!$usuario) {
            $_SESSION['erro_recuperacao'] = 'E-mail não encontrado.';
            header('Location: recuperacao.php');
            exit;
        }

        // Gera novo código único
        $codigo = gerarCodigo6();
        $codigoHash = hash('sha256', $codigo);

        $mapa = [
            'pessoa'  => ['tabela' => 'pessoa',  'idCol' => 'id_pessoa'],
            'empresa' => ['tabela' => 'empresa', 'idCol' => 'id_empresa'],
        ];
        $info = $mapa[$usuario['tipo']];

        $stmtToken = $conn->prepare("UPDATE {$info['tabela']} SET token_recuperacao = ?, token_expiracao = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE {$info['idCol']} = ?");
        if ($stmtToken) {
            $stmtToken->bind_param('si', $codigoHash, $usuario['id']);
            $stmtToken->execute();
            $stmtToken->close();
        }

        MailerHelper::enviarCodigoRecuperacao6($email, $usuario['nome'], $codigo);

        $_SESSION['recuperacao_email'] = $email;
        $_SESSION['recuperacao_tipo']  = $usuario['tipo'];
        $_SESSION['recuperacao_id']    = $usuario['id'];
        $_SESSION['recuperacao_nome']  = $usuario['nome'];
        $_SESSION['ultimo_envio_codigo'] = time();

        header('Location: codigo-senha.php');
        exit;

    } catch (Throwable $e) {
        error_log('Erro na solicitação de código: ' . $e->getMessage());
        $_SESSION['erro_recuperacao'] = 'Erro ao processar a solicitação. Tente novamente.';
        header('Location: recuperacao.php');
        exit;
    } finally {
        $conn->close();
    }
}

/*
|--------------------------------------------------------------------------
| PÁGINA 6: REENVIAR CÓDIGO (COOLDOWN DE 60 SEGUNDOS)
|--------------------------------------------------------------------------
*/
if ($acao === 'reenviar_codigo') {
    $email = $_SESSION['recuperacao_email'] ?? '';
    $id    = $_SESSION['recuperacao_id'] ?? 0;
    $tipo  = $_SESSION['recuperacao_tipo'] ?? '';
    $nome  = $_SESSION['recuperacao_nome'] ?? 'Usuário';

    if (empty($email) || !$id || !$tipo) {
        header('Location: recuperacao.php');
        exit;
    }

    $agora = time();
    $ultimoEnvio = $_SESSION['ultimo_envio_codigo'] ?? 0;
    if (($agora - $ultimoEnvio) < 60) {
        $restante = 60 - ($agora - $ultimoEnvio);
        $_SESSION['erro_codigo'] = "Aguarde {$restante}s para solicitar um novo código.";
        header('Location: codigo-senha.php');
        exit;
    }

    $conn = getDatabaseConnection();
    try {
        $codigo = gerarCodigo6();
        $codigoHash = hash('sha256', $codigo);
        $tabela = ($tipo === 'empresa') ? 'empresa' : 'pessoa';
        $colId  = ($tipo === 'empresa') ? 'id_empresa' : 'id_pessoa';

        $stmt = $conn->prepare("UPDATE {$tabela} SET token_recuperacao = ?, token_expiracao = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE {$colId} = ?");
        if ($stmt) {
            $stmt->bind_param('si', $codigoHash, $id);
            $stmt->execute();
            $stmt->close();
        }

        MailerHelper::enviarCodigoRecuperacao6($email, $nome, $codigo);
        $_SESSION['ultimo_envio_codigo'] = time();
        $_SESSION['sucesso_codigo'] = 'Novo código enviado com sucesso!';

        header('Location: codigo-senha.php');
        exit;
    } finally {
        $conn->close();
    }
}

/*
|--------------------------------------------------------------------------
| PÁGINA 6: VALIDAR CÓDIGO (ENTRAR)
|--------------------------------------------------------------------------
*/
if ($acao === 'validar_codigo') {
    $codigoDigitado = strtoupper(trim(preg_replace('/[^a-zA-Z0-9]/', '', requestString($_POST, 'codigo'))));
    $email = $_SESSION['recuperacao_email'] ?? '';

    if (empty($codigoDigitado) || strlen($codigoDigitado) !== 6 || empty($email)) {
        $_SESSION['erro_codigo'] = 'Informe o código completo de 6 caracteres.';
        header('Location: codigo-senha.php');
        exit;
    }

    $conn = getDatabaseConnection();
    try {
        $codigoHash = hash('sha256', $codigoDigitado);
        $validado = false;
        $tabelas = [
            ['table' => 'pessoa',  'idCol' => 'id_pessoa',  'type' => 'pessoa'],
            ['table' => 'empresa', 'idCol' => 'id_empresa', 'type' => 'empresa'],
        ];

        foreach ($tabelas as $tab) {
            $stmt = $conn->prepare("
                SELECT {$tab['idCol']} AS id, nome, email, '{$tab['type']}' AS tipo
                FROM {$tab['table']}
                WHERE email = ? AND token_recuperacao = ? AND token_expiracao > NOW()
                LIMIT 1
            ");
            if ($stmt) {
                $stmt->bind_param('ss', $email, $codigoHash);
                $stmt->execute();
                $usr = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                if ($usr) {
                    $validado = true;
                    // Invalida o código no banco imediatamente (uso único)
                    $stmtLimpa = $conn->prepare("UPDATE {$tab['table']} SET token_recuperacao = NULL, token_expiracao = NULL WHERE {$tab['idCol']} = ?");
                    if ($stmtLimpa) {
                        $stmtLimpa->bind_param('i', $usr['id']);
                        $stmtLimpa->execute();
                        $stmtLimpa->close();
                    }

                    $_SESSION['recuperacao_verificada'] = true;
                    $_SESSION['recuperacao_id']   = $usr['id'];
                    $_SESSION['recuperacao_tipo'] = $usr['tipo'];
                    $_SESSION['recuperacao_nome'] = $usr['nome'];
                    break;
                }
            }
        }

        if ($validado) {
            header('Location: redefinir.php');
            exit;
        }

        $_SESSION['erro_codigo'] = 'Código incorreto ou expirado. Tente novamente.';
        header('Location: codigo-senha.php');
        exit;

    } finally {
        $conn->close();
    }
}

/*
|--------------------------------------------------------------------------
| PÁGINA 7: CADASTRAR NOVA SENHA -> REDIRECIONA PARA DASHBOARD
|--------------------------------------------------------------------------
*/
if ($acao === 'redefinir_senha') {
    if (empty($_SESSION['recuperacao_verificada'])) {
        header('Location: recuperacao.php');
        exit;
    }

    $novaSenha = requestString($_POST, 'nova_senha');
    $confSenha = requestString($_POST, 'confirmar_senha');
    $id   = (int) ($_SESSION['recuperacao_id'] ?? 0);
    $tipo = $_SESSION['recuperacao_tipo'] ?? '';

    if ($novaSenha !== $confSenha) {
        $_SESSION['erro_redefinir'] = 'As senhas não coincidem.';
        header('Location: redefinir.php');
        exit;
    }

    if (strlen($novaSenha) < 8 || !preg_match('/[A-Z]/', $novaSenha) || !preg_match('/[^a-zA-Z0-9]/', $novaSenha)) {
        $_SESSION['erro_redefinir'] = 'A senha não atende a todos os requisitos de segurança.';
        header('Location: redefinir.php');
        exit;
    }

    $conn = getDatabaseConnection();
    try {
        $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
        $tabela = ($tipo === 'empresa') ? 'empresa' : 'pessoa';
        $colId  = ($tipo === 'empresa') ? 'id_empresa' : 'id_pessoa';

        $stmt = $conn->prepare("UPDATE {$tabela} SET senha_hash = ? WHERE {$colId} = ?");
        if ($stmt) {
            $stmt->bind_param('si', $senhaHash, $id);
            $stmt->execute();
            $stmt->close();
        }

        // Login automático após cadastrar nova senha
        session_regenerate_id(true);
        $_SESSION['logado']        = true;
        $_SESSION['usuario_id']    = $id;
        $_SESSION['usuario_tipo']  = $tipo;
        $_SESSION['usuario_nome']  = $_SESSION['recuperacao_nome'] ?? 'Usuário';
        $_SESSION['usuario_email'] = $_SESSION['recuperacao_email'] ?? '';

        unset($_SESSION['recuperacao_verificada'], $_SESSION['recuperacao_id'], $_SESSION['recuperacao_tipo'], $_SESSION['recuperacao_email'], $_SESSION['recuperacao_nome']);

        // Redireciona para o dashboard correspondente
        $dest = ($tipo === 'empresa') ? 'empresa.php' : 'pessoa.php';
        header("Location: {$dest}");
        exit;

    } finally {
        $conn->close();
    }
}

header('Location: index.php');
exit;