/**
 * Testes do atalho secreto para dispositivos touch dentro de "Sobre nós".
 * O navegador é simulado para provar que mouse, arrasto ou toque curto não
 * abrem o Arcade, enquanto pressão de três segundos mantém o Konami desktop.
 */
const test = require('node:test'); // Explicação DevIN: Declara variável JavaScript test para reutilizar valor ou resultado.
const assert = require('node:assert/strict'); // Explicação DevIN: Declara variável JavaScript assert para reutilizar valor ou resultado.
const fs = require('node:fs'); // Explicação DevIN: Declara variável JavaScript fs para reutilizar valor ou resultado.
const path = require('node:path'); // Explicação DevIN: Declara variável JavaScript path para reutilizar valor ou resultado.
const vm = require('node:vm'); // Explicação DevIN: Declara variável JavaScript vm para reutilizar valor ou resultado.
const root = path.resolve(__dirname, '..'); // Explicação DevIN: Declara variável JavaScript root para reutilizar valor ou resultado.
const read = file => fs.readFileSync(path.join(root, file), 'utf8'); // Explicação DevIN: Declara variável JavaScript read para reutilizar valor ou resultado.

function simulate(touch = true) { // Explicação DevIN: Declara função/método simulate, com seus parâmetros e comportamento.
    const handlers = {}; // Explicação DevIN: Declara variável JavaScript handlers para reutilizar valor ou resultado.
    const timers = new Map(); // Explicação DevIN: Declara variável JavaScript timers para reutilizar valor ou resultado.
    const classes = new Set(); // Explicação DevIN: Declara variável JavaScript classes para reutilizar valor ou resultado.
    const redirects = []; // Explicação DevIN: Declara variável JavaScript redirects para reutilizar valor ou resultado.
    let nextTimer = 1; // Explicação DevIN: Declara variável JavaScript nextTimer para reutilizar valor ou resultado.
    const button = { // Explicação DevIN: Declara variável JavaScript button para reutilizar valor ou resultado.
        addEventListener(name, callback) { handlers[name] = callback; }, // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
        classList: { add(name) { classes.add(name); }, remove(name) { classes.delete(name); } }, // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
        setAttribute() {}, // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    const documentListeners = {}; // Explicação DevIN: Declara variável JavaScript documentListeners para reutilizar valor ou resultado.
    const windowListeners = {}; // Explicação DevIN: Declara variável JavaScript windowListeners para reutilizar valor ou resultado.
    const context = { // Explicação DevIN: Declara variável JavaScript context para reutilizar valor ou resultado.
        document: { // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
            querySelector: selector => selector === '[data-mobile-arcade-secret]' ? button : null, // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
            addEventListener: (name, callback) => { documentListeners[name] = callback; }, // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
            hidden: false, // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
        }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        window: { // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
            matchMedia: () => ({ matches: touch }), // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
            setTimeout: (fn, ms) => { const id = nextTimer++; timers.set(id, { fn, ms }); return id; }, // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
            clearTimeout: id => timers.delete(id), // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
            addEventListener: (name, callback) => { windowListeners[name] = callback; }, // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
            location: { href: 'https://example.org/Trabalhos/3D/DevIN/php/pessoa.php?pagina=sobre', assign: url => redirects.push(url) }, // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
        }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        navigator: { vibrate() {} }, // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
        URL, // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
        Math, // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    vm.runInNewContext(read('js/mobile-arcade-secret.js'), context); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    return { // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        handlers, timers, classes, redirects, documentListeners, windowListeners, context, // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
        advance(ms) { // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
            const hit = [...timers.entries()].find(([, value]) => value.ms === ms); // Explicação DevIN: Declara variável JavaScript hit para reutilizar valor ou resultado.
            assert.ok(hit, 'temporizador esperado: ' + ms); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
            timers.delete(hit[0]); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
            hit[1].fn(); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
        }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

const down = (pointerType = 'touch') => ({ pointerId: 9, pointerType, button: 0, clientX: 100, clientY: 100 }); // Explicação DevIN: Declara variável JavaScript down para reutilizar valor ou resultado.

test('o segredo fica no card Time fundador e carrega nos três painéis', () => { // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    const about = read('php/helpers.php'); // Explicação DevIN: Declara variável JavaScript about para reutilizar valor ou resultado.
    assert.match(about, /sobre-team-card[\s\S]*data-mobile-arcade-secret[\s\S]*<\/section>/); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    for (const page of ['pessoa', 'empresa', 'adm']) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
        const source = read('php/' + page + '.php'); // Explicação DevIN: Declara variável JavaScript source para reutilizar valor ou resultado.
        assert.match(source, /mobile-arcade-secret\.js/); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
        assert.match(source, /konami-arcade\.js/); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('um toque contínuo de 3000ms abre a rota autenticada do Arcade', () => { // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    const env = simulate(true); // Explicação DevIN: Declara variável JavaScript env para reutilizar valor ou resultado.
    env.handlers.pointerdown(down()); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    assert.ok(env.classes.has('is-holding')); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    env.advance(3000); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    assert.deepEqual(env.redirects, []); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    env.advance(260); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    assert.equal(env.redirects.length, 1); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    assert.ok(env.redirects[0].endsWith('/php/arcade.php')); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('toque curto, movimento de rolagem e segundo plano cancelam o segredo', () => { // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    const env = simulate(true); // Explicação DevIN: Declara variável JavaScript env para reutilizar valor ou resultado.
    env.handlers.pointerdown(down()); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    env.handlers.pointerup(down()); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    assert.equal(env.timers.size, 0); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    env.handlers.pointerdown(down()); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    env.handlers.pointermove({ pointerId: 9, clientX: 120, clientY: 100 }); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    assert.equal(env.timers.size, 0); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    env.handlers.pointerdown(down()); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    env.windowListeners.scroll(); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    assert.equal(env.timers.size, 0); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    env.handlers.pointerdown(down()); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    env.context.document.hidden = true; // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    env.documentListeners.visibilitychange(); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    assert.equal(env.timers.size, 0); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    assert.equal(env.redirects.length, 0); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('mouse de desktop nunca agenda o segredo e CSS oculta o botão', () => { // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    const env = simulate(false); // Explicação DevIN: Declara variável JavaScript env para reutilizar valor ou resultado.
    env.handlers.pointerdown(down('mouse')); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    assert.equal(env.timers.size, 0); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    const css = read('css/dashboard.css'); // Explicação DevIN: Declara variável JavaScript css para reutilizar valor ou resultado.
    assert.match(css, /\.sobre-secret-arcade\s*\{\s*display:\s*none/); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    assert.match(css, /@media\s*\(hover:\s*none\)\s*and\s*\(pointer:\s*coarse\)/); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    assert.match(css, /\.menu-principal\s+\.menu-arcade\s*\{\s*display:\s*none/); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.


test('soltar mesmo após completar três segundos, antes da navegação, descarta a tentativa e permite recomeçar', () => { // Explicação DevIN: Verifica a janela de feedback em que uma liberação antes do redirecionamento deve abortar.
    const env = simulate(true); // Explicação DevIN: Cria um ambiente touch com timers controláveis.
    env.handlers.pointerdown(down()); // Explicação DevIN: Inicia a primeira tentativa pelo botão secreto.
    env.advance(3000); // Explicação DevIN: Completa a contagem de três segundos ainda com o dedo pressionado.
    env.handlers.pointerup(down()); // Explicação DevIN: Solta durante o breve feedback, antes da navegação.
    assert.equal(env.timers.size, 0); // Explicação DevIN: Tanto a contagem quanto o redirecionamento devem ser cancelados.
    assert.equal(env.redirects.length, 0); // Explicação DevIN: O Arcade não abre quando a pressão é interrompida.
    assert.equal(env.classes.has('is-discovered'), false); // Explicação DevIN: A indicação de descoberta é removida ao cancelar.
    env.handlers.pointerdown(down()); // Explicação DevIN: Começa novamente do zero, sem precisar atualizar a página.
    env.advance(3000); // Explicação DevIN: A nova pressão completa o tempo necessário.
    env.advance(260); // Explicação DevIN: Completa o curto intervalo de animação.
    assert.equal(env.redirects.length, 1); // Explicação DevIN: Somente a segunda tentativa completa pode navegar.
}); // Explicação DevIN: Encerra o teste de reset mesmo na janela de transição.
