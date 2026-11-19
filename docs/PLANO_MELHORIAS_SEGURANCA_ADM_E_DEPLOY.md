# DevIN — melhorias administrativas, explicação do código e implantação segura

**Base verificada:** branch `main`, 19/11/2026. Este documento distingue funcionalidades implementadas, ativação pendente e melhorias futuras. **O GitHub não atualiza automaticamente a Hostinger sem um deploy configurado.**

## 1. Estado real de cada melhoria solicitada

| Melhoria | Estado no código | Falta para uso real |
|---|---|---|
| Chat persistente no MySQL | **Implementado, opt-in**, em `php/controllers/ChatStorage.php` e migration `2026_11_19_007_chat_mysql_state.sql` | Backup, instalar migration, homologar importação do JSON e ativar `DEVIN_CHAT_MYSQL_ENABLED=1` |
| Estatísticas do ADM | **Implementado** em `AdminOverview.php` e `components/admin-overview.php` | Publicar e confirmar tabelas/consultas no banco real |
| Resumo de indicadores no JARVIS | **Implementado parcialmente**; não significa autonomia para modificar dados | Validar provedores, permissões e respostas |
| Lixeira e restauração | **Pendente**; `adm.php` ainda usa exclusão física | Projetar migrations, vínculos e política de retenção; homologar antes de substituir DELETE |
| Confirmação do novo e-mail | **Parcial**: exige senha atual, transação e validação de unicidade | Criar confirmação por token temporário no novo endereço e recuperação segura |
| Notificações em tempo real | **Pendente**; há mecanismos de mensagens/notificações, não uma conexão push completa | Definir atualização incremental, autenticação, limites e testes |
| Filtros avançados e paginação ADM | **Pendente** | Adicionar consultas paginadas e filtros no servidor, mantendo isolamento por tipo |
| Moderação assistida pelo JARVIS | **Parcial**: ferramentas e resumo existentes, sob controles de autorização | Expandir análises sem autorizar exclusões automáticas |

**Nota sobre chat:** a implementação MySQL guarda **dois documentos JSON** na tabela InnoDB, para preservar o contrato atual. Isso fornece persistência transacional, mas ainda **não é uma tabela relacional por mensagem**. O sistema antigo usa arquivos em `sys_get_temp_dir()`; a migração precisa preservar esses arquivos até a importação ser verificada.

## 2. Explicação dos arquivos e fluxo

### `php/controllers/ChatStorage.php`
- `acessarArmazenamentoMensagens($operacao, $recurso)` é a porta de entrada para ler ou atualizar mensagens e conversas.
- Com `DEVIN_CHAT_MYSQL_ENABLED=0`, usa JSON legado e `flock` para evitar escrita simultânea.
- Com `DEVIN_CHAT_MYSQL_ENABLED=1`, chama `acessarArmazenamentoMensagensMysql`, que usa transação, `SELECT ... FOR UPDATE`, JSON e `COMMIT`; uma exceção provoca `ROLLBACK`.
- Na primeira inicialização, a rotina pode importar o conteúdo do arquivo legado para `devin_chat_state`. **Não apague os JSON antes de conferir mensagens antigas, novas e conversas.**
- A tabela é compartilhada por recursos (`mensagens` e `conversas`), e o conteúdo continua protegido pelo controle de acesso da API chamadora.

### `database/migrations/2026_11_19_007_chat_mysql_state.sql`
- Cria `devin_chat_state` com `resource` como chave primária, `payload LONGTEXT` e `updated_at`.
- Usa InnoDB para permitir transações. `CREATE TABLE IF NOT EXISTS` não deve ser interpretado como validação de um esquema já existente.

### `php/controllers/AdminOverview.php`
- `devinAdminOverview(mysqli $db)` executa contagens agregadas: empresas, pessoas, vagas, candidaturas, contratações confirmadas, denúncias e suporte.
- Retorna `null` quando alguma consulta depende de tabela ausente, evitando apresentar zero como se fosse dado real.
- Calcula um ranking de empresas por número de vagas; esse ranking é **somente leitura**.

### `php/components/admin-overview.php`
- Mostra cartões de métricas e o ranking com textos em português, inglês e espanhol.
- Renderiza números com `number_format` e conteúdo textual com `h()` para escape de HTML.
- A leitura de métricas é feita ao abrir a página; **não é atualização em tempo real**.

### `php/adm.php`
- Organiza as seções de empresas, pessoas, vagas, denúncias e ferramentas administrativas.
- Empresas e Vagas agora têm listas e contadores separados.
- Atenção: ações `admin_delete_*` ainda executam exclusão física. **Não considerar que já existe lixeira.**

### `php/controllers/ProfileController.php`
- `updateProfile` valida os campos, exige senha atual para troca de e-mail, confere unicidade e só atualiza a sessão após persistência.
- Ainda não envia um link de confirmação para o **novo** e-mail antes de ativá-lo.

### `php/controllers/EmploymentWorkflow.php`
- Registra a confirmação bilateral de contratação; não é um mecanismo de cobrança ou contrato trabalhista.
- Mantém o histórico de candidaturas, com confirmação da empresa e depois da pessoa.

## 3. Publicar com segurança na Hostinger

1. Confirme que a `main` passou nas ações **JARVIS Admin — syntax checks** e **DevIN — comentários explicativos inline**.
2. Anote o SHA exato da versão aprovada.
3. Faça backup completo do **MySQL**, dos **arquivos hospedados**, do diretório de **uploads** e dos **JSON legados do chat**, que podem estar em diretório temporário fora da raiz do site. Se não conseguir localizar/copiar os JSON, **não ative a migração do chat**.
4. Teste a atualização em cópia/homologação, com banco separado. Não use o banco real para testes destrutivos.
5. Atualize os arquivos da `main` via Git/SFTP ou o mecanismo de deploy já configurado. **Preserve o `.env` real, uploads e segredos.**
6. No phpMyAdmin da cópia, verifique se `devin_chat_state` já existe e se sua estrutura corresponde à migration. Se não existir, aplique **somente** `database/migrations/2026_11_19_007_chat_mysql_state.sql`.
7. **Primeiro mantenha `DEVIN_CHAT_MYSQL_ENABLED=0`.** Confirme que o chat antigo funciona. Depois, na homologação, mude para `1`, reinicie/recarregue o PHP conforme a hospedagem e teste: histórico anterior, novas mensagens, conversa entre pessoa/empresa, notificações de suporte, acesso cruzado e repetição após recarregar.
8. Confira `SELECT resource, LENGTH(payload) AS bytes, updated_at FROM devin_chat_state;` em homologação. Verifique também a integridade funcional do histórico; quantidade de bytes sozinha não prova importação correta.
9. Só depois de passar os testes, repita **backup → migration → ativação** na produção, preferencialmente em janela de baixo uso. Durante a troca, evite mensagens simultâneas nos dois backends para não dividir o histórico.
10. Acesse como pessoa, empresa e ADM; teste login, perfis, mudança de e-mail, mensagens, suporte, vagas, candidaturas, denúncias e métricas. Não execute exclusões reais como teste.
11. Se houver erro, preserve logs sem segredos, restaure o código/flag conforme o procedimento de rollback e avalie **se há mensagens novas no MySQL antes de voltar para JSON**. O rollback da flag sem conciliação pode ocultar mensagens recentes.

**Importante:** não rode migrations comerciais, não habilite exclusões automáticas do JARVIS e não divulgue API keys, senha MySQL, tokens ou `.env` em prints.

## 4. Próximas implementações — critérios mínimos de aceite

### Etapa A — lixeira administrativa
- Definir exclusão lógica em pessoa, empresa e vagas, tratamento dos vínculos e regra de retenção conforme LGPD.
- Registrar autor, motivo, data, entidade e recuperação em auditoria imutável ou protegida.
- Ajustar login, listagens, vagas públicas, candidaturas e APIs para ignorar registros inativos.
- Restringir restauração a ADM autorizado com confirmação adicional; impedir que o JARVIS execute diretamente.
- Testar restauração com vínculos e migração de dados existentes.

### Etapa B — verificação do novo e-mail
- Salvar solicitação pendente, com token aleatório de uso único, hash, prazo e limite de tentativas.
- Enviar confirmação para novo e-mail; notificar o antigo sobre a solicitação sem expor token.
- Somente alterar o endereço após validação; manter login antigo funcional até confirmar.
- Tratar token expirado, e-mail já ocupado, múltiplas solicitações e falha SMTP.

### Etapa C — moderação, filtros e paginação
- Aplicar filtros por empresa, período, status e denúncias via SQL parametrizado.
- Paginar no servidor, limitar tamanho da página e evitar carregar todas as vagas.
- Garantir que os contadores e permissões não misturem empresas com vagas.
- Adicionar testes para filtros vazios, termos especiais e acesso não autorizado.

### Etapa D — notificações
- Criar endpoint incremental por usuário autenticado com cursores e índice de eventos.
- Atualizar a interface sem recarregar a página, com intervalos e limites conservadores.
- Não expor notificações de outra conta; implementar deduplicação, leitura e acessibilidade.
- Avaliar SSE/WebSocket somente após confirmar suporte e limites da Hostinger.

### Etapa E — JARVIS para moderação
- Permitir consultas e resumos com dados minimizados e rastreabilidade de fontes.
- Separar sugestões da IA de decisões administrativas.
- Exigir aprovação humana, autorização específica e trilha de auditoria para ações sensíveis.
- Testar prompts adversariais e vazamento de dados entre perfis.

## 5. Checklist de apresentação

- [ ] Código aprovado na `main` e publicado no diretório correto.
- [ ] Backups de banco, uploads, configurações e JSON legados verificados.
- [ ] Migrations existentes conferidas antes de executar SQL.
- [ ] Chat MySQL testado em homologação; flag mantida em `0` até então.
- [ ] Empresas, Vagas e Pessoas isoladas no painel ADM.
- [ ] Estatísticas mostram números reais ou **Indisponível** para tabelas ausentes.
- [ ] JARVIS não executa exclusões sem controles habilitados e testados.
- [ ] Fluxos pessoa/empresa/ADM testados sem afetar dados reais.
- [ ] Procedimento de rollback documentado e disponível.

**Conclusão:** persistência MySQL opt-in e indicadores administrativos estão codificados; a lixeira, a verificação por e-mail, notificações em tempo real, filtros avançados e a ampliação da moderação por IA são **etapas pendentes**, não devem ser anunciadas como concluídas.
