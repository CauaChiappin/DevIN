(function () {
    const board = document.querySelector("#pacman-board");
    if (!board) return;

    const scoreEl = document.querySelector("[data-score]");
    const levelEl = document.querySelector("[data-level]");
    const livesEl = document.querySelector("[data-lives]");
    const messageEl = document.querySelector("[data-message]");
    const restartButton = document.querySelector("[data-restart]");

    const maps = [
        [
            "#####################",
            "#.........#.........#",
            "#.###.###.#.###.###.#",
            "#o###.###.#.###.###o#",
            "#...................#",
            "#.###.#.#####.#.###.#",
            "#.....#...#...#.....#",
            "#####.### # ###.#####",
            "    #.#       #.#    ",
            "#####.# ## ## #.#####",
            "     .  #   #  .     ",
            "#####.# ##### #.#####",
            "    #.#       #.#    ",
            "#####.# ##### #.#####",
            "#.........#.........#",
            "#.###.###.#.###.###.#",
            "#o..#.....P.....#..o#",
            "###.#.#.#####.#.#.###",
            "#.....#...#...#.....#",
            "#.#######.#.#######.#",
            "#...................#",
            "#####################"
        ],
        [
            "#####################",
            "#o......#...#......o#",
            "#.####.#.#.#.#.####.#",
            "#......#.#.#.#......#",
            "###.##.#.....#.##.###",
            "#...##.#######.##...#",
            "#.#...............#.#",
            "#.#.##### # #####.#.#",
            "#.#.#           #.#.#",
            "#...# ###   ### #...#",
            "###.# #       # #.###",
            "#...# ######### #...#",
            "#.#.#           #.#.#",
            "#.#.##### # #####.#.#",
            "#.#...............#.#",
            "#...##.#######.##...#",
            "###.##.#..P..#.##.###",
            "#......#.#.#.#......#",
            "#.####.#.#.#.#.####.#",
            "#o......#...#......o#",
            "#####################"
        ]
    ];

    const directions = {
        ArrowUp: { x: 0, y: -1, angle: -90 },
        ArrowDown: { x: 0, y: 1, angle: 90 },
        ArrowLeft: { x: -1, y: 0, angle: 180 },
        ArrowRight: { x: 1, y: 0, angle: 0 }
    };

    let level = 0;
    let score = 0;
    let lives = 3;
    let grid = [];
    let pellets = 0;
    let pacman = { x: 10, y: 16, dir: directions.ArrowLeft, next: directions.ArrowLeft };
    let ghosts = [];
    let tickId = null;
    let scaredUntil = 0;
    let gameOver = false;

    const cellSize = () => board.querySelector(".cell")?.offsetWidth || 24;
    const offset = () => parseInt(getComputedStyle(board).paddingLeft, 10) || 0;

    function loadLevel() {
        grid = maps[level].map((row, y) => row.split("").map((tile, x) => {
            if (tile === "P") {
                pacman = { x, y, dir: directions.ArrowLeft, next: directions.ArrowLeft };
                return ".";
            }
            return tile;
        }));
        pellets = 0;
        board.innerHTML = "";
        board.style.setProperty("--board-rows", grid.length);
        grid.forEach((row, y) => {
            row.forEach((tile, x) => {
                const cell = document.createElement("div");
                cell.className = "cell";
                cell.dataset.x = x;
                cell.dataset.y = y;
                if (tile === "#") cell.classList.add("wall");
                if (tile === ".") {
                    cell.classList.add("pellet");
                    pellets += 1;
                }
                if (tile === "o") {
                    cell.classList.add("power");
                    pellets += 1;
                }
                board.appendChild(cell);
            });
        });
        board.appendChild(messageEl);

        const midY = Math.floor(grid.length / 2);
        ghosts = [
            { name: "blinky", x: 9, y: midY, startX: 9, startY: midY, dir: directions.ArrowLeft },
            { name: "pinky", x: 10, y: midY, startX: 10, startY: midY, dir: directions.ArrowRight },
            { name: "inky", x: 11, y: midY, startX: 11, startY: midY, dir: directions.ArrowUp },
            { name: "clyde", x: 10, y: midY - 1, startX: 10, startY: midY - 1, dir: directions.ArrowDown }
        ];
        createActors();
        updateHud();
        showMessage("");
    }

    function createActors() {
        const pac = document.createElement("div");
        pac.className = "pacman";
        pac.dataset.actor = "pacman";
        board.appendChild(pac);
        ghosts.forEach((ghost) => {
            const el = document.createElement("div");
            el.className = `ghost ${ghost.name}`;
            el.dataset.actor = ghost.name;
            board.appendChild(el);
        });
        positionActors();
    }

    function canMove(x, y) {
        if (y < 0 || y >= grid.length) return false;
        if (x < 0 || x >= grid[y].length) return true;
        return grid[y][x] !== "#";
    }

    function wrap(actor) {
        if (actor.x < 0) actor.x = grid[0].length - 1;
        if (actor.x >= grid[0].length) actor.x = 0;
    }

    function movePacman() {
        if (canMove(pacman.x + pacman.next.x, pacman.y + pacman.next.y)) pacman.dir = pacman.next;
        if (canMove(pacman.x + pacman.dir.x, pacman.y + pacman.dir.y)) {
            pacman.x += pacman.dir.x;
            pacman.y += pacman.dir.y;
            wrap(pacman);
        }

        const tile = grid[pacman.y][pacman.x];
        if (tile === "." || tile === "o") {
            score += tile === "o" ? 50 : 10;
            pellets -= 1;
            if (tile === "o") scaredUntil = Date.now() + 6500;
            grid[pacman.y][pacman.x] = " ";
            board.querySelector(`[data-x="${pacman.x}"][data-y="${pacman.y}"]`)?.classList.remove("pellet", "power");
        }

        if (pellets <= 0) {
            level = (level + 1) % maps.length;
            score += 500;
            loadLevel();
        }
    }

    function moveGhost(ghost) {
        const scared = Date.now() < scaredUntil;
        const options = Object.values(directions).filter((dir) => canMove(ghost.x + dir.x, ghost.y + dir.y));
        let best = options[0] || ghost.dir;
        let bestScore = scared ? -Infinity : Infinity;

        options.forEach((dir) => {
            const nx = ghost.x + dir.x;
            const ny = ghost.y + dir.y;
            const dist = Math.abs(nx - pacman.x) + Math.abs(ny - pacman.y);
            const rank = scared ? dist + Math.random() * 2 : dist - Math.random() * 2;
            if ((scared && rank > bestScore) || (!scared && rank < bestScore)) {
                bestScore = rank;
                best = dir;
            }
        });

        ghost.dir = best;
        ghost.x += best.x;
        ghost.y += best.y;
        wrap(ghost);
    }

    function collisions() {
        ghosts.forEach((ghost) => {
            if (ghost.x !== pacman.x || ghost.y !== pacman.y) return;
            if (Date.now() < scaredUntil) {
                score += 200;
                ghost.x = ghost.startX;
                ghost.y = ghost.startY;
                return;
            }
            lives -= 1;
            if (lives <= 0) {
                gameOver = true;
                showMessage("Fim de jogo");
                clearInterval(tickId);
                return;
            }
            pacman.x = 10;
            pacman.y = 16;
            showMessage("Cuidado!");
            setTimeout(() => showMessage(""), 900);
        });
    }

    function positionActors() {
        const size = cellSize();
        const pad = offset();
        const place = (el, actor, angle) => {
            if (!el) return;
            el.style.transform = `translate(${pad + actor.x * size + 2}px, ${pad + actor.y * size + 2}px) rotate(${angle || 0}deg)`;
        };
        place(board.querySelector('[data-actor="pacman"]'), pacman, pacman.dir.angle);
        ghosts.forEach((ghost) => {
            const el = board.querySelector(`[data-actor="${ghost.name}"]`);
            el?.classList.toggle("scared", Date.now() < scaredUntil);
            place(el, ghost, 0);
        });
    }

    function updateHud() {
        scoreEl.textContent = score;
        levelEl.textContent = level + 1;
        livesEl.textContent = lives;
    }

    function showMessage(text) {
        messageEl.textContent = text;
        messageEl.style.display = text ? "block" : "none";
    }

    function tick() {
        if (gameOver) return;
        movePacman();
        ghosts.forEach(moveGhost);
        collisions();
        positionActors();
        updateHud();
    }

    function restart() {
        clearInterval(tickId);
        level = 0;
        score = 0;
        lives = 3;
        gameOver = false;
        scaredUntil = 0;
        loadLevel();
        tickId = setInterval(tick, 150);
    }

    document.addEventListener("keydown", (event) => {
        if (!directions[event.key]) return;
        event.preventDefault();
        pacman.next = directions[event.key];
    });
    restartButton?.addEventListener("click", restart);
    window.addEventListener("resize", positionActors);
    restart();
})();
