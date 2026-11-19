# DevIN — alterações aplicadas, códigos explicados e implantação segura

**Revisão técnica:** 18/11/2026, branch `main`. Esta entrega prioriza segurança dos dados e estatísticas do ADM sem desabilitar recursos já utilizados. Não executar SQL ou trocar configurações em produção sem backup.

## 1. O que esta entrega implementa

### Armazenamento de mensagens no MySQL (opcional, padrão desativado)

Arquivos:

- `php/controllers/ChatStorage.php`: mantém a mesma API de callbacks de mensagens e conversas, mas passa a usar uma transação InnoDB quando `DEVIN_CHAT_MYSQL_ENABLED=1`. A leitura de mensagens legadas ocorre antes da primeira gravação.
- `database/migrations/2026_11_19_007_chat_mysql_state.sql`: cria `devin_chat_state(resource, payload, updated_at)`. Há duas linhas independentes: `mensagens` e `conversas`.
- `tests/chat_mysql_storage.php`: verifica importação de arquivo antigo, gravação persistente, leitura subsequente, rollback e separação dos recursos usando banco descartável.
- `.env.example`: documenta `DEVIN_CHAT_MYSQL_ENABLED=0`.

**Por que este desenho?** A API atual trabalha com arrays de mensagens, exclusões e indicadores de leitura. Persistir o documento completo no banco primeiro evita reescrever todo o protocolo de mensagens simultaneamente. `SELECT ... FOR UPDATE` serializa gravações concorrentes, e o arquivo JSON anterior não é excluído. Se o MySQL falhar com a flag ativa, a aplicação retorna erro ao invés de escrever silenciosamente em dois lugares diferentes.

**Limitação:** isto é uma etapa de transição, **não** uma modelagem relacional definitiva por mensagem. Cada recurso fica em uma linha LONGTEXT; com um volume grande de conversas haverá bloqueio e custo de serialização. A etapa posterior deve migrar para tabelas `messages`, `conversations` e estado de leitura por participante, com paginação e índices. Mensagens permanecem texto claro no banco, protegido pelo controle de acesso da aplicação; não há criptografia de ponta a ponta.

### Indicadores administrativos

Arquivos:

- `php/controllers/AdminOverview.php`: consultas `SELECT COUNT(*)` a empresas, pessoas, vagas, candidaturas, contratações confirmadas, denúncias pendentes e chamados abertos. Retorna `null` quando alguma tabela não está instalada, sem inventar números.
- `php/components/admin-overview.php`: apresenta indicadores e empresas com mais vagas, com traduções PT/EN/ES.
- `php/adm.php`: nova aba `?pagina=estatisticas`, somente para contas autenticadas como ADM.
- `css/ui-consistency.css`: grade própria para métricas em celular, computador, TV e lousa digital.
- `tests/admin-overview.test.cjs`: verifica rota restrita, fontes de dados, consultas somente leitura e CSS.

O ranking é limitado a oito empresas. Não altera vagas, contas, candidaturas nem permissões.

### Resumo do JARVIS com métricas verificáveis

- `php/api/jarvis/admin_tools.php?action=overview`: rota GET de números agregados, sujeita ao login ADM, à flag geral das ferramentas e ao rate limit existentes.
- `php/components/jarvis-admin-widget.php`: expõe apenas se as ferramentas estão habilitadas; não envia credenciais.
- `js/jarvis-admin.js`: o botão **Resumo do DevIN** mostra os números reais do MySQL, identificando a fonte. Quando as ferramentas estão desligadas, mantém o resumo antigo. Nenhuma decisão ou alteração é executada automaticamente.

## 2. SQL / Hostinger — ordem segura

1. No hPanel, efetue backup completo do banco atual, dos arquivos do site e dos arquivos JSON antigos de mensagens. Os JSON atuais ficam no diretório temporário de PHP, com nome derivado do diretório do projeto. Se o arquivo não existir mais, nenhum código consegue recuperar mensagens já apagadas pelo servidor.
2. Em uma cópia de homologação, abra o phpMyAdmin e **selecione o banco do DevIN**. Importe somente `database/migrations/2026_11_19_007_chat_mysql_state.sql`. A migration é `CREATE TABLE IF NOT EXISTS`: não apaga tabelas existentes.
3. Confira usando SQL **somente leitura**:

   ```sql
   SHOW TABLES LIKE 'devin_chat_state';
   SELECT resource, CHAR_LENGTH(payload) AS tamanho, updated_at
   FROM devin_chat_state;
   ```

   Antes do primeiro acesso com a flag ativada, a tabela pode estar vazia — isso é esperado.
4. Mantenha `DEVIN_CHAT_MYSQL_ENABLED=0` enquanto testa a publicação do código. Publique todos os arquivos da `main` aprovada; o painel de Estatísticas já funcionará, sem necessidade de outra migration além das que suas métricas opcionais utilizam.
5. Para testar a migração do chat, garanta que nenhuma instância antiga do site esteja gravando mensagens em paralelo e que o histórico JSON esteja acessível ao PHP. Altere **somente na homologação** a variável:

   ```dotenv
   DEVIN_CHAT_MYSQL_ENABLED=1
   ```

   Abra a aba Mensagens com duas contas de teste. A primeira leitura importa o JSON antigo para o MySQL. Envie mensagens, marque como lidas, atualize as páginas e confira as duas linhas da tabela.
6. Verifique quantidade e tamanho da importação com o backup JSON original antes de ligar a flag na produção. Não ative a flag se a importação não copiar os dados; restaure os backups e investigue. Verifique os logs PHP em busca de erro de tabela, conexão ou tamanho de pacote MySQL.
7. **Nunca volte simplesmente a flag para 0 depois de haver novas mensagens no MySQL.** Isso criaria dois históricos divergentes. A recuperação deve restaurar um conjunto consistente de banco e arquivos a partir dos backups, com manutenção controlada.
8. Na produção, repita a migração e o teste em um período de baixo movimento. Faça um último backup após validar. Não copie `.env` de homologação sobre o `.env` real.

## 3. Recomendações de segurança e disponibilidade

- Mantenha **APP_ENV=production**, HTTPS e as credenciais reais privadas em `.env`.
- Mantenha `DEVIN_CHAT_MYSQL_ENABLED=0` até concluir SQL, backup e homologação.
- Se as ferramentas do JARVIS estiverem desativadas, **não** ligue `DEVIN_JARVIS_ACTIONS_ENABLED=1` só para ver o resumo: ela controla também outras operações administrativas. Configure papéis, permissões, confirmações e testes de segurança antes.
- Não altere bancos reais com scripts de teste; a integração automatizada usa `devin_chat_ci`.
- Não rode migrações dentro de uma transação manual de rollback, pois DDL do MySQL normalmente confirma implicitamente.

## 4. Melhorias solicitadas ainda NÃO concluídas nesta entrega

**Exclusões reversíveis:** as exclusões administrativas e decisões de denúncias ainda usam `DELETE`. Antes de mudar para lixeira, é preciso projetar status de suspensão para pessoas, empresas e vagas, alterar todas as consultas de login, vagas, candidaturas e denúncia e estabelecer regras de restauração de relações sem expor contas suspensas. Trocar apenas o botão ou guardar um JSON de registro apagado seria inseguro.

**Validação do novo endereço de e-mail:** a alteração atual usa `UPDATE` transacional e pede senha, mas ainda não envia um link/OTP de confirmação. A implementação segura requer armazenamento de troca pendente, token aleatório com hash, expiração, revalidação de unicidade, fluxo de confirmação e SMTP funcionando.

**Notificações verdadeiramente em tempo real:** o chat já verifica novas mensagens periodicamente; WebSockets/SSE com autenticação, limites de conexões e infraestrutura compatível com a Hostinger exigem homologação à parte. Atualizar a frequência de polling não é o mesmo que WebSocket.

**Moderação avançada de vagas:** a aba Vagas já permite busca e filtro por tags, mas filtros indexados de data/status/empresa e paginação por servidor continuam pendentes; dependem de verificar os campos e dados do banco de produção.

O JARVIS agora pode **exibir um resumo real agregado**, mas não passa a decidir denúncias ou excluir contas automaticamente. A aprovação humana permanece obrigatória.

## 5. Testes desta entrega

A CI executa PHP 8.1/8.5, verificações JavaScript, contratos de interface e integração em banco MySQL descartável, incluindo o novo teste de mensagens. Isso **não** substitui teste de dispositivos, SMTP, implantação e dados reais de produção.

## 6. Onde mexer depois

- Tela Estatísticas: `php/adm.php`, `php/components/admin-overview.php`.
- Cálculos: `php/controllers/AdminOverview.php`.
- Chat: `php/controllers/ChatStorage.php`, `php/mensagens_api.php`.
- SQL: `database/migrations/2026_11_19_007_chat_mysql_state.sql`.
- JARVIS: `php/api/jarvis/admin_tools.php`, `js/jarvis-admin.js`.
- Layout: `css/ui-consistency.css`.

**Conclusão:** a publicação no GitHub não muda sozinha o MySQL da Hostinger nem confirma a importação dos arquivos temporários. O procedimento acima é obrigatório para que o chat passe a usar MySQL sem risco desnecessário de perder dados.
