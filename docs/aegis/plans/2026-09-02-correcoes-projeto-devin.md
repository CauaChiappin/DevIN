# Correções gerais do projeto DevIN

## Goal

Revisar e corrigir os arquivos mantidos da pasta `DevIN`, eliminando erros estruturais, referências quebradas, divergências de sessão/CSRF e problemas de execução sem reescrever a dependência PHPMailer.

## Architecture

As páginas públicas ficam em `html/`; estilos e comportamento compartilhado ficam em `css/` e `js/`; os fluxos autenticados e a camada de persistência ficam em `php/`. Os arquivos PHP de cadastro, login, recuperação e dashboards são os donos dos fluxos de servidor.

## Tech Stack

HTML5, CSS, JavaScript de navegador, PHP com MySQLi, MySQL/MariaDB sob XAMPP e PHPMailer vendorizado.

## Baseline/Authority Refs

- `README.md`
- `docs/aegis/BASELINE-GOVERNANCE.md`
- Estrutura e código atual da pasta `DevIN`, levantados antes da correção

## Compatibility Boundary

Manter as rotas relativas atuais, especialmente `html/index.html`, `html/login.html`, `html/cadastro_pessoa.html`, `html/cadastro_empresa.html`, `php/login.php`, `php/cadastro_pessoa.php`, `php/cadastro_empresa.php`, `php/recuperacao.php` e os três dashboards. Não alterar a API pública do PHPMailer nem remover tabelas ou colunas esperadas sem schema confiável.

## TDD Route

- Mode: off
- Decision: skipped
- Strict authority: not applicable; o usuário não solicitou TDD estrito
- Strict signals: não há suíte de testes existente no repositório
- Light eligibility: não aplicável ao escopo amplo
- TDD-fit exception: validação proporcional por lint, execução HTTP local e checagens estruturais
- Test posture: diagnóstico inicial e regressão pós-alteração
- Reason: o objetivo é uma auditoria ampla de um projeto PHP/HTML sem harness automatizado; a prioridade é corrigir os contratos atuais e provar os fluxos alcançáveis no ambiente
- Verification: `C:\xampp\php\php.exe -l`, checagens de referências, balanceamento estrutural e servidor local

## Scope and Requirement Ready Check

- Requirement source refs: solicitação do usuário nesta sessão
- Goals and scope refs: corrigir todos os arquivos mantidos da pasta `DevIN`
- User/scenario refs: navegação pública, cadastro, login, recuperação, dashboards e jogos
- Requirement item refs: sintaxe, lógica, imports/requires, formatação, links/recursos e execução
- Acceptance/verification criteria refs: lint sem erros, páginas sem duplicação estrutural, caminhos existentes e fluxos sem falhas óbvias
- Open blocker questions: schema completo do banco não está versionado no repositório
- Decision: ready, com limitação de validação de queries dependente do banco

## Change Necessity

- User-visible need: o projeto contém páginas duplicadas, referências inexistentes e fluxos que podem falhar em execução
- No-change/non-code option: não há opção de configuração que remova HTML duplicado ou corrija rotas e contratos de sessão
- Why code change is necessary: os defeitos estão nos arquivos-fonte e impedem ou degradam o uso da aplicação
- Minimum change boundary: arquivos mantidos em `html/`, `css/`, `js/` e `php/`, com exclusão da lógica interna do PHPMailer
- Decision: code-change

## File Map and Tasks

1. Diagnosticar os arquivos próprios, entradas, includes, recursos e páginas públicas; registrar erros confirmados.
2. Consolidar `php/cadastro_empresa.php` em uma única implementação funcional, corrigir a proteção de sessão do dashboard da empresa e alinhar os formulários estáticos ao token CSRF dinâmico.
3. Corrigir referências de imagens/favicon, scripts, links e redirecionamentos; remover código morto ou duplicado confirmado.
4. Revisar CSS/JS próprios e a emulação dos jogos sem introduzir dependências desnecessárias.
5. Executar lint PHP, checagens estruturais, servidor local e inspeção do diff; documentar lacunas causadas pela ausência de schema/testes.

## Architecture Integrity Lens

- Invariant: cada fluxo tem um único dono de processamento e os dashboards autenticados usam o middleware comum.
- Canonical owner/contract: `config/security.php` para sessão/CSRF, `middlewares/auth.php` para autenticação web e `ProfileController.php` para perfis.
- Responsibility overlap: `cadastro_empresa.php` possui duas cópias do fluxo; `empresa.php` repete autenticação e CSRF manualmente.
- Higher-level simplification: reutilizar os helpers e middlewares existentes em vez de criar outro mecanismo.
- Retirement/falsifier: a cópia duplicada e a autenticação manual deixam de existir após a consolidação; qualquer rota pública que depender da cópia será verificada.
- Verdict: corrigir e consolidar nos donos existentes.

## Complexity and Risks

- Alterações maiores ficam limitadas aos owners atuais; não será criado framework novo.
- O banco pode divergir do código, especialmente nas colunas opcionais `descricao`, `foto`, `idioma` e tokens de recuperação.
- SMTP e links enviados por e-mail não serão considerados comprovados sem configuração real de e-mail.
- Arquivos em `php/PHPMailer/` permanecem fora do escopo de edição.

## Verification

- Lint de todos os PHP próprios e, separadamente, da dependência vendorizada.
- Balanceamento básico e referências locais em HTML/CSS/JS/PHP.
- Execução das páginas públicas via `C:\xampp\php\php.exe -S 127.0.0.1:8000 -t .`.
- Inspeção de duplicações, arquivos inexistentes e diff final.

## Retirement

Retirar a implementação duplicada do cadastro de empresa e os caminhos inválidos confirmados. Manter somente os owners compartilhados atuais; nenhum fallback novo será mantido sem uma condição de remoção clara.
