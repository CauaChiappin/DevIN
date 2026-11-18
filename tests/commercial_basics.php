<?php
declare(strict_types=1);

/**
 * Testes unitários do rascunho comercial; independentes de APIs e bancos reais.
 * Nenhuma cobrança é feita. CPF/CNPJ validados por dígitos não provam identidade.
 */
require_once dirname(__DIR__).'/php/controllers/BrazilianRegistry.php';
require_once dirname(__DIR__).'/php/controllers/CommercialEntitlements.php';
require_once dirname(__DIR__).'/php/controllers/CommercialPlanCatalog.php';

function must(bool $condition, string $label): void
{
    if (!$condition) throw new RuntimeException('Commercial unit test: '.$label);
}
must(BrazilianRegistry::cpf('529.982.247-25'),'CPF de exemplo com dígitos válidos');
must(!BrazilianRegistry::cpf('529.982.247-26'),'CPF com dígito inválido');
must(!BrazilianRegistry::cpf('11111111111'),'CPF repetido');
must(BrazilianRegistry::cnpj('11.222.333/0001-81'),'CNPJ numérico de teste válido');
must(!BrazilianRegistry::cnpj('11.222.333/0001-82'),'CNPJ com dígito inválido');
must(BrazilianRegistry::cepFormat('09510-010'),'CEP com oito dígitos');
must(!BrazilianRegistry::cepFormat('12345'),'CEP incompleto');
must(CommercialPlanCatalog::employerPlacementFee(10000,500)===500,'5% de serviço B2B');
must(CommercialPlanCatalog::employerPlacementFee(10000,300)===300,'3% de serviço B2B');
must(CommercialPlanCatalog::employerPlacementFee(10000,100)===100,'1% de serviço B2B');
must(!CommercialEntitlements::enabled(),'monetização desligada sem configuração explícita');
must(CommercialEntitlements::freeLimits('empresa')['max_active_jobs']===2,'cota de exemplo empresa');
must(CommercialEntitlements::freeLimits('pessoa')['max_applications_month']===10,'cota de exemplo pessoa');
echo "DevIN commercial unit: OK\n";
