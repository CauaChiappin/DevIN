<?php
/**
 * Componente interno da central de atendimento para ADMs.
 * A API confere novamente a sessão; ocultar um botão nunca concede permissão.
 * Nomes, e-mails e descrições são exibidos como texto, sem HTML dinâmico.
 */
?>
<section class="support-admin-view" data-support-root data-role="admin"
         data-endpoint="support_api.php" data-csrf="<?= h(csrfToken()) ?>">
    <header class="support-admin-head">
        <span class="support-eyebrow">CENTRAL DE ATENDIMENTO · DEVIN</span>
        <h1>Suporte aos usuários</h1>
        <p>Chamados recebidos de candidatos e empresas, com acompanhamento, anexos e resposta individual.</p>
        <div class="support-status-row">
            <span data-support-stat-open>Carregando chamados...</span>
            <button type="button" data-support-refresh class="support-secondary">Atualizar</button>
        </div>
    </header>
    <div class="support-admin-grid">
        <section class="support-panel" aria-labelledby="support-inbox-title">
            <h2 id="support-inbox-title">Caixa de entrada</h2>
            <label for="support-admin-search">Encontrar chamado</label>
            <input id="support-admin-search" type="search" data-support-search
                   placeholder="Nome, e-mail, categoria ou número..." maxlength="100">
            <div data-support-list class="support-ticket-list">
                <p class="support-hint">Carregando solicitações...</p>
            </div>
        </section>
        <section class="support-panel" aria-labelledby="support-answer-title">
            <h2 id="support-answer-title">Detalhes e resposta</h2>
            <div data-support-detail class="support-ticket-detail">
                <p class="support-hint">Escolha um chamado à esquerda para ler a descrição, ver anexos e responder.</p>
            </div>
            <!-- O botão de envio não existe até um chamado válido ser selecionado. -->
            <form data-support-reply hidden>
                <label for="support-admin-message">Responder como Suporte DevIN</label>
                <textarea id="support-admin-message" name="message" rows="6"
                          minlength="2" maxlength="3000" required
                          placeholder="Escreva uma resposta clara e respeitosa. Não solicite senhas ou tokens."></textarea>
                <div class="support-actions">
                    <button type="submit" class="support-primary">Responder ao usuário</button>
                    <button type="button" data-support-close class="support-secondary">Encerrar chamado</button>
                </div>
                <p class="support-feedback" data-support-feedback role="status" aria-live="polite"></p>
                <p class="support-hint">Ao responder, o usuário recebe a mensagem na plataforma e uma tentativa de e-mail. Se o SMTP falhar, uma opção de reenvio ficará disponível.</p>
            </form>
        </section>
    </div>
</section>
