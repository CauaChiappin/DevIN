# DevIN Comercial — arquitetura, monetização e implementação por fases

## Ramo de desenvolvimento e segurança
- **main** é a aplicação regular testada, sem cobrança obrigatória.
- **production/monetization** contém o desenvolvimento comercial. Ter "production" no nome NÃO significa implantado.
- O workflow `sync-main-to-monetization.yml` integra automaticamente novas alterações da main neste ramo. Conflitos param a ação (nunca force push).
- **DEVIN_MONETIZATION_ENABLED=0** é o padrão e mantém comportamento comercial desligado.
- Não usar dados reais nem publicar telas de compra antes de validar segurança, gateway e contratos.

## Preços e limites — hipóteses iniciais, NÃO ofertas
Valores mensais ainda devem ser definidos (monthly_price_cents é NULL, is_published=0). Planos existentes na migration:

| Público | Nível | Vagas ativas | Candidaturas/mês | Comissão da empresa | Benefícios |
| --- | --- | --- | --- | --- | --- |
| Empresa | 1 Essencial | 3 | — | 5% | sem anúncios |
| Empresa | 2 Crescimento | 10 | — | 3% | sem anúncios, mais vagas |
| Empresa | 3 Escala | 40 | — | 1% | sem anúncios, mais vagas |
| Pessoa | 1 Essencial | — | 15 | nenhuma | sem anúncios |
| Pessoa | 2 Inteligente | — | 40 | nenhuma | sem anúncios, busca por vagas automatizada **com consentimento** |
| Pessoa | 3 Pro | — | ilimitadas | nenhuma | sem anúncios, busca avançada e recursos premium de IA planejados |

Plano gratuito preliminar: empresas 2 vagas ativas e pessoas **15 candidaturas por mês** (decisão confirmada para desenvolvimento). Como o Nível 1 pago também está em 15 no rascunho, seu diferencial por enquanto é retirar anúncios; limites pagos finais dependem de testes e validação comercial.
Números são exemplos ajustáveis por ADM após análise de mercado. Os planos são individuais por público.
**Nenhum plano é público ou vendável até configurar preço, pagamento e termos.**

## Como funciona o dinheiro
1. Assinaturas pagas precisam ser ativadas apenas por confirmação no servidor do gateway (webhook assinado, validação do evento junto ao PSP, deduplicação e período vigente). Nunca confiar em sucesso enviado pelo navegador.
2. Comissão por contratação: **cobrada somente da empresa**, após ambas as partes confirmarem contratação e existir contrato B2B sobre qual serviço e valor são devidos. A taxa de referência é de 5%, reduzida proporcionalmente pelo plano empresarial. Não descontar salário nem exigir pagamento do candidato.
3. A tabela `devin_placement_fee_events` registra o valor acordado do serviço B2B, taxa e auditoria. **Não** calcular com o salário anunciado.
4. Anúncios: inventário por posição (feed, vagas, menu), revisão editorial e indicação **Publicidade** em todos os formatos. Apenas contas gratuitas/sem plano ad-free podem recebê-los.
5. Painel de gestão: ADMs podem consultar assinantes e editar o rascunho dos planos, com verificação de CSRF, histórico antigo/novo e revisão otimista. JARVIS pode sugerir mudanças ou listar o plano, mas não editar pagamentos ou publicar sem o ADM confirmar.
6. Política de cancelamento e reembolso deve ser implementada antes do checkout, inclusive direitos de arrependimento, renovação, faturas e impostos.

## Automação responsável para candidatos
- A busca inteligente deve ser gratuita em nível básico e mais sofisticada nos níveis 2/3, com critérios de cargo, município, modalidade, salário e palavras bloqueadas.
- Inscrição automática precisa **opt-in específico**, lista de critérios aprovada, proteção contra envio repetido, pausa instantânea e log por candidatura.
- Plano 2 tem teto de 40 inscrições/mês como hipótese; nível 3 pode ser ilimitado comercialmente, mas com limite técnico de requisições, antifraude e teto de segurança para evitar spam.
- Uma IA forte é uma capacidade sujeita a custo e disponibilidade real; nunca prometer “ilimitado” de API externa sem orçamento.
- Não enviar currículos, CPF e dados pessoais a serviços externos sem informar finalidade e base legal.

## Identificação, CPF/CNPJ e CEP
**Decisão de 17/11/2026:** não ativar nem introduzir consultas externas de CPF/CNPJ/CEP na `main` durante os testes escolares. Este módulo permanece isolado na branch comercial e NÃO deve ser implantado na Hostinger neste ciclo.

- CPF e CNPJ locais: validar apenas **estrutura e dígitos verificadores**, não existência nem identidade do titular.
- Validação cadastral real de CNPJ deve usar provedor autorizado (por exemplo serviço comercial SERPRO), com contrato, custo, limite e chave somente no backend.
- CEP: formato de oito dígitos; consulta HTTPS fixa ao ViaCEP, com timeout, TLS, sem redirects e tratamento de CEP inexistente. Não prova endereço exato de alguém.
- Jamais expor resposta “CPF já cadastrado” em endpoint público. Usar mensagens genéricas, antienumeração, rate limits e proteção da LGPD.
- Vagas próximas: **cidade/UF primeiro** (quando o usuário permitir usar CEP). Só calcular distância real com geocodificação consentida, sem estimar quilômetros pela diferença numérica entre CEPs.
- Dados pessoais em resultados de CNPJ: limitar ao estritamente necessário.

## Arquivos já construídos nesta branch
- `database/migrations/2026_11_18_007_commercial_draft.sql`: seis planos, assinaturas, anúncios, auditoria, propostas de comissões e opt-in de automação.
- `php/controllers/CommercialEntitlements.php`: permissões efetivas e limites; mesmo com monetização habilitada, planos pagos exigem estado ACTIVE e período válido. Nenhuma compra simulada vira assinatura.
- `php/controllers/CommercialPlanCatalog.php`: lista planos e assinantes, atualiza rascunho com auditoria e calcula valores de referência da comissão B2B.
- `php/controllers/BrazilianRegistry.php`: valida CPF/CNPJ e confere CEP em serviço HTTPS fixo.
- `php/api/commercial/plans.php`: API ADM com sessão, CSRF e controle de nível para edições de rascunhos.

## O que ainda NÃO está operacional
- Checkout real, plano pago ativo, tratamento de webhook, gestão de reembolso/estorno e atualização de status por provedor.
- Interface de assinatura da pessoa ou empresa, páginas de preços, gestão de estoque/vencimento e relatórios financeiros prontos para produção.
- Integração de anúncios externos e atribuição confiável de impressões sem cookies de terceiros.
- Pesquisa geográfica precisa e ranking personalizado com garantia de consentimento.
- Robô de candidaturas, IA premium escalável, compra/recebimento de pagamentos, criação ou cancelamento de cobrança pelo JARVIS.
- Revisão jurídica/fiscal completa e termo de adesão/contrato de intermediação.

## Referências para decisão de implementação
- Lei 8.078/1990 (CDC), art. 49 — arrependimento em contratações à distância.
- Decreto 7.962/2013 — comércio eletrônico, transparência e cancelamento.
- Lei 13.709/2018 (LGPD) e orientações da ANPD — minimização, finalidade e segurança de CPF, CEP e perfis.
- Documentação ViaCEP (viacep.com.br) — formato e erro de CEP.
- Receita Federal / SERPRO — consultas oficiais de CNPJ têm controle de acesso e produto específico para empresas.
- Mercado Pago Developers — assinaturas recorrentes e webhooks com verificação de autenticidade.

## Próximas tarefas (ordem)
1. Validar modelos financeiros e percentual com contador/advogado; preço, teto, tributos e contratos.
2. Conectar gateway em sandbox e verificar notificações assinadas, pagamentos repetidos, cancelamentos e reembolsos.
3. Construir tela de planos, checkout e painel de assinaturas para ambos os públicos; oferecer cancelamento online.
4. Criar painel ADM/JARVIS de propostas comerciais com confirmação humana e trilha de auditoria.
5. Implementar anúncios rotulados e ferramentas de inventário, sem bloquear funções essenciais de busca.
6. Implementar busca por localidade com CEP minimizado e rankeamento transparente.
7. Lançar automação por critérios com prévia e consentimento por perfil, limite e antifraude.
8. Homologar mobile, desempenho e segurança antes de ligar DEVIN_MONETIZATION_ENABLED=1.
