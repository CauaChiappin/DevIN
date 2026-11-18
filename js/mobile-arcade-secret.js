/**
 * Entrada secreta do Arcade por cinco toques no título "Time fundador".
 * Não depende de long-press, do tipo de dispositivo ou de um botão de jogos.
 * O servidor permanece responsável por sessão, CSRF e desbloqueio temporário.
 */
(() => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    'use strict'; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    const target = document.querySelector('[data-mobile-arcade-secret]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    if (!target) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    const requiredTaps = 5; // Explicação DevIN: Declara variável JavaScript requiredTaps para reutilizar valor ou resultado.
    const maxGap = 1700; // Explicação DevIN: Declara variável JavaScript maxGap para reutilizar valor ou resultado.
    let taps = 0; // Explicação DevIN: Declara variável JavaScript taps para reutilizar valor ou resultado.
    let lastTap = 0; // Explicação DevIN: Declara variável JavaScript lastTap para reutilizar valor ou resultado.
    let unlocking = false; // Explicação DevIN: Declara variável JavaScript unlocking para reutilizar valor ou resultado.

    async function unlock() { // Explicação DevIN: Declara função/método unlock, com seus parâmetros e comportamento.
        if (unlocking) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        unlocking = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            const csrfResponse = await fetch(new URL('csrf.php', window.location.href).toString(), { credentials: 'same-origin', cache: 'no-store' }); // Explicação DevIN: Inicia solicitação HTTP assíncrona a um endereço configurado.
            const csrfData = await csrfResponse.json(); // Explicação DevIN: Declara variável JavaScript csrfData para reutilizar valor ou resultado.
            if (!csrfResponse.ok || csrfData.sucesso !== true || typeof csrfData.token !== 'string') throw new Error('Não foi possível validar a sessão.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            const response = await fetch(new URL('arcade_unlock.php', window.location.href).toString(), { // Explicação DevIN: Inicia solicitação HTTP assíncrona a um endereço configurado.
                method: 'POST', // Explicação DevIN: Continua expressão ou bloco pertencente a função unlock.
                credentials: 'same-origin', // Explicação DevIN: Continua expressão ou bloco pertencente a função unlock.
                cache: 'no-store', // Explicação DevIN: Continua expressão ou bloco pertencente a função unlock.
                headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' }, // Explicação DevIN: Continua expressão ou bloco pertencente a função unlock.
                body: new URLSearchParams({ csrf_token: csrfData.token, modo: 'toque' }) // Explicação DevIN: Continua expressão ou bloco pertencente a função unlock.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            const result = await response.json(); // Explicação DevIN: Declara variável JavaScript result para reutilizar valor ou resultado.
            if (!response.ok || result.sucesso !== true || typeof result.url !== 'string') throw new Error(result.mensagem || 'Não foi possível liberar os jogos.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            window.location.assign(result.url); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        } catch (error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            unlocking = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            if (typeof window.alert === 'function') window.alert(error.message || 'Não foi possível abrir o Arcade.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    target.addEventListener('click', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        if (unlocking) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        const now = Date.now(); // Explicação DevIN: Declara variável JavaScript now para reutilizar valor ou resultado.
        if (!lastTap || now - lastTap > maxGap) taps = 0; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        lastTap = now; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        taps += 1; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        if (taps < requiredTaps) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        taps = 0; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        void unlock(); // Explicação DevIN: Continua expressão ou bloco pertencente a função unlock.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    document.addEventListener('visibilitychange', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        if (document.hidden) { taps = 0; lastTap = 0; } // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
})(); // Explicação DevIN: Continua expressão ou bloco pertencente a função unlock.
