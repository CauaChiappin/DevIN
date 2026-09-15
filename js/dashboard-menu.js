(() => {
    'use strict';

    const MOBILE_BREAKPOINT = 720;

    const init = ({ shell, toggle, windowRef = window }) => {
        if (!shell || !toggle) return null;

        const isMobile = () => {
            if (typeof windowRef.matchMedia === 'function') {
                return windowRef.matchMedia(`(max-width: ${MOBILE_BREAKPOINT}px)`).matches;
            }
            return Number(windowRef.innerWidth || 0) <= MOBILE_BREAKPOINT;
        };

        const setOpen = (open) => {
            const nextOpen = isMobile() && open;
            shell.classList.toggle('mobile-menu-open', nextOpen);
            toggle.setAttribute('aria-expanded', String(nextOpen));
        };

        const syncViewport = () => {
            if (!isMobile()) setOpen(false);
            else toggle.setAttribute('aria-expanded', String(shell.classList.contains('mobile-menu-open')));
        };

        toggle.addEventListener('click', () => {
            if (isMobile()) setOpen(!shell.classList.contains('mobile-menu-open'));
        });

        windowRef.addEventListener?.('resize', syncViewport);
        syncViewport();

        return { isMobile, setOpen, syncViewport };
    };

    if (typeof window !== 'undefined') {
        window.DevINDashboardMenu = { init };
    }
})();
