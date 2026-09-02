# Governança da linha de base

- O código mantido do projeto está em `html/`, `css/`, `js/` e `php/`.
- `php/PHPMailer/` é uma dependência vendorizada e não deve ser reescrita durante correções da aplicação.
- URLs existentes entre as páginas HTML e PHP devem continuar funcionando sob `http://localhost/DevIN/`.
- Toda alteração ampla deve ser seguida por lint PHP, checagens estruturais e inspeção do diff.
