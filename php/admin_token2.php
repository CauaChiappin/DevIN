<?php
declare(strict_types=1);

require_once __DIR__ . '/middlewares/auth.php';
require_once __DIR__ . '/controllers/Security/AdminToken2Enrollment.php';

header('Cache-Control: no-store, private');
header('Referrer-Policy: no-referrer');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; form-action 'self'; frame-ancestors 'none'; base-uri 'none'");

$account = requireWebAuth('adm');
$adminId = (int) $account['id'];

// Esta página trata segredos. A hospedagem precisa fornecer HTTPS verdadeiro.
if (APP_ENV !== 'development' && (
    !str_starts_with(APP_BASE_URL, 'https://')
    || empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off'
)) {
    http_response_code(503);
    exit('Verificação indisponível. Contate o administrador do sistema.');
}

$feedback = '';
$success = false;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!verifyCsrfToken(is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : null)) {
        http_response_code(403);
        $feedback = 'Solicitação inválida.';
    } elseif ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 3200) {
        http_response_code(413);
        $feedback = 'Solicitação inválida.';
    } else {
        $fields = ['token1', 'activation_code', 'new_token2', 'confirm_token2'];
        $values = [];
        foreach ($fields as $key) {
            $value = $_POST[$key] ?? null;
            if (!is_string($value) || strlen($value) > 512) {
                http_response_code(400);
                $feedback = 'Solicitação inválida.';
                break;
            }
            $values[$key] = $value;
        }
        if ($feedback === '') {
            try {
                $success = AdminToken2Enrollment::enroll(
                    $adminId, $values['token1'], $values['activation_code'],
                    $values['new_token2'], $values['confirm_token2']
                );
                $feedback = $success ? 'Token 2 configurado.' : 'Não foi possível concluir a verificação.';
                if (!$success) http_response_code(400);
            } catch (InvalidArgumentException $exception) {
                http_response_code(400);
                $feedback = 'O Token 2 precisa de 14 a 72 caracteres e confirmação idêntica.';
            } catch (RuntimeException $exception) {
                http_response_code(429);
                $feedback = 'Verificação temporariamente indisponível.';
            } catch (Throwable $exception) {
                error_log('ADM Token 2 enrollment: ' . get_class($exception));
                http_response_code(503);
                $feedback = 'Não foi possível concluir a verificação.';
            }
            unset($values);
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
<title>DevIN — Configurar Token 2</title>
<link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
<style>
*{box-sizing:border-box}body{margin:0;min-height:100dvh;display:grid;place-items:center;background:#f3f7fc;padding:18px;color:#183953;font-family:Arial,Helvetica,sans-serif}
main{width:min(100%,440px);border:1px solid #d4e4f1;border-radius:18px;background:#fff;box-shadow:0 14px 40px #0c396421;padding:25px}
.logo{font-weight:800;color:#1267b7;font-size:24px;margin:0 0 18px}h1{font-size:23px;margin:0 0 18px}
label{display:block;font-weight:700;font-size:14px;margin:13px 0 6px}
input{width:100%;min-height:46px;border:1px solid #bcd1e5;border-radius:10px;background:#fff;color:#17374e;font-size:16px;padding:11px}
input:focus-visible,button:focus-visible{outline:2px solid #589fe5;outline-offset:2px}
button{width:100%;min-height:46px;border:0;border-radius:10px;color:white;background:#0965ad;font-size:15px;font-weight:700;cursor:pointer;margin-top:19px}
.feedback{padding:11px;border-radius:9px;background:#eff5fa;color:#274967;font-size:14px}
a{display:inline-block;margin-top:18px;color:#195f9a}small{color:#59728a;display:block;margin-top:9px;font-size:12px}
</style>
</head>
<body>
<main>
<p class="logo">DevIN</p>
<h1>Configurar Token 2</h1>
<?php if ($feedback !== ''): ?>
<p class="feedback" role="status"><?= htmlspecialchars($feedback, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>
<?php if (!$success): ?>
<form method="post" autocomplete="off" action="admin_token2.php">
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
<label for="t1">Informe o Token 1</label>
<input id="t1" name="token1" type="password" autocomplete="off" maxlength="512" required>
<label for="act">Informe o código de ativação</label>
<input id="act" name="activation_code" type="password" autocomplete="off" maxlength="128" required>
<label for="t2">Defina o Token 2</label>
<input id="t2" name="new_token2" type="password" autocomplete="new-password" minlength="14" maxlength="72" required>
<label for="t2confirm">Confirme o Token 2</label>
<input id="t2confirm" name="confirm_token2" type="password" autocomplete="new-password" minlength="14" maxlength="72" required>
<small>O Token 2 deve conter pelo menos 14 caracteres.</small>
<button type="submit">Salvar Token 2</button>
</form>
<?php endif; ?>
<a href="adm.php">Voltar ao painel</a>
</main>
</body>
</html>
