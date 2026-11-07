const senhaInput = document.getElementById('senha');
const confirmeSenhaInput = document.getElementById('confirme_senha');
const reqLength = document.getElementById('req-length');
const reqUpper = document.getElementById('req-upper');
const reqSpecial = document.getElementById('req-special');
const errorMatch = document.getElementById('error-match');
const cadastroForm = document.getElementById('formCadastro');
// Reutiliza o token no formulário; se a página não o trouxe, solicita um à sessão atual.
let csrfInput = cadastroForm?.querySelector('input[name="csrf_token"]');

let csrfRequest = null;
if (cadastroForm && !csrfInput) {
    /* O formulário estático não consegue renderizar csrfToken() no PHP. O token é buscado
     * com as credenciais da mesma origem e inserido antes de retomar o envio interrompido. */
    csrfRequest = fetch('../php/csrf.php', { credentials: 'same-origin' })
        .then((response) => {
            if (!response.ok) throw new Error('Falha ao iniciar a sessao.');
            return response.json();
        })
        .then((data) => {
            if (!data.token) throw new Error('Token CSRF ausente.');
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'csrf_token';
            input.value = data.token;
            cadastroForm.appendChild(input);
            csrfInput = input;
            return input;
        });
}

function togglePasswordVisibility(inputId, imgElement) {
    // Alterna o campo entre senha oculta e texto visível, atualizando também o ícone.
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
    // Troca classes e ícone juntos para o mesmo resultado aparecer visualmente e no texto do item.
    const icon = element.querySelector('.req-icon');
    element.classList.toggle('req-valid', isValid);
    element.classList.toggle('req-invalid', !isValid);
    icon.textContent = isValid ? '✅' : '⚠️';
}

function checkPasswordMatch() {
    // Só mostra divergência depois que o usuário começou a preencher a confirmação.
    if (!confirmeSenhaInput.value) {
        errorMatch.classList.remove('visible');
        return;
    }

    errorMatch.classList.toggle('visible', senhaInput.value !== confirmeSenhaInput.value);
}

if (senhaInput && confirmeSenhaInput) {
    // Atualiza requisitos em tempo real e impede o envio quando a senha ou confirmação não atendem às regras.
    senhaInput.addEventListener('input', () => {
        const senha = senhaInput.value;
        updateRequirement(reqLength, senha.length >= 8);
        updateRequirement(reqUpper, /[A-Z]/.test(senha));
        updateRequirement(reqSpecial, /[!@#$%^&*(),.?":{}|<>_+\-=\[\]\\/]/.test(senha));
        checkPasswordMatch();
    });

    confirmeSenhaInput.addEventListener('input', checkPasswordMatch);

    cadastroForm.addEventListener('submit', (event) => {
        const senha = senhaInput.value;
        const senhaValida = senha.length >= 8
            && /[A-Z]/.test(senha)
            && /[!@#$%^&*(),.?":{}|<>_+\-=\[\]\\/]/.test(senha);

        if (!senhaValida || senha !== confirmeSenhaInput.value) {
            event.preventDefault();
            alert((window.DevINTranslate || ((text) => text))('Por favor, corrija os erros nos campos de senha antes de prosseguir.'));
        }
    });
}

cadastroForm?.addEventListener('submit', async (event) => {
    if (csrfInput || !csrfRequest) return;

    event.preventDefault();
    try {
        await csrfRequest;
        cadastroForm.requestSubmit(event.submitter);
    } catch (error) {
        window.alert((window.DevINTranslate || ((text) => text))('Nao foi possivel preparar o formulario. Atualize a pagina e tente novamente.'));
    }
});

function aplicarMascara(id, limite, formatar) {
    const campo = document.getElementById(id);

    if (!campo) return;

    /* A máscara facilita a leitura durante a digitação: remove caracteres não numéricos,
     * limita o tamanho e aplica a apresentação de CPF/CNPJ/telefone/CEP. */
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
    // Escuta todos os formulários da página para prevenir cliques repetidos em botões de envio.
    form.addEventListener('submit', (event) => {
        if (event.defaultPrevented) return;
        const button = form.querySelector('button[type="submit"]');
        if (!button || button.disabled) return;
        button.disabled = true;
        button.dataset.originalText = button.textContent;
        button.textContent = 'Processando...';
    });
});
