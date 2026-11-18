/**
 * Testes do segredo mobile com cinco toques, usando eventos click reais simulados.
 * Não depende de pointer:coarse, long-press nem de botão público.
 */
const test = require('node:test'); // Explicação DevIN: Declara variável JavaScript test para reutilizar valor ou resultado.
const assert = require('node:assert/strict'); // Explicação DevIN: Declara variável JavaScript assert para reutilizar valor ou resultado.
const fs = require('node:fs'); // Explicação DevIN: Declara variável JavaScript fs para reutilizar valor ou resultado.
const path = require('node:path'); // Explicação DevIN: Declara variável JavaScript path para reutilizar valor ou resultado.
const vm = require('node:vm'); // Explicação DevIN: Declara variável JavaScript vm para reutilizar valor ou resultado.
const root = path.resolve(__dirname, '..'); // Explicação DevIN: Declara variável JavaScript root para reutilizar valor ou resultado.
const read = name => fs.readFileSync(path.join(root,name),'utf8'); // Explicação DevIN: Declara variável JavaScript read para reutilizar valor ou resultado.

function simulate(hasTarget = true, failRequest = false) { // Explicação DevIN: Declara função/método simulate, com seus parâmetros e comportamento.
    const handlers = {}; // Explicação DevIN: Declara variável JavaScript handlers para reutilizar valor ou resultado.
    const events = {}; // Explicação DevIN: Declara variável JavaScript events para reutilizar valor ou resultado.
    const calls = []; // Explicação DevIN: Declara variável JavaScript calls para reutilizar valor ou resultado.
    const redirects = []; // Explicação DevIN: Declara variável JavaScript redirects para reutilizar valor ou resultado.
    const alerts = []; // Explicação DevIN: Declara variável JavaScript alerts para reutilizar valor ou resultado.
    let clock = 1000; // Explicação DevIN: Declara variável JavaScript clock para reutilizar valor ou resultado.
    const title = { addEventListener: (name, fn) => { handlers[name] = fn; } }; // Explicação DevIN: Declara variável JavaScript title para reutilizar valor ou resultado.
    const document = { // Explicação DevIN: Declara variável JavaScript document para reutilizar valor ou resultado.
        hidden: false, // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
        querySelector: selector => selector === '[data-mobile-arcade-secret]' && hasTarget ? title : null, // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
        addEventListener: (name, fn) => { events[name] = fn; } // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    const window = { // Explicação DevIN: Declara variável JavaScript window para reutilizar valor ou resultado.
        location: { href: 'https://example.org/Trabalhos/3D/DevIN/php/pessoa.php?pagina=sobre', assign: link => redirects.push(link) }, // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
        alert: message => alerts.push(message) // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    const fetch = async (url, options) => { // Explicação DevIN: Declara variável JavaScript fetch para reutilizar valor ou resultado.
        calls.push({ url: String(url), options }); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
        if (failRequest) throw new Error('Rede indisponível'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        return { ok: true, json: async () => String(url).includes('csrf.php') // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            ? { sucesso: true, token: 'csrf-local' } // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            : { sucesso: true, url: '/Trabalhos/3D/DevIN/php/arcade.php' } }; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    vm.runInNewContext(read('js/mobile-arcade-secret.js'), {document, window, fetch, URL, URLSearchParams, Date: {now: () => clock}}); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    return {handlers, events, document, calls, redirects, alerts, tick: ms => {clock += ms;}, tap: () => handlers.click()}; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('o título Time fundador é o único acesso alternativo, sem ícone visível', () => { // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    const html = read('php/helpers.php'); // Explicação DevIN: Declara variável JavaScript html para reutilizar valor ou resultado.
    assert.match(html, /<h2 class="team-header" data-mobile-arcade-secret>Time fundador<\/h2>/); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    assert.doesNotMatch(html, /sobre-secret-arcade-button/); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    assert.doesNotMatch(read('css/dashboard.css'), /\.sobre-secret-arcade-button/); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    for (const account of ['pessoa', 'empresa', 'adm']) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
        assert.match(read('php/' + account + '.php'), /mobile-arcade-secret\.js/); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
        assert.match(read('php/' + account + '.php'), /konami-arcade\.js/); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('cinco toques sucessivos liberam sessão via CSRF e navegam ao Arcade', async () => { // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    const env = simulate(); // Explicação DevIN: Declara variável JavaScript env para reutilizar valor ou resultado.
    for (let i = 0; i < 4; i++) { env.tap(); env.tick(260); } // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
    assert.equal(env.calls.length, 0); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    env.tap(); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    await new Promise(resolve => setImmediate(resolve)); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    assert.equal(env.calls.length, 2); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    assert.ok(env.calls[0].url.endsWith('/php/csrf.php')); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    assert.ok(env.calls[1].url.endsWith('/php/arcade_unlock.php')); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    assert.equal(env.calls[1].options.body.get('csrf_token'), 'csrf-local'); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    assert.equal(env.calls[1].options.body.get('modo'), 'toque'); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    assert.ok(env.redirects[0].endsWith('/php/arcade.php')); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('intervalo grande e saída da página reiniciam a sequência', () => { // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    const env = simulate(); // Explicação DevIN: Declara variável JavaScript env para reutilizar valor ou resultado.
    for (let i = 0; i < 4; i++) { env.tap(); env.tick(200); } // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
    env.tick(1800); env.tap(); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    assert.equal(env.calls.length, 0); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    env.tap(); env.document.hidden = true; env.events.visibilitychange(); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    env.document.hidden = false; env.tap(); env.tap(); env.tap(); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    assert.equal(env.calls.length, 0); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('falha de rede mostra mensagem e permite tentar novamente', async () => { // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    const env = simulate(true, true); // Explicação DevIN: Declara variável JavaScript env para reutilizar valor ou resultado.
    for (let i = 0; i < 5; i++) env.tap(); // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
    await new Promise(resolve => setImmediate(resolve)); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    assert.equal(env.redirects.length, 0); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    assert.equal(env.alerts.length, 1); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    for (let i = 0; i < 5; i++) env.tap(); // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
    assert.equal(env.calls.length, 2); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('fora de Sobre nós o segredo não instala handlers', () => { // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    const env = simulate(false); // Explicação DevIN: Declara variável JavaScript env para reutilizar valor ou resultado.
    assert.equal(Object.keys(env.handlers).length, 0); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
    assert.equal(env.calls.length, 0); // Explicação DevIN: Continua expressão ou bloco pertencente a função simulate.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
