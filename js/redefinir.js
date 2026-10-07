document.addEventListener('DOMContentLoaded', () => {
    const novaSenha = document.getElementById('nova_senha');
    const reqLen = document.getElementById('req-len');
    const reqUpper = document.getElementById('req-upper');
    const reqSpecial = document.getElementById('req-special');
    const formReset = document.getElementById('formReset');
    const btnCadastrar = document.getElementById('btnCadastrar');

    novaSenha.addEventListener('input', () => {
        const val = novaSenha.value;

        if (val.length >= 8) {
            reqLen.className = 'check-item check-valid';
            reqLen.querySelector('.icon').textContent = '✓';
        } else {
            reqLen.className = 'check-item check-invalid';
            reqLen.querySelector('.icon').textContent = 'ⓘ';
        }

        if (/[A-Z]/.test(val)) {
            reqUpper.className = 'check-item check-valid';
            reqUpper.querySelector('.icon').textContent = '✓';
        } else {
            reqUpper.className = 'check-item check-invalid';
            reqUpper.querySelector('.icon').textContent = 'ⓘ';
        }

        if (/[^a-zA-Z0-9]/.test(val)) {
            reqSpecial.className = 'check-item check-valid';
            reqSpecial.querySelector('.icon').textContent = '✓';
        } else {
            reqSpecial.className = 'check-item check-invalid';
            reqSpecial.querySelector('.icon').textContent = 'ⓘ';
        }
    });

    document.querySelectorAll('[data-toggle]').forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-toggle');
            const input = document.getElementById(targetId);
            if (input) {
                const isPass = input.type === 'password';
                input.type = isPass ? 'text' : 'password';
                btn.classList.toggle('active', isPass);
            }
        });
    });

    if (formReset && btnCadastrar) {
        formReset.addEventListener('submit', (e) => {
            if (btnCadastrar.disabled) {
                e.preventDefault();
                return;
            }
            btnCadastrar.disabled = true;
            btnCadastrar.textContent = 'Cadastrando...';
        });
    }
});
