<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

require_once __DIR__ . '/middlewares/auth.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/controllers/Security/AdminToken2Enrollment.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

header('Cache-Control: no-store, private'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
header('Referrer-Policy: no-referrer'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
header('X-Content-Type-Options: nosniff'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
header('X-Frame-Options: DENY'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; form-action 'self'; frame-ancestors 'none'; base-uri 'none'"); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.

// Primeiro exige login e perfil ADM: sem sessão válida, o middleware
// redireciona para login; pessoa/empresa não têm acesso ao formulário.
requireWebAuth('adm'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

// Segundo confirma no banco que este ID da sessão pertence a um ADM existente.
// Verificação obrigatória tanto no GET quanto no POST; falha de banco bloqueia.
try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    AdminToken2Enrollment::requireVerifiedAdminAccount(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} catch (DomainException $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    http_response_code(403); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    exit('Acesso restrito a administradores autorizados.'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    error_log('JarvisT admin verification unavailable: ' . get_class($exception)); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    http_response_code(503); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    exit('Não foi possível confirmar a autorização.'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// A configuração trata segredos; nunca permitir POST em HTTP em produção.
// Verifique HTTPS/proxy na hospedagem antes de habilitar esta página.
if (APP_ENV !== 'development' // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    && (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off')) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    http_response_code(503); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    exit('Configuração indisponível.'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    $configured = AdminToken2Enrollment::configured(); // Explicação DevIN: Atribui um valor à variável PHP $configured.
} catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    error_log('JarvisT unavailable: ' . get_class($exception)); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    http_response_code(503); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    exit('Configuração indisponível.'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

$feedback = ''; // Explicação DevIN: Atribui um valor à variável PHP $feedback.
$success = false; // Explicação DevIN: Atribui um valor à variável PHP $success.
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    if (!verifyCsrfToken(is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : null)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        http_response_code(403); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        $feedback = 'Solicitação inválida.'; // Explicação DevIN: Atribui um valor à variável PHP $feedback.
    } elseif ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 205000) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        http_response_code(413); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        $feedback = 'Solicitação inválida.'; // Explicação DevIN: Atribui um valor à variável PHP $feedback.
    } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
        $names = ['token1','current_token2','new_token2','confirm_token2']; // Explicação DevIN: Atribui um valor à variável PHP $names.
        $values = []; // Explicação DevIN: Atribui um valor à variável PHP $values.
        foreach ($names as $key) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            $value = $_POST[$key] ?? ''; // Explicação DevIN: Atribui um valor à variável PHP $value.
            if (!is_string($value)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                http_response_code(400); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
                $feedback = 'Solicitação inválida.'; // Explicação DevIN: Atribui um valor à variável PHP $feedback.
                break; // Explicação DevIN: Controla a continuação ou saída de laço/seleção.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $values[$key] = $value; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if ($feedback === '') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                $success = AdminToken2Enrollment::save( // Explicação DevIN: Atribui um valor à variável PHP $success.
                    $values['token1'], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                    $configured ? $values['current_token2'] : null, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                    $values['new_token2'], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                    $values['confirm_token2'] // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                $feedback = $success ? 'Token 2 salvo.' : 'Não foi possível concluir a verificação.'; // Explicação DevIN: Atribui um valor à variável PHP $feedback.
                if (!$success) http_response_code(400); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            } catch (InvalidArgumentException $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
                http_response_code(400); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
                $feedback = 'Confira o Token 2 e a confirmação. Use pelo menos 12 caracteres.'; // Explicação DevIN: Atribui um valor à variável PHP $feedback.
            } catch (RuntimeException $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
                http_response_code(429); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
                $feedback = 'Verificação temporariamente indisponível.'; // Explicação DevIN: Atribui um valor à variável PHP $feedback.
            } catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
                error_log('JarvisT error: ' . get_class($exception)); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                http_response_code(503); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
                $feedback = 'Não foi possível concluir a verificação.'; // Explicação DevIN: Atribui um valor à variável PHP $feedback.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            unset($values); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            if ($success) $configured = true; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
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
<title>DevIN — JarvisT</title> <!-- Explicação DevIN: Declara elemento de marcação. -->
<style>
*{box-sizing:border-box}
body{margin:0;min-height:100dvh;display:grid;place-items:center;background:#f3f7fc;padding:18px;color:#183953;font-family:Arial,Helvetica,sans-serif} /* Explicação DevIN: Continua uma declaração de seletor ou valor CSS iniciada no trecho anterior. */
main{width:min(100%,440px);border:1px solid #d4e4f1;border-radius:18px;background:#fff;box-shadow:0 14px 40px #0c396421;padding:25px} /* Explicação DevIN: Continua uma declaração de seletor ou valor CSS iniciada no trecho anterior. */
.logo{font-weight:800;color:#1267b7;font-size:24px;margin:0 0 18px} /* Explicação DevIN: Continua uma declaração de seletor ou valor CSS iniciada no trecho anterior. */
h1{font-size:23px;margin:0 0 10px}p{line-height:1.5} /* Explicação DevIN: Continua uma declaração de seletor ou valor CSS iniciada no trecho anterior. */
label{display:block;font-weight:700;font-size:14px;margin:13px 0 6px} /* Explicação DevIN: Continua uma declaração de seletor ou valor CSS iniciada no trecho anterior. */
input{width:100%;min-height:46px;border:1px solid #bcd1e5;border-radius:10px;background:#fff;color:#17374e;font-size:16px;padding:11px} /* Explicação DevIN: Continua uma declaração de seletor ou valor CSS iniciada no trecho anterior. */
input:focus-visible,button:focus-visible,a:focus-visible{outline:2px solid #589fe5;outline-offset:2px} /* Explicação DevIN: Define propriedade visual ou de layout por meio da propriedade CSS input. */
button{width:100%;min-height:46px;border:0;border-radius:10px;color:white;background:#0965ad;font-size:15px;font-weight:700;cursor:pointer;margin-top:19px} /* Explicação DevIN: Continua uma declaração de seletor ou valor CSS iniciada no trecho anterior. */
.feedback{padding:11px;border-radius:9px;background:#eff5fa;color:#274967;font-size:14px} /* Explicação DevIN: Continua uma declaração de seletor ou valor CSS iniciada no trecho anterior. */
.status{font-size:13px;color:#59728a;margin:0 0 14px} /* Explicação DevIN: Continua uma declaração de seletor ou valor CSS iniciada no trecho anterior. */
a{display:inline-block;margin-top:18px;color:#195f9a} /* Explicação DevIN: Continua uma declaração de seletor ou valor CSS iniciada no trecho anterior. */
small{color:#59728a;display:block;margin-top:9px;font-size:12px} /* Explicação DevIN: Continua uma declaração de seletor ou valor CSS iniciada no trecho anterior. */
</style>
</head> <!-- Explicação DevIN: Fecha elemento head do documento. -->
<body><main> <!-- Explicação DevIN: Declara corpo visível da página. A linha reúne múltiplos elementos. -->
<p class="logo">DevIN</p> <!-- Explicação DevIN: Declara parágrafo. -->
<h1>JarvisT</h1> <!-- Explicação DevIN: Declara título principal. -->
<p class="status"><?= $configured ? 'Token 2 cadastrado.' : 'Token 2 não cadastrado.' ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
<?php if ($feedback !== ''): ?>
<p class="feedback" role="status"><?= htmlspecialchars($feedback, ENT_QUOTES, 'UTF-8') ?></p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. -->
<?php endif; ?>
<?php if (!$success): ?>
<form method="post" action="JarvisT.php" autocomplete="off"> <!-- Explicação DevIN: Declara formulário de entrada de dados. -->
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->
<label for="t1">Informe o Token 1</label> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
<input type="password" id="t1" name="token1" autocomplete="off" required> <!-- Explicação DevIN: Declara campo de entrada. -->
<?php if ($configured): ?>
<label for="current">Informe o Token 2</label> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
<input type="password" id="current" name="current_token2" autocomplete="off" required> <!-- Explicação DevIN: Declara campo de entrada. -->
<?php endif; ?>
<label for="new">Defina o Token 2</label> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
<input type="password" id="new" name="new_token2" autocomplete="new-password" minlength="12" required> <!-- Explicação DevIN: Declara campo de entrada. -->
<label for="confirm">Confirme o Token 2</label> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
<input type="password" id="confirm" name="confirm_token2" autocomplete="new-password" minlength="12" required> <!-- Explicação DevIN: Declara campo de entrada. -->
<small>Mínimo de 12 caracteres. Sem prazo de validade.</small> <!-- Explicação DevIN: Declara elemento de marcação. -->
<button type="submit"><?= $configured ? 'Alterar Token 2' : 'Cadastrar Token 2' ?></button> <!-- Explicação DevIN: Declara botão de ação. Contém dado dinâmico processado pelo PHP. -->
</form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
<?php endif; ?>
<a href="adm.php">Voltar ao painel</a> <!-- Explicação DevIN: Declara link de navegação. -->
</main></body></html> <!-- Explicação DevIN: Fecha elemento main do documento. -->
