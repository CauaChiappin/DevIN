<?php
declare(strict_types=1);

require_once __DIR__ . '/middlewares/auth.php';
require_once __DIR__ . '/controllers/Security/AdminToken2Enrollment.php';

header('Cache-Control: no-store, private');
header('Referrer-Policy: no-referrer');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; form-action 'self'; frame-ancestors 'none'; base-uri 'none'");

requireWebAuth('adm');

// A configuração trata segredos; nunca permitir POST em HTTP em produção.
// Verifique HTTPS/proxy na hospedagem antes de habilitar esta página.
if (APP_ENV !== 'development'
    && (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off')) {
    http_response_code(503);
    exit('Configuração indisponível.');
}

try {
    $configured = AdminToken2Enrollment::configured();
} catch (Throwable $exception) {
    error_log('JarvisT unavailable: ' . get_class($exception));
    http_response_code(503);
    exit('Configuração indisponível.');
}

$feedback = '';
$success = false;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!verifyCsrfToken(is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : null)) {
        http_response_code(403);
        $feedback = 'Solicitação inválida.';
    } elseif ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 205000) {
        http_response_code(413);
        $feedback = 'Solicitação inválida.';
    } else {
        $names = ['token1','current_token2','new_token2','confirm_token2'];
        $values = [];
        foreach ($names as $key) {
            $value = $_POST[$key] ?? '';
            if (!is_string($value)) {
                http_response_code(400);
                $feedback = 'Solicitação inválida.';
                break;
            }
            $values[$key] = $value;
        }

        if ($feedback === '') {
            try {
                $success = AdminToken2Enrollment::save(
                    $values['token1'],
                    $configured ? $values['current_token2'] : null,
                    $values['new_token2'],
                    $values['confirm_token2']
                );
                $feedback = $success ? 'Token 2 salvo.' : 'Não foi possível concluir a verificação.';
                if (!$success) http_response_code(400);
            } catch (InvalidArgumentException $exception) {
                http_response_code(400);
                $feedback = 'Confira o Token 2 e a confirmação. Use pelo menos 12 caracteres.';
            } catch (RuntimeException $exception) {
                http_response_code(429);
                $feedback = 'Verificação temporariamente indisponível.';
            } catch (Throwable $exception) {
                error_log('JarvisT error: ' . get_class($exception));
                http_response_code(503);
                $feedback = 'Não foi possível concluir a verificação.';
            }
            unset($values);
            if ($success) $configured = true;
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
<title>DevIN — JarvisT</title>
<style>
*{box-sizing:border-box}
body{margin:0;min-height:100dvh;display:grid;place-items:center;background:#f3f7fc;padding:18px;color:#183953;font-family:Arial,Helvetica,sans-serif}
main{width:min(100%,440px);border:1px solid #d4e4f1;border-radius:18px;background:#fff;box-shadow:0 14px 40px #0c396421;padding:25px}
.logo{font-weight:800;color:#1267b7;font-size:24px;margin:0 0 18px}
h1{font-size:23px;margin:0 0 10px}p{line-height:1.5}
label{display:block;font-weight:700;font-size:14px;margin:13px 0 6px}
input{width:100%;min-height:46px;border:1px solid #bcd1e5;border-radius:10px;background:#fff;color:#17374e;font-size:16px;padding:11px}
input:focus-visible,button:focus-visible,a:focus-visible{outline:2px solid #589fe5;outline-offset:2px}
button{width:100%;min-height:46px;border:0;border-radius:10px;color:white;background:#0965ad;font-size:15px;font-weight:700;cursor:pointer;margin-top:19px}
.feedback{padding:11px;border-radius:9px;background:#eff5fa;color:#274967;font-size:14px}
.status{font-size:13px;color:#59728a;margin:0 0 14px}
a{display:inline-block;margin-top:18px;color:#195f9a}
small{color:#59728a;display:block;margin-top:9px;font-size:12px}
</style>
</head>
<body><main>
<p class="logo">DevIN</p>
<h1>JarvisT</h1>
<p class="status"><?= $configured ? 'Token 2 cadastrado.' : 'Token 2 não cadastrado.' ?></p>
<?php if ($feedback !== ''): ?>
<p class="feedback" role="status"><?= htmlspecialchars($feedback, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>
<?php if (!$success): ?>
<form method="post" action="JarvisT.php" autocomplete="off">
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>">
<label for="t1">Informe o Token 1</label>
<input type="password" id="t1" name="token1" autocomplete="off" required>
<?php if ($configured): ?>
<label for="current">Informe o Token 2</label>
<input type="password" id="current" name="current_token2" autocomplete="off" required>
<?php endif; ?>
<label for="new">Defina o Token 2</label>
<input type="password" id="new" name="new_token2" autocomplete="new-password" minlength="12" required>
<label for="confirm">Confirme o Token 2</label>
<input type="password" id="confirm" name="confirm_token2" autocomplete="new-password" minlength="12" required>
<small>Mínimo de 12 caracteres. Sem prazo de validade.</small>
<button type="submit"><?= $configured ? 'Alterar Token 2' : 'Cadastrar Token 2' ?></button>
</form>
<?php endif; ?>
<a href="adm.php">Voltar ao painel</a>
</main></body></html>
