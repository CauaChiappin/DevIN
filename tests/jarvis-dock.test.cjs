'use strict';
const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const Dock = require('../js/jarvis-dock.js');

test('encaixa esquerda e direita pela posição central em pixels', () => {
  const l = Dock.snap(110, 500, 390, 844, 62, 62);
  const r = Dock.snap(300, 500, 390, 844, 62, 62);
  assert.deepEqual({side:l.side,left:l.left,top:l.top},{side:'left',left:12,top:500});
  assert.deepEqual({side:r.side,left:r.left,top:r.top},{side:'right',left:316,top:500});
  assert.equal(Dock.snap(195, 500, 390, 844, 62, 62).side,'right');
});

test('arraste mantém altura e limita posições para impedir sumiço do botão', () => {
  assert.equal(Dock.snap(-200,-100,320,568,62,62).top,12);
  assert.equal(Dock.snap(999,9999,320,568,62,62).top,494);
  assert.equal(Dock.snap(999,500,320,568,62,62).left,246);
  assert.equal(Dock.snap(-99,500,320,568,62,62).left,12);
});

test('após girar o celular mantém o lado e a altura proporcional', () => {
  const original=Dock.snap(300,220,390,844,62,62);
  const rotated=Dock.restore({side:original.side,yRatio:original.yRatio},844,390,62,62);
  assert.equal(rotated.side,'right');
  assert.equal(rotated.left,770);
  assert.ok(rotated.top>=12 && rotated.top<=316);
  const back=Dock.restore({side:rotated.side,yRatio:rotated.yRatio},390,844,62,62);
  assert.equal(back.top,original.top);
});

test('dados de armazenamento inválidos não permitem fugir da tela', () => {
  assert.equal(Dock.restore({side:'unknown',yRatio:-100},390,844,62,62).side,'left');
  assert.equal(Dock.restore({side:'right',yRatio:Infinity},390,844,62,62).top,770);
  assert.equal(Dock.restore(null,390,844,62,62).top,770);
});

test('botão usa imagem JD real sem legenda visual e mantém acessibilidade', () => {
  const component = fs.readFileSync(path.join(__dirname,'../php/components/jarvis-admin-widget.php'),'utf8');
  const launcher=component.match(/<button class="jarvis-admin__launcher"[^>]*>([\s\S]*?)<\/button>/);
  assert.ok(launcher,'botão do widget existe');
  assert.match(launcher[0],/aria-label="Abrir JarvisDevIN;/);
  assert.match(launcher[1],/jarvisdevin-jd\.webp/);
  assert.doesNotMatch(launcher[1],/jarvis-admin__launcher-label|<span/);
  const source=fs.readFileSync(path.join(__dirname,'../js/jarvis-admin.js'),'utf8');
  assert.match(source,/pointerdown/);
  assert.match(source,/pointermove/);
  assert.match(source,/pointercancel/);
  assert.match(source,/suppressDragClick/);
  const image=fs.readFileSync(path.join(__dirname,'../img/jarvisdevin-jd.webp'));
  assert.equal(image.toString('ascii',0,4),'RIFF');
  assert.equal(image.toString('ascii',8,12),'WEBP');
});
