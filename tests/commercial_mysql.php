<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

/**
 * Integração do catálogo e quotas comerciais em MySQL 8 descartável.
 * Nunca executar em banco de produção; a variável de nome é obrigatória.
 */
if (getenv('COMMERCIAL_TEST_DB')!=='devin_commercial_ci') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    throw new RuntimeException('Banco comercial de teste inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
$db=new mysqli(getenv('COMMERCIAL_TEST_HOST') ?: '127.0.0.1', // Explicação DevIN: Atribui um valor à variável PHP $db.
    getenv('COMMERCIAL_TEST_USER') ?: 'root', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    getenv('COMMERCIAL_TEST_PASSWORD') ?: '', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    'devin_commercial_ci'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
$db->set_charset('utf8mb4'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
require_once dirname(__DIR__).'/php/controllers/CommercialPlanCatalog.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once dirname(__DIR__).'/php/controllers/CommercialEntitlements.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

function assertCommercial(bool $ok, string $message): void // Explicação DevIN: Declara função/método assertCommercial, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if (!$ok) throw new RuntimeException('Commercial SQL: '.$message); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
function expectDenied(callable $callback, string $message): void // Explicação DevIN: Declara função/método expectDenied, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    try { $callback(); } // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    catch (InvalidArgumentException|DomainException $expected) { return; } // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    throw new RuntimeException('Unexpectedly allowed: '.$message); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
assertCommercial(count(CommercialPlanCatalog::all($db))===6,'seis planos rascunho'); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectDenied.
assertCommercial(count(CommercialPlanCatalog::subscribers($db))===0,'sem assinaturas automáticas'); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectDenied.
$plans=CommercialPlanCatalog::all($db); // Explicação DevIN: Atribui um valor à variável PHP $plans.
assertCommercial(count(array_filter($plans,static fn($p)=>(int)$p['is_published']!==0))===0, // Explicação DevIN: Continua expressão ou bloco pertencente a função expectDenied.
    'nenhum plano pode ser publicado por padrão'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
assertCommercial(count(array_filter($plans,static fn($p)=>$p['monthly_price_cents']!==null))===0, // Explicação DevIN: Continua expressão ou bloco pertencente a função expectDenied.
    'nenhum preço deve ser cobrado por padrão'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
$plan=$plans[0]; // Explicação DevIN: Atribui um valor à variável PHP $plan.
$id=(int)$plan['id']; // Explicação DevIN: Atribui um valor à variável PHP $id.
$rev=(int)$plan['revision']; // Explicação DevIN: Atribui um valor à variável PHP $rev.
$edited=CommercialPlanCatalog::updateDraft($db,7,$id,$rev, // Explicação DevIN: Atribui um valor à variável PHP $edited.
    ['title'=>'Plano em teste','monthly_price_cents'=>2990,'stock'=>150]); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectDenied.
assertCommercial($edited['revision']===$rev+1,'revisão otimista incrementada'); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectDenied.
assertCommercial(CommercialPlanCatalog::all($db)[0]['title']==='Plano em teste', // Explicação DevIN: Continua expressão ou bloco pertencente a função expectDenied.
    'alteração da conta administrativa persistida'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
assertCommercial((int)$db->query('SELECT COUNT(*) FROM devin_billing_plan_audit')->fetch_row()[0]===1, // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
    'trilha de auditoria armazenada'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
expectDenied(static fn()=>CommercialPlanCatalog::updateDraft($db,7,$id,$rev, // Explicação DevIN: Continua expressão ou bloco pertencente a função expectDenied.
    ['title'=>'edição vencida']),'revisão obsoleta'); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectDenied.
expectDenied(static fn()=>CommercialPlanCatalog::updateDraft($db,7,$id,$rev+1, // Explicação DevIN: Continua expressão ou bloco pertencente a função expectDenied.
    ['is_published'=>1]),'publicação manual proibida'); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectDenied.
expectDenied(static fn()=>CommercialPlanCatalog::updateDraft($db,7,$id,$rev+1, // Explicação DevIN: Continua expressão ou bloco pertencente a função expectDenied.
    ['placement_fee_bps'=>501]),'taxa máxima 5%'); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectDenied.
$db->query('CREATE TABLE vagas(id_vaga INT PRIMARY KEY,id_empresa INT,encerrada_em DATETIME NULL)'); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
$db->query('CREATE TABLE candidatura(id_candidatura INT PRIMARY KEY,id_pessoa INT,
    data_candidatura DATE NOT NULL)'); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectDenied.
$db->query('INSERT INTO vagas VALUES(101,20,NULL),(102,20,NULL)'); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
$db->query('INSERT INTO candidatura VALUES(1,5,CURDATE())'); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
putenv('DEVIN_MONETIZATION_ENABLED=1'); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectDenied.
assertCommercial(CommercialEntitlements::enabled(),'ativação explícita de CI'); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectDenied.
assertCommercial(CommercialEntitlements::forAccount($db,'empresa',20)['max_active_jobs']===2, // Explicação DevIN: Continua expressão ou bloco pertencente a função expectDenied.
    'sem assinatura ativa mantém apenas cota gratuita'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
expectDenied(static fn()=>CommercialEntitlements::assertMayPostJob($db,20), // Explicação DevIN: Continua expressão ou bloco pertencente a função expectDenied.
    'empresa gratuita com duas vagas ativas'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
assertCommercial(CommercialEntitlements::forAccount($db,'pessoa',5)['max_applications_month']===15, // Explicação DevIN: Continua expressão ou bloco pertencente a função expectDenied.
    'pessoa gratuita deve ter 15 candidaturas mensais conforme decisão aprovada'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
assertCommercial(CommercialEntitlements::forAccount($db,'pessoa',5)['auto_apply']===false, // Explicação DevIN: Continua expressão ou bloco pertencente a função expectDenied.
    'inscrição automática nunca habilitada por tabela'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
$db->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectDenied.
echo "DevIN commercial SQL: OK\n"; // Explicação DevIN: Produz saída ou conteúdo da resposta do programa.
