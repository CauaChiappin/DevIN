(() => {
    'use strict';

    if (typeof window === 'undefined' || typeof document === 'undefined' || window.__devinLenis) {
        return;
    }

    const stylesheet = document.createElement('link');
    stylesheet.rel = 'stylesheet';
    stylesheet.href = 'https://unpkg.com/lenis@1.3.23/dist/lenis.css';
    document.head.appendChild(stylesheet);

    const script = document.createElement('script');
    script.src = 'https://unpkg.com/lenis@1.3.23/dist/lenis.min.js';
    script.async = true;
    script.onload = () => {
        if (typeof window.Lenis !== 'function') {
            console.error('Lenis carregou sem disponibilizar o construtor esperado.');
            return;
        }

        const preventNativeScroll = (node) => {
            if (!(node instanceof Element)) return false;
            if (node.closest('dialog, [data-lenis-prevent]')) return true;

            for (
                let element = node;
                element && element !== document.body && element !== document.documentElement;
                element = element.parentElement
            ) {
                const overflowY = window.getComputedStyle(element).overflowY;
                if (/(auto|scroll)/.test(overflowY) && element.scrollHeight > element.clientHeight) {
                    return true;
                }
            }

            return false;
        };

        const lenis = new window.Lenis({
            smoothWheel: true,
            smoothTouch: true,
            lerp: 0.08,
            prevent: preventNativeScroll
        });

        window.__devinLenis = lenis;

        const raf = (time) => {
            lenis.raf(time);
            window.requestAnimationFrame(raf);
        };

        window.requestAnimationFrame(raf);
    };
    script.onerror = () => {
        console.error('Não foi possível carregar o Lenis; a rolagem nativa continuará ativa.');
    };
    document.head.appendChild(script);
})();
