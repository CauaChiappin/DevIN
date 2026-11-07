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

    const dropdownStyles = `
.lang-dd{position:relative;display:inline-block;font:inherit}
.lang-dd select{position:absolute;opacity:0;pointer-events:none;width:1px;height:1px}
.lang-dd-btn{display:inline-flex;align-items:center;justify-content:space-between;gap:10px;min-height:38px;padding:0 14px;border:1px solid rgba(0,74,148,.22);border-radius:999px;background:#fff;color:#111827;font:inherit;font-size:14px;font-weight:600;cursor:pointer;transition:border-color .2s,box-shadow .2s}
.lang-dd-btn:hover,.lang-dd.open .lang-dd-btn{border-color:#004a94}
.lang-dd-btn:focus-visible{outline:none;box-shadow:0 0 0 3px rgba(0,74,148,.2)}
.lang-dd-label{font-weight:600}
.lang-dd-btn svg{width:12px;height:12px;transition:transform .2s;flex:none}
.lang-dd.open .lang-dd-btn svg{transform:rotate(180deg)}
.lang-dd-menu{position:absolute;top:calc(100% + 8px);right:0;z-index:1000;min-width:100%;margin:0;padding:6px;list-style:none;border:1px solid rgba(0,74,148,.14);border-radius:16px;background:#fff;box-shadow:0 18px 40px rgba(0,40,90,.16);opacity:0;visibility:hidden;transform:translateY(-6px);transition:opacity .16s,transform .16s,visibility .16s}
.lang-dd.open .lang-dd-menu{opacity:1;visibility:visible;transform:none}
.lang-dd-menu li{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:9px 12px;border-radius:10px;color:#1f2937;font-size:14px;font-weight:600;white-space:nowrap;cursor:pointer}
.lang-dd-menu li:hover,.lang-dd-menu li:focus-visible{background:rgba(0,74,148,.08);outline:none}
.lang-dd-menu li[aria-selected="true"]{background:#004a94;color:#fff}
.lang-dd-menu li[aria-selected="true"]::after{content:"\\2713";font-size:12px}
@media (max-width:1240px){.lang-dd{width:min(68%,220px)}.lang-dd-btn{width:100%}.lang-dd-menu{left:0;right:auto}}
`;

    function buildDropdown(select) {
        const style = document.createElement('style');
        style.textContent = dropdownStyles;
        document.head.appendChild(style);

        const wrapper = document.createElement('div');
        wrapper.className = 'lang-dd';
        select.parentNode.insertBefore(wrapper, select);
        wrapper.appendChild(select);
        select.tabIndex = -1;
        select.setAttribute('aria-hidden', 'true');

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'lang-dd-btn';
        button.setAttribute('aria-haspopup', 'listbox');
        button.setAttribute('aria-expanded', 'false');
        button.innerHTML = '<b class="lang-dd-label"></b><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 4.5l4 4 4-4"/></svg>';

        const menu = document.createElement('ul');
        menu.className = 'lang-dd-menu';
        menu.setAttribute('role', 'listbox');

        const items = Array.from(select.options).map((option) => {
            const item = document.createElement('li');
            item.setAttribute('role', 'option');
            item.tabIndex = -1;
            item.dataset.value = option.value;
            item.textContent = option.textContent;
            menu.appendChild(item);
            return item;
        });

        const sync = () => {
            button.firstChild.textContent = select.options[select.selectedIndex].textContent;
            items.forEach((item) => item.setAttribute('aria-selected', String(item.dataset.value === select.value)));
        };
        const setOpen = (open) => {
            wrapper.classList.toggle('open', open);
            button.setAttribute('aria-expanded', String(open));
        };

        button.addEventListener('click', () => setOpen(!wrapper.classList.contains('open')));
        menu.addEventListener('click', (event) => {
            const item = event.target.closest('li');
            if (!item) return;
            setOpen(false);
            button.focus();
            if (select.value !== item.dataset.value) {
                select.value = item.dataset.value;
                select.dispatchEvent(new Event('change', { bubbles: true }));
            }
            sync();
        });
        menu.addEventListener('keydown', (event) => {
            const index = items.indexOf(document.activeElement);
            if (event.key === 'ArrowDown') { event.preventDefault(); items[Math.min(index + 1, items.length - 1)].focus(); }
            if (event.key === 'ArrowUp') { event.preventDefault(); items[Math.max(index - 1, 0)].focus(); }
            if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); document.activeElement.click(); }
        });
        button.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                setOpen(true);
                (items.find((item) => item.getAttribute('aria-selected') === 'true') || items[0]).focus();
            }
        });
        document.addEventListener('click', (event) => { if (!wrapper.contains(event.target)) setOpen(false); });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && wrapper.classList.contains('open')) { setOpen(false); button.focus(); }
        });

        wrapper.append(button, menu);
        sync();
    }

    document.addEventListener('DOMContentLoaded', () => {
        const selector = document.querySelector('#site-language');
        if (!selector) return;

        selector.value = supportedLanguages.includes(document.documentElement.lang)
            ? document.documentElement.lang
            : 'pt-BR';
        document.querySelectorAll('a[href*="cadastro_pessoa.php"], a[href*="cadastro_empresa.php"], a[href*="login.php"]')
            .forEach((link) => {
                const url = new URL(link.href, window.location.href);
                url.searchParams.set('idioma', selector.value);
                link.href = url.toString();
            });
        buildDropdown(selector);
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
            url.searchParams.set('idioma', language);
            window.history.replaceState(null, '', url);
            window.location.reload();
        });
    });
})();
