# JARVIS Admin — Fases 1 a 3 (experimental)

Código na branch `feature/jarvis-admin`, PR #7. **Não integrado à main**.

## Recursos implementados
- Widget flutuante e responsivo em todas as páginas do painel ADM.
- Chat com provedores OpenAI-compatible (`groq`, `openrouter`, `openai`), selecionados por configuração no servidor.
- **Memória persistente inspirada no conceito do OpenViking (não usa a biblioteca OpenViking):** conversa e mensagens gravadas no MySQL, isoladas por conta ADM; seleção, exclusão e busca por palavras individuais no histórico. Ainda **não** há busca semântica, memória vetorial ou tolerância a erros de digitação.
- **Central de atividades com três exemplos de uso (reports, vagas e relatório):** os exemplos apenas preenchem o formulário; nenhum job é executado.\n- **Central de atividades inspirada no conceito de inbox/approval:** criar tarefas, iniciar análise, concluir o registro ou cancelar; mudanças de estado exigem confirmação do usuário e geram auditoria na tabela de atividades. As tarefas são planejamentos manuais, não agentes executando em segundo plano.
- **Gerenciador sempre ativo, limitado ao DevIN:** seleciona automaticamente no máximo 2 orientações entre triagem de denúncias, qualidade de vagas, suporte a usuários e indicadores. Nenhuma das 26 skills do JarvisCode é executada no DevIN; trata-se de regras locais para elaborar respostas, não ferramentas nem autorização de escrita.\n- **Contexto com autorização:** histórico de conversas é salvo no MySQL, mas o envio das últimas 12 mensagens ao provedor está desligado por padrão e é habilitado pelo administrador no chat. Mensagem atual ainda é enviada para gerar resposta.\n- **Modelo usado visível nas respostas novas:** provedor, ID de modelo e sinalização de uso do fallback (se ocorrer). O modo Resumo usa somente dados locais.\n- Resumo agregado de candidatos, empresas, vagas e denúncias pendentes (sem dados pessoais enviados ao modelo).
- Autenticação ADM com conferência da conta no banco, CSRF nas escritas, limites, SQL preparado, IDs restritos por administrador e endpoint HTTPS de provedor fixo.
- O chat **não** executa ações administrativas. Concluir uma atividade não apaga vagas, empresas ou contas.

## Instalação
1. Crie ou use um banco MySQL do DevIN e **faça backup antes de executar migrações**.
2. Execute `database/migrations/jarvis_admin.sql` uma vez. A migração cria **apenas** as quatro tabelas JARVIS: `jarvis_conversations`, `jarvis_messages`, `jarvis_tasks`, `jarvis_task_audit`.
3. Configure o `.env` real no servidor (não faça commit):
   ```dotenv
   DEVIN_JARVIS_PROVIDER=openrouter
   DEVIN_JARVIS_MODEL=z-ai/glm-5.3-flash:free
   DEVIN_JARVIS_API_KEY=
   ```
   Sem a chave, o **Resumo do DevIN**, a memória e a fila continuam locais, mas o chat retorna uma mensagem de configuração. Não reutilize a chave do Gemini dos currículos.
4. Verifique a extensão cURL e `mbstring` do PHP, as dependências Composer do DevIN, e se a hospedagem permite conexões HTTPS com o provedor.
5. Acesse `php/adm.php` com sessão ADM válida, abra o ícone JARVIS e teste as abas **Conversas** e **Atividades**.

> O ID `z-ai/glm-5.3-flash:free` é um exemplo encontrado no OpenRouter em novembro de 2026. Modelos e cotas podem mudar. A variante gratuita tem limite de uso; revise disponibilidade, termos de dados e custos antes de colocá-la em produção. Alternativamente, escolha outro ID suportado. O fallback só é tentado **no mesmo provedor OpenRouter**, com a mesma chave e quando `DEVIN_JARVIS_FALLBACK_MODEL` está configurado com um ID terminado em `:free`. Só responde a falhas temporárias (por exemplo HTTP 429/503); não muda de provedor, não seleciona modelos pagos nem garante disponibilidade.

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
- No momento não há buscas de entidades administrativas pelo modelo, workers em segundo plano, ferramenta de ações destrutivas, anexos ou streaming.

## Roteiro de testes antes de merge
1. Executar `php -l` nos endpoints, controllers e widget; `node --check js/jarvis-admin.js` (workflow automático incluído).
2. Testar celular e desktop; campo multiline, conversas separadas, nova conversa, exclusão e pesquisa parcial.
3. Confirmar que um administrador não pode acessar conversa ou tarefa de outro ADM por ID.
4. Testar POST sem CSRF (403), sessão expirada (401), conta não-ADM (403), payload gigante (413), limites de taxa (429).
5. Confirmar transição de tarefa: `pending -> in_progress -> done` ou cancelamento; confirmar que pedidos repetidos não executem novamente.
6. Testar configuração ausente de modelo (503), modelo gratuito disponível, falha HTTP/429 do provedor e consulta de resumo sem API Key.
7. Certificar que HTML fornecido por mensagem ou descrição de tarefa não seja interpretado como código pelo navegador.
8. Verificar que a migração SQL ocorre apenas no banco de desenvolvimento e que não há chaves em commits.

## Próxima fase
Revisar arquitetura e testes reais no servidor. Depois: consultas administrativas limitadas por ferramentas; propostas de ações com confirmação transacional e auditoria detalhada, sem jamais permitir SQL/execução arbitrária do modelo. Persistência e tarefas desta fase não autorizam o modelo a editar registros reais.

## Mudanças adaptadas do JarvisCode V3 (escopo limitado)

Adaptações próprias, sem copiar dependências de terceiros:
- **SkillManager**: seleção determinística por palavras-chave e até duas especializações do próprio portal; o gerenciador permanece ativo, sem executar scripts ou ações administrativas.
- **ModelRouter**: fallback opcional somente para modelo gratuito e já autorizado no mesmo provedor OpenRouter; um pedido curto normalmente usa uma única chamada de IA.
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

A CI executa os testes sem banco de dados e sem chave de API. O uso real do chat, a migração e a UI ainda requerem testes manuais no servidor de desenvolvimento.

## Verificação administrativa sequencial (dependência da Fase 1 LGPD)

A partir desta revisão, o JARVIS Admin exige uma verificação sequencial antes de qualquer acesso a conversas, tarefas, resumo, memória ou provedor de IA. A interface mostra **somente** os campos `Informe o Token 1` e depois `Informe o Token 2` e não exibe a finalidade dos tokens.

Os valores digitados são enviados exclusivamente para a API interna `php/api/admin_security.php`, via HTTPS no mesmo domínio, e **nunca** para o endpoint de chat ou provedores externos. O modelo não participa do desbloqueio.

O `jarvisAdmin()` passa a bloquear por padrão todos os endpoints existentes `chat.php`, `tasks.php` e `conversations.php` com erro HTTP 423 enquanto a verificação não estiver concluída. Em caso de ausência da camada de segurança, erro de banco ou flag desabilitada, responde 503 e **não** permite acesso à IA.

**Dependências obrigatórias:** PR #8 deve ser implantado primeiro, incluindo migration `database/migrations/2026_11_09_002_admin_stepup.sql`, a credencial individual dos quatro administradores e `DEVIN_ADMIN_STEPUP_ENABLED=1`. Só então o PR #7 pode ser ativado. O histórico é persistido no MySQL, mas o contexto só é enviado ao modelo conforme a escolha explícita do ADM, como já previsto.

As autorizações do site e da IA são separadas, mantidas no servidor, com expiração; detalhes operacionais em `docs/admin-stepup-security.md` (PR #8). Não ative um JARVIS sem backend de verificação funcional.
