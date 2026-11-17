/*
 * REVISÃO DETALHADA DEVIN — js/lenis-global.js
 * Finalidade: lenis global.
 * Domínio: interações do navegador.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */

(() => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    'use strict'; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.

    if (typeof window === 'undefined' || typeof document === 'undefined' || window.__devinLenis) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    const stylesheet = document.createElement('link'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    stylesheet.rel = 'stylesheet'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    stylesheet.href = 'https://unpkg.com/lenis@1.3.23/dist/lenis.css'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    document.head.appendChild(stylesheet); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.

    const script = document.createElement('script'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    script.src = 'https://unpkg.com/lenis@1.3.23/dist/lenis.min.js'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    script.async = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    script.onload = () => { // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        if (typeof window.Lenis !== 'function') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            console.error('Lenis carregou sem disponibilizar o construtor esperado.'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        // Nos dashboards a rolagem acontece em .lista-area, não na janela.
        const dashboardArea = document.querySelector('.dashboard-shell .lista-area'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        const useDashboardArea = dashboardArea // Explicação DevIN: Declara variável JavaScript useDashboardArea para reutilizar valor ou resultado.
            && /(auto|scroll)/.test(window.getComputedStyle(dashboardArea).overflowY); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        const boundary = useDashboardArea ? dashboardArea : document.body; // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.

        const preventNativeScroll = (node) => { // Explicação DevIN: Declara variável JavaScript preventNativeScroll para reutilizar valor ou resultado.
            if (!(node instanceof Element)) return false; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            if (node.closest('dialog, [data-lenis-prevent]')) return true; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

            for ( // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                let element = node; // Explicação DevIN: Declara variável JavaScript element para reutilizar valor ou resultado.
                element && element !== boundary && element !== document.body && element !== document.documentElement; // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                element = element.parentElement // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            ) { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                const overflowY = window.getComputedStyle(element).overflowY; // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                if (/(auto|scroll)/.test(overflowY) && element.scrollHeight > element.clientHeight) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    return true; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            return false; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        const lenis = new window.Lenis({ // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            smoothWheel: true, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            smoothTouch: true, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            lerp: 0.08, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            prevent: preventNativeScroll, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            ...(useDashboardArea ? { wrapper: dashboardArea, content: dashboardArea, eventsTarget: dashboardArea } : {}) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        window.__devinLenis = lenis; // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.

        if (useDashboardArea) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            // O conteúdo da lista muda sem redimensionar o wrapper (filtros, abas, footer).
            new MutationObserver(() => lenis.resize()).observe(dashboardArea, { childList: true, subtree: true }); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            window.addEventListener('load', () => lenis.resize()); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        const raf = (time) => { // Explicação DevIN: Declara variável JavaScript raf para reutilizar valor ou resultado.
            lenis.raf(time); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            window.requestAnimationFrame(raf); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        window.requestAnimationFrame(raf); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    script.onerror = () => { // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        console.error('Não foi possível carregar o Lenis; a rolagem nativa continuará ativa.'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    document.head.appendChild(script); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
})(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
