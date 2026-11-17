/*
 * REVISÃO DETALHADA DEVIN — js/doom.js
 * Finalidade: doom.
 * Domínio: interações do navegador.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */

(function () { // Explicação DevIN: Define função anônima usada como operação ou callback.
  'use strict'; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.

  const canvas = document.querySelector('[data-doom-canvas], #doom-canvas'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
  // O jogo só cria controles e loop de desenho se o canvas estiver presente.
  if (!canvas) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

  const context = canvas.getContext('2d'); // Explicação DevIN: Declara variável JavaScript context para reutilizar valor ou resultado.
  const healthOutput = document.querySelector('[data-doom-health]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
  const scoreOutput = document.querySelector('[data-doom-score]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
  const enemiesOutput = document.querySelector('[data-doom-enemies]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
  const message = document.querySelector('[data-doom-message]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
  const restartButton = document.querySelector('[data-doom-restart]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.

  const map = [ // Explicação DevIN: Declara variável JavaScript map para reutilizar valor ou resultado.
    '############', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '#..........#', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '#.##.###...#', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '#....#.....#', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '#.##.#.###.#', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '#....#.....#', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '#.####.##..#', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '#..........#', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '############' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
  const mapHeight = map.length; // Explicação DevIN: Declara variável JavaScript mapHeight para reutilizar valor ou resultado.
  const mapWidth = map[0].length; // Explicação DevIN: Declara variável JavaScript mapWidth para reutilizar valor ou resultado.
  const keys = new Set(); // Explicação DevIN: Declara variável JavaScript keys para reutilizar valor ou resultado.
  const colors = ['#ef4444', '#22d3ee', '#f472b6', '#a3e635', '#fb923c']; // Explicação DevIN: Declara variável JavaScript colors para reutilizar valor ou resultado.
  let player; // Explicação DevIN: Declara variável JavaScript player para reutilizar valor ou resultado.
  let enemies; // Explicação DevIN: Declara variável JavaScript enemies para reutilizar valor ou resultado.
  let shots; // Explicação DevIN: Declara variável JavaScript shots para reutilizar valor ou resultado.
  let score; // Explicação DevIN: Declara variável JavaScript score para reutilizar valor ou resultado.
  let gameState; // Explicação DevIN: Declara variável JavaScript gameState para reutilizar valor ou resultado.
  let lastTime = 0; // Explicação DevIN: Declara variável JavaScript lastTime para reutilizar valor ou resultado.
  let fireCooldown = 0; // Explicação DevIN: Declara variável JavaScript fireCooldown para reutilizar valor ou resultado.
  let damageCooldown = 0; // Explicação DevIN: Declara variável JavaScript damageCooldown para reutilizar valor ou resultado.

  // Reinicia jogador, inimigos, projéteis e indicadores para iniciar uma partida limpa.
  // Detalhe DevIN: Método resetGame: encapsula uma responsabilidade reutilizável do módulo no contexto de doom. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
  function resetGame() { // Explicação DevIN: Declara função/método resetGame, com seus parâmetros e comportamento.
    player = { x: 2.5, y: 1.5, angle: 0, health: 100 }; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    enemies = [ // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      { x: 9.5, y: 1.5, color: colors[0], health: 1 }, // Explicação DevIN: Continua expressão ou bloco pertencente a função resetGame.
      { x: 4.5, y: 3.5, color: colors[1], health: 1 }, // Explicação DevIN: Continua expressão ou bloco pertencente a função resetGame.
      { x: 9.5, y: 3.5, color: colors[2], health: 1 }, // Explicação DevIN: Continua expressão ou bloco pertencente a função resetGame.
      { x: 2.5, y: 7.5, color: colors[3], health: 1 }, // Explicação DevIN: Continua expressão ou bloco pertencente a função resetGame.
      { x: 9.5, y: 7.5, color: colors[4], health: 1 } // Explicação DevIN: Continua expressão ou bloco pertencente a função resetGame.
    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
    shots = []; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    score = 0; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    gameState = 'playing'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    fireCooldown = 0; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    damageCooldown = 0; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    setMessage(''); // Explicação DevIN: Continua expressão ou bloco pertencente a função resetGame.
    updateHud(); // Explicação DevIN: Continua expressão ou bloco pertencente a função resetGame.
    canvas.focus(); // Explicação DevIN: Continua expressão ou bloco pertencente a função resetGame.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

  // Detalhe DevIN: Método setMessage: encapsula uma responsabilidade reutilizável do módulo no contexto de doom. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
  function setMessage(text) { // Explicação DevIN: Declara função/método setMessage, com seus parâmetros e comportamento.
    if (!message) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    message.textContent = text; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    message.hidden = text === ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

  // Detalhe DevIN: Método updateHud: modifica apenas os dados previstos pela operação no contexto de doom. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
  function updateHud() { // Explicação DevIN: Declara função/método updateHud, com seus parâmetros e comportamento.
    if (healthOutput) healthOutput.textContent = String(Math.max(0, Math.ceil(player.health))); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    if (scoreOutput) scoreOutput.textContent = String(score); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    if (enemiesOutput) enemiesOutput.textContent = String(enemies.length); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

  // Converte coordenadas do jogo em célula do mapa e trata bordas como parede.
  // Detalhe DevIN: Método isWall: verifica condições antes de liberar o próximo passo no contexto de doom. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
  function isWall(x, y) { // Explicação DevIN: Declara função/método isWall, com seus parâmetros e comportamento.
    const column = Math.floor(x); // Explicação DevIN: Declara variável JavaScript column para reutilizar valor ou resultado.
    const row = Math.floor(y); // Explicação DevIN: Declara variável JavaScript row para reutilizar valor ou resultado.
    return row < 0 || row >= mapHeight || column < 0 || column >= mapWidth || map[row][column] === '#'; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

  // Detalhe DevIN: Método canMove: verifica condições antes de liberar o próximo passo no contexto de doom. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
  function canMove(x, y, radius) { // Explicação DevIN: Declara função/método canMove, com seus parâmetros e comportamento.
    // Testa os quatro cantos do corpo para evitar atravessar paredes ao mover-se na diagonal.
    return !isWall(x - radius, y - radius) && !isWall(x + radius, y - radius) // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
      && !isWall(x - radius, y + radius) && !isWall(x + radius, y + radius); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

  // Detalhe DevIN: Método moveEntity: encapsula uma responsabilidade reutilizável do módulo no contexto de doom. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
  function moveEntity(entity, dx, dy, radius) { // Explicação DevIN: Declara função/método moveEntity, com seus parâmetros e comportamento.
    // Resolve os eixos separadamente e permite deslizar pela parede quando apenas um eixo está bloqueado.
    if (canMove(entity.x + dx, entity.y, radius)) entity.x += dx; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    if (canMove(entity.x, entity.y + dy, radius)) entity.y += dy; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

  // Detalhe DevIN: Método pressed: encapsula uma responsabilidade reutilizável do módulo no contexto de doom. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
  function pressed(...names) { // Explicação DevIN: Declara função/método pressed, com seus parâmetros e comportamento.
    return names.some((name) => keys.has(name)); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

  // Atualiza movimento e colisões usando delta em segundos, independente da taxa de quadros.
  // Detalhe DevIN: Método update: modifica apenas os dados previstos pela operação no contexto de doom. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
  function update(delta) { // Explicação DevIN: Declara função/método update, com seus parâmetros e comportamento.
    if (gameState !== 'playing') return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

    const moveX = (pressed('ArrowRight', 'd') ? 1 : 0) - (pressed('ArrowLeft', 'a') ? 1 : 0); // Explicação DevIN: Declara variável JavaScript moveX para reutilizar valor ou resultado.
    const moveY = (pressed('ArrowDown', 's') ? 1 : 0) - (pressed('ArrowUp', 'w') ? 1 : 0); // Explicação DevIN: Declara variável JavaScript moveY para reutilizar valor ou resultado.
    const magnitude = Math.hypot(moveX, moveY) || 1; // Explicação DevIN: Declara variável JavaScript magnitude para reutilizar valor ou resultado.
    const speed = 3 * delta; // Explicação DevIN: Declara variável JavaScript speed para reutilizar valor ou resultado.
    moveEntity(player, (moveX / magnitude) * speed, (moveY / magnitude) * speed, 0.22); // Explicação DevIN: Continua expressão ou bloco pertencente a função update.
    if (moveX || moveY) player.angle = Math.atan2(moveY, moveX); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

    fireCooldown -= delta; // Explicação DevIN: Continua expressão ou bloco pertencente a função update.
    damageCooldown -= delta; // Explicação DevIN: Continua expressão ou bloco pertencente a função update.
    if (pressed(' ', 'Space') && fireCooldown <= 0) fire(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

    // Projéteis expiram ao bater em parede ou inimigo; um acerto também atualiza a pontuação.
    shots.forEach((shot) => { // Explicação DevIN: Continua expressão ou bloco pertencente a função update.
      shot.x += shot.vx * delta; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      shot.y += shot.vy * delta; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      shot.ttl -= delta; // Explicação DevIN: Continua expressão ou bloco pertencente a função update.
      if (isWall(shot.x, shot.y)) shot.ttl = 0; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      enemies.forEach((enemy) => { // Explicação DevIN: Continua expressão ou bloco pertencente a função update.
        if (enemy.health > 0 && Math.hypot(enemy.x - shot.x, enemy.y - shot.y) < 0.35) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
          enemy.health = 0; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
          shot.ttl = 0; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
          score += 100; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    shots = shots.filter((shot) => shot.ttl > 0); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    enemies = enemies.filter((enemy) => enemy.health > 0); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.

    enemies.forEach((enemy) => { // Explicação DevIN: Continua expressão ou bloco pertencente a função update.
      const dx = player.x - enemy.x; // Explicação DevIN: Declara variável JavaScript dx para reutilizar valor ou resultado.
      const dy = player.y - enemy.y; // Explicação DevIN: Declara variável JavaScript dy para reutilizar valor ou resultado.
      const distance = Math.hypot(dx, dy) || 1; // Explicação DevIN: Declara variável JavaScript distance para reutilizar valor ou resultado.
      if (distance > 0.55) moveEntity(enemy, (dx / distance) * 0.8 * delta, (dy / distance) * 0.8 * delta, 0.2); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      if (distance < 0.65 && damageCooldown <= 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        player.health -= 10; // Explicação DevIN: Continua expressão ou bloco pertencente a função update.
        damageCooldown = 0.5; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    if (player.health <= 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      gameState = 'lost'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      setMessage('DERROTA - pressione Reiniciar'); // Explicação DevIN: Continua expressão ou bloco pertencente a função update.
    } else if (enemies.length === 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      gameState = 'won'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      score += 250; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      setMessage('AREA LIMPA - pressione Reiniciar'); // Explicação DevIN: Continua expressão ou bloco pertencente a função update.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    updateHud(); // Explicação DevIN: Continua expressão ou bloco pertencente a função update.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

  // Detalhe DevIN: Método fire: encapsula uma responsabilidade reutilizável do módulo no contexto de doom. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
  function fire() { // Explicação DevIN: Declara função/método fire, com seus parâmetros e comportamento.
    fireCooldown = 0.24; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    shots.push({ // Explicação DevIN: Continua expressão ou bloco pertencente a função fire.
      x: player.x + Math.cos(player.angle) * 0.35, // Explicação DevIN: Continua expressão ou bloco pertencente a função fire.
      y: player.y + Math.sin(player.angle) * 0.35, // Explicação DevIN: Continua expressão ou bloco pertencente a função fire.
      vx: Math.cos(player.angle) * 8, // Explicação DevIN: Continua expressão ou bloco pertencente a função fire.
      vy: Math.sin(player.angle) * 8, // Explicação DevIN: Continua expressão ou bloco pertencente a função fire.
      ttl: 1.2 // Explicação DevIN: Continua expressão ou bloco pertencente a função fire.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

  // Detalhe DevIN: Método resizeCanvas: encapsula uma responsabilidade reutilizável do módulo no contexto de doom. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
  function resizeCanvas() { // Explicação DevIN: Declara função/método resizeCanvas, com seus parâmetros e comportamento.
    const bounds = canvas.getBoundingClientRect(); // Explicação DevIN: Declara variável JavaScript bounds para reutilizar valor ou resultado.
    const ratio = window.devicePixelRatio || 1; // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const width = Math.max(1, Math.floor(bounds.width * ratio)); // Explicação DevIN: Declara variável JavaScript width para reutilizar valor ou resultado.
    const height = Math.max(1, Math.floor(bounds.height * ratio)); // Explicação DevIN: Declara variável JavaScript height para reutilizar valor ou resultado.
    if (canvas.width !== width || canvas.height !== height) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      canvas.width = width; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      canvas.height = height; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    context.setTransform(ratio, 0, 0, ratio, 0, 0); // Explicação DevIN: Continua expressão ou bloco pertencente a função resizeCanvas.
    return { width: bounds.width, height: bounds.height }; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

  // Detalhe DevIN: Método render: transforma ou normaliza dados para o formato esperado no contexto de doom. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
  function render() { // Explicação DevIN: Declara função/método render, com seus parâmetros e comportamento.
    const size = resizeCanvas(); // Explicação DevIN: Declara variável JavaScript size para reutilizar valor ou resultado.
    const cell = Math.min(size.width / mapWidth, size.height / mapHeight); // Explicação DevIN: Declara variável JavaScript cell para reutilizar valor ou resultado.
    const offsetX = (size.width - mapWidth * cell) / 2; // Explicação DevIN: Declara variável JavaScript offsetX para reutilizar valor ou resultado.
    const offsetY = (size.height - mapHeight * cell) / 2; // Explicação DevIN: Declara variável JavaScript offsetY para reutilizar valor ou resultado.

    context.clearRect(0, 0, size.width, size.height); // Explicação DevIN: Continua expressão ou bloco pertencente a função render.
    context.fillStyle = '#05070d'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    context.fillRect(0, 0, size.width, size.height); // Explicação DevIN: Continua expressão ou bloco pertencente a função render.
    context.fillStyle = '#101827'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    context.fillRect(offsetX, offsetY, mapWidth * cell, mapHeight * cell); // Explicação DevIN: Continua expressão ou bloco pertencente a função render.

    map.forEach((row, y) => row.split('').forEach((tile, x) => { // Explicação DevIN: Continua expressão ou bloco pertencente a função render.
      const px = offsetX + x * cell; // Explicação DevIN: Declara variável JavaScript px para reutilizar valor ou resultado.
      const py = offsetY + y * cell; // Explicação DevIN: Declara variável JavaScript py para reutilizar valor ou resultado.
      if (tile === '#') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        context.fillStyle = '#27364d'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        context.fillRect(px + 1, py + 1, cell - 2, cell - 2); // Explicação DevIN: Continua expressão ou bloco pertencente a função render.
        context.strokeStyle = '#526782'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        context.strokeRect(px + 1.5, py + 1.5, cell - 3, cell - 3); // Explicação DevIN: Continua expressão ou bloco pertencente a função render.
      } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
        context.strokeStyle = 'rgba(148, 163, 184, .08)'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        context.strokeRect(px, py, cell, cell); // Explicação DevIN: Continua expressão ou bloco pertencente a função render.
      } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    })); // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.

    const point = (x, y) => ({ x: offsetX + x * cell, y: offsetY + y * cell }); // Explicação DevIN: Declara variável JavaScript point para reutilizar valor ou resultado.
    shots.forEach((shot) => { // Explicação DevIN: Continua expressão ou bloco pertencente a função render.
      const shotPoint = point(shot.x, shot.y); // Explicação DevIN: Declara variável JavaScript shotPoint para reutilizar valor ou resultado.
      context.fillStyle = '#fde047'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      context.beginPath(); // Explicação DevIN: Continua expressão ou bloco pertencente a função render.
      context.arc(shotPoint.x, shotPoint.y, Math.max(2, cell * 0.08), 0, Math.PI * 2); // Explicação DevIN: Continua expressão ou bloco pertencente a função render.
      context.fill(); // Explicação DevIN: Continua expressão ou bloco pertencente a função render.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    enemies.forEach((enemy) => { // Explicação DevIN: Continua expressão ou bloco pertencente a função render.
      const enemyPoint = point(enemy.x, enemy.y); // Explicação DevIN: Declara variável JavaScript enemyPoint para reutilizar valor ou resultado.
      const radius = cell * 0.27; // Explicação DevIN: Declara variável JavaScript radius para reutilizar valor ou resultado.
      context.fillStyle = enemy.color; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      context.beginPath(); // Explicação DevIN: Continua expressão ou bloco pertencente a função render.
      context.arc(enemyPoint.x, enemyPoint.y, radius, 0, Math.PI * 2); // Explicação DevIN: Continua expressão ou bloco pertencente a função render.
      context.fill(); // Explicação DevIN: Continua expressão ou bloco pertencente a função render.
      context.fillStyle = '#111827'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      context.beginPath(); // Explicação DevIN: Continua expressão ou bloco pertencente a função render.
      context.arc(enemyPoint.x - radius * 0.35, enemyPoint.y - radius * 0.12, radius * 0.14, 0, Math.PI * 2); // Explicação DevIN: Continua expressão ou bloco pertencente a função render.
      context.arc(enemyPoint.x + radius * 0.35, enemyPoint.y - radius * 0.12, radius * 0.14, 0, Math.PI * 2); // Explicação DevIN: Continua expressão ou bloco pertencente a função render.
      context.fill(); // Explicação DevIN: Continua expressão ou bloco pertencente a função render.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    const playerPoint = point(player.x, player.y); // Explicação DevIN: Declara variável JavaScript playerPoint para reutilizar valor ou resultado.
    context.save(); // Explicação DevIN: Continua expressão ou bloco pertencente a função render.
    context.translate(playerPoint.x, playerPoint.y); // Explicação DevIN: Continua expressão ou bloco pertencente a função render.
    context.rotate(player.angle); // Explicação DevIN: Continua expressão ou bloco pertencente a função render.
    context.fillStyle = '#facc15'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    context.beginPath(); // Explicação DevIN: Continua expressão ou bloco pertencente a função render.
    context.moveTo(cell * 0.36, 0); // Explicação DevIN: Continua expressão ou bloco pertencente a função render.
    context.lineTo(-cell * 0.26, -cell * 0.28); // Explicação DevIN: Continua expressão ou bloco pertencente a função render.
    context.lineTo(-cell * 0.26, cell * 0.28); // Explicação DevIN: Continua expressão ou bloco pertencente a função render.
    context.closePath(); // Explicação DevIN: Continua expressão ou bloco pertencente a função render.
    context.fill(); // Explicação DevIN: Continua expressão ou bloco pertencente a função render.
    context.restore(); // Explicação DevIN: Continua expressão ou bloco pertencente a função render.

    context.strokeStyle = 'rgba(255, 255, 255, .7)'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    context.beginPath(); // Explicação DevIN: Continua expressão ou bloco pertencente a função render.
    context.moveTo(size.width / 2 - 8, size.height / 2); // Explicação DevIN: Continua expressão ou bloco pertencente a função render.
    context.lineTo(size.width / 2 + 8, size.height / 2); // Explicação DevIN: Continua expressão ou bloco pertencente a função render.
    context.moveTo(size.width / 2, size.height / 2 - 8); // Explicação DevIN: Continua expressão ou bloco pertencente a função render.
    context.lineTo(size.width / 2, size.height / 2 + 8); // Explicação DevIN: Continua expressão ou bloco pertencente a função render.
    context.stroke(); // Explicação DevIN: Continua expressão ou bloco pertencente a função render.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

  // Detalhe DevIN: Método frame: encapsula uma responsabilidade reutilizável do módulo no contexto de doom. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
  function frame(time) { // Explicação DevIN: Declara função/método frame, com seus parâmetros e comportamento.
    const delta = Math.min((time - lastTime) / 1000 || 0, 0.05); // Explicação DevIN: Declara variável JavaScript delta para reutilizar valor ou resultado.
    lastTime = time; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    update(delta); // Explicação DevIN: Continua expressão ou bloco pertencente a função frame.
    render(); // Explicação DevIN: Continua expressão ou bloco pertencente a função frame.
    window.requestAnimationFrame(frame); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

  window.addEventListener('keydown', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', ' ', 'Space'].includes(event.key)) event.preventDefault(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    keys.add(event.key); // Explicação DevIN: Continua expressão ou bloco pertencente a função frame.
    canvas.focus(); // Explicação DevIN: Continua expressão ou bloco pertencente a função frame.
  }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  window.addEventListener('keyup', (event) => keys.delete(event.key)); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
  canvas.addEventListener('click', () => canvas.focus()); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
  window.addEventListener('resize', render); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
  if (restartButton) restartButton.addEventListener('click', resetGame); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

  resetGame(); // Explicação DevIN: Continua expressão ou bloco pertencente a função frame.
  window.requestAnimationFrame(frame); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
}()); // Explicação DevIN: Continua expressão ou bloco pertencente a função frame.
