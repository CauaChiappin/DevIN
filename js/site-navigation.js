/*
 * REVISÃO DETALHADA DEVIN — js/site-navigation.js
 * Finalidade: site navigation.
 * Domínio: interações do navegador.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */

(() => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    'use strict'; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.

    const init = (root = document) => { // Explicação DevIN: Declara variável JavaScript init para reutilizar valor ou resultado.
        // Encontra o botão e o menu dentro do documento ou da raiz fornecida para reutilizar o módulo.
        const toggle = root.querySelector('[data-site-menu-toggle]'); // Explicação DevIN: Declara variável JavaScript toggle para reutilizar valor ou resultado.
        const menu = root.querySelector('[data-site-menu]') || root.querySelector('.navegacao'); // Explicação DevIN: Declara variável JavaScript menu para reutilizar valor ou resultado.

        if (!toggle || !menu) return null; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

        // Mantém estado visual e atributos acessíveis sincronizados.
        const setOpen = (open) => { // Explicação DevIN: Declara variável JavaScript setOpen para reutilizar valor ou resultado.
            menu.classList.toggle('is-open', open); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            toggle.setAttribute('aria-expanded', String(open)); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            toggle.setAttribute('aria-label', open ? 'Fechar menu' : 'Abrir menu'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        const close = () => setOpen(false); // Explicação DevIN: Declara variável JavaScript close para reutilizar valor ou resultado.

        setOpen(toggle.getAttribute('aria-expanded') === 'true'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

        toggle.addEventListener('click', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
            setOpen(toggle.getAttribute('aria-expanded') !== 'true'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        menu.querySelectorAll('a').forEach((link) => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            link.addEventListener('click', close); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        root.addEventListener('keydown', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
            if (event.key === 'Escape') close(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        root.addEventListener('click', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
            const insideMenu = typeof menu.contains === 'function' && menu.contains(event.target); // Explicação DevIN: Declara variável JavaScript insideMenu para reutilizar valor ou resultado.
            const insideToggle = typeof toggle.contains === 'function' && toggle.contains(event.target); // Explicação DevIN: Declara variável JavaScript insideToggle para reutilizar valor ou resultado.
            if (!insideToggle && !insideMenu) close(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        return { close, setOpen }; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    if (typeof window !== 'undefined') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        window.DevINSiteNavigation = { init }; // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    if (typeof document !== 'undefined') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if (document.readyState === 'loading') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            document.addEventListener('DOMContentLoaded', () => init()); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
            init(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
})(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

document.addEventListener('submit', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    // Intercepta envios repetidos e marca o formulário como ocupado sem cancelar validações nativas.
    const form = event.target; // Explicação DevIN: Declara variável JavaScript form para reutilizar valor ou resultado.
    if (!(form instanceof HTMLFormElement)) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

    if (form.dataset.submitPending === 'true') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        event.preventDefault(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    queueMicrotask(() => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        if (event.defaultPrevented || !form.checkValidity()) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

        form.dataset.submitPending = 'true'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        form.setAttribute('aria-busy', 'true'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((button) => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            button.disabled = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
}, true); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
