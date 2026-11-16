(() => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  // Usa o cabeçalho simples como alternativa quando alguma biblioteca de animação não carregou.
  const fallbackHeader = document.querySelector('.cabecalho-site'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
  const initialAnchor = window.location.hash.length > 1 && window.location.hash !== '#contato' // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    ? document.getElementById(window.location.hash.slice(1)) // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    : null; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.

  if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined' || typeof Lenis === 'undefined') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    // Sem as bibliotecas externas, mantém o efeito básico do cabeçalho em vez de falhar toda a página.
    const atualizarHeaderFallback = () => { // Explicação DevIN: Declara variável JavaScript atualizarHeaderFallback para reutilizar valor ou resultado.
      if (fallbackHeader) fallbackHeader.classList.toggle('rolado', window.scrollY > 40); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    atualizarHeaderFallback(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    window.addEventListener('scroll', atualizarHeaderFallback, { passive: true }); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    if (initialAnchor) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      requestAnimationFrame(() => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        window.scrollTo(0, Math.max( // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
          0, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
          window.scrollY + initialAnchor.getBoundingClientRect().top - 100 // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        )); // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
      }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

  // Registra o plugin antes de criar os gatilhos que acompanham a rolagem.
  gsap.registerPlugin(ScrollTrigger); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

const header = document.querySelector('.cabecalho-site'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
const lenis = new Lenis({ // Explicação DevIN: Declara variável JavaScript lenis para reutilizar valor ou resultado.
  smoothWheel: true, // Habilita a suavização do scroll com o mouse
  smoothTouch: true, // Habilita a suavização do scroll com o toque (touch)
  lerp: 0.08 // Ajusta a suavização do scroll (quanto menor, mais suave)
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

let scrollAnterior = window.scrollY; // Armazena a posição de scroll anterior para determinar a direção do scroll

/* Intercepta links internos para usar a rolagem suave e descontar a altura do cabeçalho fixo.
 * O link de contato é exceção porque o comportamento padrão já está ajustado para essa seção. */
document.querySelectorAll('a[href^="#"]').forEach((link) => { // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
  link.addEventListener('click', (evento) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    const id = link.getAttribute('href'); // Explicação DevIN: Declara variável JavaScript id para reutilizar valor ou resultado.
    if (id.length < 2) return; // ignora href="#"
    if (id === '#contato') return; // Contato mantém a rolagem padrão de antes

    const alvo = document.querySelector(id); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    if (!alvo) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

    evento.preventDefault(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    lenis.scrollTo(alvo, { offset: -100, duration: 1.2 }); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

function atualizarHeader(scroll, direcao = 0) { // Explicação DevIN: Declara função/método atualizarHeader, com seus parâmetros e comportamento.
  if (header) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    // As classes permitem ao CSS compactar o cabeçalho e diferenciar a direção da rolagem.
    header.classList.toggle('rolado', scroll > 40); // Ativa o cabeçalho compacto depois dos primeiros 40px.
    header.classList.toggle('subindo', scroll > 40 && direcao < 0); // Adiciona a classe 'subindo' quando o scroll é maior que 40px e a direção é para cima
    header.classList.toggle('descendo', scroll > 40 && direcao > 0); // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

lenis.on('scroll', ({ scroll }) => { // Evento de scroll do Lenis
  const direcao = scroll > scrollAnterior ? 1 : -1; // Determina a direção do scroll (1 para baixo, -1 para cima)

  atualizarHeader(scroll, direcao); // Atualiza o cabeçalho com base na posição e direção do scroll
  scrollAnterior = scroll; // Atualiza a posição de scroll anterior para a próxima verificação
  // Notifica as animações dependentes da posição para que usem o valor atualizado pelo Lenis.
  ScrollTrigger.update(); // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Usa o relógio do GSAP para manter a rolagem e as animações sincronizadas no mesmo ciclo de quadros.
gsap.ticker.add((time) => { // Adiciona uma função ao ticker do GSAP para atualizar o Lenis a cada frame
  lenis.raf(time * 1000); // Converte o tempo de segundos para milissegundos e atualiza o Lenis
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

gsap.ticker.lagSmoothing(0); // Desativa a suavização de lag do ticker do GSAP para evitar atrasos na animação
atualizarHeader(window.scrollY); // Atualiza o cabeçalho com base na posição inicial do scroll ao carregar a página

gsap.fromTo('.titulo-principal', // Animação de entrada do título principal
  { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    x: -90, // Começa 90px à esquerda
    opacity: 0 // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
  }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    x: 0, // Move para a posição original
    opacity: 1, // Torna visível
    duration: 1, // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
    ease: 'power3.out' // Suavização da animação
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

gsap.fromTo('.arte-principal', // Animação de entrada da arte principal
  { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    x: 90, // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
    opacity: 0 // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
  }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    x: 0, // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
    opacity: 1, // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
    duration: 1, // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
    ease: 'power3.out', // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
    delay: 0.18 // Pequeno atraso para criar um efeito de sequência
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

/* Define estado inicial e estados de entrada/saída para cada elemento observado pelo ScrollTrigger.
 * `entrada` também é reutilizado ao voltar a rolar para cima, mantendo a animação reversível. */
function revelar(selector, entrada) { // Função para revelar elementos com animação ao entrar na viewport
  gsap.utils.toArray(selector).forEach((elemento) => { // Itera sobre cada elemento selecionado
    gsap.set(elemento, entrada); // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.

    ScrollTrigger.create({ // Cria um gatilho de scroll para cada elemento
      trigger: elemento, // Define o elemento que acionará a animação
      start: 'top 85%', // Entra um pouquinho mais tarde para dar tempo de ver
      
      // 1. Rolando para BAIXO: O elemento entra lindamente
      onEnter: () => { // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
        gsap.to(elemento, { // Animação de entrada do elemento
          x: 0, // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
          y: 0, // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
          opacity: 1, // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
          duration: 0.9, // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
          ease: 'power3.out', // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
          overwrite: true // Garante que a animação sobrescreva qualquer animação anterior
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      
      // 2. Rolando para CIMA: O elemento volta para o estado inicial mais rápido
      onLeaveBack: () => { // Quando o usuário rola para cima, o elemento volta para o estado inicial
        gsap.to(elemento, { // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
          ...entrada,  // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
          duration: 0.4, // Mais rápido para acompanhar o scroll de subida!
          ease: 'power2.out', // Suavização da animação de saída
          overwrite: true // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

/* Aplica a animação a cada filho do grupo e usa stagger para que os itens apareçam em sequência.
 * Os quatro callbacks tratam entrada e saída tanto ao rolar para baixo quanto para cima. */
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
          x: 0, // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
          y: 0, // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
          opacity: 1, // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
          duration: 0.9, // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
          ease: 'power3.out', // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
          stagger: 0.12, // Adiciona um pequeno atraso entre a animação de cada item para criar um efeito de cascata
          overwrite: true // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      onLeave: () => { // Quando o grupo sai da viewport, anima todos os itens para a posição de saída
        gsap.to(itens, { //  Animação de saída dos itens do grupo
          ...saida, // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
          duration: 0.5, // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
          ease: 'power3.in', // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
          stagger: 0.08, // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
          overwrite: true // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      onEnterBack: () => { // Quando o grupo entra novamente na viewport ao rolar para cima, anima todos os itens para a posição final
        gsap.to(itens, { // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
          x: 0, // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
          y: 0, // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
          opacity: 1, // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
          duration: 0.9, // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
          ease: 'power3.out', // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
          stagger: 0.12, // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
          overwrite: true // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      onLeaveBack: () => { // Quando o grupo sai da viewport ao rolar para cima, anima todos os itens para o estado inicial
        gsap.to(itens, { // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
          ...entrada, // Volta para o estado inicial de ENTRADA com stagger
          duration: 0.6, // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
          ease: 'power3.out', // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
          stagger: 0.08, // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
          overwrite: true // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

revelar('.titulo-sobre, .cabecalho-etapas, .empresas h2, .texto-empresas', { // Animação de entrada para títulos e cabeçalhos
  y: 70, // Começa 70px abaixo da posição final
  opacity: 0 // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
}, { // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
  y: -60, // Quando sai da viewport, move 60px acima da posição final
  opacity: 0 // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

revelar('.linha-curriculo img, .linha-feed div, .formato img, .info-suporte', { // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
  x: -95, // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
  opacity: 0 // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
}, { // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
  x: -80,  // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
  opacity: 0 // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

revelar('.linha-curriculo div, .linha-feed img, .formato div, .imagem-suporte', { // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
  x: 95, // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
  opacity: 0 // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
}, { // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
  x: 80, // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
  opacity: 0 // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

revelar('.lista-etapas, .marquee-empresas, .acao-empresas', { // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
  y: 70, // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
  opacity: 0 // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
}, { // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
  y: -60, // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
  opacity: 0 // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

revelarGrupo('.perguntas', { // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
  y: 45, // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
  opacity: 0 // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
}, { // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
  y: -35, // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
  opacity: 0 // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

if (initialAnchor) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
  requestAnimationFrame(() => { // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
    lenis.scrollTo(initialAnchor, { offset: -100, duration: 0 }); // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
  }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

})(); // Explicação DevIN: Continua expressão ou bloco pertencente a função atualizarHeader.
