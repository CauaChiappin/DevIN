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
