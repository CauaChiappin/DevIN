<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

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
    header('Location: adm.php?pagina=suporte', true, 303); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
if (!in_array($actor['tipo'], ['pessoa','empresa'], true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    http_response_code(403); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    exit('Tipo de conta não autorizado.'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$dashboard = $actor['tipo'] === 'pessoa' ? 'pessoa.php' : 'empresa.php'; // Explicação DevIN: Atribui um valor à variável PHP $dashboard.
?>
<!DOCTYPE html> <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
<html lang="pt-BR"> <!-- Explicação DevIN: Declara elemento raiz do documento HTML. -->
<head> <!-- Explicação DevIN: Declara metadados e dependências do documento. -->
    <meta charset="UTF-8"> <!-- Explicação DevIN: Declara metadado de navegação. -->
    <meta name="viewport" content="width=device-width, initial-scale=1"> <!-- Explicação DevIN: Declara metadado de navegação. -->
    <meta name="robots" content="noindex,nofollow"> <!-- Explicação DevIN: Declara metadado de navegação. -->
    <title>Central de Ajuda · DevIN</title> <!-- Explicação DevIN: Declara elemento de marcação. -->
    <link rel="stylesheet" href="../css/support.css?v=<?= filemtime(__DIR__ . '/../css/support.css') ?>"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. Contém dado dinâmico processado pelo PHP. -->
</head> <!-- Explicação DevIN: Fecha elemento head do documento. -->
<body class="support-page"> <!-- Explicação DevIN: Declara corpo visível da página. -->
    <header class="support-header"> <!-- Explicação DevIN: Declara cabeçalho da seção ou página. -->
        <a class="support-brand" href="<?= h($dashboard) ?>">Dev<span>IN</span></a> <!-- Explicação DevIN: Declara link de navegação. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
        <nav aria-label="Navegação de suporte" class="support-top-links"> <!-- Explicação DevIN: Declara navegação. Inclui atributos de acessibilidade. -->
            <a href="<?= h($dashboard) ?>">← Meu painel</a> <!-- Explicação DevIN: Declara link de navegação. Contém dado dinâmico processado pelo PHP. -->
            <a href="<?= h($dashboard . '?pagina=mensagens') ?>">Mensagens</a> <!-- Explicação DevIN: Declara link de navegação. Contém dado dinâmico processado pelo PHP. -->
        </nav> <!-- Explicação DevIN: Fecha elemento nav do documento. -->
    </header> <!-- Explicação DevIN: Fecha elemento header do documento. -->
    <main class="support-main" data-support-root data-role="user"
          data-endpoint="support_api.php" data-csrf="<?= h(csrfToken()) ?>">
        <div class="support-hero"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
            <span class="support-eyebrow">SUPORTE DEVIN</span> <!-- Explicação DevIN: Declara trecho em linha. -->
            <h1>Como podemos ajudar?</h1> <!-- Explicação DevIN: Declara título principal. -->
            <p>Encontrou um erro ou tem alguma dúvida? Conte para nossa equipe. <!-- Explicação DevIN: Declara parágrafo. -->
               A resposta aparecerá aqui, nas Mensagens e no seu e-mail.</p> <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
        <!-- Navegação compacta para celulares: as duas áreas permanecem lado a lado no desktop. -->
        <div class="support-mobile-tabs" data-support-mobile-tabs role="group" aria-label="Áreas da Central de Ajuda"> <!-- Explicação DevIN: Declara agrupamento de elementos. Inclui atributos de acessibilidade. -->
            <button type="button" class="support-mobile-tab" data-support-mobile-target="create" aria-pressed="true">Novo chamado</button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
            <button type="button" class="support-mobile-tab" data-support-mobile-target="history" aria-pressed="false">Meus chamados</button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
        <p class="support-mobile-message" data-support-mobile-message role="status" aria-live="polite"></p> <!-- Explicação DevIN: Declara parágrafo. Inclui atributos de acessibilidade. -->
        <div class="support-columns support-mobile-panels" data-mobile-view="create"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
            <section class="support-panel support-create-pane" aria-labelledby="support-form-title"> <!-- Explicação DevIN: Declara seção temática. Inclui atributos de acessibilidade. -->
                <h2 id="support-form-title">Abrir chamado</h2> <!-- Explicação DevIN: Declara subtítulo de segundo nível. -->
                <p class="support-hint">Atendimento associado à conta <strong><?= h($actor['nome']) ?></strong>.</p> <!-- Explicação DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
                <!-- FormData respeita limite de arquivo e inclui token CSRF. -->
                <form data-support-create enctype="multipart/form-data" method="post"> <!-- Explicação DevIN: Declara formulário de entrada de dados. -->
                    <label for="support-category">Qual é o assunto? <span aria-hidden="true">*</span></label> <!-- Explicação DevIN: Declara rótulo associado a um campo. Inclui atributos de acessibilidade. A linha reúne múltiplos elementos. -->
                    <select id="support-category" name="category" required> <!-- Explicação DevIN: Declara seleção de opções. -->
                        <option value="">Selecione uma categoria</option> <!-- Explicação DevIN: Declara opção selecionável. -->
                        <option value="erro">Erro no site</option> <!-- Explicação DevIN: Declara opção selecionável. -->
                        <option value="duvida">Dúvida ou orientação</option> <!-- Explicação DevIN: Declara opção selecionável. -->
                        <option value="conta">Conta e acesso</option> <!-- Explicação DevIN: Declara opção selecionável. -->
                        <option value="vaga">Vagas e candidaturas</option> <!-- Explicação DevIN: Declara opção selecionável. -->
                        <option value="seguranca">Segurança e privacidade</option> <!-- Explicação DevIN: Declara opção selecionável. -->
                        <option value="sugestao">Sugestão de melhoria</option> <!-- Explicação DevIN: Declara opção selecionável. -->
                        <option value="outro">Outro assunto</option> <!-- Explicação DevIN: Declara opção selecionável. -->
                    </select> <!-- Explicação DevIN: Fecha elemento select do documento. -->
                    <label for="support-description">Descreva o que aconteceu <span aria-hidden="true">*</span></label> <!-- Explicação DevIN: Declara rótulo associado a um campo. Inclui atributos de acessibilidade. A linha reúne múltiplos elementos. -->
                    <textarea id="support-description" name="description" rows="7"
                              minlength="10" maxlength="4000" required
                              placeholder="Explique o problema, onde ocorreu e o que você esperava encontrar. Não envie senhas nem tokens."></textarea>
                    <div class="support-counter" data-support-description-counter aria-live="off">0 / 4000 caracteres</div> <!-- Explicação DevIN: Declara agrupamento de elementos. Inclui atributos de acessibilidade. -->
                    <label for="support-attachment">Anexo <small>(opcional, até 20 MB)</small></label> <!-- Explicação DevIN: Declara rótulo associado a um campo. A linha reúne múltiplos elementos. -->
                    <input id="support-attachment" name="attachment" type="file"
                           accept=".pdf,.png,.jpg,.jpeg,.webp,.txt,.log,.docx,.zip"
                           aria-describedby="support-attachment-status">
                    <!-- Feedback de seleção é atualizado no próprio aparelho, antes do envio. -->
                    <div class="support-file-preview" data-support-file-preview hidden> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                        <span id="support-attachment-status" data-support-attachment-status aria-live="polite"></span> <!-- Explicação DevIN: Declara trecho em linha. Inclui atributos de acessibilidade. -->
                        <button type="button" class="support-clear-file" data-support-clear-file>Remover</button> <!-- Explicação DevIN: Declara botão de ação. -->
                    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                    <p class="support-hint">Formatos: PDF, PNG, JPG, WEBP, TXT, LOG, DOCX ou ZIP. <!-- Explicação DevIN: Declara parágrafo. -->
                       Anexos são privados e acessíveis apenas ao dono do chamado e aos ADMs.</p> <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
                    <button type="submit" class="support-primary" data-support-submit>Enviar solicitação</button> <!-- Explicação DevIN: Declara botão de ação. -->
                    <p class="support-feedback" data-support-feedback role="status" aria-live="polite"></p> <!-- Explicação DevIN: Declara parágrafo. Inclui atributos de acessibilidade. -->
                </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
            </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->
            <section class="support-panel support-history-pane" aria-labelledby="support-history-title"> <!-- Explicação DevIN: Declara seção temática. Inclui atributos de acessibilidade. -->
                <!-- No celular, a lista recolhe após escolher um chamado para mostrar a resposta sem rolagem longa. -->
                <div data-support-history-overview>
                <h2 id="support-history-title">Meus chamados</h2> <!-- Explicação DevIN: Declara subtítulo de segundo nível. -->
                <p class="support-hint">Acompanhe as respostas sem precisar reenviar o problema.</p> <!-- Explicação DevIN: Declara parágrafo. -->
                <div data-support-list class="support-ticket-list"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                    <p class="support-hint">Carregando seus chamados...</p> <!-- Explicação DevIN: Declara parágrafo. -->
                </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                </div>
                <!-- O botão de retorno recupera a lista, sem perder a aba ou refazer o login. -->
                <button type="button" class="support-secondary support-history-back" data-support-history-back hidden>← Voltar aos meus chamados</button>
                <div data-support-detail class="support-ticket-detail" hidden></div> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
            </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->
        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
    </main> <!-- Explicação DevIN: Fecha elemento main do documento. -->
    <script src="../js/support-ui.js?v=<?= filemtime(__DIR__ . '/../js/support-ui.js') ?>" defer></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
</body> <!-- Explicação DevIN: Fecha elemento body do documento. -->
</html> <!-- Explicação DevIN: Fecha elemento html do documento. -->
