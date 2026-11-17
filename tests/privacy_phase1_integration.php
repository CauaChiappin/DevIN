<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — tests/privacy_phase1_integration.php
 * Finalidade: privacy phase1 integration.
 * Domínio: testes e prevenção de regressões.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


/** Executar APENAS com banco descartável em CI; requer variáveis LGPD_TEST_* . */
foreach (['LGPD_TEST_DB','LGPD_TEST_USER','LGPD_TEST_PASSWORD'] as $name) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
    if (getenv($name) === false) throw new RuntimeException('Ambiente de teste ausente: ' . $name); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$database = getenv('LGPD_TEST_DB'); // Explicação DevIN: Atribui um valor à variável PHP $database.
if ($database !== 'devin_lgpd_test') throw new RuntimeException('Proibido executar em banco não descartável.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
$host = getenv('LGPD_TEST_HOST') ?: '127.0.0.1'; // Explicação DevIN: Atribui um valor à variável PHP $host.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
$db = new mysqli($host, getenv('LGPD_TEST_USER'), getenv('LGPD_TEST_PASSWORD'), $database); // Explicação DevIN: Atribui um valor à variável PHP $db.
$db->set_charset('utf8mb4'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
$db->query("SET time_zone = '+00:00'"); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.

require_once dirname(__DIR__) . '/php/controllers/Privacy/PrivacyContext.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once dirname(__DIR__) . '/php/controllers/Privacy/LegalDocumentService.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once dirname(__DIR__) . '/php/controllers/Privacy/ConsentService.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once dirname(__DIR__) . '/php/controllers/Privacy/AuditService.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once dirname(__DIR__) . '/php/controllers/Privacy/AccessLogService.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once dirname(__DIR__) . '/php/controllers/Privacy/PrivacyRequestService.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once dirname(__DIR__) . '/php/controllers/Privacy/ErasureReceiptService.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once dirname(__DIR__) . '/php/controllers/Privacy/SecurityIncidentService.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once dirname(__DIR__) . '/php/controllers/Privacy/RetentionService.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

// Detalhe DevIN: Método check: verifica condições antes de liberar o próximo passo no contexto de privacy phase1 integration. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function check(bool $ok, string $label): void { // Explicação DevIN: Declara função/método check, com seus parâmetros e comportamento.
    if (!$ok) throw new RuntimeException('Falha em ' . $label); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
// Detalhe DevIN: Método rejects: encapsula uma responsabilidade reutilizável do módulo no contexto de privacy phase1 integration. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function rejects(callable $operation, string $label): void { // Explicação DevIN: Declara função/método rejects, com seus parâmetros e comportamento.
    try { $operation(); } catch (InvalidArgumentException | RuntimeException | DomainException $expected) { return; } // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    throw new RuntimeException('Não rejeitou ' . $label); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

$db->query('CREATE TABLE IF NOT EXISTS pessoa (id_pessoa INT PRIMARY KEY) ENGINE=InnoDB'); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
$db->query('CREATE TABLE IF NOT EXISTS empresa (id_empresa INT PRIMARY KEY) ENGINE=InnoDB'); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
$db->query('CREATE TABLE IF NOT EXISTS administrador (id_administrador INT PRIMARY KEY) ENGINE=InnoDB'); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
$db->query('INSERT INTO pessoa VALUES (101),(102)'); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
$db->query('INSERT INTO empresa VALUES (201)'); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
$db->query('INSERT INTO administrador(id_administrador) VALUES (301)'); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
$_ENV['DEVIN_LGPD_IP_HASH_KEY'] = str_repeat('Z', 48); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_SERVER['REMOTE_ADDR'] = '192.0.2.50'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_SERVER['HTTP_USER_AGENT'] = "LGPD-CI/1.0"; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.

LegalDocumentService::publishApproved($db, 'terms', '2026-11-v1', hash('sha256','termos revisados')); // Explicação DevIN: Continua expressão ou bloco pertencente a função rejects.
LegalDocumentService::publishApproved($db, 'privacy', '2026-11-v1', hash('sha256','politica revisada')); // Explicação DevIN: Continua expressão ou bloco pertencente a função rejects.
check(LegalDocumentService::active($db, 'terms')['version_code'] === '2026-11-v1', 'versão termos'); // Explicação DevIN: Continua expressão ou bloco pertencente a função rejects.
check(LegalDocumentService::active($db, 'privacy')['version_code'] === '2026-11-v1', 'versão política'); // Explicação DevIN: Continua expressão ou bloco pertencente a função rejects.
rejects(static fn() => LegalDocumentService::publishApproved($db,'terms','2026-11-v1',hash('sha256','texto diferente')),'imutabilidade'); // Explicação DevIN: Continua expressão ou bloco pertencente a função rejects.

// Detalhe DevIN: Inicia uma transação para manter alterações relacionadas consistentes.
$db->begin_transaction(); // Explicação DevIN: Continua expressão ou bloco pertencente a função rejects.
try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    rejects(static fn() => ConsentService::recordLegalAcceptance($db,'pessoa',101,false,'2026-11-v1','2026-11-v1'),'checkbox não marcado'); // Explicação DevIN: Continua expressão ou bloco pertencente a função rejects.
    rejects(static fn() => ConsentService::recordLegalAcceptance($db,'pessoa',101,true,'2025-old','2026-11-v1'), 'versão não exibida'); // Explicação DevIN: Continua expressão ou bloco pertencente a função rejects.
    $consentId = ConsentService::recordLegalAcceptance($db,'pessoa',101,true,'2026-11-v1','2026-11-v1'); // Explicação DevIN: Atribui um valor à variável PHP $consentId.
    // Detalhe DevIN: Confirma alterações realizadas no mesmo contexto transacional.
    $db->commit(); // Explicação DevIN: Continua expressão ou bloco pertencente a função rejects.
// Detalhe DevIN: Desfaz alterações ainda não confirmadas quando ocorre falha.
} catch (Throwable $e) {$db->rollback();throw $e;} // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
check($consentId > 0, 'registro aceite'); // Explicação DevIN: Continua expressão ou bloco pertencente a função rejects.
$row = $db->query("SELECT account_id,terms_version,privacy_notice_version,ip_hash,user_agent FROM user_legal_acceptances WHERE id = {$consentId}")->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
check((int)$row['account_id'] === 101 && $row['terms_version'] === '2026-11-v1', 'versionamento aceite'); // Explicação DevIN: Continua expressão ou bloco pertencente a função rejects.
check(strlen($row['ip_hash']) === 64 && !str_contains($row['ip_hash'],'192.0.2.50'), 'IP somente HMAC'); // Explicação DevIN: Continua expressão ou bloco pertencente a função rejects.
check($row['user_agent'] === 'LGPD-CI/1.0', 'user agent auditável'); // Explicação DevIN: Continua expressão ou bloco pertencente a função rejects.
check(!ConsentService::isGranted($db, 'pessoa',101,'marketing_email'), 'opt-out por padrão'); // Explicação DevIN: Continua expressão ou bloco pertencente a função rejects.
check(!ConsentService::setPreference($db,'pessoa',101,'marketing_email',false), 'negação ausente sem evento'); // Explicação DevIN: Continua expressão ou bloco pertencente a função rejects.
check(ConsentService::setPreference($db,'pessoa',101,'marketing_email',true), 'concessão explícita'); // Explicação DevIN: Continua expressão ou bloco pertencente a função rejects.
check(ConsentService::isGranted($db,'pessoa',101,'marketing_email'), 'preferência concedida'); // Explicação DevIN: Continua expressão ou bloco pertencente a função rejects.
check(!ConsentService::isGranted($db,'pessoa',102,'marketing_email'), 'isolamento por conta'); // Explicação DevIN: Continua expressão ou bloco pertencente a função rejects.
check(!ConsentService::setPreference($db,'pessoa',101,'marketing_email',true), 'concessão idempotente'); // Explicação DevIN: Continua expressão ou bloco pertencente a função rejects.
check(ConsentService::setPreference($db,'pessoa',101,'marketing_email',false), 'revogação'); // Explicação DevIN: Continua expressão ou bloco pertencente a função rejects.
check((int)$db->query("SELECT COUNT(*) FROM user_consent_events WHERE account_id=101")->fetch_row()[0] === 2, 'duas evidências'); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.

$auditId = AuditService::record($db,'login_success','success','pessoa',101); // Explicação DevIN: Atribui um valor à variável PHP $auditId.
check($auditId > 0, 'log de autenticação'); // Explicação DevIN: Continua expressão ou bloco pertencente a função rejects.
$audit = $db->query("SELECT event_type,metadata_json,ip_hash,retention_until FROM audit_logs WHERE id={$auditId}")->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
check($audit['metadata_json'] === null && strlen($audit['ip_hash']) === 64, 'auditoria sem payload'); // Explicação DevIN: Continua expressão ou bloco pertencente a função rejects.
check(strtotime($audit['retention_until']) > time(), 'auditoria com prazo'); // Explicação DevIN: Continua expressão ou bloco pertencente a função rejects.
rejects(static fn() => AuditService::record($db,'senha_super_secreta','success'),'eventos fechados'); // Explicação DevIN: Continua expressão ou bloco pertencente a função rejects.

$accessId = AccessLogService::record($db,'2001:db8::2','GET','/pessoa?token=segredo',200,'pessoa',101); // Explicação DevIN: Atribui um valor à variável PHP $accessId.
$access = $db->query("SELECT INET6_NTOA(client_ip) AS ip,path,delete_after FROM app_access_logs WHERE id={$accessId}")->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
check($access['ip'] === '2001:db8::2' && $access['path'] === '/pessoa', 'IP reversível, sem query'); // Explicação DevIN: Continua expressão ou bloco pertencente a função rejects.
check(strtotime($access['delete_after']) > strtotime('+5 months'), 'registro com seis meses'); // Explicação DevIN: Continua expressão ou bloco pertencente a função rejects.

$reference = PrivacyRequestService::create($db,'pessoa',101,'erasure'); // Explicação DevIN: Atribui um valor à variável PHP $reference.
check(strlen($reference) === 32, 'referência aleatória'); // Explicação DevIN: Continua expressão ou bloco pertencente a função rejects.
check(count(PrivacyRequestService::listOwn($db,'pessoa',101)) === 1, 'consulta própria'); // Explicação DevIN: Continua expressão ou bloco pertencente a função rejects.
check(count(PrivacyRequestService::listOwn($db,'pessoa',102)) === 0, 'não expõe outros titulares'); // Explicação DevIN: Continua expressão ou bloco pertencente a função rejects.
$escaped = $db->real_escape_string($reference); // Explicação DevIN: Atribui um valor à variável PHP $escaped.
$requestId = (int)$db->query("SELECT id FROM privacy_requests WHERE public_reference='{$escaped}'")->fetch_row()[0]; // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
rejects(static fn() => PrivacyRequestService::updateStatus($db,301,$requestId,'completed','ok'), 'sem pular verificação'); // Explicação DevIN: Continua expressão ou bloco pertencente a função rejects.
PrivacyRequestService::updateStatus($db,301,$requestId,'verifying_identity'); // Explicação DevIN: Continua expressão ou bloco pertencente a função rejects.
PrivacyRequestService::updateStatus($db,301,$requestId,'under_review'); // Explicação DevIN: Continua expressão ou bloco pertencente a função rejects.
PrivacyRequestService::updateStatus($db,301,$requestId,'completed','verified_erasure_performed'); // Explicação DevIN: Continua expressão ou bloco pertencente a função rejects.
$receipt = ErasureReceiptService::record($db,301,$requestId,'partially_retained',['audit'],'legal_obligation'); // Explicação DevIN: Atribui um valor à variável PHP $receipt.
check(strlen($receipt) === 32, 'recibo gerado após processo concluído'); // Explicação DevIN: Continua expressão ou bloco pertencente a função rejects.
rejects(static fn() => ErasureReceiptService::record($db,301,$requestId,'deleted'), 'recibo único'); // Explicação DevIN: Continua expressão ou bloco pertencente a função rejects.

$incident = SecurityIncidentService::register($db,301,gmdate('Y-m-d H:i:s'),'Exposição de dados em ambiente de desenvolvimento',['technical'],1); // Explicação DevIN: Atribui um valor à variável PHP $incident.
check(strlen($incident) === 32, 'incidente de segurança'); // Explicação DevIN: Continua expressão ou bloco pertencente a função rejects.
$counts = RetentionService::expiredCounts($db); // Explicação DevIN: Atribui um valor à variável PHP $counts.
check($counts === ['access_logs'=>0,'audit_logs'=>0], 'sem expiração prematura'); // Explicação DevIN: Continua expressão ou bloco pertencente a função rejects.
rejects(static fn() => RetentionService::pruneBatch($db), 'limpeza sem autorização'); // Explicação DevIN: Continua expressão ou bloco pertencente a função rejects.
$db->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função rejects.
echo "LGPD Fase 1: integração MySQL OK\n"; // Explicação DevIN: Produz saída ou conteúdo da resposta do programa.
