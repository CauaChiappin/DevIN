<?php

declare(strict_types=1);

require_once __DIR__ . '/middlewares/auth.php';
require_once __DIR__ . '/config/security.php';

$usuario = requireWebAuth();
$liberacao = $_SESSION['arcade_unlock'] ?? [];
$acessoValido = is_array($liberacao)
    && (int) ($liberacao['id'] ?? 0) === $usuario['id']
    && ($liberacao['tipo'] ?? '') === $usuario['tipo']
    && time() - (int) ($liberacao['liberado_em'] ?? 0) <= 7200;

if (!$acessoValido) {
    unset($_SESSION['arcade_unlock']);
    $dashboards = [
        'adm' => 'adm.php',
        'empresa' => 'empresa.php',
        'pessoa' => 'pessoa.php',
    ];
    header('Location: ' . APP_BASE_URL . '/php/' . ($dashboards[$usuario['tipo']] ?? 'login.php'));
    exit;
}

$csrfToken = csrfToken();
$isAdmin = $usuario['tipo'] === 'adm';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevIN Arcade</title>
    <link rel="stylesheet" href="../css/jogos.css">
    <link rel="stylesheet" href="../css/site-navigation.css">
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg">
</head>
<body class="game-page arcade-portal" data-arcade-api="arcade_api.php" data-admin="<?= $isAdmin ? 'true' : 'false' ?>">
    <div class="arcade-shell">
        <header class="arcade-header">
            <a class="arcade-logo" href="<?= htmlspecialchars(APP_BASE_URL . '/php/' . ($isAdmin ? 'adm.php' : ($usuario['tipo'] === 'empresa' ? 'empresa.php' : 'pessoa.php')), ENT_QUOTES, 'UTF-8') ?>">Dev<span>IN</span> Arcade</a>
            <nav class="arcade-nav" aria-label="Navegação dos jogos">
                <a class="arcade-button" href="<?= htmlspecialchars(APP_BASE_URL . '/html/jogos/doom.html', ENT_QUOTES, 'UTF-8') ?>">Jogos públicos</a>
                <a class="arcade-button" href="<?= htmlspecialchars(APP_BASE_URL . '/php/logout.php', ENT_QUOTES, 'UTF-8') ?>">Sair</a>
            </nav>
        </header>

        <main class="portal-content">
            <section class="portal-intro">
                <p class="portal-eyebrow">ÁREA SECRETA LIBERADA</p>
                <h1>Jogos DevIN</h1>
                <p>Escolha um jogo. Seu recorde, maior fase alcançada e última atividade ficam salvos na sua conta.</p>
            </section>

            <section class="portal-layout">
                <div class="portal-games" aria-label="Lista de jogos">
                    <button class="portal-game-card" type="button" data-launch-game="maze">
                        <span class="portal-game-icon" aria-hidden="true">◉</span>
                        <strong>Labirinto DevIN</strong><span>Explore fases, colete cristais e evite os perseguidores.</span>
                    </button>
                    <button class="portal-game-card" type="button" data-launch-game="snake">
                        <span class="portal-game-icon" aria-hidden="true">⌁</span>
                        <strong>Snake</strong><span>Cresça, avance de fase e não bata nas bordas ou em si mesmo.</span>
                    </button>
                    <button class="portal-game-card" type="button" data-launch-game="tetris">
                        <span class="portal-game-icon" aria-hidden="true">▦</span>
                        <strong>Blocos</strong><span>Complete linhas para pontuar e aumentar o nível.</span>
                    </button>
                    <button class="portal-game-card" type="button" data-launch-game="breakout">
                        <span class="portal-game-icon" aria-hidden="true">▰</span>
                        <strong>Quebra-blocos</strong><span>Rebata a bola, limpe os blocos e alcance novas fases.</span>
                    </button>
                    <button class="portal-game-card" type="button" data-launch-game="memory">
                        <span class="portal-game-icon" aria-hidden="true">◇</span>
                        <strong>Memória</strong><span>Encontre os pares com o menor número de tentativas.</span>
                    </button>
                    <button class="portal-game-card" type="button" data-launch-game="runner">
                        <span class="portal-game-icon" aria-hidden="true">◆</span>
                        <strong>Salto de Ritmo</strong><span>Corra, pule obstáculos e avance por trechos cada vez mais rápidos.</span>
                    </button>
                </div>

                <section class="arcade-play-panel" aria-label="Área de jogo">
                    <div class="arcade-play-heading">
                        <div><h2 data-game-title>Selecione um jogo</h2><p data-game-hint>Os controles e a pontuação aparecerão aqui.</p></div>
                        <button class="arcade-button" type="button" data-restart-game hidden>Reiniciar</button>
                    </div>
                    <div class="arcade-canvas-wrap">
                        <canvas id="arcade-canvas" width="640" height="480" aria-label="Tela do jogo"></canvas>
                        <div class="arcade-overlay" data-game-overlay>
                            <span data-game-overlay-message>Escolha um jogo para começar.</span>
                            <section class="game-start-card" data-game-intro hidden aria-live="polite">
                                <p class="portal-eyebrow">ANTES DE COMEÇAR</p>
                                <h2 data-game-intro-title></h2>
                                <p data-game-description></p>
                                <p class="game-start-controls" data-game-controls></p>
                                <button class="arcade-button game-start-button" type="button" data-start-game>Iniciar jogo</button>
                            </section>
                            <section class="game-over-card" data-game-over hidden aria-live="polite">
                                <p class="portal-eyebrow">FIM DE JOGO</p>
                                <h2>Partida encerrada</h2>
                                <p data-game-over-score></p>
                                <h3>Classificação — 10 melhores</h3>
                                <p data-leaderboard-message>Carregando classificação…</p>
                                <ol class="game-leaderboard" data-game-leaderboard></ol>
                                <button class="arcade-button" type="button" data-game-over-restart>Jogar novamente</button>
                            </section>
                        </div>
                    </div>
                    <div class="arcade-live-stats" aria-live="polite">
                        <span>Pontos: <strong data-game-score>0</strong></span>
                        <span>Fase: <strong data-game-level>1</strong></span>
                        <span>Recorde: <strong data-game-best>0</strong></span>
                    </div>
                    <div class="memory-controls" data-memory-controls hidden></div>
                    <p class="arcade-feedback" data-arcade-feedback role="status"></p>
                </section>
            </section>

            <?php if ($isAdmin): ?>
                <section class="admin-progress">
                    <h2>Painel de progresso</h2>
                    <p>Dados de jogo vinculados às contas. A remoção apaga somente o registro selecionado.</p>
                    <div class="admin-progress-table" data-admin-progress>Carregando registros…</div>
                </section>
            <?php endif; ?>
        </main>

        <footer class="arcade-footer"><span>DevIN | Área de jogos</span><span>Conectado como <?= htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8') ?></span></footer>
    </div>
    <script>window.DevINArcadeCsrf = <?= json_encode($csrfToken, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;</script>
    <script src="../js/arcade-games.js?v=<?= filemtime(__DIR__ . '/../js/arcade-games.js') ?>"></script>
</body>
</html>
