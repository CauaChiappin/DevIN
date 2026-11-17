/*
 * REVISÃO DETALHADA DEVIN — js/curriculo-abnt.js
 * Finalidade: curriculo abnt.
 * Domínio: interações do navegador.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */

(() => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    'use strict'; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.

    const root = document.querySelector('[data-abnt-review]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const button = document.querySelector('[data-abnt-run]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const status = document.querySelector('[data-abnt-status]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const result = document.querySelector('[data-abnt-result]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    if (!(root instanceof HTMLElement) || !(button instanceof HTMLButtonElement) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        || !(status instanceof HTMLElement) || !(result instanceof HTMLElement)) return; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.

    button.addEventListener('click', async () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        button.disabled = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        status.textContent = 'Analisando seu currículo...'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        status.dataset.state = 'loading'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        result.hidden = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.

        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            const response = await fetch('../php/analisar_curriculo_abnt_api.php', { // Explicação DevIN: Inicia solicitação HTTP assíncrona a um endereço configurado.
                method: 'POST', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                credentials: 'same-origin', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                headers: { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                    'Content-Type': 'application/json', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    'X-CSRF-Token': root.dataset.csrf || '', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                body: JSON.stringify({ consent: true }), // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            const payload = await response.json(); // Explicação DevIN: Declara variável JavaScript payload para reutilizar valor ou resultado.
            if (!response.ok || payload.sucesso !== true || !payload.revisao) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new Error(payload.mensagem || 'Não foi possível revisar o currículo.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            result.replaceChildren(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            const summary = document.createElement('p'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            summary.className = 'resume-review__summary'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            summary.textContent = payload.revisao.resumo; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            const conclusion = document.createElement('p'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            conclusion.className = 'resume-review__conclusion'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            conclusion.textContent = payload.revisao.conclusao; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            result.append(summary, conclusion); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

            if (Array.isArray(payload.revisao.pontos) && payload.revisao.pontos.length > 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                const heading = document.createElement('h2'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                heading.textContent = 'Pontos para revisar'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                const list = document.createElement('ul'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                payload.revisao.pontos.forEach((point) => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                    if (typeof point?.titulo !== 'string' || typeof point?.orientacao !== 'string') return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    const item = document.createElement('li'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                    const title = document.createElement('strong'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                    title.textContent = point.titulo; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    const advice = document.createElement('p'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                    advice.textContent = point.orientacao; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    item.append(title, advice); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                    list.appendChild(item); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                result.append(heading, list); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            result.hidden = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            status.textContent = 'Revisão concluída. Confira as sugestões e valide os dados do documento.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            status.dataset.state = 'success'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } catch (error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            status.textContent = error instanceof Error ? error.message : 'Não foi possível revisar o currículo.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            status.dataset.state = 'error'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            button.disabled = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
})(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.