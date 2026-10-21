# DevIN
TCC - Alcina Dantas Feijão 2026. Site com o propósito de ajudar as pessoas que queiram e trabalhem na área de Dev, e ajudar as empresas a contratar essas pessoas.

## E-mails e lembrete de currículo

Configure `DEVIN_SMTP_HOST`, `DEVIN_SMTP_PORT`, `DEVIN_SMTP_ENCRYPTION`, `DEVIN_SMTP_USERNAME`, `DEVIN_SMTP_PASSWORD` e `DEVIN_SMTP_FROM_EMAIL` no `.env` local. O arquivo `.env` não deve ser publicado.

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

## Jogos

O acesso público em `html/jogos/doom.html` mantém o DOOM incorporado e o Labirinto DevIN. O labirinto tem fases, coleta de cristais, perseguidores e vidas, mas não grava progresso. É um jogo original, sem reproduzir mapas, personagens, arte ou sons de Pac-Man.

Depois de entrar em qualquer dashboard, digite a sequência Konami com as setas, `B`, `A` e `Enter` para liberar `php/arcade.php`. O portal também valida a sessão no servidor; abrir o endereço diretamente não libera o acesso. A liberação dura até duas horas, mas termina antes quando a sessão expira ou a conta sai.

O portal contém Labirinto DevIN, Snake, Blocos, Quebra-blocos, Memória e Salto de Ritmo. A tabela `progresso_jogos` é criada automaticamente no primeiro acesso à API de jogos. Cada conta salva recorde, maior fase alcançada e horário da última atividade. Administradores veem os registros de todas as contas e podem remover registros indevidos; os dados são pontuações de jogos executados no navegador, não resultados certificados contra adulteração.
