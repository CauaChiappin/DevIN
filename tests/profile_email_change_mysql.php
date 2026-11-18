<?php // Explicação DevIN: Executa regressões do e-mail em banco descartável da CI.
declare(strict_types=1); // Explicação DevIN: Mantém a validação dos dados de teste estrita.
/**
 * Integração: alteração atômica de e-mail nos perfis de pessoa, empresa e ADM.
 * Nunca usar em Hostinger nem no banco de produção: este arquivo cria tabelas.
 */
if (getenv('PROFILE_TEST_DB') !== 'devin_profile_ci') { // Explicação DevIN: Só permite execução no banco isolado de testes.
    throw new RuntimeException('Banco de teste de perfil não autorizado.'); // Explicação DevIN: Evita apagar informações de produção.
} // Explicação DevIN: Finaliza bloqueio preventivo.
$env = [ // Explicação DevIN: Configura somente o MySQL descartável.
    'DB_HOST' => getenv('PROFILE_TEST_HOST') ?: '127.0.0.1', // Explicação DevIN: Usa localhost no ambiente CI.
    'DB_USER' => getenv('PROFILE_TEST_USER') ?: 'root', // Explicação DevIN: Usa usuário do serviço de teste.
    'DB_PASS' => getenv('PROFILE_TEST_PASSWORD') ?: '', // Explicação DevIN: Lê a senha fictícia do banco de teste.
    'DB_NAME' => 'devin_profile_ci', // Explicação DevIN: Impede seleção de outro schema.
    'APP_ENV' => 'development', // Explicação DevIN: Habilita execução sem arquivo privado.
    'DEVIN_JWT_SECRET' => str_repeat('profile-test-', 6), // Explicação DevIN: Usa segredo exclusivo da CI sem impacto no site.
]; // Explicação DevIN: Fecha os parâmetros do ambiente.
foreach ($env as $key => $value) { // Explicação DevIN: Ajusta os valores antes de incluir os controladores.
    putenv($key . '=' . $value); // Explicação DevIN: Configura resolução por getenv.
    $_ENV[$key] = $value; // Explicação DevIN: Configura resolução pelo Dotenv.
    $_SERVER[$key] = $value; // Explicação DevIN: Evita misturar variáveis de teste e produção.
} // Explicação DevIN: Termina a configuração.
require_once dirname(__DIR__) . '/php/controllers/ProfileController.php'; // Explicação DevIN: Testa controlador real.
require_once dirname(__DIR__) . '/php/controllers/AuthController.php'; // Explicação DevIN: Testa login real usando o novo e-mail.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Explicação DevIN: Trata falhas SQL como exceções.
$db = getDatabaseConnection(); // Explicação DevIN: Abre somente o banco de teste autorizado.
$db->query('DROP TABLE IF EXISTS pessoa'); // Explicação DevIN: Limpa dados fictícios do teste anterior.
$db->query('DROP TABLE IF EXISTS empresa'); // Explicação DevIN: Limpa empresas fictícias do teste anterior.
$db->query('DROP TABLE IF EXISTS administrador'); // Explicação DevIN: Limpa administradores fictícios do teste anterior.
foreach (['pessoa' => 'id_pessoa', 'empresa' => 'id_empresa', 'administrador' => 'id_administrador'] as $table => $pk) { // Explicação DevIN: Recria apenas as três identidades do teste.
    $db->query("CREATE TABLE {$table} (
        {$pk} INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(140) NOT NULL,
        email VARCHAR(120) NOT NULL UNIQUE,
        senha_hash VARCHAR(255) NOT NULL,
        cep VARCHAR(8) DEFAULT NULL,
        telefone VARCHAR(11) DEFAULT NULL,
        foto VARCHAR(255) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"); // Explicação DevIN: Replica as colunas necessárias ao fluxo.
} // Explicação DevIN: Finaliza a criação descartável.
$hash = password_hash('senha-de-teste-2026', PASSWORD_DEFAULT); // Explicação DevIN: Exercita a confirmação real de senha.
function seedProfile(mysqli $db, string $table, string $name, string $email, string $hash): void // Explicação DevIN: Insere identidades fictícias.
{ // Explicação DevIN: Inicia os dados de uma conta.
    $stmt = $db->prepare("INSERT INTO {$table} (nome,email,senha_hash,cep,telefone) VALUES (?, ?, ?, '01234567', '11999999999')"); // Explicação DevIN: Usa apenas tabela escolhida no teste.
    $stmt->bind_param('sss', $name, $email, $hash); // Explicação DevIN: Parametriza nome, email e hash.
    $stmt->execute(); // Explicação DevIN: Registra conta fictícia.
    $stmt->close(); // Explicação DevIN: Fecha o comando de inserção.
} // Explicação DevIN: Finaliza a conta fictícia.
function assertProfile(bool $condition, string $message): void // Explicação DevIN: Falha caso a operação tenha alterado dados incorretamente.
{ // Explicação DevIN: Inicia validação.
    if (!$condition) throw new RuntimeException('Falha no teste de e-mail: ' . $message); // Explicação DevIN: Interrompe regressões de identidade.
} // Explicação DevIN: Conclui a validação.
function expectInvalid(callable $operation, string $part): void // Explicação DevIN: Exige erro conhecido para alteração recusada.
{ // Explicação DevIN: Inicia teste de rejeição.
    try { $operation(); } catch (InvalidArgumentException $error) { // Explicação DevIN: Confirma que validações falham de maneira segura.
        assertProfile(str_contains($error->getMessage(), $part), 'Mensagem incorreta: ' . $part); // Explicação DevIN: Verifica motivo da recusa.
        return; // Explicação DevIN: Confirma o comportamento esperado.
    } // Explicação DevIN: Finaliza captura da falha esperada.
    throw new RuntimeException('A alteração deveria ter sido bloqueada: ' . $part); // Explicação DevIN: Falha quando operação insegura foi aceita.
} // Explicação DevIN: Finaliza teste de rejeição.
seedProfile($db, 'pessoa', 'Pessoa inicial', 'primeira@example.test', $hash); // Explicação DevIN: Cria candidato fictício.
seedProfile($db, 'empresa', 'Empresa inicial', 'Company@Example.test', $hash); // Explicação DevIN: Cria empresa com letras maiúsculas.
seedProfile($db, 'administrador', 'Admin inicial', 'admin@example.test', $hash); // Explicação DevIN: Cria ADM fictício.
$db->close(); // Explicação DevIN: O controlador deve abrir e fechar suas próprias conexões.
$base = ['nome'=>'Pessoa nova', 'email'=>'pessoa.nova@example.test', 'cep'=>'01234567', 'telefone'=>'11999999999']; // Explicação DevIN: Define uma edição válida sem foto.
expectInvalid(fn() => updateProfile('pessoa', 1, $base), 'senha atual'); // Explicação DevIN: Não permite alterar login sem senha.
assertProfile(findProfile('pessoa', 1)['email'] === 'primeira@example.test', 'Email alterado sem autenticação'); // Explicação DevIN: Verifica rollback da tentativa.
assertProfile(findProfile('pessoa', 1)['nome'] === 'Pessoa inicial', 'Nome alterado apesar da falha'); // Explicação DevIN: Confirma que nenhum campo foi gravado.
expectInvalid(fn() => updateProfile('pessoa', 1, $base + ['senha_atual'=>'incorreta']), 'senha atual'); // Explicação DevIN: Bloqueia senha errada.
$duplicate = $base; // Explicação DevIN: Prepara endereço já usado por outra categoria.
$duplicate['email'] = 'company@example.test'; // Explicação DevIN: Força colisão com empresa ignorando maiúsculas.
$duplicate['senha_atual'] = 'senha-de-teste-2026'; // Explicação DevIN: Usa senha correta para atingir a unicidade.
expectInvalid(fn() => updateProfile('pessoa', 1, $duplicate), 'já está em uso'); // Explicação DevIN: Recusa e-mail pertencente à empresa.
assertProfile(findProfile('pessoa', 1)['email'] === 'primeira@example.test', 'Duplicidade alterou identidade'); // Explicação DevIN: Testa isolamento entre tabelas.
$base['senha_atual'] = 'senha-de-teste-2026'; // Explicação DevIN: Autoriza mudança com a senha atual.
$updated = updateProfile('pessoa', 1, $base); // Explicação DevIN: Altera pessoa e confirma gravação.
assertProfile($updated['email_alterado'] && $updated['email'] === $base['email'], 'Resposta não indica novo e-mail'); // Explicação DevIN: Verifica dados retornados para a sessão.
assertProfile(findProfile('pessoa', 1)['email'] === $base['email'], 'Login salvo incorretamente'); // Explicação DevIN: Confere valor efetivamente armazenado.
$login = AuthController::login($base['email'], 'senha-de-teste-2026'); // Explicação DevIN: Exercita o login com o novo endereço.
assertProfile($login['usuario']['id'] === 1 && $login['usuario']['tipo'] === 'pessoa', 'Login não encontra novo e-mail'); // Explicação DevIN: Confirma autenticação posterior.
$editName = $base; // Explicação DevIN: Inicia edição que mantém o endereço existente.
$editName['nome'] = 'Pessoa apenas nome'; // Explicação DevIN: Altera somente nome.
unset($editName['senha_atual']); // Explicação DevIN: Não exige senha quando o e-mail permanece.
$ok = updateProfile('pessoa', 1, $editName); // Explicação DevIN: Salva nome e telefone normalmente.
assertProfile(!$ok['email_alterado'] && findProfile('pessoa', 1)['nome'] === 'Pessoa apenas nome', 'Edição comum deveria funcionar'); // Explicação DevIN: Preserva edição que não altera login.
$tooLong = $base; // Explicação DevIN: Simula endereço válido maior que VARCHAR(120).
$tooLong['email'] = str_repeat('a', 55) . '@' . str_repeat('b', 55) . '.' . str_repeat('c', 45) . '.com'; // Explicação DevIN: Mantém cada rótulo no limite DNS.
expectInvalid(fn() => updateProfile('pessoa', 1, $tooLong), 'tamanho permitido'); // Explicação DevIN: Evita SQL 1406 e truncamento de email.
assertProfile(findProfile('pessoa', 1)['email'] === $base['email'], 'E-mail inválido afetou login'); // Explicação DevIN: Mantém identidade após validação.
$companyChange = ['nome'=>'Empresa nova','email'=>'EMPRESA.NOVA@EXAMPLE.TEST','cep'=>'01234567','telefone'=>'11999999999','senha_atual'=>'senha-de-teste-2026']; // Explicação DevIN: Exercita perfil da empresa.
$company = updateProfile('empresa', 1, $companyChange); // Explicação DevIN: Grava e-mail da empresa de forma atômica.
assertProfile($company['email'] === 'empresa.nova@example.test', 'E-mail da empresa não normalizado'); // Explicação DevIN: Confere normalização consistente.
assertProfile(findProfile('empresa', 1)['nome'] === 'Empresa nova', 'Nome da empresa não foi gravado'); // Explicação DevIN: Confere retorno para a sessão.
$admin = updateProfile('adm', 1, ['nome'=>'Admin novo','email'=>'admin.novo@example.test','senha_atual'=>'senha-de-teste-2026']); // Explicação DevIN: Testa confirmação de senha para ADM.
assertProfile($admin['email_alterado'] && findProfile('adm', 1)['email'] === 'admin.novo@example.test', 'E-mail ADM não foi persistido'); // Explicação DevIN: Confere terceiro tipo de usuário.
echo "OK: transação de perfil, senha atual, duplicidade entre contas, limite SQL, sessão e login.\n"; // Explicação DevIN: Informa sucesso no log de testes.
