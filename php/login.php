<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

require_once __DIR__ . '/controllers/AuthController.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/config/RateLimiter.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/controllers/Privacy/AuditService.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

startSecureSession(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

// Se o usuário já estiver logado, redireciona diretamente para o dashboard apropriado
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !empty($_SESSION['logado'])) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    header('Location: ' . AuthController::redirectByUserType($_SESSION['usuario_tipo'] ?? '')); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

$erro = requestString($_GET, 'erro') === 'sessao_expirada' // Explicação DevIN: Atribui um valor à variável PHP $erro.
    ? 'Sua sessão expirou. Entre novamente.' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    : ''; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
$emailDigitado = ''; // Explicação DevIN: Atribui um valor à variável PHP $emailDigitado.

if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        // Rejeita envio forjado e limita tentativas por endereço de rede e identidade informada.
        requireValidCsrf(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

        $emailDigitado = requestString($_POST, 'email'); // Explicação DevIN: Atribui um valor à variável PHP $emailDigitado.
        $senhaDigitada = requestString($_POST, 'senha'); // Explicação DevIN: Atribui um valor à variável PHP $senhaDigitada.

        $ipKey = 'login:ip:' . RateLimiter::clientIp(); // Explicação DevIN: Atribui um valor à variável PHP $ipKey.
        $identityKey = 'login:identity:' . strtolower($emailDigitado); // Explicação DevIN: Atribui um valor à variável PHP $identityKey.
        $ipBlocked = RateLimiter::consume($ipKey, 20, 900); // Explicação DevIN: Atribui um valor à variável PHP $ipBlocked.
        $identityBlocked = RateLimiter::consume($identityKey, 8, 900); // Explicação DevIN: Atribui um valor à variável PHP $identityBlocked.
        if ($ipBlocked > 0 || $identityBlocked > 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new RuntimeException('Muitas tentativas. Aguarde e tente novamente.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        // Remove bloqueios após autenticação correta e inicia uma sessão nova para o usuário.
        $auth = AuthController::login($emailDigitado, $senhaDigitada); // Explicação DevIN: Atribui um valor à variável PHP $auth.
        RateLimiter::clear($ipKey); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        RateLimiter::clear($identityKey); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        AuthController::establishSession($auth); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        AuditService::tryRecord('login_success', 'success', (string) $auth['usuario']['tipo'], (int) $auth['usuario']['id']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

        if ($auth['usuario']['tipo'] === 'pessoa') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            header('Location: pessoa.php'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        header('Location: ' . AuthController::redirectByUserType($auth['usuario']['tipo'])); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    } catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        AuditService::tryRecord('login_failure', 'failure'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        $erro = $exception->getMessage(); // Explicação DevIN: Atribui um valor à variável PHP $erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
?>
<!DOCTYPE html> <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
<html lang="pt-BR"> <!-- Explicação DevIN: Declara elemento raiz do documento HTML. -->

<head> <!-- Explicação DevIN: Declara metadados e dependências do documento. -->
    <meta charset="UTF-8"> <!-- Explicação DevIN: Declara metadado de navegação. -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Explicação DevIN: Declara metadado de navegação. -->
    <link rel="preconnect" href="https://fonts.googleapis.com"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. -->
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. -->
    <link rel="stylesheet" href="../css/login.css?v=<?= filemtime(__DIR__ . '/../css/login.css') ?>"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. Contém dado dinâmico processado pelo PHP. -->
    <link rel="stylesheet" href="../css/style.css?v=<?= filemtime(__DIR__ . '/../css/style.css') ?>"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. Contém dado dinâmico processado pelo PHP. -->
    <link rel="stylesheet" href="../css/site-navigation.css"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. -->
    <script src="../js/index-language.js"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. -->
    <title>DevIN | Login</title> <!-- Explicação DevIN: Declara elemento de marcação. -->
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. -->
</head> <!-- Explicação DevIN: Fecha elemento head do documento. -->

<body> <!-- Explicação DevIN: Declara corpo visível da página. -->

    <header class="cabecalho-site"> <!-- Explicação DevIN: Declara cabeçalho da seção ou página. -->
        <nav class="navegacao"> <!-- Explicação DevIN: Declara navegação. -->
            <a class="marca" href="index.php#inicio">Dev<span>IN</span></a> <!-- Explicação DevIN: Declara link de navegação. A linha reúne múltiplos elementos. -->
            <button class="site-menu-toggle" type="button" aria-label="Abrir menu" aria-controls="site-menu"
                aria-expanded="false" data-site-menu-toggle>
                <span aria-hidden="true"></span><span aria-hidden="true"></span><span aria-hidden="true"></span> <!-- Explicação DevIN: Declara trecho em linha. Inclui atributos de acessibilidade. A linha reúne múltiplos elementos. -->
            </button> <!-- Explicação DevIN: Fecha elemento button do documento. -->
            <div class="site-menu" id="site-menu" data-site-menu> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                <div class="menu"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                    <a href="index.php#conheca">Conheça o DevIN</a> <!-- Explicação DevIN: Declara link de navegação. -->
                    <a href="index.php#etapas">Etapas</a> <!-- Explicação DevIN: Declara link de navegação. -->
                    <a href="index.php#contato">Contato</a> <!-- Explicação DevIN: Declara link de navegação. -->
                </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                <div class="acoes"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                    <a class="botao botao-branco" href="cadastro_pessoa.php">Cadastrar-se</a> <!-- Explicação DevIN: Declara link de navegação. -->
                    <label class="language-picker" for="site-language"> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                        <span>Idioma</span> <!-- Explicação DevIN: Declara trecho em linha. -->
                        <select id="site-language" aria-label="Idioma"> <!-- Explicação DevIN: Declara seleção de opções. Inclui atributos de acessibilidade. -->
                            <option value="pt-BR">Português</option> <!-- Explicação DevIN: Declara opção selecionável. -->
                            <option value="en">English</option> <!-- Explicação DevIN: Declara opção selecionável. -->
                            <option value="es">Español</option> <!-- Explicação DevIN: Declara opção selecionável. -->
                        </select> <!-- Explicação DevIN: Fecha elemento select do documento. -->
                    </label> <!-- Explicação DevIN: Fecha elemento label do documento. -->
                </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
        </nav> <!-- Explicação DevIN: Fecha elemento nav do documento. -->
    </header> <!-- Explicação DevIN: Fecha elemento header do documento. -->

    <main class="conteudo-login"> <!-- Explicação DevIN: Declara conteúdo principal da página. -->

        <img class="gif-robo" src="../img/robologin.gif" alt="Robô DevIN"> <!-- Explicação DevIN: Declara imagem na interface. -->

        <div class="area-login"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
            <h1>Login</h1> <!-- Explicação DevIN: Declara título principal. -->

            <?php if (!empty($erro)): ?>
                <p class="mensagem-erro" role="alert"> <!-- Explicação DevIN: Declara parágrafo. -->
                    <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?>
                </p> <!-- Explicação DevIN: Fecha elemento p do documento. -->
            <?php endif; ?>

            <form action="login.php" method="POST"> <!-- Explicação DevIN: Declara formulário de entrada de dados. -->
                <!-- Token de sessão validado pelo servidor antes da tentativa de login. -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->

                <div class="grupo-campo"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                    <label for="email">E-mail:</label> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                    <input type="email" id="email" name="email" placeholder="Seu e-mail..." value="<?= htmlspecialchars($emailDigitado, ENT_QUOTES, 'UTF-8') ?>" required autocomplete="email"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

                <div class="grupo-campo campo-senha input-container"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                    <label for="senha">Senha:</label> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                    <input type="password" id="senha" name="senha" placeholder="Sua senha..." required autocomplete="current-password"> <!-- Explicação DevIN: Declara campo de entrada. -->

                    <button type="button" id="btn-mostrar" aria-label="Mostrar senha"> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                        <img id="img-olho" src="../img/olho_fechado.png" alt="Mostrar Senha"> <!-- Explicação DevIN: Declara imagem na interface. -->
                    </button> <!-- Explicação DevIN: Fecha elemento button do documento. -->
                </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

                <a href="recuperacao.php" class="link-esqueceu">Esqueceu a Senha?</a> <!-- Explicação DevIN: Declara link de navegação. -->

                <button type="submit" class="botao-entrar">Entrar</button> <!-- Explicação DevIN: Declara botão de ação. -->
            </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->

            <p class="texto-politica"> <!-- Explicação DevIN: Declara parágrafo. -->
                Ao continuar, você reconhece a <a href="politica_privacidade.php">Política de Privacidade</a> do DevIN. <!-- Explicação DevIN: Declara link de navegação. -->
            </p> <!-- Explicação DevIN: Fecha elemento p do documento. -->
        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
        <script src="../js/login.js"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. -->
        <script src="../js/site-navigation.js"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. -->
    </main> <!-- Explicação DevIN: Fecha elemento main do documento. -->

    <script src="../js/lenis-global.js?v=<?= filemtime(__DIR__ . '/../js/lenis-global.js') ?>"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
    <script src="../js/dashboard-i18n.js?v=<?= filemtime(__DIR__ . '/../js/dashboard-i18n.js') ?>"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
</body> <!-- Explicação DevIN: Fecha elemento body do documento. -->

</html> <!-- Explicação DevIN: Fecha elemento html do documento. -->
