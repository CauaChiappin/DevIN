# Permissões administrativas do JarvisDevIN — sem Token 2

O JARVIS pode consultar vagas, candidatos e empresas, analisar registros
selecionados, preparar mensagens e permitir envio aprovado pelo administrador,
além de solicitar exclusões **supervisionadas** de vagas, pessoas e empresas.

A exclusão não é uma decisão autônoma da IA. A pessoa administradora deve
selecionar o registro na aba **Consultar**, visualizar o ID/nome e confirmar
duas vezes (confirmação visual e texto EXCLUIR). A solicitação vale por
180 segundos e é vinculada à sessão autenticada e ao objeto confirmado.

O backend usa CSRF, sessão ADM, lista fechada de tabelas/colunas,
SQL preparado, uma transação e trilha de auditoria. Não permite excluir
contas ADM nem executar SQL arbitrário.

## Implantação na Hostinger

1. Faça backup do banco e dos arquivos, com cópia fora da pasta pública.
2. Atualize os arquivos da branch `main` sem substituir o seu `.env`.
3. No phpMyAdmin do banco DevIN, aplique **uma vez** a migração
   `database/migrations/jarvis_admin_controlled_actions.sql`, se
   `jarvis_action_events` e `jarvis_platform_outbox` ainda não existem.
4. Aplique **uma vez** `database/migrations/2026_11_16_004_jarvis_moderation.sql`.
5. Verifique se existem as 3 tabelas `jarvis_action_events`,
   `jarvis_platform_outbox` e `jarvis_moderation_audit`.
6. No `.env` privado, após testar as migrações:
   ```dotenv
   DEVIN_ADMIN_STEPUP_ENABLED=0
   DEVIN_ADMIN_RBAC_ENABLED=0
   DEVIN_JARVIS_ACTIONS_ENABLED=1
   DEVIN_JARVIS_DELETIONS_ENABLED=1
   ```
7. Teste primeiro pesquisas e análise, depois exclusão de um registro
   **criado especificamente para teste** (não excluir contas/vagas reais
   para validar a ferramenta).

**Importante:** com `DEVIN_ADMIN_RBAC_ENABLED=0`, qualquer ADM autenticado
pode usar as ações do JARVIS após confirmação, inclusive exclusão.
Para restringir o acesso por cargo, implante a migração de níveis
`2026_11_16_003_admin_roles.sql`, configure os quatro ADMs e só
então ative a flag RBAC. A solicitação de deixar Token 2 para depois
não desabilita autenticação normal, CSRF ou confirmações por ação.

## Restrições técnicas

- Operações podem falhar quando o banco possui vínculos/foreign keys;
  nesse caso a transação é revertida.
- Exclusão de pessoa ou empresa pode afetar seus vínculos/candidaturas
  conforme as regras atuais do esquema. Sempre revisar consequências.
- Sem backups, exclusão pode ser irreversível.
- A IA não pode criar tabelas, executar SQL livre, enviar mensagens em
  massa ou ignorar confirmação humana.
- A aba Consultar mostra até 12 registros por pesquisa; refine o termo
  ou use o ID do registro.
- Se alguma tabela da migração estiver ausente, mantenha as flags de
  ferramentas e exclusões em 0 e ajuste o banco antes de ativar.
