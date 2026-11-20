# JARVIS Admin — Fases 1 a 3 (experimental)

O chat JARVIS requer uma sessão ADM válida. A verificação adicional Token 2 é opcional e permanece desligada por padrão até que migração, cadastro dos administradores e homologação sejam concluídos.

## Recursos implementados
- Widget flutuante e responsivo em todas as páginas do painel ADM.
- Chat com provedores OpenAI-compatible (`groq`, `openrouter`, `openai`), selecionados por configuração no servidor.
- **Memória persistente inspirada no conceito do OpenViking (não usa a biblioteca OpenViking):** conversa e mensagens gravadas no MySQL, isoladas por conta ADM; seleção, exclusão e busca por palavras individuais no histórico. Ainda **não** há busca semântica, memória vetorial ou tolerância a erros de digitação.
- **Central de atividades com três exemplos de uso (reports, vagas e relatório):** os exemplos apenas preenchem o formulário; nenhum job é executado.
- **Central de atividades inspirada no conceito de inbox/approval:** criar tarefas, iniciar análise, concluir o registro ou cancelar; mudanças de estado exigem confirmação do usuário e geram auditoria na tabela de atividades. As tarefas são planejamentos manuais, não agentes executando em segundo plano.
- **Gerenciador sempre ativo, limitado ao DevIN:** seleciona automaticamente no máximo 2 orientações entre triagem de denúncias, qualidade de vagas, suporte a usuários e indicadores. Nenhuma das 26 skills do JarvisCode é executada no DevIN; trata-se de regras locais para elaborar respostas, não ferramentas nem autorização de escrita.
- **Contexto com autorização:** histórico de conversas é salvo no MySQL, mas o envio das últimas 12 mensagens ao provedor está desligado por padrão e é habilitado pelo administrador no chat. Mensagem atual ainda é enviada para gerar resposta.
- **Modelo usado visível nas respostas novas:** provedor, ID de modelo e sinalização de uso do fallback (se ocorrer). O modo Resumo usa somente dados locais.
- Resumo agregado de candidatos, empresas, vagas e denúncias pendentes (sem dados pessoais enviados ao modelo).
- Autenticação ADM com conferência da conta no banco, CSRF nas escritas, limites, SQL preparado, IDs restritos por administrador e endpoint HTTPS de provedor fixo.
- O chat não executa ações destrutivas. Ferramentas separadas permitem consultas limitadas e rascunhos de mensagens, com revisão e aprovação humana; concluir uma atividade não apaga vagas, empresas ou contas.

## Instalação
1. Use o banco MySQL já configurado para o DevIN; **não crie um banco local novo nem execute migrations na hospedagem sem comparar o schema e fazer backup**. Primeiro valide as migrations em uma cópia de desenvolvimento e confirme se as tabelas JARVIS já existem.
2. Se as tabelas ainda não existirem, aplique `database/migrations/jarvis_admin.sql` uma vez, pelo procedimento aprovado para o ambiente. Ela cria **apenas** as quatro tabelas JARVIS: `jarvis_conversations`, `jarvis_messages`, `jarvis_tasks`, `jarvis_task_audit`.
3. Configure o `.env` real no servidor (não faça commit):
   ```dotenv
   DEVIN_JARVIS_PROVIDER=openrouter
   DEVIN_JARVIS_MODEL=z-ai/glm-5.3-flash:free
   DEVIN_JARVIS_API_KEY=
   ```
   Sem a chave, o **Resumo do DevIN**, a memória e a fila continuam locais, mas o chat retorna uma mensagem de configuração. Não reutilize a chave do Gemini dos currículos.
4. Use PHP 8.1 ou superior com as extensões cURL, `mbstring` e `mysqli`; verifique as dependências Composer e se a hospedagem permite conexões HTTPS com o provedor.
5. Acesse `php/adm.php` com sessão ADM válida, abra o ícone JARVIS e teste as abas **Conversas** e **Atividades**.

> O ID `z-ai/glm-5.3-flash:free` é um exemplo encontrado no OpenRouter em novembro de 2026. Modelos e cotas podem mudar. A variante gratuita tem limite de uso; revise disponibilidade, termos de dados e custos antes de colocá-la em produção. Alternativamente, escolha outro ID suportado. O roteador OpenRouter usa até **10 modelos gratuitos reserva**, configuráveis em `DEVIN_JARVIS_FALLBACK_MODELS` (lista separada por vírgulas), com defaults no código e último recurso `openrouter/free`. Envia a cadeia no campo `models` em uma chamada, sem tentar cada modelo com requisições separadas. `DEVIN_JARVIS_API_KEY` é a chave principal e `DEVIN_JARVIS_BACKUP_API_KEY` é uma segunda chave opcional usada quando a principal falha por credencial, cota, rede ou indisponibilidade. Duas chaves da mesma conta podem compartilhar cota. A variável antiga `DEVIN_JARVIS_FALLBACK_MODEL` continua aceitando **somente ID de modelo**, nunca chave de API. Consulte `docs/jarvis-openrouter-dual-key.md`.

## Atualização do roteador OpenRouter (duas chaves)

- Configure `DEVIN_JARVIS_API_KEY` e opcionalmente `DEVIN_JARVIS_BACKUP_API_KEY`.
- `DEVIN_JARVIS_FALLBACK_MODELS` aceita até dez IDs gratuitos priorizados; se ficar vazia, usa dez reservas padrão.
- Erros do modelo agora informam `error_code` e `provider_http_status` seguros, sem chave, prompt ou resposta bruta.
- Metadados das respostas incluem `model`, `fallback_used` e `key_used` (nível `primary` ou `backup`).
- O login normal ADM continua obrigatório; Token 2 é opcional e segue a seção de acesso abaixo.
- Instalação e diagnóstico: `docs/jarvis-openrouter-dual-key.md`.

## Endpoints
- `POST php/api/jarvis/chat.php`: `{mode:"chat",message,page,conversation_id}`; retorna `{reply,conversation_id}`. `{mode:"summary"}` devolve dados agregados sem usar provedor externo.
- `GET php/api/jarvis/conversations.php`: `?q=palavra` lista conversas; `?id=123` recupera mensagens do ADM autenticado.
- `POST php/api/jarvis/conversations.php`: `{action:"create"}` ou `{action:"delete",conversation_id:123}` (UI cria nova conversa automaticamente no primeiro envio, sem gerar conversas vazias).
- `GET php/api/jarvis/tasks.php`: lista até 50 tarefas do administrador.
- `POST php/api/jarvis/tasks.php`: `{action:"create",title,description}`; ou `{action:"start"|"complete"|"cancel",task_id:123}`. **Somente altera o registro da tarefa**.

As escritas exigem cabeçalho `X-CSRF-Token`. Uma sessão inexistente retorna JSON 401 e um perfil não-ADM retorna JSON 403.

## Privacidade e limites
- Mensagens são armazenadas sem criptografia em nível de coluna no MySQL; proteja backups e acesso ao banco e defina uma política de retenção antes de uso real.
- O conteúdo digitado no chat é enviado ao provedor selecionado. Até 12 mensagens anteriores só são incluídas no pedido quando o administrador ativa **Usar mensagens anteriores como contexto da IA**. O MySQL continua guardando o histórico independentemente dessa escolha. Evite CPF, credenciais, dados pessoais de candidatos e conteúdo sensível de denúncias.
- O chat depende da disponibilidade da API e não garante precisão. A pesquisa atual divide a consulta em palavras e usa `LIKE`; não equivale a recuperação semântica.
- Consultas de entidades só estão disponíveis pelas ferramentas controladas, com seleção humana e campos limitados; não há workers em segundo plano, ações destrutivas, anexos ou streaming.

## Roteiro de testes de homologação
1. Executar `php -l` nos endpoints, controllers e widget; `node --check js/jarvis-admin.js` (workflow automático incluído).
2. Testar celular e desktop; campo multiline, conversas separadas, nova conversa, exclusão e pesquisa parcial.
3. Confirmar que um administrador não pode acessar conversa ou tarefa de outro ADM por ID.
4. Testar POST sem CSRF (403), sessão expirada (401), conta não-ADM (403), payload gigante (413), limites de taxa (429).
5. Confirmar transição de tarefa: `pending -> in_progress -> done` ou cancelamento; confirmar que pedidos repetidos não executem novamente.
6. Testar configuração ausente de modelo (503), modelo gratuito disponível, falha HTTP/429 do provedor e consulta de resumo sem API Key.
7. Certificar que HTML fornecido por mensagem ou descrição de tarefa não seja interpretado como código pelo navegador.
8. Verificar que a migração SQL ocorre apenas no banco de desenvolvimento e que não há chaves em commits.

## Próximos passos
Homologar a arquitetura no servidor, revisar privacidade e testar as ferramentas supervisionadas em ambiente de desenvolvimento. Busca semântica, workers e ações que alterem registros exigem implementação e revisão de segurança separadas; nunca permitir SQL ou execução arbitrária pelo modelo.

## Mudanças adaptadas do JarvisCode V3 (escopo limitado)

Adaptações próprias, sem copiar dependências de terceiros:
- **SkillManager**: seleção determinística por palavras-chave e até duas especializações do próprio portal; o gerenciador permanece ativo, sem executar scripts ou ações administrativas.
- **ModelRouter**: fallback nativo entre modelo principal e até dez reservas gratuitas, mais uma chave OpenRouter independente de backup, sem alternar para provedores diferentes nem modelos pagos.
- **Contexto e memória**: separação entre persistir histórico no MySQL e consentir em enviá-lo ao modelo.
- **Central de atividades**: três exemplos explicativos e botões para registrar andamento, sem automação, fila executável ou poderes de moderação.
- **Visibilidade**: metadados de modelo/skill por resposta nova. As mensagens antigas não guardam esses metadados na tabela atual.

Não são incluídos: execução Android (Shizuku/rish), voz, captura de tela, controle de aplicativos, as 26 skills genéricas, modelos locais on-device, MCP amplo, edição de código, criação de agentes autônomos, sessões de shell ou tarefas agendadas. Atualizações futuras exigem revisão de segurança e testes.

### Teste rápido

```bash
php tests/jarvis_admin_smoke.php
php -l php/controllers/Jarvis/AdminSkillManager.php
php -l php/controllers/Jarvis/ModelRouter.php
node --check js/jarvis-admin.js
```

A CI executa testes de sintaxe e habilidades sem chaves de API, além de integração MySQL isolada para memória, tarefas e isolamento dos administradores. O uso real do provedor externo e a UI mobile ainda exigem testes na hospedagem.

## Acesso administrativo e Token 2

O JARVIS Admin sempre exige **login normal do ADM** e valida a conta no banco. Com `DEVIN_ADMIN_STEPUP_ENABLED=0` (padrão), não solicita Token 2. Para ativar a etapa adicional, aplique primeiro `database/migrations/2026_11_09_002_admin_stepup.sql`, faça backup e homologue em desenvolvimento; os quatro ADMs precisam cadastrar o Token 2 individualmente em `php/JarvisT.php` **antes** de definir a variável como `1`. A etapa protege endpoints críticos e não deve ser ligada sem esse preparo. Consulte `docs/admin-stepup-security.md`.

Os endpoints JARVIS exigem autenticação ADM (sessão válida e existência da conta no MySQL). O servidor usa validação de CSRF nas escritas, limites de uso, separação de dados por ID de ADM quando aplicável e mensagens de erro sem expor segredos. As ferramentas de mensagens exigem revisão e aprovação explícitas; não há endpoints JARVIS para banir, excluir ou moderar automaticamente.

**Não confundir com publicação:** merge na `main` não aplica automaticamente migrations nem configura chaves de IA na hospedagem. O chat depende de `database/migrations/jarvis_admin.sql` para histórico e de uma chave válida do provedor para gerar respostas. Ferramentas supervisionadas exigem também `database/migrations/jarvis_admin_controlled_actions.sql`; Token 2 exige a migration própria e cadastro prévio. O resumo agregado não usa API de modelo.


## Ferramentas administrativas supervisionadas

O JARVIS incorpora ferramentas adicionais descritas em `docs/jarvis-controlled-actions.md`: **Consultar** candidatos/empresas/vagas/denúncias (leitura restrita), **Analisar** um registro apenas mediante seleção humana, **Mensagens** com rascunho individual gerado por IA e **Histórico** compartilhado de eventos por ADM responsável. A IA NÃO executa banimentos, deleções, alterações de cadastro ou decisões automáticas sobre denúncias. A entrega de mensagens exige POST separado, aprovação explícita e quotas no backend, não apenas no JavaScript. Para ativar a fase, instalar a migration adicional e usar `DEVIN_JARVIS_ACTIONS_ENABLED=1` somente após testes em desenvolvimento. O canal oficial de mensagens usa atualmente arquivos temporários; a outbox e a auditoria novas são persistidas em MySQL. Acesse o guia técnico para riscos e limitações.

## Casos de suporte e reports pelo chat do JARVIS

O assistente pode listar os casos existentes, selecionar um chamado ou denúncia, ajudar a analisar o texto ou prints com consentimento e preparar um parecer que o ADM poderá editar integralmente. A interface principal é a aba Conversas do JARVIS, não o formulário de resposta do Suporte.

Após a migração 008, 'minha jurisdição' no suporte significa os chamados cujo ADM da sessão é o representante. A fila geral permanece compartilhada e denúncias de vagas/perfis continuam moderadas por permissão RBAC, sem atribuição individual. A prévia de envio ou moderação não é uma decisão: exige uma segunda requisição autenticada e confirmação específica, com palavra diferenciada para exclusões. Exclusões de vagas e perfis continuam irreversíveis e exigem respaldo humano.

Os prints são opcionais e somente imagens PNG/JPG/WebP são aceitas (até 4 MB). A extensão GD do PHP precisa estar ativa para recodificar a imagem e descartar metadados. A conta JARVIS precisa ter um modelo que suporte visão; não se promete que todos os modelos gratuitos aceitem imagens. O consentimento abrange o envio ao provedor externo, inclusive informações eventualmente visíveis no próprio print.

A geração e a revisão de currículo **não usam** o mesmo provedor do JARVIS: elas têm endpoints Gemini próprios. Para inspecionar as dependências da Hostinger com a conta de candidato, use php/resume_ai_readiness.php. O diagnóstico verifica presença de chave e extensões, não prova conectividade, quota ou qualidade do modelo. Teste efetivo requer gerar currículo fictício e revisar PDF/DOCX consentidos; consulte tests/resume_abnt_contract.php.


## Jurisdição = representante do chamado

O comando "JARVIS, me ajuda com os reports/chamados que estão na minha jurisdição" agora solicita **somente suporte atribuído a mim** (`assigned_admin_id`). Não se deve interpretar jurisdição como todos os records do nível de permissão do ADM. Se um chamado não foi assumido, aparece na fila compartilhada e não na carteira individual.

O ADM pode usar **Assumir suporte** ou **Liberar atendimento** no chat do JARVIS. A tomada de um caso de outro ADM pede confirmação explícita. A resposta definitiva e o encerramento só são permitidos ao representante atual; a primeira resposta de um chamado livre cria a atribuição. Todos os ADMs ainda podem consultar o histórico. Banco: `database/migrations/2026_11_20_008_support_representatives.sql`. Sem SQL, o recurso antigo continua disponível, mas a representação não fica habilitada.
