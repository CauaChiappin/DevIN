# DevIN — verificação administrativa em duas etapas

> Fase 1 LGPD, branch `feature/lgpd-phase1-foundation`, PR #8. **Não implantar diretamente em produção.** A proteção envolve o código do painel ADM e a integração do PR #7 (JARVIS).

## Fluxos implementados

- Login ADM existente permanece com e-mail e senha.
- Site: ao executar pela primeira vez uma operação sensível, o servidor apresenta apenas **Informe o Token 2**. Quando aceito, retoma o POST original na mesma sessão (inclusive formulário enviado sem JavaScript).
- IA: ao abrir o JARVIS, o servidor exige **Informe o Token 1** e, somente depois da validação, **Informe o Token 2**. O backend bloqueia até lá todos os endpoints de chat, conversa, resumo e atividades.
- Os dois escopos de autorização são separados. Uma aprovação para o site não libera a IA nem vice-versa.
- O sistema **não** fornece explicação dos tokens nas telas nem nas mensagens do modelo. Tokens não são persistidos em texto puro, salvos no chat, logs ou enviados à IA.

**Nota técnica para revisão de segurança:** dois segredos de conhecimento não constituem MFA com fatores independentes, segundo a OWASP. Esta é uma autenticação adicional sequencial ("step-up"); para MFA propriamente dito, acrescente futuramente TOTP ou passkeys/WebAuthn. Ocultar descrições dos campos melhora discrição visual, mas não substitui proteção de credenciais, HTTPS e revisão de código.

## Atribuição individual aos 4 ADMs

Migration: `database/migrations/2026_11_09_002_admin_stepup.sql`.

- Cria `admin_security_credentials` com vínculo por `administrador.id_administrador`, hash de Token 2 e versão para revogação imediata.
- Gera credencial aleatória de alta entropia, **diferente para cada ADM**, impressa uma única vez no terminal do operador. O servidor armazena somente `password_hash()` (Argon2id quando disponível).
- Para cada um dos **quatro IDs reais**, execute no ambiente de desenvolvimento, depois de criar a tabela:

```bash
php scripts/provision-admin-token2.php 1
php scripts/provision-admin-token2.php 2
php scripts/provision-admin-token2.php 3
php scripts/provision-admin-token2.php 4
```

**Os números acima são ilustrativos, NÃO são os IDs reais.** Descubra os quatro IDs reais no banco, então execute individualmente. Não copie as chaves impressas para a conversa, comandos shell, arquivos Git ou CI. Entregue individualmente aos respectivos administradores por um canal seguro. A reexecução para o mesmo ID **rotaciona** a credencial e invalida autorizações anteriores.

## Como ativar com segurança

1. Confirme backup recuperável e teste o banco em ambiente descartável (a CI usa MySQL 8.0).
2. Confirme a coluna `administrador.senha_hash` e chave `administrador.id_administrador`; a migration cria FK para essa tabela.
3. Aplique a migration **apenas em desenvolvimento**.
4. Provisionar os quatro ADMs reais e testar que cada chave é exclusiva. Não existe credencial compartilhada nem default.
5. Em `.env` do ambiente de desenvolvimento, habilite `DEVIN_ADMIN_STEPUP_ENABLED=1`; antes disso o controle permanece desligado no painel para não bloquear funções existentes durante preparação.
6. Valide: exclusões/edições/reports do painel, exclusão administrativa no arcade, gate do site com reenvio seguro; bloqueio de bypass com POST direto e CSRF; token inválido e limites; suspensão por inatividade, logout, troca de senha e rotação de Token 2.
7. Após revisão de segurança e testes funcionais, ativar em produção apenas com HTTPS e os quatro ADMs provisionados. Nunca habilitar IA que dependa desse gate antes de aplicar o PR #8.

**A IA do PR #7 falha de forma fechada** se os arquivos do guard ainda não estiverem presentes ou a configuração não estiver ativa. Ordem recomendada: validar/implantar PR #8 primeiro e PR #7 depois.

## Permissões e persistência

- A autorização fica na **sessão PHP atual**, vinculada ao ID do administrador e à revisão da credencial do banco.
- Não armazena nenhuma senha ou token em cookie/JWT/localStorage; apenas um estado de autorização na sessão do servidor.
- O estado expira após **30 minutos de inatividade** ou **8 horas desde a validação**, o que ocorrer primeiro; logout também encerra o estado.
- Troca de senha da conta ou rotação de Token 2 invalida imediatamente as permissões ao serem verificadas no backend.
- Nova sessão, outro navegador ou outro dispositivo exigem autenticação novamente.
- O estágio intermediário do desbloqueio da IA expira em cinco minutos.
- O rate limiter existente impõe limites por ADM e IP, sem armazenar os tokens.
- Em modo habilitado, erros de banco ou credenciais não provisionadas devem impedir novas ações em vez de liberar acesso.

## Arquivos

| Arquivo | Função |
| --- | --- |
| `database/migrations/2026_11_09_002_admin_stepup.sql` | tabela de credenciais exclusivas |
| `scripts/provision-admin-token2.php` | provisionamento/rotação CLI |
| `php/controllers/Security/AdminStepUp.php` | verificação e sessão autorizada |
| `php/controllers/Security/AdminGate.php` | interceptação e retomada segura do POST |
| `php/admin_security_gate.php` | formulário restrito para ações do painel |
| `php/api/admin_security.php` | API restrita ao navegador para desbloqueio da IA |
| `php/adm.php`, `php/arcade_api.php` | guardas nas operações administrativas |
| `php/controllers/AuthController.php` | revogação na criação de novas sessões |
| `tests/admin_stepup_integration.php` | testes de ordem, isolação, sessão e rotação |

## Limitações e cuidados

- O gate não substitui regras de autorização por recurso, CSRF, auditoria, checagem de propriedade ou revisão humana.
- Formulários com arquivos anexados não podem ter o arquivo temporário automaticamente retomado após o desbloqueio: por segurança, é necessário reenviar esse formulário.
- O gate do site protege as ações existentes de `adm.php` e remoção de progresso do arcade; novas rotas administrativas críticas **devem obrigatoriamente invocar `AdminStepUp::requireAllowed('site')`**.
- A UI da IA é desenvolvida no PR #7 e depende da implantação deste guard. Enquanto os PRs não forem integrados, não há um sistema completo em produção.
