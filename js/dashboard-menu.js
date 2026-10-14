(() => {
    'use strict';

    const MOBILE_BREAKPOINT = 720;

    const init = ({ shell, toggle, windowRef = window }) => {
        /* Recebe elementos explicitamente para manter a lógica reutilizável e fácil de conectar ao painel.
         * O mesmo limite de 720px é usado pelo CSS para mudar entre navegação móvel e desktop. */
        if (!shell || !toggle) return null;

        const isMobile = () => {
            // matchMedia acompanha a regra CSS; o fallback cobre ambientes sem essa API.
            if (typeof windowRef.matchMedia === 'function') {
                return windowRef.matchMedia(`(max-width: ${MOBILE_BREAKPOINT}px)`).matches;
            }
            return Number(windowRef.innerWidth || 0) <= MOBILE_BREAKPOINT;
        };

        // Atualiza classe e estado ARIA juntos para manter interface e leitor de tela alinhados.
        const setOpen = (open) => {
            // Em desktop o menu não pode ficar neste modo expansível, mesmo se receber open=true.
            const nextOpen = isMobile() && open;
            shell.classList.toggle('mobile-menu-open', nextOpen);
            toggle.setAttribute('aria-expanded', String(nextOpen));
        };

        const syncViewport = () => {
            // Fecha o menu ao sair do modo móvel e preserva o estado atual ao redimensionar dentro dele.
            if (!isMobile()) setOpen(false);
            else toggle.setAttribute('aria-expanded', String(shell.classList.contains('mobile-menu-open')));
        };

        toggle.addEventListener('click', () => {
            // No desktop, o layout lateral é controlado por dashboard.js; aqui o clique só alterna no celular.
            if (isMobile()) setOpen(!shell.classList.contains('mobile-menu-open'));
        });

        windowRef.addEventListener?.('resize', syncViewport);
        // Reavalia imediatamente para que o estado inicial reflita a largura atual da janela.
        syncViewport();

        return { isMobile, setOpen, syncViewport };
    };

    if (typeof window !== 'undefined') {
        // Expõe a inicialização para dashboard.js sem executar o módulo em ambientes sem navegador.
        window.DevINDashboardMenu = { init };
    }
})();
