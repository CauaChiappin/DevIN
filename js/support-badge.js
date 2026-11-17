/**
 * Indicador discreto de chamados abertos no menu ADM.
 * O endpoint revalida a sessão no servidor; nenhum dado pessoal é exposto.
 * Falhas de rede ou banco não interrompem a navegação do painel principal.
 */
(() => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    'use strict'; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    const badge = document.querySelector('[data-support-menu-count]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    if (!badge) return; // Apenas o painel administrativo possui essa entrada.

    async function refresh() { // Explicação DevIN: Declara função/método refresh, com seus parâmetros e comportamento.
        const url = new URL('support_api.php?action=count', window.location.href); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            const response = await fetch(url.toString(), { // Explicação DevIN: Inicia solicitação HTTP assíncrona a um endereço configurado.
                credentials: 'same-origin', cache: 'no-store' // Explicação DevIN: Continua expressão ou bloco pertencente a função refresh.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            if (!response.ok) return; // Sistema de suporte não implantado ainda.
            const result = await response.json(); // Explicação DevIN: Declara variável JavaScript result para reutilizar valor ou resultado.
            if (!result.sucesso) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            const count = Math.max(0, Number(result.count) || 0); // Explicação DevIN: Declara variável JavaScript count para reutilizar valor ou resultado.
            badge.hidden = count === 0; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            badge.textContent = count > 99 ? '99+' : String(count); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            badge.setAttribute('aria-label', count + ' chamados abertos'); // Explicação DevIN: Continua expressão ou bloco pertencente a função refresh.
        } catch (_) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            // Disponibilidade da Central não deve impedir o restante do dashboard.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    refresh(); // Contagem inicial ao abrir qualquer tela administrativa.
    document.addEventListener('visibilitychange', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        if (!document.hidden) void refresh(); // Reconsulta ao voltar para a aba.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
})(); // Explicação DevIN: Continua expressão ou bloco pertencente a função refresh.
