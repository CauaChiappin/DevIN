<?php
declare(strict_types=1);

if (getenv('LGPD_TEST_DB') !== 'devin_lgpd_test') {
    throw new RuntimeException('Somente banco descartável de CI.');
}
$_ENV['APP_ENV'] = 'development';
$_ENV['DEVIN_JWT_SECRET'] = 'not-for-real-use-token-test-secret';
$_ENV['DB_HOST'] = getenv('LGPD_TEST_HOST') ?: '127.0.0.1';
$_ENV['DB_PORT'] = '3306';
$_ENV['DB_NAME'] = 'devin_lgpd_test';
$_ENV['DB_USER'] = getenv('LGPD_TEST_USER') ?: 'root';
$_ENV['DB_PASS'] = getenv('LGPD_TEST_PASSWORD') ?: '';
$_ENV['DEVIN_ADMIN_STEPUP_ENABLED'] = '1';
$_SERVER['REMOTE_ADDR'] = '192.0.2.25';

require_once dirname(__DIR__) . '/php/controllers/Security/AdminStepUp.php';
require_once dirname(__DIR__) . '/php/controllers/Security/AdminGate.php';

function stepCheck(bool $condition, string $label): void {
    if (!$condition) throw new RuntimeException('ADM step-up: ' . $label);
}
$db = getDatabaseConnection();
$login = 'login@' . bin2hex(random_bytes(8));
$loginHash = password_hash($login, PASSWORD_DEFAULT);
$stmt = $db->prepare(
    'INSERT INTO administrador (id_administrador,senha_hash,token2_hash,token2_version) '
    . 'VALUES (?,?,?,1)'
);
$keys = [];
foreach ([301,302,303,304] as $id) {
    $keys[$id] = 'secondary-' . $id . '-@' . bin2hex(random_bytes(12));
    $hash2 = AdminToken2Hasher::create($keys[$id]);
    $stmt->bind_param('iss', $id, $loginHash, $hash2);
    $stmt->execute();
}
$stmt->close();
$db->close();

startSecureSession();
$_SESSION['logado'] = true;
$_SESSION['usuario_tipo'] = 'adm';
$_SESSION['usuario_id'] = 301;
$_SESSION['auth_time'] = time();
$_SESSION['last_activity'] = time();

stepCheck(!AdminStepUp::allowed('site') && !AdminStepUp::allowed('ai'), 'sem sessão liberada');
stepCheck(AdminStepUp::nextAiToken() === 1, 'IA começa pelo Token 1');
stepCheck(!AdminStepUp::verifyAiSecond($keys[301]), 'não permite segunda etapa antes da primeira');
stepCheck(!AdminStepUp::verifyAiFirst('senha-errada'), 'rejeita Token 1 inválido');
stepCheck(AdminStepUp::verifyAiFirst($login), 'aceita Token 1');
stepCheck(AdminStepUp::nextAiToken() === 2, 'solicita Token 2');
stepCheck(!AdminStepUp::verifyAiSecond($keys[302]), 'não aceita Token 2 de outro ADM');
stepCheck(AdminStepUp::verifyAiSecond($keys[301]), 'aceita Token 2 próprio');
stepCheck(AdminStepUp::allowed('ai') && !AdminStepUp::allowed('site'), 'escopos independentes');
stepCheck(AdminStepUp::verifySite($keys[301]), 'libera ações do site');
stepCheck(AdminStepUp::allowed('site'), 'estado persiste na sessão');
$grant = $_SESSION['admin_stepup_grant_site'];
$_SESSION['admin_stepup_grant_site']['issued_at'] = time()-28801;
stepCheck(!AdminStepUp::allowed('site'), 'o grant expira em 8 horas sem expirar a senha');
$_SESSION['admin_stepup_grant_site'] = $grant;
$_SESSION['admin_stepup_grant_site']['used_at'] = time()-1801;
stepCheck(!AdminStepUp::allowed('site'), 'o grant expira após inatividade');

$db = getDatabaseConnection();
$db->query('UPDATE administrador SET token2_version = token2_version + 1 WHERE id_administrador = 301');
$db->close();
stepCheck(!AdminStepUp::allowed('ai'), 'rotação revoga grant da IA');
stepCheck(AdminStepUp::verifySite($keys[301]), 'senha Token 2 continua válida após expiração do grant');

$db = getDatabaseConnection();
$newLoginHash = password_hash('nova-senha-da-conta-de-teste', PASSWORD_DEFAULT);
$stmt = $db->prepare('UPDATE administrador SET senha_hash = ? WHERE id_administrador = 301');
$stmt->bind_param('s', $newLoginHash);
$stmt->execute();
$stmt->close();
$db->close();
stepCheck(!AdminStepUp::allowed('site'), 'troca de senha de login revoga grant');
$_SESSION['usuario_id'] = 302;
stepCheck(!AdminStepUp::allowed('site') && !AdminStepUp::allowed('ai'), 'grants não passam a outro ADM');
$_SESSION['usuario_id'] = 301;
AdminStepUp::clear();
stepCheck(!isset($_SESSION['admin_stepup_grant_ai']), 'limpeza remove grant da IA');

$csrf = csrfToken();
$_SESSION['admin_stepup_pending'] = [
    'admin_id' => 301, 'nonce' => bin2hex(random_bytes(16)),
    'created_at' => time(), 'post' => ['action'=>'delete_account','csrf_token'=>$csrf],
    'page'=>'perfil', 'category'=>'vagas',
];
stepCheck(AdminGate::consumePending(302) === null, 'pendência não pode atravessar contas');
$_SESSION['admin_stepup_pending'] = [
    'admin_id' => 301, 'nonce' => bin2hex(random_bytes(16)),
    'created_at' => time(), 'post' => ['action'=>'delete_account','csrf_token'=>$csrf],
    'page'=>'perfil', 'category'=>'vagas',
];
stepCheck(AdminGate::consumePending(301) !== null, 'ação própria pode ser retomada');
stepCheck(AdminGate::consumePending(301) === null, 'ação de uso único');
$_SESSION['admin_stepup_pending'] = [
    'admin_id' => 301, 'nonce' => bin2hex(random_bytes(16)),
    'created_at' => time()-301, 'post' => ['action'=>'delete_account','csrf_token'=>$csrf],
    'page'=>'perfil', 'category'=>'vagas',
];
stepCheck(AdminGate::consumePending(301) === null, 'pendência expirada negada');
session_destroy();
echo "ADM step-up MySQL: OK\n";
