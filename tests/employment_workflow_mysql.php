<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

/**
 * Testes de contratação somente em banco descartável de CI.
 * Valida propriedade da empresa, aprovação prévia, concordância da pessoa,
 * idempotência, preservação das candidaturas e retirada opcional.
 */
if (getenv('HIRING_TEST_DB') !== 'devin_hiring_ci') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    throw new RuntimeException('Banco de teste de contratação não autorizado.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
$db=new mysqli(getenv('HIRING_TEST_HOST') ?: '127.0.0.1', // Explicação DevIN: Atribui um valor à variável PHP $db.
    getenv('HIRING_TEST_USER') ?: 'root', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    getenv('HIRING_TEST_PASSWORD') ?: '', 'devin_hiring_ci'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
$db->set_charset('utf8mb4'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
require_once dirname(__DIR__) . '/php/controllers/EmploymentWorkflow.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
function hiringAssert(bool $ok, string $description): void // Explicação DevIN: Declara função/método hiringAssert, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if (!$ok) throw new RuntimeException('Hiring flow: '.$description); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
function hiringReject(callable $fn, string $description): void // Explicação DevIN: Declara função/método hiringReject, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    try { $fn(); } catch (DomainException|InvalidArgumentException $expected) { return; } // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    throw new RuntimeException('Invalid action accepted: '.$description); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$db->query('CREATE TABLE empresa(id_empresa INT PRIMARY KEY,nome VARCHAR(60))'); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
$db->query('CREATE TABLE pessoa(id_pessoa INT PRIMARY KEY,nome VARCHAR(60))'); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
$db->query('CREATE TABLE vagas(id_vaga INT PRIMARY KEY,id_empresa INT NOT NULL)'); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
$db->query('CREATE TABLE candidatura(id_candidatura INT PRIMARY KEY,id_pessoa INT NOT NULL,
    id_vaga INT NOT NULL,status VARCHAR(24) NOT NULL)'); // Explicação DevIN: Continua expressão ou bloco pertencente a função hiringReject.
$db->query("INSERT INTO empresa VALUES(1,'Empresa Alfa'),(2,'Empresa Beta')"); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
$db->query("INSERT INTO pessoa VALUES(20,'Pessoa Teste')"); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
$db->query("INSERT INTO vagas VALUES(101,1),(102,2),(103,2),(104,1)"); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
$db->query("INSERT INTO candidatura VALUES(1,20,101,'aprovado'),(2,20,102,'pendente'),
    (3,20,103,'aprovado'),(4,20,104,'pendente')"); // Explicação DevIN: Continua expressão ou bloco pertencente a função hiringReject.
EmploymentWorkflow::ensureInstalled($db); // Explicação DevIN: Continua expressão ou bloco pertencente a função hiringReject.
hiringReject(static fn()=>EmploymentWorkflow::employerConfirm($db,2,1),'outra empresa não pode confirmar'); // Explicação DevIN: Continua expressão ou bloco pertencente a função hiringReject.
hiringReject(static fn()=>EmploymentWorkflow::employerConfirm($db,1,4),'não pode confirmar pendente'); // Explicação DevIN: Continua expressão ou bloco pertencente a função hiringReject.
hiringReject(static fn()=>EmploymentWorkflow::candidateConfirm($db,20,1,true),'empresa deve confirmar primeiro'); // Explicação DevIN: Continua expressão ou bloco pertencente a função hiringReject.
EmploymentWorkflow::employerConfirm($db,1,1); // Explicação DevIN: Continua expressão ou bloco pertencente a função hiringReject.
EmploymentWorkflow::employerConfirm($db,1,1); // Explicação DevIN: Continua expressão ou bloco pertencente a função hiringReject.
hiringReject(static fn()=>EmploymentWorkflow::candidateConfirm($db,21,1,true),'outra pessoa não pode confirmar'); // Explicação DevIN: Continua expressão ou bloco pertencente a função hiringReject.
$withdrawals=EmploymentWorkflow::candidateConfirm($db,20,1,true); // Explicação DevIN: Atribui um valor à variável PHP $withdrawals.
hiringAssert($withdrawals===2,'retirar exatamente as duas pendentes'); // Explicação DevIN: Continua expressão ou bloco pertencente a função hiringReject.
hiringAssert(EmploymentWorkflow::candidateConfirm($db,20,1,true)===0,'reenvio idempotente'); // Explicação DevIN: Continua expressão ou bloco pertencente a função hiringReject.
hiringAssert((int)$db->query('SELECT COUNT(*) FROM candidatura')->fetch_row()[0]===4, // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
    'nenhuma candidatura deve ser excluída'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
hiringAssert((int)$db->query('SELECT COUNT(*) FROM devin_application_withdrawals')->fetch_row()[0]===2, // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
    'duas retiradas com histórico'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
hiringAssert((int)$db->query('SELECT COUNT(*) FROM devin_application_withdrawals WHERE application_id=3')->fetch_row()[0]===0, // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
    'candidatura aprovada em outra empresa não deve ser retirada'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
hiringAssert((int)$db->query('SELECT COUNT(*) FROM devin_employment_confirmations
    WHERE employer_confirmed_at IS NOT NULL AND candidate_confirmed_at IS NOT NULL')->fetch_row()[0]===1, // Explicação DevIN: Continua expressão ou bloco pertencente a função hiringReject.
    'exigir ambas as confirmações'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
$db->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função hiringReject.
echo "DevIN employment workflow: OK\n"; // Explicação DevIN: Produz saída ou conteúdo da resposta do programa.
