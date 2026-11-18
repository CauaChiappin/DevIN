<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

/**
 * Testes unitários do rascunho comercial; independentes de APIs e bancos reais.
 * Nenhuma cobrança é feita. CPF/CNPJ validados por dígitos não provam identidade.
 */
require_once dirname(__DIR__).'/php/controllers/BrazilianRegistry.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once dirname(__DIR__).'/php/controllers/CommercialEntitlements.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once dirname(__DIR__).'/php/controllers/CommercialPlanCatalog.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

function must(bool $condition, string $label): void // Explicação DevIN: Declara função/método must, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if (!$condition) throw new RuntimeException('Commercial unit test: '.$label); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
must(BrazilianRegistry::cpf('529.982.247-25'),'CPF de exemplo com dígitos válidos'); // Explicação DevIN: Continua expressão ou bloco pertencente a função must.
must(!BrazilianRegistry::cpf('529.982.247-26'),'CPF com dígito inválido'); // Explicação DevIN: Continua expressão ou bloco pertencente a função must.
must(!BrazilianRegistry::cpf('11111111111'),'CPF repetido'); // Explicação DevIN: Continua expressão ou bloco pertencente a função must.
must(BrazilianRegistry::cnpj('11.222.333/0001-81'),'CNPJ numérico de teste válido'); // Explicação DevIN: Continua expressão ou bloco pertencente a função must.
must(!BrazilianRegistry::cnpj('11.222.333/0001-82'),'CNPJ com dígito inválido'); // Explicação DevIN: Continua expressão ou bloco pertencente a função must.
must(BrazilianRegistry::cepFormat('09510-010'),'CEP com oito dígitos'); // Explicação DevIN: Continua expressão ou bloco pertencente a função must.
must(!BrazilianRegistry::cepFormat('12345'),'CEP incompleto'); // Explicação DevIN: Continua expressão ou bloco pertencente a função must.
must(CommercialPlanCatalog::employerPlacementFee(10000,500)===500,'5% de serviço B2B'); // Explicação DevIN: Continua expressão ou bloco pertencente a função must.
must(CommercialPlanCatalog::employerPlacementFee(10000,300)===300,'3% de serviço B2B'); // Explicação DevIN: Continua expressão ou bloco pertencente a função must.
must(CommercialPlanCatalog::employerPlacementFee(10000,100)===100,'1% de serviço B2B'); // Explicação DevIN: Continua expressão ou bloco pertencente a função must.
must(!CommercialEntitlements::enabled(),'monetização desligada sem configuração explícita'); // Explicação DevIN: Continua expressão ou bloco pertencente a função must.
must(CommercialEntitlements::freeLimits('empresa')['max_active_jobs']===2,'cota de exemplo empresa'); // Explicação DevIN: Continua expressão ou bloco pertencente a função must.
must(CommercialEntitlements::freeLimits('pessoa')['max_applications_month']===10,'cota de exemplo pessoa'); // Explicação DevIN: Continua expressão ou bloco pertencente a função must.
echo "DevIN commercial unit: OK\n"; // Explicação DevIN: Produz saída ou conteúdo da resposta do programa.
