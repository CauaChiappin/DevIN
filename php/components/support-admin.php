<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
/**
 * Componente interno da central de atendimento para ADMs.
 * A API confere novamente a sessão; ocultar um botão nunca concede permissão.
 * Nomes, e-mails e descrições são exibidos como texto, sem HTML dinâmico.
 */
?>
<section class="support-admin-view" data-support-root data-role="admin"
         data-endpoint="support_api.php" data-csrf="<?= h(csrfToken()) ?>">
    <header class="support-admin-head"> <!-- Explicação DevIN: Declara cabeçalho da seção ou página. -->
        <span class="support-eyebrow">CENTRAL DE ATENDIMENTO · DEVIN</span> <!-- Explicação DevIN: Declara trecho em linha. -->
        <h1>Suporte aos usuários</h1> <!-- Explicação DevIN: Declara título principal. -->
        <p>Chamados recebidos de candidatos e empresas, com acompanhamento, anexos e resposta individual.</p> <!-- Explicação DevIN: Declara parágrafo. -->
        <div class="support-status-row"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
            <span data-support-stat-open>Carregando chamados...</span> <!-- Explicação DevIN: Declara trecho em linha. -->
            <button type="button" data-support-refresh class="support-secondary">Atualizar</button> <!-- Explicação DevIN: Declara botão de ação. -->
        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
    </header> <!-- Explicação DevIN: Fecha elemento header do documento. -->
    <!-- Em celular, alterna entre caixa de entrada e atendimento sem rolagem horizontal. -->
    <div class="support-mobile-tabs" data-support-mobile-tabs role="group" aria-label="Áreas do atendimento"> <!-- Explicação DevIN: Declara agrupamento de elementos. Inclui atributos de acessibilidade. -->
        <button type="button" class="support-mobile-tab" data-support-mobile-target="inbox" aria-pressed="true">Caixa de entrada</button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
        <button type="button" class="support-mobile-tab" data-support-mobile-target="detail" aria-pressed="false">Atendimento</button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
    <p class="support-mobile-message" data-support-mobile-message role="status" aria-live="polite"></p> <!-- Explicação DevIN: Declara parágrafo. Inclui atributos de acessibilidade. -->
    <div class="support-admin-grid support-mobile-panels" data-mobile-view="inbox"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
        <section class="support-panel support-inbox-pane" aria-labelledby="support-inbox-title"> <!-- Explicação DevIN: Declara seção temática. Inclui atributos de acessibilidade. -->
            <h2 id="support-inbox-title">Caixa de entrada</h2> <!-- Explicação DevIN: Declara subtítulo de segundo nível. -->
            <label for="support-admin-search">Encontrar chamado</label> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
            <input id="support-admin-search" type="search" data-support-search
                   placeholder="Nome, e-mail, categoria ou número..." maxlength="100">
            <!-- Filtro rápido, útil quando o ADM atende vários chamados pelo celular. -->
            <label for="support-admin-status">Filtrar atendimento</label> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
            <select id="support-admin-status" data-support-status-filter> <!-- Explicação DevIN: Declara seleção de opções. -->
                <option value="all">Todos os chamados</option> <!-- Explicação DevIN: Declara opção selecionável. -->
                <option value="aberto">Aguardando resposta</option> <!-- Explicação DevIN: Declara opção selecionável. -->
                <option value="respondido">Respondidos</option> <!-- Explicação DevIN: Declara opção selecionável. -->
                <option value="fechado">Encerrados</option> <!-- Explicação DevIN: Declara opção selecionável. -->
            </select> <!-- Explicação DevIN: Fecha elemento select do documento. -->
            <div data-support-list class="support-ticket-list"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                <p class="support-hint">Carregando solicitações...</p> <!-- Explicação DevIN: Declara parágrafo. -->
            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
        </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->
        <section class="support-panel support-detail-pane" aria-labelledby="support-answer-title"> <!-- Explicação DevIN: Declara seção temática. Inclui atributos de acessibilidade. -->
            <div class="support-detail-head"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                <h2 id="support-answer-title">Detalhes e resposta</h2> <!-- Explicação DevIN: Declara subtítulo de segundo nível. -->
                <button type="button" class="support-secondary support-mobile-back" data-support-back>Voltar à lista</button> <!-- Explicação DevIN: Declara botão de ação. -->
            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
            <div data-support-detail class="support-ticket-detail"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                <p class="support-hint">Escolha um chamado à esquerda para ler a descrição, ver anexos e responder.</p> <!-- Explicação DevIN: Declara parágrafo. -->
            </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
            <!-- O botão de envio não existe até um chamado válido ser selecionado. -->
            <form data-support-reply hidden> <!-- Explicação DevIN: Declara formulário de entrada de dados. -->
                <label for="support-admin-message">Responder como Suporte DevIN</label> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                <textarea id="support-admin-message" name="message" rows="6"
                          minlength="2" maxlength="3000" required
                          placeholder="Escreva uma resposta clara e respeitosa. Não solicite senhas ou tokens."></textarea>
                <!-- Contador acessível: ajuda a evitar respostas cortadas pelo limite de 3.000 caracteres. -->
                <div class="support-counter" data-support-reply-counter aria-live="off">0 / 3000 caracteres</div> <!-- Explicação DevIN: Declara agrupamento de elementos. Inclui atributos de acessibilidade. -->
                <div class="support-actions"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                    <button type="submit" class="support-primary">Responder ao usuário</button> <!-- Explicação DevIN: Declara botão de ação. -->
                    <button type="button" data-support-close class="support-secondary">Encerrar chamado</button> <!-- Explicação DevIN: Declara botão de ação. -->
                </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                <p class="support-feedback" data-support-feedback role="status" aria-live="polite"></p> <!-- Explicação DevIN: Declara parágrafo. Inclui atributos de acessibilidade. -->
                <p class="support-hint">Ao responder, o usuário recebe a mensagem na plataforma e uma tentativa de e-mail. Se o SMTP falhar, uma opção de reenvio ficará disponível.</p> <!-- Explicação DevIN: Declara parágrafo. -->
            </form> <!-- Explicação DevIN: Fecha elemento form do documento. -->
        </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->
    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
</section> <!-- Explicação DevIN: Fecha elemento section do documento. -->
