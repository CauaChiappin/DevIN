/*
 * REVISÃO DETALHADA DEVIN — tests/jarvis-dock.test.cjs
 * Finalidade: jarvis dock.test.
 * Domínio: assistência administrativa.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */

'use strict'; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
const test = require('node:test'); // Explicação DevIN: Declara variável JavaScript test para reutilizar valor ou resultado.
const assert = require('node:assert/strict'); // Explicação DevIN: Declara variável JavaScript assert para reutilizar valor ou resultado.
const fs = require('node:fs'); // Explicação DevIN: Declara variável JavaScript fs para reutilizar valor ou resultado.
const path = require('node:path'); // Explicação DevIN: Declara variável JavaScript path para reutilizar valor ou resultado.
const Dock = require('../js/jarvis-dock.js'); // Explicação DevIN: Declara variável JavaScript Dock para reutilizar valor ou resultado.

test('encaixa esquerda e direita pela posição central em pixels', () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  const l = Dock.snap(110, 500, 390, 844, 62, 62); // Explicação DevIN: Declara variável JavaScript l para reutilizar valor ou resultado.
  const r = Dock.snap(300, 500, 390, 844, 62, 62); // Explicação DevIN: Declara variável JavaScript r para reutilizar valor ou resultado.
  assert.deepEqual({side:l.side,left:l.left,top:l.top},{side:'left',left:12,top:500}); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.deepEqual({side:r.side,left:r.left,top:r.top},{side:'right',left:316,top:500}); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.equal(Dock.snap(195, 500, 390, 844, 62, 62).side,'right'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('arraste mantém altura e limita posições para impedir sumiço do botão', () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.equal(Dock.snap(-200,-100,320,568,62,62).top,12); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.equal(Dock.snap(999,9999,320,568,62,62).top,494); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.equal(Dock.snap(999,500,320,568,62,62).left,246); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.equal(Dock.snap(-99,500,320,568,62,62).left,12); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('após girar o celular mantém o lado e a altura proporcional', () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  const original=Dock.snap(300,220,390,844,62,62); // Explicação DevIN: Declara variável JavaScript original para reutilizar valor ou resultado.
  const rotated=Dock.restore({side:original.side,yRatio:original.yRatio},844,390,62,62); // Explicação DevIN: Declara variável JavaScript rotated para reutilizar valor ou resultado.
  assert.equal(rotated.side,'right'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.equal(rotated.left,770); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.ok(rotated.top>=12 && rotated.top<=316); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  const back=Dock.restore({side:rotated.side,yRatio:rotated.yRatio},390,844,62,62); // Explicação DevIN: Declara variável JavaScript back para reutilizar valor ou resultado.
  assert.equal(back.top,original.top); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('dados de armazenamento inválidos não permitem fugir da tela', () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.equal(Dock.restore({side:'unknown',yRatio:-100},390,844,62,62).side,'left'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.equal(Dock.restore({side:'right',yRatio:Infinity},390,844,62,62).top,770); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.equal(Dock.restore(null,390,844,62,62).top,770); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('botão usa imagem JD real sem legenda visual e mantém acessibilidade', () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  const component = fs.readFileSync(path.join(__dirname,'../php/components/jarvis-admin-widget.php'),'utf8'); // Explicação DevIN: Declara variável JavaScript component para reutilizar valor ou resultado.
  const launcher=component.match(/<button class="jarvis-admin__launcher"[^>]*>([\s\S]*?)<\/button>/); // Explicação DevIN: Declara variável JavaScript launcher para reutilizar valor ou resultado.
  assert.ok(launcher,'botão do widget existe'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(launcher[0],/aria-label="Abrir JarvisDevIN;/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(launcher[1],/jarvisdevin-jd\.webp/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.doesNotMatch(launcher[1],/jarvis-admin__launcher-label|<span/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  const source=fs.readFileSync(path.join(__dirname,'../js/jarvis-admin.js'),'utf8'); // Explicação DevIN: Declara variável JavaScript source para reutilizar valor ou resultado.
  assert.match(source,/pointerdown/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(source,/pointermove/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(source,/pointercancel/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(source,/suppressDragClick/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  const image=fs.readFileSync(path.join(__dirname,'../img/jarvisdevin-jd.webp')); // Explicação DevIN: Declara variável JavaScript image para reutilizar valor ou resultado.
  assert.equal(image.toString('ascii',0,4),'RIFF'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.equal(image.toString('ascii',8,12),'WEBP'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
