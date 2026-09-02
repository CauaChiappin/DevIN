document.addEventListener('DOMContentLoaded', () => {

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
    const button = document.querySelector('.botao-entrar');
    if (!button || button.disabled) return;
    button.disabled = true;
    button.textContent = 'Entrando...';
});

const loginForm = document.querySelector('form[action="login.php"], form[action$="/login.php"]');
let loginCsrfInput = loginForm?.querySelector('input[name="csrf_token"]');
let loginCsrfRequest = null;

if (loginForm && !loginCsrfInput) {
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
        if (loginCsrfInput) return;
        event.preventDefault();
        try {
            await loginCsrfRequest;
            loginForm.requestSubmit(event.submitter);
        } catch (error) {
            window.alert('Nao foi possivel preparar o login. Atualize a pagina e tente novamente.');
        }
    });
}
