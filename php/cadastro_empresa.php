<?php

// Guarda o que vai para a tela para conseguir redirecionar a página depois sem dar erro
ob_start();

// Puxa os arquivos do sistema que ajudam no login, banco de dados e segurança
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/security.php';

// Inicia a sessão de forma segura para guardar o usuário logado
startSecureSession();

// Cria uma variável vazia. Se algo der errado, a mensagem de erro fica aqui
$erro = '';

/*
|--------------------------------------------------------------------------
| PROCESSAMENTO DO FORMULÁRIO
|--------------------------------------------------------------------------
*/

// Só roda esse código se a pessoa apertou o botão de cadastrar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Checa se o formulário veio do nosso site mesmo (segurança)
    requireValidCsrf();

    // Pega o nome e e-mail e tira espaços extras no começo e no fim
    $nome  = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');

    // Limpa o CNPJ, CEP e telefone para guardar SÓ os números (tira pontos, traços e parênteses)
    $cnpj     = preg_replace('/[^0-9]/', '', $_POST['cnpj'] ?? '');
    $cep      = preg_replace('/[^0-9]/', '', $_POST['cep'] ?? '');
    $telefone = preg_replace('/[^0-9]/', '', $_POST['telefone'] ?? '');

    // Pega as senhas digitadas
    $senha         = $_POST['senha'] ?? '';
    $confirmeSenha = $_POST['confirme_senha'] ?? '';

    try {

        /*
        |--------------------------------------------------------------------------
        | TESTES DOS CAMPOS
        |--------------------------------------------------------------------------
        */

        // Se não digitou o nome, para e avisa
        if ($nome === '') {
            throw new Exception('Informe o nome da empresa.');
        }

        // Testa se o e-mail tem o formato certo (exemplo: nome@dominio.com)
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Informe um e-mail válido.');
        }

        // O CEP precisa ter exatamente 8 números
        if (strlen($cep) !== 8) {
            throw new Exception('Informe um CEP válido.');
        }

        // O telefone precisa ter no mínimo 10 números (DDD + número)
        if (strlen($telefone) < 10) {
            throw new Exception('Informe um telefone válido.');
        }

        /*
        |--------------------------------------------------------------------------
        | REGRAS DA SENHA
        |--------------------------------------------------------------------------
        */

        // Se a senha estiver vazia, para tudo
        if ($senha === '') {
            throw new Exception('Informe uma senha.');
        }

        // Checa se as duas senhas digitadas são iguais
        if ($senha !== $confirmeSenha) {
            throw new Exception('As senhas não coincidem.');
        }

        // A senha tem que ter pelo menos 8 letras/números
        if (strlen($senha) < 8) {
            throw new Exception('A senha deve ter no mínimo 8 caracteres.');
        }

        // Procura se tem pelo menos 1 letra MAIÚSCULA
        if (!preg_match('/[A-Z]/', $senha)) {
            throw new Exception('A senha deve possuir pelo menos uma letra maiúscula.');
        }

        // Procura se tem pelo menos 1 símbolo (tipo @, #, !, $)
        if (!preg_match('/[^a-zA-Z0-9]/', $senha)) {
            throw new Exception('A senha deve possuir pelo menos um caractere especial.');
        }

        /*
        |--------------------------------------------------------------------------
        | CONSULTA AO BANCO DE DADOS
        |--------------------------------------------------------------------------
        */

        // Manda o sistema avisar se der qualquer erro no banco
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        // Conecta com o banco de dados
        $conn = getDatabaseConnection();

        /*
        |--------------------------------------------------------------------------
        | CONFERE SE O E-MAIL JÁ EXISTE NO SISTEMA
        |--------------------------------------------------------------------------
        */

        // Procura em todas as tabelas pra ver se esse e-mail já não foi cadastrado antes
        foreach (['pessoa', 'empresa', 'administrador'] as $tabelaEmail) {
            
            // Prepara a busca no banco de forma segura
            $stmtEmail = $conn->prepare("SELECT email FROM {$tabelaEmail} WHERE email = ? LIMIT 1");

            if (!$stmtEmail) {
                throw new RuntimeException('Nao foi possivel validar o e-mail.');
            }

            // Coloca o e-mail na busca e executa
            $stmtEmail->bind_param('s', $email);
            $stmtEmail->execute();
            
            // Confere se achou algum e-mail igual
            $emailExiste = $stmtEmail->get_result();
            $existe      = $emailExiste && $emailExiste->num_rows > 0;
            $stmtEmail->close();

            // Se achou um e-mail igual, não deixa cadastrar
            if ($existe) {
                throw new InvalidArgumentException(
                    'Este e-mail ja esta cadastrado. Use outro e-mail ou faca login.'
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | ESCONDE A SENHA E SALVA
        |--------------------------------------------------------------------------
        */

        // Embaralha a senha para ela não ficar visível no banco de dados
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        // Prepara o comando para inserir a empresa nova no banco
        $stmt = $conn->prepare("
            INSERT INTO empresa (nome, cnpj, cep, email, senha_hash, telefone)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        // Envia os 6 dados limpos para o comando do banco de dados
        $stmt->bind_param('ssssss', $nome, $cnpj, $cep, $email, $senhaHash, $telefone);

        // Executa o salvamento
        $stmt->execute();

        // Fecha a conexão com o banco
        $stmt->close();
        $conn->close();

        /*
        |--------------------------------------------------------------------------
        | ENTRADA AUTOMÁTICA
        |--------------------------------------------------------------------------
        */

        // Faz o login do usuário recém-cadastrado
        $auth = AuthController::login($email, $senha);

        // Inicia a sessão dele
        AuthController::establishSession($auth);

        // Manda o usuário direto para o painel da empresa
        header('Location: ' . AuthController::redirectByUserType($auth['usuario']['tipo']));
        exit;

    } catch (mysqli_sql_exception $e) {

        // Se o banco falhar, salva o motivo num arquivo secreto de erros no servidor
        error_log('Erro MySQL cadastro empresa: ' . $e->getMessage());

        // Código 1062 significa dados duplicados (ex: CNPJ que já existe)
        if ($e->getCode() === 1062) {
            $erro = 'Este CNPJ ou e-mail já está cadastrado.';
        } else {
            $erro = 'Não foi possível cadastrar a empresa.';
        }

    } catch (Throwable $e) {

        // Pega a mensagem de erro que definimos nos testes acima para mostrar na tela
        $erro = $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">

    <!-- Faz a página se ajustar certinho em telas de celular -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>DevIN | Criar Conta</title>

    <!-- Ícones da aba do navegador -->
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
    <link rel="icon" type="image/png" href="../img/favicon.png">

    <!-- Estilo visual (cores, tamanhos e posições) -->
    <link rel="stylesheet" href="../css/cadastrostyle.css">

</head>

<body>

<div class="main-container">

    <section class="left-side">

        <!-- Logo do site -->
        <div class="brand-logo">
            <a href="index.php">Dev<span>IN</span></a>
        </div>

        <!-- Botões para trocar entre cadastro Pessoal e Empresa -->
        <div class="toggle-container">
            <a href="cadastro_pessoa.php" class="toggle-btn pessoal">Pessoal</a>
            <span class="toggle-divider">OU</span>
            <a href="cadastro_empresa.php" class="toggle-btn empresa active">Empresa</a>
        </div>

        <h1 class="page-title">Criar conta</h1>

        <!-- Mostra o aviso vermelho SÓ SE tiver algum erro retornado pelo PHP -->
        <?php if (!empty($erro)): ?>
            <div class="php-toast error-toast" style="color: red; font-weight: bold; margin-bottom: 15px;">
                <!-- O htmlspecialchars impede invasões via texto do erro -->
                <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
            </div>
        <?php endif; ?>

        <!-- Formulário onde o usuário digita os dados -->
        <form action="cadastro_empresa.php" method="POST" class="register-form" id="formCadastro">
            
            <!-- Campo invisível com a chave de segurança do formulário -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">

            <div class="form-columns">

                <!-- Coluna 1 da tela -->
                <div class="form-column">

                    <div class="input-group">
                        <label for="nome">Nome:*</label>
                        <!-- Se der erro ao enviar, este 'value' mantém o que a pessoa já digitou para não apagar tudo -->
                        <input type="text" id="nome" name="nome" required value="<?= htmlspecialchars($_POST['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="input-group">
                        <label for="cnpj">CNPJ:*</label>
                        <input type="text" id="cnpj" name="cnpj" placeholder="00.000.000/0000-00" required value="<?= htmlspecialchars($_POST['cnpj'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="input-group">
                        <label for="cep">CEP:*</label>
                        <input type="text" id="cep" name="cep" placeholder="00000-000" maxlength="9" required value="<?= htmlspecialchars($_POST['cep'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="input-group password-wrapper">
                        <label for="confirme_senha">Confirme a sua senha:*</label>
                        <div class="input-icon-container">
                            <input type="password" id="confirme_senha" name="confirme_senha" required>
                            <!-- Ícone do olho que mostra/esconde a senha ao clicar -->
                            <img src="../img/olho_fechado.png" class="toggle-password-eye" onclick="togglePasswordVisibility('confirme_senha', this)" alt="Mostrar ou ocultar senha">
                        </div>
                        <span id="error-match" class="error-message-text">Senhas não coincidem</span>
                    </div>

                </div>

                <!-- Coluna 2 da tela -->
                <div class="form-column">

                    <div class="input-group">
                        <label for="email">E-mail:*</label>
                        <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="input-group">
                        <label for="telefone">Telefone:*</label>
                        <input type="tel" id="telefone" name="telefone" placeholder="(00) 00000-0000" required value="<?= htmlspecialchars($_POST['telefone'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="input-group password-wrapper">
                        <label for="senha">Senha:*</label>
                        <div class="input-icon-container">
                            <input type="password" id="senha" name="senha" required>
                            <img src="../img/olho_fechado.png" class="toggle-password-eye" onclick="togglePasswordVisibility('senha', this)" alt="Mostrar ou ocultar senha">
                        </div>
                    </div>

                    <!-- Lista de requisitos da senha (muda de cor dinamicamente conforme digita) -->
                    <div class="password-requirements">
                        <div class="requirement-item req-invalid" id="req-length">
                            <span class="req-icon">⚠️</span> No mínimo 8 caracteres
                        </div>
                        <div class="requirement-item req-invalid" id="req-upper">
                            <span class="req-icon">⚠️</span> Pelo menos 1 letra maiúscula (A-Z)
                        </div>
                        <div class="requirement-item req-invalid" id="req-special">
                            <span class="req-icon">⚠️</span> Pelo menos 1 caractere especial (como ! @ # $)
                        </div>
                    </div>

                </div>

            </div>

            <!-- Botão de enviar e link de login -->
            <div class="form-footer-action">
                <button type="submit" class="btn-submit">Cadastrar</button>
                <p class="login-redirect">Já tem conta? <a href="login.php">Faça login</a></p>
            </div>

        </form>

        <footer class="page-footer">
            Dev<span>IN</span> | Escola Profª Alcina Dantas Feijão | © DevIN 2026. Todos os direitos reservados
            <a href="../html/jogos/doom.html" class="secret-doom" aria-label="." title="">.</a>
        </footer>

    </section>

    <!-- Lado direito da tela com a imagem do mascote -->
    <section class="right-side">
        <a href="login.php" class="btn-top-login">Login</a>
        <div class="mascot-container">
            <img src="../img/robocadastro.webp" alt="Robô DevIN" class="mascot-img">
        </div>

        <div class="toggle-container">

            <a
                href="cadastro_pessoa.php"
                class="toggle-btn pessoal"
            >
                Pessoal
            </a>

            <span class="toggle-divider">
                OU
            </span>

            <a
                href="cadastro_empresa.php"
                class="toggle-btn empresa active"
            >
                Empresa
            </a>

        </div>

        <h1 class="page-title">
            Criar conta
        </h1>

        <?php if (!empty($erro)): ?>

            <div
                class="php-toast error-toast"
                style="
                    color: red;
                    font-weight: bold;
                    margin-bottom: 15px;
                "
            >

                <?= htmlspecialchars(
                    $erro,
                    ENT_QUOTES,
                    'UTF-8'
                ) ?>

            </div>

        <?php endif; ?>

        <form
            action="cadastro_empresa.php"
            method="POST"
            class="register-form"
            id="formCadastro"
        >
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">

            <div class="form-columns">

                <div class="form-column">

                    <div class="input-group">

                        <label for="nome">
                            Nome:*
                        </label>

                        <input
                            type="text"
                            id="nome"
                            name="nome"
                            required
                            value="<?= htmlspecialchars(
                                $_POST['nome'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                        >

                    </div>

                    <div class="input-group">

                        <label for="cnpj">
                            CNPJ:*
                        </label>

                        <input
                            type="text"
                            id="cnpj"
                            name="cnpj"
                            placeholder="00.000.000/0000-00"
                            required
                            value="<?= htmlspecialchars(
                                $_POST['cnpj'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                        >

                    </div>

                    <div class="input-group">

                        <label for="cep">
                            CEP:*
                        </label>

                        <input
                            type="text"
                            id="cep"
                            name="cep"
                            placeholder="00000-000"
                            maxlength="9"
                            required
                            value="<?= htmlspecialchars(
                                $_POST['cep'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                        >

                    </div>

                    <div class="input-group password-wrapper">

                        <label for="confirme_senha">
                            Confirme a sua senha:*
                        </label>

                        <div class="input-icon-container">

                            <input
                                type="password"
                                id="confirme_senha"
                                name="confirme_senha"
                                required
                            >

                            <img
                                src="../img/olho_fechado.png"
                                class="toggle-password-eye"
                                onclick="togglePasswordVisibility(
                                    'confirme_senha',
                                    this
                                )"
                                alt="Mostrar ou ocultar senha"
                            >

                        </div>

                        <span
                            id="error-match"
                            class="error-message-text"
                        >
                            Senhas não coincidem
                        </span>

                    </div>

                </div>

                <div class="form-column">

                    <div class="input-group">

                        <label for="email">
                            E-mail:*
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            required
                            value="<?= htmlspecialchars(
                                $_POST['email'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                        >

                    </div>

                    <div class="input-group">

                        <label for="telefone">
                            Telefone:*
                        </label>

                        <input
                            type="tel"
                            id="telefone"
                            name="telefone"
                            placeholder="(00) 00000-0000"
                            required
                            value="<?= htmlspecialchars(
                                $_POST['telefone'] ?? '',
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>"
                        >

                    </div>

                    <div class="input-group password-wrapper">

                        <label for="senha">
                            Senha:*
                        </label>

                        <div class="input-icon-container">

                            <input
                                type="password"
                                id="senha"
                                name="senha"
                                required
                            >

                            <img
                                src="../img/olho_fechado.png"
                                class="toggle-password-eye"
                                onclick="togglePasswordVisibility(
                                    'senha',
                                    this
                                )"
                                alt="Mostrar ou ocultar senha"
                            >

                        </div>

                    </div>

                    <div class="password-requirements">

                        <div
                            class="requirement-item req-invalid"
                            id="req-length"
                        >

                            <span class="req-icon">
                                ⚠️
                            </span>

                            No mínimo 8 caracteres

                        </div>

                        <div
                            class="requirement-item req-invalid"
                            id="req-upper"
                        >

                            <span class="req-icon">
                                ⚠️
                            </span>

                            Pelo menos 1 letra maiúscula (A-Z)

                        </div>

                        <div
                            class="requirement-item req-invalid"
                            id="req-special"
                        >

                            <span class="req-icon">
                                ⚠️
                            </span>

                            Pelo menos 1 caractere especial
                            (como ! @ # $)

                        </div>

                    </div>

                </div>

            </div>

            <div class="form-footer-action">

                <button
                    type="submit"
                    class="btn-submit"
                >
                    Cadastrar
                </button>

                <p class="login-redirect">

                    Já tem conta?

                    <a href="login.php">
                        Faça login
                    </a>

                </p>

            </div>

        </form>

        <footer class="page-footer">

            Dev<span>IN</span> |
            Escola Profª Alcina Dantas Feijão |
            © DevIN 2026.
            Todos os direitos reservados<a
            href="../html/jogos/doom.html"
            class="secret-doom"
            aria-label="."
            title=""
        >.</a>

        </footer>

       

    </section>

    <section class="right-side">

        <a
            href="login.php"
            class="btn-top-login"
        >
            Login
        </a>

        <div class="mascot-container">

            <img
                src="../img/robocadastro.webp"
                alt="Robô DevIN"
                class="mascot-img"
            >

        </div>

    </section>

</div>

<script src="../js/cadastro.js"></script>

</body>
