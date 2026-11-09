<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/php/controllers/Privacy/PrivacyContext.php';
require_once dirname(__DIR__) . '/php/controllers/Privacy/AccessLogService.php';
require_once dirname(__DIR__) . '/php/controllers/Privacy/ConsentService.php';
require_once dirname(__DIR__) . '/php/controllers/Privacy/AuditService.php';

function testAssert(bool $condition, string $name): void {
    if (!$condition) throw new RuntimeException('Falhou: ' . $name);
}
function testThrows(callable $operation, string $name): void {
    try { $operation(); } catch (InvalidArgumentException | RuntimeException | DomainException $expected) { return; }
    throw new RuntimeException('Não rejeitou: ' . $name);
}

testAssert(PrivacyContext::clientIp(['REMOTE_ADDR' => '127.0.0.1','HTTP_X_FORWARDED_FOR'=>'192.0.2.1']) === '127.0.0.1', 'IP servidor, sem spoof');
testAssert(PrivacyContext::clientIp(['HTTP_X_FORWARDED_FOR'=>'192.0.2.1']) === null, 'ignorar IP cliente');
testAssert(PrivacyContext::clientIp(['REMOTE_ADDR' => 'impostor']) === null, 'IP inválido');
$key = str_repeat('A', 48);
$hash = PrivacyContext::ipHash('2001:0db8::1', $key);
testAssert(strlen((string) $hash) === 64, 'HMAC SHA256');
testAssert(hash_equals((string) $hash, (string) PrivacyContext::ipHash('2001:db8:0:0:0:0:0:1', $key)), 'IPv6 normalizado');
testAssert(PrivacyContext::ipHash('impostor', $key) === null, 'hash IP inválido');
testThrows(static fn() => PrivacyContext::ipHash('127.0.0.1', 'senha-fraca'), 'pepper HMAC fraca');
testThrows(static fn() => PrivacyContext::validateAccount('intruso', 10), 'tipo de conta fechado');
testThrows(static fn() => PrivacyContext::validatePurpose('marketing_whatsapp'), 'finalidade não registrada');
testAssert(PrivacyContext::userAgent("Teste\r\nAgente") === 'TesteAgente', 'User-Agent sem controles');
testAssert(AccessLogService::cleanPath('/pessoa?email=segredo@example.com&token=abc#fragmento') === '/pessoa', 'query string nunca vira log');
testAssert(AccessLogService::cleanPath('') === '/', 'path seguro');
testAssert(preg_match('/^[a-f0-9-]{36}$/D', PrivacyContext::requestId()) === 1, 'request id formato');
testAssert(!AuditService::enabled(), 'auditoria opt-in sem variável no ambiente');
echo "LGPD Fase 1: testes puros OK\n";
