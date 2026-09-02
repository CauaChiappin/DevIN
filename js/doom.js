(function () {
  'use strict';

  const canvas = document.querySelector('[data-doom-canvas], #doom-canvas');
  if (!canvas) return;

  const context = canvas.getContext('2d');
  const healthOutput = document.querySelector('[data-doom-health]');
  const scoreOutput = document.querySelector('[data-doom-score]');
  const enemiesOutput = document.querySelector('[data-doom-enemies]');
  const message = document.querySelector('[data-doom-message]');
  const restartButton = document.querySelector('[data-doom-restart]');

  const map = [
    '############',
    '#..........#',
    '#.##.###...#',
    '#....#.....#',
    '#.##.#.###.#',
    '#....#.....#',
    '#.####.##..#',
    '#..........#',
    '############'
  ];
  const mapHeight = map.length;
  const mapWidth = map[0].length;
  const keys = new Set();
  const colors = ['#ef4444', '#22d3ee', '#f472b6', '#a3e635', '#fb923c'];
  let player;
  let enemies;
  let shots;
  let score;
  let gameState;
  let lastTime = 0;
  let fireCooldown = 0;
  let damageCooldown = 0;

  function resetGame() {
    player = { x: 2.5, y: 1.5, angle: 0, health: 100 };
    enemies = [
      { x: 9.5, y: 1.5, color: colors[0], health: 1 },
      { x: 4.5, y: 3.5, color: colors[1], health: 1 },
      { x: 9.5, y: 3.5, color: colors[2], health: 1 },
      { x: 2.5, y: 7.5, color: colors[3], health: 1 },
      { x: 9.5, y: 7.5, color: colors[4], health: 1 }
    ];
    shots = [];
    score = 0;
    gameState = 'playing';
    fireCooldown = 0;
    damageCooldown = 0;
    setMessage('');
    updateHud();
    canvas.focus();
  }

  function setMessage(text) {
    if (!message) return;
    message.textContent = text;
    message.hidden = text === '';
  }

  function updateHud() {
    if (healthOutput) healthOutput.textContent = String(Math.max(0, Math.ceil(player.health)));
    if (scoreOutput) scoreOutput.textContent = String(score);
    if (enemiesOutput) enemiesOutput.textContent = String(enemies.length);
  }

  function isWall(x, y) {
    const column = Math.floor(x);
    const row = Math.floor(y);
    return row < 0 || row >= mapHeight || column < 0 || column >= mapWidth || map[row][column] === '#';
  }

  function canMove(x, y, radius) {
    return !isWall(x - radius, y - radius) && !isWall(x + radius, y - radius)
      && !isWall(x - radius, y + radius) && !isWall(x + radius, y + radius);
  }

  function moveEntity(entity, dx, dy, radius) {
    if (canMove(entity.x + dx, entity.y, radius)) entity.x += dx;
    if (canMove(entity.x, entity.y + dy, radius)) entity.y += dy;
  }

  function pressed(...names) {
    return names.some((name) => keys.has(name));
  }

  function update(delta) {
    if (gameState !== 'playing') return;

    const moveX = (pressed('ArrowRight', 'd') ? 1 : 0) - (pressed('ArrowLeft', 'a') ? 1 : 0);
    const moveY = (pressed('ArrowDown', 's') ? 1 : 0) - (pressed('ArrowUp', 'w') ? 1 : 0);
    const magnitude = Math.hypot(moveX, moveY) || 1;
    const speed = 3 * delta;
    moveEntity(player, (moveX / magnitude) * speed, (moveY / magnitude) * speed, 0.22);
    if (moveX || moveY) player.angle = Math.atan2(moveY, moveX);

    fireCooldown -= delta;
    damageCooldown -= delta;
    if (pressed(' ', 'Space') && fireCooldown <= 0) fire();

    shots.forEach((shot) => {
      shot.x += shot.vx * delta;
      shot.y += shot.vy * delta;
      shot.ttl -= delta;
      if (isWall(shot.x, shot.y)) shot.ttl = 0;
      enemies.forEach((enemy) => {
        if (enemy.health > 0 && Math.hypot(enemy.x - shot.x, enemy.y - shot.y) < 0.35) {
          enemy.health = 0;
          shot.ttl = 0;
          score += 100;
        }
      });
    });
    shots = shots.filter((shot) => shot.ttl > 0);
    enemies = enemies.filter((enemy) => enemy.health > 0);

    enemies.forEach((enemy) => {
      const dx = player.x - enemy.x;
      const dy = player.y - enemy.y;
      const distance = Math.hypot(dx, dy) || 1;
      if (distance > 0.55) moveEntity(enemy, (dx / distance) * 0.8 * delta, (dy / distance) * 0.8 * delta, 0.2);
      if (distance < 0.65 && damageCooldown <= 0) {
        player.health -= 10;
        damageCooldown = 0.5;
      }
    });

    if (player.health <= 0) {
      gameState = 'lost';
      setMessage('DERROTA - pressione Reiniciar');
    } else if (enemies.length === 0) {
      gameState = 'won';
      score += 250;
      setMessage('AREA LIMPA - pressione Reiniciar');
    }
    updateHud();
  }

  function fire() {
    fireCooldown = 0.24;
    shots.push({
      x: player.x + Math.cos(player.angle) * 0.35,
      y: player.y + Math.sin(player.angle) * 0.35,
      vx: Math.cos(player.angle) * 8,
      vy: Math.sin(player.angle) * 8,
      ttl: 1.2
    });
  }

  function resizeCanvas() {
    const bounds = canvas.getBoundingClientRect();
    const ratio = window.devicePixelRatio || 1;
    const width = Math.max(1, Math.floor(bounds.width * ratio));
    const height = Math.max(1, Math.floor(bounds.height * ratio));
    if (canvas.width !== width || canvas.height !== height) {
      canvas.width = width;
      canvas.height = height;
    }
    context.setTransform(ratio, 0, 0, ratio, 0, 0);
    return { width: bounds.width, height: bounds.height };
  }

  function render() {
    const size = resizeCanvas();
    const cell = Math.min(size.width / mapWidth, size.height / mapHeight);
    const offsetX = (size.width - mapWidth * cell) / 2;
    const offsetY = (size.height - mapHeight * cell) / 2;

    context.clearRect(0, 0, size.width, size.height);
    context.fillStyle = '#05070d';
    context.fillRect(0, 0, size.width, size.height);
    context.fillStyle = '#101827';
    context.fillRect(offsetX, offsetY, mapWidth * cell, mapHeight * cell);

    map.forEach((row, y) => row.split('').forEach((tile, x) => {
      const px = offsetX + x * cell;
      const py = offsetY + y * cell;
      if (tile === '#') {
        context.fillStyle = '#27364d';
        context.fillRect(px + 1, py + 1, cell - 2, cell - 2);
        context.strokeStyle = '#526782';
        context.strokeRect(px + 1.5, py + 1.5, cell - 3, cell - 3);
      } else {
        context.strokeStyle = 'rgba(148, 163, 184, .08)';
        context.strokeRect(px, py, cell, cell);
      }
    }));

    const point = (x, y) => ({ x: offsetX + x * cell, y: offsetY + y * cell });
    shots.forEach((shot) => {
      const shotPoint = point(shot.x, shot.y);
      context.fillStyle = '#fde047';
      context.beginPath();
      context.arc(shotPoint.x, shotPoint.y, Math.max(2, cell * 0.08), 0, Math.PI * 2);
      context.fill();
    });

    enemies.forEach((enemy) => {
      const enemyPoint = point(enemy.x, enemy.y);
      const radius = cell * 0.27;
      context.fillStyle = enemy.color;
      context.beginPath();
      context.arc(enemyPoint.x, enemyPoint.y, radius, 0, Math.PI * 2);
      context.fill();
      context.fillStyle = '#111827';
      context.beginPath();
      context.arc(enemyPoint.x - radius * 0.35, enemyPoint.y - radius * 0.12, radius * 0.14, 0, Math.PI * 2);
      context.arc(enemyPoint.x + radius * 0.35, enemyPoint.y - radius * 0.12, radius * 0.14, 0, Math.PI * 2);
      context.fill();
    });

    const playerPoint = point(player.x, player.y);
    context.save();
    context.translate(playerPoint.x, playerPoint.y);
    context.rotate(player.angle);
    context.fillStyle = '#facc15';
    context.beginPath();
    context.moveTo(cell * 0.36, 0);
    context.lineTo(-cell * 0.26, -cell * 0.28);
    context.lineTo(-cell * 0.26, cell * 0.28);
    context.closePath();
    context.fill();
    context.restore();

    context.strokeStyle = 'rgba(255, 255, 255, .7)';
    context.beginPath();
    context.moveTo(size.width / 2 - 8, size.height / 2);
    context.lineTo(size.width / 2 + 8, size.height / 2);
    context.moveTo(size.width / 2, size.height / 2 - 8);
    context.lineTo(size.width / 2, size.height / 2 + 8);
    context.stroke();
  }

  function frame(time) {
    const delta = Math.min((time - lastTime) / 1000 || 0, 0.05);
    lastTime = time;
    update(delta);
    render();
    window.requestAnimationFrame(frame);
  }

  window.addEventListener('keydown', (event) => {
    if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', ' ', 'Space'].includes(event.key)) event.preventDefault();
    keys.add(event.key);
    canvas.focus();
  });
  window.addEventListener('keyup', (event) => keys.delete(event.key));
  canvas.addEventListener('click', () => canvas.focus());
  window.addEventListener('resize', render);
  if (restartButton) restartButton.addEventListener('click', resetGame);

  resetGame();
  window.requestAnimationFrame(frame);
}());
