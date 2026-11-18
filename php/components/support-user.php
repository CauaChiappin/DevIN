<?php // Página de suporte integrada aos dashboards, sem remover o menu lateral.
declare(strict_types=1); // Garante o mesmo contrato de tipos da aplicação.
require_once __DIR__ . '/../middlewares/auth.php'; // Exige conta autenticada antes de mostrar o formulário.
require_once __DIR__ . '/../config/security.php'; // Disponibiliza token CSRF na sessão.
require_once __DIR__ . '/../helpers.php'; // Mantém a sanitização dos dados exibidos.
$actor = requireWebAuth(); // Obtém a identidade no servidor, nunca de um campo do navegador.
if (!in_array($actor['tipo'], ['pessoa', 'empresa'], true)) { // Impede uso do formulário de usuário por contas administrativas.
    http_response_code(403); // Recusa acesso incompatível com a área.
    exit('Tipo de conta não autorizado.'); // Não processa dados sem permissão.
} // Conclui a validação do tipo de conta.
?>
<div class="support-main" data-support-root data-role="user"
          data-endpoint="support_api.php" data-csrf="<?= h(csrfToken()) ?>">
        <div class="support-hero"> <!-- Nota DevIN: Declara agrupamento de elementos. -->
            <span class="support-eyebrow">SUPORTE DEVIN</span> <!-- Nota DevIN: Declara trecho em linha. -->
            <h1>Como podemos ajudar?</h1> <!-- Nota DevIN: Declara título principal. -->
            <p>Encontrou um erro ou tem alguma dúvida? Conte para nossa equipe. <!-- Nota DevIN: Declara parágrafo. -->
               A resposta aparecerá aqui, nas Mensagens e no seu e-mail.</p> <!-- Nota DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
        </div> <!-- Nota DevIN: Fecha elemento div do documento. -->
        <!-- Navegação compacta para celulares: as duas áreas permanecem lado a lado no desktop. -->
        <div class="support-mobile-tabs" data-support-mobile-tabs role="group" aria-label="Áreas da Central de Ajuda"> <!-- Nota DevIN: Declara agrupamento de elementos. Inclui atributos de acessibilidade. -->
            <button type="button" class="support-mobile-tab" data-support-mobile-target="create" aria-pressed="true">Novo chamado</button> <!-- Nota DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
            <button type="button" class="support-mobile-tab" data-support-mobile-target="history" aria-pressed="false">Meus chamados</button> <!-- Nota DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
        </div> <!-- Nota DevIN: Fecha elemento div do documento. -->
        <p class="support-mobile-message" data-support-mobile-message role="status" aria-live="polite"></p> <!-- Nota DevIN: Declara parágrafo. Inclui atributos de acessibilidade. -->
        <div class="support-columns support-mobile-panels" data-mobile-view="create"> <!-- Nota DevIN: Declara agrupamento de elementos. -->
            <section class="support-panel support-create-pane" aria-labelledby="support-form-title"> <!-- Nota DevIN: Declara seção temática. Inclui atributos de acessibilidade. -->
                <h2 id="support-form-title">Abrir chamado</h2> <!-- Nota DevIN: Declara subtítulo de segundo nível. -->
                <p class="support-hint">Atendimento associado à conta <strong><?= h($actor['nome']) ?></strong>.</p> <!-- Nota DevIN: Declara parágrafo. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
                <!-- FormData respeita limite de arquivo e inclui token CSRF. -->
                <form data-support-create enctype="multipart/form-data" method="post"> <!-- Nota DevIN: Declara formulário de entrada de dados. -->
                    <label for="support-category">Qual é o assunto? <span aria-hidden="true">*</span></label> <!-- Nota DevIN: Declara rótulo associado a um campo. Inclui atributos de acessibilidade. A linha reúne múltiplos elementos. -->
                    <select id="support-category" name="category" required> <!-- Nota DevIN: Declara seleção de opções. -->
                        <option value="">Selecione uma categoria</option> <!-- Nota DevIN: Declara opção selecionável. -->
                        <option value="erro">Erro no site</option> <!-- Nota DevIN: Declara opção selecionável. -->
                        <option value="duvida">Dúvida ou orientação</option> <!-- Nota DevIN: Declara opção selecionável. -->
                        <option value="conta">Conta e acesso</option> <!-- Nota DevIN: Declara opção selecionável. -->
                        <option value="vaga">Vagas e candidaturas</option> <!-- Nota DevIN: Declara opção selecionável. -->
                        <option value="seguranca">Segurança e privacidade</option> <!-- Nota DevIN: Declara opção selecionável. -->
                        <option value="sugestao">Sugestão de melhoria</option> <!-- Nota DevIN: Declara opção selecionável. -->
                        <option value="outro">Outro assunto</option> <!-- Nota DevIN: Declara opção selecionável. -->
                    </select> <!-- Nota DevIN: Fecha elemento select do documento. -->
                    <label for="support-description">Descreva o que aconteceu <span aria-hidden="true">*</span></label> <!-- Nota DevIN: Declara rótulo associado a um campo. Inclui atributos de acessibilidade. A linha reúne múltiplos elementos. -->
                    <textarea id="support-description" name="description" rows="7"
                              minlength="10" maxlength="4000" required
                              placeholder="Explique o problema, onde ocorreu e o que você esperava encontrar. Não envie senhas nem tokens."></textarea>
                    <div class="support-counter" data-support-description-counter aria-live="off">0 / 4000 caracteres</div> <!-- Nota DevIN: Declara agrupamento de elementos. Inclui atributos de acessibilidade. -->
                    <label for="support-attachment">Anexo <small>(opcional, até 20 MB)</small></label> <!-- Nota DevIN: Declara rótulo associado a um campo. A linha reúne múltiplos elementos. -->
                    <input id="support-attachment" name="attachment" type="file"
                           accept=".pdf,.png,.jpg,.jpeg,.webp,.txt,.log,.docx,.zip"
                           aria-describedby="support-attachment-status">
                    <!-- Feedback de seleção é atualizado no próprio aparelho, antes do envio. -->
                    <div class="support-file-preview" data-support-file-preview hidden> <!-- Nota DevIN: Declara agrupamento de elementos. -->
                        <span id="support-attachment-status" data-support-attachment-status aria-live="polite"></span> <!-- Nota DevIN: Declara trecho em linha. Inclui atributos de acessibilidade. -->
                        <button type="button" class="support-clear-file" data-support-clear-file>Remover</button> <!-- Nota DevIN: Declara botão de ação. -->
                    </div> <!-- Nota DevIN: Fecha elemento div do documento. -->
                    <p class="support-hint">Formatos: PDF, PNG, JPG, WEBP, TXT, LOG, DOCX ou ZIP. <!-- Nota DevIN: Declara parágrafo. -->
                       Anexos são privados e acessíveis apenas ao dono do chamado e aos ADMs.</p> <!-- Nota DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
                    <button type="submit" class="support-primary" data-support-submit>Enviar solicitação</button> <!-- Nota DevIN: Declara botão de ação. -->
                    <p class="support-feedback" data-support-feedback role="status" aria-live="polite"></p> <!-- Nota DevIN: Declara parágrafo. Inclui atributos de acessibilidade. -->
                </form> <!-- Nota DevIN: Fecha elemento form do documento. -->
            </section> <!-- Nota DevIN: Fecha elemento section do documento. -->
            <section class="support-panel support-history-pane" aria-labelledby="support-history-title"> <!-- Nota DevIN: Declara seção temática. Inclui atributos de acessibilidade. -->
                <!-- No celular, a lista recolhe após escolher um chamado para mostrar a resposta sem rolagem longa. -->
                <div data-support-history-overview> <!-- Nota DevIN: Declara agrupamento de elementos. -->
                <h2 id="support-history-title">Meus chamados</h2> <!-- Nota DevIN: Declara subtítulo de segundo nível. -->
                <p class="support-hint">Acompanhe as respostas sem precisar reenviar o problema.</p> <!-- Nota DevIN: Declara parágrafo. -->
                <div data-support-list class="support-ticket-list"> <!-- Nota DevIN: Declara agrupamento de elementos. -->
                    <p class="support-hint">Carregando seus chamados...</p> <!-- Nota DevIN: Declara parágrafo. -->
                </div> <!-- Nota DevIN: Fecha elemento div do documento. -->
                </div> <!-- Nota DevIN: Fecha elemento div do documento. -->
                <!-- O botão de retorno recupera a lista, sem perder a aba ou refazer o login. -->
                <button type="button" class="support-secondary support-history-back" data-support-history-back hidden>← Voltar aos meus chamados</button> <!-- Nota DevIN: Declara botão de ação. -->
                <div data-support-detail class="support-ticket-detail" hidden></div> <!-- Nota DevIN: Declara agrupamento de elementos. -->
            </section> <!-- Nota DevIN: Fecha elemento section do documento. -->
        </div> <!-- Nota DevIN: Fecha elemento div do documento. -->
    </div> <!-- Nota DevIN: Fecha o conteúdo integrado de suporte. -->
