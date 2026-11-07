<?php
declare(strict_types=1);
// Incluído somente pelo painel ADM após requireWebAuth('adm').
?>
<section class="jarvis-admin" id="jarvisAdmin" data-endpoint="api/jarvis/chat.php" data-csrf="<?= h(csrfToken()) ?>" data-page="<?= h($pagina) ?>">
    <button class="jarvis-admin__launcher" type="button" aria-label="Abrir JARVIS Admin" aria-controls="jarvisAdminPanel" aria-expanded="false" data-jarvis-toggle>
        <span aria-hidden="true">✦</span><span class="jarvis-admin__launcher-label">JARVIS</span>
    </button>
    <section class="jarvis-admin__panel" id="jarvisAdminPanel" role="dialog" aria-modal="false" aria-label="Assistente JARVIS Admin" hidden>
        <header class="jarvis-admin__header">
            <span class="jarvis-admin__mark" aria-hidden="true">✦</span>
            <div class="jarvis-admin__identity"><strong>JARVIS Admin</strong><small>Copiloto administrativo · DevIN</small></div>
            <button type="button" class="jarvis-admin__icon-button" aria-label="Limpar conversa" title="Limpar conversa" data-jarvis-clear>↻</button>
            <button type="button" class="jarvis-admin__icon-button" aria-label="Expandir chat" title="Expandir chat" data-jarvis-expand>⛶</button>
            <button type="button" class="jarvis-admin__icon-button" aria-label="Fechar chat" data-jarvis-close>×</button>
        </header>
        <div class="jarvis-admin__messages" role="log" aria-live="polite" aria-label="Mensagens do JARVIS" data-jarvis-messages>
            <p class="jarvis-admin__welcome">Olá! Posso conversar e consultar indicadores gerais do DevIN. Ações administrativas exigirão confirmação e ainda não estão habilitadas nesta versão.</p>
        </div>
        <div class="jarvis-admin__quick-actions">
            <button type="button" data-jarvis-summary>Resumo do DevIN</button>
        </div>
        <p class="jarvis-admin__status" role="status" aria-live="polite" data-jarvis-status></p>
        <form class="jarvis-admin__composer" data-jarvis-form>
            <label class="jarvis-admin__sr-only" for="jarvisAdminInput">Mensagem para o JARVIS</label>
            <textarea id="jarvisAdminInput" rows="1" maxlength="3000" placeholder="Pergunte ao JARVIS..." data-jarvis-input></textarea>
            <button type="submit" aria-label="Enviar mensagem" data-jarvis-send>➤</button>
        </form>
        <p class="jarvis-admin__disclaimer">A IA pode errar. Verifique informações antes de decidir.</p>
    </section>
</section>
