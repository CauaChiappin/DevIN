gsap.registerPlugin(ScrollTrigger);

const header = document.querySelector('.cabecalho-site');
const lenis = new Lenis({
  smoothWheel: true,
  lerp: 0.08
});

let scrollAnterior = window.scrollY;

function atualizarHeader(scroll, direcao = 0) {
  if (header) {
    header.classList.toggle('rolado', scroll > 40);
    header.classList.toggle('subindo', scroll > 40 && direcao < 0);
    header.classList.toggle('descendo', scroll > 40 && direcao > 0);
  }
}

lenis.on('scroll', ({ scroll }) => {
  const direcao = scroll > scrollAnterior ? 1 : -1;

  atualizarHeader(scroll, direcao);
  scrollAnterior = scroll;
  ScrollTrigger.update();
});

gsap.ticker.add((time) => {
  lenis.raf(time * 1000);
});

gsap.ticker.lagSmoothing(0);
atualizarHeader(window.scrollY);

gsap.fromTo('.texto-principal',
  {
    x: -90,
    opacity: 0
  },
  {
    x: 0,
    opacity: 1,
    duration: 1,
    ease: 'power3.out'
  }
);

gsap.fromTo('.arte-principal',
  {
    x: 90,
    opacity: 0
  },
  {
    x: 0,
    opacity: 1,
    duration: 1,
    ease: 'power3.out',
    delay: 0.18
  }
);

function revelar(selector, entrada, saida) {
  gsap.utils.toArray(selector).forEach((elemento) => {
    gsap.set(elemento, entrada);

    ScrollTrigger.create({
      trigger: elemento,
      start: 'top 82%',
      onEnter: () => {
        gsap.to(elemento, {
          x: 0,
          y: 0,
          opacity: 1,
          duration: 0.9,
          ease: 'power3.out',
          overwrite: true
        });
      },
      onLeaveBack: () => {
        gsap.to(elemento, {
          ...saida,
          duration: 0.55,
          ease: 'power2.in',
          overwrite: true
        });
      }
    });
  });
}

function revelarGrupo(selector, entrada, saida) {
  gsap.utils.toArray(selector).forEach((grupo) => {
    const itens = gsap.utils.toArray(grupo.children);

    gsap.set(itens, entrada);

    ScrollTrigger.create({
      trigger: grupo,
      start: 'top 82%',
      onEnter: () => {
        gsap.to(itens, {
          x: 0,
          y: 0,
          opacity: 1,
          duration: 0.9,
          ease: 'power3.out',
          stagger: 0.12,
          overwrite: true
        });
      },
      onLeaveBack: () => {
        gsap.to(itens, {
          ...saida,
          duration: 0.5,
          ease: 'power2.in',
          stagger: 0.08,
          overwrite: true
        });
      }
    });
  });
}

revelar('.titulo-sobre, .cabecalho-etapas, .empresas h2, .texto-empresas', {
  y: 70,
  opacity: 0
}, {
  y: -60,
  opacity: 0
});

revelar('.linha-curriculo img, .linha-feed div, .formato img, .info-suporte', {
  x: -95,
  opacity: 0
}, {
  x: -80,
  opacity: 0
});

revelar('.linha-curriculo div, .linha-feed img, .formato div, .imagem-suporte', {
  x: 95,
  opacity: 0
}, {
  x: 80,
  opacity: 0
});

revelar('.lista-etapas, .marquee-empresas', {
  y: 70,
  opacity: 0
}, {
  y: -60,
  opacity: 0
});

revelarGrupo('.perguntas', {
  y: 45,
  opacity: 0
}, {
  y: -35,
  opacity: 0
});
