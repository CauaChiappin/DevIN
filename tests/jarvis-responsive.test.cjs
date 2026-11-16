'use strict'; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.

const test = require('node:test'); // Explicação DevIN: Declara variável JavaScript test para reutilizar valor ou resultado.
const assert = require('node:assert/strict'); // Explicação DevIN: Declara variável JavaScript assert para reutilizar valor ou resultado.
const fs = require('node:fs'); // Explicação DevIN: Declara variável JavaScript fs para reutilizar valor ou resultado.
const path = require('node:path'); // Explicação DevIN: Declara variável JavaScript path para reutilizar valor ou resultado.

const css = fs.readFileSync(path.join(__dirname,'../css/jarvis-admin.css'),'utf8'); // Explicação DevIN: Declara variável JavaScript css para reutilizar valor ou resultado.
const html = fs.readFileSync(path.join(__dirname,'../php/components/jarvis-admin-widget.php'),'utf8'); // Explicação DevIN: Declara variável JavaScript html para reutilizar valor ou resultado.
const js = fs.readFileSync(path.join(__dirname,'../js/jarvis-admin.js'),'utf8'); // Explicação DevIN: Declara variável JavaScript js para reutilizar valor ou resultado.
const dashboardJs = fs.readFileSync(path.join(__dirname,'../js/dashboard.js'),'utf8'); // Explicação DevIN: Declara variável JavaScript dashboardJs para reutilizar valor ou resultado.

function blocks(selector) { // Explicação DevIN: Declara função/método blocks, com seus parâmetros e comportamento.
  const normalized = selector.replace(/[.*+?^${}()|[\]\\]/g,'\\$&'); // Explicação DevIN: Declara variável JavaScript normalized para reutilizar valor ou resultado.
  const expression = new RegExp(normalized+'\\s*\\{([^}]+)\\}', 'g'); // Explicação DevIN: Declara variável JavaScript expression para reutilizar valor ou resultado.
  let match; // Explicação DevIN: Declara variável JavaScript match para reutilizar valor ou resultado.
  const result = []; // Explicação DevIN: Declara variável JavaScript result para reutilizar valor ou resultado.
  while ((match = expression.exec(css))) result.push(match[1]); // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
  return result; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
function hasRule(selector, expression) { // Explicação DevIN: Declara função/método hasRule, com seus parâmetros e comportamento.
  return blocks(selector).some(block => expression.test(block)); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('envio tem somente um ícone pequeno e descrição acessível', () => { // Explicação DevIN: Continua expressão ou bloco pertencente a função hasRule.
  assert.match(html, /<button[^>]*type="submit"[^>]*aria-label="Enviar mensagem"[^>]*data-jarvis-send>➤<\/button>/); // Explicação DevIN: Continua expressão ou bloco pertencente a função hasRule.
  assert.ok(js.includes("const send = $('[data-jarvis-send]');")); // Explicação DevIN: Continua expressão ou bloco pertencente a função hasRule.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('formulários do JARVIS não recebem loading genérico do dashboard', () => { // Explicação DevIN: Continua expressão ou bloco pertencente a função hasRule.
  assert.match(dashboardJs, /form\.closest\(['"]\.jarvis-admin['"]\)/); // Explicação DevIN: Continua expressão ou bloco pertencente a função hasRule.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('textarea encolhe e o botão mantém largura estável', () => { // Explicação DevIN: Continua expressão ou bloco pertencente a função hasRule.
  const composer='.jarvis-admin__composer'; // Explicação DevIN: Declara variável JavaScript composer para reutilizar valor ou resultado.
  const textarea='.jarvis-admin__composer textarea'; // Explicação DevIN: Declara variável JavaScript textarea para reutilizar valor ou resultado.
  const button='.jarvis-admin__composer button[data-jarvis-send]'; // Explicação DevIN: Declara variável JavaScript button para reutilizar valor ou resultado.
  assert.ok(hasRule(composer,/min-width\s*:\s*0/)); // Explicação DevIN: Continua expressão ou bloco pertencente a função hasRule.
  assert.ok(hasRule(composer,/width\s*:\s*100%/)); // Explicação DevIN: Continua expressão ou bloco pertencente a função hasRule.
  assert.ok(hasRule(textarea,/min-width\s*:\s*0/)); // Explicação DevIN: Continua expressão ou bloco pertencente a função hasRule.
  assert.ok(hasRule(textarea,/flex\s*:\s*1\s+1\s+0%/)); // Explicação DevIN: Continua expressão ou bloco pertencente a função hasRule.
  assert.ok(hasRule(button,/min-width\s*:\s*44px/)); // Explicação DevIN: Continua expressão ou bloco pertencente a função hasRule.
  assert.ok(hasRule(button,/max-width\s*:\s*44px/)); // Explicação DevIN: Continua expressão ou bloco pertencente a função hasRule.
  assert.ok(hasRule(button,/flex\s*:\s*0\s+0\s+44px/)); // Explicação DevIN: Continua expressão ou bloco pertencente a função hasRule.
  assert.ok(hasRule(button,/overflow\s*:\s*hidden/)); // Explicação DevIN: Continua expressão ou bloco pertencente a função hasRule.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('seleção de conversa e botões de ação não forçam largura do painel', () => { // Explicação DevIN: Continua expressão ou bloco pertencente a função hasRule.
  assert.ok(hasRule('.jarvis-admin__conversation-bar select',/min-width\s*:\s*0/)); // Explicação DevIN: Continua expressão ou bloco pertencente a função hasRule.
  assert.ok(hasRule('.jarvis-admin__conversation-bar button',/max-width\s*:\s*38px/)); // Explicação DevIN: Continua expressão ou bloco pertencente a função hasRule.
  assert.ok(hasRule('.jarvis-admin__quick-actions button',/max-width\s*:\s*100%/)); // Explicação DevIN: Continua expressão ou bloco pertencente a função hasRule.
  assert.ok(hasRule('.jarvis-admin__tool-actions button',/max-width\s*:\s*100%/)); // Explicação DevIN: Continua expressão ou bloco pertencente a função hasRule.
  assert.match(css,/\.jarvis-admin__tabs\s*\{[^}]*overflow-x\s*:\s*auto/s); // Explicação DevIN: Continua expressão ou bloco pertencente a função hasRule.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('abas usam grade responsiva sem cortar os títulos', () => { // Explicação DevIN: Continua expressão ou bloco pertencente a função hasRule.
  assert.ok(hasRule('.jarvis-admin__tabs', /grid-template-columns\s*:\s*repeat\(5,\s*minmax\(0,\s*1fr\)\)/)); // Explicação DevIN: Continua expressão ou bloco pertencente a função hasRule.
  assert.ok(hasRule('.jarvis-admin__tabs', /grid-template-columns\s*:\s*repeat\(3,\s*minmax\(0,\s*1fr\)\)/)); // Explicação DevIN: Continua expressão ou bloco pertencente a função hasRule.
  assert.ok(hasRule('.jarvis-admin__tabs button', /min-width\s*:\s*0/)); // Explicação DevIN: Continua expressão ou bloco pertencente a função hasRule.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('mobile permanece fullscreen e tem ajuste especial para 320px', () => { // Explicação DevIN: Continua expressão ou bloco pertencente a função hasRule.
  assert.match(css,/@media\s*\(max-width:600px\)/); // Explicação DevIN: Continua expressão ou bloco pertencente a função hasRule.
  assert.match(css,/\.jarvis-admin__panel,\.jarvis-admin__panel\.is-expanded\s*\{[^}]*width:100vw[^}]*height:100dvh/s); // Explicação DevIN: Continua expressão ou bloco pertencente a função hasRule.
  assert.match(css,/@media\s*\(max-width:360px\)/); // Explicação DevIN: Continua expressão ou bloco pertencente a função hasRule.
  assert.ok(hasRule('.jarvis-admin__composer button[data-jarvis-send]',/width\s*:\s*42px/)); // Explicação DevIN: Continua expressão ou bloco pertencente a função hasRule.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
