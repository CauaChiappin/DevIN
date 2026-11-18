/**
 * Testa as traduções reais carregadas pelo painel DevIN para a Central de Suporte.
 * O suporte deve usar pt-BR, en e es, sem traduzir dados enviados pelo usuário.
 */
const test = require('node:test'); // Explicação DevIN: Declara variável JavaScript test para reutilizar valor ou resultado.
const assert = require('node:assert/strict'); // Explicação DevIN: Declara variável JavaScript assert para reutilizar valor ou resultado.
const fs = require('node:fs'); // Explicação DevIN: Declara variável JavaScript fs para reutilizar valor ou resultado.
const path = require('node:path'); // Explicação DevIN: Declara variável JavaScript path para reutilizar valor ou resultado.
const vm = require('node:vm'); // Explicação DevIN: Declara variável JavaScript vm para reutilizar valor ou resultado.
const root = path.resolve(__dirname, '..'); // Explicação DevIN: Declara variável JavaScript root para reutilizar valor ou resultado.
const read = name => fs.readFileSync(path.join(root,name),'utf8'); // Explicação DevIN: Declara variável JavaScript read para reutilizar valor ou resultado.

function translator(lang) { // Explicação DevIN: Declara função/método translator, com seus parâmetros e comportamento.
    const window = {}; // Explicação DevIN: Declara variável JavaScript window para reutilizar valor ou resultado.
    const body = {hasAttribute: () => false, querySelectorAll: () => []}; // Explicação DevIN: Declara variável JavaScript body para reutilizar valor ou resultado.
    const document = { // Explicação DevIN: Declara variável JavaScript document para reutilizar valor ou resultado.
        documentElement: {lang}, // Explicação DevIN: Continua expressão ou bloco pertencente a função translator.
        title: 'DevIN', // Explicação DevIN: Continua expressão ou bloco pertencente a função translator.
        body, // Explicação DevIN: Continua expressão ou bloco pertencente a função translator.
        createTreeWalker: () => ({nextNode: () => null}) // Explicação DevIN: Continua expressão ou bloco pertencente a função translator.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    class MutationObserver { observe() {} } // Explicação DevIN: Declara o tipo MutationObserver e inicia sua estrutura.
    vm.runInNewContext(read('js/dashboard-i18n.js'), {window, document, MutationObserver, NodeFilter: {SHOW_TEXT: 4}}); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo MutationObserver.
    return window.DevINTranslate; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('traduz campos e ações do suporte para inglês e espanhol', () => { // Explicação DevIN: Continua expressão ou bloco pertencente a tipo MutationObserver.
    const en = translator('en'); // Explicação DevIN: Declara variável JavaScript en para reutilizar valor ou resultado.
    const es = translator('es'); // Explicação DevIN: Declara variável JavaScript es para reutilizar valor ou resultado.
    assert.equal(en('Como podemos ajudar?'), 'How can we help?'); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo MutationObserver.
    assert.equal(es('Como podemos ajudar?'), '¿Cómo podemos ayudarte?'); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo MutationObserver.
    assert.equal(en('Meus chamados'), 'My requests'); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo MutationObserver.
    assert.equal(es('Enviar solicitação'), 'Enviar solicitud'); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo MutationObserver.
    assert.equal(en('Selecione uma categoria'), 'Select a category'); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo MutationObserver.
    assert.equal(es('Caixa de entrada'), 'Bandeja de entrada'); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo MutationObserver.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('traduz avisos, confirmações e mensagens da API sem alterar texto livre', () => { // Explicação DevIN: Continua expressão ou bloco pertencente a tipo MutationObserver.
    const en = translator('en'); // Explicação DevIN: Declara variável JavaScript en para reutilizar valor ou resultado.
    const es = translator('es'); // Explicação DevIN: Declara variável JavaScript es para reutilizar valor ou resultado.
    assert.equal(en('Encerrar este chamado? As mensagens continuam registradas.'), 'Close this request? The messages will remain saved.'); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo MutationObserver.
    assert.equal(es('O suporte ainda não foi configurado no banco. Aplique a migração de suporte.'), 'El soporte aún no está configurado en la base de datos. Aplica la migración de soporte.'); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo MutationObserver.
    assert.equal(en('Descrição enviada'), 'Submitted description'); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo MutationObserver.
    assert.equal(es('Histórico do atendimento'), 'Historial de atención'); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo MutationObserver.
    const userMessage = 'Meu problema com o pedido #123 não está na lista.'; // Explicação DevIN: Declara variável JavaScript userMessage para reutilizar valor ou resultado.
    assert.equal(en(userMessage), userMessage); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo MutationObserver.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('mantém o português original e traduz textos dinâmicos pelo mesmo helper', () => { // Explicação DevIN: Continua expressão ou bloco pertencente a tipo MutationObserver.
    const pt = translator('pt-BR'); // Explicação DevIN: Declara variável JavaScript pt para reutilizar valor ou resultado.
    assert.equal(pt('Meus chamados'), 'Meus chamados'); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo MutationObserver.
    const ui = read('js/support-ui.js'); // Explicação DevIN: Declara variável JavaScript ui para reutilizar valor ou resultado.
    assert.match(ui, /window\.DevINTranslate/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo MutationObserver.
    assert.match(ui, /t\('caracteres'\)/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo MutationObserver.
    assert.match(ui, /t\('Encerrar este chamado/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo MutationObserver.
    assert.doesNotMatch(ui, /innerHTML\s*=/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo MutationObserver.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
