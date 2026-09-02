# Intento e linha de base

## TaskIntentDraft

Corrigir os arquivos mantidos do projeto DevIN para que as páginas públicas, autenticação, cadastros, recuperação de senha, dashboards e jogos não contenham duplicação estrutural, referências quebradas ou divergências claras de execução.

## BaselineUsageDraft

- Required baseline refs: `README.md`, `docs/aegis/BASELINE-GOVERNANCE.md`, plano em `docs/aegis/plans/2026-09-02-correcoes-projeto-devin.md` e snapshot Git capturado em 2026-09-02.
- Acknowledged before edits: todos os itens acima.
- Cited in plan: `README.md`, governança e estrutura atual do projeto.
- Missing refs: schema SQL versionado e suíte de testes.
- Decision: continue com verificação de queries limitada ao banco local disponível.

## ImpactStatementDraft

- Usuários afetados: visitantes, candidatos, empresas e administradores.
- Fluxos afetados: navegação, cadastro, login, sessão/CSRF, perfis, vagas, candidaturas e jogos.
- Limite: `php/PHPMailer/` permanece apenas em validação, sem edição.

## Execution Readiness View

- Intent Lock: corrigir defeitos existentes nos owners atuais.
- Scope Fence: `html/`, `css/`, `js/`, PHP próprio e registros Aegis; excluir `php/PHPMailer/`.
- Baseline Lock: README, governança, plano e snapshot Git.
- Approved Behavior: preservar URLs relativas e contratos das tabelas/colunas existentes.
- Owner/Contract Constraints: security para sessão/CSRF, middleware para autenticação e controller para perfis.
- Compatibility Boundary: páginas `html/*` e `php/*` devem continuar acessíveis sob XAMPP.
- Retirement Boundary: retirar a cópia duplicada de cadastro de empresa e referências comprovadamente inválidas.
- Task Batches: diagnóstico; cadastro/sessão; referências e jogos; validação.
- Test Obligations: lint PHP, checagens estruturais, referências locais e execução HTTP local.
- Review Gates: leitura do diff e nova validação depois de cada bloco.
- Drift/Rewind Rules: parar se o schema, as rotas ou o snapshot contradisserem o plano; preservar alterações preexistentes.
- Evidence Required Before Completion: resultados frescos dos checks e limitações explicitadas.
- Advisory Boundary: documento operacional, sem autoridade de aceite externo.
