# DevIN — Token 2 permanente dos ADMs (Fase 1 LGPD)

> Branch de desenvolvimento: feature/lgpd-phase1-foundation, PR #8 (draft).
> O banco da hospedagem NÃO foi alterado. Realizar testes e backup antes do deploy.

## Comportamento

Há quatro contas ADM identificadas por administrador.id_administrador.
Cada uma possui uma senha principal de login e pode pré-cadastrar um Token 2
**pessoal, permanente e sem data de validade**.

- Acessar /php/JarvisT.php (somente ADM logado) para configurar ou trocar.
- Primeiro cadastro: informar Token 1, definir Token 2 e confirmar.
- Mudança futura: informar Token 1, informar o Token 2 atual, definir e confirmar o novo.
- Não há códigos de ativação, prazo de 30 minutos ou renovação compulsória.
- Quem perdeu o Token 2 precisa de procedimento de recuperação supervisionado no servidor, ainda não exposto na interface. Não permitir recuperação só pela sessão.
- Após alterar, autorizações antigas de site e IA são invalidadas imediatamente; a senha registrada permanece válida até o próprio ADM mudá-la.

As interfaces operacionais do site e da IA continuam a pedir apenas "Informe o Token 1"
ou "Informe o Token 2", sem explicar a função dos tokens.

## Esquema SQL

Arquivo: database/migrations/2026_11_09_002_admin_stepup.sql

Novas colunas adicionadas à tabela existente administrador:

| Coluna | Tipo | Uso |
|---|---|---|
| token2_hash | VARCHAR(255) NULL | Hash protegido e individual; NULL antes da configuração |
| token2_version | BIGINT UNSIGNED | Revisão da credencial; invalida sessões após troca |
| token2_configured_at | DATETIME(6) NULL | Data de criação ou última alteração, **não é expiração** |

token2_hash é o nome técnico da coluna (não "cartografia 2"); nenhum valor legível é salvo.
Uma migração de banco deve ser aplicada **uma única vez pelo operador**, com backup,
permissões adequadas e em banco de desenvolvimento. A página web nunca executa
CREATE TABLE ou ALTER TABLE.

Os antigos arquivos de convite 2026_11_09_003_admin_token2_enrollment.sql e
scripts/issue-admin-token2-activation.php foram retirados da branch. Se algum
ambiente de desenvolvimento recebeu versões anteriores dessas migrations,
não as rode novamente sem comparar o schema e planejar a convergência.

## Segurança do Token 2 longo

- Sem atributo maxlength e sem limite de caracteres na interface.
- Pelo menos 12 caracteres Unicode; validação UTF-8 e confirmação idêntica.
- Teto técnico de 64 KiB de entrada, para evitar ataques de negação de serviço.
- SHA-256 com domínio específico **antes** de password_hash() Argon2id
  (ou algoritmo PHP seguro disponível), evitando truncamento de 72 bytes do bcrypt.
- Verificação com password_verify(), sem guardar dados sensíveis em logs,
  JavaScript ou prompts de IA. adminId() vem da sessão autenticada, nunca do POST.
- Antes de substituir um Token 2 cadastrado, exige o Token 2 atual e o Token 1.
- Valores iguais ao Token 1 ou ao Token 2 de outro ADM são recusados.
- Rate limit e CSRF em POST; a tela só deve operar em HTTPS em produção.

**Nota de risco:** para um ADM ainda sem Token 2, o pré-cadastro depende do login
existente + confirmação do Token 1. Um invasor que já possua essa senha poderia
tentar cadastrar primeiro. Por isso os quatro administradores devem realizar
o pré-cadastro de forma supervisionada e segura antes da ativação da proteção.
Para resistência real contra comprometimento dessa senha, é recomendável
adicionar autenticação independente (passkey/WebAuthn ou TOTP).

## Acesso ao painel e JARVIS

- Ações críticas no painel: Token 2 obrigatório antes do primeiro POST;
  autorização por sessão (site).
- JARVIS Admin do PR #7: Token 1, depois Token 2, obrigatoriamente nessa ordem;
  sem tokens no histórico/modelo.
- A senha é permanente, **mas** as autorizações de sessão expiram após
  30 minutos de inatividade ou 8 horas da validação. O logout revoga a sessão.
- AdminStepUp.php verifica a versão/hash atuais no banco a cada acesso protegido.
- Falta da migration/credencial impede uso do desbloqueio; não há fallback inseguro.
- No .env de desenvolvimento: DEVIN_ADMIN_STEPUP_ENABLED=1 somente depois
  de migrar a tabela e configurar os quatro ADMs. Iniciar com 0 para pré-cadastro.

## Testes e deploy

- tests/admin_token2_permanent_integration.php: cadastro, senhas longas,
  troca, recusa da chave anterior, verificação da chave de outro ADM,
  persistência sem expiração e revogação dos grants.
- tests/admin_stepup_integration.php: sequência de IA, isolamento de sessões,
  revalidação e proteção de POST de ações críticas.
- .github/workflows/lgpd-phase1.yml executa PHP lint e MySQL 8.0
  descartável, sem conexão com o banco de produção.

Ordem: backup/validação do schema real → migração 001 (LGPD) e 002
(administrador) em desenvolvimento → pré-cadastro dos quatro ADMs em
JarvisT.php → ativar a flag em desenvolvimento → testar painel/JARVIS →
revisão do PR #8 → só então integrar o PR #7 e considerar produção.

Não há garantia de conformidade LGPD apenas por implementar essas rotas.
