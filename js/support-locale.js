/**
 * Tradução determinística da Central de Suporte.
 * Isola o atendimento da tradução automática do navegador e do observador global.
 * Nunca traduz relato, nome, e-mail, anexo ou outra informação fornecida por usuários.
 */
(() => { // Explicação DevIN: Isola o tradutor da Central de Ajuda.
    'use strict'; // Explicação DevIN: Utiliza execução estrita.
    const root = document.querySelector('[data-support-root]'); // Explicação DevIN: Localiza o painel já autenticado.
    if (!root || typeof window.DevINTranslate !== 'function') return; // Explicação DevIN: Funciona somente quando o tradutor do site está disponível.
    const translate = window.DevINTranslate; // Explicação DevIN: Reaproveita o idioma atual do DevIN.
    const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT); // Explicação DevIN: Percorre nós textuais sem usar innerHTML.
    let node = null; // Explicação DevIN: Guarda o texto estático corrente.
    while ((node = walker.nextNode())) { // Explicação DevIN: Processa textos existentes no formulário e no ADM.
        const parent = node.parentElement; // Explicação DevIN: Verifica origem de cada texto.
        if (!parent || parent.closest('script, style, textarea, input, [data-support-list], [data-support-detail], [data-support-feedback], [data-support-mobile-message], [data-support-file-preview]')) continue; // Explicação DevIN: Mantém intactos relatos e valores dinâmicos.
        if (parent.tagName === 'STRONG' && parent.closest('[data-support-create]')) continue; // Explicação DevIN: Preserva o nome de usuário ou empresa.
        const value = translate(node.nodeValue); // Explicação DevIN: Traduz apenas termos conhecidos do dicionário.
        if (value !== node.nodeValue) node.nodeValue = value; // Explicação DevIN: Muda somente a interface do atendimento.
    } // Explicação DevIN: Finaliza os textos visíveis.
    root.querySelectorAll('[placeholder], [aria-label], [title]').forEach(element => { // Explicação DevIN: Traduz dicas de campos e textos acessíveis.
        for (const name of ['placeholder', 'aria-label', 'title']) { // Explicação DevIN: Examina cada atributo seguro.
            if (!element.hasAttribute(name)) continue; // Explicação DevIN: Ignora atributo inexistente.
            const original = element.getAttribute(name); // Explicação DevIN: Lê o rótulo original em português.
            const translated = translate(original); // Explicação DevIN: Consulta as traduções atuais.
            if (translated !== original) element.setAttribute(name, translated); // Explicação DevIN: Aplica somente termos correspondentes.
        } // Explicação DevIN: Finaliza os atributos do elemento.
    }); // Explicação DevIN: Finaliza textos acessíveis.
})(); // Explicação DevIN: Conclui o módulo de localização do Suporte.
