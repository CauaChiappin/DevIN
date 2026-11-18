/**
 * Tradução determinística da Central de Suporte.
 * Isola o atendimento da tradução automática do navegador e do observador global.
 * Nunca traduz relato, nome, e-mail, anexo ou outra informação fornecida por usuários.
 */
(() => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    'use strict'; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    const root = document.querySelector('[data-support-root]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    if (!root || typeof window.DevINTranslate !== 'function') return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    const translate = window.DevINTranslate; // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    let node = null; // Explicação DevIN: Declara variável JavaScript node para reutilizar valor ou resultado.
    while ((node = walker.nextNode())) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
        const parent = node.parentElement; // Explicação DevIN: Declara variável JavaScript parent para reutilizar valor ou resultado.
        if (!parent || parent.closest('script, style, textarea, input, [data-support-list], [data-support-detail], [data-support-feedback], [data-support-mobile-message], [data-support-file-preview]')) continue; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if (parent.tagName === 'STRONG' && parent.closest('[data-support-create]')) continue; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        const value = translate(node.nodeValue); // Explicação DevIN: Declara variável JavaScript value para reutilizar valor ou resultado.
        if (value !== node.nodeValue) node.nodeValue = value; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    root.querySelectorAll('[placeholder], [aria-label], [title]').forEach(element => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        for (const name of ['placeholder', 'aria-label', 'title']) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            if (!element.hasAttribute(name)) continue; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            const original = element.getAttribute(name); // Explicação DevIN: Declara variável JavaScript original para reutilizar valor ou resultado.
            const translated = translate(original); // Explicação DevIN: Declara variável JavaScript translated para reutilizar valor ou resultado.
            if (translated !== original) element.setAttribute(name, translated); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    const counter = root.querySelector('[data-support-description-counter]'); // Explicação DevIN: Declara variável JavaScript counter para reutilizar valor ou resultado.
    if (counter) counter.textContent = counter.textContent.replace(/caracteres$/, translate('caracteres')); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
})(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
