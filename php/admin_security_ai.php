<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/admin_security_ai.php
 * Finalidade: admin security ai.
 * Domínio: processamento no servidor.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */

require_once __DIR__ . '/middlewares/auth.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/controllers/Security/AdminStepUp.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
header('Cache-Control: no-store, private'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
header('Referrer-Policy: no-referrer'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
header('X-Content-Type-Options: nosniff'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
header('X-Frame-Options: DENY'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; form-action 'self'; frame-ancestors 'none'; base-uri 'none'"); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.

requireWebAuth('adm'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
if (!AdminStepUp::enabled()) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    header('Location: adm.php', true, 303); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
if (APP_ENV !== 'development' && (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off')) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    http_response_code(503); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    exit('Verificação indisponível em conexão não segura.'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$error = ''; // Explicação DevIN: Atribui um valor à variável PHP $error.
try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    if (AdminStepUp::allowed('ai')) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        header('Location: adm.php', true, 303); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if (!verifyCsrfToken(is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : null)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new DomainException('Solicitação inválida.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 70000) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new DomainException('Solicitação muito grande.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $token = $_POST['token'] ?? null; // Explicação DevIN: Atribui um valor à variável PHP $token.
        if (!is_string($token)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new DomainException('Token inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $step = AdminStepUp::nextAiToken(); // Explicação DevIN: Atribui um valor à variável PHP $step.
        $valid = $step === 1 // Explicação DevIN: Atribui um valor à variável PHP $valid.
            ? AdminStepUp::verifyAiFirst($token) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            : AdminStepUp::verifyAiSecond($token); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        unset($token); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        if (!$valid) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $error = 'Token inválido.'; // Explicação DevIN: Atribui um valor à variável PHP $error.
        } elseif (AdminStepUp::allowed('ai')) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            header('Location: adm.php', true, 303); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    $step = AdminStepUp::nextAiToken(); // Explicação DevIN: Atribui um valor à variável PHP $step.
} catch (DomainException $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    http_response_code(403); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    $error = 'Acesso não autorizado ou solicitação inválida.'; // Explicação DevIN: Atribui um valor à variável PHP $error.
    $step = 1; // Explicação DevIN: Atribui um valor à variável PHP $step.
} catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    error_log('JARVIS AI unlock: ' . get_class($exception)); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    http_response_code(503); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    $error = 'Verificação temporariamente indisponível.'; // Explicação DevIN: Atribui um valor à variável PHP $error.
    $step = 1; // Explicação DevIN: Atribui um valor à variável PHP $step.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
?>
<!doctype html> <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
<html lang="pt-BR"> <!-- Explicação DevIN: Declara elemento raiz do documento HTML. -->
<head> <!-- Explicação DevIN: Declara metadados e dependências do documento. -->
<meta charset="utf-8"> <!-- Explicação DevIN: Declara metadado de navegação. -->
<meta name="viewport" content="width=device-width,initial-scale=1"> <!-- Explicação DevIN: Declara metadado de navegação. -->
<meta name="referrer" content="no-referrer"> <!-- Explicação DevIN: Declara metadado de navegação. -->
<title>DevIN — Verificação do JARVIS</title> <!-- Explicação DevIN: Declara elemento de marcação. -->
<style>
*{box-sizing:border-box}
body{margin:0;min-height:100dvh;display:grid;place-items:center;padding:18px;font-family:Arial,Helvetica,sans-serif;background:#f2f7fc;color:#143450} /* Explicação DevIN: Continua uma declaração de seletor ou valor CSS iniciada no trecho anterior. */
main{width:min(100%,430px);background:#fff;border:1px solid #d6e4ef;border-radius:17px;padding:26px;box-shadow:0 16px 44px #123b5720} /* Explicação DevIN: Continua uma declaração de seletor ou valor CSS iniciada no trecho anterior. */
h1{font-size:23px;margin:0 0 10px} /* Explicação DevIN: Continua uma declaração de seletor ou valor CSS iniciada no trecho anterior. */
p{line-height:1.5} /* Explicação DevIN: Continua uma declaração de seletor ou valor CSS iniciada no trecho anterior. */
label{display:block;font-weight:700;margin:20px 0 8px} /* Explicação DevIN: Continua uma declaração de seletor ou valor CSS iniciada no trecho anterior. */
input{width:100%;font-size:17px;min-height:50px;border:1px solid #a9c5de;border-radius:9px;padding:10px} /* Explicação DevIN: Continua uma declaração de seletor ou valor CSS iniciada no trecho anterior. */
button{width:100%;margin-top:17px;min-height:48px;border:0;border-radius:9px;background:#0868b6;color:#fff;font-weight:700;cursor:pointer} /* Explicação DevIN: Continua uma declaração de seletor ou valor CSS iniciada no trecho anterior. */
a{display:inline-block;margin-top:18px;color:#075c98} /* Explicação DevIN: Continua uma declaração de seletor ou valor CSS iniciada no trecho anterior. */
.error{color:#a32121} /* Explicação DevIN: Continua uma declaração de seletor ou valor CSS iniciada no trecho anterior. */
</style>
</head> <!-- Explicação DevIN: Fecha elemento head do documento. -->
<body><main> <!-- Explicação DevIN: Declara corpo visível da página. A linha reúne múltiplos elementos. -->
<h1>Desbloquear JARVIS</h1> <!-- Explicação DevIN: Declara título principal. -->
<p>A confirmação de identidade exige dois passos. Nenhum token será enviado ao modelo de IA.</p> <!-- Explicação DevIN: Declara parágrafo. -->
<?php if ($error !== ''): ?>
<p class="error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
<?php endif; ?>
<?php if (http_response_code() !== 503): ?>
<form method="post" autocomplete="off" action="admin_security_ai.php"> <!-- Explicação DevIN: Declara formulário de entrada de dados. -->
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
<label for="token">Informe o Token <?= $step === 2 ? '2' : '1' ?></label> <!-- Explicação DevIN: Declara rótulo associado a um campo. Contém dado dinâmico processado pelo PHP. -->
<input id="token" name="token" type="password" autofocus required autocomplete="off"> <!-- Explicação DevIN: Declara campo de entrada. -->
<button type="submit">Continuar</button> <!-- Explicação DevIN: Declara botão de ação. -->
</form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
<?php endif; ?>
<a href="JarvisT.php">Cadastrar ou alterar Token 2</a> <!-- Explicação DevIN: Declara link de navegação. -->
<a href="adm.php">Voltar ao painel</a> <!-- Explicação DevIN: Declara link de navegação. -->
</main></body> <!-- Explicação DevIN: Fecha elemento main do documento. -->
</html> <!-- Explicação DevIN: Fecha elemento html do documento. -->
