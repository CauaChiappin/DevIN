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
- **Cada ADM escolhe o próprio Token 2**, sem que o operador do sistema o conheça. O servidor salva somente o hash Argon2id (se disponível) ou hash seguro padrão do PHP.
- O operador emite apenas um **código de ativação temporário** por ADM, enviado individualmente por canal seguro. O código expira em 30 minutos e é consumido após uso.
- Ao entrar no painel, cada ADM usa o link **Configurar Token 2**. A página apresenta apenas os campos **Informe o Token 1**, **Informe o código de ativação**, **Defina o Token 2** e **Confirme o Token 2**.
- O código de ativação não substitui nenhuma das duas etapas posteriores, não é enviado ao JARVIS e não libera as ações críticas.
- Exigência para o novo Token 2: pelo menos 14 caracteres e no máximo 72 bytes, não pode coincidir com o Token 1 nem com outro Token 2 existente. A nova escolha invalida permissões de sessões anteriores.

Migrações necessárias: `2026_11_09_002_admin_stepup.sql` **e depois** `2026_11_09_003_admin_token2_enrollment.sql`.

No servidor de **desenvolvimento**, depois de aplicar as duas migrations e conferir os quatro IDs reais:

```bash
php scripts/issue-admin-token2-activation.php <id_administrador>
```

Execute uma vez para cada ADM. Não adivinhe IDs; os números nos exemplos anteriores não eram IDs confirmados. O código gerado só aparece uma vez no terminal e deve ser entregue por canal privado. **Não peça aos ADMs que enviem seus Tokens 1 ou 2 para outra pessoa.** Para recuperação ou troca do Token 2, emita um novo código temporário; o ADM configurará outro segredo. O script antigo `provision-admin-token2.php` foi desabilitado para impedir atribuição automática.

## Como ativar com segurança

1. Confirme backup recuperável e teste o banco em ambiente descartável (a CI usa MySQL 8.0).
2. Confirme a coluna `administrador.senha_hash` e chave `administrador.id_administrador`; a migration cria FK para essa tabela.
3. Aplique a migration **apenas em desenvolvimento**.
4. Emitir quatro códigos temporários, um por ADM, entregá-los com segurança e permitir que **cada ADM configure o próprio Token 2** antes de ativar o bloqueio. Não existe credencial compartilhada nem padrão.
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
| `scripts/issue-admin-token2-activation.php` | emissão de convite temporário individual via CLI |
| `php/admin_token2.php` | formulário protegido para o próprio ADM definir o Token 2 |
| `php/controllers/Security/AdminToken2Enrollment.php` | validação do Token 1, convite de uso único e hash do novo Token 2 |
| `scripts/provision-admin-token2.php` | comando antigo descontinuado |
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

### Segurança da autoinscrição

O código individual e o Token 1 são exigidos simultaneamente para definir o Token 2; uma sessão ADM já aberta não é suficiente. A confirmação ocorre por HTTPS no ambiente de produção e exige CSRF. Não há tokens pré-fixados em `.env`. O processo é isolado por conta, usa limitação de tentativas por ADM/IP e consome o código no mesmo COMMIT que salva o novo hash. A tela não mostra a finalidade dos tokens.

Os dados de ativação e o novo Token 2 não podem ser enviados por mensagens, incluídos no histórico do JARVIS ou registrados no console do navegador. A senha escolhida deve ficar só com o respectivo ADM. Essa etapa não transforma dois segredos de conhecimento em fatores independentes de MFA.
