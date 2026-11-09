<?php
declare(strict_types=1);

/** Executar APENAS com banco descartável em CI; requer variáveis LGPD_TEST_* . */
foreach (['LGPD_TEST_DB','LGPD_TEST_USER','LGPD_TEST_PASSWORD'] as $name) {
    if (getenv($name) === false) throw new RuntimeException('Ambiente de teste ausente: ' . $name);
}
$database = getenv('LGPD_TEST_DB');
if ($database !== 'devin_lgpd_test') throw new RuntimeException('Proibido executar em banco não descartável.');
$host = getenv('LGPD_TEST_HOST') ?: '127.0.0.1';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = new mysqli($host, getenv('LGPD_TEST_USER'), getenv('LGPD_TEST_PASSWORD'), $database);
$db->set_charset('utf8mb4');
$db->query("SET time_zone = '+00:00'");

require_once dirname(__DIR__) . '/php/controllers/Privacy/PrivacyContext.php';
require_once dirname(__DIR__) . '/php/controllers/Privacy/LegalDocumentService.php';
require_once dirname(__DIR__) . '/php/controllers/Privacy/ConsentService.php';
require_once dirname(__DIR__) . '/php/controllers/Privacy/AuditService.php';
require_once dirname(__DIR__) . '/php/controllers/Privacy/AccessLogService.php';
require_once dirname(__DIR__) . '/php/controllers/Privacy/PrivacyRequestService.php';
require_once dirname(__DIR__) . '/php/controllers/Privacy/ErasureReceiptService.php';
require_once dirname(__DIR__) . '/php/controllers/Privacy/SecurityIncidentService.php';
require_once dirname(__DIR__) . '/php/controllers/Privacy/RetentionService.php';

function check(bool $ok, string $label): void {
    if (!$ok) throw new RuntimeException('Falha em ' . $label);
}
function rejects(callable $operation, string $label): void {
    try { $operation(); } catch (InvalidArgumentException | RuntimeException | DomainException $expected) { return; }
    throw new RuntimeException('Não rejeitou ' . $label);
}

$db->query('CREATE TABLE IF NOT EXISTS pessoa (id_pessoa INT PRIMARY KEY) ENGINE=InnoDB');
$db->query('CREATE TABLE IF NOT EXISTS empresa (id_empresa INT PRIMARY KEY) ENGINE=InnoDB');
$db->query('CREATE TABLE IF NOT EXISTS administrador (id_administrador INT PRIMARY KEY) ENGINE=InnoDB');
$db->query('INSERT INTO pessoa VALUES (101),(102)');
$db->query('INSERT INTO empresa VALUES (201)');
$db->query('INSERT INTO administrador VALUES (301)');
$_ENV['DEVIN_LGPD_IP_HASH_KEY'] = str_repeat('Z', 48);
$_SERVER['REMOTE_ADDR'] = '192.0.2.50';
$_SERVER['HTTP_USER_AGENT'] = "LGPD-CI/1.0";

LegalDocumentService::publishApproved($db, 'terms', '2026-11-v1', hash('sha256','termos revisados'));
LegalDocumentService::publishApproved($db, 'privacy', '2026-11-v1', hash('sha256','politica revisada'));
check(LegalDocumentService::active($db, 'terms')['version_code'] === '2026-11-v1', 'versão termos');
check(LegalDocumentService::active($db, 'privacy')['version_code'] === '2026-11-v1', 'versão política');
rejects(static fn() => LegalDocumentService::publishApproved($db,'terms','2026-11-v1',hash('sha256','texto diferente')),'imutabilidade');

$db->begin_transaction();
try {
    rejects(static fn() => ConsentService::recordLegalAcceptance($db,'pessoa',101,false,'2026-11-v1','2026-11-v1'),'checkbox não marcado');
    rejects(static fn() => ConsentService::recordLegalAcceptance($db,'pessoa',101,true,'2025-old','2026-11-v1'), 'versão não exibida');
    $consentId = ConsentService::recordLegalAcceptance($db,'pessoa',101,true,'2026-11-v1','2026-11-v1');
    $db->commit();
} catch (Throwable $e) {$db->rollback();throw $e;}
check($consentId > 0, 'registro aceite');
$row = $db->query("SELECT account_id,terms_version,privacy_notice_version,ip_hash,user_agent FROM user_legal_acceptances WHERE id = {$consentId}")->fetch_assoc();
check((int)$row['account_id'] === 101 && $row['terms_version'] === '2026-11-v1', 'versionamento aceite');
check(strlen($row['ip_hash']) === 64 && !str_contains($row['ip_hash'],'192.0.2.50'), 'IP somente HMAC');
check($row['user_agent'] === 'LGPD-CI/1.0', 'user agent auditável');
check(!ConsentService::isGranted($db, 'pessoa',101,'marketing_email'), 'opt-out por padrão');
check(!ConsentService::setPreference($db,'pessoa',101,'marketing_email',false), 'negação ausente sem evento');
check(ConsentService::setPreference($db,'pessoa',101,'marketing_email',true), 'concessão explícita');
check(ConsentService::isGranted($db,'pessoa',101,'marketing_email'), 'preferência concedida');
check(!ConsentService::isGranted($db,'pessoa',102,'marketing_email'), 'isolamento por conta');
check(!ConsentService::setPreference($db,'pessoa',101,'marketing_email',true), 'concessão idempotente');
check(ConsentService::setPreference($db,'pessoa',101,'marketing_email',false), 'revogação');
check((int)$db->query("SELECT COUNT(*) FROM user_consent_events WHERE account_id=101")->fetch_row()[0] === 2, 'duas evidências');

$auditId = AuditService::record($db,'login_success','success','pessoa',101);
check($auditId > 0, 'log de autenticação');
$audit = $db->query("SELECT event_type,metadata_json,ip_hash,retention_until FROM audit_logs WHERE id={$auditId}")->fetch_assoc();
check($audit['metadata_json'] === null && strlen($audit['ip_hash']) === 64, 'auditoria sem payload');
check(strtotime($audit['retention_until']) > time(), 'auditoria com prazo');
rejects(static fn() => AuditService::record($db,'senha_super_secreta','success'),'eventos fechados');

$accessId = AccessLogService::record($db,'2001:db8::2','GET','/pessoa?token=segredo',200,'pessoa',101);
$access = $db->query("SELECT INET6_NTOA(client_ip) AS ip,path,delete_after FROM app_access_logs WHERE id={$accessId}")->fetch_assoc();
check($access['ip'] === '2001:db8::2' && $access['path'] === '/pessoa', 'IP reversível, sem query');
check(strtotime($access['delete_after']) > strtotime('+5 months'), 'registro com seis meses');

$reference = PrivacyRequestService::create($db,'pessoa',101,'erasure');
check(strlen($reference) === 32, 'referência aleatória');
check(count(PrivacyRequestService::listOwn($db,'pessoa',101)) === 1, 'consulta própria');
check(count(PrivacyRequestService::listOwn($db,'pessoa',102)) === 0, 'não expõe outros titulares');
$escaped = $db->real_escape_string($reference);
$requestId = (int)$db->query("SELECT id FROM privacy_requests WHERE public_reference='{$escaped}'")->fetch_row()[0];
rejects(static fn() => PrivacyRequestService::updateStatus($db,301,$requestId,'completed','ok'), 'sem pular verificação');
PrivacyRequestService::updateStatus($db,301,$requestId,'verifying_identity');
PrivacyRequestService::updateStatus($db,301,$requestId,'under_review');
PrivacyRequestService::updateStatus($db,301,$requestId,'completed','verified_erasure_performed');
$receipt = ErasureReceiptService::record($db,301,$requestId,'partially_retained',['audit'],'legal_obligation');
check(strlen($receipt) === 32, 'recibo gerado após processo concluído');
rejects(static fn() => ErasureReceiptService::record($db,301,$requestId,'deleted'), 'recibo único');

$incident = SecurityIncidentService::register($db,301,gmdate('Y-m-d H:i:s'),'Exposição de dados em ambiente de desenvolvimento',['technical'],1);
check(strlen($incident) === 32, 'incidente de segurança');
$counts = RetentionService::expiredCounts($db);
check($counts === ['access_logs'=>0,'audit_logs'=>0], 'sem expiração prematura');
rejects(static fn() => RetentionService::pruneBatch($db), 'limpeza sem autorização');
$db->close();
echo "LGPD Fase 1: integração MySQL OK\n";
