document.addEventListener('DOMContentLoaded', () => {
    // --- Prevenção de múltiplos cliques no envio do e-mail ---
    const formRecuperacao = document.getElementById('formRecuperacao');
    const btnEnviar = document.getElementById('btnEnviar');

    if (formRecuperacao && btnEnviar) {
        formRecuperacao.addEventListener('submit', (e) => {
            if (btnEnviar.disabled) {
                e.preventDefault();
                return;
            }
            btnEnviar.disabled = true;
            btnEnviar.textContent = 'Enviando...';
        });
    }

    // --- Tratamentos de formulários e senhas (se presentes na página) ---
    const inputCodigo = document.getElementById('codigo');
    const inputNovaSenha = document.getElementById('nova_senha');
    const inputConfSenha = document.getElementById('confirmar_senha');
    const matchError = document.getElementById('match-error');

    const requirements = [
        [document.getElementById('req-length'), value => value.length >= 12],
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

    // Validação interativa de requisitos da palavra-passe
    if (inputNovaSenha) {
        const reqLength = document.getElementById('req-length');
        const reqUpper = document.getElementById('req-upper');
        const reqSpecial = document.getElementById('req-special');

        inputNovaSenha.addEventListener('input', () => {
            const val = inputNovaSenha.value;

            if (reqLength) {
                if (val.length >= 8) {
                    reqLength.classList.remove('req-invalid');
                    reqLength.classList.add('req-valid');
                } else {
                    reqLength.classList.remove('req-valid');
                    reqLength.classList.add('req-invalid');
                }
            }

            if (reqUpper) {
                if (/[A-Z]/.test(val)) {
                    reqUpper.classList.remove('req-invalid');
                    reqUpper.classList.add('req-valid');
                } else {
                    reqUpper.classList.remove('req-valid');
                    reqUpper.classList.add('req-invalid');
                }
            }

            if (reqSpecial) {
                if (/[^a-zA-Z0-9]/.test(val)) {
                    reqSpecial.classList.remove('req-invalid');
                    reqSpecial.classList.add('req-valid');
                } else {
                    reqSpecial.classList.remove('req-valid');
                    reqSpecial.classList.add('req-invalid');
                }
            }

            verificarCoincidencia();
        });
    }

    if (inputConfSenha) {
        inputConfSenha.addEventListener('input', verificarCoincidencia);
    }

    function verificarCoincidencia() {
        if (!inputConfSenha || !inputNovaSenha || !matchError) return;

        if (inputConfSenha.value.length > 0 && inputNovaSenha.value !== inputConfSenha.value) {
            matchError.classList.add('visible');
        } else {
            matchError.classList.remove('visible');
        }
    }

    // Alternar visibilidade da palavra-passe
    document.querySelectorAll('[data-password-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const fieldId = btn.getAttribute('data-password-toggle');
            const field = document.getElementById(fieldId);
            if (!field) return;

<<<<<<< HEAD
    form?.addEventListener('submit', (event) => {
        const value = senha?.value || '';
        const confirmation = confirmar?.value || '';
        const valid = value.length >= 12 && /[A-Z]/.test(value) && /[^a-zA-Z0-9]/.test(value) && value === confirmation;
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
=======
            const isPassword = field.type === 'password';
            field.type = isPassword ? 'text' : 'password';

            const img = btn.querySelector('img');
            if (img) {
                img.src = isPassword ? '../img/olho_aberto.png' : '../img/olho_fechado.png';
            }
        });
>>>>>>> bb0413abcd2a8c17f9c53b600f5a5acb10a41c97
    });
});