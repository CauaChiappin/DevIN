document.addEventListener('DOMContentLoaded', () => {

    // Aguarda o HTML ser montado antes de procurar elementos; o mesmo script pode ser incluído em outras páginas.

    const campoSenha =
        document.getElementById('senha');

    const btnMostrar =
        document.getElementById('btn-mostrar');

    const imgOlho =
        document.getElementById('img-olho');

    /*
    |--------------------------------------------------------------------------
    | VERIFICA SE OS ELEMENTOS EXISTEM
    |--------------------------------------------------------------------------
    */

    if (
        !campoSenha ||
        !btnMostrar ||
        !imgOlho
    ) {
        // Sem os três elementos do formulário de login, não há controle de senha para inicializar.
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | MOSTRAR / OCULTAR SENHA
    |--------------------------------------------------------------------------
    */

    btnMostrar.addEventListener(
        'click',
        () => {

            // Alterna tipo, imagem e texto alternativo para que o estado visível também seja anunciado por leitores de tela.

            if (
                campoSenha.type === 'password'
            ) {

                campoSenha.type = 'text';

                imgOlho.src =
                    '../img/olho_aberto.png';

                imgOlho.alt =
                    'Ocultar senha';

            } else {

                campoSenha.type =
                    'password';

                imgOlho.src =
                    '../img/olho_fechado.png';

                imgOlho.alt =
                    'Mostrar senha';
            }
        }
    );

});

document.querySelector('form[action="login.php"], form[action$="/login.php"]')?.addEventListener('submit', () => {
    // Dá retorno imediato e bloqueia cliques duplicados enquanto a requisição de autenticação está em andamento.
    const button = document.querySelector('.botao-entrar');
    if (!button || button.disabled) return;
    button.disabled = true;
    button.textContent = 'Entrando...';
});

const loginForm = document.querySelector('form[action="login.php"], form[action$="/login.php"]');
let loginCsrfInput = loginForm?.querySelector('input[name="csrf_token"]');
let loginCsrfRequest = null;

if (loginForm && !loginCsrfInput) {
    /* Algumas versões da tela são HTML estático e não conseguem imprimir o token PHP.
     * Busca o token da sessão e só então permite que o POST siga para a validação do servidor. */
    loginCsrfRequest = fetch('../php/csrf.php', { credentials: 'same-origin' })
        .then((response) => {
            if (!response.ok) throw new Error('Falha ao iniciar a sessao.');
            return response.json();
        })
        .then((data) => {
            if (!data.token) throw new Error('Token CSRF ausente.');
            loginCsrfInput = document.createElement('input');
            loginCsrfInput.type = 'hidden';
            loginCsrfInput.name = 'csrf_token';
            loginCsrfInput.value = data.token;
            loginForm.appendChild(loginCsrfInput);
        });

    loginForm.addEventListener('submit', async (event) => {
        // Se o fetch ainda não terminou, pausa o envio; requestSubmit retoma a validação HTML e o botão original.
        if (loginCsrfInput) return;
        event.preventDefault();
        try {
            await loginCsrfRequest;
            loginForm.requestSubmit(event.submitter);
        } catch (error) {
            window.alert((window.DevINTranslate || ((text) => text))('Nao foi possivel preparar o login. Atualize a pagina e tente novamente.'));
        }
    });
}
