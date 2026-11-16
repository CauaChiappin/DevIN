<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

require_once __DIR__ . '/middlewares/auth.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/config/database.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/config/security.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

/*
|-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
| VERIFICA LOGIN E TIPO DE CONTA // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
|-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
*/

/* A identidade não vem do formulário: requireWebAuth lê a sessão validada e também
 * confere o perfil, evitando que outra categoria abra a área de currículo. */
$usuario = requireWebAuth('pessoa'); // Explicação DevIN: Atribui um valor à variável PHP $usuario.

/*
|-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
| IDENTIFICA A PESSOA // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
|-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
*/

$idPessoa    = (int) $usuario['id']; // Explicação DevIN: Atribui um valor à variável PHP $idPessoa.
$nomePessoa  = $_SESSION['nome_pessoa']  ?? $usuario['nome']  ?? 'Candidato'; // Explicação DevIN: Atribui um valor à variável PHP $nomePessoa.
$emailPessoa = $_SESSION['email_pessoa'] ?? $usuario['email'] ?? ''; // Explicação DevIN: Atribui um valor à variável PHP $emailPessoa.
$gerarComIa = requestString($_GET, 'ia') === '1'; // Explicação DevIN: Atribui um valor à variável PHP $gerarComIa.

$mensagemSucesso = (string) ($_SESSION['sucesso_curriculo'] ?? ''); // Explicação DevIN: Atribui um valor à variável PHP $mensagemSucesso.
$mensagemErro = (string) ($_SESSION['erro_curriculo'] ?? ''); // Explicação DevIN: Atribui um valor à variável PHP $mensagemErro.
$mensagemCadastro = (string) ($_SESSION['sucesso_cadastro'] ?? ''); // Explicação DevIN: Atribui um valor à variável PHP $mensagemCadastro.
$mensagemEmailCadastro = (string) ($_SESSION['erro_email_cadastro'] ?? ''); // Explicação DevIN: Atribui um valor à variável PHP $mensagemEmailCadastro.
$mensagemSenhaAlterada = (string) ($_SESSION['sucesso_login'] ?? ''); // Explicação DevIN: Atribui um valor à variável PHP $mensagemSenhaAlterada.
$mensagemEmailSenha = (string) ($_SESSION['erro_email_senha'] ?? ''); // Explicação DevIN: Atribui um valor à variável PHP $mensagemEmailSenha.
$avisoSenha = implode(' ', array_filter([$mensagemSenhaAlterada, $mensagemEmailSenha])); // Explicação DevIN: Atribui um valor à variável PHP $avisoSenha.
// Estas mensagens são flash: a página as mostra uma vez e remove-as antes do próximo carregamento.
unset($_SESSION['sucesso_curriculo'], $_SESSION['erro_curriculo'], $_SESSION['sucesso_cadastro'], $_SESSION['erro_email_cadastro'], $_SESSION['sucesso_login'], $_SESSION['erro_email_senha']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

// Se o currículo acabou de ser salvo com sucesso, agenda o redirecionamento para pessoa.php em 2 segundos
if ($mensagemSucesso !== '') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    header('Refresh: 2; url=pessoa.php'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

/*
|-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
| BUSCA DADOS ATUAIS DO CURRÍCULO (GET) // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
|-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
*/

$dadosCurriculo = []; // Explicação DevIN: Atribui um valor à variável PHP $dadosCurriculo.

try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    $conn = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $conn.
    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        /* A consulta usa o ID vindo da sessão, então a página só carrega o currículo do usuário atual.
         * O placeholder e bind_param separam o valor do SQL e evitam concatenar entrada na consulta. */
        $stmtFetch = $conn->prepare("
            SELECT nome_social, grau_de_escolaridade, cursos, experiencia, idiomas
            FROM curriculo
            WHERE id_pessoa = ?
            LIMIT 1
        "); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        if ($stmtFetch) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $stmtFetch->bind_param('i', $idPessoa); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmtFetch->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $dadosCurriculo = $stmtFetch->get_result()->fetch_assoc() ?? []; // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            $stmtFetch->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
        $conn->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} catch (Throwable $e) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    error_log('Erro ao buscar currículo: ' . $e->getMessage()); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

$cNomeSocial       = $dadosCurriculo['nome_social']          ?? ''; // Explicação DevIN: Atribui um valor à variável PHP $cNomeSocial.
$cGrauEscolaridade = $dadosCurriculo['grau_de_escolaridade'] ?? ''; // Explicação DevIN: Atribui um valor à variável PHP $cGrauEscolaridade.
$cCursos           = $dadosCurriculo['cursos']               ?? ''; // Explicação DevIN: Atribui um valor à variável PHP $cCursos.
$cExperiencia      = $dadosCurriculo['experiencia']          ?? ''; // Explicação DevIN: Atribui um valor à variável PHP $cExperiencia.
$cIdiomas          = $dadosCurriculo['idiomas']              ?? ''; // Explicação DevIN: Atribui um valor à variável PHP $cIdiomas.

// Valores ausentes viram strings vazias para que o formulário funcione tanto no primeiro acesso quanto na edição.

?>
<!DOCTYPE html> <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
<html lang="pt-BR"> <!-- Explicação DevIN: Declara elemento raiz do documento HTML. -->

<head> <!-- Explicação DevIN: Declara metadados e dependências do documento. -->
    <meta charset="UTF-8"> <!-- Explicação DevIN: Declara metadado de navegação. -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Explicação DevIN: Declara metadado de navegação. -->
    <title>DevIN | Preencher Currículo</title> <!-- Explicação DevIN: Declara elemento de marcação. -->
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. -->
    <link rel="stylesheet" href="../css/cadastrostyle.css"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. -->
    <link rel="stylesheet" href="../css/site-navigation.css"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. -->
    <link rel="stylesheet" href="../css/curriculo.css?v=<?= filemtime(__DIR__ . '/../css/curriculo.css') ?>"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. Contém dado dinâmico processado pelo PHP. -->
</head> <!-- Explicação DevIN: Fecha elemento head do documento. -->

<body class="curriculo-page"> <!-- Explicação DevIN: Declara corpo visível da página. -->

<div class="main-container"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->

    <div class="left-side"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->

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
                <a href="logout.php" class="header-action">Sair</a> <!-- Explicação DevIN: Declara link de navegação. -->
            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
        </header> <!-- Explicação DevIN: Fecha elemento header do documento. -->

        <?php if ($mensagemSucesso): ?>
            <div class="php-toast success-toast" role="status"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                <?= htmlspecialchars($mensagemSucesso, ENT_QUOTES, 'UTF-8') ?>
            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
            <script>
                setTimeout(function () { // Explicação DevIN: Define função anônima usada como operação ou callback.
                    window.location.href = 'pessoa.php'; // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                }, 2000); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
            </script>
        <?php endif; ?>

        <?php if ($mensagemErro): ?>
            <div class="php-toast error-toast" role="alert"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                <?= htmlspecialchars($mensagemErro, ENT_QUOTES, 'UTF-8') ?>
            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
        <?php endif; ?>

        <?php if ($mensagemCadastro): ?>
            <div class="php-toast success-toast" role="status"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                <?= htmlspecialchars($mensagemCadastro, ENT_QUOTES, 'UTF-8') ?>
            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
        <?php endif; ?>

        <?php if ($mensagemEmailCadastro): ?>
            <div class="php-toast error-toast" role="alert"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                <?= htmlspecialchars($mensagemEmailCadastro, ENT_QUOTES, 'UTF-8') ?>
            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
        <?php endif; ?>

        <?php if ($avisoSenha !== ''): ?>
            <div class="php-toast <?= $mensagemEmailSenha !== '' ? 'error-toast' : 'success-toast' ?>" role="<?= $mensagemEmailSenha !== '' ? 'alert' : 'status' ?>"> <!-- Explicação DevIN: Declara agrupamento de elementos. Contém dado dinâmico processado pelo PHP. -->
                <?= htmlspecialchars($avisoSenha, ENT_QUOTES, 'UTF-8') ?>
            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
        <?php endif; ?>

        <form method="POST" action="processar.php" class="register-form"> <!-- Explicação DevIN: Declara formulário de entrada de dados. -->
            <!-- `acao` seleciona o fluxo de gravação em processar.php; o token CSRF abaixo permite ao servidor
                 confirmar que o envio veio de um formulário associado à sessão deste navegador. --> <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
            <input type="hidden" name="acao" value="cadastrar_curriculo"> <!-- Explicação DevIN: Declara campo de entrada. -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->

            <h2 class="title-curriculo">Preenchimento de Currículo</h2> <!-- Explicação DevIN: Declara subtítulo de segundo nível. -->

            <?php if ($gerarComIa): ?>
                <div class="resume-ai-consent"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                    <p>Ao continuar, seus dados serão enviados ao Google Gemini para preparar o texto do currículo. Revise todo o resultado antes de usá-lo.</p> <!-- Explicação DevIN: Declara parágrafo. -->
                    <label><input type="checkbox" name="consentimento_ia" value="yes" required> Autorizo o envio dos dados para a geração com IA.</label> <!-- Explicação DevIN: Declara rótulo associado a um campo. A linha reúne múltiplos elementos. -->
                    <input type="hidden" name="gerar_com_ia" value="1"> <!-- Explicação DevIN: Declara campo de entrada. -->
                </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
            <?php endif; ?>

            <div class="form-columns"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->

                <div class="form-column"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->

                    <div class="input-group"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                        <label for="nome_social">Nome Social / Como prefere ser chamado(a):</label> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                        <input type="text" id="nome_social" name="nome_social" placeholder="Ex: Alex Silva" value="<?= htmlspecialchars($cNomeSocial, ENT_QUOTES, 'UTF-8') ?>" required> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

                    <div class="input-group"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                        <label for="grau_de_escolaridade">Grau de Escolaridade:</label> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                        <select id="grau_de_escolaridade" name="grau_de_escolaridade" required> <!-- Explicação DevIN: Declara seleção de opções. -->
                            <option value="">Selecione...</option> <!-- Explicação DevIN: Declara opção selecionável. -->
                            <option value="Ensino Médio Incompleto" <?= $cGrauEscolaridade === 'Ensino Médio Incompleto' ? 'selected' : '' ?>>Ensino Médio Incompleto</option> <!-- Explicação DevIN: Declara opção selecionável. Contém dado dinâmico processado pelo PHP. -->
                            <option value="Ensino Médio Completo" <?= $cGrauEscolaridade === 'Ensino Médio Completo' ? 'selected' : '' ?>>Ensino Médio Completo</option> <!-- Explicação DevIN: Declara opção selecionável. Contém dado dinâmico processado pelo PHP. -->
                            <option value="Ensino Superior Incompleto" <?= $cGrauEscolaridade === 'Ensino Superior Incompleto' ? 'selected' : '' ?>>Ensino Superior Incompleto</option> <!-- Explicação DevIN: Declara opção selecionável. Contém dado dinâmico processado pelo PHP. -->
                            <option value="Ensino Superior Completo" <?= $cGrauEscolaridade === 'Ensino Superior Completo' ? 'selected' : '' ?>>Ensino Superior Completo</option> <!-- Explicação DevIN: Declara opção selecionável. Contém dado dinâmico processado pelo PHP. -->
                            <option value="Pós-graduação / Especialização" <?= $cGrauEscolaridade === 'Pós-graduação / Especialização' ? 'selected' : '' ?>>Pós-graduação / Especialização</option> <!-- Explicação DevIN: Declara opção selecionável. Contém dado dinâmico processado pelo PHP. -->
                        </select> <!-- Explicação DevIN: Fecha elemento select do documento. -->
                    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

                    <div class="input-group"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                        <label for="idiomas">Idiomas:</label> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                        <input type="text" id="idiomas" name="idiomas" placeholder="Ex: Português Nativo, Inglês Intermediário" value="<?= htmlspecialchars($cIdiomas, ENT_QUOTES, 'UTF-8') ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
                    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

                </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

                <div class="form-column"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->

                    <div class="input-group"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                        <label for="cursos">Cursos e Certificações:</label> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                        <textarea id="cursos" name="cursos" placeholder="Ex: Curso de PHP Avançado, HTML5/CSS3, MySQL"><?= htmlspecialchars($cCursos, ENT_QUOTES, 'UTF-8') ?></textarea> <!-- Explicação DevIN: Declara campo de texto multilinha. Contém dado dinâmico processado pelo PHP. -->
                    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

                    <div class="input-group"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                        <label for="experiencia">Experiência Profissional:</label> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                        <textarea id="experiencia" name="experiencia" placeholder="Ex: Desenvolvedor Web na Empresa X (2022 - Atual)"><?= htmlspecialchars($cExperiencia, ENT_QUOTES, 'UTF-8') ?></textarea> <!-- Explicação DevIN: Declara campo de texto multilinha. Contém dado dinâmico processado pelo PHP. -->
                    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

                </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

            <div class="form-footer-action"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                <button type="submit" class="btn-submit"><?= $gerarComIa ? 'Salvar e criar currículo com IA' : 'Finalizar e Salvar Currículo' ?></button> <!-- Explicação DevIN: Declara botão de ação. Contém dado dinâmico processado pelo PHP. -->
                <div class="login-redirect"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                    Deseja sair do sistema? <a href="logout.php">Clique aqui</a> <!-- Explicação DevIN: Declara link de navegação. -->
                </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

        </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->

        <div class="page-footer"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
            © <?= date('Y') ?> <span>DevIN</span>. Todos os direitos reservados. <!-- Explicação DevIN: Declara trecho em linha. Contém dado dinâmico processado pelo PHP. -->
        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

    <div class="right-side"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
        <a href="logout.php" class="btn-top-login">Sair</a> <!-- Explicação DevIN: Declara link de navegação. -->
        <div class="mascot-container"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
            <img src="../img/robocadastro.webp" alt="Mascote DevIN" class="mascot-img"> <!-- Explicação DevIN: Declara imagem na interface. -->
        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

</div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

<script src="../js/site-navigation.js"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. -->
<script src="../js/lenis-global.js?v=<?= filemtime(__DIR__ . '/../js/lenis-global.js') ?>"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
</body> <!-- Explicação DevIN: Fecha elemento body do documento. -->
</html> <!-- Explicação DevIN: Fecha elemento html do documento. -->