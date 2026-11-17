<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — tests/privacy_phase1_unit.php
 * Finalidade: privacy phase1 unit.
 * Domínio: testes e prevenção de regressões.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


require_once dirname(__DIR__) . '/php/controllers/Privacy/PrivacyContext.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once dirname(__DIR__) . '/php/controllers/Privacy/AccessLogService.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once dirname(__DIR__) . '/php/controllers/Privacy/ConsentService.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once dirname(__DIR__) . '/php/controllers/Privacy/AuditService.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

// Detalhe DevIN: Método testAssert: encapsula uma responsabilidade reutilizável do módulo no contexto de privacy phase1 unit. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function testAssert(bool $condition, string $name): void { // Explicação DevIN: Declara função/método testAssert, com seus parâmetros e comportamento.
    if (!$condition) throw new RuntimeException('Falhou: ' . $name); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
// Detalhe DevIN: Método testThrows: encapsula uma responsabilidade reutilizável do módulo no contexto de privacy phase1 unit. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function testThrows(callable $operation, string $name): void { // Explicação DevIN: Declara função/método testThrows, com seus parâmetros e comportamento.
    try { $operation(); } catch (InvalidArgumentException | RuntimeException | DomainException $expected) { return; } // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    throw new RuntimeException('Não rejeitou: ' . $name); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

testAssert(PrivacyContext::clientIp(['REMOTE_ADDR' => '127.0.0.1','HTTP_X_FORWARDED_FOR'=>'192.0.2.1']) === '127.0.0.1', 'IP servidor, sem spoof'); // Explicação DevIN: Continua expressão ou bloco pertencente a função testThrows.
testAssert(PrivacyContext::clientIp(['HTTP_X_FORWARDED_FOR'=>'192.0.2.1']) === null, 'ignorar IP cliente'); // Explicação DevIN: Continua expressão ou bloco pertencente a função testThrows.
testAssert(PrivacyContext::clientIp(['REMOTE_ADDR' => 'impostor']) === null, 'IP inválido'); // Explicação DevIN: Continua expressão ou bloco pertencente a função testThrows.
$key = str_repeat('A', 48); // Explicação DevIN: Atribui um valor à variável PHP $key.
$hash = PrivacyContext::ipHash('2001:0db8::1', $key); // Explicação DevIN: Atribui um valor à variável PHP $hash.
testAssert(strlen((string) $hash) === 64, 'HMAC SHA256'); // Explicação DevIN: Continua expressão ou bloco pertencente a função testThrows.
// Detalhe DevIN: Compara hashes ou segredos sem diferenças de tempo evitáveis.
testAssert(hash_equals((string) $hash, (string) PrivacyContext::ipHash('2001:db8:0:0:0:0:0:1', $key)), 'IPv6 normalizado'); // Explicação DevIN: Executa operação criptográfica de senha ou comparação protegida.
testAssert(PrivacyContext::ipHash('impostor', $key) === null, 'hash IP inválido'); // Explicação DevIN: Continua expressão ou bloco pertencente a função testThrows.
testThrows(static fn() => PrivacyContext::ipHash('127.0.0.1', 'senha-fraca'), 'pepper HMAC fraca'); // Explicação DevIN: Continua expressão ou bloco pertencente a função testThrows.
testThrows(static fn() => PrivacyContext::validateAccount('intruso', 10), 'tipo de conta fechado'); // Explicação DevIN: Continua expressão ou bloco pertencente a função testThrows.
testThrows(static fn() => PrivacyContext::validatePurpose('marketing_whatsapp'), 'finalidade não registrada'); // Explicação DevIN: Continua expressão ou bloco pertencente a função testThrows.
testAssert(PrivacyContext::userAgent("Teste\r\nAgente") === 'TesteAgente', 'User-Agent sem controles'); // Explicação DevIN: Continua expressão ou bloco pertencente a função testThrows.
testAssert(AccessLogService::cleanPath('/pessoa?email=segredo@example.com&token=abc#fragmento') === '/pessoa', 'query string nunca vira log'); // Explicação DevIN: Continua expressão ou bloco pertencente a função testThrows.
testAssert(AccessLogService::cleanPath('') === '/', 'path seguro'); // Explicação DevIN: Continua expressão ou bloco pertencente a função testThrows.
testAssert(preg_match('/^[a-f0-9-]{36}$/D', PrivacyContext::requestId()) === 1, 'request id formato'); // Explicação DevIN: Continua expressão ou bloco pertencente a função testThrows.
testAssert(!AuditService::enabled(), 'auditoria opt-in sem variável no ambiente'); // Explicação DevIN: Continua expressão ou bloco pertencente a função testThrows.
echo "LGPD Fase 1: testes puros OK\n"; // Explicação DevIN: Produz saída ou conteúdo da resposta do programa.
