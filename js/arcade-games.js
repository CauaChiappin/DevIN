/*
 * REVISÃO DETALHADA DEVIN — js/arcade-games.js
 * Finalidade: arcade games.
 * Domínio: interações do navegador.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */

(() => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    'use strict'; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.

    const canvas = document.querySelector('#arcade-canvas'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const publicMaze = document.querySelector('#public-maze-canvas'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    if (!canvas && !publicMaze) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

    const activeCanvas = canvas || publicMaze; // Explicação DevIN: Declara variável JavaScript activeCanvas para reutilizar valor ou resultado.
    const ctx = activeCanvas.getContext('2d'); // Explicação DevIN: Declara variável JavaScript ctx para reutilizar valor ou resultado.
    const portal = Boolean(canvas); // Explicação DevIN: Declara variável JavaScript portal para reutilizar valor ou resultado.
    const apiUrl = document.body.dataset.arcadeApi || ''; // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const csrfToken = window.DevINArcadeCsrf || ''; // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const titleEl = document.querySelector('[data-game-title]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const hintEl = document.querySelector('[data-game-hint]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const overlayEl = document.querySelector('[data-game-overlay]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const overlayMessageEl = document.querySelector('[data-game-overlay-message]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const gameIntroEl = document.querySelector('[data-game-intro]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const gameIntroTitleEl = document.querySelector('[data-game-intro-title]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const gameDescriptionEl = document.querySelector('[data-game-description]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const gameControlsEl = document.querySelector('[data-game-controls]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const scoreEl = document.querySelector('[data-game-score]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const levelEl = document.querySelector('[data-game-level]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const bestEl = document.querySelector('[data-game-best]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const feedbackEl = document.querySelector('[data-arcade-feedback]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const restartButton = document.querySelector('[data-restart-game]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const startButton = document.querySelector('[data-start-game]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const gameOverEl = document.querySelector('[data-game-over]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const gameOverScoreEl = document.querySelector('[data-game-over-score]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const leaderboardMessageEl = document.querySelector('[data-leaderboard-message]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const leaderboardEl = document.querySelector('[data-game-leaderboard]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const gameOverRestartButton = document.querySelector('[data-game-over-restart]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const progress = {}; // Explicação DevIN: Declara variável JavaScript progress para reutilizar valor ou resultado.
    let engine = null; // Explicação DevIN: Declara variável JavaScript engine para reutilizar valor ou resultado.
    let lastFrame = 0; // Explicação DevIN: Declara variável JavaScript lastFrame para reutilizar valor ou resultado.
    let saveTimer = 0; // Explicação DevIN: Declara variável JavaScript saveTimer para reutilizar valor ou resultado.
    let selectedGame = null; // Explicação DevIN: Declara variável JavaScript selectedGame para reutilizar valor ou resultado.
    let countdownInterval = 0; // Explicação DevIN: Declara variável JavaScript countdownInterval para reutilizar valor ou resultado.
    let countdownTimeout = 0; // Explicação DevIN: Declara variável JavaScript countdownTimeout para reutilizar valor ou resultado.

    const colors = { // Explicação DevIN: Declara variável JavaScript colors para reutilizar valor ou resultado.
        bg: '#020617', wall: '#2563eb', line: '#60a5fa', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        text: '#f8fafc', muted: '#94a3b8', yellow: '#facc15', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        cyan: '#22d3ee', pink: '#f472b6', red: '#fb7185', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        green: '#34d399', orange: '#fb923c' // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    const games = { // Explicação DevIN: Declara variável JavaScript games para reutilizar valor ou resultado.
        maze: { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            title: 'Labirinto DevIN', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            description: 'Explore o labirinto, colete cristais e evite os perseguidores. Cristais especiais deixam os perseguidores vulneráveis.', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            hint: 'Controles: setas ou WASD para mover.' // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        snake: { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            title: 'Snake', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            description: 'Colete orbes para crescer e avançar de fase sem bater nas bordas ou em si mesmo.', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            hint: 'Controles: setas ou WASD para mover.' // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        tetris: { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            title: 'Blocos', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            description: 'Encaixe as peças e complete linhas para pontuar e aumentar o nível.', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            hint: 'Controles: ← → move, ↑ gira, ↓ acelera e Espaço derruba a peça.' // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        breakout: { // Explicação DevIN: Controla a continuação ou saída de laço/seleção.
            title: 'Quebra-blocos', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            description: 'Rebata a bola com a plataforma, limpe os blocos e alcance novas fases.', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            hint: 'Controles: ← →, A/D ou mouse para mover a plataforma.' // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        memory: { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            title: 'Memória', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            description: 'Encontre todos os pares de cartas usando o menor número de tentativas.', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            hint: 'Controles: clique nas cartas para revelar os pares.' // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        runner: { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            title: 'Salto de Ritmo', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            description: 'Corra e pule obstáculos para avançar por trechos cada vez mais rápidos.', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            hint: 'Controles: Espaço, ↑ ou clique para pular.' // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: O catálogo externo acrescenta jogos originais sem criar novas permissões de banco.
    const additionalCatalog = window.DevINArcadeExtraGames?.metadata || {}; // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    Object.assign(games, additionalCatalog); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    const cellRect = (x, y, size, left, top) => ({ // Explicação DevIN: Declara variável JavaScript cellRect para reutilizar valor ou resultado.
        x: left + x * size, y: top + y * size, size // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    const mazeStepMs = 100; // Explicação DevIN: Declara variável JavaScript mazeStepMs para reutilizar valor ou resultado.

    function setStats() { // Explicação DevIN: Declara função/método setStats, com seus parâmetros e comportamento.
        if (!engine) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if (scoreEl) scoreEl.textContent = String(engine.score); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if (levelEl) levelEl.textContent = String(engine.level); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if (bestEl) bestEl.textContent = String(Math.max(engine.score, progress[engine.game]?.recorde || 0)); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    function showOverlay(text, visible = true) { // Explicação DevIN: Declara função/método showOverlay, com seus parâmetros e comportamento.
        if (!overlayEl) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if (overlayMessageEl) overlayMessageEl.textContent = text; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        else overlayEl.textContent = text; // Explicação DevIN: Define ramo alternativo para condição anterior.
        overlayEl.hidden = !visible; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    function clearCountdown() { // Explicação DevIN: Declara função/método clearCountdown, com seus parâmetros e comportamento.
        window.clearInterval(countdownInterval); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        window.clearTimeout(countdownTimeout); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        countdownInterval = 0; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        countdownTimeout = 0; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        if (startButton) startButton.disabled = false; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    async function apiRequest(values, method = 'POST') { // Explicação DevIN: Declara função/método apiRequest, com seus parâmetros e comportamento.
        const options = { method, credentials: 'same-origin' }; // Explicação DevIN: Declara variável JavaScript options para reutilizar valor ou resultado.
        let url = apiUrl; // Explicação DevIN: Declara variável JavaScript url para reutilizar valor ou resultado.
        if (method === 'GET') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            url += (url.includes('?') ? '&' : '?') + new URLSearchParams(values).toString(); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
            options.headers = { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' }; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            options.body = new URLSearchParams({ ...values, csrf_token: csrfToken }); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        const response = await fetch(url, options); // Explicação DevIN: Inicia solicitação HTTP assíncrona a um endereço configurado.
        const data = await response.json(); // Explicação DevIN: Declara variável JavaScript data para reutilizar valor ou resultado.
        if (!response.ok || data.sucesso !== true) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new Error(data.mensagem || 'Não foi possível salvar o progresso.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return data; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    async function saveProgress(gameState = engine) { // Explicação DevIN: Declara função/método saveProgress, com seus parâmetros e comportamento.
        if (!portal || !gameState) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            await apiRequest({ // Explicação DevIN: Continua expressão ou bloco pertencente a função saveProgress.
                acao: 'salvar', // Explicação DevIN: Continua expressão ou bloco pertencente a função saveProgress.
                jogo: gameState.game, // Explicação DevIN: Continua expressão ou bloco pertencente a função saveProgress.
                recorde: String(gameState.score), // Explicação DevIN: Continua expressão ou bloco pertencente a função saveProgress.
                fase: String(gameState.level) // Explicação DevIN: Continua expressão ou bloco pertencente a função saveProgress.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            progress[gameState.game] = { // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                ...(progress[gameState.game] || {}), // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                recorde: Math.max(gameState.score, progress[gameState.game]?.recorde || 0), // Explicação DevIN: Continua expressão ou bloco pertencente a função saveProgress.
                maior_fase: Math.max(gameState.level, progress[gameState.game]?.maior_fase || 0) // Explicação DevIN: Continua expressão ou bloco pertencente a função saveProgress.
            }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            if (feedbackEl) feedbackEl.textContent = 'Progresso salvo.'; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            if (engine === gameState) setStats(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        } catch (error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            if (feedbackEl) feedbackEl.textContent = error.message; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    async function gameOver() { // Explicação DevIN: Declara função/método gameOver, com seus parâmetros e comportamento.
        if (!engine || engine.ended) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        const endedGame = engine; // Explicação DevIN: Declara variável JavaScript endedGame para reutilizar valor ou resultado.
        endedGame.ended = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        if (!portal || !gameOverEl) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            showOverlay(`Fim de jogo — ${endedGame.score} pontos. Reinicie para jogar novamente.`); // Explicação DevIN: Continua expressão ou bloco pertencente a função gameOver.
            return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        restartButton.hidden = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        if (gameIntroEl) gameIntroEl.hidden = true; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        gameOverEl.hidden = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        if (overlayMessageEl) overlayMessageEl.hidden = true; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        showOverlay('', true); // Explicação DevIN: Continua expressão ou bloco pertencente a função gameOver.
        gameOverScoreEl.textContent = `Sua pontuação: ${endedGame.score} pontos · Fase ${endedGame.level}`; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        leaderboardMessageEl.textContent = 'Carregando classificação…'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        leaderboardEl.replaceChildren(); // Explicação DevIN: Continua expressão ou bloco pertencente a função gameOver.
        await saveProgress(endedGame); // Explicação DevIN: Continua expressão ou bloco pertencente a função gameOver.
        await loadLeaderboard(endedGame.game); // Explicação DevIN: Continua expressão ou bloco pertencente a função gameOver.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    function drawBackground() { // Explicação DevIN: Declara função/método drawBackground, com seus parâmetros e comportamento.
        ctx.fillStyle = colors.bg; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        ctx.fillRect(0, 0, activeCanvas.width, activeCanvas.height); // Explicação DevIN: Continua expressão ou bloco pertencente a função drawBackground.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    function makeMaze(level) { // Explicação DevIN: Declara função/método makeMaze, com seus parâmetros e comportamento.
        const layout = [ // Explicação DevIN: Declara variável JavaScript layout para reutilizar valor ou resultado.
            '#####################', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '#..o...............o#', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '#.###.###.#.###.###.#', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '#.....#...#...#.....#', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '###.#.#.###.#.#.#.###', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '#...#.#.....#.#.#...#', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '#.###.#####.#.###.#.#', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '#.....#.....#.....#.#', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '#####.#.P...#.#####.#', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '#.....#..G..#.....#.#', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '#.###.#####.#.###.#.#', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '#...#.#.....#.#.#...#', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '###.#.#.###.#.#.#.###', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '#.....#...#...#.....#', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '#.###.###.#.###.###.#', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '#..o...............o#', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            '#####################' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
        const grid = layout.map((row) => [...row]); // Explicação DevIN: Declara variável JavaScript grid para reutilizar valor ou resultado.
        if (level % 2 === 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            grid[3][10] = '.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            grid[13][10] = '.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        const start = { x: 8, y: 8 }; // Explicação DevIN: Declara variável JavaScript start para reutilizar valor ou resultado.
        const ghosts = [ // Explicação DevIN: Declara variável JavaScript ghosts para reutilizar valor ou resultado.
            { x: 2, y: 1, color: colors.red }, // Explicação DevIN: Continua expressão ou bloco pertencente a função makeMaze.
            { x: 17, y: 1, color: colors.pink }, // Explicação DevIN: Continua expressão ou bloco pertencente a função makeMaze.
            { x: 2, y: 15, color: colors.cyan }, // Explicação DevIN: Continua expressão ou bloco pertencente a função makeMaze.
            { x: 17, y: 15, color: colors.orange } // Explicação DevIN: Continua expressão ou bloco pertencente a função makeMaze.
        ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
        let pellets = 0; // Explicação DevIN: Declara variável JavaScript pellets para reutilizar valor ou resultado.
        grid.forEach((row, y) => row.forEach((tile, x) => { // Explicação DevIN: Continua expressão ou bloco pertencente a função makeMaze.
            if (tile === '.' || tile === 'o') pellets += 1; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            if (tile === 'P') grid[y][x] = ' '; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        })); // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
        return { grid, start, ghosts, pellets }; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    const directions = { // Explicação DevIN: Declara variável JavaScript directions para reutilizar valor ou resultado.
        ArrowUp: { x: 0, y: -1 }, KeyW: { x: 0, y: -1 }, // Explicação DevIN: Continua expressão ou bloco pertencente a função makeMaze.
        ArrowDown: { x: 0, y: 1 }, KeyS: { x: 0, y: 1 }, // Explicação DevIN: Continua expressão ou bloco pertencente a função makeMaze.
        ArrowLeft: { x: -1, y: 0 }, KeyA: { x: -1, y: 0 }, // Explicação DevIN: Continua expressão ou bloco pertencente a função makeMaze.
        ArrowRight: { x: 1, y: 0 }, KeyD: { x: 1, y: 0 } // Explicação DevIN: Continua expressão ou bloco pertencente a função makeMaze.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    function mazeEngine(level = 1, score = 0) { // Explicação DevIN: Declara função/método mazeEngine, com seus parâmetros e comportamento.
        const maze = makeMaze(level); // Explicação DevIN: Declara variável JavaScript maze para reutilizar valor ou resultado.
        const state = { // Explicação DevIN: Declara variável JavaScript state para reutilizar valor ou resultado.
            map: maze.grid, start: maze.start, pellets: maze.pellets, // Explicação DevIN: Continua expressão ou bloco pertencente a função mazeEngine.
            player: { ...maze.start, dir: { x: 0, y: 0 }, next: { x: 0, y: 0 } }, // Explicação DevIN: Continua expressão ou bloco pertencente a função mazeEngine.
            ghosts: maze.ghosts.map((ghost) => ({ ...ghost, startX: ghost.x, startY: ghost.y })), // Explicação DevIN: Continua expressão ou bloco pertencente a função mazeEngine.
            scaredUntil: 0, lives: 3, step: 0, lastMove: 0 // Explicação DevIN: Continua expressão ou bloco pertencente a função mazeEngine.
        }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        const directionsList = [{ x: 1, y: 0 }, { x: -1, y: 0 }, { x: 0, y: 1 }, { x: 0, y: -1 }]; // Explicação DevIN: Declara variável JavaScript directionsList para reutilizar valor ou resultado.
        const open = (x, y) => y >= 0 && y < state.map.length && x >= 0 && x < state.map[y].length // Explicação DevIN: Declara variável JavaScript open para reutilizar valor ou resultado.
            && state.map[y][x] !== '#'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        const move = (actor, dir) => { // Explicação DevIN: Declara variável JavaScript move para reutilizar valor ou resultado.
            if (!open(actor.x + dir.x, actor.y + dir.y)) return false; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            actor.x += dir.x; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            actor.y += dir.y; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            return true; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return { // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            game: 'maze', level, score, state, ended: false, // Explicação DevIN: Continua expressão ou bloco pertencente a função mazeEngine.
            key(key) { // Explicação DevIN: Continua expressão ou bloco pertencente a função mazeEngine.
                if (directions[key]) state.player.next = directions[key]; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            update(delta) { // Explicação DevIN: Continua expressão ou bloco pertencente a função mazeEngine.
                state.lastMove += delta; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                if (state.lastMove < mazeStepMs) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                state.lastMove %= mazeStepMs; // Explicação DevIN: Continua expressão ou bloco pertencente a função mazeEngine.
                if (open(state.player.x + state.player.next.x, state.player.y + state.player.next.y)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    state.player.dir = state.player.next; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                move(state.player, state.player.dir); // Explicação DevIN: Continua expressão ou bloco pertencente a função mazeEngine.
                let tile = state.map[state.player.y][state.player.x]; // Explicação DevIN: Declara variável JavaScript tile para reutilizar valor ou resultado.
                if (tile === '.' || tile === 'o') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    this.score += tile === 'o' ? 50 : 10; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    state.map[state.player.y][state.player.x] = ' '; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    state.pellets -= 1; // Explicação DevIN: Continua expressão ou bloco pertencente a função mazeEngine.
                    if (tile === 'o') state.scaredUntil = performance.now() + 7000; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                state.step += 1; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                if (state.step % 2 === 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    state.ghosts.forEach((ghost) => { // Explicação DevIN: Continua expressão ou bloco pertencente a função mazeEngine.
                        const options = directionsList.filter((dir) => open(ghost.x + dir.x, ghost.y + dir.y)); // Explicação DevIN: Declara variável JavaScript options para reutilizar valor ou resultado.
                        if (!options.length) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        const scared = performance.now() < state.scaredUntil; // Explicação DevIN: Declara variável JavaScript scared para reutilizar valor ou resultado.
                        options.sort((a, b) => { // Explicação DevIN: Continua expressão ou bloco pertencente a função mazeEngine.
                            const da = Math.abs(ghost.x + a.x - state.player.x) + Math.abs(ghost.y + a.y - state.player.y); // Explicação DevIN: Declara variável JavaScript da para reutilizar valor ou resultado.
                            const db = Math.abs(ghost.x + b.x - state.player.x) + Math.abs(ghost.y + b.y - state.player.y); // Explicação DevIN: Declara variável JavaScript db para reutilizar valor ou resultado.
                            return scared ? db - da : da - db; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
                        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                        const candidates = options.slice(0, Math.min(2, options.length)); // Explicação DevIN: Declara variável JavaScript candidates para reutilizar valor ou resultado.
                        const next = candidates[Math.floor(Math.random() * candidates.length)]; // Explicação DevIN: Declara variável JavaScript next para reutilizar valor ou resultado.
                        move(ghost, next); // Explicação DevIN: Continua expressão ou bloco pertencente a função mazeEngine.
                        if (ghost.x === state.player.x && ghost.y === state.player.y) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                            if (performance.now() < state.scaredUntil) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                                this.score += 200; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                                ghost.x = ghost.startX; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                                ghost.y = ghost.startY; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                            } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
                                state.lives -= 1; // Explicação DevIN: Continua expressão ou bloco pertencente a função mazeEngine.
                                if (state.lives <= 0) gameOver(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                                else { // Explicação DevIN: Define ramo alternativo para condição anterior.
                                    state.player = { ...state.start, dir: { x: 0, y: 0 }, next: { x: 0, y: 0 } }; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                                    state.ghosts.forEach((item) => { item.x = item.startX; item.y = item.startY; }); // Explicação DevIN: Continua expressão ou bloco pertencente a função mazeEngine.
                                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                if (state.pellets <= 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    this.level += 1; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    this.score += 250; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    const next = mazeEngine(this.level, this.score); // Explicação DevIN: Declara variável JavaScript next para reutilizar valor ou resultado.
                    engine = next; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    if (portal) saveProgress(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            draw() { // Explicação DevIN: Continua expressão ou bloco pertencente a função mazeEngine.
                drawBackground(); // Explicação DevIN: Continua expressão ou bloco pertencente a função mazeEngine.
                const size = 24; // Explicação DevIN: Declara variável JavaScript size para reutilizar valor ou resultado.
                const left = (activeCanvas.width - state.map[0].length * size) / 2; // Explicação DevIN: Declara variável JavaScript left para reutilizar valor ou resultado.
                const top = (activeCanvas.height - state.map.length * size) / 2; // Explicação DevIN: Declara variável JavaScript top para reutilizar valor ou resultado.
                state.map.forEach((row, y) => row.forEach((tile, x) => { // Explicação DevIN: Continua expressão ou bloco pertencente a função mazeEngine.
                    const rect = cellRect(x, y, size, left, top); // Explicação DevIN: Declara variável JavaScript rect para reutilizar valor ou resultado.
                    if (tile === '#') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        ctx.fillStyle = colors.wall; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                        ctx.fillRect(rect.x + 2, rect.y + 2, size - 4, size - 4); // Explicação DevIN: Continua expressão ou bloco pertencente a função mazeEngine.
                        ctx.strokeStyle = colors.line; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                        ctx.strokeRect(rect.x + 4, rect.y + 4, size - 8, size - 8); // Explicação DevIN: Continua expressão ou bloco pertencente a função mazeEngine.
                    } else if (tile === '.') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        ctx.fillStyle = '#fef3c7'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                        ctx.beginPath(); ctx.arc(rect.x + size / 2, rect.y + size / 2, 3, 0, Math.PI * 2); ctx.fill(); // Explicação DevIN: Continua expressão ou bloco pertencente a função mazeEngine.
                    } else if (tile === 'o') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        ctx.fillStyle = colors.yellow; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                        ctx.beginPath(); ctx.arc(rect.x + size / 2, rect.y + size / 2, 7, 0, Math.PI * 2); ctx.fill(); // Explicação DevIN: Continua expressão ou bloco pertencente a função mazeEngine.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                })); // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
                ctx.fillStyle = colors.yellow; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                ctx.beginPath(); ctx.arc(left + (state.player.x + .5) * size, top + (state.player.y + .5) * size, 9, 0, Math.PI * 2); ctx.fill(); // Explicação DevIN: Continua expressão ou bloco pertencente a função mazeEngine.
                state.ghosts.forEach((ghost) => { // Explicação DevIN: Continua expressão ou bloco pertencente a função mazeEngine.
                    ctx.fillStyle = performance.now() < state.scaredUntil ? '#3b82f6' : ghost.color; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    ctx.beginPath(); ctx.arc(left + (ghost.x + .5) * size, top + (ghost.y + .5) * size, 9, Math.PI, 0); ctx.lineTo(left + (ghost.x + 1) * size - 3, top + (ghost.y + 1) * size - 3); ctx.lineTo(left + ghost.x * size + 3, top + (ghost.y + 1) * size - 3); ctx.fill(); // Explicação DevIN: Continua expressão ou bloco pertencente a função mazeEngine.
                }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                ctx.fillStyle = colors.text; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                ctx.font = '16px Arial'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                ctx.fillText(`Vidas: ${state.lives}`, 16, 24); // Explicação DevIN: Continua expressão ou bloco pertencente a função mazeEngine.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    function snakeEngine(level = 1, score = 0) { // Explicação DevIN: Declara função/método snakeEngine, com seus parâmetros e comportamento.
        const state = { body: [{ x: 10, y: 10 }, { x: 9, y: 10 }, { x: 8, y: 10 }], dir: { x: 1, y: 0 }, next: { x: 1, y: 0 }, food: null, timer: 0 }; // Explicação DevIN: Declara variável JavaScript state para reutilizar valor ou resultado.
        const placeFood = () => { // Explicação DevIN: Declara variável JavaScript placeFood para reutilizar valor ou resultado.
            let food; // Explicação DevIN: Declara variável JavaScript food para reutilizar valor ou resultado.
            do { food = { x: Math.floor(Math.random() * 20), y: Math.floor(Math.random() * 18) }; } // Explicação DevIN: Continua expressão ou bloco pertencente a função snakeEngine.
            while (state.body.some((part) => part.x === food.x && part.y === food.y)); // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            state.food = food; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        placeFood(); // Explicação DevIN: Continua expressão ou bloco pertencente a função snakeEngine.
        return { // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            game: 'snake', level, score, state, ended: false, // Explicação DevIN: Continua expressão ou bloco pertencente a função snakeEngine.
            key(key) { // Explicação DevIN: Continua expressão ou bloco pertencente a função snakeEngine.
                const next = directions[key]; // Explicação DevIN: Declara variável JavaScript next para reutilizar valor ou resultado.
                if (next && next.x !== -state.dir.x && next.y !== -state.dir.y) state.next = next; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            update(delta) { // Explicação DevIN: Continua expressão ou bloco pertencente a função snakeEngine.
                state.timer += delta; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                if (state.timer < Math.max(55, 150 - this.level * 8)) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                state.timer = 0; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                state.dir = state.next; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                const head = { x: state.body[0].x + state.dir.x, y: state.body[0].y + state.dir.y }; // Explicação DevIN: Declara variável JavaScript head para reutilizar valor ou resultado.
                if (head.x < 0 || head.x >= 20 || head.y < 0 || head.y >= 18 // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    || state.body.some((part, index) => index < state.body.length - 1 && part.x === head.x && part.y === head.y)) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    gameOver(); return; // Explicação DevIN: Continua expressão ou bloco pertencente a função snakeEngine.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                state.body.unshift(head); // Explicação DevIN: Continua expressão ou bloco pertencente a função snakeEngine.
                if (head.x === state.food.x && head.y === state.food.y) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    this.score += 10; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    if (this.score > 0 && this.score % 50 === 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        this.level += 1; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                        if (portal) saveProgress(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                    placeFood(); // Explicação DevIN: Continua expressão ou bloco pertencente a função snakeEngine.
                } else state.body.pop(); // Explicação DevIN: Define ramo alternativo para condição anterior.
            }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            draw() { // Explicação DevIN: Continua expressão ou bloco pertencente a função snakeEngine.
                drawBackground(); // Explicação DevIN: Continua expressão ou bloco pertencente a função snakeEngine.
                const size = 22; // Explicação DevIN: Declara variável JavaScript size para reutilizar valor ou resultado.
                const left = (activeCanvas.width - size * 20) / 2; // Explicação DevIN: Declara variável JavaScript left para reutilizar valor ou resultado.
                const top = (activeCanvas.height - size * 18) / 2; // Explicação DevIN: Declara variável JavaScript top para reutilizar valor ou resultado.
                ctx.strokeStyle = '#334155'; ctx.strokeRect(left, top, size * 20, size * 18); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                ctx.fillStyle = colors.red; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                ctx.beginPath(); ctx.arc(left + state.food.x * size + size / 2, top + state.food.y * size + size / 2, size / 3, 0, Math.PI * 2); ctx.fill(); // Explicação DevIN: Continua expressão ou bloco pertencente a função snakeEngine.
                state.body.forEach((part, index) => { // Explicação DevIN: Continua expressão ou bloco pertencente a função snakeEngine.
                    ctx.fillStyle = index === 0 ? colors.green : '#059669'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    ctx.fillRect(left + part.x * size + 1, top + part.y * size + 1, size - 2, size - 2); // Explicação DevIN: Continua expressão ou bloco pertencente a função snakeEngine.
                }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    const pieces = [ // Explicação DevIN: Declara variável JavaScript pieces para reutilizar valor ou resultado.
        [[1, 1, 1, 1]], // Explicação DevIN: Continua expressão ou bloco pertencente a função snakeEngine.
        [[1, 1], [1, 1]], // Explicação DevIN: Continua expressão ou bloco pertencente a função snakeEngine.
        [[0, 1, 0], [1, 1, 1]], // Explicação DevIN: Continua expressão ou bloco pertencente a função snakeEngine.
        [[1, 0, 0], [1, 1, 1]], // Explicação DevIN: Continua expressão ou bloco pertencente a função snakeEngine.
        [[0, 0, 1], [1, 1, 1]], // Explicação DevIN: Continua expressão ou bloco pertencente a função snakeEngine.
        [[1, 1, 0], [0, 1, 1]], // Explicação DevIN: Continua expressão ou bloco pertencente a função snakeEngine.
        [[0, 1, 1], [1, 1, 0]] // Explicação DevIN: Continua expressão ou bloco pertencente a função snakeEngine.
    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.

    function tetrisEngine(level = 1, score = 0) { // Explicação DevIN: Declara função/método tetrisEngine, com seus parâmetros e comportamento.
        const state = { grid: Array.from({ length: 20 }, () => Array(10).fill(0)), piece: null, lines: 0, timer: 0 }; // Explicação DevIN: Declara variável JavaScript state para reutilizar valor ou resultado.
        let game; // Explicação DevIN: Declara variável JavaScript game para reutilizar valor ou resultado.
        const newPiece = () => { // Explicação DevIN: Declara variável JavaScript newPiece para reutilizar valor ou resultado.
            const shape = pieces[Math.floor(Math.random() * pieces.length)].map((row) => [...row]); // Explicação DevIN: Declara variável JavaScript shape para reutilizar valor ou resultado.
            state.piece = { shape, x: Math.floor((10 - shape[0].length) / 2), y: 0, color: 1 + Math.floor(Math.random() * 6) }; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            if (collides(state.piece.x, state.piece.y, shape)) gameOver(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        const collides = (x, y, shape) => shape.some((row, dy) => row.some((cell, dx) => // Explicação DevIN: Declara variável JavaScript collides para reutilizar valor ou resultado.
            cell && (x + dx < 0 || x + dx >= 10 || y + dy >= 20 || (y + dy >= 0 && state.grid[y + dy][x + dx])))); // Explicação DevIN: Continua expressão ou bloco pertencente a função tetrisEngine.
        const lock = () => { // Explicação DevIN: Declara variável JavaScript lock para reutilizar valor ou resultado.
            state.piece.shape.forEach((row, dy) => row.forEach((cell, dx) => { // Explicação DevIN: Continua expressão ou bloco pertencente a função tetrisEngine.
                if (cell && state.piece.y + dy >= 0) state.grid[state.piece.y + dy][state.piece.x + dx] = state.piece.color; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            })); // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
            const remain = state.grid.filter((row) => row.some((cell) => !cell)); // Explicação DevIN: Declara variável JavaScript remain para reutilizar valor ou resultado.
            state.lines += 20 - remain.length; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            game.score += (20 - remain.length) * 100 * game.level; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            while (remain.length < 20) remain.unshift(Array(10).fill(0)); // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            state.grid = remain; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            const nextLevel = 1 + Math.floor(state.lines / 10); // Explicação DevIN: Declara variável JavaScript nextLevel para reutilizar valor ou resultado.
            if (nextLevel > game.level) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                game.level = nextLevel; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                if (portal) saveProgress(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            newPiece(); // Explicação DevIN: Continua expressão ou bloco pertencente a função tetrisEngine.
        }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        const rotate = (shape) => shape[0].map((_, i) => shape.map((row) => row[i]).reverse()); // Explicação DevIN: Declara variável JavaScript rotate para reutilizar valor ou resultado.
        newPiece(); // Explicação DevIN: Continua expressão ou bloco pertencente a função tetrisEngine.
        game = { // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            game: 'tetris', level, score, state, ended: false, // Explicação DevIN: Continua expressão ou bloco pertencente a função tetrisEngine.
            key(key) { // Explicação DevIN: Continua expressão ou bloco pertencente a função tetrisEngine.
                if (key === 'ArrowLeft' || key === 'KeyA') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    if (!collides(state.piece.x - 1, state.piece.y, state.piece.shape)) state.piece.x -= 1; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                } else if (key === 'ArrowRight' || key === 'KeyD') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    if (!collides(state.piece.x + 1, state.piece.y, state.piece.shape)) state.piece.x += 1; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                } else if (key === 'ArrowDown' || key === 'KeyS') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    if (!collides(state.piece.x, state.piece.y + 1, state.piece.shape)) state.piece.y += 1; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    else lock(); // Explicação DevIN: Define ramo alternativo para condição anterior.
                } else if (key === 'ArrowUp' || key === 'KeyW') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    const turned = rotate(state.piece.shape); // Explicação DevIN: Declara variável JavaScript turned para reutilizar valor ou resultado.
                    if (!collides(state.piece.x, state.piece.y, turned)) state.piece.shape = turned; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                } else if (key === 'Space') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    while (!collides(state.piece.x, state.piece.y + 1, state.piece.shape)) state.piece.y += 1; // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                    lock(); // Explicação DevIN: Continua expressão ou bloco pertencente a função tetrisEngine.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            update(delta) { // Explicação DevIN: Continua expressão ou bloco pertencente a função tetrisEngine.
                state.timer += delta; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                if (state.timer < Math.max(100, 800 - this.level * 55)) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                state.timer = 0; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                if (!collides(state.piece.x, state.piece.y + 1, state.piece.shape)) state.piece.y += 1; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                else lock(); // Explicação DevIN: Define ramo alternativo para condição anterior.
            }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            draw() { // Explicação DevIN: Continua expressão ou bloco pertencente a função tetrisEngine.
                drawBackground(); // Explicação DevIN: Continua expressão ou bloco pertencente a função tetrisEngine.
                const size = 20; // Explicação DevIN: Declara variável JavaScript size para reutilizar valor ou resultado.
                const left = (activeCanvas.width - size * 10) / 2; // Explicação DevIN: Declara variável JavaScript left para reutilizar valor ou resultado.
                const top = (activeCanvas.height - size * 20) / 2; // Explicação DevIN: Declara variável JavaScript top para reutilizar valor ou resultado.
                const palette = [colors.bg, colors.cyan, colors.yellow, colors.pink, colors.orange, colors.green, colors.red]; // Explicação DevIN: Declara variável JavaScript palette para reutilizar valor ou resultado.
                state.grid.forEach((row, y) => row.forEach((cell, x) => { // Explicação DevIN: Continua expressão ou bloco pertencente a função tetrisEngine.
                    ctx.fillStyle = palette[cell]; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    if (cell) ctx.fillRect(left + x * size + 1, top + y * size + 1, size - 2, size - 2); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    ctx.strokeStyle = '#1e293b'; ctx.strokeRect(left + x * size, top + y * size, size, size); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                })); // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
                if (state.piece) state.piece.shape.forEach((row, dy) => row.forEach((cell, dx) => { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    if (!cell) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    ctx.fillStyle = palette[state.piece.color]; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    ctx.fillRect(left + (state.piece.x + dx) * size + 1, top + (state.piece.y + dy) * size + 1, size - 2, size - 2); // Explicação DevIN: Continua expressão ou bloco pertencente a função tetrisEngine.
                })); // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return game; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    function breakoutEngine(level = 1, score = 0) { // Explicação DevIN: Declara função/método breakoutEngine, com seus parâmetros e comportamento.
        const state = { paddleX: 270, ballX: 320, ballY: 380, vx: 3, vy: -4, bricks: [], lives: 3, timer: 0 }; // Explicação DevIN: Declara variável JavaScript state para reutilizar valor ou resultado.
        let currentLevel = level; // Explicação DevIN: Declara variável JavaScript currentLevel para reutilizar valor ou resultado.
        const createBricks = () => { // Explicação DevIN: Declara variável JavaScript createBricks para reutilizar valor ou resultado.
            state.bricks = []; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            const rows = Math.min(4 + currentLevel, 8); // Explicação DevIN: Declara variável JavaScript rows para reutilizar valor ou resultado.
            for (let row = 0; row < rows; row += 1) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                for (let col = 0; col < 10; col += 1) state.bricks.push({ x: 40 + col * 56, y: 55 + row * 24, alive: true }); // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        const resetBall = () => { state.ballX = 320; state.ballY = 380; state.vx = 3 + Math.random(); state.vy = -4; }; // Explicação DevIN: Declara variável JavaScript resetBall para reutilizar valor ou resultado.
        createBricks(); // Explicação DevIN: Continua expressão ou bloco pertencente a função breakoutEngine.
        return { // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            game: 'breakout', level, score, state, ended: false, // Explicação DevIN: Continua expressão ou bloco pertencente a função breakoutEngine.
            key(key) { // Explicação DevIN: Continua expressão ou bloco pertencente a função breakoutEngine.
                if (key === 'ArrowLeft' || key === 'KeyA') state.paddleX -= 30; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                if (key === 'ArrowRight' || key === 'KeyD') state.paddleX += 30; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                state.paddleX = Math.max(45, Math.min(595, state.paddleX)); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            pointer(event) { // Explicação DevIN: Continua expressão ou bloco pertencente a função breakoutEngine.
                const rect = activeCanvas.getBoundingClientRect(); // Explicação DevIN: Declara variável JavaScript rect para reutilizar valor ou resultado.
                state.paddleX = Math.max(45, Math.min(595, ((event.clientX - rect.left) / rect.width) * activeCanvas.width)); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            update(delta) { // Explicação DevIN: Continua expressão ou bloco pertencente a função breakoutEngine.
                state.timer += delta; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                if (state.timer < 16) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                state.timer = 0; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                state.ballX += state.vx; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                state.ballY += state.vy; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                if (state.ballX < 12 || state.ballX > 628) state.vx *= -1; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                if (state.ballY < 12) state.vy *= -1; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                if (state.ballY > 418 && state.ballY < 438 && Math.abs(state.ballX - state.paddleX) < 55 && state.vy > 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    state.vy = -Math.abs(state.vy); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    state.vx += (state.ballX - state.paddleX) * 0.035; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                state.bricks.forEach((brick) => { // Explicação DevIN: Continua expressão ou bloco pertencente a função breakoutEngine.
                    if (brick.alive && state.ballX >= brick.x && state.ballX <= brick.x + 50 // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        && state.ballY >= brick.y && state.ballY <= brick.y + 16 && state.vy < 0) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                        brick.alive = false; state.vy *= -1; this.score += 10; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                if (!state.bricks.some((brick) => brick.alive)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    this.level += 1; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    currentLevel = this.level; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    this.score += 100; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    createBricks(); resetBall(); // Explicação DevIN: Continua expressão ou bloco pertencente a função breakoutEngine.
                    if (portal) saveProgress(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                if (state.ballY > 480) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    state.lives -= 1; // Explicação DevIN: Continua expressão ou bloco pertencente a função breakoutEngine.
                    if (state.lives <= 0) gameOver(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    else resetBall(); // Explicação DevIN: Define ramo alternativo para condição anterior.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            draw() { // Explicação DevIN: Continua expressão ou bloco pertencente a função breakoutEngine.
                drawBackground(); // Explicação DevIN: Continua expressão ou bloco pertencente a função breakoutEngine.
                state.bricks.forEach((brick, index) => { // Explicação DevIN: Continua expressão ou bloco pertencente a função breakoutEngine.
                    if (!brick.alive) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    ctx.fillStyle = [colors.cyan, colors.pink, colors.orange, colors.green][index % 4]; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    ctx.fillRect(brick.x, brick.y, 50, 16); // Explicação DevIN: Continua expressão ou bloco pertencente a função breakoutEngine.
                }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                ctx.fillStyle = colors.text; ctx.fillRect(state.paddleX - 48, 430, 96, 12); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                ctx.fillStyle = colors.yellow; ctx.beginPath(); ctx.arc(state.ballX, state.ballY, 8, 0, Math.PI * 2); ctx.fill(); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                ctx.fillStyle = colors.text; ctx.font = '16px Arial'; ctx.fillText(`Vidas: ${state.lives}`, 16, 24); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    function memoryEngine(level = 1, score = 0) { // Explicação DevIN: Declara função/método memoryEngine, com seus parâmetros e comportamento.
        const symbols = ['◆', '●', '▲', '■', '★', '✦', '⬟', '✚', '☾', '✿', '⬢', '♥']; // Explicação DevIN: Declara variável JavaScript symbols para reutilizar valor ou resultado.
        const pairCount = Math.min(4 + level - 1, symbols.length); // Explicação DevIN: Declara variável JavaScript pairCount para reutilizar valor ou resultado.
        const cards = [...symbols.slice(0, pairCount), ...symbols.slice(0, pairCount)] // Explicação DevIN: Declara variável JavaScript cards para reutilizar valor ou resultado.
            .sort(() => Math.random() - 0.5) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            .map((symbol, id) => ({ id, symbol, revealed: false, matched: false })); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        const state = { cards, first: null, lock: false, timer: 0, attempts: 0, matches: 0 }; // Explicação DevIN: Declara variável JavaScript state para reutilizar valor ou resultado.
        return { // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            game: 'memory', level, score, state, ended: false, // Explicação DevIN: Continua expressão ou bloco pertencente a função memoryEngine.
            key() {}, // Explicação DevIN: Continua expressão ou bloco pertencente a função memoryEngine.
            pointer(event) { // Explicação DevIN: Continua expressão ou bloco pertencente a função memoryEngine.
                if (state.lock) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                const rect = activeCanvas.getBoundingClientRect(); // Explicação DevIN: Declara variável JavaScript rect para reutilizar valor ou resultado.
                const x = ((event.clientX - rect.left) / rect.width) * activeCanvas.width; // Explicação DevIN: Declara variável JavaScript x para reutilizar valor ou resultado.
                const y = ((event.clientY - rect.top) / rect.height) * activeCanvas.height; // Explicação DevIN: Declara variável JavaScript y para reutilizar valor ou resultado.
                const cols = Math.min(6, Math.ceil(Math.sqrt(cards.length))); // Explicação DevIN: Declara variável JavaScript cols para reutilizar valor ou resultado.
                const rows = Math.ceil(cards.length / cols); // Explicação DevIN: Declara variável JavaScript rows para reutilizar valor ou resultado.
                const cardWidth = Math.min(82, 480 / cols); // Explicação DevIN: Declara variável JavaScript cardWidth para reutilizar valor ou resultado.
                const cardHeight = Math.min(80, 340 / rows); // Explicação DevIN: Declara variável JavaScript cardHeight para reutilizar valor ou resultado.
                const left = (activeCanvas.width - cols * cardWidth) / 2; // Explicação DevIN: Declara variável JavaScript left para reutilizar valor ou resultado.
                const top = (activeCanvas.height - rows * cardHeight) / 2; // Explicação DevIN: Declara variável JavaScript top para reutilizar valor ou resultado.
                const col = Math.floor((x - left) / cardWidth); // Explicação DevIN: Declara variável JavaScript col para reutilizar valor ou resultado.
                const row = Math.floor((y - top) / cardHeight); // Explicação DevIN: Declara variável JavaScript row para reutilizar valor ou resultado.
                const index = row * cols + col; // Explicação DevIN: Declara variável JavaScript index para reutilizar valor ou resultado.
                const card = cards[index]; // Explicação DevIN: Declara variável JavaScript card para reutilizar valor ou resultado.
                if (!card || card.revealed || card.matched || x < left || y < top) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                card.revealed = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                if (state.first === null) state.first = card; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                else { // Explicação DevIN: Define ramo alternativo para condição anterior.
                    state.attempts += 1; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    if (state.first.symbol === card.symbol) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        state.first.matched = true; card.matched = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                        state.matches += 1; this.score += 100; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                        state.first = null; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                        if (state.matches === pairCount) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                            this.level += 1; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                            if (portal) saveProgress(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                            setTimeout(() => { if (engine?.game === 'memory' && engine.level === this.level) engine = memoryEngine(this.level, this.score); }, 900); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                    } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
                        state.lock = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                        if (state.attempts >= pairCount * 3) gameOver(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        setTimeout(() => { // Explicação DevIN: Continua expressão ou bloco pertencente a função memoryEngine.
                            state.first.revealed = false; card.revealed = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                            state.first = null; state.lock = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                        }, 700); // Explicação DevIN: Continua expressão ou bloco pertencente a função memoryEngine.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            update() {}, // Explicação DevIN: Continua expressão ou bloco pertencente a função memoryEngine.
            draw() { // Explicação DevIN: Continua expressão ou bloco pertencente a função memoryEngine.
                drawBackground(); // Explicação DevIN: Continua expressão ou bloco pertencente a função memoryEngine.
                const cols = Math.min(6, Math.ceil(Math.sqrt(cards.length))); // Explicação DevIN: Declara variável JavaScript cols para reutilizar valor ou resultado.
                const rows = Math.ceil(cards.length / cols); // Explicação DevIN: Declara variável JavaScript rows para reutilizar valor ou resultado.
                const cardWidth = Math.min(82, 480 / cols); // Explicação DevIN: Declara variável JavaScript cardWidth para reutilizar valor ou resultado.
                const cardHeight = Math.min(80, 340 / rows); // Explicação DevIN: Declara variável JavaScript cardHeight para reutilizar valor ou resultado.
                const left = (activeCanvas.width - cols * cardWidth) / 2; // Explicação DevIN: Declara variável JavaScript left para reutilizar valor ou resultado.
                const top = (activeCanvas.height - rows * cardHeight) / 2; // Explicação DevIN: Declara variável JavaScript top para reutilizar valor ou resultado.
                cards.forEach((card, index) => { // Explicação DevIN: Continua expressão ou bloco pertencente a função memoryEngine.
                    const x = left + (index % cols) * cardWidth; // Explicação DevIN: Declara variável JavaScript x para reutilizar valor ou resultado.
                    const y = top + Math.floor(index / cols) * cardHeight; // Explicação DevIN: Declara variável JavaScript y para reutilizar valor ou resultado.
                    ctx.fillStyle = card.matched ? '#14532d' : (card.revealed ? '#1d4ed8' : '#1e293b'); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    ctx.fillRect(x + 3, y + 3, cardWidth - 6, cardHeight - 6); // Explicação DevIN: Continua expressão ou bloco pertencente a função memoryEngine.
                    ctx.strokeStyle = '#60a5fa'; ctx.strokeRect(x + 3, y + 3, cardWidth - 6, cardHeight - 6); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    if (card.revealed || card.matched) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        ctx.fillStyle = colors.text; ctx.font = '36px Arial'; ctx.textAlign = 'center'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                        ctx.fillText(card.symbol, x + cardWidth / 2, y + cardHeight / 2 + 12); // Explicação DevIN: Continua expressão ou bloco pertencente a função memoryEngine.
                    } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
                        ctx.fillStyle = colors.cyan; ctx.font = '24px Arial'; ctx.textAlign = 'center'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                        ctx.fillText('?', x + cardWidth / 2, y + cardHeight / 2 + 8); // Explicação DevIN: Continua expressão ou bloco pertencente a função memoryEngine.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                ctx.textAlign = 'start'; ctx.fillStyle = colors.text; ctx.font = '16px Arial'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                ctx.fillText(`Tentativas: ${state.attempts} | Pares: ${state.matches}/${pairCount}`, 16, 24); // Explicação DevIN: Continua expressão ou bloco pertencente a função memoryEngine.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    function runnerEngine(level = 1, score = 0) { // Explicação DevIN: Declara função/método runnerEngine, com seus parâmetros e comportamento.
        const state = { y: 380, vy: 0, obstacles: [], distance: 0, timer: 0, nextObstacle: 250, onGround: true }; // Explicação DevIN: Declara variável JavaScript state para reutilizar valor ou resultado.
        return { // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            game: 'runner', level, score, state, ended: false, // Explicação DevIN: Continua expressão ou bloco pertencente a função runnerEngine.
            key(key) { // Explicação DevIN: Continua expressão ou bloco pertencente a função runnerEngine.
                if ((key === 'Space' || key === 'ArrowUp' || key === 'KeyW') && state.onGround) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    state.vy = -520; state.onGround = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            pointer() { this.key('Space'); }, // Explicação DevIN: Continua expressão ou bloco pertencente a função runnerEngine.
            update(delta) { // Explicação DevIN: Continua expressão ou bloco pertencente a função runnerEngine.
                const seconds = delta / 1000; // Explicação DevIN: Declara variável JavaScript seconds para reutilizar valor ou resultado.
                state.distance += seconds * (220 + this.level * 18); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                this.score = Math.floor(state.distance); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                state.vy += 1400 * seconds; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                state.y += state.vy * seconds; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                if (state.y >= 380) { state.y = 380; state.vy = 0; state.onGround = true; } // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                const speed = 220 + this.level * 18; // Explicação DevIN: Declara variável JavaScript speed para reutilizar valor ou resultado.
                state.obstacles.forEach((item) => { item.x -= speed * seconds; }); // Explicação DevIN: Continua expressão ou bloco pertencente a função runnerEngine.
                state.obstacles = state.obstacles.filter((item) => item.x > -40); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                state.nextObstacle -= speed * seconds; // Explicação DevIN: Continua expressão ou bloco pertencente a função runnerEngine.
                if (state.nextObstacle <= 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    state.obstacles.push({ x: 660, height: 24 + Math.random() * 28, width: 22 + Math.random() * 16 }); // Explicação DevIN: Continua expressão ou bloco pertencente a função runnerEngine.
                    state.nextObstacle = 260 + Math.random() * 220; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                if (state.obstacles.some((item) => 115 < item.x + item.width && 145 > item.x // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    && state.y + 34 > 400 - item.height)) gameOver(); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                const nextLevel = 1 + Math.floor(state.distance / 1000); // Explicação DevIN: Declara variável JavaScript nextLevel para reutilizar valor ou resultado.
                if (nextLevel > this.level) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    this.level = nextLevel; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    if (portal) saveProgress(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            draw() { // Explicação DevIN: Continua expressão ou bloco pertencente a função runnerEngine.
                drawBackground(); // Explicação DevIN: Continua expressão ou bloco pertencente a função runnerEngine.
                ctx.fillStyle = '#164e63'; ctx.fillRect(0, 410, 640, 70); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                ctx.strokeStyle = colors.cyan; ctx.lineWidth = 2; ctx.beginPath(); ctx.moveTo(0, 410); ctx.lineTo(640, 410); ctx.stroke(); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                ctx.fillStyle = colors.yellow; ctx.fillRect(112, state.y, 32, 32); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                ctx.fillStyle = colors.pink; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                state.obstacles.forEach((item) => ctx.fillRect(item.x, 410 - item.height, item.width, item.height)); // Explicação DevIN: Continua expressão ou bloco pertencente a função runnerEngine.
                ctx.fillStyle = colors.text; ctx.font = '16px Arial'; ctx.fillText(`${Math.floor(state.distance)} m`, 16, 24); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Novos motores seguem o contrato key(), pointer(), update() e draw().
    // A persistência de recordes e a classificação seguem sob controle da API original.
    const extraFactories = window.DevINArcadeExtraGames?.create({ // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        ctx, canvas: activeCanvas, colors, gameOver, saveProgress // Explicação DevIN: Continua expressão ou bloco pertencente a função runnerEngine.
    }).factories || {}; // Explicação DevIN: Continua expressão ou bloco pertencente a função runnerEngine.
    const factories = { // Explicação DevIN: Declara variável JavaScript factories para reutilizar valor ou resultado.
        maze: mazeEngine, snake: snakeEngine, tetris: tetrisEngine, // Explicação DevIN: Continua expressão ou bloco pertencente a função runnerEngine.
        breakout: breakoutEngine, memory: memoryEngine, runner: runnerEngine, // Explicação DevIN: Controla a continuação ou saída de laço/seleção.
        ...extraFactories // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    async function launch(game) { // Explicação DevIN: Declara função/método launch, com seus parâmetros e comportamento.
        if (!factories[game]) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        clearCountdown(); // Explicação DevIN: Continua expressão ou bloco pertencente a função launch.
        if (portal) await apiRequest({ acao: 'iniciar', jogo: game }); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        engine = factories[game](1, 0); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        saveTimer = 0; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        if (portal) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            titleEl.textContent = games[game].title; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            hintEl.textContent = games[game].hint; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            restartButton.hidden = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            gameOverEl.hidden = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            const prior = progress[game]; // Explicação DevIN: Declara variável JavaScript prior para reutilizar valor ou resultado.
            bestEl.textContent = String(prior?.recorde || 0); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            feedbackEl.textContent = prior ? `Maior fase registrada: ${prior.maior_fase}.` : ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            if (gameIntroEl) gameIntroEl.hidden = true; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            showOverlay('', false); // Explicação DevIN: Continua expressão ou bloco pertencente a função launch.
        } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
            showOverlay('', false); // Explicação DevIN: Continua expressão ou bloco pertencente a função launch.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        setStats(); // Explicação DevIN: Continua expressão ou bloco pertencente a função launch.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    function selectGame(game) { // Explicação DevIN: Declara função/método selectGame, com seus parâmetros e comportamento.
        if (!factories[game]) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        clearCountdown(); // Explicação DevIN: Continua expressão ou bloco pertencente a função selectGame.
        selectedGame = game; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        engine = null; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        if (!portal) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            launch(game); // Explicação DevIN: Continua expressão ou bloco pertencente a função selectGame.
            return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        const details = games[game]; // Explicação DevIN: Declara variável JavaScript details para reutilizar valor ou resultado.
        titleEl.textContent = details.title; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        hintEl.textContent = 'Leia as instruções e inicie quando estiver pronto.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        gameIntroTitleEl.textContent = details.title; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        gameDescriptionEl.textContent = details.description; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        gameControlsEl.textContent = details.hint; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        gameIntroEl.hidden = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        if (gameOverEl) gameOverEl.hidden = true; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        restartButton.hidden = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        scoreEl.textContent = '0'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        levelEl.textContent = '1'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        bestEl.textContent = String(progress[game]?.recorde || 0); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        feedbackEl.textContent = ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        showOverlay('', true); // Explicação DevIN: Continua expressão ou bloco pertencente a função selectGame.
        if (overlayMessageEl) overlayMessageEl.hidden = true; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    function startSelectedGame() { // Explicação DevIN: Declara função/método startSelectedGame, com seus parâmetros e comportamento.
        if (!selectedGame || !portal) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        clearCountdown(); // Explicação DevIN: Continua expressão ou bloco pertencente a função startSelectedGame.
        engine = null; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        restartButton.hidden = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        gameOverEl.hidden = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        if (startButton) startButton.disabled = true; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        gameIntroEl.hidden = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        if (overlayMessageEl) overlayMessageEl.hidden = false; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

        let seconds = 3; // Explicação DevIN: Declara variável JavaScript seconds para reutilizar valor ou resultado.
        showOverlay(`O jogo começa em ${seconds}...`); // Explicação DevIN: Continua expressão ou bloco pertencente a função startSelectedGame.
        countdownInterval = window.setInterval(() => { // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            seconds -= 1; // Explicação DevIN: Continua expressão ou bloco pertencente a função startSelectedGame.
            if (seconds > 0) showOverlay(`O jogo começa em ${seconds}...`); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        }, 1000); // Explicação DevIN: Continua expressão ou bloco pertencente a função startSelectedGame.
        countdownTimeout = window.setTimeout(() => { // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            launch(selectedGame).catch((error) => { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
                gameIntroEl.hidden = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                if (overlayMessageEl) overlayMessageEl.hidden = true; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                showOverlay('', true); // Explicação DevIN: Continua expressão ou bloco pertencente a função startSelectedGame.
                feedbackEl.textContent = error.message; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                if (startButton) startButton.disabled = false; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        }, 3000); // Explicação DevIN: Continua expressão ou bloco pertencente a função startSelectedGame.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    function restartCurrentGame() { // Explicação DevIN: Declara função/método restartCurrentGame, com seus parâmetros e comportamento.
        if (!engine?.game) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        selectedGame = engine.game; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        if (portal) startSelectedGame(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        else launch(selectedGame); // Explicação DevIN: Define ramo alternativo para condição anterior.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    async function loadProgress() { // Explicação DevIN: Declara função/método loadProgress, com seus parâmetros e comportamento.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            const data = await apiRequest({}, 'GET'); // Explicação DevIN: Declara variável JavaScript data para reutilizar valor ou resultado.
            data.progresso.forEach((item) => { progress[item.jogo] = item; }); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadProgress.
            if (engine) setStats(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        } catch (error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            if (feedbackEl) feedbackEl.textContent = error.message; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    async function loadLeaderboard(game) { // Explicação DevIN: Declara função/método loadLeaderboard, com seus parâmetros e comportamento.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            const data = await apiRequest({ acao: 'classificacao', jogo: game }); // Explicação DevIN: Declara variável JavaScript data para reutilizar valor ou resultado.
            if (!data.classificacao.length) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                leaderboardMessageEl.textContent = 'Ainda não há pontuações para este jogo.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            leaderboardMessageEl.textContent = ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            const rows = document.createDocumentFragment(); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            data.classificacao.forEach((item, index) => { // Explicação DevIN: Continua expressão ou bloco pertencente a função loadLeaderboard.
                const row = document.createElement('li'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                const name = document.createElement('span'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                const score = document.createElement('strong'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                name.textContent = `${index + 1}. ${item.nome}`; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                score.textContent = `${item.recorde} pts · fase ${item.maior_fase}`; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                row.append(name, score); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadLeaderboard.
                rows.appendChild(row); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadLeaderboard.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            leaderboardEl.replaceChildren(rows); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadLeaderboard.
        } catch (error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            leaderboardMessageEl.textContent = error.message; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    async function loadAdminProgress() { // Explicação DevIN: Declara função/método loadAdminProgress, com seus parâmetros e comportamento.
        const target = document.querySelector('[data-admin-progress]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        if (!target) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            const data = await apiRequest({ escopo: 'todos' }, 'GET'); // Explicação DevIN: Declara variável JavaScript data para reutilizar valor ou resultado.
            // A consulta de todos os perfis é restrita a administradores no servidor.
            target.replaceChildren(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAdminProgress.
            if (!data.progresso.length) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                target.textContent = 'Ainda não há progresso salvo.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            const table = document.createElement('table'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            table.className = 'admin-progress-grid'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            const header = table.createTHead().insertRow(); // Explicação DevIN: Declara variável JavaScript header para reutilizar valor ou resultado.
            ['Conta', 'Perfil', 'Jogo', 'Recorde', 'Maior fase', 'Última atividade', 'Ação'].forEach((label) => { // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAdminProgress.
                const cell = document.createElement('th'); cell.textContent = label; header.appendChild(cell); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            const body = table.createTBody(); // Explicação DevIN: Declara variável JavaScript body para reutilizar valor ou resultado.
            data.progresso.forEach((item) => { // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAdminProgress.
                const row = body.insertRow(); // Explicação DevIN: Declara variável JavaScript row para reutilizar valor ou resultado.
                [item.nome, item.perfil_tipo, games[item.jogo]?.title || item.jogo, item.recorde, item.maior_fase, item.ultima_atividade] // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAdminProgress.
                    .forEach((value) => { const cell = row.insertCell(); cell.textContent = String(value); }); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                const action = row.insertCell(); // Explicação DevIN: Declara variável JavaScript action para reutilizar valor ou resultado.
                const button = document.createElement('button'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                button.type = 'button'; button.className = 'arcade-button danger-button'; button.textContent = 'Remover'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                button.addEventListener('click', async () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
                    if (!window.confirm(`Remover o progresso de ${item.nome} em ${games[item.jogo]?.title || item.jogo}?`)) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                        await apiRequest({ acao: 'excluir', perfil_tipo: item.perfil_tipo, perfil_id: String(item.perfil_id), jogo: item.jogo }); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAdminProgress.
                        await loadAdminProgress(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAdminProgress.
                    } catch (error) { target.textContent = error.message; } // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
                }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                action.appendChild(button); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAdminProgress.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            target.appendChild(table); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAdminProgress.
        } catch (error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            target.textContent = error.message; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    document.querySelectorAll('[data-launch-game]').forEach((button) => { // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        button.addEventListener('click', () => selectGame(button.dataset.launchGame)); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    startButton?.addEventListener('click', startSelectedGame); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    gameOverRestartButton?.addEventListener('click', restartCurrentGame); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    restartButton?.addEventListener('click', restartCurrentGame); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.

    window.addEventListener('keydown', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        if (!engine || engine.ended) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', 'Space'].includes(event.code)) event.preventDefault(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        engine.key(event.code); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAdminProgress.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    activeCanvas.addEventListener('pointerdown', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        if (!engine?.ended) engine?.pointer?.(event); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Arraste no canvas para posicionar a plataforma/cesta em dispositivos táteis.
    activeCanvas.addEventListener('pointermove', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        if (!engine?.ended && event.buttons === 1) engine?.pointer?.(event); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    // Alternativa ao teclado físico para aparelhos com tela sensível ao toque.
    document.querySelectorAll('[data-arcade-key]').forEach((button) => { // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        button.addEventListener('pointerdown', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
            event.preventDefault(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAdminProgress.
            if (!engine || engine.ended) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            engine.key?.(button.dataset.arcadeKey); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAdminProgress.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    document.querySelectorAll('[data-launch-game]').forEach((button) => { // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        button.addEventListener('click', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
            document.querySelectorAll('[data-launch-game]').forEach((item) => { // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                item.classList.toggle('is-active', item === button); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAdminProgress.
                item.setAttribute('aria-pressed', item === button ? 'true' : 'false'); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAdminProgress.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    function frame(timestamp) { // Explicação DevIN: Declara função/método frame, com seus parâmetros e comportamento.
        const delta = lastFrame ? Math.min(timestamp - lastFrame, 80) : 16; // Explicação DevIN: Declara variável JavaScript delta para reutilizar valor ou resultado.
        lastFrame = timestamp; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        if (engine) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            if (!engine.ended) engine.update(delta); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            engine.draw(); // Explicação DevIN: Continua expressão ou bloco pertencente a função frame.
            setStats(); // Explicação DevIN: Continua expressão ou bloco pertencente a função frame.
            saveTimer += delta; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            if (portal && saveTimer >= 20000 && !engine.ended) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                saveTimer = 0; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                saveProgress(); // Explicação DevIN: Continua expressão ou bloco pertencente a função frame.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
            drawBackground(); // Explicação DevIN: Continua expressão ou bloco pertencente a função frame.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        window.requestAnimationFrame(frame); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    if (portal) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        loadProgress(); // Explicação DevIN: Continua expressão ou bloco pertencente a função frame.
        loadAdminProgress(); // Explicação DevIN: Continua expressão ou bloco pertencente a função frame.
    } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
        engine = mazeEngine(); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        showOverlay('', false); // Explicação DevIN: Continua expressão ou bloco pertencente a função frame.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    window.requestAnimationFrame(frame); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
})(); // Explicação DevIN: Continua expressão ou bloco pertencente a função frame.
