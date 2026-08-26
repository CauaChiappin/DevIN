if (typeof Lenis !== 'undefined') {
    const lenis = new Lenis({
        smoothWheel: true,
        smoothTouch: true,
        lerp: 0.08
    });

    function raf(time) {
        lenis.raf(time);
        requestAnimationFrame(raf);
    }

    requestAnimationFrame(raf);
}
