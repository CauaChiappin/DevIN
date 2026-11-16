if (typeof Lenis !== 'undefined') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    /* Esta página não precisa de GSAP: cria Lenis diretamente quando a biblioteca está disponível.
     * A checagem evita erro em telas onde o script comum é carregado sem a dependência externa. */
    const lenis = new Lenis({ // Explicação DevIN: Declara variável JavaScript lenis para reutilizar valor ou resultado.
        // smoothWheel e smoothTouch ativam interpolação; lerp controla a rapidez com que ela acompanha o movimento.
        smoothWheel: true, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        smoothTouch: true, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        lerp: 0.08 // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    function raf(time) { // Explicação DevIN: Declara função/método raf, com seus parâmetros e comportamento.
        // Lenis precisa receber cada frame; requestAnimationFrame repete a atualização enquanto a página está aberta.
        lenis.raf(time); // Explicação DevIN: Continua expressão ou bloco pertencente a função raf.
        requestAnimationFrame(raf); // Explicação DevIN: Continua expressão ou bloco pertencente a função raf.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    requestAnimationFrame(raf); // Explicação DevIN: Continua expressão ou bloco pertencente a função raf.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
