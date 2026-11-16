document.addEventListener('DOMContentLoaded', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    // Espera o HTML existir para conectar validações e eventos aos formulários.
    const formRecuperacao = document.getElementById('formRecuperacao'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const formRedefinir = document.getElementById('formRedefinir'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.

    formRecuperacao?.addEventListener('submit', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        // Evita dois pedidos de código enquanto a primeira solicitação está sendo processada.
        const button = event.currentTarget.querySelector('button[type="submit"]'); // Explicação DevIN: Declara variável JavaScript button para reutilizar valor ou resultado.
        if (!button || button.disabled) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            event.preventDefault(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        button.disabled = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        button.textContent = 'Enviando...'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    const senha = document.getElementById('nova_senha'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const confirmar = document.getElementById('confirmar_senha'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const matchError = document.getElementById('match-error'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const requisitos = [ // Explicação DevIN: Declara variável JavaScript requisitos para reutilizar valor ou resultado.
        // Cada par associa um indicador visual à regra que deve ser satisfeita pela senha.
        [document.getElementById('req-length'), (value) => value.length >= 8], // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        [document.getElementById('req-upper'), (value) => /[A-Z]/.test(value)], // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        [document.getElementById('req-special'), (value) => /[^a-zA-Z0-9]/.test(value)], // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.

    function validarSenha() { // Explicação DevIN: Declara função/método validarSenha, com seus parâmetros e comportamento.
        // Reflete os critérios no checklist e compara a confirmação sem substituir a validação do servidor.
        const value = senha?.value || ''; // Explicação DevIN: Declara variável JavaScript value para reutilizar valor ou resultado.
        requisitos.forEach(([element, validar]) => { // Explicação DevIN: Continua expressão ou bloco pertencente a função validarSenha.
            if (!element) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            const valida = validar(value); // Explicação DevIN: Declara variável JavaScript valida para reutilizar valor ou resultado.
            element.classList.toggle('req-valid', valida); // Explicação DevIN: Continua expressão ou bloco pertencente a função validarSenha.
            element.classList.toggle('req-invalid', !valida); // Explicação DevIN: Continua expressão ou bloco pertencente a função validarSenha.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if (matchError && confirmar) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            matchError.classList.toggle('visible', confirmar.value !== '' && value !== confirmar.value); // Explicação DevIN: Continua expressão ou bloco pertencente a função validarSenha.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    senha?.addEventListener('input', validarSenha); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    confirmar?.addEventListener('input', validarSenha); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.

    formRedefinir?.addEventListener('submit', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        // Feedback no cliente evita envio incompleto; o PHP também valida os requisitos de segurança.
        const value = senha?.value || ''; // Explicação DevIN: Declara variável JavaScript value para reutilizar valor ou resultado.
        const confirmation = confirmar?.value || ''; // Explicação DevIN: Declara variável JavaScript confirmation para reutilizar valor ou resultado.
        const valid = value.length >= 8 // Explicação DevIN: Declara variável JavaScript valid para reutilizar valor ou resultado.
            && /[A-Z]/.test(value) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            && /[^a-zA-Z0-9]/.test(value) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            && value === confirmation; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.

        if (!valid) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            event.preventDefault(); // Explicação DevIN: Continua expressão ou bloco pertencente a função validarSenha.
            validarSenha(); // Explicação DevIN: Continua expressão ou bloco pertencente a função validarSenha.
            return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        const button = formRedefinir.querySelector('button[type="submit"]'); // Explicação DevIN: Declara variável JavaScript button para reutilizar valor ou resultado.
        if (button && !button.disabled) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            button.disabled = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            button.textContent = 'Redefinindo...'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    document.querySelectorAll('[data-password-toggle]').forEach((button) => { // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        button.addEventListener('click', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
            const field = document.getElementById(button.getAttribute('data-password-toggle')); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            if (!field) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

            const visible = field.type === 'password'; // Explicação DevIN: Declara variável JavaScript visible para reutilizar valor ou resultado.
            field.type = visible ? 'text' : 'password'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            const image = button.querySelector('img'); // Explicação DevIN: Declara variável JavaScript image para reutilizar valor ou resultado.
            if (image) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                image.src = visible ? '../img/olho_aberto.png' : '../img/olho_fechado.png'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                image.alt = visible ? 'Ocultar senha' : 'Mostrar senha'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            button.setAttribute('aria-label', visible ? 'Ocultar senha' : 'Mostrar senha'); // Explicação DevIN: Continua expressão ou bloco pertencente a função validarSenha.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
