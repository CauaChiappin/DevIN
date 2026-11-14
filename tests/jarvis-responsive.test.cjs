'use strict';

const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');

const css = fs.readFileSync(path.join(__dirname,'../css/jarvis-admin.css'),'utf8');
const html = fs.readFileSync(path.join(__dirname,'../php/components/jarvis-admin-widget.php'),'utf8');
const js = fs.readFileSync(path.join(__dirname,'../js/jarvis-admin.js'),'utf8');

function lastBlock(selector) {
  const normalized = selector.replace(/[.*+?^${}()|[\]\\]/g,'\\$&');
  const expression = new RegExp(normalized+'\\s*\\{([^}]+)\\}', 'g');
  let match, result = '';
  while ((match = expression.exec(css))) result = match[1];
  return result;
}

test('envio tem somente um ícone pequeno e descrição acessível', () => {
  assert.match(html, /<button[^>]*type="submit"[^>]*aria-label="Enviar mensagem"[^>]*data-jarvis-send>➤<\/button>/);
  assert.doesNotMatch(js,/\bsend\.textContent\s*=/);
});

test('textarea encolhe e o botão mantém largura estável', () => {
  const composer=lastBlock('.jarvis-admin__composer');
  const textarea=lastBlock('.jarvis-admin__composer textarea');
  const button=lastBlock('.jarvis-admin__composer button[data-jarvis-send]');
  assert.match(composer,/min-width\s*:\s*0/);
  assert.match(composer,/width\s*:\s*100%/);
  assert.match(textarea,/min-width\s*:\s*0/);
  assert.match(textarea,/flex\s*:\s*1\s+1\s+0%/);
  assert.match(button,/min-width\s*:\s*44px/);
  assert.match(button,/max-width\s*:\s*44px/);
  assert.match(button,/flex\s*:\s*0\s+0\s+44px/);
  assert.match(button,/overflow\s*:\s*hidden/);
});

test('seleção de conversa e botões de ação não forçam largura do painel', () => {
  assert.match(lastBlock('.jarvis-admin__conversation-bar select'),/min-width\s*:\s*0/);
  assert.match(lastBlock('.jarvis-admin__conversation-bar button'),/max-width\s*:\s*38px/);
  assert.match(lastBlock('.jarvis-admin__quick-actions button'),/max-width\s*:\s*100%/);
  assert.match(lastBlock('.jarvis-admin__tool-actions button'),/max-width\s*:\s*100%/);
  assert.match(css,/\.jarvis-admin__tabs\s*\{[^}]*overflow-x\s*:\s*auto/s);
});

test('mobile permanece fullscreen e tem ajuste especial para 320px', () => {
  assert.match(css,/@media\s*\(max-width:600px\)/);
  assert.match(css,/\.jarvis-admin__panel,\.jarvis-admin__panel\.is-expanded\s*\{[^}]*width:100vw[^}]*height:100dvh/s);
  assert.match(css,/@media\s*\(max-width:360px\)/);
  assert.match(lastBlock('.jarvis-admin__composer button[data-jarvis-send]'),/width\s*:\s*42px/);
});
