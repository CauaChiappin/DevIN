# DevIN
TCC - Alcina Dantas Feijão 2026. Site com o propósito de ajudar as pessoas que queiram e trabalhem na área de Dev, e ajudar as empresas a contratar essas pessoas.

## E-mails e lembrete de currículo

Configure `DEVIN_SMTP_HOST`, `DEVIN_SMTP_PORT`, `DEVIN_SMTP_ENCRYPTION`, `DEVIN_SMTP_USERNAME`, `DEVIN_SMTP_PASSWORD` e `DEVIN_SMTP_FROM_EMAIL` no `.env` local. O arquivo `.env` não deve ser publicado.

Os e-mails de confirmação e recuperação são enviados durante a solicitação HTTP, com timeout SMTP de 8 segundos. A tela bloqueia reenvios do mesmo formulário enquanto a solicitação está em andamento.

O lembrete de currículo após uma hora depende do Agendador de Tarefas do Windows. Configure uma tarefa para repetir a cada minuto, executando:

- Programa: `C:\xampp\php\php.exe`
- Argumentos: `-f C:\xampp\htdocs\DevIN\php\cron_lembrete_curriculo.php`

O script aceita execução apenas pela CLI e marca o lembrete como enviado somente após sucesso no SMTP.
