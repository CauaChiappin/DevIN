if (typeof Lenis !== 'undefined') {
    /* Esta página não precisa de GSAP: cria Lenis diretamente quando a biblioteca está disponível.
     * A checagem evita erro em telas onde o script comum é carregado sem a dependência externa. */
    const lenis = new Lenis({
        // smoothWheel e smoothTouch ativam interpolação; lerp controla a rapidez com que ela acompanha o movimento.
        smoothWheel: true,
        smoothTouch: true,
        lerp: 0.08
    });

    function raf(time) {
        // Lenis precisa receber cada frame; requestAnimationFrame repete a atualização enquanto a página está aberta.
        lenis.raf(time);
        requestAnimationFrame(raf);
    }

    requestAnimationFrame(raf);
}
