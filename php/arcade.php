<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/arcade.php
 * Finalidade: arcade.
 * Domínio: processamento no servidor.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


require_once __DIR__ . '/middlewares/auth.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/config/security.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/controllers/ProfileLanguagePreference.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

$usuario = requireWebAuth(); // Explicação DevIN: Atribui um valor à variável PHP $usuario.
$conn = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $conn.
try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    $idiomaAtual = ProfileLanguagePreference::load($conn, $usuario['tipo'], (int) $usuario['id']); // Explicação DevIN: Atribui um valor à variável PHP $idiomaAtual.
} finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
    $conn->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$_SESSION['idioma'] = $idiomaAtual; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.

$liberacao = $_SESSION['arcade_unlock'] ?? []; // Explicação DevIN: Atribui um valor à variável PHP $liberacao.
$acessoValido = is_array($liberacao) // Explicação DevIN: Atribui um valor à variável PHP $acessoValido.
    && (int) ($liberacao['id'] ?? 0) === $usuario['id'] // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    && ($liberacao['tipo'] ?? '') === $usuario['tipo'] // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    && time() - (int) ($liberacao['liberado_em'] ?? 0) <= 7200; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.

if (!$acessoValido) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    unset($_SESSION['arcade_unlock']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    $dashboards = [ // Explicação DevIN: Atribui um valor à variável PHP $dashboards.
        'adm' => 'adm.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'empresa' => 'empresa.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'pessoa' => 'pessoa.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
    header('Location: ' . APP_BASE_URL . '/php/' . ($dashboards[$usuario['tipo']] ?? 'login.php')); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

$csrfToken = csrfToken(); // Explicação DevIN: Atribui um valor à variável PHP $csrfToken.
$isAdmin = $usuario['tipo'] === 'adm'; // Explicação DevIN: Atribui um valor à variável PHP $isAdmin.
?>
<!DOCTYPE html> <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
<html lang="<?= htmlspecialchars($idiomaAtual, ENT_QUOTES, 'UTF-8') ?>"> <!-- Explicação DevIN: Declara elemento raiz do documento HTML. Contém dado dinâmico processado pelo PHP. -->
<head> <!-- Explicação DevIN: Declara metadados e dependências do documento. -->
    <meta charset="UTF-8"> <!-- Explicação DevIN: Declara metadado de navegação. -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Explicação DevIN: Declara metadado de navegação. -->
    <title>DevIN Arcade</title> <!-- Explicação DevIN: Declara elemento de marcação. -->
    <link rel="stylesheet" href="../css/jogos.css"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. -->
    <link rel="stylesheet" href="../css/site-navigation.css"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. -->
    <link rel="icon" type="image/svg+xml" href="../img/favicon.svg"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. -->
</head> <!-- Explicação DevIN: Fecha elemento head do documento. -->
<body class="game-page arcade-portal" data-arcade-api="arcade_api.php" data-admin="<?= $isAdmin ? 'true' : 'false' ?>"> <!-- Explicação DevIN: Declara corpo visível da página. Contém dado dinâmico processado pelo PHP. -->
    <div class="arcade-shell"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
        <header class="arcade-header"> <!-- Explicação DevIN: Declara cabeçalho da seção ou página. -->
            <a class="arcade-logo" href="<?= htmlspecialchars(APP_BASE_URL . '/php/' . ($isAdmin ? 'adm.php' : ($usuario['tipo'] === 'empresa' ? 'empresa.php' : 'pessoa.php')), ENT_QUOTES, 'UTF-8') ?>">Dev<span>IN</span> Arcade</a> <!-- Explicação DevIN: Declara link de navegação. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
            <nav class="arcade-nav" aria-label="Navegação dos jogos"> <!-- Explicação DevIN: Declara navegação. Inclui atributos de acessibilidade. -->
                <a class="arcade-button" href="<?= htmlspecialchars(APP_BASE_URL . '/html/jogos/doom.html', ENT_QUOTES, 'UTF-8') ?>">Jogos públicos</a> <!-- Explicação DevIN: Declara link de navegação. Contém dado dinâmico processado pelo PHP. -->
                <a class="arcade-button" href="<?= htmlspecialchars(APP_BASE_URL . '/php/logout.php', ENT_QUOTES, 'UTF-8') ?>">Sair</a> <!-- Explicação DevIN: Declara link de navegação. Contém dado dinâmico processado pelo PHP. -->
            </nav> <!-- Explicação DevIN: Fecha elemento nav do documento. -->
        </header> <!-- Explicação DevIN: Fecha elemento header do documento. -->

        <main class="portal-content"> <!-- Explicação DevIN: Declara conteúdo principal da página. -->
            <section class="portal-intro"> <!-- Explicação DevIN: Declara seção temática. -->
                <p class="portal-eyebrow">ÁREA SECRETA LIBERADA</p> <!-- Explicação DevIN: Declara parágrafo. -->
                <h1>Jogos DevIN</h1> <!-- Explicação DevIN: Declara título principal. -->
                <p>Escolha um jogo. Seu recorde, maior fase alcançada e última atividade ficam salvos na sua conta.</p> <!-- Explicação DevIN: Declara parágrafo. -->
            </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->

            <section class="portal-layout"> <!-- Explicação DevIN: Declara seção temática. -->
                <div class="portal-games" aria-label="Lista de jogos"> <!-- Explicação DevIN: Declara agrupamento de elementos. Inclui atributos de acessibilidade. -->
                    <button class="portal-game-card" type="button" data-launch-game="maze"> <!-- Explicação DevIN: Declara botão de ação. -->
                        <span class="portal-game-icon" aria-hidden="true">◉</span> <!-- Explicação DevIN: Declara trecho em linha. Inclui atributos de acessibilidade. -->
                        <strong>Labirinto DevIN</strong><span>Explore fases, colete cristais e evite os perseguidores.</span> <!-- Explicação DevIN: Declara elemento de marcação. A linha reúne múltiplos elementos. -->
                    </button> <!-- Explicação DevIN: Fecha elemento button do documento. -->
                    <button class="portal-game-card" type="button" data-launch-game="snake"> <!-- Explicação DevIN: Declara botão de ação. -->
                        <span class="portal-game-icon" aria-hidden="true">⌁</span> <!-- Explicação DevIN: Declara trecho em linha. Inclui atributos de acessibilidade. -->
                        <strong>Snake</strong><span>Cresça, avance de fase e não bata nas bordas ou em si mesmo.</span> <!-- Explicação DevIN: Declara elemento de marcação. A linha reúne múltiplos elementos. -->
                    </button> <!-- Explicação DevIN: Fecha elemento button do documento. -->
                    <button class="portal-game-card" type="button" data-launch-game="tetris"> <!-- Explicação DevIN: Declara botão de ação. -->
                        <span class="portal-game-icon" aria-hidden="true">▦</span> <!-- Explicação DevIN: Declara trecho em linha. Inclui atributos de acessibilidade. -->
                        <strong>Blocos</strong><span>Complete linhas para pontuar e aumentar o nível.</span> <!-- Explicação DevIN: Declara elemento de marcação. A linha reúne múltiplos elementos. -->
                    </button> <!-- Explicação DevIN: Fecha elemento button do documento. -->
                    <button class="portal-game-card" type="button" data-launch-game="breakout"> <!-- Explicação DevIN: Declara botão de ação. -->
                        <span class="portal-game-icon" aria-hidden="true">▰</span> <!-- Explicação DevIN: Declara trecho em linha. Inclui atributos de acessibilidade. -->
                        <strong>Quebra-blocos</strong><span>Rebata a bola, limpe os blocos e alcance novas fases.</span> <!-- Explicação DevIN: Declara elemento de marcação. A linha reúne múltiplos elementos. -->
                    </button> <!-- Explicação DevIN: Fecha elemento button do documento. -->
                    <button class="portal-game-card" type="button" data-launch-game="memory"> <!-- Explicação DevIN: Declara botão de ação. -->
                        <span class="portal-game-icon" aria-hidden="true">◇</span> <!-- Explicação DevIN: Declara trecho em linha. Inclui atributos de acessibilidade. -->
                        <strong>Memória</strong><span>Encontre os pares com o menor número de tentativas.</span> <!-- Explicação DevIN: Declara elemento de marcação. A linha reúne múltiplos elementos. -->
                    </button> <!-- Explicação DevIN: Fecha elemento button do documento. -->
                    <button class="portal-game-card" type="button" data-launch-game="runner"> <!-- Explicação DevIN: Declara botão de ação. -->
                        <span class="portal-game-icon" aria-hidden="true">◆</span> <!-- Explicação DevIN: Declara trecho em linha. Inclui atributos de acessibilidade. -->
                        <strong>Salto de Ritmo</strong><span>Corra, pule obstáculos e avance por trechos cada vez mais rápidos.</span> <!-- Explicação DevIN: Declara elemento de marcação. A linha reúne múltiplos elementos. -->
                    </button> <!-- Explicação DevIN: Fecha elemento button do documento. -->
                </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->

                <section class="arcade-play-panel" aria-label="Área de jogo"> <!-- Explicação DevIN: Declara seção temática. Inclui atributos de acessibilidade. -->
                    <div class="arcade-play-heading"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                        <div><h2 data-game-title>Selecione um jogo</h2><p data-game-hint>Os controles e a pontuação aparecerão aqui.</p></div> <!-- Explicação DevIN: Declara agrupamento de elementos. A linha reúne múltiplos elementos. -->
                        <button class="arcade-button" type="button" data-restart-game hidden>Reiniciar</button> <!-- Explicação DevIN: Declara botão de ação. -->
                    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                    <div class="arcade-canvas-wrap"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                        <canvas id="arcade-canvas" width="640" height="480" aria-label="Tela do jogo"></canvas> <!-- Explicação DevIN: Declara elemento de marcação. Inclui atributos de acessibilidade. -->
                        <div class="arcade-overlay" data-game-overlay> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                            <span data-game-overlay-message>Escolha um jogo para começar.</span> <!-- Explicação DevIN: Declara trecho em linha. -->
                            <section class="game-start-card" data-game-intro hidden aria-live="polite"> <!-- Explicação DevIN: Declara seção temática. Inclui atributos de acessibilidade. -->
                                <p class="portal-eyebrow">ANTES DE COMEÇAR</p> <!-- Explicação DevIN: Declara parágrafo. -->
                                <h2 data-game-intro-title></h2> <!-- Explicação DevIN: Declara subtítulo de segundo nível. -->
                                <p data-game-description></p> <!-- Explicação DevIN: Declara parágrafo. -->
                                <p class="game-start-controls" data-game-controls></p> <!-- Explicação DevIN: Declara parágrafo. -->
                                <button class="arcade-button game-start-button" type="button" data-start-game>Iniciar jogo</button> <!-- Explicação DevIN: Declara botão de ação. -->
                            </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->
                            <section class="game-over-card" data-game-over hidden aria-live="polite"> <!-- Explicação DevIN: Declara seção temática. Inclui atributos de acessibilidade. -->
                                <p class="portal-eyebrow">FIM DE JOGO</p> <!-- Explicação DevIN: Declara parágrafo. -->
                                <h2>Partida encerrada</h2> <!-- Explicação DevIN: Declara subtítulo de segundo nível. -->
                                <p data-game-over-score></p> <!-- Explicação DevIN: Declara parágrafo. -->
                                <h3>Classificação — 10 melhores</h3> <!-- Explicação DevIN: Declara subtítulo de terceiro nível. -->
                                <p data-leaderboard-message>Carregando classificação…</p> <!-- Explicação DevIN: Declara parágrafo. -->
                                <ol class="game-leaderboard" data-game-leaderboard></ol> <!-- Explicação DevIN: Declara elemento de marcação. -->
                                <button class="arcade-button" type="button" data-game-over-restart>Jogar novamente</button> <!-- Explicação DevIN: Declara botão de ação. -->
                            </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->
                        </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                    <div class="arcade-live-stats" aria-live="polite"> <!-- Explicação DevIN: Declara agrupamento de elementos. Inclui atributos de acessibilidade. -->
                        <span>Pontos: <strong data-game-score>0</strong></span> <!-- Explicação DevIN: Declara trecho em linha. A linha reúne múltiplos elementos. -->
                        <span>Fase: <strong data-game-level>1</strong></span> <!-- Explicação DevIN: Declara trecho em linha. A linha reúne múltiplos elementos. -->
                        <span>Recorde: <strong data-game-best>0</strong></span> <!-- Explicação DevIN: Declara trecho em linha. A linha reúne múltiplos elementos. -->
                    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                    <div class="memory-controls" data-memory-controls hidden></div> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                    <p class="arcade-feedback" data-arcade-feedback role="status"></p> <!-- Explicação DevIN: Declara parágrafo. -->
                </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->
            </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->

            <?php if ($isAdmin): ?>
                <section class="admin-progress"> <!-- Explicação DevIN: Declara seção temática. -->
                    <h2>Painel de progresso</h2> <!-- Explicação DevIN: Declara subtítulo de segundo nível. -->
                    <p>Dados de jogo vinculados às contas. A remoção apaga somente o registro selecionado.</p> <!-- Explicação DevIN: Declara parágrafo. -->
                    <div class="admin-progress-table" data-admin-progress>Carregando registros…</div> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->
            <?php endif; ?>
        </main> <!-- Explicação DevIN: Fecha elemento main do documento. -->

        <footer class="arcade-footer"><span>DevIN | Área de jogos</span><span>Conectado como <?= htmlspecialchars($usuario['nome'], ENT_QUOTES, 'UTF-8') ?></span></footer> <!-- Explicação DevIN: Declara rodapé. Contém dado dinâmico processado pelo PHP. A linha reúne múltiplos elementos. -->
    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
    <script>window.DevINArcadeCsrf = <?= json_encode($csrfToken, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;</script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
    <script src="../js/dashboard-i18n.js?v=<?= filemtime(__DIR__ . '/../js/dashboard-i18n.js') ?>"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
    <script src="../js/arcade-games.js?v=<?= filemtime(__DIR__ . '/../js/arcade-games.js') ?>"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
    <script src="../js/lenis-global.js?v=<?= filemtime(__DIR__ . '/../js/lenis-global.js') ?>"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
</body> <!-- Explicação DevIN: Fecha elemento body do documento. -->
</html> <!-- Explicação DevIN: Fecha elemento html do documento. -->
