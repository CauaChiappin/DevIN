document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        const input = document.getElementById(button.dataset.passwordToggle);
        const image = button.querySelector('img');
        if (!input || !image) return;

        button.addEventListener('click', () => {
            const visible = input.type === 'password';
            input.type = visible ? 'text' : 'password';
            image.src = visible ? '../img/olho_aberto.png' : '../img/olho_fechado.png';
            image.alt = visible ? 'Ocultar senha' : 'Mostrar senha';
            button.setAttribute('aria-label', image.alt);
        });
    });

    const senha = document.getElementById('nova_senha');
    const confirmar = document.getElementById('confirmar_senha');
    const form = document.getElementById('formRedefinir');
    const matchError = document.getElementById('match-error');

    const requirements = [
        [document.getElementById('req-length'), value => value.length >= 8],
        [document.getElementById('req-upper'), value => /[A-Z]/.test(value)],
        [document.getElementById('req-special'), value => /[^a-zA-Z0-9]/.test(value)],
    ];

    function updateRequirement(element, valid) {
        if (!element) return;
        const icon = element.querySelector('.req-icon');
        element.classList.toggle('req-valid', valid);
        element.classList.toggle('req-invalid', !valid);
        if (icon) icon.textContent = valid ? '✓' : 'ⓘ';
    }

    function validate() {
        const value = senha?.value || '';
        requirements.forEach(([element, test]) => updateRequirement(element, test(value)));
        if (matchError && confirmar) {
            const mismatch = confirmar.value !== '' && value !== confirmar.value;
            matchError.classList.toggle('visible', mismatch);
        }
    }

    senha?.addEventListener('input', validate);
    confirmar?.addEventListener('input', validate);

    form?.addEventListener('submit', (event) => {
        const value = senha?.value || '';
        const confirmation = confirmar?.value || '';
        const valid = value.length >= 8 && /[A-Z]/.test(value) && /[^a-zA-Z0-9]/.test(value) && value === confirmation;
        if (!valid) {
            event.preventDefault();
            validate();
            return;
        }
        const button = form.querySelector('button[type="submit"]');
        if (button) {
            button.disabled = true;
            button.textContent = 'Cadastrando...';
        }
    });

    document.getElementById('formRecuperacao')?.addEventListener('submit', (event) => {
        const button = event.currentTarget.querySelector('button[type="submit"]');
        if (!button || button.disabled) return;
        button.disabled = true;
        button.textContent = 'Enviando...';
    });

    validate();
});
