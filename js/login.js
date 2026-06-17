// Seleciona os elementos do HTML
const campoSenha = document.getElementById('senha');
const botaoMostrar = document.getElementById('btn-mostrar');

// Adiciona o evento de clique no botão
botaoMostrar.addEventListener('click', () => {
    if (campoSenha.type === 'password') {
        campoSenha.type = 'text';
        botaoMostrar.textContent = 'Esconder';
    } else {
        campoSenha.type = 'password';
        botaoMostrar.textContent = 'Mostrar';
    }
});