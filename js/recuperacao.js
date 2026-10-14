document.addEventListener('DOMContentLoaded', () => {
    const formRecuperacao = document.getElementById('formRecuperacao');
    const formRedefinir = document.getElementById('formRedefinir');

    formRecuperacao?.addEventListener('submit', (event) => {
        const button = event.currentTarget.querySelector('button[type="submit"]');
        if (!button || button.disabled) {
            event.preventDefault();
            return;
        }

        button.disabled = true;
        button.textContent = 'Enviando...';
    });

    const senha = document.getElementById('nova_senha');
    const confirmar = document.getElementById('confirmar_senha');
    const matchError = document.getElementById('match-error');
    const requisitos = [
        [document.getElementById('req-length'), (value) => value.length >= 8],
        [document.getElementById('req-upper'), (value) => /[A-Z]/.test(value)],
        [document.getElementById('req-special'), (value) => /[^a-zA-Z0-9]/.test(value)],
    ];

    function validarSenha() {
        const value = senha?.value || '';
        requisitos.forEach(([element, validar]) => {
            if (!element) return;
            const valida = validar(value);
            element.classList.toggle('req-valid', valida);
            element.classList.toggle('req-invalid', !valida);
        });

        if (matchError && confirmar) {
            matchError.classList.toggle('visible', confirmar.value !== '' && value !== confirmar.value);
        }
    }

    senha?.addEventListener('input', validarSenha);
    confirmar?.addEventListener('input', validarSenha);

    formRedefinir?.addEventListener('submit', (event) => {
        const value = senha?.value || '';
        const confirmation = confirmar?.value || '';
        const valid = value.length >= 8
            && /[A-Z]/.test(value)
            && /[^a-zA-Z0-9]/.test(value)
            && value === confirmation;

        if (!valid) {
            event.preventDefault();
            validarSenha();
            return;
        }

        const button = formRedefinir.querySelector('button[type="submit"]');
        if (button && !button.disabled) {
            button.disabled = true;
            button.textContent = 'Redefinindo...';
        }
    });

    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const field = document.getElementById(button.getAttribute('data-password-toggle'));
            if (!field) return;

            const visible = field.type === 'password';
            field.type = visible ? 'text' : 'password';
            const image = button.querySelector('img');
            if (image) {
                image.src = visible ? '../img/olho_aberto.png' : '../img/olho_fechado.png';
                image.alt = visible ? 'Ocultar senha' : 'Mostrar senha';
            }
            button.setAttribute('aria-label', visible ? 'Ocultar senha' : 'Mostrar senha');
        });
    });
});
