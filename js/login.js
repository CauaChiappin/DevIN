document.addEventListener('DOMContentLoaded', () => {

    const campoSenha =
        document.getElementById('senha');

    const btnMostrar =
        document.getElementById('btn-mostrar');

    const imgOlho =
        document.getElementById('img-olho');

    /*
    |--------------------------------------------------------------------------
    | VERIFICA SE OS ELEMENTOS EXISTEM
    |--------------------------------------------------------------------------
    */

    if (
        !campoSenha ||
        !btnMostrar ||
        !imgOlho
    ) {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | MOSTRAR / OCULTAR SENHA
    |--------------------------------------------------------------------------
    */

    btnMostrar.addEventListener(
        'click',
        () => {

            if (
                campoSenha.type === 'password'
            ) {

                campoSenha.type = 'text';

                imgOlho.src =
                    '../img/olho_aberto.png';

                imgOlho.alt =
                    'Ocultar senha';

            } else {

                campoSenha.type =
                    'password';

                imgOlho.src =
                    '../img/olho_fechado.png';

                imgOlho.alt =
                    'Mostrar senha';
            }
        }
    );

});