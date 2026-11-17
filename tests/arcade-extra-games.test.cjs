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

test('recorde próprio e visão geral ADM são consultados separadamente', () => {
    const api = read('php/arcade_api.php');
    const driver = read('js/arcade-games.js');
    assert.match(api, /\$overview\s*=\s*\$usuario\['tipo'\]\s*===\s*'adm'/);
    assert.match(api, /\$_GET\['escopo'\]/);
    assert.match(driver, /apiRequest\(\{ escopo: 'todos' \}, 'GET'\)/);
    assert.match(driver, /async function loadProgress\(\)[\s\S]*?apiRequest\(\{\}, 'GET'\)/);
    assert.match(api, /'escopo'\s*=>\s*\$overview\s*\?\s*'todos'\s*:\s*'proprio'/);
});

test('todos os 14 jogos podem ter recordes removidos por ADM', () => {
    const api = read('php/arcade_api.php');
    assert.match(api, /\$allowedGames\s*=\s*array_keys\(arcadeGameLimits\(\)\)/);
    assert.match(api, /!in_array\(\$game,\s*\$allowedGames,\s*true\)/);
    assert.doesNotMatch(api, /!in_array\(\$game,\s*\['maze',\s*'snake'/);
});

test('arcade permanece protegido por login mas não exige código secreto', () => {
    const page = read('php/arcade.php');
    const api = read('php/arcade_api.php');
    assert.match(page, /\$usuario\s*=\s*requireWebAuth\(\)/);
    assert.match(api, /\$usuario\s*=\s*requireWebAuth\(\)/);
    assert.doesNotMatch(api, /requireArcadeUnlock\(\$usuario\)/);
    assert.doesNotMatch(page, /\$_SESSION\['arcade_unlock'\]/);
    for (const name of ['pessoa','empresa','adm']) {
        assert.match(read('php/'+name+'.php'), /href="arcade\.php"/);
    }
});

test('partidas de reflexos e memória evoluem sem quebrar', () => {
    const engines = registry.create({ ctx, canvas, colors: {}, gameOver(){}, saveProgress(){} }).factories;
    // O primeiro alvo é intencionalmente central para permitir teste de clique.
    const target = engines.target();
    target.pointer({clientX:320, clientY:230});
    assert.equal(target.score, 20);

    const collector = engines.collector();
    const star = collector.state.stars[0];
    collector.pointer({clientX:star.x, clientY:star.y});
    collector.update(16);
    assert.ok(collector.score >= 25, 'Coleta por toque deve pontuar');

    const racer = engines.racer();
    racer.key('ArrowLeft');
    assert.equal(racer.state.lane, 0);
    racer.key('ArrowRight');
    assert.equal(racer.state.lane, 1);

    const flappy = engines.flappy();
    flappy.key('Space');
    flappy.update(16);
    assert.ok(flappy.state.y < 220, 'Pulo deve mudar a altura');

    const simon = engines.simon();
    simon.update(750);
    simon.update(16);
    simon.update(600);
    simon.pick(simon.state.sequence[0]);
    assert.equal(simon.score, 30, 'Repetição correta de memória deve pontuar');
});

test('motores toleram várias atualizações consecutivas de tempo', () => {
    const engines = registry.create({ ctx, canvas, colors: {}, gameOver(){}, saveProgress(){} }).factories;
    for (const id of ids) {
        const engine = engines[id]();
        assert.doesNotThrow(() => {
            for (let frame=0; frame<170; frame++) engine.update(16);
            engine.draw();
        }, 'Falha ao simular partida: '+id);
        assert.ok(Number.isFinite(engine.score), 'Score finito: '+id);
        assert.ok(Number.isFinite(engine.level), 'Nível finito: '+id);
    }
});
