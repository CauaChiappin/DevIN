/* Geometria pura do encaixe do botão JarvisDevIN, sem dependência do DOM. */
(function (host, factory) { // Explicação DevIN: Define função anônima usada como operação ou callback.
  'use strict'; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  const docking = factory(); // Explicação DevIN: Declara variável JavaScript docking para reutilizar valor ou resultado.
  if (typeof module === 'object' && module.exports) module.exports = docking; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
  if (host) host.DevInJarvisDock = docking; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
})(typeof window === 'undefined' ? null : window, function () { // Explicação DevIN: Define função anônima usada como operação ou callback.
  'use strict'; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  const MARGIN = 12; // Explicação DevIN: Declara variável JavaScript MARGIN para reutilizar valor ou resultado.
  function clamp(value, low, high) { // Explicação DevIN: Declara função/método clamp, com seus parâmetros e comportamento.
    return Math.max(low, Math.min(high, value)); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  function snap(centerX, desiredTop, viewportWidth, viewportHeight, width, height, margin = MARGIN) { // Explicação DevIN: Declara função/método snap, com seus parâmetros e comportamento.
    const safeWidth = Math.max(1, viewportWidth); // Explicação DevIN: Declara variável JavaScript safeWidth para reutilizar valor ou resultado.
    const safeHeight = Math.max(1, viewportHeight); // Explicação DevIN: Declara variável JavaScript safeHeight para reutilizar valor ou resultado.
    const safeMargin = Math.max(0, margin); // Explicação DevIN: Declara variável JavaScript safeMargin para reutilizar valor ou resultado.
    const maxLeft = Math.max(0, safeWidth - Math.max(0, width) - safeMargin); // Explicação DevIN: Declara variável JavaScript maxLeft para reutilizar valor ou resultado.
    const maxTop = Math.max(0, safeHeight - Math.max(0, height) - safeMargin); // Explicação DevIN: Declara variável JavaScript maxTop para reutilizar valor ou resultado.
    const leftEdge = Math.min(safeMargin, maxLeft); // Explicação DevIN: Declara variável JavaScript leftEdge para reutilizar valor ou resultado.
    const rightEdge = Math.max(leftEdge, maxLeft); // Explicação DevIN: Declara variável JavaScript rightEdge para reutilizar valor ou resultado.
    const minTop = Math.min(safeMargin, maxTop); // Explicação DevIN: Declara variável JavaScript minTop para reutilizar valor ou resultado.
    const side = centerX < safeWidth / 2 ? 'left' : 'right'; // Explicação DevIN: Declara variável JavaScript side para reutilizar valor ou resultado.
    const top = clamp(desiredTop, minTop, maxTop); // Explicação DevIN: Declara variável JavaScript top para reutilizar valor ou resultado.
    const range = maxTop - minTop; // Explicação DevIN: Declara variável JavaScript range para reutilizar valor ou resultado.
    return { // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
      side, // Explicação DevIN: Continua expressão ou bloco pertencente a função snap.
      left: side === 'left' ? leftEdge : rightEdge, // Explicação DevIN: Continua expressão ou bloco pertencente a função snap.
      top, // Explicação DevIN: Continua expressão ou bloco pertencente a função snap.
      yRatio: range > 0 ? (top - minTop) / range : 0 // Explicação DevIN: Continua expressão ou bloco pertencente a função snap.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  function restore(saved, viewportWidth, viewportHeight, width, height, margin = MARGIN) { // Explicação DevIN: Declara função/método restore, com seus parâmetros e comportamento.
    const side = saved && saved.side === 'right' ? 'right' : 'left'; // Explicação DevIN: Declara variável JavaScript side para reutilizar valor ou resultado.
    const ratio = saved && Number.isFinite(saved.yRatio) // Explicação DevIN: Declara variável JavaScript ratio para reutilizar valor ou resultado.
      ? clamp(saved.yRatio, 0, 1) : 1; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    const maxTop = Math.max(0, viewportHeight - height - margin); // Explicação DevIN: Declara variável JavaScript maxTop para reutilizar valor ou resultado.
    const minTop = Math.min(margin, maxTop); // Explicação DevIN: Declara variável JavaScript minTop para reutilizar valor ou resultado.
    const top = minTop + ratio * (maxTop - minTop); // Explicação DevIN: Declara variável JavaScript top para reutilizar valor ou resultado.
    return snap(side === 'right' ? viewportWidth : 0, // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
      top, viewportWidth, viewportHeight, width, height, margin); // Explicação DevIN: Continua expressão ou bloco pertencente a função restore.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  return {snap, restore, clamp, MARGIN}; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
