gsap.registerPlugin(ScrollTrigger);

const header = document.querySelector('.cabecalho-site');
const lenis = new Lenis({
  smoothWheel: true, // Habilita a suavização do scroll com o mouse
  smoothTouch: true, // Habilita a suavização do scroll com o toque (touch)
  lerp: 0.08 // Ajusta a suavização do scroll (quanto menor, mais suave)
});

let scrollAnterior = window.scrollY; // Armazena a posição de scroll anterior para determinar a direção do scroll

function atualizarHeader(scroll, direcao = 0) {
  if (header) {
    header.classList.toggle('rolado', scroll > 40); // Adiciona a classe 'rolado' quando o scroll é maior que 40px
    header.classList.toggle('subindo', scroll > 40 && direcao < 0); // Adiciona a classe 'subindo' quando o scroll é maior que 40px e a direção é para cima
    header.classList.toggle('descendo', scroll > 40 && direcao > 0);
  }
}

lenis.on('scroll', ({ scroll }) => { // Evento de scroll do Lenis
  const direcao = scroll > scrollAnterior ? 1 : -1; // Determina a direção do scroll (1 para baixo, -1 para cima)

  atualizarHeader(scroll, direcao); // Atualiza o cabeçalho com base na posição e direção do scroll
  scrollAnterior = scroll; // Atualiza a posição de scroll anterior para a próxima verificação
  ScrollTrigger.update();
});

gsap.ticker.add((time) => { // Adiciona uma função ao ticker do GSAP para atualizar o Lenis a cada frame
  lenis.raf(time * 1000); // Converte o tempo de segundos para milissegundos e atualiza o Lenis
});

gsap.ticker.lagSmoothing(0); // Desativa a suavização de lag do ticker do GSAP para evitar atrasos na animação
atualizarHeader(window.scrollY); // Atualiza o cabeçalho com base na posição inicial do scroll ao carregar a página

gsap.fromTo('.texto-principal', // Animação de entrada do texto principal
  {
    x: -90, // Começa 90px à esquerda
    opacity: 0
  },
  {
    x: 0, // Move para a posição original
    opacity: 1, // Torna visível
    duration: 1,
    ease: 'power3.out' // Suavização da animação
  }
);

gsap.fromTo('.arte-principal', // Animação de entrada da arte principal
  {
    x: 90,
    opacity: 0
  },
  {
    x: 0,
    opacity: 1,
    duration: 1,
    ease: 'power3.out',
    delay: 0.18 // Pequeno atraso para criar um efeito de sequência
  }
);

function revelar(selector, entrada) { // Função para revelar elementos com animação ao entrar na viewport
  gsap.utils.toArray(selector).forEach((elemento) => { // Itera sobre cada elemento selecionado
    gsap.set(elemento, entrada);

    ScrollTrigger.create({ // Cria um gatilho de scroll para cada elemento
      trigger: elemento, // Define o elemento que acionará a animação
      start: 'top 85%', // Entra um pouquinho mais tarde para dar tempo de ver
      
      // 1. Rolando para BAIXO: O elemento entra lindamente
      onEnter: () => {
        gsap.to(elemento, { // Animação de entrada do elemento
          x: 0,
          y: 0,
          opacity: 1,
          duration: 0.9,
          ease: 'power3.out',
          overwrite: true // Garante que a animação sobrescreva qualquer animação anterior
        });
      },
      
      // 2. Rolando para CIMA: O elemento volta para o estado inicial mais rápido
      onLeaveBack: () => { // Quando o usuário rola para cima, o elemento volta para o estado inicial
        gsap.to(elemento, {
          ...entrada, 
          duration: 0.4, // Mais rápido para acompanhar o scroll de subida!
          ease: 'power2.out', // Suavização da animação de saída
          overwrite: true
        });
      }
    });
  });
}

function revelarGrupo(selector, entrada, saida) { // Função para revelar grupos de elementos com animação ao entrar na viewport
  gsap.utils.toArray(selector).forEach((grupo) => { // Itera sobre cada grupo selecionado
    const itens = gsap.utils.toArray(grupo.children); // Seleciona todos os filhos do grupo para animar em conjunto

    gsap.set(itens, entrada); // Define o estado inicial de entrada para todos os itens do grupo

    ScrollTrigger.create({ // Cria um gatilho de scroll para o grupo
      trigger: grupo, // Define o grupo como o elemento que acionará a animação
      start: 'top 82%', // Inicia a animação quando o topo do grupo atinge 82% da altura da viewport
      end: 'bottom 18%', // Finaliza a animação quando o fundo do grupo atinge 18% da altura da viewport
      
      onEnter: () => { // Quando o grupo entra na viewport, anima todos os itens para a posição final
        gsap.to(itens, { // Animação de entrada dos itens do grupo
          x: 0,
          y: 0,
          opacity: 1,
          duration: 0.9,
          ease: 'power3.out',
          stagger: 0.12, // Adiciona um pequeno atraso entre a animação de cada item para criar um efeito de cascata
          overwrite: true
        });
      },
      onLeave: () => { // Quando o grupo sai da viewport, anima todos os itens para a posição de saída
        gsap.to(itens, { //  Animação de saída dos itens do grupo
          ...saida,
          duration: 0.5,
          ease: 'power3.in',
          stagger: 0.08,
          overwrite: true
        });
      },
      onEnterBack: () => { // Quando o grupo entra novamente na viewport ao rolar para cima, anima todos os itens para a posição final
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
      onLeaveBack: () => { // Quando o grupo sai da viewport ao rolar para cima, anima todos os itens para o estado inicial
        gsap.to(itens, {
          ...entrada, // Volta para o estado inicial de ENTRADA com stagger
          duration: 0.6,
          ease: 'power3.out',
          stagger: 0.08,
          overwrite: true
        });
      }
    });
  });
}

revelar('.titulo-sobre, .cabecalho-etapas, .empresas h2, .texto-empresas', { // Animação de entrada para títulos e cabeçalhos
  y: 70, // Começa 70px abaixo da posição final
  opacity: 0
}, {
  y: -60, // Quando sai da viewport, move 60px acima da posição final
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
