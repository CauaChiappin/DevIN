<?php
declare(strict_types=1);

/**
 * Integração do catálogo e quotas comerciais em MySQL 8 descartável.
 * Nunca executar em banco de produção; a variável de nome é obrigatória.
 */
if (getenv('COMMERCIAL_TEST_DB')!=='devin_commercial_ci') {
    throw new RuntimeException('Banco comercial de teste inválido.');
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db=new mysqli(getenv('COMMERCIAL_TEST_HOST') ?: '127.0.0.1',
    getenv('COMMERCIAL_TEST_USER') ?: 'root',
    getenv('COMMERCIAL_TEST_PASSWORD') ?: '',
    'devin_commercial_ci');
$db->set_charset('utf8mb4');
require_once dirname(__DIR__).'/php/controllers/CommercialPlanCatalog.php';
require_once dirname(__DIR__).'/php/controllers/CommercialEntitlements.php';

function assertCommercial(bool $ok, string $message): void
{
    if (!$ok) throw new RuntimeException('Commercial SQL: '.$message);
}
function expectDenied(callable $callback, string $message): void
{
    try { $callback(); }
    catch (InvalidArgumentException|DomainException $expected) { return; }
    throw new RuntimeException('Unexpectedly allowed: '.$message);
}
assertCommercial(count(CommercialPlanCatalog::all($db))===6,'seis planos rascunho');
assertCommercial(count(CommercialPlanCatalog::subscribers($db))===0,'sem assinaturas automáticas');
$plans=CommercialPlanCatalog::all($db);
assertCommercial(count(array_filter($plans,static fn($p)=>(int)$p['is_published']!==0))===0,
    'nenhum plano pode ser publicado por padrão');
assertCommercial(count(array_filter($plans,static fn($p)=>$p['monthly_price_cents']!==null))===0,
    'nenhum preço deve ser cobrado por padrão');
$plan=$plans[0];
$id=(int)$plan['id'];
$rev=(int)$plan['revision'];
$edited=CommercialPlanCatalog::updateDraft($db,7,$id,$rev,
    ['title'=>'Plano em teste','monthly_price_cents'=>2990,'stock'=>150]);
assertCommercial($edited['revision']===$rev+1,'revisão otimista incrementada');
assertCommercial(CommercialPlanCatalog::all($db)[0]['title']==='Plano em teste',
    'alteração da conta administrativa persistida');
assertCommercial((int)$db->query('SELECT COUNT(*) FROM devin_billing_plan_audit')->fetch_row()[0]===1,
    'trilha de auditoria armazenada');
expectDenied(static fn()=>CommercialPlanCatalog::updateDraft($db,7,$id,$rev,
    ['title'=>'edição vencida']),'revisão obsoleta');
expectDenied(static fn()=>CommercialPlanCatalog::updateDraft($db,7,$id,$rev+1,
    ['is_published'=>1]),'publicação manual proibida');
expectDenied(static fn()=>CommercialPlanCatalog::updateDraft($db,7,$id,$rev+1,
    ['placement_fee_bps'=>501]),'taxa máxima 5%');
$db->query('CREATE TABLE vagas(id_vaga INT PRIMARY KEY,id_empresa INT,encerrada_em DATETIME NULL)');
$db->query('CREATE TABLE candidatura(id_candidatura INT PRIMARY KEY,id_pessoa INT,
    data_candidatura DATE NOT NULL)');
$db->query('INSERT INTO vagas VALUES(101,20,NULL),(102,20,NULL)');
$db->query('INSERT INTO candidatura VALUES(1,5,CURDATE())');
putenv('DEVIN_MONETIZATION_ENABLED=1');
assertCommercial(CommercialEntitlements::enabled(),'ativação explícita de CI');
assertCommercial(CommercialEntitlements::forAccount($db,'empresa',20)['max_active_jobs']===2,
    'sem assinatura ativa mantém apenas cota gratuita');
expectDenied(static fn()=>CommercialEntitlements::assertMayPostJob($db,20),
    'empresa gratuita com duas vagas ativas');
assertCommercial(CommercialEntitlements::forAccount($db,'pessoa',5)['max_applications_month']===10,
    'pessoa sem assinatura ativa mantém cota de exemplo');
assertCommercial(CommercialEntitlements::forAccount($db,'pessoa',5)['auto_apply']===false,
    'inscrição automática nunca habilitada por tabela');
$db->close();
echo "DevIN commercial SQL: OK\n";
