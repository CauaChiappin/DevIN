/**
 * Testes de integração estática e execução básica dos oito motores extras.
 * Canvas falso não testa renderização pixel-a-pixel; garante que os motores
 * sejam instanciáveis e exponham contrato compatível com o Arcade existente.
 */
const test = require('node:test'); // Explicação DevIN: Importa o executor de testes da plataforma Node.js.
const assert = require('node:assert/strict'); // Explicação DevIN: Importa asserções estritas para validar comportamento.
const fs = require('node:fs'); // Explicação DevIN: Permite ler os arquivos reais do projeto.
const vm = require('node:vm'); // Explicação DevIN: Executa o módulo em ambiente isolado de testes.
const path = require('node:path'); // Explicação DevIN: Monta caminhos independentes do sistema operacional.
const root = path.join(__dirname, '..'); // Explicação DevIN: Define diretório principal do repositório.
const read = (name) => fs.readFileSync(path.join(root, name), 'utf8'); // Explicação DevIN: Carrega texto para validação.
const browser = { DevINArcadeExtraGames: null }; // Explicação DevIN: Imita o objeto window com o registro do módulo.
vm.runInNewContext(read('js/arcade-extra-games.js'), { window: browser, Math }); // Explicação DevIN: Registra os jogos sem abrir um navegador.
const registry = browser.DevINArcadeExtraGames; // Explicação DevIN: Obtém catálogo e fábrica registrados no window.
const ids = ['pong','dodge','flappy','target','collector','racer','simon','basket']; // Explicação DevIN: Define lista esperada de minijogos.
const ctx = {
    fillRect(){}, beginPath(){}, arc(){}, fill(){}, moveTo(){}, lineTo(){}, stroke(){},
    fillText(){}, closePath(){}, save(){}, restore(){},
    set fillStyle(x){ this._fill = x; }, get fillStyle(){ return this._fill; },
    set strokeStyle(x){ this._stroke = x; }, set font(x){ this._font = x; },
    set textAlign(x){ this._align = x; }
}; // Explicação DevIN: Emula os comandos Canvas usados pelos motores.
const canvas = {getBoundingClientRect(){return {left:0,top:0,width:640,height:480};}}; // Explicação DevIN: Emula as dimensões do canvas.

test('catálogo apresenta exatamente os oito títulos extras', () => {
    assert.deepEqual(Array.from(Object.keys(registry.metadata)).sort(), ids.slice().sort());
    for (const id of ids) {
        assert.ok(registry.metadata[id].title, 'Nome ausente: ' + id);
        assert.ok(registry.metadata[id].hint, 'Controles ausentes: ' + id);
    }
});

test('cada jogo possui motor compatível, desenha e recebe entradas', () => {
    const context = { ctx, canvas, colors: {}, gameOver(){}, saveProgress(){} };
    const engines = registry.create(context).factories;
    for (const id of ids) {
        assert.equal(typeof engines[id], 'function', id);
        const engine = engines[id]();
        assert.equal(engine.game, id);
        assert.equal(engine.level, 1);
        assert.equal(engine.score, 0);
        for (const method of ['key','pointer','update','draw']) {
            assert.equal(typeof engine[method], 'function', id + ' - ' + method);
        }
        assert.doesNotThrow(() => {
            engine.key('ArrowLeft');
            engine.key('ArrowRight');
            engine.key('Space');
            engine.pointer({clientX:300,clientY:200});
            for (let i=0;i<12;i++) engine.update(16);
            engine.draw();
        }, 'Erro no motor '+id);
        assert.ok(Number.isFinite(engine.score) && engine.score>=0, id);
    }
});

test('HTML e backend registram todos os jogos com recordes', () => {
    const html = read('php/arcade.php');
    const api = read('php/arcade_api.php');
    const driver = read('js/arcade-games.js');
    for (const id of ids) {
        assert.ok(html.includes('data-launch-game="'+id+'"'), 'Card não localizado: '+id);
        assert.ok(api.includes("'"+id+"' =>"), 'Limites de placar ausentes: '+id);
    }
    assert.ok(html.includes('arcade-extra-games.js'));
    assert.ok(html.includes('arcade-library.js'));
    assert.ok(driver.includes('...extraFactories'));
    assert.ok(html.indexOf('arcade-extra-games.js') < html.indexOf('arcade-games.js'));
});

test('catálogo suporta busca, filtros e escolha aleatória', () => {
    const src = read('js/arcade-library.js');
    assert.ok(src.includes('data-arcade-search'));
    assert.ok(src.includes('data-arcade-filter'));
    assert.ok(src.includes('data-random-game'));
});
