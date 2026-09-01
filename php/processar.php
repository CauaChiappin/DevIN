<?php

require_once __DIR__ . '/config/security.php';
startSecureSession();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/MailerHelper.php';

$acao = $_POST['acao'] ?? '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

requireValidCsrf();

/*
|--------------------------------------------------------------------------
| SOLICITAR RECUPERAÇÃO DE SENHA
|--------------------------------------------------------------------------
*/

if ($acao === 'solicitar_recuperacao') {

    $email = trim(
        $_POST['email'] ?? ''
    );

    if (
        empty($email) ||
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $_SESSION['erro_recuperacao'] =
            'Por favor, informe um e-mail válido.';

        header(
            'Location: recuperacao.php'
        );

        exit;
    }

    try {

        $conn = getDatabaseConnection();

        /*
        |--------------------------------------------------------------------------
        | PROCURA NA TABELA PESSOA
        |--------------------------------------------------------------------------
        */

        $stmtPessoa = $conn->prepare("
            SELECT
                id_pessoa AS id,
                nome,
                email,
                'pessoa' AS tipo
            FROM pessoa
            WHERE email = ?
            LIMIT 1
        ");

        $stmtPessoa->bind_param(
            's',
            $email
        );

        $stmtPessoa->execute();

        $usuario =
            $stmtPessoa
                ->get_result()
                ->fetch_assoc();

        $stmtPessoa->close();

        /*
        |--------------------------------------------------------------------------
        | SE NÃO ENCONTROU, PROCURA NA EMPRESA
        |--------------------------------------------------------------------------
        */

        if (!$usuario) {

            $stmtEmpresa = $conn->prepare("
                SELECT
                    id_empresa AS id,
                    nome,
                    email,
                    'empresa' AS tipo
                FROM empresa
                WHERE email = ?
                LIMIT 1
            ");

            $stmtEmpresa->bind_param(
                's',
                $email
            );

            $stmtEmpresa->execute();

            $usuario =
                $stmtEmpresa
                    ->get_result()
                    ->fetch_assoc();

            $stmtEmpresa->close();
        }

        /*
        |--------------------------------------------------------------------------
        | USUÁRIO ENCONTRADO
        |--------------------------------------------------------------------------
        */

        if ($usuario) {

            $token = bin2hex(
                random_bytes(32)
            );

            $tabela =
                $usuario['tipo'] === 'pessoa'
                    ? 'pessoa'
                    : 'empresa';

            $colunaId =
                $usuario['tipo'] === 'pessoa'
                    ? 'id_pessoa'
                    : 'id_empresa';

            $sql = "
                UPDATE {$tabela}
                SET
                    token_recuperacao = ?,
                    token_expiracao =
                        DATE_ADD(NOW(), INTERVAL 1 HOUR)
                WHERE {$colunaId} = ?
            ";

            $stmtToken =
                $conn->prepare($sql);

            $stmtToken->bind_param(
                'si',
                $token,
                $usuario['id']
            );

            $stmtToken->execute();
            $stmtToken->close();

            /*
            |--------------------------------------------------------------------------
            | LINK DE RECUPERAÇÃO
            |--------------------------------------------------------------------------
            |
            | Se o projeto estiver em:
            | http://localhost/DevIN/
            |
            | este endereço funciona no XAMPP.
            |
            */

            $linkRedefinicao = rtrim(APP_BASE_URL, '/') . '/php/redefinir.php?token=' . urlencode($token);

            $nomeSeguro =
                htmlspecialchars(
                    $usuario['nome'],
                    ENT_QUOTES,
                    'UTF-8'
                );

            $assunto =
                'Redefinição de Senha - DevIN';

            $corpoHtml = "
                <div style='
                    font-family: Arial, sans-serif;
                    padding: 20px;
                    background-color: #f4f6f9;
                '>

                    <div style='
                        max-width: 500px;
                        margin: 0 auto;
                        background: #ffffff;
                        padding: 25px;
                        border-radius: 8px;
                    '>

                        <h2 style='color: #2b56f5;'>
                            Olá, {$nomeSeguro}!
                        </h2>

                        <p>
                            Recebemos uma solicitação para
                            redefinir a senha da sua conta
                            no <strong>DevIN</strong>.
                        </p>

                        <p>
                            Clique no botão abaixo para criar
                            uma nova senha.
                        </p>

                        <p>
                            Este link ficará disponível
                            por <strong>1 hora</strong>.
                        </p>

                        <div style='
                            text-align: center;
                            margin: 25px 0;
                        '>

                            <a
                                href='{$linkRedefinicao}'
                                style='
                                    background-color: #2b56f5;
                                    color: #ffffff;
                                    padding: 12px 24px;
                                    text-decoration: none;
                                    border-radius: 5px;
                                    font-weight: bold;
                                    display: inline-block;
                                '
                            >
                                Redefinir Minha Senha
                            </a>

                        </div>

                        <p style='
                            color: #777;
                            font-size: 12px;
                        '>
                            Se você não solicitou esta alteração,
                            desconsidere este e-mail.
                        </p>

                    </div>

                </div>
            ";

            if (!MailerHelper::enviar(
                $email,
                $usuario['nome'],
                $assunto,
                $corpoHtml
            )) {
                error_log('Falha ao enviar e-mail de recuperação para ' . $email);
            }
        }

        $conn->close();

        /*
        |--------------------------------------------------------------------------
        | MENSAGEM GENÉRICA
        |--------------------------------------------------------------------------
        |
        | Não informamos se o e-mail existe ou não.
        | Isso evita exposição de contas cadastradas.
        |
        */

        $_SESSION['sucesso_recuperacao'] =
            'Se o e-mail informado estiver cadastrado, você receberá o link de redefinição em instantes.';

        header(
            'Location: recuperacao.php'
        );

        exit;

    } catch (Throwable $e) {

        error_log(
            'Erro na recuperação de senha: ' .
            $e->getMessage()
        );

        $_SESSION['erro_recuperacao'] =
            'Não foi possível processar a solicitação. Tente novamente.';

        header(
            'Location: recuperacao.php'
        );

        exit;
    }
}


/*
|--------------------------------------------------------------------------
| REDEFINIR SENHA
|--------------------------------------------------------------------------
*/

if ($acao === 'redefinir_senha') {

    $token =
        trim($_POST['token'] ?? '');

    $novaSenha =
        $_POST['nova_senha'] ?? '';

    $confSenha =
        $_POST['confirmar_senha'] ?? '';

    /*
    |--------------------------------------------------------------------------
    | VALIDAÇÕES
    |--------------------------------------------------------------------------
    */

    if (
        empty($token) ||
        empty($novaSenha) ||
        empty($confSenha)
    ) {

        $_SESSION['erro_redefinir'] =
            'Preencha todos os campos.';

        header(
            'Location: redefinir.php?token=' .
            urlencode($token)
        );

        exit;
    }

    if ($novaSenha !== $confSenha) {

        $_SESSION['erro_redefinir'] =
            'As senhas não coincidem.';

        header(
            'Location: redefinir.php?token=' .
            urlencode($token)
        );

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | MESMAS REGRAS DO CADASTRO
    |--------------------------------------------------------------------------
    */

    if (strlen($novaSenha) < 8) {

        $_SESSION['erro_redefinir'] =
            'A senha deve ter no mínimo 8 caracteres.';

        header(
            'Location: redefinir.php?token=' .
            urlencode($token)
        );

        exit;
    }

    if (!preg_match('/[A-Z]/', $novaSenha)) {

        $_SESSION['erro_redefinir'] =
            'A senha deve possuir pelo menos uma letra maiúscula.';

        header(
            'Location: redefinir.php?token=' .
            urlencode($token)
        );

        exit;
    }

    if (!preg_match('/[^a-zA-Z0-9]/', $novaSenha)) {

        $_SESSION['erro_redefinir'] =
            'A senha deve possuir pelo menos um caractere especial.';

        header(
            'Location: redefinir.php?token=' .
            urlencode($token)
        );

        exit;
    }

    try {

        $conn =
            getDatabaseConnection();

        $senhaHash =
            password_hash(
                $novaSenha,
                PASSWORD_DEFAULT
            );

        /*
        |--------------------------------------------------------------------------
        | TENTA ALTERAR NA PESSOA
        |--------------------------------------------------------------------------
        */

        $stmtPessoa =
            $conn->prepare("
                UPDATE pessoa
                SET
                    senha_hash = ?,
                    token_recuperacao = NULL,
                    token_expiracao = NULL
                WHERE
                    token_recuperacao = ?
                    AND token_expiracao > NOW()
            ");

        $stmtPessoa->bind_param(
            'ss',
            $senhaHash,
            $token
        );

        $stmtPessoa->execute();

        $afetados =
            $stmtPessoa->affected_rows;

        $stmtPessoa->close();

        /*
        |--------------------------------------------------------------------------
        | SE NÃO ALTEROU PESSOA, TENTA EMPRESA
        |--------------------------------------------------------------------------
        */

        if ($afetados === 0) {

            $stmtEmpresa =
                $conn->prepare("
                    UPDATE empresa
                    SET
                        senha_hash = ?,
                        token_recuperacao = NULL,
                        token_expiracao = NULL
                    WHERE
                        token_recuperacao = ?
                        AND token_expiracao > NOW()
                ");

            $stmtEmpresa->bind_param(
                'ss',
                $senhaHash,
                $token
            );

            $stmtEmpresa->execute();

            $afetados =
                $stmtEmpresa->affected_rows;

            $stmtEmpresa->close();
        }

        $conn->close();

        /*
        |--------------------------------------------------------------------------
        | RESULTADO
        |--------------------------------------------------------------------------
        */

        if ($afetados > 0) {

            $_SESSION['sucesso_login'] =
                'Senha redefinida com sucesso! Faça seu login.';

            header(
                'Location: login.php'
            );

            exit;
        }

        $_SESSION['erro_redefinir'] =
            'O link de redefinição é inválido ou já expirou. Solicite um novo.';

        header(
            'Location: recuperacao.php'
        );

        exit;

    } catch (Throwable $e) {

        error_log(
            'Erro ao redefinir senha: ' .
            $e->getMessage()
        );

        $_SESSION['erro_redefinir'] =
            'Não foi possível redefinir a senha. Tente novamente.';

        header(
            'Location: redefinir.php?token=' .
            urlencode($token)
        );

        exit;
    }
}


/*
|--------------------------------------------------------------------------
| AÇÃO DESCONHECIDA
|--------------------------------------------------------------------------
*/

header('Location: index.php');
exit;