<?php
declare(strict_types=1);
require_once __DIR__ . '/middlewares/auth.php';
require_once __DIR__ . '/controllers/Security/AdminStepUp.php';
header('Cache-Control: no-store, private');
header('Referrer-Policy: no-referrer');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; form-action 'self'; frame-ancestors 'none'; base-uri 'none'");

requireWebAuth('adm');
if (!AdminStepUp::enabled()) {
    header('Location: adm.php', true, 303);
    exit;
}
if (APP_ENV !== 'development' && (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off')) {
    http_response_code(503);
    exit('Verificação indisponível em conexão não segura.');
}
$error = '';
try {
    if (AdminStepUp::allowed('ai')) {
        header('Location: adm.php', true, 303);
        exit;
    }
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
        if (!verifyCsrfToken(is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : null)) {
            throw new DomainException('Solicitação inválida.');
        }
        if ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 70000) {
            throw new DomainException('Solicitação muito grande.');
        }
        $token = $_POST['token'] ?? null;
        if (!is_string($token)) {
            throw new DomainException('Token inválido.');
        }
        $step = AdminStepUp::nextAiToken();
        $valid = $step === 1
            ? AdminStepUp::verifyAiFirst($token)
            : AdminStepUp::verifyAiSecond($token);
        unset($token);
        if (!$valid) {
            $error = 'Token inválido.';
        } elseif (AdminStepUp::allowed('ai')) {
            header('Location: adm.php', true, 303);
            exit;
        }
    }
    $step = AdminStepUp::nextAiToken();
} catch (DomainException $exception) {
    http_response_code(403);
    $error = 'Acesso não autorizado ou solicitação inválida.';
    $step = 1;
} catch (Throwable $exception) {
    error_log('JARVIS AI unlock: ' . get_class($exception));
    http_response_code(503);
    $error = 'Verificação temporariamente indisponível.';
    $step = 1;
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="referrer" content="no-referrer">
<title>DevIN — Verificação do JARVIS</title>
<style>
*{box-sizing:border-box}
body{margin:0;min-height:100dvh;display:grid;place-items:center;padding:18px;font-family:Arial,Helvetica,sans-serif;background:#f2f7fc;color:#143450}
main{width:min(100%,430px);background:#fff;border:1px solid #d6e4ef;border-radius:17px;padding:26px;box-shadow:0 16px 44px #123b5720}
h1{font-size:23px;margin:0 0 10px}
p{line-height:1.5}
label{display:block;font-weight:700;margin:20px 0 8px}
input{width:100%;font-size:17px;min-height:50px;border:1px solid #a9c5de;border-radius:9px;padding:10px}
button{width:100%;margin-top:17px;min-height:48px;border:0;border-radius:9px;background:#0868b6;color:#fff;font-weight:700;cursor:pointer}
a{display:inline-block;margin-top:18px;color:#075c98}
.error{color:#a32121}
</style>
</head>
<body><main>
<h1>Desbloquear JARVIS</h1>
<p>A confirmação de identidade exige dois passos. Nenhum token será enviado ao modelo de IA.</p>
<?php if ($error !== ''): ?>
<p class="error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>
<?php if (http_response_code() !== 503): ?>
<form method="post" autocomplete="off" action="admin_security_ai.php">
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
<label for="token">Informe o Token <?= $step === 2 ? '2' : '1' ?></label>
<input id="token" name="token" type="password" autofocus required autocomplete="off">
<button type="submit">Continuar</button>
</form>
<?php endif; ?>
<a href="JarvisT.php">Cadastrar ou alterar Token 2</a>
<a href="adm.php">Voltar ao painel</a>
</main></body>
</html>
