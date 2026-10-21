(() => {
    'use strict';

    const canvas = document.querySelector('#arcade-canvas');
    const publicMaze = document.querySelector('#public-maze-canvas');
    if (!canvas && !publicMaze) return;

    const activeCanvas = canvas || publicMaze;
    const ctx = activeCanvas.getContext('2d');
    const portal = Boolean(canvas);
    const apiUrl = document.body.dataset.arcadeApi || '';
    const csrfToken = window.DevINArcadeCsrf || '';
    const titleEl = document.querySelector('[data-game-title]');
    const hintEl = document.querySelector('[data-game-hint]');
    const overlayEl = document.querySelector('[data-game-overlay]');
    const overlayMessageEl = document.querySelector('[data-game-overlay-message]');
    const gameIntroEl = document.querySelector('[data-game-intro]');
    const gameIntroTitleEl = document.querySelector('[data-game-intro-title]');
    const gameDescriptionEl = document.querySelector('[data-game-description]');
    const gameControlsEl = document.querySelector('[data-game-controls]');
    const scoreEl = document.querySelector('[data-game-score]');
    const levelEl = document.querySelector('[data-game-level]');
    const bestEl = document.querySelector('[data-game-best]');
    const feedbackEl = document.querySelector('[data-arcade-feedback]');
    const restartButton = document.querySelector('[data-restart-game]');
    const startButton = document.querySelector('[data-start-game]');
    const gameOverEl = document.querySelector('[data-game-over]');
    const gameOverScoreEl = document.querySelector('[data-game-over-score]');
    const leaderboardMessageEl = document.querySelector('[data-leaderboard-message]');
    const leaderboardEl = document.querySelector('[data-game-leaderboard]');
    const gameOverRestartButton = document.querySelector('[data-game-over-restart]');
    const progress = {};
    let engine = null;
    let lastFrame = 0;
    let saveTimer = 0;
    let selectedGame = null;
    let countdownInterval = 0;
    let countdownTimeout = 0;

    const colors = {
        bg: '#020617', wall: '#2563eb', line: '#60a5fa',
        text: '#f8fafc', muted: '#94a3b8', yellow: '#facc15',
        cyan: '#22d3ee', pink: '#f472b6', red: '#fb7185',
        green: '#34d399', orange: '#fb923c'
    };

    const games = {
        maze: {
            title: 'Labirinto DevIN',
            description: 'Explore o labirinto, colete cristais e evite os perseguidores. Cristais especiais deixam os perseguidores vulneráveis.',
            hint: 'Controles: setas ou WASD para mover.'
        },
        snake: {
            title: 'Snake',
            description: 'Colete orbes para crescer e avançar de fase sem bater nas bordas ou em si mesmo.',
            hint: 'Controles: setas ou WASD para mover.'
        },
        tetris: {
            title: 'Blocos',
            description: 'Encaixe as peças e complete linhas para pontuar e aumentar o nível.',
            hint: 'Controles: ← → move, ↑ gira, ↓ acelera e Espaço derruba a peça.'
        },
        breakout: {
            title: 'Quebra-blocos',
            description: 'Rebata a bola com a plataforma, limpe os blocos e alcance novas fases.',
            hint: 'Controles: ← →, A/D ou mouse para mover a plataforma.'
        },
        memory: {
            title: 'Memória',
            description: 'Encontre todos os pares de cartas usando o menor número de tentativas.',
            hint: 'Controles: clique nas cartas para revelar os pares.'
        },
        runner: {
            title: 'Salto de Ritmo',
            description: 'Corra e pule obstáculos para avançar por trechos cada vez mais rápidos.',
            hint: 'Controles: Espaço, ↑ ou clique para pular.'
        }
    };

    const cellRect = (x, y, size, left, top) => ({
        x: left + x * size, y: top + y * size, size
    });

    function setStats() {
        if (!engine) return;
        if (scoreEl) scoreEl.textContent = String(engine.score);
        if (levelEl) levelEl.textContent = String(engine.level);
        if (bestEl) bestEl.textContent = String(Math.max(engine.score, progress[engine.game]?.recorde || 0));
    }

    function showOverlay(text, visible = true) {
        if (!overlayEl) return;
        if (overlayMessageEl) overlayMessageEl.textContent = text;
        else overlayEl.textContent = text;
        overlayEl.hidden = !visible;
    }

    function clearCountdown() {
        window.clearInterval(countdownInterval);
        window.clearTimeout(countdownTimeout);
        countdownInterval = 0;
        countdownTimeout = 0;
        if (startButton) startButton.disabled = false;
    }

    async function apiRequest(values, method = 'POST') {
        const options = { method, credentials: 'same-origin' };
        let url = apiUrl;
        if (method === 'GET') {
            url += (url.includes('?') ? '&' : '?') + new URLSearchParams(values).toString();
        } else {
            options.headers = { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' };
            options.body = new URLSearchParams({ ...values, csrf_token: csrfToken });
        }
        const response = await fetch(url, options);
        const data = await response.json();
        if (!response.ok || data.sucesso !== true) {
            throw new Error(data.mensagem || 'Não foi possível salvar o progresso.');
        }
        return data;
    }

    async function saveProgress(gameState = engine) {
        if (!portal || !gameState) return;
        try {
            await apiRequest({
                acao: 'salvar',
                jogo: gameState.game,
                recorde: String(gameState.score),
                fase: String(gameState.level)
            });
            progress[gameState.game] = {
                ...(progress[gameState.game] || {}),
                recorde: Math.max(gameState.score, progress[gameState.game]?.recorde || 0),
                maior_fase: Math.max(gameState.level, progress[gameState.game]?.maior_fase || 0)
            };
            if (feedbackEl) feedbackEl.textContent = 'Progresso salvo.';
            if (engine === gameState) setStats();
        } catch (error) {
            if (feedbackEl) feedbackEl.textContent = error.message;
        }
    }

    async function gameOver() {
        if (!engine || engine.ended) return;
        const endedGame = engine;
        endedGame.ended = true;
        if (!portal || !gameOverEl) {
            showOverlay(`Fim de jogo — ${endedGame.score} pontos. Reinicie para jogar novamente.`);
            return;
        }

        restartButton.hidden = true;
        if (gameIntroEl) gameIntroEl.hidden = true;
        gameOverEl.hidden = false;
        if (overlayMessageEl) overlayMessageEl.hidden = true;
        showOverlay('', true);
        gameOverScoreEl.textContent = `Sua pontuação: ${endedGame.score} pontos · Fase ${endedGame.level}`;
        leaderboardMessageEl.textContent = 'Carregando classificação…';
        leaderboardEl.replaceChildren();
        await saveProgress(endedGame);
        await loadLeaderboard(endedGame.game);
    }

    function drawBackground() {
        ctx.fillStyle = colors.bg;
        ctx.fillRect(0, 0, activeCanvas.width, activeCanvas.height);
    }

    function makeMaze(level) {
        const layout = [
            '#####################',
            '#..o...............o#',
            '#.###.###.#.###.###.#',
            '#.....#...#...#.....#',
            '###.#.#.###.#.#.#.###',
            '#...#.#.....#.#.#...#',
            '#.###.#####.#.###.#.#',
            '#.....#.....#.....#.#',
            '#####.#.P...#.#####.#',
            '#.....#..G..#.....#.#',
            '#.###.#####.#.###.#.#',
            '#...#.#.....#.#.#...#',
            '###.#.#.###.#.#.#.###',
            '#.....#...#...#.....#',
            '#.###.###.#.###.###.#',
            '#..o...............o#',
            '#####################'
        ];
        const grid = layout.map((row) => [...row]);
        if (level % 2 === 0) {
            grid[3][10] = '.';
            grid[13][10] = '.';
        }
        const start = { x: 8, y: 8 };
        const ghosts = [
            { x: 2, y: 1, color: colors.red },
            { x: 17, y: 1, color: colors.pink },
            { x: 2, y: 15, color: colors.cyan },
            { x: 17, y: 15, color: colors.orange }
        ];
        let pellets = 0;
        grid.forEach((row, y) => row.forEach((tile, x) => {
            if (tile === '.' || tile === 'o') pellets += 1;
            if (tile === 'P') grid[y][x] = ' ';
        }));
        return { grid, start, ghosts, pellets };
    }

    const directions = {
        ArrowUp: { x: 0, y: -1 }, KeyW: { x: 0, y: -1 },
        ArrowDown: { x: 0, y: 1 }, KeyS: { x: 0, y: 1 },
        ArrowLeft: { x: -1, y: 0 }, KeyA: { x: -1, y: 0 },
        ArrowRight: { x: 1, y: 0 }, KeyD: { x: 1, y: 0 }
    };

    function mazeEngine(level = 1, score = 0) {
        const maze = makeMaze(level);
        const state = {
            map: maze.grid, start: maze.start, pellets: maze.pellets,
            player: { ...maze.start, dir: { x: 0, y: 0 }, next: { x: 0, y: 0 } },
            ghosts: maze.ghosts.map((ghost) => ({ ...ghost, startX: ghost.x, startY: ghost.y })),
            scaredUntil: 0, lives: 3, step: 0, lastMove: 0
        };
        const directionsList = [{ x: 1, y: 0 }, { x: -1, y: 0 }, { x: 0, y: 1 }, { x: 0, y: -1 }];
        const open = (x, y) => y >= 0 && y < state.map.length && x >= 0 && x < state.map[y].length
            && state.map[y][x] !== '#';
        const move = (actor, dir) => {
            if (!open(actor.x + dir.x, actor.y + dir.y)) return false;
            actor.x += dir.x;
            actor.y += dir.y;
            return true;
        };
        return {
            game: 'maze', level, score, state, ended: false,
            key(key) {
                if (directions[key]) state.player.next = directions[key];
            },
            update(delta) {
                state.lastMove += delta;
                if (state.lastMove < 120) return;
                state.lastMove = 0;
                if (open(state.player.x + state.player.next.x, state.player.y + state.player.next.y)) {
                    state.player.dir = state.player.next;
                }
                move(state.player, state.player.dir);
                let tile = state.map[state.player.y][state.player.x];
                if (tile === '.' || tile === 'o') {
                    this.score += tile === 'o' ? 50 : 10;
                    state.map[state.player.y][state.player.x] = ' ';
                    state.pellets -= 1;
                    if (tile === 'o') state.scaredUntil = performance.now() + 7000;
                }
                state.step += 1;
                if (state.step % 2 === 0) {
                    state.ghosts.forEach((ghost) => {
                        const options = directionsList.filter((dir) => open(ghost.x + dir.x, ghost.y + dir.y));
                        if (!options.length) return;
                        const scared = performance.now() < state.scaredUntil;
                        options.sort((a, b) => {
                            const da = Math.abs(ghost.x + a.x - state.player.x) + Math.abs(ghost.y + a.y - state.player.y);
                            const db = Math.abs(ghost.x + b.x - state.player.x) + Math.abs(ghost.y + b.y - state.player.y);
                            return scared ? db - da : da - db;
                        });
                        const candidates = options.slice(0, Math.min(2, options.length));
                        const next = candidates[Math.floor(Math.random() * candidates.length)];
                        move(ghost, next);
                        if (ghost.x === state.player.x && ghost.y === state.player.y) {
                            if (performance.now() < state.scaredUntil) {
                                this.score += 200;
                                ghost.x = ghost.startX;
                                ghost.y = ghost.startY;
                            } else {
                                state.lives -= 1;
                                if (state.lives <= 0) gameOver();
                                else {
                                    state.player = { ...state.start, dir: { x: 0, y: 0 }, next: { x: 0, y: 0 } };
                                    state.ghosts.forEach((item) => { item.x = item.startX; item.y = item.startY; });
                                }
                            }
                        }
                    });
                }
                if (state.pellets <= 0) {
                    this.level += 1;
                    this.score += 250;
                    const next = mazeEngine(this.level, this.score);
                    engine = next;
                    if (portal) saveProgress();
                }
            },
            draw() {
                drawBackground();
                const size = 24;
                const left = (activeCanvas.width - state.map[0].length * size) / 2;
                const top = (activeCanvas.height - state.map.length * size) / 2;
                state.map.forEach((row, y) => row.forEach((tile, x) => {
                    const rect = cellRect(x, y, size, left, top);
                    if (tile === '#') {
                        ctx.fillStyle = colors.wall;
                        ctx.fillRect(rect.x + 2, rect.y + 2, size - 4, size - 4);
                        ctx.strokeStyle = colors.line;
                        ctx.strokeRect(rect.x + 4, rect.y + 4, size - 8, size - 8);
                    } else if (tile === '.') {
                        ctx.fillStyle = '#fef3c7';
                        ctx.beginPath(); ctx.arc(rect.x + size / 2, rect.y + size / 2, 3, 0, Math.PI * 2); ctx.fill();
                    } else if (tile === 'o') {
                        ctx.fillStyle = colors.yellow;
                        ctx.beginPath(); ctx.arc(rect.x + size / 2, rect.y + size / 2, 7, 0, Math.PI * 2); ctx.fill();
                    }
                }));
                ctx.fillStyle = colors.yellow;
                ctx.beginPath(); ctx.arc(left + (state.player.x + .5) * size, top + (state.player.y + .5) * size, 9, 0, Math.PI * 2); ctx.fill();
                state.ghosts.forEach((ghost) => {
                    ctx.fillStyle = performance.now() < state.scaredUntil ? '#3b82f6' : ghost.color;
                    ctx.beginPath(); ctx.arc(left + (ghost.x + .5) * size, top + (ghost.y + .5) * size, 9, Math.PI, 0); ctx.lineTo(left + (ghost.x + 1) * size - 3, top + (ghost.y + 1) * size - 3); ctx.lineTo(left + ghost.x * size + 3, top + (ghost.y + 1) * size - 3); ctx.fill();
                });
                ctx.fillStyle = colors.text;
                ctx.font = '16px Arial';
                ctx.fillText(`Vidas: ${state.lives}`, 16, 24);
            }
        };
    }

    function snakeEngine(level = 1, score = 0) {
        const state = { body: [{ x: 10, y: 10 }, { x: 9, y: 10 }, { x: 8, y: 10 }], dir: { x: 1, y: 0 }, next: { x: 1, y: 0 }, food: null, timer: 0 };
        const placeFood = () => {
            let food;
            do { food = { x: Math.floor(Math.random() * 20), y: Math.floor(Math.random() * 18) }; }
            while (state.body.some((part) => part.x === food.x && part.y === food.y));
            state.food = food;
        };
        placeFood();
        return {
            game: 'snake', level, score, state, ended: false,
            key(key) {
                const next = directions[key];
                if (next && next.x !== -state.dir.x && next.y !== -state.dir.y) state.next = next;
            },
            update(delta) {
                state.timer += delta;
                if (state.timer < Math.max(55, 150 - this.level * 8)) return;
                state.timer = 0;
                state.dir = state.next;
                const head = { x: state.body[0].x + state.dir.x, y: state.body[0].y + state.dir.y };
                if (head.x < 0 || head.x >= 20 || head.y < 0 || head.y >= 18
                    || state.body.some((part, index) => index < state.body.length - 1 && part.x === head.x && part.y === head.y)) {
                    gameOver(); return;
                }
                state.body.unshift(head);
                if (head.x === state.food.x && head.y === state.food.y) {
                    this.score += 10;
                    if (this.score > 0 && this.score % 50 === 0) {
                        this.level += 1;
                        if (portal) saveProgress();
                    }
                    placeFood();
                } else state.body.pop();
            },
            draw() {
                drawBackground();
                const size = 22;
                const left = (activeCanvas.width - size * 20) / 2;
                const top = (activeCanvas.height - size * 18) / 2;
                ctx.strokeStyle = '#334155'; ctx.strokeRect(left, top, size * 20, size * 18);
                ctx.fillStyle = colors.red;
                ctx.beginPath(); ctx.arc(left + state.food.x * size + size / 2, top + state.food.y * size + size / 2, size / 3, 0, Math.PI * 2); ctx.fill();
                state.body.forEach((part, index) => {
                    ctx.fillStyle = index === 0 ? colors.green : '#059669';
                    ctx.fillRect(left + part.x * size + 1, top + part.y * size + 1, size - 2, size - 2);
                });
            }
        };
    }

    const pieces = [
        [[1, 1, 1, 1]],
        [[1, 1], [1, 1]],
        [[0, 1, 0], [1, 1, 1]],
        [[1, 0, 0], [1, 1, 1]],
        [[0, 0, 1], [1, 1, 1]],
        [[1, 1, 0], [0, 1, 1]],
        [[0, 1, 1], [1, 1, 0]]
    ];

    function tetrisEngine(level = 1, score = 0) {
        const state = { grid: Array.from({ length: 20 }, () => Array(10).fill(0)), piece: null, lines: 0, timer: 0 };
        let game;
        const newPiece = () => {
            const shape = pieces[Math.floor(Math.random() * pieces.length)].map((row) => [...row]);
            state.piece = { shape, x: Math.floor((10 - shape[0].length) / 2), y: 0, color: 1 + Math.floor(Math.random() * 6) };
            if (collides(state.piece.x, state.piece.y, shape)) gameOver();
        };
        const collides = (x, y, shape) => shape.some((row, dy) => row.some((cell, dx) =>
            cell && (x + dx < 0 || x + dx >= 10 || y + dy >= 20 || (y + dy >= 0 && state.grid[y + dy][x + dx]))));
        const lock = () => {
            state.piece.shape.forEach((row, dy) => row.forEach((cell, dx) => {
                if (cell && state.piece.y + dy >= 0) state.grid[state.piece.y + dy][state.piece.x + dx] = state.piece.color;
            }));
            const remain = state.grid.filter((row) => row.some((cell) => !cell));
            state.lines += 20 - remain.length;
            game.score += (20 - remain.length) * 100 * game.level;
            while (remain.length < 20) remain.unshift(Array(10).fill(0));
            state.grid = remain;
            const nextLevel = 1 + Math.floor(state.lines / 10);
            if (nextLevel > game.level) {
                game.level = nextLevel;
                if (portal) saveProgress();
            }
            newPiece();
        };
        const rotate = (shape) => shape[0].map((_, i) => shape.map((row) => row[i]).reverse());
        newPiece();
        game = {
            game: 'tetris', level, score, state, ended: false,
            key(key) {
                if (key === 'ArrowLeft' || key === 'KeyA') {
                    if (!collides(state.piece.x - 1, state.piece.y, state.piece.shape)) state.piece.x -= 1;
                } else if (key === 'ArrowRight' || key === 'KeyD') {
                    if (!collides(state.piece.x + 1, state.piece.y, state.piece.shape)) state.piece.x += 1;
                } else if (key === 'ArrowDown' || key === 'KeyS') {
                    if (!collides(state.piece.x, state.piece.y + 1, state.piece.shape)) state.piece.y += 1;
                    else lock();
                } else if (key === 'ArrowUp' || key === 'KeyW') {
                    const turned = rotate(state.piece.shape);
                    if (!collides(state.piece.x, state.piece.y, turned)) state.piece.shape = turned;
                } else if (key === 'Space') {
                    while (!collides(state.piece.x, state.piece.y + 1, state.piece.shape)) state.piece.y += 1;
                    lock();
                }
            },
            update(delta) {
                state.timer += delta;
                if (state.timer < Math.max(100, 800 - this.level * 55)) return;
                state.timer = 0;
                if (!collides(state.piece.x, state.piece.y + 1, state.piece.shape)) state.piece.y += 1;
                else lock();
            },
            draw() {
                drawBackground();
                const size = 20;
                const left = (activeCanvas.width - size * 10) / 2;
                const top = (activeCanvas.height - size * 20) / 2;
                const palette = [colors.bg, colors.cyan, colors.yellow, colors.pink, colors.orange, colors.green, colors.red];
                state.grid.forEach((row, y) => row.forEach((cell, x) => {
                    ctx.fillStyle = palette[cell];
                    if (cell) ctx.fillRect(left + x * size + 1, top + y * size + 1, size - 2, size - 2);
                    ctx.strokeStyle = '#1e293b'; ctx.strokeRect(left + x * size, top + y * size, size, size);
                }));
                if (state.piece) state.piece.shape.forEach((row, dy) => row.forEach((cell, dx) => {
                    if (!cell) return;
                    ctx.fillStyle = palette[state.piece.color];
                    ctx.fillRect(left + (state.piece.x + dx) * size + 1, top + (state.piece.y + dy) * size + 1, size - 2, size - 2);
                }));
            }
        };
        return game;
    }

    function breakoutEngine(level = 1, score = 0) {
        const state = { paddleX: 270, ballX: 320, ballY: 380, vx: 3, vy: -4, bricks: [], lives: 3, timer: 0 };
        let currentLevel = level;
        const createBricks = () => {
            state.bricks = [];
            const rows = Math.min(4 + currentLevel, 8);
            for (let row = 0; row < rows; row += 1) {
                for (let col = 0; col < 10; col += 1) state.bricks.push({ x: 40 + col * 56, y: 55 + row * 24, alive: true });
            }
        };
        const resetBall = () => { state.ballX = 320; state.ballY = 380; state.vx = 3 + Math.random(); state.vy = -4; };
        createBricks();
        return {
            game: 'breakout', level, score, state, ended: false,
            key(key) {
                if (key === 'ArrowLeft' || key === 'KeyA') state.paddleX -= 30;
                if (key === 'ArrowRight' || key === 'KeyD') state.paddleX += 30;
                state.paddleX = Math.max(45, Math.min(595, state.paddleX));
            },
            pointer(event) {
                const rect = activeCanvas.getBoundingClientRect();
                state.paddleX = Math.max(45, Math.min(595, ((event.clientX - rect.left) / rect.width) * activeCanvas.width));
            },
            update(delta) {
                state.timer += delta;
                if (state.timer < 16) return;
                state.timer = 0;
                state.ballX += state.vx;
                state.ballY += state.vy;
                if (state.ballX < 12 || state.ballX > 628) state.vx *= -1;
                if (state.ballY < 12) state.vy *= -1;
                if (state.ballY > 418 && state.ballY < 438 && Math.abs(state.ballX - state.paddleX) < 55 && state.vy > 0) {
                    state.vy = -Math.abs(state.vy);
                    state.vx += (state.ballX - state.paddleX) * 0.035;
                }
                state.bricks.forEach((brick) => {
                    if (brick.alive && state.ballX >= brick.x && state.ballX <= brick.x + 50
                        && state.ballY >= brick.y && state.ballY <= brick.y + 16 && state.vy < 0) {
                        brick.alive = false; state.vy *= -1; this.score += 10;
                    }
                });
                if (!state.bricks.some((brick) => brick.alive)) {
                    this.level += 1;
                    currentLevel = this.level;
                    this.score += 100;
                    createBricks(); resetBall();
                    if (portal) saveProgress();
                }
                if (state.ballY > 480) {
                    state.lives -= 1;
                    if (state.lives <= 0) gameOver();
                    else resetBall();
                }
            },
            draw() {
                drawBackground();
                state.bricks.forEach((brick, index) => {
                    if (!brick.alive) return;
                    ctx.fillStyle = [colors.cyan, colors.pink, colors.orange, colors.green][index % 4];
                    ctx.fillRect(brick.x, brick.y, 50, 16);
                });
                ctx.fillStyle = colors.text; ctx.fillRect(state.paddleX - 48, 430, 96, 12);
                ctx.fillStyle = colors.yellow; ctx.beginPath(); ctx.arc(state.ballX, state.ballY, 8, 0, Math.PI * 2); ctx.fill();
                ctx.fillStyle = colors.text; ctx.font = '16px Arial'; ctx.fillText(`Vidas: ${state.lives}`, 16, 24);
            }
        };
    }

    function memoryEngine(level = 1, score = 0) {
        const symbols = ['◆', '●', '▲', '■', '★', '✦', '⬟', '✚', '☾', '✿', '⬢', '♥'];
        const pairCount = Math.min(4 + level - 1, symbols.length);
        const cards = [...symbols.slice(0, pairCount), ...symbols.slice(0, pairCount)]
            .sort(() => Math.random() - 0.5)
            .map((symbol, id) => ({ id, symbol, revealed: false, matched: false }));
        const state = { cards, first: null, lock: false, timer: 0, attempts: 0, matches: 0 };
        return {
            game: 'memory', level, score, state, ended: false,
            key() {},
            pointer(event) {
                if (state.lock) return;
                const rect = activeCanvas.getBoundingClientRect();
                const x = ((event.clientX - rect.left) / rect.width) * activeCanvas.width;
                const y = ((event.clientY - rect.top) / rect.height) * activeCanvas.height;
                const cols = Math.min(6, Math.ceil(Math.sqrt(cards.length)));
                const rows = Math.ceil(cards.length / cols);
                const cardWidth = Math.min(82, 480 / cols);
                const cardHeight = Math.min(80, 340 / rows);
                const left = (activeCanvas.width - cols * cardWidth) / 2;
                const top = (activeCanvas.height - rows * cardHeight) / 2;
                const col = Math.floor((x - left) / cardWidth);
                const row = Math.floor((y - top) / cardHeight);
                const index = row * cols + col;
                const card = cards[index];
                if (!card || card.revealed || card.matched || x < left || y < top) return;
                card.revealed = true;
                if (state.first === null) state.first = card;
                else {
                    state.attempts += 1;
                    if (state.first.symbol === card.symbol) {
                        state.first.matched = true; card.matched = true;
                        state.matches += 1; this.score += 100;
                        state.first = null;
                        if (state.matches === pairCount) {
                            this.level += 1;
                            if (portal) saveProgress();
                            setTimeout(() => { if (engine?.game === 'memory' && engine.level === this.level) engine = memoryEngine(this.level, this.score); }, 900);
                        }
                    } else {
                        state.lock = true;
                        if (state.attempts >= pairCount * 3) gameOver();
                        setTimeout(() => {
                            state.first.revealed = false; card.revealed = false;
                            state.first = null; state.lock = false;
                        }, 700);
                    }
                }
            },
            update() {},
            draw() {
                drawBackground();
                const cols = Math.min(6, Math.ceil(Math.sqrt(cards.length)));
                const rows = Math.ceil(cards.length / cols);
                const cardWidth = Math.min(82, 480 / cols);
                const cardHeight = Math.min(80, 340 / rows);
                const left = (activeCanvas.width - cols * cardWidth) / 2;
                const top = (activeCanvas.height - rows * cardHeight) / 2;
                cards.forEach((card, index) => {
                    const x = left + (index % cols) * cardWidth;
                    const y = top + Math.floor(index / cols) * cardHeight;
                    ctx.fillStyle = card.matched ? '#14532d' : (card.revealed ? '#1d4ed8' : '#1e293b');
                    ctx.fillRect(x + 3, y + 3, cardWidth - 6, cardHeight - 6);
                    ctx.strokeStyle = '#60a5fa'; ctx.strokeRect(x + 3, y + 3, cardWidth - 6, cardHeight - 6);
                    if (card.revealed || card.matched) {
                        ctx.fillStyle = colors.text; ctx.font = '36px Arial'; ctx.textAlign = 'center';
                        ctx.fillText(card.symbol, x + cardWidth / 2, y + cardHeight / 2 + 12);
                    } else {
                        ctx.fillStyle = colors.cyan; ctx.font = '24px Arial'; ctx.textAlign = 'center';
                        ctx.fillText('?', x + cardWidth / 2, y + cardHeight / 2 + 8);
                    }
                });
                ctx.textAlign = 'start'; ctx.fillStyle = colors.text; ctx.font = '16px Arial';
                ctx.fillText(`Tentativas: ${state.attempts} | Pares: ${state.matches}/${pairCount}`, 16, 24);
            }
        };
    }

    function runnerEngine(level = 1, score = 0) {
        const state = { y: 380, vy: 0, obstacles: [], distance: 0, timer: 0, nextObstacle: 250, onGround: true };
        return {
            game: 'runner', level, score, state, ended: false,
            key(key) {
                if ((key === 'Space' || key === 'ArrowUp' || key === 'KeyW') && state.onGround) {
                    state.vy = -520; state.onGround = false;
                }
            },
            pointer() { this.key('Space'); },
            update(delta) {
                const seconds = delta / 1000;
                state.distance += seconds * (220 + this.level * 18);
                this.score = Math.floor(state.distance);
                state.vy += 1400 * seconds;
                state.y += state.vy * seconds;
                if (state.y >= 380) { state.y = 380; state.vy = 0; state.onGround = true; }
                const speed = 220 + this.level * 18;
                state.obstacles.forEach((item) => { item.x -= speed * seconds; });
                state.obstacles = state.obstacles.filter((item) => item.x > -40);
                state.nextObstacle -= speed * seconds;
                if (state.nextObstacle <= 0) {
                    state.obstacles.push({ x: 660, height: 24 + Math.random() * 28, width: 22 + Math.random() * 16 });
                    state.nextObstacle = 260 + Math.random() * 220;
                }
                if (state.obstacles.some((item) => 115 < item.x + item.width && 145 > item.x
                    && state.y + 34 > 400 - item.height)) gameOver();
                const nextLevel = 1 + Math.floor(state.distance / 1000);
                if (nextLevel > this.level) {
                    this.level = nextLevel;
                    if (portal) saveProgress();
                }
            },
            draw() {
                drawBackground();
                ctx.fillStyle = '#164e63'; ctx.fillRect(0, 410, 640, 70);
                ctx.strokeStyle = colors.cyan; ctx.lineWidth = 2; ctx.beginPath(); ctx.moveTo(0, 410); ctx.lineTo(640, 410); ctx.stroke();
                ctx.fillStyle = colors.yellow; ctx.fillRect(112, state.y, 32, 32);
                ctx.fillStyle = colors.pink;
                state.obstacles.forEach((item) => ctx.fillRect(item.x, 410 - item.height, item.width, item.height));
                ctx.fillStyle = colors.text; ctx.font = '16px Arial'; ctx.fillText(`${Math.floor(state.distance)} m`, 16, 24);
            }
        };
    }

    const factories = { maze: mazeEngine, snake: snakeEngine, tetris: tetrisEngine, breakout: breakoutEngine, memory: memoryEngine, runner: runnerEngine };

    async function launch(game) {
        if (!factories[game]) return;
        clearCountdown();
        if (portal) await apiRequest({ acao: 'iniciar', jogo: game });
        engine = factories[game](1, 0);
        saveTimer = 0;
        if (portal) {
            titleEl.textContent = games[game].title;
            hintEl.textContent = games[game].hint;
            restartButton.hidden = false;
            gameOverEl.hidden = true;
            const prior = progress[game];
            bestEl.textContent = String(prior?.recorde || 0);
            feedbackEl.textContent = prior ? `Maior fase registrada: ${prior.maior_fase}.` : '';
            if (gameIntroEl) gameIntroEl.hidden = true;
            showOverlay('', false);
        } else {
            showOverlay('', false);
        }
        setStats();
    }

    function selectGame(game) {
        if (!factories[game]) return;
        clearCountdown();
        selectedGame = game;
        engine = null;
        if (!portal) {
            launch(game);
            return;
        }

        const details = games[game];
        titleEl.textContent = details.title;
        hintEl.textContent = 'Leia as instruções e inicie quando estiver pronto.';
        gameIntroTitleEl.textContent = details.title;
        gameDescriptionEl.textContent = details.description;
        gameControlsEl.textContent = details.hint;
        gameIntroEl.hidden = false;
        if (gameOverEl) gameOverEl.hidden = true;
        restartButton.hidden = true;
        scoreEl.textContent = '0';
        levelEl.textContent = '1';
        bestEl.textContent = String(progress[game]?.recorde || 0);
        feedbackEl.textContent = '';
        showOverlay('', true);
        if (overlayMessageEl) overlayMessageEl.hidden = true;
    }

    function startSelectedGame() {
        if (!selectedGame || !portal) return;
        clearCountdown();
        engine = null;
        restartButton.hidden = true;
        gameOverEl.hidden = true;
        if (startButton) startButton.disabled = true;
        gameIntroEl.hidden = true;
        if (overlayMessageEl) overlayMessageEl.hidden = false;

        let seconds = 3;
        showOverlay(`O jogo começa em ${seconds}...`);
        countdownInterval = window.setInterval(() => {
            seconds -= 1;
            if (seconds > 0) showOverlay(`O jogo começa em ${seconds}...`);
        }, 1000);
        countdownTimeout = window.setTimeout(() => {
            launch(selectedGame).catch((error) => {
                gameIntroEl.hidden = false;
                if (overlayMessageEl) overlayMessageEl.hidden = true;
                showOverlay('', true);
                feedbackEl.textContent = error.message;
                if (startButton) startButton.disabled = false;
            });
        }, 3000);
    }

    function restartCurrentGame() {
        if (!engine?.game) return;
        selectedGame = engine.game;
        if (portal) startSelectedGame();
        else launch(selectedGame);
    }

    async function loadProgress() {
        try {
            const data = await apiRequest({}, 'GET');
            data.progresso.forEach((item) => { progress[item.jogo] = item; });
            if (engine) setStats();
        } catch (error) {
            if (feedbackEl) feedbackEl.textContent = error.message;
        }
    }

    async function loadLeaderboard(game) {
        try {
            const data = await apiRequest({ acao: 'classificacao', jogo: game });
            if (!data.classificacao.length) {
                leaderboardMessageEl.textContent = 'Ainda não há pontuações para este jogo.';
                return;
            }

            leaderboardMessageEl.textContent = '';
            const rows = document.createDocumentFragment();
            data.classificacao.forEach((item, index) => {
                const row = document.createElement('li');
                const name = document.createElement('span');
                const score = document.createElement('strong');
                name.textContent = `${index + 1}. ${item.nome}`;
                score.textContent = `${item.recorde} pts · fase ${item.maior_fase}`;
                row.append(name, score);
                rows.appendChild(row);
            });
            leaderboardEl.replaceChildren(rows);
        } catch (error) {
            leaderboardMessageEl.textContent = error.message;
        }
    }

    async function loadAdminProgress() {
        const target = document.querySelector('[data-admin-progress]');
        if (!target) return;
        try {
            const data = await apiRequest({}, 'GET');
            target.replaceChildren();
            if (!data.progresso.length) {
                target.textContent = 'Ainda não há progresso salvo.';
                return;
            }
            const table = document.createElement('table');
            table.className = 'admin-progress-grid';
            const header = table.createTHead().insertRow();
            ['Conta', 'Perfil', 'Jogo', 'Recorde', 'Maior fase', 'Última atividade', 'Ação'].forEach((label) => {
                const cell = document.createElement('th'); cell.textContent = label; header.appendChild(cell);
            });
            const body = table.createTBody();
            data.progresso.forEach((item) => {
                const row = body.insertRow();
                [item.nome, item.perfil_tipo, games[item.jogo]?.title || item.jogo, item.recorde, item.maior_fase, item.ultima_atividade]
                    .forEach((value) => { const cell = row.insertCell(); cell.textContent = String(value); });
                const action = row.insertCell();
                const button = document.createElement('button');
                button.type = 'button'; button.className = 'arcade-button danger-button'; button.textContent = 'Remover';
                button.addEventListener('click', async () => {
                    if (!window.confirm(`Remover o progresso de ${item.nome} em ${games[item.jogo]?.title || item.jogo}?`)) return;
                    try {
                        await apiRequest({ acao: 'excluir', perfil_tipo: item.perfil_tipo, perfil_id: String(item.perfil_id), jogo: item.jogo });
                        await loadAdminProgress();
                    } catch (error) { target.textContent = error.message; }
                });
                action.appendChild(button);
            });
            target.appendChild(table);
        } catch (error) {
            target.textContent = error.message;
        }
    }

    document.querySelectorAll('[data-launch-game]').forEach((button) => {
        button.addEventListener('click', () => selectGame(button.dataset.launchGame));
    });
    startButton?.addEventListener('click', startSelectedGame);
    gameOverRestartButton?.addEventListener('click', restartCurrentGame);
    restartButton?.addEventListener('click', restartCurrentGame);

    window.addEventListener('keydown', (event) => {
        if (!engine || engine.ended) return;
        if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'Space'].includes(event.code)) event.preventDefault();
        engine.key(event.code);
    });
    activeCanvas.addEventListener('pointerdown', (event) => {
        if (!engine?.ended) engine?.pointer?.(event);
    });

    function frame(timestamp) {
        const delta = lastFrame ? Math.min(timestamp - lastFrame, 80) : 16;
        lastFrame = timestamp;
        if (engine) {
            if (!engine.ended) engine.update(delta);
            engine.draw();
            setStats();
            saveTimer += delta;
            if (portal && saveTimer >= 20000 && !engine.ended) {
                saveTimer = 0;
                saveProgress();
            }
        } else {
            drawBackground();
        }
        window.requestAnimationFrame(frame);
    }

    if (portal) {
        loadProgress();
        loadAdminProgress();
    } else {
        engine = mazeEngine();
        showOverlay('', false);
    }
    window.requestAnimationFrame(frame);
})();
