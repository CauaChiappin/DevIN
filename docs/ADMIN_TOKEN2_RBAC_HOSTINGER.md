# Ativação do Token 2 e dos níveis ADM — Hostinger

> **Não alterar o .env para 1 nem executar SQL antes de efetuar backup do banco e confirmar os administradores.** Nenhuma operação é executada automaticamente pelo deploy.

## Estado e limites

- Token 1: senha normal de login do ADM.
- Token 2: senha pessoal de cada ADM, cadastrada em `php/JarvisT.php` e protegida com hash.
- Token 2: permanente até alteração voluntária. Trocas invalidam permissões de sessão.
- Autorização extra: no máximo 8 horas e revogada após 30 minutos sem uso.
- `DEVIN_ADMIN_STEPUP_ENABLED=0`: verificação extra ainda não aplicada aos POSTs.
- `DEVIN_ADMIN_RBAC_ENABLED=0`: níveis de permissão ainda não aplicados.
- Não fornece exclusão de contas/vagas diretamente pelo chat da IA. As exclusões continuam no painel humano.

## Etapa 1 — Backup e banco

1. Fazer backup completo do banco Hostinger e guardar fora do diretório público.
2. Conferir os campos atuais antes de executar qualquer `ALTER TABLE`:

```sql
SHOW COLUMNS FROM administrador LIKE 'token2_hash';
SHOW COLUMNS FROM administrador LIKE 'token2_version';
SHOW COLUMNS FROM administrador LIKE 'token2_configured_at';
SHOW COLUMNS FROM administrador LIKE 'nivel_permissao';
```

3. Caso faltem **todos** os três campos do Token 2, executar **uma vez**:
   `database/migrations/2026_11_09_002_admin_stepup.sql`.
   Se já houver alguns campos, não executar o `ALTER` integral; ajustar individualmente com backup.
4. Caso falte `nivel_permissao`, executar **uma vez**:
   `database/migrations/2026_11_16_003_admin_roles.sql`.
5. Conferir os quatro ADMs sem expor hashes:

```sql
SELECT id_administrador, nome, nivel_permissao,
       CASE WHEN token2_hash IS NULL OR token2_hash = '' THEN 'nao'
            ELSE 'sim' END AS token2_cadastrado
FROM administrador ORDER BY id_administrador;
```

## Etapa 2 — Cadastro pessoal

Cada ADM faz login normalmente e abre `php/JarvisT.php`. Para cadastrar Token 2:
- informar seu Token 1 (senha de login);
- criar Token 2 privado com pelo menos 12 caracteres;
- repetir Token 2 para confirmar;
- guardar em gerenciador seguro, nunca em e-mail/planilha compartilhada.

Para trocar um Token 2, informar Token 1 e Token 2 atual antes de definir o novo.
Não compartilhar tokens com a IA.

## Etapa 3 — Atribuir níveis (não atribuir automaticamente)

Os níveis propostos são:

| Nível | Permissões previstas |
| --- | --- |
| `consulta` | Consultar registros e relatórios, atualizar próprio perfil; não pode excluir |
| `moderacao` | Resolver denúncias e excluir vagas no painel; analisar e criar rascunhos no JARVIS; sem excluir contas |
| `gestao` | Permissões anteriores, exclusão humana de pessoas/empresas, ajustes administrativos e envio revisado de mensagens |

Os três níveis são um **ponto de partida técnico**, não atribuições de pessoas já aprovadas.
Depois de revisar quem ocupará cada papel, atualizar **individualmente** por ID:

```sql
-- Exemplo; substituir SOMENTE após identificar o ADM autorizado:
-- UPDATE administrador SET nivel_permissao='gestao' WHERE id_administrador=<ID_APROVADO>;
```

Não executar exemplos sem substituir os IDs e confirmar os responsáveis. O padrão na migração é `consulta`.

## Etapa 4 — Ativar flags em etapas

Após confirmar os quatro cadastros, permissões e testes básicos:

```dotenv
DEVIN_ADMIN_STEPUP_ENABLED=1
DEVIN_ADMIN_RBAC_ENABLED=1
```

A primeira flag ativa a proteção adicional prevista no painel e na IA.
A segunda flag aplica verificação de níveis no backend das operações administrativas.
**Não alterar `DEVIN_JARVIS_ACTIONS_ENABLED` apenas para habilitar Token 2.**

## Testes e recuperação

- Cada ADM consegue acessar o próprio `JarvisT.php`; pessoas e empresas não.
- Cada ADM configura Token 2 sem acesso a tokens alheios.
- Sem Token 2, um POST sensível do painel deve solicitar verificação adicional.
- Nível `consulta` deve ser impedido de excluir vaga.
- Nível `moderacao` pode revisar vaga, mas não excluir pessoa/empresa.
- Nível `gestao` pode usar operações permitidas após verificação.
- No JARVIS, ações não autorizadas devem ser negadas no servidor, não apenas ocultas no frontend.
- Com conta de teste, validar expiração por inatividade, troca de Token 2, CSRF e logout.

Se houver bloqueio inesperado, em emergência voltar ambas as flags para `0`,
investigar o log e reverter a versão de código conforme backup. **Isso desativa temporariamente
as proteções adicionais e não deve ser mantido como solução permanente.**
Não apagar as colunas nem os hashes para fazer rollback.
