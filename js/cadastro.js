console.log("cadastro.js carregou");
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
        imgElement.src = '../img/olho_fechado.png';
    } else {
        input.type = 'password';
        imgElement.src = '../img/olho_aberto.png';
    }
}

function updateRequirement(element, isValid) {
    const icon = element.querySelector('.req-icon');
    if (isValid) {
        element.classList.remove('req-invalid');
        element.classList.add('req-valid');
        icon.textContent = '✅';
    } else {
        element.classList.remove('req-valid');
        element.classList.add('req-invalid');
        icon.textContent = '⚠️';
    }
}

senhaInput.addEventListener('input', () => {
    const val = senhaInput.value;
    updateRequirement(reqLength, val.length >= 8);
    updateRequirement(reqUpper, /[A-Z]/.test(val));
    updateRequirement(reqSpecial, /[!@#$%^&*(),.?":{}|<>_+\-=\[\]\\\/]/.test(val));
    checkPasswordMatch();
});

function checkPasswordMatch() {
    if (confirmeSenhaInput.value === '') {
        errorMatch.classList.remove('visible');
        return;
    }
    if (senhaInput.value !== confirmeSenhaInput.value) {
        errorMatch.classList.add('visible');
    } else {
        errorMatch.classList.remove('visible');
    }
}

confirmeSenhaInput.addEventListener('input', checkPasswordMatch);

document.getElementById('formCadastro').addEventListener('submit', function (e) {
    const val = senhaInput.value;
    const isAllValid = (val.length >= 8) && /[A-Z]/.test(val) && /[!@#$%^&*(),.?":{}|<>_+\-=\[\]\\\/]/.test(val);
    const isMatch = senhaInput.value === confirmeSenhaInput.value;

    if (!isAllValid || !isMatch) {
        e.preventDefault();
        alert('Por favor, corrija os erros nos campos de senha antes de prosseguir.');
    }
});

// =====================
// MÁSCARA CPF
// =====================
const cpf = document.getElementById('cpf');

cpf.addEventListener('input', () => {
    let valor = cpf.value.replace(/\D/g, '');

    valor = valor.substring(0, 11);

    valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
    valor = valor.replace(/(\d{3})(\d)/, '$1.$2');
    valor = valor.replace(/(\d{3})(\d{1,2})$/, '$1-$2');

    cpf.value = valor;
});


// =====================
// MÁSCARA TELEFONE
// =====================
const telefone = document.getElementById('telefone');

telefone.addEventListener('input', () => {
    let valor = telefone.value.replace(/\D/g, '');

    valor = valor.substring(0, 11);

    if (valor.length <= 10) {
        valor = valor.replace(/(\d{2})(\d)/, '($1) $2');
        valor = valor.replace(/(\d{4})(\d)/, '$1-$2');
    } else {
        valor = valor.replace(/(\d{2})(\d)/, '($1) $2');
        valor = valor.replace(/(\d{5})(\d)/, '$1-$2');
    }

    telefone.value = valor;
});


// =====================
// MÁSCARA CEP
// =====================
const cep = document.getElementById('cep');

cep.addEventListener('input', () => {
    let valor = cep.value.replace(/\D/g, '');

    valor = valor.substring(0, 8);

    valor = valor.replace(/(\d{5})(\d)/, '$1-$2');

    cep.value = valor;
});