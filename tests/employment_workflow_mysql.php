<?php
declare(strict_types=1);

/**
 * Testes de contratação somente em banco descartável de CI.
 * Valida propriedade da empresa, aprovação prévia, concordância da pessoa,
 * idempotência, preservação das candidaturas e retirada opcional.
 */
if (getenv('HIRING_TEST_DB') !== 'devin_hiring_ci') {
    throw new RuntimeException('Banco de teste de contratação não autorizado.');
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db=new mysqli(getenv('HIRING_TEST_HOST') ?: '127.0.0.1',
    getenv('HIRING_TEST_USER') ?: 'root',
    getenv('HIRING_TEST_PASSWORD') ?: '', 'devin_hiring_ci');
$db->set_charset('utf8mb4');
require_once dirname(__DIR__) . '/php/controllers/EmploymentWorkflow.php';
function hiringAssert(bool $ok, string $description): void
{
    if (!$ok) throw new RuntimeException('Hiring flow: '.$description);
}
function hiringReject(callable $fn, string $description): void
{
    try { $fn(); } catch (DomainException|InvalidArgumentException $expected) { return; }
    throw new RuntimeException('Invalid action accepted: '.$description);
}
$db->query('CREATE TABLE empresa(id_empresa INT PRIMARY KEY,nome VARCHAR(60))');
$db->query('CREATE TABLE pessoa(id_pessoa INT PRIMARY KEY,nome VARCHAR(60))');
$db->query('CREATE TABLE vagas(id_vaga INT PRIMARY KEY,id_empresa INT NOT NULL)');
$db->query('CREATE TABLE candidatura(id_candidatura INT PRIMARY KEY,id_pessoa INT NOT NULL,
    id_vaga INT NOT NULL,status VARCHAR(24) NOT NULL)');
$db->query("INSERT INTO empresa VALUES(1,'Empresa Alfa'),(2,'Empresa Beta')");
$db->query("INSERT INTO pessoa VALUES(20,'Pessoa Teste')");
$db->query("INSERT INTO vagas VALUES(101,1),(102,2),(103,2),(104,1)");
$db->query("INSERT INTO candidatura VALUES(1,20,101,'aprovado'),(2,20,102,'pendente'),
    (3,20,103,'aprovado'),(4,20,104,'pendente')");
EmploymentWorkflow::ensureInstalled($db);
hiringReject(static fn()=>EmploymentWorkflow::employerConfirm($db,2,1),'outra empresa não pode confirmar');
hiringReject(static fn()=>EmploymentWorkflow::employerConfirm($db,1,4),'não pode confirmar pendente');
hiringReject(static fn()=>EmploymentWorkflow::candidateConfirm($db,20,1,true),'empresa deve confirmar primeiro');
EmploymentWorkflow::employerConfirm($db,1,1);
EmploymentWorkflow::employerConfirm($db,1,1);
hiringReject(static fn()=>EmploymentWorkflow::candidateConfirm($db,21,1,true),'outra pessoa não pode confirmar');
$withdrawals=EmploymentWorkflow::candidateConfirm($db,20,1,true);
hiringAssert($withdrawals===2,'retirar exatamente as duas pendentes');
hiringAssert(EmploymentWorkflow::candidateConfirm($db,20,1,true)===0,'reenvio idempotente');
hiringAssert((int)$db->query('SELECT COUNT(*) FROM candidatura')->fetch_row()[0]===4,
    'nenhuma candidatura deve ser excluída');
hiringAssert((int)$db->query('SELECT COUNT(*) FROM devin_application_withdrawals')->fetch_row()[0]===2,
    'duas retiradas com histórico');
hiringAssert((int)$db->query('SELECT COUNT(*) FROM devin_application_withdrawals WHERE application_id=3')->fetch_row()[0]===0,
    'candidatura aprovada em outra empresa não deve ser retirada');
hiringAssert((int)$db->query('SELECT COUNT(*) FROM devin_employment_confirmations
    WHERE employer_confirmed_at IS NOT NULL AND candidate_confirmed_at IS NOT NULL')->fetch_row()[0]===1,
    'exigir ambas as confirmações');
$db->close();
echo "DevIN employment workflow: OK\n";
