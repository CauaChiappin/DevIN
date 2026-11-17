/*
 * REVISÃO DETALHADA DEVIN — js/redefinir.js
 * Finalidade: redefinir.
 * Domínio: interações do navegador.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */

document.addEventListener('DOMContentLoaded', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    // Obtém os campos e indicadores usados para feedback imediato durante a redefinição.
    const novaSenha = document.getElementById('nova_senha'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const reqLen = document.getElementById('req-len'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const reqUpper = document.getElementById('req-upper'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const reqSpecial = document.getElementById('req-special'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const formReset = document.getElementById('formReset'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const btnCadastrar = document.getElementById('btnCadastrar'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.

    // Atualiza cada requisito visual conforme a senha é digitada.
    novaSenha.addEventListener('input', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        const val = novaSenha.value; // Explicação DevIN: Declara variável JavaScript val para reutilizar valor ou resultado.

        if (val.length >=8) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            reqLen.className = 'check-item check-valid'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            reqLen.querySelector('.icon').textContent = '✓'; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
            reqLen.className = 'check-item check-invalid'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            reqLen.querySelector('.icon').textContent = 'ⓘ'; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if (/[A-Z]/.test(val)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            reqUpper.className = 'check-item check-valid'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            reqUpper.querySelector('.icon').textContent = '✓'; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
            reqUpper.className = 'check-item check-invalid'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            reqUpper.querySelector('.icon').textContent = 'ⓘ'; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if (/[^a-zA-Z0-9]/.test(val)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            reqSpecial.className = 'check-item check-valid'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            reqSpecial.querySelector('.icon').textContent = '✓'; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
            reqSpecial.className = 'check-item check-invalid'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            reqSpecial.querySelector('.icon').textContent = 'ⓘ'; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Os botões revelam apenas o campo indicado pelo atributo data-toggle.
    document.querySelectorAll('[data-toggle]').forEach(btn => { // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        btn.addEventListener('click', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
            const targetId = btn.getAttribute('data-toggle'); // Explicação DevIN: Declara variável JavaScript targetId para reutilizar valor ou resultado.
            const input = document.getElementById(targetId); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            if (input) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                const isPass = input.type === 'password'; // Explicação DevIN: Declara variável JavaScript isPass para reutilizar valor ou resultado.
                input.type = isPass ? 'text' : 'password'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                btn.classList.toggle('active', isPass); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                const image = btn.querySelector('img'); // Explicação DevIN: Declara variável JavaScript image para reutilizar valor ou resultado.
                if (image) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    image.src = isPass ? '../img/olho_aberto.png' : '../img/olho_fechado.png'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                btn.setAttribute('aria-label', isPass ? 'Ocultar senha' : 'Mostrar senha'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    if (formReset && btnCadastrar) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        formReset.addEventListener('submit', (e) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
            // Desabilita o botão depois da validação visual para impedir alterações duplicadas.
            if (btnCadastrar.disabled) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                e.preventDefault(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            btnCadastrar.disabled = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            btnCadastrar.textContent = 'Cadastrando...'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
