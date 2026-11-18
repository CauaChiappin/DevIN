/*
 * REVISÃO DETALHADA DEVIN — js/konami-arcade.js
 * Finalidade: konami arcade.
 * Domínio: interações do navegador.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */

(() => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    'use strict'; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.

    const sequence = [ // Explicação DevIN: Declara variável JavaScript sequence para reutilizar valor ou resultado.
        'ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'KeyB', 'KeyA', 'Enter' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
    let position = 0; // Explicação DevIN: Declara variável JavaScript position para reutilizar valor ou resultado.
    let unlocking = false; // Explicação DevIN: Declara variável JavaScript unlocking para reutilizar valor ou resultado.

    const isTyping = (target) => target instanceof HTMLElement // Explicação DevIN: Declara variável JavaScript isTyping para reutilizar valor ou resultado.
        && (target.isContentEditable || /^(INPUT|TEXTAREA|SELECT)$/.test(target.tagName)); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.

    const notify = (message) => { // Explicação DevIN: Declara variável JavaScript notify para reutilizar valor ou resultado.
        let status = document.querySelector('[data-arcade-unlock-status]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        if (!status) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            status = document.createElement('div'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            status.dataset.arcadeUnlockStatus = ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            status.setAttribute('role', 'status'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            status.className = 'arcade-unlock-status'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            document.body.appendChild(status); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        status.textContent = message; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        status.hidden = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    const unlock = async () => { // Explicação DevIN: Declara variável JavaScript unlock para reutilizar valor ou resultado.
        unlocking = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            const csrfResponse = await fetch('../php/csrf.php', { credentials: 'same-origin' }); // Explicação DevIN: Inicia solicitação HTTP assíncrona a um endereço configurado.
            const csrfData = await csrfResponse.json(); // Explicação DevIN: Declara variável JavaScript csrfData para reutilizar valor ou resultado.
            if (!csrfResponse.ok || csrfData.sucesso !== true || typeof csrfData.token !== 'string') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new Error('Não foi possível validar a sessão. Atualize o dashboard e tente novamente.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            const response = await fetch('../php/arcade_unlock.php', { // Explicação DevIN: Inicia solicitação HTTP assíncrona a um endereço configurado.
                method: 'POST', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                credentials: 'same-origin', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' }, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                body: new URLSearchParams({ // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                    csrf_token: csrfData.token, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                    sequencia: JSON.stringify(sequence) // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
                }) // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            const result = await response.json(); // Explicação DevIN: Declara variável JavaScript result para reutilizar valor ou resultado.
            if (!response.ok || result.sucesso !== true || typeof result.url !== 'string') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new Error(result.mensagem || 'Não foi possível liberar os jogos.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            window.location.assign(result.url); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        } catch (error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            position = 0; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            notify(error.message || 'Não foi possível liberar os jogos. Atualize o dashboard e tente novamente.'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
            unlocking = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    document.addEventListener('keydown', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        if (unlocking || event.repeat) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if (isTyping(event.target)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            position = 0; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        const key = event.code; // Explicação DevIN: Declara variável JavaScript key para reutilizar valor ou resultado.
        if (key === sequence[position]) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            position += 1; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            if (position === sequence.length) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                event.preventDefault(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                position = 0; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                unlock(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        position = key === sequence[0] ? 1 : 0; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
})(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
