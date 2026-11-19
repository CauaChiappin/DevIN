# DevIN — SQL completo disponível, modelo .env e instalação na Hostinger

**Referência:** branch `main`, 19/11/2026. Este pacote foi gerado a partir das migrations e do `.env.example` reais do repositório. Não inclui credenciais. Não executamos SQL na Hostinger.

## Arquivos deste pacote

| Arquivo | Descrição | Onde utilizar |
|---|---|---|
| [SQL_COMPLEMENTAR_DEVIN_2026.sql](SQL_COMPLEMENTAR_DEVIN_2026.sql) | Reúne as **sete migrations exclusivamente aditivas**, com 20 tabelas complementares de LGPD, JARVIS, suporte, contratação e chat | phpMyAdmin, após backup e teste em homologação |
| [SQL_ADMIN_ALTER_MANUAL_DEVIN_2026.sql](SQL_ADMIN_ALTER_MANUAL_DEVIN_2026.sql) | Alterações de colunas em `administrador` para Token 2 e níveis de permissão | Executar individualmente só se colunas não existirem |
| [SQL_DIAGNOSTICO_LEITURA_DEVIN.sql](SQL_DIAGNOSTICO_LEITURA_DEVIN.sql) | Verificação de versão, banco, tabelas e colunas **sem modificar dados** | Executar antes dos demais comandos no phpMyAdmin |
| [ENV_HOSTINGER_MODELO_PREENCHER.txt](ENV_HOSTINGER_MODELO_PREENCHER.txt) | As **32 variáveis** do `.env.example`, com valores fictícios `SUBSTITUIR_...` | Copiar conteúdo para `.env` **privado** na raiz do DevIN depois de trocar os exemplos |

## Limite importante — banco completo do zero

O repositório contém **migrations complementares**, mas **não contém um SQL completo das tabelas originais** como `pessoa`, `empresa`, `administrador`, `vagas`, `candidatura` e `curriculo`. Portanto, o SQL fornecido **NÃO é uma recriação completa do banco do zero**. Para nova instalação, é necessário exportar a estrutura original da instalação atual no phpMyAdmin e restaurá-la no banco novo antes de aplicar as migrations complementares.

Não inventamos tabelas/campos da base original nem usamos `DROP DATABASE`, `DROP TABLE`, `DELETE` ou `TRUNCATE` no pacote. As migrations complementares usam `CREATE TABLE IF NOT EXISTS`, que não apaga dados, mas **também não corrige tabelas existentes incompatíveis**.

## Passo a passo mais seguro

1. Na Hostinger, entre no **hPanel → Bancos de dados MySQL**. Se já existe um banco usado pelo DevIN, **não crie outro nem troque o DB_NAME**. Se estiver instalando do zero, crie um banco e usuário no hPanel (o nome normalmente recebe prefixo da Hostinger).
2. Abra **phpMyAdmin**, clique no **nome correto do banco do DevIN**. Execute primeiro [SQL_DIAGNOSTICO_LEITURA_DEVIN.sql](SQL_DIAGNOSTICO_LEITURA_DEVIN.sql) na aba **SQL**. Confira se existem as tabelas originais e quais funcionalidades opcionais já têm tabelas.
3. Exporte um backup integral do banco atual (**Exportar → SQL**) e salve separadamente. Copie também uploads, `.env` atual e os arquivos JSON legados de mensagens no diretório temporário PHP. Se não tiver acesso aos JSON, **não migre o chat sem um plano para preservar o histórico**.
4. Faça uma cópia de homologação com banco separado e restaure nela a estrutura original. Execute nela [SQL_COMPLEMENTAR_DEVIN_2026.sql](SQL_COMPLEMENTAR_DEVIN_2026.sql) apenas para tabelas complementares ainda necessárias, conferindo as estruturas já presentes. Repita a consulta de diagnóstico e teste os módulos.
5. Avalie [SQL_ADMIN_ALTER_MANUAL_DEVIN_2026.sql](SQL_ADMIN_ALTER_MANUAL_DEVIN_2026.sql) separadamente. A presença de `token2_hash`, `token2_version`, `token2_configured_at` e `nivel_permissao` deve ser conferida com `SHOW COLUMNS FROM administrador;`. Se já existirem, não execute o respectivo `ALTER TABLE`.
6. Com a homologação funcionando, faça backup atualizado e aplique na produção **somente** as migrations faltantes, uma de cada vez, registrando o sucesso. Não aplique o pacote inteiro indiscriminadamente sobre um banco existente com alterações de esquema diferentes.
7. Para configurar o PHP, copie o modelo para um arquivo com nome **`.env`** na raiz do projeto no servidor. Substitua todas as strings `SUBSTITUIR_...` por valores reais do hPanel, SMTP e APIs; confira a URL e nomes de modelo. **Nunca coloque o `.env` real no GitHub nem em local acessível pela web**.
8. Deixe `DEVIN_CHAT_MYSQL_ENABLED=0` até a migration de chat ser validada, o histórico JSON estar preservado e não haver gravação simultânea em duas instâncias. Depois, em homologação, passe a `1`, teste importação, envio, leitura, recarregamento e notificações; só então faça a troca controlada em produção.
9. **Não altere** `DEVIN_JARVIS_ACTIONS_ENABLED`, `DEVIN_JARVIS_DELETIONS_ENABLED`, `DEVIN_ADMIN_STEPUP_ENABLED`, `DEVIN_ADMIN_RBAC_ENABLED` nem `DEVIN_LGPD_AUDIT_ENABLED` para `1` sem confirmar migrations, permissões, auditoria, Token 2, testes e backups específicos. Criar tabelas **não** ativa recursos.
10. Execute smoke tests: login pessoa/empresa/ADM, empresas isoladas de vagas, candidaturas, suporte, JARVIS, troca de e-mail, chat e métricas. Confirme logs PHP/SMTP sem revelar segredos.

## SQL de verificação

```sql
SELECT DATABASE() AS banco_em_uso, VERSION() AS versao_mysql;
SHOW TABLES;
SHOW COLUMNS FROM administrador;
SHOW TABLES LIKE 'devin_chat_state';
SELECT resource, LENGTH(payload) AS bytes, updated_at FROM devin_chat_state;
```

O último `SELECT` só funciona depois da criação de `devin_chat_state`; antes da primeira utilização pode retornar nenhuma linha.

## O que trocar no .env

- `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`: credenciais **do seu banco** na Hostinger; `DB_PORT` normalmente é 3306, confira no hPanel.
- `DEVIN_JWT_SECRET`: segredo forte e aleatório de 32+ caracteres, **diferente** das senhas e das outras chaves.
- `DEVIN_SMTP_HOST`, `DEVIN_SMTP_USERNAME`, `DEVIN_SMTP_PASSWORD`, `DEVIN_SMTP_FROM_EMAIL`: servidor e credenciais reais de envio de e-mails.
- `DEVIN_GEMINI_API_KEY`: chave da API do Gemini para currículos, caso use o recurso.
- `DEVIN_JARVIS_API_KEY`: chave do provedor indicado por `DEVIN_JARVIS_PROVIDER` (no modelo, OpenRouter).
- `DEVIN_LGPD_IP_HASH_KEY`: outro segredo forte, diferente do JWT.
- `DEVIN_JARVIS_BACKUP_API_KEY` e `DEVIN_SUPPORT_STORAGE_DIR`: **opcionais**; podem ficar vazios. Se escolher caminho de armazenamento privado, crie uma pasta válida e proteja-a.
- `DEVIN_JARVIS_FALLBACK_MODELS` e `DEVIN_JARVIS_FALLBACK_MODEL`: **opcionais**, não são chaves; valores devem ser IDs de modelo.

No modelo, **os valores fictícios servem apenas como sinalização**, não autenticam nem protegem o sistema. Usá-los em produção deixa login, envio de e-mails e APIs indisponíveis ou inseguros. Gere chaves reais em ambiente privado.

## O que não está incluído

O código atual ainda não contém a implementação completa da lixeira administrativa com restauração, confirmação por link da troca de e-mail, push em tempo real nem paginação avançada da moderação. Não criamos tabelas fictícias para esses fluxos pendentes.

**Atenção ao rollback do chat:** após novas mensagens serem gravadas no MySQL, simplesmente voltar a flag para `0` pode ocultar histórico recente. Um rollback deve conciliar os dados e preservar backups consistentes.
