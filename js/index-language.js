(() => {
    'use strict';

    const storageKey = 'devin-public-language';
    const supportedLanguages = ['pt-BR', 'en', 'es'];

    try {
        const requestedLanguage = new URLSearchParams(window.location.search).get('lang');
        const savedLanguage = requestedLanguage || window.localStorage.getItem(storageKey);
        if (supportedLanguages.includes(savedLanguage)) {
            document.documentElement.lang = savedLanguage;
        }
    } catch (error) {
        console.warn('Não foi possível carregar o idioma salvo da página inicial.', error);
    }

    document.addEventListener('DOMContentLoaded', () => {
        const selector = document.querySelector('#site-language');
        if (!selector) return;

        selector.value = supportedLanguages.includes(document.documentElement.lang)
            ? document.documentElement.lang
            : 'pt-BR';
        document.querySelectorAll('a[href*="cadastro_pessoa.php"], a[href*="cadastro_empresa.php"]')
            .forEach((link) => {
                const url = new URL(link.href, window.location.href);
                url.searchParams.set('idioma', selector.value);
                link.href = url.toString();
            });
        selector.addEventListener('change', () => {
            const language = selector.value;
            if (!supportedLanguages.includes(language)) return;

            try {
                window.localStorage.setItem(storageKey, language);
            } catch (error) {
                console.warn('Não foi possível salvar o idioma da página inicial.', error);
            }
            const url = new URL(window.location.href);
            url.searchParams.set('lang', language);
            window.history.replaceState(null, '', url);
            window.location.reload();
        });
    });
})();
