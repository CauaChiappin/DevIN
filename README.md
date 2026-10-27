# DevIN
TCC - Alcina Dantas Feijão 2026. Site com o propósito de ajudar as pessoas que queiram e trabalhem na área de Dev, e ajudar as empresas a contratar essas pessoas.

## E-mails e lembrete de currículo

Preencha o `.env` na raiz do projeto com os dados do seu provedor SMTP. Um modelo também está disponível em `.env.example`. As variáveis usadas são:

- `DEVIN_SMTP_HOST`: servidor SMTP (Gmail: `smtp.gmail.com`; Outlook/Microsoft 365: `smtp.office365.com`).
- `DEVIN_SMTP_PORT`: porta SMTP; normalmente `587` para TLS ou `465` para SSL.
- `DEVIN_SMTP_ENCRYPTION`: `tls` para porta `587` ou `ssl` para porta `465`.
- `DEVIN_SMTP_USERNAME`: usuário SMTP, geralmente o e-mail completo.
- `DEVIN_SMTP_PASSWORD`: senha SMTP. No Gmail, use uma senha de app criada na Conta Google com a verificação em duas etapas ativada; não use sua senha normal.
- `DEVIN_SMTP_FROM_EMAIL`: endereço de remetente autorizado pelo provedor, normalmente igual ao usuário SMTP.
- `DEVIN_SMTP_FROM_NAME`: nome exibido como remetente (opcional; o padrão é `Plataforma DevIN`).

O endereço de remetente precisa ser permitido pelo serviço SMTP. O `.env` real é ignorado pelo Git e não deve ser publicado nem compartilhado; mantenha credenciais apenas nele.

O `.env` também contém a URL local (`APP_URL`) e as credenciais do MySQL (`DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`). Os valores iniciais correspondem à configuração comum do XAMPP; ajuste-os se o banco ou a porta forem diferentes. `APP_ENV=development` ativa o modo local. Sem `APP_ENV`, a aplicação assume produção e exige `DEVIN_JWT_SECRET`; configure esse segredo com uma chave aleatória forte e privada e não reutilize a chave de desenvolvimento.

O logout encerra a sessão e remove o cookie JWT do navegador, mas tokens já emitidos continuam válidos até expirarem (no máximo uma hora); a aplicação ainda não mantém uma lista de revogação. A pontuação do arcade é limitada por sessão e tempo de partida, mas ainda depende de dados enviados pelo cliente e pode ser falsificada; esse risco é aceito.

Os e-mails de confirmação e recuperação são enviados durante a solicitação HTTP, com timeout SMTP de 8 segundos. A tela bloqueia reenvios do mesmo formulário enquanto a solicitação está em andamento.

O lembrete de currículo após uma hora depende do Agendador de Tarefas do Windows. Configure uma tarefa para repetir a cada minuto, executando:

- Programa: `C:\xampp\php\php.exe`
- Argumentos: `-f C:\xampp\htdocs\DevIN\php\cron_lembrete_curriculo.php`

O script aceita execução apenas pela CLI e marca o lembrete como enviado somente após sucesso no SMTP.

## Mensagens no dashboard

As conversas privadas entre contas de pessoa e empresa funcionam sem criar a tabela `mensagens`: por enquanto, as mensagens ficam em um arquivo temporário local compartilhado pela instalação. Elas não são sincronizadas com outras instalações e podem ser apagadas pela limpeza dos arquivos temporários do sistema. A lista de contatos continua usando as contas cadastradas no banco de dados. A área de mensagens atualiza conversas abertas automaticamente a cada quatro segundos.

O menu de três pontos em cada mensagem permite ocultá-la para a própria conta; em mensagens enviadas, também é possível apagá-la para os dois participantes. A opção para todos remove o texto original e deixa um aviso no lugar. Uma conversa inteira pode ser limpa pelo botão no cabeçalho, apenas para a conta que executa a ação.

As preferências de idioma do dashboard são salvas por conta na tabela `preferencias_dashboard`, criada automaticamente no primeiro acesso ao dashboard. A interface oferece português, inglês e espanhol; a preferência permanece após sair e entrar novamente.

Para traduzir mensagens recebidas, a opção **Traduzir mensagem** no menu de três pontos usa a API pública MyMemory, sem exigir conta ou chave. O texto só é enviado ao provedor quando a pessoa solicita a tradução; a resposta aparece junto ao original e usa o idioma selecionado no dashboard. O serviço limita o uso anônimo a 5.000 caracteres por dia e cada trecho da solicitação a 500 bytes. A tradução depende da disponibilidade do serviço e as mensagens selecionadas são enviadas a terceiros.

## Denúncias de vagas

Uma pessoa autenticada pode denunciar uma vaga pelo painel de detalhes. Cada conta pode enviar uma denúncia por vaga; é necessário escolher um motivo e explicar o caso. As denúncias ficam na aba **Reports** do dashboard administrativo, onde a equipe pode manter ou excluir a vaga. A decisão e as notificações pendentes são salvas nas tabelas `reports_vagas` e `reports_vagas_notificacoes`, criadas automaticamente. Após a análise, quem denunciou e a empresa responsável recebem o resultado em uma conversa somente leitura com a Plataforma DevIN.

Cada denúncia guarda uma cópia do título, descrição, salário e limite de aprovados da vaga no momento do envio, para que a equipe possa analisar os detalhes mesmo que a vaga seja alterada ou removida depois.

As mensagens de conversa, inclusive as notificações já entregues, continuam no armazenamento temporário local descrito acima; elas não são sincronizadas entre instalações e podem ser removidas pela limpeza dos arquivos temporários. A fila no banco de dados permite tentar novamente a entrega de notificações que ainda estejam pendentes.

As mensagens de conclusão da análise de denúncias de vagas ficam disponíveis no chat por 24 horas após a entrega e são removidas na próxima atualização do armazenamento de mensagens.

As empresas informam o salário mensal em reais e o máximo de candidatos aprovados ao publicar ou editar uma vaga. Salário `0` aparece como **A combinar**. Em uma edição, limite `0` significa sem limite. O sistema impede aprovações acima do limite e não permite reduzi-lo abaixo do total já aprovado. As colunas `salario_mensal` e `limite_aprovados` são adicionadas automaticamente à tabela `vagas`; vagas anteriores ficam sem limite até serem editadas.

## Jogos

O acesso público em `html/jogos/doom.html` mantém o DOOM incorporado e o Labirinto DevIN. O labirinto tem fases, coleta de cristais, perseguidores e vidas, mas não grava progresso. É um jogo original, sem reproduzir mapas, personagens, arte ou sons de Pac-Man.

Depois de entrar em qualquer dashboard, digite a sequência Konami com as setas, `B`, `A` e `Enter` para liberar `php/arcade.php`. O portal também valida a sessão no servidor; abrir o endereço diretamente não libera o acesso. A liberação dura até duas horas, mas termina antes quando a sessão expira ou a conta sai.

O portal contém Labirinto DevIN, Snake, Blocos, Quebra-blocos, Memória e Salto de Ritmo. A tabela `progresso_jogos` é criada automaticamente no primeiro acesso à API de jogos. Cada conta salva recorde, maior fase alcançada e horário da última atividade. Administradores veem os registros de todas as contas e podem remover registros indevidos; os dados são pontuações de jogos executados no navegador, não resultados certificados contra adulteração.

No portal, selecionar um jogo abre uma tela com a descrição e os controles; a partida só começa após confirmação e uma contagem regressiva de três segundos. Reiniciar também aguarda essa contagem.

Ao perder, a tela mostra os dez melhores recordes daquele jogo. O servidor exige que a partida seja iniciada pela API e rejeita pontuações e fases acima de limites plausíveis para o tempo da sessão. Isso reduz envios acidentais ou pontuações claramente impossíveis, mas não torna os jogos invioláveis: a simulação ainda roda no navegador e jogadores determinados podem manipular o cliente. Para uma classificação competitiva confiável, a simulação e a validação de cada jogada precisariam ser executadas no servidor. A classificação reutiliza `progresso_jogos`; não é necessária alteração ou migração do banco de dados.
