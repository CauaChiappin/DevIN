/**
 * Regressões do código Konami: erros reiniciam silenciosamente, sem bloquear
 * setas ou teclado de formulários. Executa o script real com DOM simulado;
 * apenas fetch usa resposta falsa para não depender do servidor remoto.
 */
const test = require('node:test'); // Explicação DevIN: Declara variável JavaScript test para reutilizar valor ou resultado.
const assert = require('node:assert/strict'); // Explicação DevIN: Declara variável JavaScript assert para reutilizar valor ou resultado.
const fs = require('node:fs'); // Explicação DevIN: Declara variável JavaScript fs para reutilizar valor ou resultado.
const path = require('node:path'); // Explicação DevIN: Declara variável JavaScript path para reutilizar valor ou resultado.
const vm = require('node:vm'); // Explicação DevIN: Declara variável JavaScript vm para reutilizar valor ou resultado.

const sequence = ['ArrowUp','ArrowUp','ArrowDown','ArrowDown','ArrowLeft','ArrowRight','ArrowLeft','ArrowRight','KeyB','KeyA','Enter']; // Explicação DevIN: Declara variável JavaScript sequence para reutilizar valor ou resultado.
function browser() { // Explicação DevIN: Declara função/método browser, com seus parâmetros e comportamento.
    const listeners = {}; // Explicação DevIN: Declara variável JavaScript listeners para reutilizar valor ou resultado.
    const notifications = []; // Explicação DevIN: Declara variável JavaScript notifications para reutilizar valor ou resultado.
    const requests = []; // Explicação DevIN: Declara variável JavaScript requests para reutilizar valor ou resultado.
    const redirects = []; // Explicação DevIN: Declara variável JavaScript redirects para reutilizar valor ou resultado.
    class HTMLElement { constructor(tagName='DIV', editable=false) { this.tagName=tagName; this.isContentEditable=editable; } } // Explicação DevIN: Declara o tipo HTMLElement e inicia sua estrutura.
    const context = { // Explicação DevIN: Declara variável JavaScript context para reutilizar valor ou resultado.
        HTMLElement, // Explicação DevIN: Continua expressão ou bloco pertencente a tipo HTMLElement.
        document: { // Explicação DevIN: Continua expressão ou bloco pertencente a tipo HTMLElement.
            addEventListener(name, callback) { listeners[name] = callback; }, // Explicação DevIN: Continua expressão ou bloco pertencente a tipo HTMLElement.
            querySelector() { return null; }, // Explicação DevIN: Continua expressão ou bloco pertencente a tipo HTMLElement.
            createElement() { return {dataset:{},setAttribute(){},className:'',textContent:'',hidden:true}; }, // Explicação DevIN: Continua expressão ou bloco pertencente a tipo HTMLElement.
            body: {appendChild(item) { notifications.push(item); }}, // Explicação DevIN: Continua expressão ou bloco pertencente a tipo HTMLElement.
            hidden: false, // Explicação DevIN: Continua expressão ou bloco pertencente a tipo HTMLElement.
        }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        window: { location: { assign(url) { redirects.push(url); } }, addEventListener(name,fn) { listeners[name]=fn; } }, // Explicação DevIN: Continua expressão ou bloco pertencente a tipo HTMLElement.
        fetch: async url => { // Explicação DevIN: Continua expressão ou bloco pertencente a tipo HTMLElement.
            requests.push(url); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo HTMLElement.
            return {ok:true, json:async()=>url.includes('csrf.php') ? {sucesso:true,token:'local-test'} : {sucesso:true,url:'/php/arcade.php'}}; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        URLSearchParams, // Explicação DevIN: Continua expressão ou bloco pertencente a tipo HTMLElement.
        JSON, // Explicação DevIN: Continua expressão ou bloco pertencente a tipo HTMLElement.
        Error, // Explicação DevIN: Continua expressão ou bloco pertencente a tipo HTMLElement.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    vm.runInNewContext(fs.readFileSync(path.resolve(__dirname,'../js/konami-arcade.js'),'utf8'), context); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo HTMLElement.
    function press(code,target=new HTMLElement()) { // Explicação DevIN: Declara função/método press, com seus parâmetros e comportamento.
        let prevented=false; // Explicação DevIN: Declara variável JavaScript prevented para reutilizar valor ou resultado.
        listeners.keydown({code,target,repeat:false,preventDefault(){prevented=true;}}); // Explicação DevIN: Continua expressão ou bloco pertencente a função press.
        return prevented; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    return {press,HTMLElement,requests,redirects,notifications}; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('errar uma tecla reinicia sem banner, rede ou bloqueio de setas', () => { // Explicação DevIN: Continua expressão ou bloco pertencente a função press.
    const env=browser(); // Explicação DevIN: Declara variável JavaScript env para reutilizar valor ou resultado.
    assert.equal(env.press('ArrowUp'),false); // Explicação DevIN: Continua expressão ou bloco pertencente a função press.
    assert.equal(env.press('ArrowUp'),false); // Explicação DevIN: Continua expressão ou bloco pertencente a função press.
    assert.equal(env.press('ArrowLeft'),false); // Explicação DevIN: Continua expressão ou bloco pertencente a função press.
    for (const code of sequence.slice(2)) env.press(code); // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
    assert.equal(env.requests.length,0); // Explicação DevIN: Continua expressão ou bloco pertencente a função press.
    assert.equal(env.notifications.length,0); // Explicação DevIN: Continua expressão ou bloco pertencente a função press.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('após erro, uma nova sequência correta funciona sem recarregar a página', async () => { // Explicação DevIN: Continua expressão ou bloco pertencente a função press.
    const env=browser(); // Explicação DevIN: Declara variável JavaScript env para reutilizar valor ou resultado.
    env.press('ArrowUp'); env.press('KeyX'); // Explicação DevIN: Continua expressão ou bloco pertencente a função press.
    for (const code of sequence.slice(0,-1)) assert.equal(env.press(code),false); // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
    assert.equal(env.press('Enter'),true); // Explicação DevIN: Continua expressão ou bloco pertencente a função press.
    await new Promise(resolve=>setImmediate(resolve)); // Explicação DevIN: Continua expressão ou bloco pertencente a função press.
    assert.equal(env.requests.length,2); // Explicação DevIN: Continua expressão ou bloco pertencente a função press.
    assert.equal(env.redirects.length,1); // Explicação DevIN: Continua expressão ou bloco pertencente a função press.
    assert.equal(env.notifications.length,0); // Explicação DevIN: Continua expressão ou bloco pertencente a função press.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('digitar num campo durante a sequência cancela o progresso anterior', () => { // Explicação DevIN: Continua expressão ou bloco pertencente a função press.
    const env=browser(); // Explicação DevIN: Declara variável JavaScript env para reutilizar valor ou resultado.
    env.press('ArrowUp'); env.press('ArrowUp'); // Explicação DevIN: Continua expressão ou bloco pertencente a função press.
    env.press('KeyX',new env.HTMLElement('INPUT')); // Explicação DevIN: Continua expressão ou bloco pertencente a função press.
    for (const code of sequence.slice(2)) env.press(code); // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
    assert.equal(env.requests.length,0); // Explicação DevIN: Continua expressão ou bloco pertencente a função press.
    assert.equal(env.notifications.length,0); // Explicação DevIN: Continua expressão ou bloco pertencente a função press.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
