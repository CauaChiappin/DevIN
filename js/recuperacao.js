document.addEventListener('DOMContentLoaded', () => {
    const inputCodigo = document.getElementById('codigo');
    const inputNovaSenha = document.getElementById('nova_senha');
    const inputConfSenha = document.getElementById('confirmar_senha');
    const matchError = document.getElementById('match-error');

    // Restringir campo de código apenas para números
    if (inputCodigo) {
        inputCodigo.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/[^0-9]/g, '').slice(0, 8);
        });
    }

    // Validação interativa de requisitos da palavra-passe
    if (inputNovaSenha) {
        const reqLength = document.getElementById('req-length');
        const reqUpper = document.getElementById('req-upper');
        const reqSpecial = document.getElementById('req-special');

        inputNovaSenha.addEventListener('input', () => {
            const val = inputNovaSenha.value;

            // Mínimo 8 caracteres
            if (val.length >= 8) {
                reqLength.classList.remove('req-invalid');
                reqLength.classList.add('req-valid');
            } else {
                reqLength.classList.remove('req-valid');
                reqLength.classList.add('req-invalid');
            }

            // Letra Maiúscula
            if (/[A-Z]/.test(val)) {
                reqUpper.classList.remove('req-invalid');
                reqUpper.classList.add('req-valid');
            } else {
                reqUpper.classList.remove('req-valid');
                reqUpper.classList.add('req-invalid');
            }

            // Caráter Especial
            if (/[^a-zA-Z0-9]/.test(val)) {
                reqSpecial.classList.remove('req-invalid');
                reqSpecial.classList.add('req-valid');
            } else {
                reqSpecial.classList.remove('req-valid');
                reqSpecial.classList.add('req-invalid');
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

            const isPassword = field.type === 'password';
            field.type = isPassword ? 'text' : 'password';

            const img = btn.querySelector('img');
            if (img) {
                img.src = isPassword ? '../img/olho_aberto.png' : '../img/olho_fechado.png';
            }
        });
    });
});