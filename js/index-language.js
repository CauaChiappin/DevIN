(() => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    'use strict'; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.

    const storageKey = 'devin-public-language'; // Explicação DevIN: Declara variável JavaScript storageKey para reutilizar valor ou resultado.
    const supportedLanguages = ['pt-BR', 'en', 'es']; // Explicação DevIN: Declara variável JavaScript supportedLanguages para reutilizar valor ou resultado.

    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        const requestedLanguage = new URLSearchParams(window.location.search).get('lang'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        const savedLanguage = requestedLanguage || window.localStorage.getItem(storageKey); // Explicação DevIN: Lê ou atualiza preferências guardadas no armazenamento local do navegador.
        if (supportedLanguages.includes(savedLanguage)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            document.documentElement.lang = savedLanguage; // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } catch (error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        console.warn('Não foi possível carregar o idioma salvo da página inicial.', error); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

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

    function buildDropdown(select) { // Explicação DevIN: Declara função/método buildDropdown, com seus parâmetros e comportamento.
        const style = document.createElement('style'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        style.textContent = dropdownStyles; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        document.head.appendChild(style); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.

        const wrapper = document.createElement('div'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        wrapper.className = 'lang-dd'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        select.parentNode.insertBefore(wrapper, select); // Explicação DevIN: Continua expressão ou bloco pertencente a função buildDropdown.
        wrapper.appendChild(select); // Explicação DevIN: Continua expressão ou bloco pertencente a função buildDropdown.
        select.tabIndex = -1; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        select.setAttribute('aria-hidden', 'true'); // Explicação DevIN: Continua expressão ou bloco pertencente a função buildDropdown.

        const button = document.createElement('button'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        button.type = 'button'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        button.className = 'lang-dd-btn'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        button.setAttribute('aria-haspopup', 'listbox'); // Explicação DevIN: Continua expressão ou bloco pertencente a função buildDropdown.
        button.setAttribute('aria-expanded', 'false'); // Explicação DevIN: Continua expressão ou bloco pertencente a função buildDropdown.
        button.innerHTML = '<b class="lang-dd-label"></b><svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 4.5l4 4 4-4"/></svg>'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.

        const menu = document.createElement('ul'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        menu.className = 'lang-dd-menu'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        menu.setAttribute('role', 'listbox'); // Explicação DevIN: Continua expressão ou bloco pertencente a função buildDropdown.

        const items = Array.from(select.options).map((option) => { // Explicação DevIN: Declara variável JavaScript items para reutilizar valor ou resultado.
            const item = document.createElement('li'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            item.setAttribute('role', 'option'); // Explicação DevIN: Continua expressão ou bloco pertencente a função buildDropdown.
            item.tabIndex = -1; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            item.dataset.value = option.value; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            item.textContent = option.textContent; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            menu.appendChild(item); // Explicação DevIN: Continua expressão ou bloco pertencente a função buildDropdown.
            return item; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        const sync = () => { // Explicação DevIN: Declara variável JavaScript sync para reutilizar valor ou resultado.
            button.firstChild.textContent = select.options[select.selectedIndex].textContent; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            items.forEach((item) => item.setAttribute('aria-selected', String(item.dataset.value === select.value))); // Explicação DevIN: Continua expressão ou bloco pertencente a função buildDropdown.
        }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        const setOpen = (open) => { // Explicação DevIN: Declara variável JavaScript setOpen para reutilizar valor ou resultado.
            wrapper.classList.toggle('open', open); // Explicação DevIN: Continua expressão ou bloco pertencente a função buildDropdown.
            button.setAttribute('aria-expanded', String(open)); // Explicação DevIN: Continua expressão ou bloco pertencente a função buildDropdown.
        }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        button.addEventListener('click', () => setOpen(!wrapper.classList.contains('open'))); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        menu.addEventListener('click', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
            const item = event.target.closest('li'); // Explicação DevIN: Declara variável JavaScript item para reutilizar valor ou resultado.
            if (!item) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            setOpen(false); // Explicação DevIN: Continua expressão ou bloco pertencente a função buildDropdown.
            button.focus(); // Explicação DevIN: Continua expressão ou bloco pertencente a função buildDropdown.
            if (select.value !== item.dataset.value) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                select.value = item.dataset.value; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                select.dispatchEvent(new Event('change', { bubbles: true })); // Explicação DevIN: Continua expressão ou bloco pertencente a função buildDropdown.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            sync(); // Explicação DevIN: Continua expressão ou bloco pertencente a função buildDropdown.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        menu.addEventListener('keydown', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
            const index = items.indexOf(document.activeElement); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            if (event.key === 'ArrowDown') { event.preventDefault(); items[Math.min(index + 1, items.length - 1)].focus(); } // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            if (event.key === 'ArrowUp') { event.preventDefault(); items[Math.max(index - 1, 0)].focus(); } // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); document.activeElement.click(); } // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        button.addEventListener('keydown', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
            if (event.key === 'ArrowDown') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                event.preventDefault(); // Explicação DevIN: Continua expressão ou bloco pertencente a função buildDropdown.
                setOpen(true); // Explicação DevIN: Continua expressão ou bloco pertencente a função buildDropdown.
                (items.find((item) => item.getAttribute('aria-selected') === 'true') || items[0]).focus(); // Explicação DevIN: Continua expressão ou bloco pertencente a função buildDropdown.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        document.addEventListener('click', (event) => { if (!wrapper.contains(event.target)) setOpen(false); }); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        document.addEventListener('keydown', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
            if (event.key === 'Escape' && wrapper.classList.contains('open')) { setOpen(false); button.focus(); } // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        wrapper.append(button, menu); // Explicação DevIN: Continua expressão ou bloco pertencente a função buildDropdown.
        sync(); // Explicação DevIN: Continua expressão ou bloco pertencente a função buildDropdown.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    document.addEventListener('DOMContentLoaded', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        const selector = document.querySelector('#site-language'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        if (!selector) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

        selector.value = supportedLanguages.includes(document.documentElement.lang) // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            ? document.documentElement.lang // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            : 'pt-BR'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        document.querySelectorAll('a[href*="cadastro_pessoa.php"], a[href*="cadastro_empresa.php"], a[href*="login.php"]') // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            .forEach((link) => { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                const url = new URL(link.href, window.location.href); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                url.searchParams.set('idioma', selector.value); // Explicação DevIN: Continua expressão ou bloco pertencente a função buildDropdown.
                link.href = url.toString(); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        buildDropdown(selector); // Explicação DevIN: Continua expressão ou bloco pertencente a função buildDropdown.
        selector.addEventListener('change', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
            const language = selector.value; // Explicação DevIN: Declara variável JavaScript language para reutilizar valor ou resultado.
            if (!supportedLanguages.includes(language)) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                window.localStorage.setItem(storageKey, language); // Explicação DevIN: Lê ou atualiza preferências guardadas no armazenamento local do navegador.
            } catch (error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
                console.warn('Não foi possível salvar o idioma da página inicial.', error); // Explicação DevIN: Continua expressão ou bloco pertencente a função buildDropdown.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            const url = new URL(window.location.href); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            url.searchParams.set('lang', language); // Explicação DevIN: Continua expressão ou bloco pertencente a função buildDropdown.
            url.searchParams.set('idioma', language); // Explicação DevIN: Continua expressão ou bloco pertencente a função buildDropdown.
            window.history.replaceState(null, '', url); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            window.location.reload(); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
})(); // Explicação DevIN: Continua expressão ou bloco pertencente a função buildDropdown.
