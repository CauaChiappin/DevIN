# JARVIS Admin — OpenRouter com duas chaves e 10 modelos reserva

Atualização do módulo na branch `fix/jarvis-openrouter-dual-key-model-fallback`.

## Configuração correta do .env na raiz do projeto

```dotenv
# Login padrão do ADM continua igual; sem Token 1/Token 2.
DEVIN_JARVIS_PROVIDER=openrouter

# Um dos modelos gratuitos válidos, ou openrouter/free.
DEVIN_JARVIS_MODEL=z-ai/glm-5.3-flash:free

# Primeira chave de API da sua conta OpenRouter.
DEVIN_JARVIS_API_KEY=

# Segunda chave de API OpenRouter, usada SOMENTE se a principal falhar
# por causa de autenticação, limite, conexão ou indisponibilidade.
DEVIN_JARVIS_BACKUP_API_KEY=

# Opcional: lista priorizada de até 10 modelos :free, separada por vírgulas.
# Deixe vazio para usar os 10 backups gratuitos padrão no código.
DEVIN_JARVIS_FALLBACK_MODELS=

# Legado de uma única reserva; deixe vazio na nova configuração.
# NUNCA insira aqui uma chave de API.
DEVIN_JARVIS_FALLBACK_MODEL=
```

**Mapeamento:** a chave que antes estava em `DEVIN_JARVIS_FALLBACK_MODEL`
deve ser movida pelo operador para `DEVIN_JARVIS_BACKUP_API_KEY`.
A variável `DEVIN_JARVIS_FALLBACK_MODEL` é um ID de modelo, não uma chave.

**Atenção:** não copie credenciais em chat nem em commits. Credenciais
expostas anteriormente devem ser revogadas, mesmo que você tenha
alterado parte dos caracteres antes de compartilhá-las.

## Lista padrão de 10 reservas

O modelo `DEVIN_JARVIS_MODEL` sempre vem primeiro. Em seguida:

1. `google/gemma-4-26b-a4b-it:free`
2. `google/gemma-4-31b-it:free`
3. `nvidia/nemotron-3.5-lightning:free`
4. `nvidia/nemotron-3.5-super-vl:free`
5. `dots-studio/dots-3-note-preview:free`
6. `cohere/north-mini-code:free`
7. `recursive/flint-v1.0:free`
8. `liquid/lfm-3-1.7b:free`
9. `nvidia/nemotron-3-ultra-550b-a55b:free`
10. `openrouter/free` (roteador gratuito automático).

O sistema remove duplicatas, recusa IDs pagos e ignora valores
inválidos na variável legada (inclusive uma chave colada no campo
errado), sem publicar o valor. Modelos são serviços externos e
podem mudar/desaparecer. No momento, o fluxo usa **até** 10 reservas,
não garante dez endpoints simultaneamente disponíveis.

A lista é enviada à OpenRouter numa requisição usando o campo
`models`, permitindo ao provedor escolher o próximo modelo.
Assim não há onze chamadas HTTP sequenciais nem necessidade de
uma chave por modelo.

Se a primeira chave falhar por HTTP 401, 402, 403, 408, 429,
5xx ou conexão, o backend poderá fazer **uma segunda tentativa**
com `DEVIN_JARVIS_BACKUP_API_KEY`. Respostas 400/404/422
não são repetidas automaticamente. Cada tentativa pode utilizar
toda a lista de modelos nativamente. Se as duas chaves forem
idênticas, não há segunda tentativa.

**IMPORTANTE:** duas chaves vinculadas à mesma conta/workspace
OpenRouter compartilham possivelmente as mesmas cotas. Duas chaves
não contornam limites de uso por conta. A OpenRouter limita modelos
`:free`; tentativas malsucedidas podem consumir a cota diária.

## Diagnóstico sem exibir chaves

O endpoint `php/api/jarvis/chat.php` devolve um erro classificado e seguro:

| error_code | Diagnóstico |
| --- | --- |
| `auth` | Chave inválida/recusada |
| `credits` | Orçamento/créditos indisponíveis |
| `rate_limit` | Cota/limite atingido |
| `network` | DNS/conexão/timeout/cURL |
| `provider` | Erro do serviço da IA |
| `model` | Erro de modelo ou payload |
| `invalid_response` | JSON ou texto de resposta vazio |
| `configuration` | Variável obrigatória ausente ou modelo principal não gratuito |

Também devolve `provider_http_status` (status bruto, sem conteúdo da
API) e, em resposta bem-sucedida, `model`, `fallback_used` e
`key_used` como `primary` ou `backup`; **nunca** os segredos.
O front mostra o modelo e quando a chave reserva foi utilizada.

Os logs técnicos registram apenas código, categoria e nível da
chave. Não registram prompts, nomes de usuários ou tokens.

## Procedimento de implantação

1. Confirme que não existe nenhum segredo real no repositório.
2. Faça backup dos arquivos e do banco da Hostinger antes de atualizar.
3. Publique a `main` revisada após os testes e a aprovação do PR.
4. Abra o `.env` na **raiz** do repositório, ao lado do `composer.json`,
   e use as duas chaves nos campos corretos.
5. Se a primeira linha do `.env` for uma sequência literal de sinais
   `=` sem `#`, remova-a ou transforme-a em comentário; a sintaxe
   dotenv exige linhas de chave/valor ou comentários.
6. Em hospedagem real, configure `APP_ENV=production` e
   `DEVIN_JWT_SECRET` com um valor forte aleatório (não um texto
   demonstrativo). A troca do segredo JWT invalida tokens existentes.
7. Verifique PHP cURL, mbstring, acesso HTTPS de saída para
   `openrouter.ai` e carregamento do `.env` com `vendor/autoload.php`.
8. Entre como ADM e teste primeiro **Resumo do DevIN** (não usa IA).
   Depois envie **Oi** no chat.
9. Se falhar, anote apenas `error_code` e
   `provider_http_status` ou as categorias do log, jamais copie
   credenciais, senhas ou corpos completos de requisição.
10. Se funcionou, teste a opção de histórico, novas conversas e
    atividades. A migration do JARVIS precisa estar aplicada ao
    MySQL para gravar essas informações.

Este PR não executa ações administrativas destrutivas, não altera
o esquema SQL e não depende do 2FA do PR #8.

## Testes offline (nenhuma chave de verdade)

```bash
php tests/jarvis_admin_smoke.php
php tests/jarvis_openrouter_router.php
node --check js/jarvis-admin.js
```

GitHub Actions também mantém teste MySQL descartável; esses testes
não confirmam se as credenciais e quotas reais da hospedagem são válidas.
