<?php
declare(strict_types=1);

/** Nunca executar em banco real: exigência explícita de banco descartável. */
if (getenv('LGPD_TEST_DB') !== 'devin_lgpd_test') {
    throw new RuntimeException('Ambiente de teste inválido.');
}
$_ENV['APP_ENV'] = 'development';
$_ENV['DEVIN_JWT_SECRET'] = 'test-only-secret-which-is-not-for-production';
$_ENV['DB_HOST'] = getenv('LGPD_TEST_HOST') ?: '127.0.0.1';
$_ENV['DB_PORT'] = '3306';
$_ENV['DB_NAME'] = 'devin_lgpd_test';
$_ENV['DB_USER'] = getenv('LGPD_TEST_USER') ?: 'root';
$_ENV['DB_PASS'] = getenv('LGPD_TEST_PASSWORD') ?: '';
$_ENV['DEVIN_ADMIN_STEPUP_ENABLED'] = '1';
$_SERVER['REMOTE_ADDR'] = '192.0.2.25';

require_once dirname(__DIR__) . '/php/controllers/Security/AdminStepUp.php';
require_once dirname(__DIR__) . '/php/controllers/Security/AdminGate.php';
require_once dirname(__DIR__) . '/php/controllers/Security/AdminToken2Enrollment.php';
function ensure(bool $value, string $message): void {
    if (!$value) throw new RuntimeException('Teste step-up: ' . $message);
}
$db = getDatabaseConnection();
$db->query("ALTER TABLE administrador ADD COLUMN senha_hash VARCHAR(255) NULL");
$original = 'LoginExample-Strong@' . bin2hex(random_bytes(5));
$hash = password_hash($original, PASSWORD_DEFAULT);
$insert = $db->prepare('INSERT INTO administrador (id_administrador,senha_hash) VALUES (?,?) ON DUPLICATE KEY UPDATE senha_hash=VALUES(senha_hash)');
for ($id=301; $id<=304; $id++) {
    $insert->bind_param('is',$id,$hash);
    $insert->execute();
}
$insert->close();
$keys = [];
$save = $db->prepare('INSERT INTO admin_security_credentials (admin_id,token2_hash) VALUES (?,?)');
for ($id=301; $id<=304; $id++) {
    $keys[$id] = 'Distinct-Secondary-' . $id . '-@' . bin2hex(random_bytes(12));
    $hash2 = password_hash($keys[$id], PASSWORD_DEFAULT);
    $save->bind_param('is',$id,$hash2);
    $save->execute();
}
$save->close();
$db->close();

startSecureSession();
$_SESSION['logado'] = true;
$_SESSION['usuario_tipo'] = 'adm';
$_SESSION['usuario_id'] = 301;
$_SESSION['auth_time'] = time();
$_SESSION['last_activity'] = time();
ensure(!AdminStepUp::allowed('site') && !AdminStepUp::allowed('ai'), 'sem liberação inicial');
ensure(AdminStepUp::nextAiToken() === 1, 'ordem etapa 1');
ensure(!AdminStepUp::verifyAiSecond($keys[301]), 'não permite pular etapa 1');
ensure(!AdminStepUp::verifyAiFirst('incorreto'), 'rejeitar Token 1 errado');
ensure(AdminStepUp::verifyAiFirst($original), 'validar Token 1');
ensure(AdminStepUp::nextAiToken() === 2, 'desbloqueia próxima etapa');
ensure(!AdminStepUp::verifyAiSecond($keys[302]), 'chave de outro ADM não aceita');
ensure(AdminStepUp::verifyAiSecond($keys[301]), 'segundo passo correto');
ensure(AdminStepUp::allowed('ai'), 'IA autorizada somente depois de ambos');
ensure(!AdminStepUp::allowed('site'), 'autorizações separadas');
ensure(AdminStepUp::verifySite($keys[301]), 'site libera com Token 2');
ensure(AdminStepUp::allowed('site'), 'site persiste na sessão');
$site = $_SESSION['admin_stepup_grant_site'];
$_SESSION['admin_stepup_grant_site']['issued_at'] = time() - 28801;
ensure(!AdminStepUp::allowed('site'), 'expiração absoluta');
$_SESSION['admin_stepup_grant_site'] = $site;
$_SESSION['admin_stepup_grant_site']['used_at'] = time() - 1801;
ensure(!AdminStepUp::allowed('site'), 'expiração por inatividade');

// Rotação invalida os grants existentes sem tocar em outras contas.
$db = getDatabaseConnection();
$db->query("UPDATE admin_security_credentials SET credential_version = credential_version+1 WHERE admin_id=301");
$db->close();
ensure(!AdminStepUp::allowed('ai'), 'rotação revoga grant IA');
ensure(!AdminStepUp::allowed('site'), 'rotação revoga grant site');
ensure(AdminStepUp::verifySite($keys[301]), 'revalidar após rotação');
$db = getDatabaseConnection();
$changed = password_hash('nova-credencial-local-de-testes', PASSWORD_DEFAULT);
$stmt = $db->prepare('UPDATE administrador SET senha_hash = ? WHERE id_administrador = 301');
$stmt->bind_param('s',$changed);
$stmt->execute();
$stmt->close();
$db->close();
ensure(!AdminStepUp::allowed('site'), 'troca de senha primária revoga grant');
$_SESSION['usuario_id'] = 302;
ensure(!AdminStepUp::allowed('ai') && !AdminStepUp::allowed('site'), 'isolamento por ADM');
$_SESSION['usuario_id'] = 301;
AdminStepUp::clear();
ensure(!isset($_SESSION['admin_stepup_grant_ai']), 'logout invalida grant local');
$token = csrfToken();
$_SESSION['admin_stepup_pending'] = [
    'admin_id' => 301, 'nonce' => bin2hex(random_bytes(16)),
    'created_at' => time(), 'post' => ['action'=>'delete_account','csrf_token'=>$token],
    'page'=>'perfil', 'category'=>'vagas',
];
ensure(AdminGate::consumePending(302) === null, 'formulário pendente não cruza contas');
$_SESSION['admin_stepup_pending'] = [
    'admin_id' => 301, 'nonce' => bin2hex(random_bytes(16)),
    'created_at' => time(), 'post' => ['action'=>'delete_account','csrf_token'=>$token],
    'page'=>'perfil', 'category'=>'vagas',
];
ensure(AdminGate::consumePending(301) !== null, 'consome formulário válido');
ensure(AdminGate::consumePending(301) === null, 'formulário de uso único');
$_SESSION['admin_stepup_pending'] = [
    'admin_id' => 301, 'nonce' => bin2hex(random_bytes(16)),
    'created_at' => time()-301, 'post' => ['action'=>'delete_account','csrf_token'=>$token],
    'page'=>'perfil', 'category'=>'vagas',
];
ensure(AdminGate::consumePending(301) === null, 'formulário expirado');
// Cadastro individual de Token 2 no banco descartável.
$db = getDatabaseConnection();
$code = rtrim(strtr(base64_encode(random_bytes(24)), '+/', '-_'), '=');
$codeHash = password_hash($code, PASSWORD_DEFAULT);
$stmt = $db->prepare(
    'INSERT INTO admin_security_enrollments (admin_id,activation_hash,expires_at) '
    . 'VALUES (?, ?, DATE_ADD(UTC_TIMESTAMP(6), INTERVAL 30 MINUTE))'
);
$targetId = 301;
$stmt->bind_param('is', $targetId, $codeHash);
$stmt->execute();
$stmt->close();
$db->close();

$newToken2 = 'Escolha-individual-@' . bin2hex(random_bytes(8));
ensure(!AdminToken2Enrollment::enroll(301, 'incorreto', $code, $newToken2, $newToken2), 'Token 1 errado impede cadastro');
ensure(!AdminToken2Enrollment::enroll(301, 'nova-credencial-local-de-testes', 'codigo-invalido', $newToken2, $newToken2), 'código inválido impede cadastro');
try {
    AdminToken2Enrollment::validateNewToken('curto','curto');
    throw new RuntimeException('Token 2 curto não foi rejeitado');
} catch (InvalidArgumentException $expected) {}
ensure(AdminToken2Enrollment::enroll(301,'nova-credencial-local-de-testes',$code,$newToken2,$newToken2), 'admin configura seu Token 2');
ensure(!AdminToken2Enrollment::enroll(301,'nova-credencial-local-de-testes',$code,$newToken2,$newToken2), 'código de ativação de uso único');
ensure(AdminStepUp::verifySite($newToken2), 'novo Token 2 libera site do próprio ADM');
ensure(!AdminStepUp::allowed('ai'), 'novo Token 2 não libera IA sem sequência');
$db = getDatabaseConnection();
$usedCode = $db->query("SELECT COUNT(*) FROM admin_security_enrollments WHERE admin_id = 301")->fetch_row();
ensure((int) $usedCode[0] === 0, 'ativação consumida pelo MySQL');
$db->close();

session_destroy();
echo "ADM step-up MySQL: OK\n";
