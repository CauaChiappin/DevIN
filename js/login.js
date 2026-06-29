const campoSenha = document.getElementById('senha');
const btnMostrar = document.getElementById('btn-mostrar');
const imgOlho = document.getElementById('img-olho');

btnMostrar.addEventListener('click', () => {
    if (campoSenha.type === 'password') {
        campoSenha.type = 'text';
        imgOlho.src = '../img/olho_aberto.png'; // Verifique se o nome do arquivo é este mesmo
    } else {
        campoSenha.type = 'password';
        imgOlho.src = '../img/olho_fechado.png';
    }
});

