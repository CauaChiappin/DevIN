const senhaInput = document.getElementById('senha');
const confirmeSenhaInput = document.getElementById('confirme_senha');
const reqLength = document.getElementById('req-length');
const reqUpper = document.getElementById('req-upper');
const reqSpecial = document.getElementById('req-special');
const errorMatch = document.getElementById('error-match');

function togglePasswordVisibility(inputId, imgElement) {
    const input = document.getElementById(inputId);

    if (input.type === 'password') {
        input.type = 'text';
        imgElement.src = '../img/olho_aberto.png';
    } else {
        input.type = 'password';
        imgElement.src = '../img/olho_fechado.png';
    }
}

function updateRequirement(element, isValid) {
    const icon = element.querySelector('.req-icon');
    element.classList.toggle('req-valid', isValid);
    element.classList.toggle('req-invalid', !isValid);
    icon.textContent = isValid ? '✅' : '⚠️';
}

function checkPasswordMatch() {
    if (!confirmeSenhaInput.value) {
        errorMatch.classList.remove('visible');
        return;
    }

    errorMatch.classList.toggle('visible', senhaInput.value !== confirmeSenhaInput.value);
}

if (senhaInput && confirmeSenhaInput) {
    senhaInput.addEventListener('input', () => {
        const senha = senhaInput.value;
        updateRequirement(reqLength, senha.length >= 8);
        updateRequirement(reqUpper, /[A-Z]/.test(senha));
        updateRequirement(reqSpecial, /[!@#$%^&*(),.?":{}|<>_+\-=\[\]\\/]/.test(senha));
        checkPasswordMatch();
    });

    confirmeSenhaInput.addEventListener('input', checkPasswordMatch);

    document.getElementById('formCadastro').addEventListener('submit', (event) => {
        const senha = senhaInput.value;
        const senhaValida = senha.length >= 8
            && /[A-Z]/.test(senha)
            && /[!@#$%^&*(),.?":{}|<>_+\-=\[\]\\/]/.test(senha);

        if (!senhaValida || senha !== confirmeSenhaInput.value) {
            event.preventDefault();
            alert('Por favor, corrija os erros nos campos de senha antes de prosseguir.');
        }
    });
}

function aplicarMascara(id, limite, formatar) {
    const campo = document.getElementById(id);

    if (!campo) return;

    campo.addEventListener('input', () => {
        const numeros = campo.value.replace(/\D/g, '').slice(0, limite);
        campo.value = formatar(numeros);
    });
}

aplicarMascara('cpf', 11, (valor) => valor
    .replace(/(\d{3})(\d)/, '$1.$2')
    .replace(/(\d{3})(\d)/, '$1.$2')
    .replace(/(\d{3})(\d{1,2})$/, '$1-$2'));

aplicarMascara('cnpj', 14, (valor) => valor
    .replace(/^(\d{2})(\d)/, '$1.$2')
    .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
    .replace(/\.(\d{3})(\d)/, '.$1/$2')
    .replace(/(\d{4})(\d)/, '$1-$2'));

aplicarMascara('telefone', 11, (valor) => {
    if (valor.length <= 10) {
        return valor
            .replace(/(\d{2})(\d)/, '($1) $2')
            .replace(/(\d{4})(\d)/, '$1-$2');
    }

    return valor
        .replace(/(\d{2})(\d)/, '($1) $2')
        .replace(/(\d{5})(\d)/, '$1-$2');
});

aplicarMascara('cep', 8, (valor) => valor.replace(/(\d{5})(\d)/, '$1-$2'));

// Evita duplo envio e dá feedback visual durante o processamento do cadastro.
document.querySelectorAll('form').forEach((form) => {
    form.addEventListener('submit', () => {
        const button = form.querySelector('button[type="submit"]');
        if (!button || button.disabled) return;
        button.disabled = true;
        button.dataset.originalText = button.textContent;
        button.textContent = 'Processando...';
    });
});
