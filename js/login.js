document.addEventListener('DOMContentLoaded', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.

    // Aguarda o HTML ser montado antes de procurar elementos; o mesmo script pode ser incluído em outras páginas.

    const campoSenha = // Explicação DevIN: Declara variável JavaScript campoSenha para reutilizar valor ou resultado.
        document.getElementById('senha'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.

    const btnMostrar = // Explicação DevIN: Declara variável JavaScript btnMostrar para reutilizar valor ou resultado.
        document.getElementById('btn-mostrar'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.

    const imgOlho = // Explicação DevIN: Declara variável JavaScript imgOlho para reutilizar valor ou resultado.
        document.getElementById('img-olho'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.

    /*
    |-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    | VERIFICA SE OS ELEMENTOS EXISTEM // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    |-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    */

    if ( // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        !campoSenha || // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        !btnMostrar || // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        !imgOlho // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    ) { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        // Sem os três elementos do formulário de login, não há controle de senha para inicializar.
        return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /*
    |-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    | MOSTRAR / OCULTAR SENHA // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    |-------------------------------------------------------------------------- // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    */

    btnMostrar.addEventListener( // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        'click', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

            // Alterna tipo, imagem e texto alternativo para que o estado visível também seja anunciado por leitores de tela.

            if ( // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                campoSenha.type === 'password' // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            ) { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

                campoSenha.type = 'text'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.

                imgOlho.src = // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    '../img/olho_aberto.png'; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.

                imgOlho.alt = // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    'Ocultar senha'; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.

            } else { // Explicação DevIN: Define ramo alternativo para condição anterior.

                campoSenha.type = // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    'password'; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.

                imgOlho.src = // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    '../img/olho_fechado.png'; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.

                imgOlho.alt = // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    'Mostrar senha'; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

document.querySelector('form[action="login.php"], form[action$="/login.php"]')?.addEventListener('submit', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    // Dá retorno imediato e bloqueia cliques duplicados enquanto a requisição de autenticação está em andamento.
    const button = document.querySelector('.botao-entrar'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    if (!button || button.disabled) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    button.disabled = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    button.textContent = 'Entrando...'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

const loginForm = document.querySelector('form[action="login.php"], form[action$="/login.php"]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
let loginCsrfInput = loginForm?.querySelector('input[name="csrf_token"]'); // Explicação DevIN: Declara variável JavaScript loginCsrfInput para reutilizar valor ou resultado.
let loginCsrfRequest = null; // Explicação DevIN: Declara variável JavaScript loginCsrfRequest para reutilizar valor ou resultado.

if (loginForm && !loginCsrfInput) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    /* Algumas versões da tela são HTML estático e não conseguem imprimir o token PHP.
     * Busca o token da sessão e só então permite que o POST siga para a validação do servidor. */
    loginCsrfRequest = fetch('../php/csrf.php', { credentials: 'same-origin' }) // Explicação DevIN: Inicia solicitação HTTP assíncrona a um endereço configurado.
        .then((response) => { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            if (!response.ok) throw new Error('Falha ao iniciar a sessao.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            return response.json(); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        }) // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        .then((data) => { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            if (!data.token) throw new Error('Token CSRF ausente.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            loginCsrfInput = document.createElement('input'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            loginCsrfInput.type = 'hidden'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            loginCsrfInput.name = 'csrf_token'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            loginCsrfInput.value = data.token; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            loginForm.appendChild(loginCsrfInput); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    loginForm.addEventListener('submit', async (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        // Se o fetch ainda não terminou, pausa o envio; requestSubmit retoma a validação HTML e o botão original.
        if (loginCsrfInput) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        event.preventDefault(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            await loginCsrfRequest; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            loginForm.requestSubmit(event.submitter); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } catch (error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            window.alert((window.DevINTranslate || ((text) => text))('Nao foi possivel preparar o login. Atualize a pagina e tente novamente.')); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
