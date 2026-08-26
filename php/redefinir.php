<?php

require_once __DIR__ . '/config/security.php';
startSecureSession();

require_once __DIR__ . '/config/database.php';

$token = trim(
    $_GET['token'] ?? ''
);

$tokenValido = false;

if ($token !== '') {

    try {

        $conn =
            getDatabaseConnection();

        /*
        |--------------------------------------------------------------------------
        | PROCURA TOKEN NA PESSOA
        |--------------------------------------------------------------------------
        */

        $stmtPessoa =
            $conn->prepare("
                SELECT id_pessoa
                FROM pessoa
                WHERE
                    token_recuperacao = ?
                    AND token_expiracao > NOW()
                LIMIT 1
            ");

        $stmtPessoa->bind_param(
            's',
            $token
        );

        $stmtPessoa->execute();

        $resultadoPessoa =
            $stmtPessoa->get_result();

        if (
            $resultadoPessoa &&
            $resultadoPessoa->num_rows > 0
        ) {
            $tokenValido = true;
        }

        $stmtPessoa->close();

        /*
        |--------------------------------------------------------------------------
        | SE NÃO ENCONTROU, PROCURA NA EMPRESA
        |--------------------------------------------------------------------------
        */

        if (!$tokenValido) {

            $stmtEmpresa =
                $conn->prepare("
                    SELECT id_empresa
                    FROM empresa
                    WHERE
                        token_recuperacao = ?
                        AND token_expiracao > NOW()
                    LIMIT 1
                ");

            $stmtEmpresa->bind_param(
                's',
                $token
            );

            $stmtEmpresa->execute();

            $resultadoEmpresa =
                $stmtEmpresa->get_result();

            if (
                $resultadoEmpresa &&
                $resultadoEmpresa->num_rows > 0
            ) {
                $tokenValido = true;
            }

            $stmtEmpresa->close();
        }

        $conn->close();

    } catch (Throwable $e) {

        error_log(
            'Erro ao validar token: ' .
            $e->getMessage()
        );

        $tokenValido = false;
    }
}

$mensagemErro =
    $_SESSION['erro_redefinir'] ?? '';

unset(
    $_SESSION['erro_redefinir']
);

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Nova Senha - DevIN</title>

    <link
        rel="icon"
        type="image/svg+xml"
        href="../img/favicon.svg"
    >

    <link
        rel="icon"
        type="image/png"
        href="../img/favicon.png"
    >

    <link
        rel="stylesheet"
        href="../css/recuperacao.css"
    >

</head>

<body>

<div class="card">

    <h1>
        Criar Nova Senha
    </h1>

    <?php if (!$tokenValido): ?>

        <div class="alert-error">

            Este link de redefinição é inválido
            ou já expirou.

        </div>

        <a
            href="recuperacao.php"
            class="btn-submit"
            style="
                display: block;
                text-align: center;
                text-decoration: none;
            "
        >
            Solicitar Novo Link
        </a>

    <?php else: ?>

        <p class="subtitle">

            Digite sua nova senha abaixo
            para atualizar sua conta.

        </p>

        <?php if ($mensagemErro): ?>

            <div class="alert-error">

                <?= htmlspecialchars(
                    $mensagemErro,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

            </div>

        <?php endif; ?>

        <form
            action="processar.php"
            method="POST"
        >

            <input
                type="hidden"
                name="acao"
                value="redefinir_senha"
            >

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>"
            >

            <input
                type="hidden"
                name="token"
                value="<?= htmlspecialchars(
                    $token,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>"
            >

            <div class="form-group">

                <label for="nova_senha">
                    Nova Senha:
                </label>

                <input
                    type="password"
                    id="nova_senha"
                    name="nova_senha"
                    placeholder="••••••••"
                    required
                    minlength="8"
                >

            </div>

            <div class="form-group">

                <label for="confirmar_senha">
                    Confirme a Nova Senha:
                </label>

                <input
                    type="password"
                    id="confirmar_senha"
                    name="confirmar_senha"
                    placeholder="••••••••"
                    required
                    minlength="8"
                >

            </div>

            <button
                type="submit"
                class="btn-submit"
            >
                Atualizar Senha
            </button>

        </form>

    <?php endif; ?>

    <a
        href="login.php"
        class="back-link"
    >
        ← Voltar para o Login
    </a>

</div>

</body>
</html>