<?php

session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/MailerHelper.php';

/*
|--------------------------------------------------------------------------
| VERIFICA LOGIN
|--------------------------------------------------------------------------
*/

if (
    empty($_SESSION['id_pessoa']) &&
    empty($_SESSION['usuario_id'])
) {

    header(
        'Location: login.php'
    );

    exit;
}

/*
|--------------------------------------------------------------------------
| IDENTIFICA A PESSOA
|--------------------------------------------------------------------------
*/

$idPessoa =
    (int) (
        $_SESSION['id_pessoa']
        ?? $_SESSION['usuario_id']
    );

$nomePessoa =
    $_SESSION['nome_pessoa']
    ?? $_SESSION['usuario_nome']
    ?? 'Candidato';

$emailPessoa =
    $_SESSION['email_pessoa']
    ?? $_SESSION['usuario_email']
    ?? '';

/*
|--------------------------------------------------------------------------
| MENSAGENS
|--------------------------------------------------------------------------
*/

$mensagemSucesso = '';
$mensagemErro = '';

/*
|--------------------------------------------------------------------------
| CONEXÃO
|--------------------------------------------------------------------------
*/

try {

    $conn =
        getDatabaseConnection();

} catch (Throwable $e) {

    error_log(
        'Erro de conexão: ' .
        $e->getMessage()
    );

    $conn = null;

    $mensagemErro =
        'Não foi possível conectar ao banco de dados.';
}

/*
|--------------------------------------------------------------------------
| SALVAR CURRÍCULO
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    $conn instanceof mysqli
) {

    $nomeSocial =
        trim(
            $_POST['nome_social'] ?? ''
        );

    $grauEscolaridade =
        trim(
            $_POST['grau_de_escolaridade'] ?? ''
        );

    $cursos =
        trim(
            $_POST['cursos'] ?? ''
        );

    $experiencia =
        trim(
            $_POST['experiencia'] ?? ''
        );

    $idiomas =
        trim(
            $_POST['idiomas'] ?? ''
        );

    try {

        /*
        |--------------------------------------------------------------------------
        | VALIDAÇÃO
        |--------------------------------------------------------------------------
        */

        if ($nomeSocial === '') {
            throw new InvalidArgumentException(
                'Informe seu nome social.'
            );
        }

        if ($grauEscolaridade === '') {
            throw new InvalidArgumentException(
                'Selecione seu grau de escolaridade.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | VERIFICA SE JÁ EXISTE CURRÍCULO
        |--------------------------------------------------------------------------
        */

        $stmtCheck =
            $conn->prepare("
                SELECT id_curriculo
                FROM curriculo
                WHERE id_pessoa = ?
                LIMIT 1
            ");

        $stmtCheck->bind_param(
            'i',
            $idPessoa
        );

        $stmtCheck->execute();

        $resCheck =
            $stmtCheck->get_result();

        $jaExiste =
            $resCheck &&
            $resCheck->num_rows > 0;

        $stmtCheck->close();

        /*
        |--------------------------------------------------------------------------
        | ATUALIZA OU INSERE
        |--------------------------------------------------------------------------
        */

        if ($jaExiste) {

            $stmt =
                $conn->prepare("
                    UPDATE curriculo
                    SET
                        nome_social = ?,
                        grau_de_escolaridade = ?,
                        cursos = ?,
                        experiencia = ?,
                        idiomas = ?
                    WHERE id_pessoa = ?
                ");

            $stmt->bind_param(
                'sssssi',
                $nomeSocial,
                $grauEscolaridade,
                $cursos,
                $experiencia,
                $idiomas,
                $idPessoa
            );

        } else {

            $stmt =
                $conn->prepare("
                    INSERT INTO curriculo
                    (
                        id_pessoa,
                        nome_social,
                        grau_de_escolaridade,
                        cursos,
                        experiencia,
                        idiomas
                    )
                    VALUES
                    (?, ?, ?, ?, ?, ?)
                ");

            $stmt->bind_param(
                'isssss',
                $idPessoa,
                $nomeSocial,
                $grauEscolaridade,
                $cursos,
                $experiencia,
                $idiomas
            );
        }

        $executou =
            $stmt->execute();

        $stmt->close();

        /*
        |--------------------------------------------------------------------------
        | RESULTADO
        |--------------------------------------------------------------------------
        */

        if ($executou) {

            /*
            |--------------------------------------------------------------------------
            | E-MAIL DO CANDIDATO
            |--------------------------------------------------------------------------
            */

            if (
                !empty($emailPessoa) &&
                filter_var(
                    $emailPessoa,
                    FILTER_VALIDATE_EMAIL
                )
            ) {

                MailerHelper::
                    enviarConfirmacaoCadastroCurriculo(
                        $emailPessoa,
                        $nomePessoa
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | AVISA EMPRESAS
            |--------------------------------------------------------------------------
            */

            MailerHelper::
                notificarEmpresasNovoCandidato(
                    $conn,
                    $nomePessoa
                );

            $_SESSION['curriculo_success'] =
                'Currículo salvo com sucesso!';

            header(
                'Location: pessoa.php?pagina=inicio'
            );

            exit;

        } else {

            $mensagemErro =
                'Erro ao salvar o currículo.';
        }

    } catch (Throwable $e) {

        error_log(
            'Erro ao salvar currículo: ' .
            $e->getMessage()
        );

        $mensagemErro =
            'Não foi possível salvar o currículo. Tente novamente.';
    }
}

/*
|--------------------------------------------------------------------------
| BUSCA CURRÍCULO
|--------------------------------------------------------------------------
*/

$dadosCurriculo = [];

if ($conn instanceof mysqli) {

    try {

        $stmtFetch =
            $conn->prepare("
                SELECT
                    nome_social,
                    grau_de_escolaridade,
                    cursos,
                    experiencia,
                    idiomas
                FROM curriculo
                WHERE id_pessoa = ?
                LIMIT 1
            ");

        $stmtFetch->bind_param(
            'i',
            $idPessoa
        );

        $stmtFetch->execute();

        $dadosCurriculo =
            $stmtFetch
                ->get_result()
                ->fetch_assoc()
                ?? [];

        $stmtFetch->close();

        $conn->close();

    } catch (Throwable $e) {

        error_log(
            'Erro ao buscar currículo: ' .
            $e->getMessage()
        );
    }
}

$cNomeSocial =
    $dadosCurriculo['nome_social']
    ?? '';

$cGrauEscolaridade =
    $dadosCurriculo['grau_de_escolaridade']
    ?? '';

$cCursos =
    $dadosCurriculo['cursos']
    ?? '';

$cExperiencia =
    $dadosCurriculo['experiencia']
    ?? '';

$cIdiomas =
    $dadosCurriculo['idiomas']
    ?? '';

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        DevIN | Preencher Currículo
    </title>

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
        href="../css/cadastrostyle.css"
    >

    <link
        rel="stylesheet"
        href="../css/curriculo.css"
    >

</head>

<body class="curriculo-page">

<div class="main-container">

    <div class="left-side">

        <div class="brand-logo">

            <a href="index.php">
                Dev<span>IN</span>
            </a>

        </div>

        <?php if ($mensagemSucesso): ?>

            <div class="php-toast success-toast">

                <?= htmlspecialchars(
                    $mensagemSucesso,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

            </div>

        <?php endif; ?>

        <?php if ($mensagemErro): ?>

            <div class="php-toast error-toast">

                <?= htmlspecialchars(
                    $mensagemErro,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

            </div>

        <?php endif; ?>

        <form
            method="POST"
            action="cadastrar_curriculo.php"
            class="register-form"
        >

            <h2 class="title-curriculo">
                Preenchimento de Currículo
            </h2>

            <div class="form-columns">

                <div class="form-column">

                    <div class="input-group">

                        <label for="nome_social">
                            Nome Social / Como prefere ser chamado(a):
                        </label>

                        <input
                            type="text"
                            id="nome_social"
                            name="nome_social"
                            placeholder="Ex: Alex Silva"
                            value="<?= htmlspecialchars(
                                $cNomeSocial,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                            required
                        >

                    </div>

                    <div class="input-group">

                        <label for="grau_de_escolaridade">
                            Grau de Escolaridade:
                        </label>

                        <select
                            id="grau_de_escolaridade"
                            name="grau_de_escolaridade"
                            required
                        >

                            <option value="">
                                Selecione...
                            </option>

                            <option
                                value="Ensino Médio Incompleto"
                                <?= $cGrauEscolaridade === 'Ensino Médio Incompleto'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Ensino Médio Incompleto
                            </option>

                            <option
                                value="Ensino Médio Completo"
                                <?= $cGrauEscolaridade === 'Ensino Médio Completo'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Ensino Médio Completo
                            </option>

                            <option
                                value="Ensino Superior Incompleto"
                                <?= $cGrauEscolaridade === 'Ensino Superior Incompleto'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Ensino Superior Incompleto
                            </option>

                            <option
                                value="Ensino Superior Completo"
                                <?= $cGrauEscolaridade === 'Ensino Superior Completo'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Ensino Superior Completo
                            </option>

                            <option
                                value="Pós-graduação / Especialização"
                                <?= $cGrauEscolaridade === 'Pós-graduação / Especialização'
                                    ? 'selected'
                                    : '' ?>
                            >
                                Pós-graduação / Especialização
                            </option>

                        </select>

                    </div>

                    <div class="input-group">

                        <label for="idiomas">
                            Idiomas:
                        </label>

                        <input
                            type="text"
                            id="idiomas"
                            name="idiomas"
                            placeholder="Ex: Português Nativo, Inglês Intermediário"
                            value="<?= htmlspecialchars(
                                $cIdiomas,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                        >

                    </div>

                </div>

                <div class="form-column">

                    <div class="input-group">

                        <label for="cursos">
                            Cursos e Certificações:
                        </label>

                        <textarea
                            id="cursos"
                            name="cursos"
                            placeholder="Ex: Curso de PHP Avançado, HTML5/CSS3, MySQL"
                        ><?= htmlspecialchars(
                            $cCursos,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?></textarea>

                    </div>

                    <div class="input-group">

                        <label for="experiencia">
                            Experiência Profissional:
                        </label>

                        <textarea
                            id="experiencia"
                            name="experiencia"
                            placeholder="Ex: Desenvolvedor Web na Empresa X (2022 - Atual)"
                        ><?= htmlspecialchars(
                            $cExperiencia,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?></textarea>

                    </div>

                </div>

            </div>

            <div class="form-footer-action">

                <button
                    type="submit"
                    class="btn-submit"
                >
                    Finalizar e Salvar Currículo
                </button>

                <div class="login-redirect">

                    Deseja sair do sistema?

                    <a href="logout.php">
                        Clique aqui
                    </a>

                </div>

            </div>

        </form>

        <div class="page-footer">

            © <?= date('Y') ?>

            <span>
                DevIN
            </span>

            . Todos os direitos reservados.

        </div>

            <div class="form-group">
                <label for="grau_de_escolaridade">Grau de Escolaridade<span class="asterisk">*</span></label>
                <input type="text" id="grau_de_escolaridade" name="grau_de_escolaridade" required>
            </div>

            <div class="form-group">
                <label for="cursos">Cursos</label>
                <input type="text" id="cursos" name="cursos">
            </div>

            <div class="form-group">
                <label for="experiencia">Experiência<span class="asterisk">*</span></label>
                <input type="text" id="experiencia" name="experiencia" required>
            </div>

            <div class="form-group">
                <label for="idiomas">Idiomas</label>
                <input type="text" id="idiomas" name="idiomas">
            </div>

            <div class="button-container">
                <button type="submit" class="btn-enviar">Enviar</button>
            </div>

        </form>
    </div>

    <div class="right-side">

        <a
            href="logout.php"
            class="btn-top-login"
        >
            Sair
        </a>

        <div class="mascot-container">

            <img
                src="../img/robocadastro.webp"
                alt="Mascote DevIN"
                class="mascot-img"
            >

        </div>

    </div>

</div>

</body>
</html>
