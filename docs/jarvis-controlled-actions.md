# JARVIS Admin — ferramentas controladas, histórico e mensagens oficiais

> Branch de desenvolvimento: `feat/jarvis-controlled-actions-and-platform-messages`.
> As novas ferramentas são **opt-in**. Não habilite na Hostinger antes de aplicar a migration, testar os quatro ADMs e revisar retenção de dados.

## Escopo implementado

- **Consultar**: busca somente leitura de candidatos, empresas, vagas e denúncias, até 12 resultados. Os SQLs são fixos e preparados, sem comandos SQL gerados pela IA. Não retorna CPF/CNPJ, email, fotos, credenciais ou currículos.
- **Analisar com IA**: quando o ADM seleciona explicitamente um resultado, envia um resumo mínimo do registro ao modelo para análise orientativa; nenhuma alteração é efetuada e o evento entra no histórico.
- **Mensagens**: a IA cria um rascunho individual com nome público do destinatário e orientação do ADM. A conta do remetente no chat do portal aparece como `Support DevIN`; o envio só acontece com outro POST autenticado, CSRF, revisão do texto e confirmação manual **ENVIAR**.
- **Histórico**: trilha de auditoria compartilhada e somente leitura para os quatro ADMs. Registra ator (IA, ADM ou sistema), ADM responsável, operação, registro alvo e horário. **Não registra conteúdo de conversa, mensagem, CPF, endereço ou prompts.**
- **Travas**: zero endpoints de banir, excluir, suspender, aprovar denúncia ou editar conta neste módulo; máximo de 15 mensagens oficiais enviadas por ADM em 24 horas e 2 por destinatário em 24 horas; validação de destinatário novamente no envio; idempotência da entrega; nenhuma automação agendada ou envio em massa.
- A alteração de qualquer decisão de moderação continua nas ferramentas já existentes do portal, fora do JARVIS, com revisão humana.

## Migração e ativação

Nova migration adicional: `database/migrations/jarvis_admin_controlled_actions.sql`. Cria `jarvis_action_events` e `jarvis_platform_outbox` e **não modifica** tabelas de pessoas, empresas, vagas ou denúncias.

Após backup e testes da base de desenvolvimento, executar no MySQL usado pela branch de testes:

```bash
mysql -h HOST -u USER -p BANCO_DE_DESENVOLVIMENTO < database/migrations/jarvis_admin_controlled_actions.sql
```

A migration original `database/migrations/jarvis_admin.sql` também é necessária.

No `.env` do servidor de desenvolvimento:

```dotenv
# 0 = novas ferramentas desligadas até migração + homologação
DEVIN_JARVIS_ACTIONS_ENABLED=0
```

Após criar as tabelas e testar com contas fictícias, alterar para `1` **somente no servidor de desenvolvimento**. Não há qualquer novo segredo. O chat JARVIS normal e as atividades antigas continuam operando com valor 0.

## Endpoints

`php/api/jarvis/admin_tools.php` (somente ADM com sessão válida):
- GET `?action=search&kind=vaga&q=php` — consulta permitida.
- GET `?action=inspect&kind=vaga&id=31` — inspeção mínima, sem IA.
- GET `?action=history` — histórico coletivo com ADM responsável.
- GET `?action=drafts` — rascunhos apenas do ADM logado.
- POST JSON `{action:"analyze",kind:"vaga",id:31,question:"..."}` — consulta à IA, não modifica registro.
- POST JSON `{action:"generate",recipient_type:"pessoa",recipient_id:11,brief:"..."}` — gera somente rascunho com IA.
- POST JSON `{action:"manual_draft",recipient_type:"pessoa",recipient_id:11,body:"..."}` — rascunho administrativo sem IA.
- POST JSON `{action:"revise",draft_id:1,body:"..."}` — revisa antes de enviar.
- POST JSON `{action:"send",draft_id:1,confirmed:true,approval:"ENVIAR"}` — envio **separado** com confirmação humana.
- POST JSON `{action:"cancel",draft_id:1}` — cancela um rascunho.
Todos os POSTs usam `X-CSRF-Token`, verificação de ADM no backend e limitação de requisições. APIs não aceitam envio para listas, CSV, todos os usuários ou grupos.

## Privacidade e limitações

O cadastro guarda mensagem e destinatário na outbox MySQL, junto de status/ADM aprovador. A entrega usa a **infraestrutura de chat oficial que já existe no DevIN**, `appendPlatformChatNotification()`, baseada em arquivos temporários. A tabela de outbox é permanente, mas esse armazenamento de entrega ainda é temporário. É necessário validar retenção, restauração e estabilidade da hospedagem antes de considerar notificações permanentes garantidas. Uma futura evolução poderá migrar a caixa de mensagens para o MySQL sem quebrar o chat atual.

Textos gerados e dados de perfis selecionados são enviados à OpenRouter apenas após o ADM acionar explicitamente a análise ou geração. Não usar IA para decisões automáticas com consequências relevantes; revisar textos gerados para exatidão, adequação, privacidade e ausência de fraudes antes de enviar. A política de privacidade e o processo de aprovação de comunicações devem ser revisados com o responsável pelo tratamento de dados.

O histórico é append-only pela aplicação, mas administradores com acesso direto ao banco podem alterá-lo. Exigir backup, privilégios SQL restritos e retenção documentada.

## Testes

O workflow `.github/workflows/jarvis-admin-checks.yml` executa PHP lint, JavaScript syntax, migrações no MySQL 8.0 descartável e `tests/jarvis_controlled_actions_mysql.php` para buscar registros, criar rascunhos, impedir acessos cruzados de ADMs, exigir revisão, enviar pelo remetente sistema, evitar duplicidade, limitar repetição por destinatário, cancelar e auditar. **Não envia mensagens a usuários reais.**

Depois dos testes do CI, homologar no navegador com contas fictícias, em celulares e desktop, com cURL/MySQL na Hostinger antes de usar dados reais.
