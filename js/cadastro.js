/*
 * REVISÃO DETALHADA DEVIN — js/cadastro.js
 * Finalidade: cadastro.
 * Domínio: interações do navegador.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */

const senhaInput = document.getElementById('senha'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
const confirmeSenhaInput = document.getElementById('confirme_senha'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
const reqLength = document.getElementById('req-length'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
const reqUpper = document.getElementById('req-upper'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
const reqSpecial = document.getElementById('req-special'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
const errorMatch = document.getElementById('error-match'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
const cadastroForm = document.getElementById('formCadastro'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
// Reutiliza o token no formulário; se a página não o trouxe, solicita um à sessão atual.
let csrfInput = cadastroForm?.querySelector('input[name="csrf_token"]'); // Explicação DevIN: Declara variável JavaScript csrfInput para reutilizar valor ou resultado.

let csrfRequest = null; // Explicação DevIN: Declara variável JavaScript csrfRequest para reutilizar valor ou resultado.
if (cadastroForm && !csrfInput) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    /* O formulário estático não consegue renderizar csrfToken() no PHP. O token é buscado
     * com as credenciais da mesma origem e inserido antes de retomar o envio interrompido. */
    csrfRequest = fetch('../php/csrf.php', { credentials: 'same-origin' }) // Explicação DevIN: Inicia solicitação HTTP assíncrona a um endereço configurado.
        .then((response) => { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            if (!response.ok) throw new Error('Falha ao iniciar a sessao.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            return response.json(); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        }) // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        .then((data) => { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            if (!data.token) throw new Error('Token CSRF ausente.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            const input = document.createElement('input'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            input.type = 'hidden'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            input.name = 'csrf_token'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            input.value = data.token; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            cadastroForm.appendChild(input); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            csrfInput = input; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            return input; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Detalhe DevIN: Método togglePasswordVisibility: encapsula uma responsabilidade reutilizável do módulo no contexto de cadastro. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function togglePasswordVisibility(inputId, imgElement) { // Explicação DevIN: Declara função/método togglePasswordVisibility, com seus parâmetros e comportamento.
    // Alterna o campo entre senha oculta e texto visível, atualizando também o ícone.
    const input = document.getElementById(inputId); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.

    if (input.type === 'password') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        input.type = 'text'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        imgElement.src = '../img/olho_aberto.png'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
        input.type = 'password'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        imgElement.src = '../img/olho_fechado.png'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Detalhe DevIN: Método updateRequirement: modifica apenas os dados previstos pela operação no contexto de cadastro. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function updateRequirement(element, isValid) { // Explicação DevIN: Declara função/método updateRequirement, com seus parâmetros e comportamento.
    // Troca classes e ícone juntos para o mesmo resultado aparecer visualmente e no texto do item.
    const icon = element.querySelector('.req-icon'); // Explicação DevIN: Declara variável JavaScript icon para reutilizar valor ou resultado.
    element.classList.toggle('req-valid', isValid); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateRequirement.
    element.classList.toggle('req-invalid', !isValid); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateRequirement.
    icon.textContent = isValid ? '✅' : '⚠️'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Detalhe DevIN: Método checkPasswordMatch: verifica condições antes de liberar o próximo passo no contexto de cadastro. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function checkPasswordMatch() { // Explicação DevIN: Declara função/método checkPasswordMatch, com seus parâmetros e comportamento.
    // Só mostra divergência depois que o usuário começou a preencher a confirmação.
    if (!confirmeSenhaInput.value) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        errorMatch.classList.remove('visible'); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkPasswordMatch.
        return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    errorMatch.classList.toggle('visible', senhaInput.value !== confirmeSenhaInput.value); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkPasswordMatch.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

if (senhaInput && confirmeSenhaInput) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    // Atualiza requisitos em tempo real e impede o envio quando a senha ou confirmação não atendem às regras.
    senhaInput.addEventListener('input', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        const senha = senhaInput.value; // Explicação DevIN: Declara variável JavaScript senha para reutilizar valor ou resultado.
        updateRequirement(reqLength, senha.length >= 8); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkPasswordMatch.
        updateRequirement(reqUpper, /[A-Z]/.test(senha)); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkPasswordMatch.
        updateRequirement(reqSpecial, /[!@#$%^&*(),.?":{}|<>_+\-=\[\]\\/]/.test(senha)); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkPasswordMatch.
        checkPasswordMatch(); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkPasswordMatch.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    confirmeSenhaInput.addEventListener('input', checkPasswordMatch); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.

    cadastroForm.addEventListener('submit', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        const senha = senhaInput.value; // Explicação DevIN: Declara variável JavaScript senha para reutilizar valor ou resultado.
        const senhaValida = senha.length >= 8 // Explicação DevIN: Declara variável JavaScript senhaValida para reutilizar valor ou resultado.
            && /[A-Z]/.test(senha) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            && /[!@#$%^&*(),.?":{}|<>_+\-=\[\]\\/]/.test(senha); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.

        if (!senhaValida || senha !== confirmeSenhaInput.value) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            event.preventDefault(); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkPasswordMatch.
            alert((window.DevINTranslate || ((text) => text))('Por favor, corrija os erros nos campos de senha antes de prosseguir.')); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

cadastroForm?.addEventListener('submit', async (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    if (csrfInput || !csrfRequest) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

    event.preventDefault(); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkPasswordMatch.
    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        await csrfRequest; // Explicação DevIN: Continua expressão ou bloco pertencente a função checkPasswordMatch.
        cadastroForm.requestSubmit(event.submitter); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkPasswordMatch.
    } catch (error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        window.alert((window.DevINTranslate || ((text) => text))('Nao foi possivel preparar o formulario. Atualize a pagina e tente novamente.')); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Detalhe DevIN: Método aplicarMascara: encapsula uma responsabilidade reutilizável do módulo no contexto de cadastro. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function aplicarMascara(id, limite, formatar) { // Explicação DevIN: Declara função/método aplicarMascara, com seus parâmetros e comportamento.
    const campo = document.getElementById(id); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.

    if (!campo) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

    /* A máscara facilita a leitura durante a digitação: remove caracteres não numéricos,
     * limita o tamanho e aplica a apresentação de CPF/CNPJ/telefone/CEP. */
    campo.addEventListener('input', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        const numeros = campo.value.replace(/\D/g, '').slice(0, limite); // Explicação DevIN: Declara variável JavaScript numeros para reutilizar valor ou resultado.
        campo.value = formatar(numeros); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

aplicarMascara('cpf', 11, (valor) => valor // Explicação DevIN: Continua expressão ou bloco pertencente a função aplicarMascara.
    .replace(/(\d{3})(\d)/, '$1.$2') // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    .replace(/(\d{3})(\d)/, '$1.$2') // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    .replace(/(\d{3})(\d{1,2})$/, '$1-$2')); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.

aplicarMascara('cnpj', 14, (valor) => valor // Explicação DevIN: Continua expressão ou bloco pertencente a função aplicarMascara.
    .replace(/^(\d{2})(\d)/, '$1.$2') // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3') // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    .replace(/\.(\d{3})(\d)/, '.$1/$2') // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    .replace(/(\d{4})(\d)/, '$1-$2')); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.

aplicarMascara('telefone', 11, (valor) => { // Explicação DevIN: Continua expressão ou bloco pertencente a função aplicarMascara.
    if (valor.length <= 10) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        return valor // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            .replace(/(\d{2})(\d)/, '($1) $2') // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            .replace(/(\d{4})(\d)/, '$1-$2'); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    return valor // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        .replace(/(\d{2})(\d)/, '($1) $2') // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        .replace(/(\d{5})(\d)/, '$1-$2'); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

aplicarMascara('cep', 8, (valor) => valor.replace(/(\d{5})(\d)/, '$1-$2')); // Explicação DevIN: Continua expressão ou bloco pertencente a função aplicarMascara.

// Evita duplo envio e dá feedback visual durante o processamento do cadastro.
document.querySelectorAll('form').forEach((form) => { // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    // Escuta todos os formulários da página para prevenir cliques repetidos em botões de envio.
    form.addEventListener('submit', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        if (event.defaultPrevented) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        const button = form.querySelector('button[type="submit"]'); // Explicação DevIN: Declara variável JavaScript button para reutilizar valor ou resultado.
        if (!button || button.disabled) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        button.disabled = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        button.dataset.originalText = button.textContent; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        button.textContent = 'Processando...'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
