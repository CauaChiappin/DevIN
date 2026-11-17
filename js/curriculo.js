/*
 * REVISÃO DETALHADA DEVIN — js/curriculo.js
 * Finalidade: curriculo.
 * Domínio: interações do navegador.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */

(() => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    'use strict'; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.

    const translate = (value) => typeof window.DevINTranslate === 'function' // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        ? window.DevINTranslate(value) // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        : value; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    const consent = document.querySelector('[data-resume-consent]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const generateButton = document.querySelector('[data-generate-resume]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const printButton = document.querySelector('[data-print-resume]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const status = document.querySelector('[data-resume-status]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const output = document.querySelector('[data-resume-output]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const emptyState = document.querySelector('[data-resume-empty]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const content = document.querySelector('[data-resume-content]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    if (printButton instanceof HTMLButtonElement) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        printButton.addEventListener('click', () => window.print()); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    if (!(consent instanceof HTMLInputElement) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        || !(status instanceof HTMLElement) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        || !(output instanceof HTMLElement) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        || !(emptyState instanceof HTMLElement) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        || !(content instanceof HTMLElement)) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    if (!(generateButton instanceof HTMLButtonElement)) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

    const sections = [ // Explicação DevIN: Declara variável JavaScript sections para reutilizar valor ou resultado.
        ['resumo', translate('Resumo profissional')], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        ['formacao', translate('Formação')], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        ['experiencia', translate('Experiência profissional')], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        ['cursos', translate('Cursos e certificações')], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        ['idiomas', translate('Idiomas')], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.

    consent.addEventListener('change', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        generateButton.disabled = !consent.checked; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    generateButton.addEventListener('click', async () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        if (!consent.checked || generateButton.disabled) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

        generateButton.disabled = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        consent.disabled = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        generateButton.textContent = translate('Gerando seu currículo...'); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        status.textContent = translate('A IA está organizando seus dados. Isso pode levar alguns segundos.'); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        status.dataset.state = 'loading'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.

        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            const response = await fetch('../php/gerar_curriculo_api.php', { // Explicação DevIN: Inicia solicitação HTTP assíncrona a um endereço configurado.
                method: 'POST', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                credentials: 'same-origin', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                headers: { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                    'Content-Type': 'application/json', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    'X-CSRF-Token': document.querySelector('[data-resume-csrf]')?.getAttribute('data-resume-csrf') // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                        || '', // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                body: JSON.stringify({ consent: true }), // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            let result; // Explicação DevIN: Declara variável JavaScript result para reutilizar valor ou resultado.
            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                result = await response.json(); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            } catch { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                throw new Error(response.redirected // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
                    ? translate('Sua sessão expirou. Entre novamente e tente gerar o currículo.') // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    : translate('O serviço de geração retornou uma resposta inválida. Tente novamente.')); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            if (!response.ok || result.sucesso !== true || !result.curriculo) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new Error(translate(result.mensagem || 'Não foi possível gerar o currículo.')); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            content.replaceChildren(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            const name = document.createElement('h2'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            name.textContent = result.curriculo.nome; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            content.appendChild(name); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

            sections.forEach(([key, title]) => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                const value = result.curriculo[key]; // Explicação DevIN: Declara variável JavaScript value para reutilizar valor ou resultado.
                const editorField = document.querySelector(`[name="resume[${key}]"]`); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                if (editorField instanceof HTMLTextAreaElement) editorField.value = value; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                if (typeof value !== 'string' || value.trim() === '') return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                const section = document.createElement('section'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                section.className = 'dashboard-resume__section'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                const heading = document.createElement('h3'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                heading.textContent = title; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                const paragraph = document.createElement('p'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                paragraph.textContent = value; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                section.append(heading, paragraph); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                content.appendChild(section); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            emptyState.hidden = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            content.hidden = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            output.classList.remove('dashboard-resume__paper--empty'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            if (printButton) printButton.hidden = false; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            status.textContent = translate('Currículo gerado. Revise todas as informações antes de imprimir ou salvar em PDF.'); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            status.dataset.state = 'success'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } catch (error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            status.textContent = error instanceof Error // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                ? translate(error.message) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                : translate('Não foi possível gerar o currículo. Tente novamente.'); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            status.dataset.state = 'error'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
            consent.disabled = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            generateButton.textContent = translate(content.hidden // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                ? '✦ Tentar gerar novamente' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                : '✦ Gerar novamente'); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            generateButton.disabled = !consent.checked; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    const generatorPanel = generateButton.closest('[data-resume-auto-generate]'); // Explicação DevIN: Declara variável JavaScript generatorPanel para reutilizar valor ou resultado.
    if (generatorPanel?.getAttribute('data-resume-auto-generate') === 'true') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        consent.checked = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        generateButton.disabled = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        generateButton.click(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
})(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
