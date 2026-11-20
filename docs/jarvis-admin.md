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


## Conversas antigas, persistência de atendimentos e fallback gratuito (20/11/2026)

O histórico de conversas JARVIS é individual por ADM: o MySQL `jarvis_conversations` e `jarvis_messages` só devolve os registros do `admin_id` autenticado. A caixa de seleção "Usar mensagens anteriores como contexto da IA" **não** recupera mensagens não salvas: apenas autoriza o envio de mensagens já registradas ao modelo remoto.

**Mudanças:** o widget passa a registrar os comandos de suporte e as respostas/retornos exibidos nos cartões no histórico do ADM, por meio da ação autenticada e protegida por CSRF `conversations.php?action=append` (POST). Todos os cartões continuam sem execução autônoma de ações administrativas. As falhas de IA também registram as perguntas do ADM e o erro exibido, quando o banco está disponível. Na próxima abertura, a última conversa salva aparece automaticamente; o usuário pode escolher outra conversa no seletor, buscar por texto ou usar o botão `+` para criar uma nova. O histórico retorna até 100 mensagens da conversa e a listagem mostra até 100 conversas mais recentes. Os controles interativos de cartões antigos não são recriados ao carregar mensagens: para reabrir, selecione o chamado de suporte pelo ID.

**Limite importante:** interações anteriores a esta alteração, que só apareceram no DOM e nunca foram gravadas no banco, **não podem ser recuperadas**. Nenhuma migração SQL destrutiva e nenhuma alteração nas conversas já salvas. O transcript da interface não substitui a auditoria oficial de respostas e transferências de suporte; é apenas histórico de conversa. Os registros permanecem sujeitos às permissões por ADM.

**Comandos locais:** "Quais são os suportes que eu estou de representante?" e "Mostre os chamados na minha jurisdição" acessam diretamente o banco e não dependem da API de IA. A consulta de casos e o histórico de respostas do suporte são operações distintas das conversas persistidas do JARVIS.

**Modelo indisponível:** se o provedor está configurado como OpenRouter e o modelo gratuito escolhido retornar erro de modelo (`400/404/422`), o servidor tenta **uma única** recuperação usando `openrouter/free` com a mesma chave; não solicita modelos pagos. O roteador gratuito oficial é o valor recomendado em `.env.example`. Esta proteção não resolve chaves revogadas, cotas esgotadas, ausência de rede ou modelos de visão incompatíveis. A chave real é configurada **somente** no `.env` da Hostinger, fora do repositório.

**Teste manual:** autentique-se como ADM, abra um chamado, peça a listagem dos chamados da sua jurisdição, selecione-o, recarregue a página, abra o chat e confira se o histórico reaparece. Faça a mesma consulta com "suportes" no plural (sem IA). Depois, com um caso de teste consentido, solicite análise por IA e verifique o modelo efetivamente usado e o aviso de fallback. Jamais publique chaves em prints ou GitHub.

**Verificação na Hostinger:** depois de atualizar a `main`, entre como ADM e consulte `php/api/jarvis/readiness.php`. O novo objeto `model_diagnostic` mostra provedor, ID configurado, política gratuita, cURL e indicação `live_provider_checked=false`, sem revelar segredos. Se continuar vendo "modelo solicitado não está disponível", confirme no **.env real da Hostinger** `DEVIN_JARVIS_PROVIDER=openrouter` e `DEVIN_JARVIS_MODEL=openrouter/free`; preserve a chave real existente e não a envie por screenshots. Se o erro persistir, confira a categoria HTTP no log do servidor, quota e disponibilidade do provedor. O exemplo do GitHub não substitui o .env hospedado.

## Interpretação de linguagem natural sem OpenRouter — 20/11/2026

O chat de administração ganhou o interpretador `js/jarvis-intents.js`, executado **antes** das chamadas à IA externa. Sua função é converter diferentes formas de pedir informações do DevIN em uma intenção **somente de leitura**, com domínio e escopo definidos. Exemplos equivalentes: "quais suportes estou representando?", "chamados da minha jurisdição", "tickets que estão comigo", "mostre meus atendimentos" e "quais solicitações assumi?". Todos usam a carteira de chamados vinculada ao `admin_id` da sessão, jamais um ID passado pelo navegador.

Consultas reconhecidas: (1) meus chamados atribuídos; (2) fila geral de suporte; (3) denúncias que o ADM pode moderar; (4) detalhe de chamado/denúncia por ID e tipo inequívoco; (5) indicadores agregados de empresas, pessoas, vagas e candidaturas. O código normaliza acentos, plurais, abreviações e alguns erros comuns. **Não é um modelo semântico universal**: casos ambíguos exigem esclarecimento, e pedidos não relacionados ao banco seguem o chat normal. Informações abstratas como "como melhorar o suporte" não devem abrir a fila.

O interpretador não gera SQL, não escolhe alvos arbitrários, não executa ações administrativas e não recebe credenciais. O backend permanece responsável por validar sessão, CSRF, propriedade do chamado e RBAC. Decisões, respostas e exclusões continuam exigindo a confirmação explícita do ADM nos fluxos já existentes. Pedidos de análise por IA continuam sujeitos à autorização de compartilhamento do contexto do caso.

Para verificar, abra o JARVIS na Hostinger, tente as frases de `tests/jarvis-intents.test.cjs` e confirme que listagens funcionam **mesmo com o provedor indisponível**. Perguntas de opinião devem continuar indo para o modelo externo; perguntas ambíguas devem pedir esclarecimento, sem adivinhar. Os testes Node são executados pelo GitHub Actions. Não há novo SQL nem novas variáveis de ambiente.


## Interpretação natural de ferramentas administrativas (20/11/2026)

O módulo `js/jarvis-admin-natural.js`, carregado antes do chat ADM, reconhece múltiplas formulações sem chamar OpenRouter. Operações suportadas pelo backend atual: pesquisa de pessoas, empresas e vagas por nome/título/ID; listagem de até 12 cadastros recentes por tipo; detalhamento de registros; preparação de rascunhos de mensagens para pessoa ou empresa; listagem de rascunhos; leitura do histórico de auditoria; leitura das conversas salvas pelo ADM; leitura/criação/atualização da própria fila de tarefas.

Exemplos de leitura: "mostre todas as vagas", "ache a empresa Acme", "localize o candidato João Silva", "quais tarefas estão pendentes?", "mostre meus rascunhos", "quero ver o histórico das ações", "abra as conversas antigas". A interpretação não inventa filtros SQL: buscas genéricas apresentam um máximo explícito de 12 resultados por categoria, e pesquisas por nomes mostram escolhas com ID.

Exemplos de ação: "crie uma tarefa para revisar anúncios", "conclua tarefa #7", "mande mensagem para a candidata Maria com o texto: Olá", "remova a vaga Desenvolvedor PHP". **Nenhuma dessas frases executa a ação imediatamente.** A tarefa exige botão e confirmação; mensagens são rascunhos, enviados apenas após seleção do destinatário, revisão do conteúdo e confirmação explícita com a palavra ENVIAR; exclusões exigem a escolha do registro, prévia, confirmação e a palavra EXCLUIR. Os fluxos atuais do backend continuam responsáveis por RBAC, autenticação, CSRF, auditoria e isolamento dos dados. A IA externa não recebe nem escolhe identificadores de ações administrativas.

Consultas a chamados e denúncias continuam sob `js/jarvis-intents.js` e `js/jarvis-cases.js`, respeitando "minha jurisdição" como o suporte do ADM representante. Conversas sobre conceitos e perguntas sem intenção administrativa identificável continuam no chat normal. O módulo não é um modelo de linguagem universal; pedidos ambíguos solicitam mais detalhes em vez de executar ações.

Testes: `node --test tests/jarvis-admin-natural.test.cjs` e `node --test tests/jarvis-chat-commands.test.cjs`; validar no celular, após atualizar a hospedagem com `main`. Nenhuma migração SQL nova. `.env` e chaves devem continuar fora do GitHub.


## Roteamento semântico de consultas ADM, sem migração nem novas chaves (20/11/2026)

O JARVIS continua verificando **primeiro** as regras determinísticas de suporte, reports e ferramentas ADM. Somente quando nenhuma regra reconhece a frase, e ela parece uma consulta administrativa de leitura, o navegador consulta `php/api/jarvis/intent.php`. O servidor exige sessão ADM ativa, segundo fator quando habilitado, CSRF, limite de 18 solicitações por hora/ADM e usa as configurações `DEVIN_JARVIS_PROVIDER`, `DEVIN_JARVIS_MODEL` e `DEVIN_JARVIS_API_KEY` **já existentes**; não exige novo `.env` nem migração MySQL. Com OpenRouter, o código atual aceita exclusivamente modelos `:free` ou `openrouter/free` e aplica os limites do serviço.

**Privacidade e segurança:** para essa classificação não são enviados registros do banco, histórico anterior, anexos ou chaves. A frase atual é truncada e passa por ocultação de e-mails, telefones, documentos, credenciais e alguns nomes próprios. A sugestão é estritamente um destes rótulos de leitura: `support_mine`, `support_queue`, `reports_queue`, `overview`, `jobs`, `people`, `companies`, `tasks`, `drafts`, `audit`, `conversations`. O modelo só devolve JSON validado com confiança mínima de 0,86; nenhuma outra palavra, propriedade, ID ou texto do modelo é executado. Pedidos destrutivos, respostas, envio de mensagens, mudança de tarefas e ações administrativas seguem seus controles locais com revisão e confirmação humana. A proteção efetiva permanece no backend, com RBAC, titularidade, CSRF e verificações por ID.

**Quando o provedor falha:** nenhum resultado é inventado e nenhuma alteração é executada; o chat sugere um comando local equivalente. Quando a frase não sugere consulta administrativa, ela segue ao chat geral sem classificação adicional. Respostas ambíguas não executam consultas. A classificação semântica **não garante** compreender qualquer expressão, nem substitui o teste real de modelo gratuito na Hostinger. Quando a configuração de ferramentas existentes está desativada, essas ferramentas continuam indisponíveis; o classificador não altera flags ou permissões.

**Testes locais/CI:** `php tests/jarvis_semantic_intent.php` simula a resposta do modelo sem rede e verifica lista permitida, filtro de dados pessoais e negação de alterações. `node --test tests/jarvis-semantic.test.cjs` verifica fronteira de consultas e respostas inválidas. Na Hostinger, após sincronizar a `main`, teste com o JARVIS: "Poderia fazer um panorama dos indicadores do DevIN?", "Consegue localizar os atendimentos em que estou responsável?" e "Será que existem vagas cadastradas no portal?". Compare sempre resultados com a página administrativa; não publique o `.env` real.
