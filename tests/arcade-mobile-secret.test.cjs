/**
 * Testes do segredo mobile com cinco toques, usando eventos click reais simulados.
 * Não depende de pointer:coarse, long-press nem de botão público.
 */
const test = require('node:test'); // Explicação DevIN: Disponibiliza o runner padrão Node.
const assert = require('node:assert/strict'); // Explicação DevIN: Compara resultados sem coerções.
const fs = require('node:fs'); // Explicação DevIN: Lê arquivos do código versionado.
const path = require('node:path'); // Explicação DevIN: Resolve caminhos do repositório.
const vm = require('node:vm'); // Explicação DevIN: Simula o navegador com isolamento.
const root = path.resolve(__dirname, '..'); // Explicação DevIN: Localiza a raiz DevIN.
const read = name => fs.readFileSync(path.join(root,name),'utf8'); // Explicação DevIN: Obtém conteúdo sem rede.

function simulate(hasTarget = true, failRequest = false) { // Explicação DevIN: Cria contexto de teste para a interface secreta.
    const handlers = {}; // Explicação DevIN: Armazena os eventos inscritos no título.
    const events = {}; // Explicação DevIN: Armazena eventos globais de visibilidade.
    const calls = []; // Explicação DevIN: Registra somente as requisições efetuadas.
    const redirects = []; // Explicação DevIN: Registra navegação autorizada.
    const alerts = []; // Explicação DevIN: Captura falhas de abertura.
    let clock = 1000; // Explicação DevIN: Permite simular intervalos sem aguardar tempo real.
    const title = { addEventListener: (name, fn) => { handlers[name] = fn; } }; // Explicação DevIN: Representa o título Time fundador.
    const document = { // Explicação DevIN: Oferece apenas os recursos usados pelo módulo.
        hidden: false, // Explicação DevIN: Inicia em uma página visível.
        querySelector: selector => selector === '[data-mobile-arcade-secret]' && hasTarget ? title : null, // Explicação DevIN: Seleciona o título pelo atributo.
        addEventListener: (name, fn) => { events[name] = fn; } // Explicação DevIN: Observa ocultação da página.
    }; // Explicação DevIN: Conclui a simulação do documento.
    const window = { // Explicação DevIN: Simula navegação e aviso no navegador.
        location: { href: 'https://example.org/Trabalhos/3D/DevIN/php/pessoa.php?pagina=sobre', assign: link => redirects.push(link) }, // Explicação DevIN: Armazena o destino usado.
        alert: message => alerts.push(message) // Explicação DevIN: Captura feedback caso a rede falhe.
    }; // Explicação DevIN: Conclui a simulação da janela.
    const fetch = async (url, options) => { // Explicação DevIN: Simula respostas HTTP sem usar o servidor.
        calls.push({ url: String(url), options }); // Explicação DevIN: Inspeciona CSRF e caminho da API.
        if (failRequest) throw new Error('Rede indisponível'); // Explicação DevIN: Simula uma falha recuperável.
        return { ok: true, json: async () => String(url).includes('csrf.php') // Explicação DevIN: Oferece token na primeira chamada.
            ? { sucesso: true, token: 'csrf-local' } // Explicação DevIN: Usa token fictício para teste.
            : { sucesso: true, url: '/Trabalhos/3D/DevIN/php/arcade.php' } }; // Explicação DevIN: Confirma liberação pelo backend.
    }; // Explicação DevIN: Conclui o transporte simulado.
    vm.runInNewContext(read('js/mobile-arcade-secret.js'), {document, window, fetch, URL, URLSearchParams, Date: {now: () => clock}}); // Explicação DevIN: Executa o módulo dentro do navegador simulado.
    return {handlers, events, document, calls, redirects, alerts, tick: ms => {clock += ms;}, tap: () => handlers.click()}; // Explicação DevIN: Expõe eventos ao teste.
} // Explicação DevIN: Finaliza a fábrica do contexto.

test('o título Time fundador é o único acesso alternativo, sem ícone visível', () => { // Explicação DevIN: Confere o novo segredo no HTML real.
    const html = read('php/helpers.php'); // Explicação DevIN: Lê a seção Sobre nós compartilhada.
    assert.match(html, /<h2 class="team-header" data-mobile-arcade-secret>Time fundador<\/h2>/); // Explicação DevIN: Usa título existente.
    assert.doesNotMatch(html, /sobre-secret-arcade-button/); // Explicação DevIN: Impede o retorno do botão público.
    assert.doesNotMatch(read('css/dashboard.css'), /\.sobre-secret-arcade-button/); // Explicação DevIN: Remove CSS do botão antigo.
    for (const account of ['pessoa', 'empresa', 'adm']) { // Explicação DevIN: Verifica os três painéis autenticados.
        assert.match(read('php/' + account + '.php'), /mobile-arcade-secret\.js/); // Explicação DevIN: Mantém script do segredo em cada painel.
        assert.match(read('php/' + account + '.php'), /konami-arcade\.js/); // Explicação DevIN: Preserva a sequência do computador.
    } // Explicação DevIN: Conclui a verificação dos perfis.
}); // Explicação DevIN: Encerra o teste do template.

test('cinco toques sucessivos liberam sessão via CSRF e navegam ao Arcade', async () => { // Explicação DevIN: Exige desbloqueio pelo backend.
    const env = simulate(); // Explicação DevIN: Inicializa uma página com título.
    for (let i = 0; i < 4; i++) { env.tap(); env.tick(260); } // Explicação DevIN: Executa quatro toques incompletos.
    assert.equal(env.calls.length, 0); // Explicação DevIN: Nenhuma chamada antes do último toque.
    env.tap(); // Explicação DevIN: Completa a sequência de cinco toques.
    await new Promise(resolve => setImmediate(resolve)); // Explicação DevIN: Escoa promessas do fluxo assíncrono.
    assert.equal(env.calls.length, 2); // Explicação DevIN: Busca CSRF e depois solicita desbloqueio.
    assert.ok(env.calls[0].url.endsWith('/php/csrf.php')); // Explicação DevIN: Confirma endereço da proteção.
    assert.ok(env.calls[1].url.endsWith('/php/arcade_unlock.php')); // Explicação DevIN: Confirma endpoint secreto.
    assert.equal(env.calls[1].options.body.get('csrf_token'), 'csrf-local'); // Explicação DevIN: Confirma token encaminhado.
    assert.equal(env.calls[1].options.body.get('modo'), 'toque'); // Explicação DevIN: Mantém protocolo de sessão existente.
    assert.ok(env.redirects[0].endsWith('/php/arcade.php')); // Explicação DevIN: Só navega com confirmação do servidor.
}); // Explicação DevIN: Encerra o teste de sucesso.

test('intervalo grande e saída da página reiniciam a sequência', () => { // Explicação DevIN: Evita liberação acidental.
    const env = simulate(); // Explicação DevIN: Cria contexto independente.
    for (let i = 0; i < 4; i++) { env.tap(); env.tick(200); } // Explicação DevIN: Executa sequência parcial.
    env.tick(1800); env.tap(); // Explicação DevIN: Um intervalo maior reinicia o contador.
    assert.equal(env.calls.length, 0); // Explicação DevIN: Não libera por toques distantes.
    env.tap(); env.document.hidden = true; env.events.visibilitychange(); // Explicação DevIN: Limpa sequência ao ocultar aba.
    env.document.hidden = false; env.tap(); env.tap(); env.tap(); // Explicação DevIN: Retoma sequência curta.
    assert.equal(env.calls.length, 0); // Explicação DevIN: Não herda toques antes da ocultação.
}); // Explicação DevIN: Encerra o teste de reinício.

test('falha de rede mostra mensagem e permite tentar novamente', async () => { // Explicação DevIN: Confere recuperação sem tela de erro crua.
    const env = simulate(true, true); // Explicação DevIN: Simula conexão indisponível.
    for (let i = 0; i < 5; i++) env.tap(); // Explicação DevIN: Completa a sequência secreta.
    await new Promise(resolve => setImmediate(resolve)); // Explicação DevIN: Aguarda captura da falha.
    assert.equal(env.redirects.length, 0); // Explicação DevIN: Bloqueia navegação sem validação.
    assert.equal(env.alerts.length, 1); // Explicação DevIN: Mostra mensagem legível.
    for (let i = 0; i < 5; i++) env.tap(); // Explicação DevIN: Refaz o segredo após erro.
    assert.equal(env.calls.length, 2); // Explicação DevIN: Permite nova tentativa.
}); // Explicação DevIN: Encerra o teste de erro.

test('fora de Sobre nós o segredo não instala handlers', () => { // Explicação DevIN: Mantém outras páginas sem entrada visível.
    const env = simulate(false); // Explicação DevIN: Simula página sem título fundador.
    assert.equal(Object.keys(env.handlers).length, 0); // Explicação DevIN: Nenhuma ação secreta é instalada.
    assert.equal(env.calls.length, 0); // Explicação DevIN: Não aciona nenhuma requisição.
}); // Explicação DevIN: Encerra o teste de ausência.
