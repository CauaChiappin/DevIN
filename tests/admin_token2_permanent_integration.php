<?php
declare(strict_types=1);

if (getenv('LGPD_TEST_DB') !== 'devin_lgpd_test') {
    throw new RuntimeException('Somente banco descartável.');
}
$_ENV['APP_ENV'] = 'development';
$_ENV['DEVIN_JWT_SECRET'] = 'test-only-secret-do-not-use';
$_ENV['DB_HOST'] = getenv('LGPD_TEST_HOST') ?: '127.0.0.1';
$_ENV['DB_PORT'] = '3306';
$_ENV['DB_NAME'] = 'devin_lgpd_test';
$_ENV['DB_USER'] = getenv('LGPD_TEST_USER') ?: 'root';
$_ENV['DB_PASS'] = getenv('LGPD_TEST_PASSWORD') ?: '';
$_ENV['DEVIN_ADMIN_STEPUP_ENABLED'] = '1';
$_SERVER['REMOTE_ADDR'] = '192.0.2.89';
require_once dirname(__DIR__) . '/php/controllers/Security/AdminStepUp.php';
require_once dirname(__DIR__) . '/php/controllers/Security/AdminToken2Enrollment.php';

function assertPermanent(bool $condition, string $reason): void {
    if (!$condition) throw new RuntimeException('Token 2 permanente: ' . $reason);
}

$db = getDatabaseConnection();
$db->query('ALTER TABLE administrador ADD COLUMN senha_hash VARCHAR(255) NULL');
$login = 'senha-de-login@' . bin2hex(random_bytes(7));
$loginHash = password_hash($login, PASSWORD_DEFAULT);
$stmt = $db->prepare('INSERT INTO administrador (id_administrador,senha_hash) VALUES (?,?)');
foreach ([401,402,403,404] as $id) {
    $stmt->bind_param('is', $id, $loginHash);
    $stmt->execute();
}
$stmt->close();
$db->close();

startSecureSession();
$_SESSION['logado'] = true;
$_SESSION['usuario_tipo'] = 'adm';
$_SESSION['usuario_id'] = 401;
$_SESSION['auth_time'] = time();
$_SESSION['last_activity'] = time();

assertPermanent(!AdminToken2Enrollment::configured(), 'ADM inicialmente sem chave');
$chosen = str_repeat('uma-chave-sem-limite-de-caracteres-ç!@', 70);
assertPermanent(strlen($chosen) > 72, 'a senha de teste excede 72 bytes');
assertPermanent(AdminToken2Enrollment::save($login, null, $chosen, $chosen), 'primeiro cadastro sem convite temporário');
assertPermanent(AdminToken2Enrollment::configured(), 'primeiro cadastro persistido');
assertPermanent(AdminStepUp::verifySite($chosen), 'Token 2 longo aceito nas ações');
assertPermanent(AdminStepUp::verifyAiFirst($login), 'IA ainda pede Token 1');
assertPermanent(AdminStepUp::verifyAiSecond($chosen), 'IA aceita Token 2 novo');

$db = getDatabaseConnection();
$stored = $db->query('SELECT token2_hash, token2_configured_at FROM administrador WHERE id_administrador = 401')->fetch_assoc();
assertPermanent($stored['token2_hash'] !== $chosen, 'apenas hash salvo');
assertPermanent($stored['token2_configured_at'] !== null, 'nenhum prazo de expiração da senha');
$db->close();

assertPermanent(!AdminToken2Enrollment::save($login, 'incorreto', 'novo-segredo-proprio-123456789', 'novo-segredo-proprio-123456789'), 'troca recusa Token 2 anterior errado');
$changed = 'novo-segredo-proprio-123456789';
assertPermanent(AdminToken2Enrollment::save($login, $chosen, $changed, $changed), 'próprio ADM troca quando quiser');
assertPermanent(!AdminStepUp::allowed('site') && !AdminStepUp::allowed('ai'), 'troca revoga ambos os grants');
assertPermanent(!AdminStepUp::verifySite($chosen), 'Token 2 antigo revogado');
assertPermanent(AdminStepUp::verifySite($changed), 'Token 2 novo válido');
$_SESSION['usuario_id'] = 402;
assertPermanent(!AdminToken2Enrollment::configured(), 'conta diferente permanece sem senha');
assertPermanent(!AdminToken2Enrollment::save('senha-inválida', null, 'individual-402-seguro-123456', 'individual-402-seguro-123456'), 'cadastro exige Token 1');
assertPermanent(!AdminToken2Enrollment::save($login, null, $changed, $changed), 'outro ADM não pode escolher chave igual');
assertPermanent(AdminToken2Enrollment::save($login, null, 'individual-402-seguro-123456', 'individual-402-seguro-123456'), 'outro ADM cadastra própria senha');
$_SESSION['usuario_id'] = 401;
assertPermanent(AdminStepUp::verifySite($changed), 'credencial persistiu entre utilizações');
session_destroy();
echo "ADM Token 2 permanente: OK\n";
