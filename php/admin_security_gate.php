<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/admin_security_gate.php
 * Finalidade: admin security gate.
 * Domínio: processamento no servidor.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


require_once __DIR__ . '/middlewares/auth.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/controllers/Security/AdminGate.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

header('Cache-Control: no-store, private'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
header('Referrer-Policy: no-referrer'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
header('X-Content-Type-Options: nosniff'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
header('X-Frame-Options: DENY'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.

$admin = requireWebAuth('adm'); // Explicação DevIN: Atribui um valor à variável PHP $admin.
$adminId = (int) $admin['id']; // Explicação DevIN: Atribui um valor à variável PHP $adminId.
if (!AdminStepUp::enabled()) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    http_response_code(503); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    exit('Verificação indisponível.'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$error = ''; // Explicação DevIN: Atribui um valor à variável PHP $error.
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    if (!verifyCsrfToken(is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : null)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        http_response_code(403); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        $error = 'Solicitação inválida.'; // Explicação DevIN: Atribui um valor à variável PHP $error.
    } elseif (isset($_SESSION['admin_stepup_pending']) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        && (!is_string($_POST['pending_nonce'] ?? null) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            || !hash_equals((string) ($_SESSION['admin_stepup_pending']['nonce'] ?? ''), $_POST['pending_nonce']))) { // Explicação DevIN: Executa operação criptográfica de senha ou comparação protegida.
        http_response_code(403); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        $error = 'Solicitação inválida.'; // Explicação DevIN: Atribui um valor à variável PHP $error.
    } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            $token = is_string($_POST['token'] ?? null) ? $_POST['token'] : ''; // Explicação DevIN: Atribui um valor à variável PHP $token.
            if (AdminStepUp::verifySite($token)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                $pending = AdminGate::consumePending($adminId); // Explicação DevIN: Atribui um valor à variável PHP $pending.
                if ($pending !== null) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    // POST original foi verificado e pertence à sessão corrente.
                    $_GET['pagina'] = $pending['page']; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    $_GET['categoria'] = $pending['category']; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    $_POST = $pending['post']; // Explicação DevIN: Atribui um valor à variável PHP $_POST.
                    $_FILES = []; // Explicação DevIN: Atribui um valor à variável PHP $_FILES.
                    $_SERVER['REQUEST_METHOD'] = 'POST'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    unset($token, $pending); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                    require __DIR__ . '/adm.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
                    exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                $_SESSION['profile_error'] = 'Autorização concluída. Envie o formulário novamente.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                header('Location: adm.php', true, 303); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
                exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $error = 'Token inválido.'; // Explicação DevIN: Atribui um valor à variável PHP $error.
        } catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            error_log('ADM gate error: ' . get_class($exception)); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            $error = 'Verificação temporariamente indisponível.'; // Explicação DevIN: Atribui um valor à variável PHP $error.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
?>
<!doctype html> <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
<html lang="pt-BR"> <!-- Explicação DevIN: Declara elemento raiz do documento HTML. -->
<head> <!-- Explicação DevIN: Declara metadados e dependências do documento. -->
<meta charset="utf-8"> <!-- Explicação DevIN: Declara metadado de navegação. -->
<meta name="viewport" content="width=device-width,initial-scale=1"> <!-- Explicação DevIN: Declara metadado de navegação. -->
<meta name="referrer" content="no-referrer"> <!-- Explicação DevIN: Declara metadado de navegação. -->
<title>DevIN — Verificação</title> <!-- Explicação DevIN: Declara elemento de marcação. -->
<link rel="icon" type="image/svg+xml" href="../img/favicon.svg"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. -->
<style>
*{box-sizing:border-box}body{margin:0;min-height:100dvh;display:grid;place-items:center;padding:20px;background:#f3f7fc;color:#17324d;font-family:Arial,Helvetica,sans-serif}
main{width:min(100%,390px);background:#fff;border:1px solid #dce7f3;border-radius:18px;padding:28px;box-shadow:0 12px 45px #093b7223} /* Explicação DevIN: Continua uma declaração de seletor ou valor CSS iniciada no trecho anterior. */
.brand{margin:0 0 20px;color:#0966bb;font-weight:800;font-size:22px}h1{font-size:22px;margin:0 0 18px} /* Explicação DevIN: Continua uma declaração de seletor ou valor CSS iniciada no trecho anterior. */
label{display:block;font-weight:600;font-size:15px;margin-bottom:8px}input{width:100%;min-height:48px;padding:12px;font-size:16px;border:1px solid #aac4df;border-radius:10px} /* Explicação DevIN: Continua uma declaração de seletor ou valor CSS iniciada no trecho anterior. */
button{width:100%;min-height:48px;border:0;border-radius:10px;background:#0768bb;color:#fff;font-size:15px;font-weight:700;cursor:pointer;margin-top:15px} /* Explicação DevIN: Continua uma declaração de seletor ou valor CSS iniciada no trecho anterior. */
a{display:inline-block;margin-top:20px;color:#24567d} .error{color:#a22929;line-height:1.5} /* Explicação DevIN: Continua uma declaração de seletor ou valor CSS iniciada no trecho anterior. */
</style>
</head> <!-- Explicação DevIN: Fecha elemento head do documento. -->
<body><main> <!-- Explicação DevIN: Declara corpo visível da página. A linha reúne múltiplos elementos. -->
<p class="brand">DevIN</p> <!-- Explicação DevIN: Declara parágrafo. -->
<h1>Verificação</h1> <!-- Explicação DevIN: Declara título principal. -->
<?php if ($error !== ''): ?><p class="error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
<form method="post" autocomplete="off" action="admin_security_gate.php"> <!-- Explicação DevIN: Declara formulário de entrada de dados. -->
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
<?php if (isset($_SESSION['admin_stepup_pending']['nonce'])): ?>
<input type="hidden" name="pending_nonce" value="<?= htmlspecialchars((string) $_SESSION['admin_stepup_pending']['nonce'], ENT_QUOTES, 'UTF-8') ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
<?php endif; ?>
<label for="token">Informe o Token 2</label> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
<input id="token" name="token" type="password" required autocomplete="off" autofocus> <!-- Explicação DevIN: Declara campo de entrada. -->
<button type="submit">Continuar</button> <!-- Explicação DevIN: Declara botão de ação. -->
</form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
<a href="JarvisT.php">Configurar Token 2</a> · <a href="adm.php">Voltar ao painel</a> <!-- Explicação DevIN: Declara link de navegação. A linha reúne múltiplos elementos. -->
</main></body></html> <!-- Explicação DevIN: Fecha elemento main do documento. -->
