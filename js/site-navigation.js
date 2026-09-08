(() => {
    'use strict';

    const init = (root = document) => {
        const toggle = root.querySelector('[data-site-menu-toggle]');
        const menu = root.querySelector('[data-site-menu]') || root.querySelector('.navegacao');

        if (!toggle || !menu) return null;

        const setOpen = (open) => {
            menu.classList.toggle('is-open', open);
            toggle.setAttribute('aria-expanded', String(open));
            toggle.setAttribute('aria-label', open ? 'Fechar menu' : 'Abrir menu');
        };

        const close = () => setOpen(false);

        setOpen(toggle.getAttribute('aria-expanded') === 'true');

        toggle.addEventListener('click', () => {
            setOpen(toggle.getAttribute('aria-expanded') !== 'true');
        });

        menu.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', close);
        });

        root.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') close();
        });

        root.addEventListener('click', (event) => {
            const insideMenu = typeof menu.contains === 'function' && menu.contains(event.target);
            if (event.target !== toggle && !insideMenu) close();
        });

        return { close, setOpen };
    };

    if (typeof window !== 'undefined') {
        window.DevINSiteNavigation = { init };
    }

    if (typeof document !== 'undefined') {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => init());
        } else {
            init();
        }
    }
})();
