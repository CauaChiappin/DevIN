/**
 * Segredo do Arcade para celulares na seção "Sobre nós" de todos os dashboards.
 * Um toque contínuo de três segundos no pequeno controle abre php/arcade.php.
 * O desktop conserva o código Konami em konami-arcade.js, sem alterações.
 * O gesto libera uma sessão temporária no backend, que exige login e CSRF.
 */
(() => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    'use strict'; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    const button = document.querySelector('[data-mobile-arcade-secret]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    if (!button) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    const coarseTouch = window.matchMedia('(hover: none) and (pointer: coarse)'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const holdMilliseconds = 3000; // Explicação DevIN: Declara variável JavaScript holdMilliseconds para reutilizar valor ou resultado.
    const moveTolerancePixels = 14; // Explicação DevIN: Declara variável JavaScript moveTolerancePixels para reutilizar valor ou resultado.
    let timer = null; // Explicação DevIN: Declara variável JavaScript timer para reutilizar valor ou resultado.
    let navigationTimer = null; // Explicação DevIN: Declara variável JavaScript navigationTimer para reutilizar valor ou resultado.
    let trackingPointer = null; // Explicação DevIN: Declara variável JavaScript trackingPointer para reutilizar valor ou resultado.
    let startX = 0; // Explicação DevIN: Declara variável JavaScript startX para reutilizar valor ou resultado.
    let startY = 0; // Explicação DevIN: Declara variável JavaScript startY para reutilizar valor ou resultado.
    let opening = false; // Explicação DevIN: Declara variável JavaScript opening para reutilizar valor ou resultado.

    function cancel() { // Explicação DevIN: Declara função/método cancel, com seus parâmetros e comportamento.
        if (timer !== null) window.clearTimeout(timer); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if (navigationTimer !== null) window.clearTimeout(navigationTimer); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        timer = null; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        navigationTimer = null; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        trackingPointer = null; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        opening = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        button.classList.remove('is-holding'); // Explicação DevIN: Continua expressão ou bloco pertencente a função cancel.
        button.classList.remove('is-discovered'); // Explicação DevIN: Continua expressão ou bloco pertencente a função cancel.
        button.setAttribute('aria-label', 'Segredo do DevIN: mantenha pressionado por três segundos para abrir os jogos'); // Explicação DevIN: Continua expressão ou bloco pertencente a função cancel.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    function openArcade() { // Explicação DevIN: Declara função/método openArcade, com seus parâmetros e comportamento.
        if (opening || !coarseTouch.matches) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        opening = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        timer = null; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        button.classList.remove('is-holding'); // Explicação DevIN: Continua expressão ou bloco pertencente a função openArcade.
        button.classList.add('is-discovered'); // Explicação DevIN: Continua expressão ou bloco pertencente a função openArcade.
        button.setAttribute('aria-label', 'Arcade encontrado! Abrindo jogos DevIN'); // Explicação DevIN: Continua expressão ou bloco pertencente a função openArcade.
        if (typeof navigator.vibrate === 'function') navigator.vibrate(45); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        navigationTimer = window.setTimeout(async () => { // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            if (!opening) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            navigationTimer = null; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            trackingPointer = null; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            try {
                const csrfResponse = await fetch(new URL('csrf.php', window.location.href).toString(), { credentials: 'same-origin' });
                const csrfData = await csrfResponse.json();
                if (!csrfResponse.ok || csrfData.sucesso !== true || typeof csrfData.token !== 'string') {
                    throw new Error('Não foi possível validar a sessão.');
                }
                const response = await fetch(new URL('arcade_unlock.php', window.location.href).toString(), {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
                    body: new URLSearchParams({ csrf_token: csrfData.token, modo: 'toque' })
                });
                const result = await response.json();
                if (!response.ok || result.sucesso !== true || typeof result.url !== 'string') {
                    throw new Error(result.mensagem || 'Não foi possível liberar os jogos.');
                }
                if (opening) window.location.assign(result.url);
            } catch (error) {
                if (opening) {
                    cancel();
                    button.setAttribute('aria-label', 'Não foi possível abrir o Arcade. Tente novamente.');
                }
            } // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        }, 260); // Explicação DevIN: Continua expressão ou bloco pertencente a função openArcade.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    button.addEventListener('pointerdown', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        if (!coarseTouch.matches || opening || event.button !== 0 || event.pointerType === 'mouse') return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if (trackingPointer !== null) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        trackingPointer = event.pointerId; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        startX = event.clientX; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        startY = event.clientY; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        button.classList.add('is-holding'); // Explicação DevIN: Continua expressão ou bloco pertencente a função openArcade.
        timer = window.setTimeout(openArcade, holdMilliseconds); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    button.addEventListener('pointermove', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        if (event.pointerId !== trackingPointer) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if (Math.hypot(event.clientX - startX, event.clientY - startY) > moveTolerancePixels) cancel(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    for (const type of ['pointerup', 'pointercancel', 'pointerleave']) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
        button.addEventListener(type, (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
            if (event.pointerId === trackingPointer) cancel(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    button.addEventListener('contextmenu', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        if (coarseTouch.matches) event.preventDefault(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    window.addEventListener('scroll', cancel, { passive: true }); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    document.addEventListener('visibilitychange', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        if (document.hidden) cancel(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    window.addEventListener('blur', cancel); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    button.addEventListener('click', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        event.preventDefault(); // Explicação DevIN: Continua expressão ou bloco pertencente a função openArcade.
        if (event.detail === 0) openArcade(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
})(); // Explicação DevIN: Continua expressão ou bloco pertencente a função openArcade.
