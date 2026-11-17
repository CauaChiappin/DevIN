<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — tests/admin_token2_permanent_integration.php
 * Finalidade: admin token2 permanent integration.
 * Domínio: testes e prevenção de regressões.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


if (getenv('LGPD_TEST_DB') !== 'devin_lgpd_test') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    throw new RuntimeException('Somente banco descartável.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$_ENV['APP_ENV'] = 'development'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_ENV['DEVIN_JWT_SECRET'] = 'test-only-secret-do-not-use'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_ENV['DB_HOST'] = getenv('LGPD_TEST_HOST') ?: '127.0.0.1'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_ENV['DB_PORT'] = '3306'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_ENV['DB_NAME'] = 'devin_lgpd_test'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_ENV['DB_USER'] = getenv('LGPD_TEST_USER') ?: 'root'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_ENV['DB_PASS'] = getenv('LGPD_TEST_PASSWORD') ?: ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_ENV['DEVIN_ADMIN_STEPUP_ENABLED'] = '1'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_SERVER['REMOTE_ADDR'] = '192.0.2.89'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
require_once dirname(__DIR__) . '/php/controllers/Security/AdminStepUp.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once dirname(__DIR__) . '/php/controllers/Security/AdminToken2Enrollment.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

// Detalhe DevIN: Método assertPermanent: encapsula uma responsabilidade reutilizável do módulo no contexto de admin token2 permanent integration. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function assertPermanent(bool $condition, string $reason): void { // Explicação DevIN: Declara função/método assertPermanent, com seus parâmetros e comportamento.
    if (!$condition) throw new RuntimeException('Token 2 permanente: ' . $reason); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

$db = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $db.
$db->query('ALTER TABLE administrador ADD COLUMN senha_hash VARCHAR(255) NULL'); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
$login = 'senha-de-login@' . bin2hex(random_bytes(7)); // Explicação DevIN: Atribui um valor à variável PHP $login.
$loginHash = password_hash($login, PASSWORD_DEFAULT); // Explicação DevIN: Executa operação criptográfica de senha ou comparação protegida.
// Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
$stmt = $db->prepare('INSERT INTO administrador (id_administrador,senha_hash) VALUES (?,?)'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
foreach ([401,402,403,404] as $id) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
    $stmt->bind_param('is', $id, $loginHash); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
    $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertPermanent.
$db->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertPermanent.

startSecureSession(); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertPermanent.
$_SESSION['logado'] = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_SESSION['usuario_tipo'] = 'adm'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_SESSION['usuario_id'] = 401; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_SESSION['auth_time'] = time(); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_SESSION['last_activity'] = time(); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.

// O formulário deve ser inacessível a não-ADMs ou a contas ADM ausentes no banco.
assertPermanent(AdminToken2Enrollment::requireVerifiedAdminAccount() === 401, 'conta ADM real confirmada no banco'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertPermanent.
$_SESSION['usuario_tipo'] = 'pessoa'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$deniedType = false; // Explicação DevIN: Atribui um valor à variável PHP $deniedType.
try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    AdminToken2Enrollment::requireVerifiedAdminAccount(); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertPermanent.
} catch (DomainException $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    $deniedType = true; // Explicação DevIN: Atribui um valor à variável PHP $deniedType.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
assertPermanent($deniedType, 'tipo pessoa não pode cadastrar Token 2'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertPermanent.
$_SESSION['usuario_tipo'] = 'adm'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_SESSION['usuario_id'] = 999; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$deniedMissing = false; // Explicação DevIN: Atribui um valor à variável PHP $deniedMissing.
try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    AdminToken2Enrollment::requireVerifiedAdminAccount(); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertPermanent.
} catch (DomainException $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    $deniedMissing = true; // Explicação DevIN: Atribui um valor à variável PHP $deniedMissing.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
assertPermanent($deniedMissing, 'ID ADM inexistente não pode abrir formulário'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertPermanent.
$_SESSION['usuario_id'] = 401; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.

assertPermanent(!AdminToken2Enrollment::configured(), 'ADM inicialmente sem chave'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertPermanent.
$chosen = str_repeat('uma-chave-sem-limite-de-caracteres-ç!@', 70); // Explicação DevIN: Atribui um valor à variável PHP $chosen.
assertPermanent(strlen($chosen) > 72, 'a senha de teste excede 72 bytes'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertPermanent.
assertPermanent(AdminToken2Enrollment::save($login, null, $chosen, $chosen), 'primeiro cadastro sem convite temporário'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertPermanent.
assertPermanent(AdminToken2Enrollment::configured(), 'primeiro cadastro persistido'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertPermanent.
assertPermanent(AdminStepUp::verifySite($chosen), 'Token 2 longo aceito nas ações'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertPermanent.
assertPermanent(AdminStepUp::verifyAiFirst($login), 'IA ainda pede Token 1'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertPermanent.
assertPermanent(AdminStepUp::verifyAiSecond($chosen), 'IA aceita Token 2 novo'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertPermanent.

$db = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $db.
$stored = $db->query('SELECT token2_hash, token2_configured_at FROM administrador WHERE id_administrador = 401')->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
assertPermanent($stored['token2_hash'] !== $chosen, 'apenas hash salvo'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertPermanent.
assertPermanent($stored['token2_configured_at'] !== null, 'nenhum prazo de expiração da senha'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertPermanent.
$db->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertPermanent.

assertPermanent(!AdminToken2Enrollment::save($login, 'incorreto', 'novo-segredo-proprio-123456789', 'novo-segredo-proprio-123456789'), 'troca recusa Token 2 anterior errado'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertPermanent.
$changed = 'novo-segredo-proprio-123456789'; // Explicação DevIN: Atribui um valor à variável PHP $changed.
assertPermanent(AdminToken2Enrollment::save($login, $chosen, $changed, $changed), 'próprio ADM troca quando quiser'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertPermanent.
assertPermanent(!AdminStepUp::allowed('site') && !AdminStepUp::allowed('ai'), 'troca revoga ambos os grants'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertPermanent.
assertPermanent(!AdminStepUp::verifySite($chosen), 'Token 2 antigo revogado'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertPermanent.
assertPermanent(AdminStepUp::verifySite($changed), 'Token 2 novo válido'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertPermanent.
$_SESSION['usuario_id'] = 402; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
assertPermanent(!AdminToken2Enrollment::configured(), 'conta diferente permanece sem senha'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertPermanent.
assertPermanent(!AdminToken2Enrollment::save('senha-inválida', null, 'individual-402-seguro-123456', 'individual-402-seguro-123456'), 'cadastro exige Token 1'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertPermanent.
assertPermanent(!AdminToken2Enrollment::save($login, null, $changed, $changed), 'outro ADM não pode escolher chave igual'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertPermanent.
assertPermanent(AdminToken2Enrollment::save($login, null, 'individual-402-seguro-123456', 'individual-402-seguro-123456'), 'outro ADM cadastra própria senha'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertPermanent.
$_SESSION['usuario_id'] = 401; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
assertPermanent(AdminStepUp::verifySite($changed), 'credencial persistiu entre utilizações'); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertPermanent.
session_destroy(); // Explicação DevIN: Continua expressão ou bloco pertencente a função assertPermanent.
echo "ADM Token 2 permanente: OK\n"; // Explicação DevIN: Produz saída ou conteúdo da resposta do programa.
