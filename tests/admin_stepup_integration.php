<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — tests/admin_stepup_integration.php
 * Finalidade: admin stepup integration.
 * Domínio: testes e prevenção de regressões.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


if (getenv('LGPD_TEST_DB') !== 'devin_lgpd_test') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    throw new RuntimeException('Somente banco descartável de CI.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$_ENV['APP_ENV'] = 'development'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_ENV['DEVIN_JWT_SECRET'] = 'not-for-real-use-token-test-secret'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_ENV['DB_HOST'] = getenv('LGPD_TEST_HOST') ?: '127.0.0.1'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_ENV['DB_PORT'] = '3306'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_ENV['DB_NAME'] = 'devin_lgpd_test'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_ENV['DB_USER'] = getenv('LGPD_TEST_USER') ?: 'root'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_ENV['DB_PASS'] = getenv('LGPD_TEST_PASSWORD') ?: ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_ENV['DEVIN_ADMIN_STEPUP_ENABLED'] = '1'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_SERVER['REMOTE_ADDR'] = '192.0.2.25'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.

require_once dirname(__DIR__) . '/php/controllers/Security/AdminStepUp.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once dirname(__DIR__) . '/php/controllers/Security/AdminGate.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

// Detalhe DevIN: Método stepCheck: encapsula uma responsabilidade reutilizável do módulo no contexto de admin stepup integration. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function stepCheck(bool $condition, string $label): void { // Explicação DevIN: Declara função/método stepCheck, com seus parâmetros e comportamento.
    if (!$condition) throw new RuntimeException('ADM step-up: ' . $label); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$db = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $db.
$login = 'login@' . bin2hex(random_bytes(8)); // Explicação DevIN: Atribui um valor à variável PHP $login.
$loginHash = password_hash($login, PASSWORD_DEFAULT); // Explicação DevIN: Executa operação criptográfica de senha ou comparação protegida.
// Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
$stmt = $db->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
    'INSERT INTO administrador (id_administrador,senha_hash,token2_hash,token2_version) ' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    . 'VALUES (?,?,?,1)' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$keys = []; // Explicação DevIN: Atribui um valor à variável PHP $keys.
foreach ([311,312,313,314] as $id) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
    $keys[$id] = 'secondary-' . $id . '-@' . bin2hex(random_bytes(12)); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    $hash2 = AdminToken2Hasher::create($keys[$id]); // Explicação DevIN: Atribui um valor à variável PHP $hash2.
    $stmt->bind_param('iss', $id, $loginHash, $hash2); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
    $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função stepCheck.
$db->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função stepCheck.

startSecureSession(); // Explicação DevIN: Continua expressão ou bloco pertencente a função stepCheck.
$_SESSION['logado'] = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_SESSION['usuario_tipo'] = 'adm'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_SESSION['usuario_id'] = 311; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_SESSION['auth_time'] = time(); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_SESSION['last_activity'] = time(); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.

stepCheck(!AdminStepUp::allowed('site') && !AdminStepUp::allowed('ai'), 'sem sessão liberada'); // Explicação DevIN: Continua expressão ou bloco pertencente a função stepCheck.
stepCheck(AdminStepUp::nextAiToken() === 1, 'IA começa pelo Token 1'); // Explicação DevIN: Continua expressão ou bloco pertencente a função stepCheck.
stepCheck(!AdminStepUp::verifyAiSecond($keys[311]), 'não permite segunda etapa antes da primeira'); // Explicação DevIN: Continua expressão ou bloco pertencente a função stepCheck.
stepCheck(!AdminStepUp::verifyAiFirst('senha-errada'), 'rejeita Token 1 inválido'); // Explicação DevIN: Continua expressão ou bloco pertencente a função stepCheck.
stepCheck(AdminStepUp::verifyAiFirst($login), 'aceita Token 1'); // Explicação DevIN: Continua expressão ou bloco pertencente a função stepCheck.
stepCheck(AdminStepUp::nextAiToken() === 2, 'solicita Token 2'); // Explicação DevIN: Continua expressão ou bloco pertencente a função stepCheck.
stepCheck(!AdminStepUp::verifyAiSecond($keys[312]), 'não aceita Token 2 de outro ADM'); // Explicação DevIN: Continua expressão ou bloco pertencente a função stepCheck.
stepCheck(AdminStepUp::verifyAiSecond($keys[311]), 'aceita Token 2 próprio'); // Explicação DevIN: Continua expressão ou bloco pertencente a função stepCheck.
stepCheck(AdminStepUp::allowed('ai') && !AdminStepUp::allowed('site'), 'escopos independentes'); // Explicação DevIN: Continua expressão ou bloco pertencente a função stepCheck.
stepCheck(AdminStepUp::verifySite($keys[311]), 'libera ações do site'); // Explicação DevIN: Continua expressão ou bloco pertencente a função stepCheck.
stepCheck(AdminStepUp::allowed('site'), 'estado persiste na sessão'); // Explicação DevIN: Continua expressão ou bloco pertencente a função stepCheck.
$grant = $_SESSION['admin_stepup_grant_site']; // Explicação DevIN: Atribui um valor à variável PHP $grant.
$_SESSION['admin_stepup_grant_site']['issued_at'] = time()-28801; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
stepCheck(!AdminStepUp::allowed('site'), 'o grant expira em 8 horas sem expirar a senha'); // Explicação DevIN: Continua expressão ou bloco pertencente a função stepCheck.
$_SESSION['admin_stepup_grant_site'] = $grant; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_SESSION['admin_stepup_grant_site']['used_at'] = time()-1801; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
stepCheck(!AdminStepUp::allowed('site'), 'o grant expira após inatividade'); // Explicação DevIN: Continua expressão ou bloco pertencente a função stepCheck.

$db = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $db.
$db->query('UPDATE administrador SET token2_version = token2_version + 1 WHERE id_administrador = 311'); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
$db->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função stepCheck.
stepCheck(!AdminStepUp::allowed('ai'), 'rotação revoga grant da IA'); // Explicação DevIN: Continua expressão ou bloco pertencente a função stepCheck.
stepCheck(AdminStepUp::verifySite($keys[311]), 'senha Token 2 continua válida após expiração do grant'); // Explicação DevIN: Continua expressão ou bloco pertencente a função stepCheck.

$db = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $db.
$newLoginHash = password_hash('nova-senha-da-conta-de-teste', PASSWORD_DEFAULT); // Explicação DevIN: Executa operação criptográfica de senha ou comparação protegida.
// Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
$stmt = $db->prepare('UPDATE administrador SET senha_hash = ? WHERE id_administrador = 311'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
$stmt->bind_param('s', $newLoginHash); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
$stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
$stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função stepCheck.
$db->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função stepCheck.
stepCheck(!AdminStepUp::allowed('site'), 'troca de senha de login revoga grant'); // Explicação DevIN: Continua expressão ou bloco pertencente a função stepCheck.
$_SESSION['usuario_id'] = 312; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
stepCheck(!AdminStepUp::allowed('site') && !AdminStepUp::allowed('ai'), 'grants não passam a outro ADM'); // Explicação DevIN: Continua expressão ou bloco pertencente a função stepCheck.
$_SESSION['usuario_id'] = 311; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
AdminStepUp::clear(); // Explicação DevIN: Continua expressão ou bloco pertencente a função stepCheck.
stepCheck(!isset($_SESSION['admin_stepup_grant_ai']), 'limpeza remove grant da IA'); // Explicação DevIN: Continua expressão ou bloco pertencente a função stepCheck.

$csrf = csrfToken(); // Explicação DevIN: Atribui um valor à variável PHP $csrf.
$_SESSION['admin_stepup_pending'] = [ // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    'admin_id' => 311, 'nonce' => bin2hex(random_bytes(16)), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'created_at' => time(), 'post' => ['action'=>'delete_account','csrf_token'=>$csrf], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'page'=>'perfil', 'category'=>'vagas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
stepCheck(AdminGate::consumePending(312) === null, 'pendência não pode atravessar contas'); // Explicação DevIN: Continua expressão ou bloco pertencente a função stepCheck.
$_SESSION['admin_stepup_pending'] = [ // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    'admin_id' => 311, 'nonce' => bin2hex(random_bytes(16)), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'created_at' => time(), 'post' => ['action'=>'delete_account','csrf_token'=>$csrf], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'page'=>'perfil', 'category'=>'vagas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
stepCheck(AdminGate::consumePending(311) !== null, 'ação própria pode ser retomada'); // Explicação DevIN: Continua expressão ou bloco pertencente a função stepCheck.
stepCheck(AdminGate::consumePending(311) === null, 'ação de uso único'); // Explicação DevIN: Continua expressão ou bloco pertencente a função stepCheck.
$_SESSION['admin_stepup_pending'] = [ // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    'admin_id' => 311, 'nonce' => bin2hex(random_bytes(16)), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'created_at' => time()-311, 'post' => ['action'=>'delete_account','csrf_token'=>$csrf], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'page'=>'perfil', 'category'=>'vagas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
stepCheck(AdminGate::consumePending(311) === null, 'pendência expirada negada'); // Explicação DevIN: Continua expressão ou bloco pertencente a função stepCheck.
session_destroy(); // Explicação DevIN: Continua expressão ou bloco pertencente a função stepCheck.
echo "ADM step-up MySQL: OK\n"; // Explicação DevIN: Produz saída ou conteúdo da resposta do programa.
