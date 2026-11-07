# JARVIS Admin — Fases 1 e 2 (experimental)

Código na branch `feature/jarvis-admin`, PR #7. **Não integrado à main**.

## Recursos implementados
- Widget flutuante e responsivo em todas as páginas do painel ADM.
- Chat com provedores OpenAI-compatible (`groq`, `openrouter`, `openai`), selecionados por configuração no servidor.
- **Memória persistente inspirada no conceito do OpenViking (não usa a biblioteca OpenViking):** conversa e mensagens gravadas no MySQL, isoladas por conta ADM; seleção, exclusão e busca por palavras individuais no histórico. Ainda **não** há busca semântica, memória vetorial ou tolerância a erros de digitação.
- **Central de atividades inspirada no conceito de inbox/approval:** criar tarefas, iniciar análise, concluir o registro ou cancelar; mudanças de estado exigem confirmação do usuário e geram auditoria na tabela de atividades. As tarefas são planejamentos manuais, não agentes executando em segundo plano.
- Resumo agregado de candidatos, empresas, vagas e denúncias pendentes (sem dados pessoais enviados ao modelo).
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

> O ID `z-ai/glm-5.3-flash:free` é um exemplo encontrado no OpenRouter em novembro de 2026. Modelos e cotas podem mudar. A variante gratuita tem limite de uso; revise disponibilidade, termos de dados e custos antes de colocá-la em produção. Alternativamente, escolha outro ID suportado. Não existe fallback automático.

## Endpoints
- `POST php/api/jarvis/chat.php`: `{mode:"chat",message,page,conversation_id}`; retorna `{reply,conversation_id}`. `{mode:"summary"}` devolve dados agregados sem usar provedor externo.
- `GET php/api/jarvis/conversations.php`: `?q=palavra` lista conversas; `?id=123` recupera mensagens do ADM autenticado.
- `POST php/api/jarvis/conversations.php`: `{action:"create"}` ou `{action:"delete",conversation_id:123}` (UI cria nova conversa automaticamente no primeiro envio, sem gerar conversas vazias).
- `GET php/api/jarvis/tasks.php`: lista até 50 tarefas do administrador.
- `POST php/api/jarvis/tasks.php`: `{action:"create",title,description}`; ou `{action:"start"|"complete"|"cancel",task_id:123}`. **Somente altera o registro da tarefa**.

As escritas exigem cabeçalho `X-CSRF-Token`. Uma sessão inexistente retorna JSON 401 e um perfil não-ADM retorna JSON 403.

## Privacidade e limites
- Mensagens são armazenadas sem criptografia em nível de coluna no MySQL; proteja backups e acesso ao banco e defina uma política de retenção antes de uso real.
- O conteúdo digitado no chat e até 12 mensagens anteriores pode ser enviado ao provedor selecionado. Evite CPF, credenciais, dados pessoais de candidatos e conteúdo sensível de denúncias.
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
