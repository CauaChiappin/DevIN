<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

require_once __DIR__ . '/config/security.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
startSecureSession(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

$erro = $_SESSION['erro_recuperacao'] ?? ''; // Explicação DevIN: Atribui um valor à variável PHP $erro.
$sucesso = $_SESSION['sucesso_recuperacao'] ?? ''; // Explicação DevIN: Atribui um valor à variável PHP $sucesso.
// Exibe mensagens da etapa anterior uma única vez; ao removê-las, uma atualização não repete avisos antigos.
unset($_SESSION['erro_recuperacao'], $_SESSION['sucesso_recuperacao']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
?>
<!DOCTYPE html> <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
<html lang="pt-BR"> <!-- Explicação DevIN: Declara elemento raiz do documento HTML. -->
<head> <!-- Explicação DevIN: Declara metadados e dependências do documento. -->
    <meta charset="UTF-8"> <!-- Explicação DevIN: Declara metadado de navegação. -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Explicação DevIN: Declara metadado de navegação. -->
    <title>DevIN | Recuperação de Senha</title> <!-- Explicação DevIN: Declara elemento de marcação. -->
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. -->
    <link rel="stylesheet" href="../css/recuperacao.css"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. -->
</head> <!-- Explicação DevIN: Fecha elemento head do documento. -->
<body> <!-- Explicação DevIN: Declara corpo visível da página. -->
    <main class="recovery-wrapper"> <!-- Explicação DevIN: Declara conteúdo principal da página. -->
        <section class="card-box" aria-labelledby="rec-title"> <!-- Explicação DevIN: Declara seção temática. Inclui atributos de acessibilidade. -->
            <a class="brand-link" href="index.php">Dev<span>IN</span></a> <!-- Explicação DevIN: Declara link de navegação. A linha reúne múltiplos elementos. -->
            <h1 id="rec-title">Recuperação de senha</h1> <!-- Explicação DevIN: Declara título principal. -->

            <?php if ($erro): ?>
                <div class="alert alert-error"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div> <!-- Explicação DevIN: Declara agrupamento de elementos. Contém dado dinâmico processado pelo PHP. -->
            <?php endif; ?>
            <?php if ($sucesso): ?>
                <div class="alert alert-success" role="status"><?= htmlspecialchars($sucesso, ENT_QUOTES, 'UTF-8') ?></div> <!-- Explicação DevIN: Declara agrupamento de elementos. Contém dado dinâmico processado pelo PHP. -->
            <?php endif; ?>

            <form action="processar.php" method="POST" id="formRecuperacao"> <!-- Explicação DevIN: Declara formulário de entrada de dados. -->
                <!-- Define a ação do endpoint e associa a solicitação ao token da sessão. -->
                <input type="hidden" name="acao" value="solicitar_recuperacao"> <!-- Explicação DevIN: Declara campo de entrada. -->
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken(), ENT_QUOTES, 'UTF-8') ?>"> <!-- Explicação DevIN: Declara campo de entrada. Contém dado dinâmico processado pelo PHP. -->

                <div class="input-block"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                    <label for="email">Email:</label> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                    <div class="input-icon-field"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
              
                        <input type="email" id="email" name="email" required placeholder="✉ Informe seu email..." autocomplete="email"> <!-- Explicação DevIN: Declara campo de entrada. -->
                    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

                <div class="btn-group"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                    <a href="login.php" class="btn btn-secondary">Voltar</a> <!-- Explicação DevIN: Declara link de navegação. -->
                    <button type="submit" class="btn btn-primary" id="btnEnviar">Enviar</button> <!-- Explicação DevIN: Declara botão de ação. -->
                </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
            </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
        </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->
    </main> <!-- Explicação DevIN: Fecha elemento main do documento. -->

    <footer class="recovery-footer"> <!-- Explicação DevIN: Declara rodapé. -->
        Dev<span>IN</span> | Escola Profª Alcina Dantas Feijão | DevIN 2026. Todos os direitos reservados. <!-- Explicação DevIN: Declara trecho em linha. -->
    </footer> <!-- Explicação DevIN: Fecha elemento footer do documento. -->

    <script src="../js/recuperacao.js"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. -->
    <script src="../js/lenis-global.js?v=<?= filemtime(__DIR__ . '/../js/lenis-global.js') ?>"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
</body> <!-- Explicação DevIN: Fecha elemento body do documento. -->
</html> <!-- Explicação DevIN: Fecha elemento html do documento. -->
