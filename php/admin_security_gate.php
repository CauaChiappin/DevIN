<?php
declare(strict_types=1);

require_once __DIR__ . '/middlewares/auth.php';
require_once __DIR__ . '/controllers/Security/AdminGate.php';

header('Cache-Control: no-store, private');
header('Referrer-Policy: no-referrer');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

$admin = requireWebAuth('adm');
$adminId = (int) $admin['id'];
if (!AdminStepUp::enabled()) {
    http_response_code(503);
    exit('Verificação indisponível.');
}
$error = '';
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!verifyCsrfToken(is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : null)) {
        http_response_code(403);
        $error = 'Solicitação inválida.';
    } elseif (isset($_SESSION['admin_stepup_pending'])
        && (!is_string($_POST['pending_nonce'] ?? null)
            || !hash_equals((string) ($_SESSION['admin_stepup_pending']['nonce'] ?? ''), $_POST['pending_nonce']))) {
        http_response_code(403);
        $error = 'Solicitação inválida.';
    } else {
        try {
            $token = is_string($_POST['token'] ?? null) ? $_POST['token'] : '';
            if (AdminStepUp::verifySite($token)) {
                $pending = AdminGate::consumePending($adminId);
                if ($pending !== null) {
                    // POST original foi verificado e pertence à sessão corrente.
                    $_GET['pagina'] = $pending['page'];
                    $_GET['categoria'] = $pending['category'];
                    $_POST = $pending['post'];
                    $_FILES = [];
                    $_SERVER['REQUEST_METHOD'] = 'POST';
                    unset($token, $pending);
                    require __DIR__ . '/adm.php';
                    exit;
                }
                $_SESSION['profile_error'] = 'Autorização concluída. Envie o formulário novamente.';
                header('Location: adm.php', true, 303);
                exit;
            }
            $error = 'Token inválido.';
        } catch (Throwable $exception) {
            error_log('ADM gate error: ' . get_class($exception));
            $error = 'Verificação temporariamente indisponível.';
        }
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="referrer" content="no-referrer">
<title>DevIN — Verificação</title>
<link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
<style>
*{box-sizing:border-box}body{margin:0;min-height:100dvh;display:grid;place-items:center;padding:20px;background:#f3f7fc;color:#17324d;font-family:Arial,Helvetica,sans-serif}
main{width:min(100%,390px);background:#fff;border:1px solid #dce7f3;border-radius:18px;padding:28px;box-shadow:0 12px 45px #093b7223}
.brand{margin:0 0 20px;color:#0966bb;font-weight:800;font-size:22px}h1{font-size:22px;margin:0 0 18px}
label{display:block;font-weight:600;font-size:15px;margin-bottom:8px}input{width:100%;min-height:48px;padding:12px;font-size:16px;border:1px solid #aac4df;border-radius:10px}
button{width:100%;min-height:48px;border:0;border-radius:10px;background:#0768bb;color:#fff;font-size:15px;font-weight:700;cursor:pointer;margin-top:15px}
a{display:inline-block;margin-top:20px;color:#24567d} .error{color:#a22929;line-height:1.5}
</style>
</head>
<body><main>
<p class="brand">DevIN</p>
<h1>Verificação</h1>
<?php if ($error !== ''): ?><p class="error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
<form method="post" autocomplete="off" action="admin_security_gate.php">
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
<?php if (isset($_SESSION['admin_stepup_pending']['nonce'])): ?>
<input type="hidden" name="pending_nonce" value="<?= htmlspecialchars((string) $_SESSION['admin_stepup_pending']['nonce'], ENT_QUOTES, 'UTF-8') ?>">
<?php endif; ?>
<label for="token">Informe o Token 2</label>
<input id="token" name="token" type="password" maxlength="512" required autocomplete="off" autofocus>
<button type="submit">Continuar</button>
</form>
<a href="adm.php">Voltar ao painel</a>
</main></body></html>
