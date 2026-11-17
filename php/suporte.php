<?php
declare(strict_types=1);

/**
 * Página de abertura e acompanhamento de chamados.
 * A identidade da pessoa/empresa vem da sessão; nome e e-mail não são editáveis.
 * O script usa a API autenticada e não coloca dados pessoais em URLs públicas.
 */
require_once __DIR__ . '/middlewares/auth.php'; // Autenticador principal do portal.
require_once __DIR__ . '/config/security.php'; // Token CSRF da sessão atual.
require_once __DIR__ . '/helpers.php'; // Escapador h() e ícones do DevIN.

$actor = requireWebAuth(); // Rejeita visitantes que não estejam conectados.
if ($actor['tipo'] === 'adm') { // O ADM responde em sua área própria.
    header('Location: adm.php?pagina=suporte', true, 303);
    exit;
}
if (!in_array($actor['tipo'], ['pessoa','empresa'], true)) {
    http_response_code(403);
    exit('Tipo de conta não autorizado.');
}
$dashboard = $actor['tipo'] === 'pessoa' ? 'pessoa.php' : 'empresa.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Central de Ajuda · DevIN</title>
    <link rel="stylesheet" href="../css/support.css?v=<?= filemtime(__DIR__ . '/../css/support.css') ?>">
</head>
<body class="support-page">
    <header class="support-header">
        <a class="support-brand" href="<?= h($dashboard) ?>">Dev<span>IN</span></a>
        <nav aria-label="Navegação de suporte" class="support-top-links">
            <a href="<?= h($dashboard) ?>">← Meu painel</a>
            <a href="<?= h($dashboard . '?pagina=mensagens') ?>">Mensagens</a>
        </nav>
    </header>
    <main class="support-main" data-support-root data-role="user"
          data-endpoint="support_api.php" data-csrf="<?= h(csrfToken()) ?>">
        <div class="support-hero">
            <span class="support-eyebrow">SUPORTE DEVIN</span>
            <h1>Como podemos ajudar?</h1>
            <p>Encontrou um erro ou tem alguma dúvida? Conte para nossa equipe.
               A resposta aparecerá aqui, nas Mensagens e no seu e-mail.</p>
        </div>
        <div class="support-columns">
            <section class="support-panel" aria-labelledby="support-form-title">
                <h2 id="support-form-title">Abrir chamado</h2>
                <p class="support-hint">Atendimento associado à conta <strong><?= h($actor['nome']) ?></strong>.</p>
                <!-- FormData respeita limite de arquivo e inclui token CSRF. -->
                <form data-support-create enctype="multipart/form-data" method="post">
                    <label for="support-category">Qual é o assunto? <span aria-hidden="true">*</span></label>
                    <select id="support-category" name="category" required>
                        <option value="">Selecione uma categoria</option>
                        <option value="erro">Erro no site</option>
                        <option value="duvida">Dúvida ou orientação</option>
                        <option value="conta">Conta e acesso</option>
                        <option value="vaga">Vagas e candidaturas</option>
                        <option value="seguranca">Segurança e privacidade</option>
                        <option value="sugestao">Sugestão de melhoria</option>
                        <option value="outro">Outro assunto</option>
                    </select>
                    <label for="support-description">Descreva o que aconteceu <span aria-hidden="true">*</span></label>
                    <textarea id="support-description" name="description" rows="7"
                              minlength="10" maxlength="4000" required
                              placeholder="Explique o problema, onde ocorreu e o que você esperava encontrar. Não envie senhas nem tokens."></textarea>
                    <label for="support-attachment">Anexo <small>(opcional, até 20 MB)</small></label>
                    <input id="support-attachment" name="attachment" type="file"
                           accept=".pdf,.png,.jpg,.jpeg,.webp,.txt,.log,.docx,.zip">
                    <p class="support-hint">Formatos: PDF, PNG, JPG, WEBP, TXT, LOG, DOCX ou ZIP.
                       Anexos são privados e acessíveis apenas ao dono do chamado e aos ADMs.</p>
                    <button type="submit" class="support-primary" data-support-submit>Enviar solicitação</button>
                    <p class="support-feedback" data-support-feedback role="status" aria-live="polite"></p>
                </form>
            </section>
            <section class="support-panel" aria-labelledby="support-history-title">
                <h2 id="support-history-title">Meus chamados</h2>
                <p class="support-hint">Acompanhe as respostas sem precisar reenviar o problema.</p>
                <div data-support-list class="support-ticket-list">
                    <p class="support-hint">Carregando seus chamados...</p>
                </div>
                <div data-support-detail class="support-ticket-detail" hidden></div>
            </section>
        </div>
    </main>
    <script src="../js/support-ui.js?v=<?= filemtime(__DIR__ . '/../js/support-ui.js') ?>" defer></script>
</body>
</html>
