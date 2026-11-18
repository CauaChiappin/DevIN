<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/cadastro_empresa.php
 * Finalidade: cadastrar dados de empresas.
 * Domínio: processamento no servidor.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


ob_start(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

require_once __DIR__ . '/controllers/AuthController.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/config/database.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/config/security.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/MailerHelper.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/config/RateLimiter.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
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
    /* Primeiro valida CSRF; depois normaliza nomes e números para que formatação visual
     * como pontos e traços não altere as verificações de CNPJ, CEP e telefone. */
    requireValidCsrf(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

    // Normaliza textos e remove pontuação dos números antes de validar e salvar os dados.
    $nome          = trim(requestString($_POST, 'nome')); // Explicação DevIN: Atribui um valor à variável PHP $nome.
    $email         = trim(requestString($_POST, 'email')); // Explicação DevIN: Atribui um valor à variável PHP $email.
    $cnpj          = preg_replace('/[^0-9]/', '', requestString($_POST, 'cnpj')); // Explicação DevIN: Atribui um valor à variável PHP $cnpj.
    $cep           = preg_replace('/[^0-9]/', '', requestString($_POST, 'cep')); // Explicação DevIN: Atribui um valor à variável PHP $cep.
    $telefone      = preg_replace('/[^0-9]/', '', requestString($_POST, 'telefone')); // Explicação DevIN: Atribui um valor à variável PHP $telefone.
    $senha         = requestString($_POST, 'senha'); // Explicação DevIN: Atribui um valor à variável PHP $senha.
    $confirmeSenha = requestString($_POST, 'confirme_senha'); // Explicação DevIN: Atribui um valor à variável PHP $confirmeSenha.

    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        // Limita cadastros por IP para reduzir automação e abuso do formulário.
        if (RateLimiter::consume('registration:ip:' . RateLimiter::clientIp(), 8, 3600) > 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new RuntimeException('Muitas tentativas de cadastro. Aguarde e tente novamente.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        /*
        |-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        | VALIDAÇÕES DOS CAMPOS // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        |-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        */

        // A validação no servidor é a regra efetiva; atributos HTML melhoram a experiência, mas podem ser contornados.

        if ($nome === '') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new Exception('Informe o nome da empresa.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new Exception('Informe um e-mail válido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if (strlen($cnpj) !== 14) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new Exception('Informe um CNPJ válido com 14 dígitos.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if (strlen($cep) !== 8) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new Exception('Informe um CEP válido com 8 dígitos.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if (strlen($telefone) < 10 || strlen($telefone) > 11) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new Exception('Informe um telefone válido (10 ou 11 dígitos com DDD).'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
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
            foreach (['pessoa', 'empresa', 'administrador'] as $tabelaEmail) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                // O nome da tabela vem de uma lista fixa; o e-mail segue como parâmetro preparado.
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

            // CNPJ único na tabela de empresas, consultado como parâmetro para não misturar dado e SQL.
            $stmtCnpj = $conn->prepare('SELECT id_empresa FROM empresa WHERE cnpj = ? LIMIT 1'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            if ($stmtCnpj) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                $stmtCnpj->bind_param('s', $cnpj); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmtCnpj->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $cnpjExiste = $stmtCnpj->get_result(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
                $existeCnpj = $cnpjExiste && $cnpjExiste->num_rows > 0; // Explicação DevIN: Atribui um valor à variável PHP $existeCnpj.
                $stmtCnpj->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

                if ($existeCnpj) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    throw new Exception('Este CNPJ já está cadastrado em nosso sistema.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            /*
            |-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            | HASH DA SENHA E INSERÇÃO // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            |-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            */

            // Guarda apenas o hash; PASSWORD_DEFAULT permite ao PHP escolher um algoritmo seguro padrão.
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT); // Explicação DevIN: Executa operação criptográfica de senha ou comparação protegida.
            if ($senhaHash === false) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new RuntimeException('Não foi possível proteger a senha.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $stmtInsert = $conn->prepare('INSERT INTO empresa (nome, cnpj, cep, email, senha_hash, telefone) VALUES (?, ?, ?, ?, ?, ?)'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.

            if (!$stmtInsert) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new RuntimeException('Erro ao preparar o cadastro da empresa.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            // Os valores são enviados separadamente do SQL para evitar injeção de comandos.
            $stmtInsert->bind_param('ssssss', $nome, $cnpj, $cep, $email, $senhaHash, $telefone); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmtInsert->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmtInsert->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

        } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
            $conn->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        /*
        |-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        | LOGIN AUTOMÁTICO E REDIRECIONAMENTO // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        |-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        */

        // Autentica a conta recém-criada, inicia a sessão e segue para o painel conforme o tipo de usuário.
        $auth = AuthController::login($email, $senha); // Explicação DevIN: Atribui um valor à variável PHP $auth.
        AuthController::establishSession($auth); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        AuditService::tryRecord('registration_success', 'success', 'empresa', (int) $auth['usuario']['id']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

        $idiomaEmailCadastro = $_SESSION['idioma_cadastro'] ?? ($auth['usuario']['idioma'] ?? 'pt-BR'); // Explicação DevIN: Atribui um valor à variável PHP $idiomaEmailCadastro.
        if (MailerHelper::enviarConfirmacaoCadastroEmpresa($email, $nome, $idiomaEmailCadastro)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $_SESSION['sucesso_cadastro'] = 'Cadastro confirmado. Enviamos uma confirmação para seu e-mail.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
            $_SESSION['erro_email_cadastro'] = 'Seu cadastro foi criado, mas não foi possível enviar o e-mail de confirmação.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        unset($_SESSION['idioma_cadastro']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

        header('Location: ' . AuthController::redirectByUserType($auth['usuario']['tipo'])); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

    } catch (mysqli_sql_exception $e) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        error_log('Erro MySQL cadastro empresa: ' . $e->getMessage()); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

        if ($e->getCode() === 1062) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $erro = 'Este CNPJ ou e-mail já está cadastrado em nosso sistema.'; // Explicação DevIN: Atribui um valor à variável PHP $erro.
        } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
            $erro = 'Não foi possível cadastrar a empresa. Tente novamente.'; // Explicação DevIN: Atribui um valor à variável PHP $erro.
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
    <title>DevIN | Criar Conta Empresa</title> <!-- Explicação DevIN: Declara elemento de marcação. -->
    <script src="../js/index-language.js"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. -->
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. -->
    <link rel="icon" type="image/png" href="../img/favicon.png"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. -->
    <link rel="stylesheet" href="../css/cadastrostyle.css?v=<?= filemtime(__DIR__ . '/../css/cadastrostyle.css') ?>"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. Contém dado dinâmico processado pelo PHP. -->
    <link rel="stylesheet" href="../css/site-navigation.css"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. -->
    <link rel="stylesheet" href="../css/ui-consistency.css?v=<?= filemtime(__DIR__ . '/../css/ui-consistency.css') ?>"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. Contém dado dinâmico processado pelo PHP. -->
</head> <!-- Explicação DevIN: Fecha elemento head do documento. -->
<body class="cadastro-page"> <!-- Explicação DevIN: Declara corpo visível da página. -->

<div class="main-container"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
    <section class="left-side"> <!-- Explicação DevIN: Declara seção temática. -->
        <header class="cadastro-header"> <!-- Explicação DevIN: Declara cabeçalho da seção ou página. -->
            <div class="brand-logo"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                <a href="index.php">Dev<span>IN</span></a> <!-- Explicação DevIN: Declara link de navegação. A linha reúne múltiplos elementos. -->
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
            <a href="cadastro_pessoa.php" class="toggle-btn pessoal">Pessoal</a> <!-- Explicação DevIN: Declara link de navegação. -->
            <span class="toggle-divider">OU</span> <!-- Explicação DevIN: Declara trecho em linha. -->
            <a href="cadastro_empresa.php" class="toggle-btn empresa active">Empresa</a> <!-- Explicação DevIN: Declara link de navegação. -->
        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

        <h1 class="page-title">Criar conta</h1> <!-- Explicação DevIN: Declara título principal. -->

        <?php if (!empty($erro)): ?>
            <div class="php-toast error-toast" role="alert"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
            <!-- Escapa a mensagem antes de inseri-la no HTML para evitar execução de conteúdo enviado. -->
                <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
        <?php endif; ?>

        <form action="cadastro_empresa.php" method="POST" class="register-form" id="formCadastro"> <!-- Explicação DevIN: Declara formulário de entrada de dados. -->
            <!-- O token oculto acompanha o POST e é conferido pelo backend antes de processar o cadastro. -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->

            <div class="form-columns"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                <div class="form-column"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                    <div class="input-group mobile-order-1"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                        <label for="nome">Nome da Empresa:*</label> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                        <input type="text" id="nome" name="nome" required value="<?= htmlspecialchars(requestString($_POST, 'nome'), ENT_QUOTES, 'UTF-8') ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

                    <div class="input-group mobile-order-3"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                        <label for="cnpj">CNPJ:*</label> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                        <input type="text" id="cnpj" name="cnpj" placeholder="00.000.000/0000-00" required value="<?= htmlspecialchars(requestString($_POST, 'cnpj'), ENT_QUOTES, 'UTF-8') ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
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
                <p class="login-redirect">Já tem conta? <a href="login.php">Faça login</a></p> <!-- Explicação DevIN: Declara parágrafo. A linha reúne múltiplos elementos. -->
            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
        </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->

  <footer class="page-footer"> <!-- Explicação DevIN: Declara rodapé. -->
    Dev<span>IN</span> | Escola Profª Alcina Dantas Feijão | © DevIN 2026. Todos os direitos reservados<a class="secret-doom" href="../html/jogos/doom.html">.</a> <!-- Explicação DevIN: Declara trecho em linha. A linha reúne múltiplos elementos. -->
</footer>   <!-- Explicação DevIN: Fecha elemento footer do documento. -->
    </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->

    <section class="right-side"> <!-- Explicação DevIN: Declara seção temática. -->
        <div class="mascot-container"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
            <img src="../img/robocadastro.webp" alt="Robô DevIN" class="mascot-img"> <!-- Explicação DevIN: Declara imagem na interface. -->
        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
    </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->
</div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

<script src="../js/cadastro.js"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. -->
<script src="../js/site-navigation.js"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. -->
<script src="../js/dashboard-i18n.js?v=<?= filemtime(__DIR__ . '/../js/dashboard-i18n.js') ?>"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
<script src="../js/lenis-global.js?v=<?= filemtime(__DIR__ . '/../js/lenis-global.js') ?>"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
</body> <!-- Explicação DevIN: Fecha elemento body do documento. -->
</html> <!-- Explicação DevIN: Fecha elemento html do documento. -->
