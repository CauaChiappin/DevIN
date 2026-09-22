const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const test = require('node:test');
const vm = require('node:vm');

function carregarPagina() {
  const classes = new Set();
  const header = {
    classList: {
      add: (classe) => classes.add(classe),
      contains: (classe) => classes.has(classe),
      remove: (classe) => classes.delete(classe),
      toggle: (classe, ativo) => {
        if (ativo) classes.add(classe);
        else classes.delete(classe);
      }
    }
  };

  class LenisFalso {
    constructor() {
      this.eventos = {};
      LenisFalso.instancia = this;
    }

    on(evento, callback) {
      this.eventos[evento] = callback;
    }

    emitirScroll(scroll) {
      this.eventos.scroll({ scroll });
    }

    raf() {}
  }

  const contexto = {
    Lenis: LenisFalso,
    ScrollTrigger: { update: () => {} },
    document: {
      querySelector: (seletor) => (seletor === '.cabecalho-site' ? header : null)
    },
    gsap: {
      fromTo: () => {},
      registerPlugin: () => {},
      set: () => {},
      ticker: { add: () => {}, lagSmoothing: () => {} },
      to: () => {},
      utils: { toArray: () => [] }
    },
    setTimeout,
    clearTimeout,
    window: { scrollY: 0 }
  };

  const script = fs.readFileSync(path.join(__dirname, '..', 'js', 'lenis.js'), 'utf8');
  vm.runInNewContext(script, contexto);

  return { header, lenis: LenisFalso.instancia };
}

test('mostra o header como subindo ao parar depois de descer', async () => {
  const { header, lenis } = carregarPagina();

  lenis.emitirScroll(120);
  assert.equal(header.classList.contains('descendo'), true);

  await new Promise((resolve) => setTimeout(resolve, 250));

  assert.equal(header.classList.contains('subindo'), true);
  assert.equal(header.classList.contains('descendo'), false);
});
