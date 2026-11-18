/**
 * Testes de integração estática e execução básica dos oito motores extras.
 * Canvas falso não testa renderização pixel-a-pixel; garante que os motores
 * sejam instanciáveis e exponham contrato compatível com o Arcade existente.
 */
const test = require('node:test'); // Explicação DevIN: Declara variável JavaScript test para reutilizar valor ou resultado.
const assert = require('node:assert/strict'); // Explicação DevIN: Declara variável JavaScript assert para reutilizar valor ou resultado.
const fs = require('node:fs'); // Explicação DevIN: Declara variável JavaScript fs para reutilizar valor ou resultado.
const vm = require('node:vm'); // Explicação DevIN: Declara variável JavaScript vm para reutilizar valor ou resultado.
const path = require('node:path'); // Explicação DevIN: Declara variável JavaScript path para reutilizar valor ou resultado.
const root = path.join(__dirname, '..'); // Explicação DevIN: Declara variável JavaScript root para reutilizar valor ou resultado.
const read = (name) => fs.readFileSync(path.join(root, name), 'utf8'); // Explicação DevIN: Declara variável JavaScript read para reutilizar valor ou resultado.
const browser = { DevINArcadeExtraGames: null }; // Explicação DevIN: Declara variável JavaScript browser para reutilizar valor ou resultado.
vm.runInNewContext(read('js/arcade-extra-games.js'), { window: browser, Math }); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
const registry = browser.DevINArcadeExtraGames; // Explicação DevIN: Declara variável JavaScript registry para reutilizar valor ou resultado.
const ids = ['pong','dodge','flappy','target','collector','racer','simon','basket']; // Explicação DevIN: Declara variável JavaScript ids para reutilizar valor ou resultado.
const ctx = { // Explicação DevIN: Declara variável JavaScript ctx para reutilizar valor ou resultado.
    fillRect(){}, beginPath(){}, arc(){}, fill(){}, moveTo(){}, lineTo(){}, stroke(){}, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    fillText(){}, closePath(){}, save(){}, restore(){}, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    set fillStyle(x){ this._fill = x; }, get fillStyle(){ return this._fill; }, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    set strokeStyle(x){ this._stroke = x; }, set font(x){ this._font = x; }, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    set textAlign(x){ this._align = x; } // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
}; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
const canvas = {getBoundingClientRect(){return {left:0,top:0,width:640,height:480};}}; // Explicação DevIN: Declara variável JavaScript canvas para reutilizar valor ou resultado.

test('catálogo apresenta exatamente os oito títulos extras', () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.deepEqual(Array.from(Object.keys(registry.metadata)).sort(), ids.slice().sort()); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    for (const id of ids) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
        assert.ok(registry.metadata[id].title, 'Nome ausente: ' + id); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        assert.ok(registry.metadata[id].hint, 'Controles ausentes: ' + id); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('cada jogo possui motor compatível, desenha e recebe entradas', () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    const context = { ctx, canvas, colors: {}, gameOver(){}, saveProgress(){} }; // Explicação DevIN: Declara variável JavaScript context para reutilizar valor ou resultado.
    const engines = registry.create(context).factories; // Explicação DevIN: Declara variável JavaScript engines para reutilizar valor ou resultado.
    for (const id of ids) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
        assert.equal(typeof engines[id], 'function', id); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        const engine = engines[id](); // Explicação DevIN: Declara variável JavaScript engine para reutilizar valor ou resultado.
        assert.equal(engine.game, id); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        assert.equal(engine.level, 1); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        assert.equal(engine.score, 0); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        for (const method of ['key','pointer','update','draw']) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            assert.equal(typeof engine[method], 'function', id + ' - ' + method); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        assert.doesNotThrow(() => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            engine.key('ArrowLeft'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            engine.key('ArrowRight'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            engine.key('Space'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            engine.pointer({clientX:300,clientY:200}); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            for (let i=0;i<12;i++) engine.update(16); // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            engine.draw(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        }, 'Erro no motor '+id); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        assert.ok(Number.isFinite(engine.score) && engine.score>=0, id); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('HTML e backend registram todos os jogos com recordes', () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    const html = read('php/arcade.php'); // Explicação DevIN: Declara variável JavaScript html para reutilizar valor ou resultado.
    const api = read('php/arcade_api.php'); // Explicação DevIN: Declara variável JavaScript api para reutilizar valor ou resultado.
    const driver = read('js/arcade-games.js'); // Explicação DevIN: Declara variável JavaScript driver para reutilizar valor ou resultado.
    for (const id of ids) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
        assert.ok(html.includes('data-launch-game="'+id+'"'), 'Card não localizado: '+id); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        assert.ok(api.includes("'"+id+"' =>"), 'Limites de placar ausentes: '+id); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    assert.ok(html.includes('arcade-extra-games.js')); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.ok(html.includes('arcade-library.js')); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.ok(driver.includes('...extraFactories')); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.ok(html.indexOf('arcade-extra-games.js') < html.indexOf('arcade-games.js')); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('catálogo suporta busca, filtros e escolha aleatória', () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    const src = read('js/arcade-library.js'); // Explicação DevIN: Declara variável JavaScript src para reutilizar valor ou resultado.
    assert.ok(src.includes('data-arcade-search')); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.ok(src.includes('data-arcade-filter')); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.ok(src.includes('data-random-game')); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('recorde próprio e visão geral ADM são consultados separadamente', () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    const api = read('php/arcade_api.php'); // Explicação DevIN: Declara variável JavaScript api para reutilizar valor ou resultado.
    const driver = read('js/arcade-games.js'); // Explicação DevIN: Declara variável JavaScript driver para reutilizar valor ou resultado.
    assert.match(api, /\$overview\s*=\s*\$usuario\['tipo'\]\s*===\s*'adm'/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(api, /\$_GET\['escopo'\]/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(driver, /apiRequest\(\{ escopo: 'todos' \}, 'GET'\)/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(driver, /async function loadProgress\(\)[\s\S]*?apiRequest\(\{\}, 'GET'\)/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(api, /'escopo'\s*=>\s*\$overview\s*\?\s*'todos'\s*:\s*'proprio'/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('todos os 14 jogos podem ter recordes removidos por ADM', () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    const api = read('php/arcade_api.php'); // Explicação DevIN: Declara variável JavaScript api para reutilizar valor ou resultado.
    assert.match(api, /\$allowedGames\s*=\s*array_keys\(arcadeGameLimits\(\)\)/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(api, /!in_array\(\$game,\s*\$allowedGames,\s*true\)/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.doesNotMatch(api, /!in_array\(\$game,\s*\['maze',\s*'snake'/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('arcade exige login, desbloqueio secreto e não publica link no menu', () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    const page = read('php/arcade.php'); // Explicação DevIN: Declara variável JavaScript page para reutilizar valor ou resultado.
    const api = read('php/arcade_api.php'); // Explicação DevIN: Declara variável JavaScript api para reutilizar valor ou resultado.
    const unlock = read('php/arcade_unlock.php'); // Confirma que os atalhos liberam a sessão via servidor.
    assert.match(page, /\$usuario\s*=\s*requireWebAuth\(\)/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(api, /\$usuario\s*=\s*requireWebAuth\(\)/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(page, /ArcadeAccess::isUnlocked\(\$usuario\)/); // A página exige liberação secreta válida.
    assert.match(api, /ArcadeAccess::isUnlocked\(\$usuario\)/); // A API compartilha a mesma proteção.
    assert.match(unlock, /ArcadeAccess::grant\(\$usuario\)/); // A liberação ocorre somente no endpoint autenticado.
    for (const name of ['pessoa','empresa','adm']) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
        assert.doesNotMatch(read('php/'+name+'.php'), /href="arcade\.php"|menu-arcade/); // Nenhum dashboard anuncia a entrada do Arcade.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('partidas de reflexos e memória evoluem sem quebrar', () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    const engines = registry.create({ ctx, canvas, colors: {}, gameOver(){}, saveProgress(){} }).factories; // Explicação DevIN: Declara variável JavaScript engines para reutilizar valor ou resultado.
    // O primeiro alvo é intencionalmente central para permitir teste de clique.
    const target = engines.target(); // Explicação DevIN: Declara variável JavaScript target para reutilizar valor ou resultado.
    target.pointer({clientX:320, clientY:230}); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.equal(target.score, 20); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

    const collector = engines.collector(); // Explicação DevIN: Declara variável JavaScript collector para reutilizar valor ou resultado.
    const star = collector.state.stars[0]; // Explicação DevIN: Declara variável JavaScript star para reutilizar valor ou resultado.
    collector.pointer({clientX:star.x, clientY:star.y}); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    collector.update(16); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.ok(collector.score >= 25, 'Coleta por toque deve pontuar'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

    const racer = engines.racer(); // Explicação DevIN: Declara variável JavaScript racer para reutilizar valor ou resultado.
    racer.key('ArrowLeft'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.equal(racer.state.lane, 0); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    racer.key('ArrowRight'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.equal(racer.state.lane, 1); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

    const flappy = engines.flappy(); // Explicação DevIN: Declara variável JavaScript flappy para reutilizar valor ou resultado.
    flappy.key('Space'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    flappy.update(16); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.ok(flappy.state.y < 220, 'Pulo deve mudar a altura'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

    const simon = engines.simon(); // Explicação DevIN: Declara variável JavaScript simon para reutilizar valor ou resultado.
    simon.update(750); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    simon.update(16); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    simon.update(600); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    simon.pick(simon.state.sequence[0]); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.equal(simon.score, 30, 'Repetição correta de memória deve pontuar'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('motores toleram várias atualizações consecutivas de tempo', () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    const engines = registry.create({ ctx, canvas, colors: {}, gameOver(){}, saveProgress(){} }).factories; // Explicação DevIN: Declara variável JavaScript engines para reutilizar valor ou resultado.
    for (const id of ids) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
        const engine = engines[id](); // Explicação DevIN: Declara variável JavaScript engine para reutilizar valor ou resultado.
        assert.doesNotThrow(() => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            for (let frame=0; frame<170; frame++) engine.update(16); // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            engine.draw(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        }, 'Falha ao simular partida: '+id); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        assert.ok(Number.isFinite(engine.score), 'Score finito: '+id); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        assert.ok(Number.isFinite(engine.level), 'Nível finito: '+id); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
