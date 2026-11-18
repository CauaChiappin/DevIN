<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * Integração: alteração atômica de e-mail nos perfis de pessoa, empresa e ADM.
 * Nunca usar em Hostinger nem no banco de produção: este arquivo cria tabelas.
 */
if (getenv('PROFILE_TEST_DB') !== 'devin_profile_ci') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    throw new RuntimeException('Banco de teste de perfil não autorizado.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$env = [ // Explicação DevIN: Atribui um valor à variável PHP $env.
    'DB_HOST' => getenv('PROFILE_TEST_HOST') ?: '127.0.0.1', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'DB_USER' => getenv('PROFILE_TEST_USER') ?: 'root', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'DB_PASS' => getenv('PROFILE_TEST_PASSWORD') ?: '', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'DB_NAME' => 'devin_profile_ci', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'APP_ENV' => 'development', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'DEVIN_JWT_SECRET' => str_repeat('profile-test-', 6), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
foreach ($env as $key => $value) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
    putenv($key . '=' . $value); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    $_ENV[$key] = $value; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    $_SERVER[$key] = $value; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
require_once dirname(__DIR__) . '/php/controllers/ProfileController.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once dirname(__DIR__) . '/php/controllers/AuthController.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
$db = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $db.
$db->query('DROP TABLE IF EXISTS pessoa'); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
$db->query('DROP TABLE IF EXISTS empresa'); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
$db->query('DROP TABLE IF EXISTS administrador'); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
foreach (['pessoa' => 'id_pessoa', 'empresa' => 'id_empresa', 'administrador' => 'id_administrador'] as $table => $pk) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
    $db->query("CREATE TABLE {$table} (
        {$pk} INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(140) NOT NULL,
        email VARCHAR(120) NOT NULL UNIQUE,
        senha_hash VARCHAR(255) NOT NULL,
        cep VARCHAR(8) DEFAULT NULL,
        telefone VARCHAR(11) DEFAULT NULL,
        foto VARCHAR(255) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$hash = password_hash('senha-de-teste-2026', PASSWORD_DEFAULT); // Explicação DevIN: Executa operação criptográfica de senha ou comparação protegida.
function seedProfile(mysqli $db, string $table, string $name, string $email, string $hash): void // Explicação DevIN: Declara função/método seedProfile, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    $stmt = $db->prepare("INSERT INTO {$table} (nome,email,senha_hash,cep,telefone) VALUES (?, ?, ?, '01234567', '11999999999')"); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
    $stmt->bind_param('sss', $name, $email, $hash); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
    $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
    $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função seedProfile.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
function assertProfile(bool $condition, string $message): void // Explicação DevIN: Declara função/método assertProfile, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if (!$condition) throw new RuntimeException('Falha no teste de e-mail: ' . $message); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
function expectInvalid(callable $operation, string $part): void // Explicação DevIN: Declara função/método expectInvalid, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    try { $operation(); } catch (InvalidArgumentException $error) { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        assertProfile(str_contains($error->getMessage(), $part), 'Mensagem incorreta: ' . $part); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectInvalid.
        return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    throw new RuntimeException('A alteração deveria ter sido bloqueada: ' . $part); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
seedProfile($db, 'pessoa', 'Pessoa inicial', 'primeira@example.test', $hash); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectInvalid.
seedProfile($db, 'empresa', 'Empresa inicial', 'Company@Example.test', $hash); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectInvalid.
seedProfile($db, 'administrador', 'Admin inicial', 'admin@example.test', $hash); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectInvalid.
$db->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectInvalid.
$base = ['nome'=>'Pessoa nova', 'email'=>'pessoa.nova@example.test', 'cep'=>'01234567', 'telefone'=>'11999999999']; // Explicação DevIN: Atribui um valor à variável PHP $base.
expectInvalid(fn() => updateProfile('pessoa', 1, $base), 'senha atual'); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectInvalid.
assertProfile(findProfile('pessoa', 1)['email'] === 'primeira@example.test', 'Email alterado sem autenticação'); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectInvalid.
assertProfile(findProfile('pessoa', 1)['nome'] === 'Pessoa inicial', 'Nome alterado apesar da falha'); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectInvalid.
expectInvalid(fn() => updateProfile('pessoa', 1, $base + ['senha_atual'=>'incorreta']), 'senha atual'); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectInvalid.
$duplicate = $base; // Explicação DevIN: Atribui um valor à variável PHP $duplicate.
$duplicate['email'] = 'company@example.test'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$duplicate['senha_atual'] = 'senha-de-teste-2026'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
expectInvalid(fn() => updateProfile('pessoa', 1, $duplicate), 'já está em uso'); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectInvalid.
assertProfile(findProfile('pessoa', 1)['email'] === 'primeira@example.test', 'Duplicidade alterou identidade'); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectInvalid.
$base['senha_atual'] = 'senha-de-teste-2026'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$updated = updateProfile('pessoa', 1, $base); // Explicação DevIN: Atribui um valor à variável PHP $updated.
assertProfile($updated['email_alterado'] && $updated['email'] === $base['email'], 'Resposta não indica novo e-mail'); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectInvalid.
assertProfile(findProfile('pessoa', 1)['email'] === $base['email'], 'Login salvo incorretamente'); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectInvalid.
$login = AuthController::login($base['email'], 'senha-de-teste-2026'); // Explicação DevIN: Atribui um valor à variável PHP $login.
assertProfile($login['usuario']['id'] === 1 && $login['usuario']['tipo'] === 'pessoa', 'Login não encontra novo e-mail'); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectInvalid.
$editName = $base; // Explicação DevIN: Atribui um valor à variável PHP $editName.
$editName['nome'] = 'Pessoa apenas nome'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
unset($editName['senha_atual']); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectInvalid.
$ok = updateProfile('pessoa', 1, $editName); // Explicação DevIN: Atribui um valor à variável PHP $ok.
assertProfile(!$ok['email_alterado'] && findProfile('pessoa', 1)['nome'] === 'Pessoa apenas nome', 'Edição comum deveria funcionar'); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectInvalid.
$tooLong = $base; // Explicação DevIN: Atribui um valor à variável PHP $tooLong.
$tooLong['email'] = str_repeat('a', 55) . '@' . str_repeat('b', 55) . '.' . str_repeat('c', 45) . '.com'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
expectInvalid(fn() => updateProfile('pessoa', 1, $tooLong), 'tamanho permitido'); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectInvalid.
assertProfile(findProfile('pessoa', 1)['email'] === $base['email'], 'E-mail inválido afetou login'); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectInvalid.
$companyChange = ['nome'=>'Empresa nova','email'=>'EMPRESA.NOVA@EXAMPLE.TEST','cep'=>'01234567','telefone'=>'11999999999','senha_atual'=>'senha-de-teste-2026']; // Explicação DevIN: Atribui um valor à variável PHP $companyChange.
$company = updateProfile('empresa', 1, $companyChange); // Explicação DevIN: Atribui um valor à variável PHP $company.
assertProfile($company['email'] === 'empresa.nova@example.test', 'E-mail da empresa não normalizado'); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectInvalid.
assertProfile(findProfile('empresa', 1)['nome'] === 'Empresa nova', 'Nome da empresa não foi gravado'); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectInvalid.
$admin = updateProfile('adm', 1, ['nome'=>'Admin novo','email'=>'admin.novo@example.test','senha_atual'=>'senha-de-teste-2026']); // Explicação DevIN: Atribui um valor à variável PHP $admin.
assertProfile($admin['email_alterado'] && findProfile('adm', 1)['email'] === 'admin.novo@example.test', 'E-mail ADM não foi persistido'); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectInvalid.
echo "OK: transação de perfil, senha atual, duplicidade entre contas, limite SQL, sessão e login.\n"; // Explicação DevIN: Produz saída ou conteúdo da resposta do programa.
