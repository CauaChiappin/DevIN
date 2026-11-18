<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/cadastro_pessoa.php
 * Finalidade: cadastrar dados de candidatos.
 * Domínio: processamento no servidor.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


ob_start(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

require_once __DIR__ . '/controllers/AuthController.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/config/database.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/config/security.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/config/RateLimiter.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/MailerHelper.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/controllers/Privacy/AuditService.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

startSecureSession(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

if (isset($_GET['idioma']) && in_array($_GET['idioma'], ['pt-BR', 'en', 'es'], true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    $_SESSION['idioma_cadastro'] = $_GET['idioma']; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

$erro = ''; // Explicação DevIN: Atribui um valor à variável PHP $erro.

/*
|-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
| PROCESSAMENTO DO CADASTRO // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
|-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    /* O POST é tratado só depois de confirmar o token vinculado à sessão. Em seguida,
     * os valores numéricos são normalizados sem pontuação para validar e salvar um formato único. */
    requireValidCsrf(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

    $nome          = trim(requestString($_POST, 'nome')); // Explicação DevIN: Atribui um valor à variável PHP $nome.
    $email         = trim(requestString($_POST, 'email')); // Explicação DevIN: Atribui um valor à variável PHP $email.
    $cpf           = preg_replace('/[^0-9]/', '', requestString($_POST, 'cpf')); // Explicação DevIN: Atribui um valor à variável PHP $cpf.
    $telefone      = preg_replace('/[^0-9]/', '', requestString($_POST, 'telefone')); // Explicação DevIN: Atribui um valor à variável PHP $telefone.
    $cep           = preg_replace('/[^0-9]/', '', requestString($_POST, 'cep')); // Explicação DevIN: Atribui um valor à variável PHP $cep.
    $senha         = requestString($_POST, 'senha'); // Explicação DevIN: Atribui um valor à variável PHP $senha.
    $confirmeSenha = requestString($_POST, 'confirme_senha'); // Explicação DevIN: Atribui um valor à variável PHP $confirmeSenha.

    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        // Limita criações repetidas de conta feitas a partir do mesmo IP.
        if (RateLimiter::consume('registration:ip:' . RateLimiter::clientIp(), 8, 3600) > 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new RuntimeException('Muitas tentativas de cadastro. Aguarde e tente novamente.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        /*
        |-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        | VALIDAÇÕES DOS CAMPOS // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        |-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        */

        // Essas verificações no servidor continuam obrigatórias mesmo que o navegador valide os campos do formulário.

        if ($nome === '') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new Exception('Informe seu nome.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new Exception('Informe um e-mail válido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if (strlen($cpf) !== 11) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new Exception('Informe um CPF válido com 11 dígitos.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if (strlen($telefone) < 10 || strlen($telefone) > 11) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new Exception('Informe um telefone válido (10 ou 11 dígitos com DDD).'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if (strlen($cep) !== 8) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new Exception('Informe um CEP válido com 8 dígitos.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if ($senha === '') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new Exception('Informe uma senha.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if ($senha !== $confirmeSenha) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new Exception('As senhas não coincidem.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        /*
        |-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        | REGRAS DE SEGURANÇA DA SENHA // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        |-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        */

        if (strlen($senha) < 8) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new Exception('A senha deve ter no mínimo 8 caracteres.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if (!preg_match('/[A-Z]/', $senha)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new Exception('A senha deve possuir pelo menos uma letra maiúscula (A-Z).'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if (!preg_match('/[^a-zA-Z0-9]/', $senha)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new Exception('A senha deve possuir pelo menos um caractere especial (ex: ! @ # $).'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        /*
        |-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        | VERIFICAÇÕES NO BANCO DE DADOS // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        |-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        */

        $conn = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $conn.

        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            // 1. E-mail único entre todos os tipos de conta
            // Procura e-mail duplicado em todos os tipos de conta usando consultas preparadas.
            foreach (['pessoa', 'empresa', 'administrador'] as $tabelaEmail) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                $stmtEmail = $conn->prepare("SELECT email FROM {$tabelaEmail} WHERE email = ? LIMIT 1"); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.

                if (!$stmtEmail) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    throw new RuntimeException('Não foi possível validar o e-mail.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

                $stmtEmail->bind_param('s', $email); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmtEmail->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $emailExiste = $stmtEmail->get_result(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
                $existe = $emailExiste && $emailExiste->num_rows > 0; // Explicação DevIN: Atribui um valor à variável PHP $existe.
                $stmtEmail->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

                if ($existe) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    throw new Exception('Este e-mail já está cadastrado. Use outro e-mail ou faça login.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            // CPF também precisa ser único; o parâmetro evita concatenar o valor recebido ao SQL.
            $stmtCpf = $conn->prepare('SELECT id_pessoa FROM pessoa WHERE cpf = ? LIMIT 1'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            if ($stmtCpf) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                $stmtCpf->bind_param('s', $cpf); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmtCpf->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $cpfExiste = $stmtCpf->get_result(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
                $existeCpf = $cpfExiste && $cpfExiste->num_rows > 0; // Explicação DevIN: Atribui um valor à variável PHP $existeCpf.
                $stmtCpf->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

                if ($existeCpf) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    throw new Exception('Este CPF já está cadastrado em nosso sistema.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            /*
            |-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            | HASH DA SENHA E INSERÇÃO // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            |-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            */

            // Salva um hash irreversível da senha, nunca a senha digitada em texto puro.
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT); // Explicação DevIN: Executa operação criptográfica de senha ou comparação protegida.
            if ($senhaHash === false) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new RuntimeException('Não foi possível proteger a senha.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $stmtInsert = $conn->prepare("
                INSERT INTO pessoa
                (nome, email, cpf, telefone, cep, senha_hash, foto, created_at, lembrete_enviado)
                VALUES
                (?, ?, ?, ?, ?, ?, ?, NOW(), 0)
            "); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.

            if (!$stmtInsert) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new RuntimeException('Erro ao preparar o cadastro.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $foto = ''; // Foto inicial vazia
            $stmtInsert->bind_param('sssssss', $nome, $email, $cpf, $telefone, $cep, $senhaHash, $foto); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmtInsert->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $idPessoa = (int) $stmtInsert->insert_id; // Explicação DevIN: Atribui um valor à variável PHP $idPessoa.
            $stmtInsert->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

        } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
            $conn->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        /*
        |-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        | LOGIN AUTOMÁTICO E SESSÃO DO CURRÍCULO // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        |-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        */

        // Cria a sessão imediatamente e leva a pessoa à próxima etapa do onboarding, sem pedir novo login.
        $auth = AuthController::login($email, $senha); // Explicação DevIN: Atribui um valor à variável PHP $auth.
        AuthController::establishSession($auth); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        AuditService::tryRecord('registration_success', 'success', 'pessoa', $idPessoa); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

        $idiomaEmailCadastro = $_SESSION['idioma_cadastro'] ?? ($auth['usuario']['idioma'] ?? 'pt-BR'); // Explicação DevIN: Atribui um valor à variável PHP $idiomaEmailCadastro.
        if (MailerHelper::enviarConfirmacaoCadastro($email, $nome, $idiomaEmailCadastro)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $_SESSION['sucesso_cadastro'] = 'Cadastro confirmado. Enviamos uma confirmação para seu e-mail.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
            $_SESSION['erro_email_cadastro'] = 'Seu cadastro foi criado, mas não foi possível enviar o e-mail de confirmação.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        unset($_SESSION['idioma_cadastro']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

        $_SESSION['id_pessoa']    = $idPessoa; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        $_SESSION['pessoa_nome']  = $nome; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        $_SESSION['nome_pessoa']  = $nome; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        $_SESSION['email_pessoa'] = $email; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.

        header('Location: curriculo_onboarding.php'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

    } catch (mysqli_sql_exception $e) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        error_log('Erro MySQL cadastro pessoa: ' . $e->getMessage()); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

        if ($e->getCode() === 1062) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $erro = 'Este CPF ou e-mail já está cadastrado em nosso sistema.'; // Explicação DevIN: Atribui um valor à variável PHP $erro.
        } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
            $erro = 'Não foi possível realizar o cadastro. Tente novamente.'; // Explicação DevIN: Atribui um valor à variável PHP $erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } catch (Throwable $e) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        $erro = $e->getMessage(); // Explicação DevIN: Atribui um valor à variável PHP $erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

?>
<!DOCTYPE html> <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
<html lang="pt-BR"> <!-- Explicação DevIN: Declara elemento raiz do documento HTML. -->

<head> <!-- Explicação DevIN: Declara metadados e dependências do documento. -->
    <meta charset="UTF-8"> <!-- Explicação DevIN: Declara metadado de navegação. -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Explicação DevIN: Declara metadado de navegação. -->
    <title>DevIN | Criar Conta Pessoal</title> <!-- Explicação DevIN: Declara elemento de marcação. -->
    <script src="../js/index-language.js"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. -->
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. -->
    <link rel="stylesheet" href="../css/cadastrostyle.css?v=<?= filemtime(__DIR__ . '/../css/cadastrostyle.css') ?>"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. Contém dado dinâmico processado pelo PHP. -->
    <link rel="stylesheet" href="../css/site-navigation.css"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. -->
    <link rel="stylesheet" href="../css/ui-consistency.css?v=<?= filemtime(__DIR__ . '/../css/ui-consistency.css') ?>"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. Contém dado dinâmico processado pelo PHP. -->
</head> <!-- Explicação DevIN: Fecha elemento head do documento. -->

<body class="cadastro-page"> <!-- Explicação DevIN: Declara corpo visível da página. -->

<div class="main-container"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->

    <section class="left-side"> <!-- Explicação DevIN: Declara seção temática. -->

        <header class="cadastro-header"> <!-- Explicação DevIN: Declara cabeçalho da seção ou página. -->
            <div class="brand-logo"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                <a href="index.php"> <!-- Explicação DevIN: Declara link de navegação. -->
                    Dev<span>IN</span> <!-- Explicação DevIN: Declara trecho em linha. -->
                </a> <!-- Explicação DevIN: Fecha elemento a do documento. -->
            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

            <button class="site-menu-toggle" type="button" aria-label="Abrir menu" aria-controls="site-menu" aria-expanded="false" data-site-menu-toggle> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                <span aria-hidden="true"></span><span aria-hidden="true"></span><span aria-hidden="true"></span> <!-- Explicação DevIN: Declara trecho em linha. Inclui atributos de acessibilidade. A linha reúne múltiplos elementos. -->
            </button> <!-- Explicação DevIN: Fecha elemento button do documento. -->
            <div class="site-menu" id="site-menu" data-site-menu> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                <label class="language-picker" for="site-language"> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                        <span>Idioma</span> <!-- Explicação DevIN: Declara trecho em linha. -->
                        <select id="site-language" aria-label="Idioma"> <!-- Explicação DevIN: Declara seleção de opções. Inclui atributos de acessibilidade. -->
                            <option value="pt-BR">Português</option> <!-- Explicação DevIN: Declara opção selecionável. -->
                            <option value="en">English</option> <!-- Explicação DevIN: Declara opção selecionável. -->
                            <option value="es">Español</option> <!-- Explicação DevIN: Declara opção selecionável. -->
                        </select> <!-- Explicação DevIN: Fecha elemento select do documento. -->
                    </label> <!-- Explicação DevIN: Fecha elemento label do documento. -->
                <a href="login.php" class="header-action">Login</a> <!-- Explicação DevIN: Declara link de navegação. -->
            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
        </header> <!-- Explicação DevIN: Fecha elemento header do documento. -->

        <div class="toggle-container"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
            <a href="cadastro_pessoa.php" class="toggle-btn pessoal active"> <!-- Explicação DevIN: Declara link de navegação. -->
                Pessoal <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
            </a> <!-- Explicação DevIN: Fecha elemento a do documento. -->
            <span class="toggle-divider">OU</span> <!-- Explicação DevIN: Declara trecho em linha. -->
            <a href="cadastro_empresa.php" class="toggle-btn empresa"> <!-- Explicação DevIN: Declara link de navegação. -->
                Empresa <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
            </a> <!-- Explicação DevIN: Fecha elemento a do documento. -->
        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

        <h1 class="page-title">Criar conta</h1> <!-- Explicação DevIN: Declara título principal. -->

        <?php if (!empty($erro)): ?>
            <div class="php-toast error-toast" role="alert"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
        <?php endif; ?>

        <form action="cadastro_pessoa.php" method="POST" class="register-form" id="formCadastro"> <!-- Explicação DevIN: Declara formulário de entrada de dados. -->
            <!-- Token oculto necessário para que o backend aceite o POST do cadastro. -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->

            <div class="form-columns"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->

                <div class="form-column"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->

                    <div class="input-group mobile-order-1"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                        <label for="nome">Nome:*</label> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                        <input type="text" id="nome" name="nome" required value="<?= htmlspecialchars(requestString($_POST, 'nome'), ENT_QUOTES, 'UTF-8') ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

                    <div class="input-group mobile-order-3"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                        <label for="cpf">CPF:*</label> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                        <input type="text" id="cpf" name="cpf" placeholder="000.000.000-00" maxlength="14" required value="<?= htmlspecialchars(requestString($_POST, 'cpf'), ENT_QUOTES, 'UTF-8') ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

                    <div class="input-group mobile-order-5"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                        <label for="cep">CEP:*</label> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                        <input type="text" id="cep" name="cep" placeholder="00000-000" maxlength="9" required value="<?= htmlspecialchars(requestString($_POST, 'cep'), ENT_QUOTES, 'UTF-8') ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

                    <div class="input-group password-wrapper mobile-order-8"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                        <label for="confirme_senha">Confirme a sua senha:*</label> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                        <div class="input-icon-container"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                            <input type="password" id="confirme_senha" name="confirme_senha" required minlength="8" autocomplete="new-password"> <!-- Explicação DevIN: Declara campo de entrada. -->
                            <img src="../img/olho_fechado.png" class="toggle-password-eye" onclick="togglePasswordVisibility('confirme_senha', this)" alt="Mostrar ou ocultar senha"> <!-- Explicação DevIN: Declara imagem na interface. Vincula um evento da interface. -->
                        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                        <span id="error-match" class="error-message-text">Senhas não coincidem</span> <!-- Explicação DevIN: Declara trecho em linha. -->
                    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

                </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

                <div class="form-column"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->

                    <div class="input-group mobile-order-2"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                        <label for="email">E-mail:*</label> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                        <input type="email" id="email" name="email" required autocomplete="email" value="<?= htmlspecialchars(requestString($_POST, 'email'), ENT_QUOTES, 'UTF-8') ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

                    <div class="input-group mobile-order-4"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                        <label for="telefone">Telefone:*</label> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                        <input type="tel" id="telefone" name="telefone" placeholder="(00) 00000-0000" required value="<?= htmlspecialchars(requestString($_POST, 'telefone'), ENT_QUOTES, 'UTF-8') ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

                    <div class="input-group password-wrapper mobile-order-6"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                        <label for="senha">Senha:*</label> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                        <div class="input-icon-container"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                            <input type="password" id="senha" name="senha" required minlength="8" autocomplete="new-password"> <!-- Explicação DevIN: Declara campo de entrada. -->
                            <img src="../img/olho_fechado.png" class="toggle-password-eye" onclick="togglePasswordVisibility('senha', this)" alt="Mostrar ou ocultar senha"> <!-- Explicação DevIN: Declara imagem na interface. Vincula um evento da interface. -->
                        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

                    <div class="password-requirements mobile-order-7"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                        <div class="requirement-item req-invalid" id="req-length"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                            <span class="req-icon">⚠️</span> No mínimo 8 caracteres <!-- Explicação DevIN: Declara trecho em linha. -->
                        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                        <div class="requirement-item req-invalid" id="req-upper"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                            <span class="req-icon">⚠️</span> Pelo menos 1 letra maiúscula (A-Z) <!-- Explicação DevIN: Declara trecho em linha. -->
                        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                        <div class="requirement-item req-invalid" id="req-special"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                            <span class="req-icon">⚠️</span> Pelo menos 1 caractere especial (ex: ! @ # $) <!-- Explicação DevIN: Declara trecho em linha. -->
                        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

                </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

            <div class="form-footer-action"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                <button type="submit" class="btn-submit">Cadastrar</button> <!-- Explicação DevIN: Declara botão de ação. -->
                <p class="login-redirect"> <!-- Explicação DevIN: Declara parágrafo. -->
                    Já tem conta? <a href="login.php">Faça login</a> <!-- Explicação DevIN: Declara link de navegação. -->
                </p> <!-- Explicação DevIN: Fecha elemento p do documento. -->
            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

        </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->

        <footer class="page-footer"> <!-- Explicação DevIN: Declara rodapé. -->
            Dev<span>IN</span> | Escola Profª Alcina Dantas Feijão | © DevIN 2026. Todos os direitos reservados. <!-- Explicação DevIN: Declara trecho em linha. -->
        </footer> <!-- Explicação DevIN: Fecha elemento footer do documento. -->

    </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->

    <section class="right-side"> <!-- Explicação DevIN: Declara seção temática. -->
        <div class="mascot-container"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
            <img src="../img/robocadastro.webp" alt="Robô DevIN" class="mascot-img"> <!-- Explicação DevIN: Declara imagem na interface. -->
        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
    </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->

</div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

<div id="status-alert-container"></div> <!-- Explicação DevIN: Declara agrupamento de elementos. -->

<script src="../js/cadastro.js"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. -->
<script src="../js/site-navigation.js"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. -->
<script src="../js/dashboard-i18n.js?v=<?= filemtime(__DIR__ . '/../js/dashboard-i18n.js') ?>"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
<script src="../js/lenis-global.js?v=<?= filemtime(__DIR__ . '/../js/lenis-global.js') ?>"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->

</body> <!-- Explicação DevIN: Fecha elemento body do documento. -->
</html> <!-- Explicação DevIN: Fecha elemento html do documento. -->
