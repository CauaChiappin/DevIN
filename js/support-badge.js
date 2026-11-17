/**
 * Indicador discreto de chamados abertos no menu ADM.
 * O endpoint revalida a sessão no servidor; nenhum dado pessoal é exposto.
 * Falhas de rede ou banco não interrompem a navegação do painel principal.
 */
(() => {
    'use strict';
    const badge = document.querySelector('[data-support-menu-count]');
    if (!badge) return; // Apenas o painel administrativo possui essa entrada.

    async function refresh() {
        const url = new URL('support_api.php?action=count', window.location.href);
        try {
            const response = await fetch(url.toString(), {
                credentials: 'same-origin', cache: 'no-store'
            });
            if (!response.ok) return; // Sistema de suporte não implantado ainda.
            const result = await response.json();
            if (!result.sucesso) return;
            const count = Math.max(0, Number(result.count) || 0);
            badge.hidden = count === 0;
            badge.textContent = count > 99 ? '99+' : String(count);
            badge.setAttribute('aria-label', count + ' chamados abertos');
        } catch (_) {
            // Disponibilidade da Central não deve impedir o restante do dashboard.
        }
    }
    refresh(); // Contagem inicial ao abrir qualquer tela administrativa.
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) void refresh(); // Reconsulta ao voltar para a aba.
    });
})();
