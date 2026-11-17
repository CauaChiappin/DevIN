/*
 * REVISÃO DETALHADA DEVIN — js/pacman.js
 * Finalidade: pacman.
 * Domínio: interações do navegador.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */

(function () { // Explicação DevIN: Define função anônima usada como operação ou callback.
    // Não inicializa o jogo em páginas que não possuem o tabuleiro correspondente.
    const board = document.querySelector("#pacman-board"); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    if (!board) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

    const scoreEl = document.querySelector("[data-score]"); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const levelEl = document.querySelector("[data-level]"); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const livesEl = document.querySelector("[data-lives]"); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const messageEl = document.querySelector("[data-message]"); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const restartButton = document.querySelector("[data-restart]"); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.

    // Cada caractere do mapa representa parede, espaço, ponto, bônus ou posição inicial.
    const maps = [ // Explicação DevIN: Declara variável JavaScript maps para reutilizar valor ou resultado.
        [ // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
            "#####################", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "#.........#.........#", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "#.###.###.#.###.###.#", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "#o###.###.#.###.###o#", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "#...................#", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "#.###.#.#####.#.###.#", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "#.....#...#...#.....#", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "#####.### # ###.#####", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "    #.#       #.#    ", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "#####.# ## ## #.#####", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "     .  #   #  .     ", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "#####.# ##### #.#####", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "    #.#       #.#    ", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "#####.# ##### #.#####", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "#.........#.........#", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "#.###.###.#.###.###.#", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "#o..#.....P.....#..o#", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "###.#.#.#####.#.#.###", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "#.....#...#...#.....#", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "#.#######.#.#######.#", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "#...................#", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "#####################" // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ], // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        [ // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
            "#####################", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "#o......#...#......o#", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "#.####.#.#.#.#.####.#", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "#......#.#.#.#......#", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "###.##.#.....#.##.###", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "#...##.#######.##...#", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "#.#...............#.#", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "#.#.##### # #####.#.#", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "#.#.#           #.#.#", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "#...# ###   ### #...#", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "###.# #       # #.###", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "#...# ######### #...#", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "#.#.#           #.#.#", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "#.#.##### # #####.#.#", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "#.#...............#.#", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "#...##.#######.##...#", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "###.##.#..P..#.##.###", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "#......#.#.#.#......#", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "#.####.#.#.#.#.####.#", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "#o......#...#......o#", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            "#####################" // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ] // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.

    const directions = { // Explicação DevIN: Declara variável JavaScript directions para reutilizar valor ou resultado.
        ArrowUp: { x: 0, y: -1, angle: -90 }, // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        ArrowDown: { x: 0, y: 1, angle: 90 }, // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        ArrowLeft: { x: -1, y: 0, angle: 180 }, // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        ArrowRight: { x: 1, y: 0, angle: 0 } // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    let level = 0; // Explicação DevIN: Declara variável JavaScript level para reutilizar valor ou resultado.
    let score = 0; // Explicação DevIN: Declara variável JavaScript score para reutilizar valor ou resultado.
    let lives = 3; // Explicação DevIN: Declara variável JavaScript lives para reutilizar valor ou resultado.
    let grid = []; // Explicação DevIN: Declara variável JavaScript grid para reutilizar valor ou resultado.
    let pellets = 0; // Explicação DevIN: Declara variável JavaScript pellets para reutilizar valor ou resultado.
    let pacman = { x: 10, y: 16, dir: directions.ArrowLeft, next: directions.ArrowLeft }; // Explicação DevIN: Declara variável JavaScript pacman para reutilizar valor ou resultado.
    let ghosts = []; // Explicação DevIN: Declara variável JavaScript ghosts para reutilizar valor ou resultado.
    let tickId = null; // Explicação DevIN: Declara variável JavaScript tickId para reutilizar valor ou resultado.
    let scaredUntil = 0; // Explicação DevIN: Declara variável JavaScript scaredUntil para reutilizar valor ou resultado.
    let gameOver = false; // Explicação DevIN: Declara variável JavaScript gameOver para reutilizar valor ou resultado.

    const cellSize = () => board.querySelector(".cell")?.offsetWidth || 24; // Explicação DevIN: Declara variável JavaScript cellSize para reutilizar valor ou resultado.
    const offset = () => parseInt(getComputedStyle(board).paddingLeft, 10) || 0; // Explicação DevIN: Declara variável JavaScript offset para reutilizar valor ou resultado.

    // Converte o mapa textual em células HTML e reposiciona jogador e fantasmas.
    // Detalhe DevIN: Método loadLevel: consulta e organiza dados sem presumir que o resultado seja único no contexto de pacman. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    function loadLevel() { // Explicação DevIN: Declara função/método loadLevel, com seus parâmetros e comportamento.
        grid = maps[level].map((row, y) => row.split("").map((tile, x) => { // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            if (tile === "P") { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                pacman = { x, y, dir: directions.ArrowLeft, next: directions.ArrowLeft }; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                return "."; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            return tile; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        })); // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
        pellets = 0; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        board.innerHTML = ""; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        board.style.setProperty("--board-rows", grid.length); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadLevel.
        grid.forEach((row, y) => { // Explicação DevIN: Continua expressão ou bloco pertencente a função loadLevel.
            row.forEach((tile, x) => { // Explicação DevIN: Continua expressão ou bloco pertencente a função loadLevel.
                const cell = document.createElement("div"); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                cell.className = "cell"; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                cell.dataset.x = x; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                cell.dataset.y = y; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                if (tile === "#") cell.classList.add("wall"); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                if (tile === ".") { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    cell.classList.add("pellet"); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadLevel.
                    pellets += 1; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                if (tile === "o") { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    cell.classList.add("power"); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadLevel.
                    pellets += 1; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                board.appendChild(cell); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadLevel.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        board.appendChild(messageEl); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadLevel.

        const midY = Math.floor(grid.length / 2); // Explicação DevIN: Declara variável JavaScript midY para reutilizar valor ou resultado.
        ghosts = [ // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            { name: "blinky", x: 9, y: midY, startX: 9, startY: midY, dir: directions.ArrowLeft }, // Explicação DevIN: Continua expressão ou bloco pertencente a função loadLevel.
            { name: "pinky", x: 10, y: midY, startX: 10, startY: midY, dir: directions.ArrowRight }, // Explicação DevIN: Continua expressão ou bloco pertencente a função loadLevel.
            { name: "inky", x: 11, y: midY, startX: 11, startY: midY, dir: directions.ArrowUp }, // Explicação DevIN: Continua expressão ou bloco pertencente a função loadLevel.
            { name: "clyde", x: 10, y: midY - 1, startX: 10, startY: midY - 1, dir: directions.ArrowDown } // Explicação DevIN: Continua expressão ou bloco pertencente a função loadLevel.
        ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
        createActors(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadLevel.
        updateHud(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadLevel.
        showMessage(""); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadLevel.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Método createActors: cria registros após checagem da entrada no contexto de pacman. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    function createActors() { // Explicação DevIN: Declara função/método createActors, com seus parâmetros e comportamento.
        const pac = document.createElement("div"); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        pac.className = "pacman"; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        pac.dataset.actor = "pacman"; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        board.appendChild(pac); // Explicação DevIN: Continua expressão ou bloco pertencente a função createActors.
        ghosts.forEach((ghost) => { // Explicação DevIN: Continua expressão ou bloco pertencente a função createActors.
            const el = document.createElement("div"); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            el.className = `ghost ${ghost.name}`; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            el.dataset.actor = ghost.name; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            board.appendChild(el); // Explicação DevIN: Continua expressão ou bloco pertencente a função createActors.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        positionActors(); // Explicação DevIN: Continua expressão ou bloco pertencente a função createActors.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Método canMove: verifica condições antes de liberar o próximo passo no contexto de pacman. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    function canMove(x, y) { // Explicação DevIN: Declara função/método canMove, com seus parâmetros e comportamento.
        // Bloqueia linhas fora do mapa, mas permite que o jogador use o túnel e reapareça na outra lateral.
        if (y < 0 || y >= grid.length) return false; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if (x < 0 || x >= grid[y].length) return true; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        return grid[y][x] !== "#"; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Método wrap: encapsula uma responsabilidade reutilizável do módulo no contexto de pacman. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    function wrap(actor) { // Explicação DevIN: Declara função/método wrap, com seus parâmetros e comportamento.
        if (actor.x < 0) actor.x = grid[0].length - 1; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if (actor.x >= grid[0].length) actor.x = 0; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Aplica a direção solicitada quando possível, coleta itens e atualiza a pontuação.
    // Detalhe DevIN: Método movePacman: encapsula uma responsabilidade reutilizável do módulo no contexto de pacman. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    function movePacman() { // Explicação DevIN: Declara função/método movePacman, com seus parâmetros e comportamento.
        if (canMove(pacman.x + pacman.next.x, pacman.y + pacman.next.y)) pacman.dir = pacman.next; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if (canMove(pacman.x + pacman.dir.x, pacman.y + pacman.dir.y)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            pacman.x += pacman.dir.x; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            pacman.y += pacman.dir.y; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            wrap(pacman); // Explicação DevIN: Continua expressão ou bloco pertencente a função movePacman.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        // A célula é limpa após a coleta para que o mesmo ponto não some novamente à pontuação.
        const tile = grid[pacman.y][pacman.x]; // Explicação DevIN: Declara variável JavaScript tile para reutilizar valor ou resultado.
        if (tile === "." || tile === "o") { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            score += tile === "o" ? 50 : 10; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            pellets -= 1; // Explicação DevIN: Continua expressão ou bloco pertencente a função movePacman.
            if (tile === "o") scaredUntil = Date.now() + 6500; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            grid[pacman.y][pacman.x] = " "; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            board.querySelector(`[data-x="${pacman.x}"][data-y="${pacman.y}"]`)?.classList.remove("pellet", "power"); // Explicação DevIN: Continua expressão ou bloco pertencente a função movePacman.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if (pellets <= 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            level = (level + 1) % maps.length; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            score += 500; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            loadLevel(); // Explicação DevIN: Continua expressão ou bloco pertencente a função movePacman.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Método moveGhost: encapsula uma responsabilidade reutilizável do módulo no contexto de pacman. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    function moveGhost(ghost) { // Explicação DevIN: Declara função/método moveGhost, com seus parâmetros e comportamento.
        const scared = Date.now() < scaredUntil; // Explicação DevIN: Declara variável JavaScript scared para reutilizar valor ou resultado.
        const options = Object.values(directions).filter((dir) => canMove(ghost.x + dir.x, ghost.y + dir.y)); // Explicação DevIN: Declara variável JavaScript options para reutilizar valor ou resultado.
        let best = options[0] || ghost.dir; // Explicação DevIN: Declara variável JavaScript best para reutilizar valor ou resultado.
        let bestScore = scared ? -Infinity : Infinity; // Explicação DevIN: Declara variável JavaScript bestScore para reutilizar valor ou resultado.

        options.forEach((dir) => { // Explicação DevIN: Continua expressão ou bloco pertencente a função moveGhost.
            const nx = ghost.x + dir.x; // Explicação DevIN: Declara variável JavaScript nx para reutilizar valor ou resultado.
            const ny = ghost.y + dir.y; // Explicação DevIN: Declara variável JavaScript ny para reutilizar valor ou resultado.
            const dist = Math.abs(nx - pacman.x) + Math.abs(ny - pacman.y); // Explicação DevIN: Declara variável JavaScript dist para reutilizar valor ou resultado.
            const rank = scared ? dist + Math.random() * 2 : dist - Math.random() * 2; // Explicação DevIN: Declara variável JavaScript rank para reutilizar valor ou resultado.
            if ((scared && rank > bestScore) || (!scared && rank < bestScore)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                bestScore = rank; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                best = dir; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        ghost.dir = best; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        ghost.x += best.x; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        ghost.y += best.y; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        wrap(ghost); // Explicação DevIN: Continua expressão ou bloco pertencente a função moveGhost.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Método collisions: encapsula uma responsabilidade reutilizável do módulo no contexto de pacman. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    function collisions() { // Explicação DevIN: Declara função/método collisions, com seus parâmetros e comportamento.
        ghosts.forEach((ghost) => { // Explicação DevIN: Continua expressão ou bloco pertencente a função collisions.
            if (ghost.x !== pacman.x || ghost.y !== pacman.y) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            if (Date.now() < scaredUntil) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                score += 200; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                ghost.x = ghost.startX; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                ghost.y = ghost.startY; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            lives -= 1; // Explicação DevIN: Continua expressão ou bloco pertencente a função collisions.
            if (lives <= 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                gameOver = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                showMessage("Fim de jogo"); // Explicação DevIN: Continua expressão ou bloco pertencente a função collisions.
                clearInterval(tickId); // Explicação DevIN: Continua expressão ou bloco pertencente a função collisions.
                return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            pacman.x = 10; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            pacman.y = 16; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            showMessage("Cuidado!"); // Explicação DevIN: Continua expressão ou bloco pertencente a função collisions.
            setTimeout(() => showMessage(""), 900); // Explicação DevIN: Continua expressão ou bloco pertencente a função collisions.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Método positionActors: encapsula uma responsabilidade reutilizável do módulo no contexto de pacman. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    function positionActors() { // Explicação DevIN: Declara função/método positionActors, com seus parâmetros e comportamento.
        const size = cellSize(); // Explicação DevIN: Declara variável JavaScript size para reutilizar valor ou resultado.
        const pad = offset(); // Explicação DevIN: Declara variável JavaScript pad para reutilizar valor ou resultado.
        const place = (el, actor, angle) => { // Explicação DevIN: Declara variável JavaScript place para reutilizar valor ou resultado.
            if (!el) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            el.style.transform = `translate(${pad + actor.x * size + 2}px, ${pad + actor.y * size + 2}px) rotate(${angle || 0}deg)`; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        place(board.querySelector('[data-actor="pacman"]'), pacman, pacman.dir.angle); // Explicação DevIN: Continua expressão ou bloco pertencente a função positionActors.
        ghosts.forEach((ghost) => { // Explicação DevIN: Continua expressão ou bloco pertencente a função positionActors.
            const el = board.querySelector(`[data-actor="${ghost.name}"]`); // Explicação DevIN: Declara variável JavaScript el para reutilizar valor ou resultado.
            el?.classList.toggle("scared", Date.now() < scaredUntil); // Explicação DevIN: Continua expressão ou bloco pertencente a função positionActors.
            place(el, ghost, 0); // Explicação DevIN: Continua expressão ou bloco pertencente a função positionActors.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Método updateHud: modifica apenas os dados previstos pela operação no contexto de pacman. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    function updateHud() { // Explicação DevIN: Declara função/método updateHud, com seus parâmetros e comportamento.
        scoreEl.textContent = score; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        levelEl.textContent = level + 1; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        livesEl.textContent = lives; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Método showMessage: encapsula uma responsabilidade reutilizável do módulo no contexto de pacman. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    function showMessage(text) { // Explicação DevIN: Declara função/método showMessage, com seus parâmetros e comportamento.
        messageEl.textContent = text; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        messageEl.style.display = text ? "block" : "none"; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Método tick: encapsula uma responsabilidade reutilizável do módulo no contexto de pacman. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    function tick() { // Explicação DevIN: Declara função/método tick, com seus parâmetros e comportamento.
        if (gameOver) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        movePacman(); // Explicação DevIN: Continua expressão ou bloco pertencente a função tick.
        ghosts.forEach(moveGhost); // Explicação DevIN: Continua expressão ou bloco pertencente a função tick.
        collisions(); // Explicação DevIN: Continua expressão ou bloco pertencente a função tick.
        positionActors(); // Explicação DevIN: Continua expressão ou bloco pertencente a função tick.
        updateHud(); // Explicação DevIN: Continua expressão ou bloco pertencente a função tick.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Método restart: encapsula uma responsabilidade reutilizável do módulo no contexto de pacman. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    function restart() { // Explicação DevIN: Declara função/método restart, com seus parâmetros e comportamento.
        clearInterval(tickId); // Explicação DevIN: Continua expressão ou bloco pertencente a função restart.
        level = 0; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        score = 0; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        lives = 3; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        gameOver = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        scaredUntil = 0; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        loadLevel(); // Explicação DevIN: Continua expressão ou bloco pertencente a função restart.
        tickId = setInterval(tick, 150); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    document.addEventListener("keydown", (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        if (!directions[event.key]) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        event.preventDefault(); // Explicação DevIN: Continua expressão ou bloco pertencente a função restart.
        pacman.next = directions[event.key]; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    restartButton?.addEventListener("click", restart); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    window.addEventListener("resize", positionActors); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    restart(); // Explicação DevIN: Continua expressão ou bloco pertencente a função restart.
})(); // Explicação DevIN: Continua expressão ou bloco pertencente a função restart.
