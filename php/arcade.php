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
require_once __DIR__ . '/controllers/ProfileLanguagePreference.php'; // Bloqueia acesso direto quando não houve desbloqueio por gesto secreto.
require_once __DIR__ . '/controllers/ArcadeAccess.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

$usuario = requireWebAuth(); // Explicação DevIN: Atribui um valor à variável PHP $usuario.
if (!ArcadeAccess::isUnlocked($usuario)) { // Bloqueia acesso direto quando não houve desbloqueio por gesto secreto.
    header('Cache-Control: no-store, private'); // Bloqueia acesso direto quando não houve desbloqueio por gesto secreto.
    header('Location: ' . APP_BASE_URL . '/php/' . ($usuario['tipo'] === 'adm' ? 'adm.php' : ($usuario['tipo'] === 'empresa' ? 'empresa.php' : 'pessoa.php'))); // Bloqueia acesso direto quando não houve desbloqueio por gesto secreto.
    exit; // Bloqueia acesso direto quando não houve desbloqueio por gesto secreto.
} // Bloqueia acesso direto quando não houve desbloqueio por gesto secreto.
$conn = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $conn.
try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    $idiomaAtual = ProfileLanguagePreference::load($conn, $usuario['tipo'], (int) $usuario['id']); // Explicação DevIN: Atribui um valor à variável PHP $idiomaAtual.
} finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
    $conn->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$_SESSION['idioma'] = $idiomaAtual; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.

// O Arcade exige sessão autenticada e liberação temporária por atalho secreto.
// Não há links públicos para o Arcade nos menus dos dashboards.
$csrfToken = csrfToken(); // Explicação DevIN: Atribui um valor à variável PHP $csrfToken.
$isAdmin = $usuario['tipo'] === 'adm'; // Explicação DevIN: Atribui um valor à variável PHP $isAdmin.
?>
<!DOCTYPE html> <!-- Explicação DevIN: Continua atributos ou conteúdo do elemento de marcação aberto anteriormente. -->
<html lang="<?= htmlspecialchars($idiomaAtual, ENT_QUOTES, 'UTF-8') ?>"> <!-- Explicação DevIN: Declara elemento raiz do documento HTML. Contém dado dinâmico processado pelo PHP. -->
<head> <!-- Explicação DevIN: Declara metadados e dependências do documento. -->
    <meta charset="UTF-8"> <!-- Explicação DevIN: Declara metadado de navegação. -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Explicação DevIN: Declara metadado de navegação. -->
    <title>DevIN Arcade</title> <!-- Explicação DevIN: Declara elemento de marcação. -->
    <link rel="stylesheet" href="../css/jogos.css?v=<?= filemtime(__DIR__ . '/../css/jogos.css') ?>"> <!-- Explicação DevIN: Declara dependência ou relação com arquivo externo. -->
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
                <p class="portal-eyebrow">DEVIN PLAY · SUA PAUSA MERECIDA</p> <!-- Explicação DevIN: Declara parágrafo. -->
                <h1>Seu espaço para <span class="arcade-highlight">jogar e relaxar.</span></h1> <!-- Explicação DevIN: Declara título principal. A linha reúne múltiplos elementos. -->
                <p>São 14 minijogos para desafiar seus reflexos, memória e criatividade. Escolha um favorito ou deixe a sorte decidir. Os recordes continuam vinculados à sua conta.</p> <!-- Explicação DevIN: Declara parágrafo. -->
                <!-- Ferramentas de descoberta: filtragem apenas visual, sem consultas adicionais ao MySQL. -->
                <div class="arcade-quick-stats"><span><strong>14</strong> jogos</span><span><strong>8</strong> novidades</span><span>Recordes por perfil</span></div> <!-- Explicação DevIN: Declara agrupamento de elementos. A linha reúne múltiplos elementos. -->
            </section> <!-- Explicação DevIN: Fecha elemento section do documento. -->

            <section class="portal-layout"> <!-- Explicação DevIN: Declara seção temática. -->
                <div class="portal-library" aria-label="Catálogo de jogos"> <!-- Explicação DevIN: Declara agrupamento de elementos. Inclui atributos de acessibilidade. -->
                    <div class="arcade-library-heading"> <!-- Explicação DevIN: Declara agrupamento de elementos. -->
                        <div><p class="portal-eyebrow">EXPLORE O CATÁLOGO</p><h2>Escolha sua próxima aventura</h2></div> <!-- Explicação DevIN: Declara agrupamento de elementos. A linha reúne múltiplos elementos. -->
                        <button type="button" class="arcade-random" data-random-game aria-label="Escolher um jogo aleatoriamente">🎲 Surpreenda-me</button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                    <label class="arcade-search-label" for="arcade-search">Encontre um jogo</label> <!-- Explicação DevIN: Declara rótulo associado a um campo. -->
                    <input id="arcade-search" class="arcade-search" data-arcade-search type="search" placeholder="Buscar por nome ou estilo..." autocomplete="off" maxlength="70"> <!-- Explicação DevIN: Declara campo de entrada. -->
                    <div class="arcade-filters" data-arcade-filters role="group" aria-label="Filtrar por tipo de jogo"> <!-- Explicação DevIN: Declara agrupamento de elementos. Inclui atributos de acessibilidade. -->
                        <button class="arcade-filter is-selected" type="button" data-arcade-filter="todos" aria-pressed="true">Todos</button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                        <button class="arcade-filter" type="button" data-arcade-filter="Arcade" aria-pressed="false">Arcade</button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                        <button class="arcade-filter" type="button" data-arcade-filter="Reflexos" aria-pressed="false">Reflexos</button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                        <button class="arcade-filter" type="button" data-arcade-filter="Raciocínio" aria-pressed="false">Raciocínio</button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                        <button class="arcade-filter" type="button" data-arcade-filter="Habilidade" aria-pressed="false">Habilidade</button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                        <button class="arcade-filter" type="button" data-arcade-filter="Aventura" aria-pressed="false">Aventura</button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                        <button class="arcade-filter" type="button" data-arcade-filter="Corrida" aria-pressed="false">Corrida</button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                    <p class="arcade-found" data-arcade-count aria-live="polite">14 jogos disponíveis</p> <!-- Explicação DevIN: Declara parágrafo. Inclui atributos de acessibilidade. -->
                    <div class="portal-games" aria-label="Lista de jogos"> <!-- Explicação DevIN: Declara agrupamento de elementos. Inclui atributos de acessibilidade. -->
                    <button class="portal-game-card" type="button" data-launch-game="maze" data-genre="Aventura" aria-pressed="false"> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                        <span class="portal-game-icon" aria-hidden="true">◉</span> <!-- Explicação DevIN: Declara trecho em linha. Inclui atributos de acessibilidade. -->
                        <strong>Labirinto DevIN</strong><span>Explore fases, colete cristais e evite os perseguidores.</span> <!-- Explicação DevIN: Declara elemento de marcação. A linha reúne múltiplos elementos. -->
                    </button> <!-- Explicação DevIN: Fecha elemento button do documento. -->
                    <button class="portal-game-card" type="button" data-launch-game="snake" data-genre="Arcade" aria-pressed="false"> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                        <span class="portal-game-icon" aria-hidden="true">⌁</span> <!-- Explicação DevIN: Declara trecho em linha. Inclui atributos de acessibilidade. -->
                        <strong>Snake</strong><span>Cresça, avance de fase e não bata nas bordas ou em si mesmo.</span> <!-- Explicação DevIN: Declara elemento de marcação. A linha reúne múltiplos elementos. -->
                    </button> <!-- Explicação DevIN: Fecha elemento button do documento. -->
                    <button class="portal-game-card" type="button" data-launch-game="tetris" data-genre="Raciocínio" aria-pressed="false"> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                        <span class="portal-game-icon" aria-hidden="true">▦</span> <!-- Explicação DevIN: Declara trecho em linha. Inclui atributos de acessibilidade. -->
                        <strong>Blocos</strong><span>Complete linhas para pontuar e aumentar o nível.</span> <!-- Explicação DevIN: Declara elemento de marcação. A linha reúne múltiplos elementos. -->
                    </button> <!-- Explicação DevIN: Fecha elemento button do documento. -->
                    <button class="portal-game-card" type="button" data-launch-game="breakout" data-genre="Habilidade" aria-pressed="false"> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                        <span class="portal-game-icon" aria-hidden="true">▰</span> <!-- Explicação DevIN: Declara trecho em linha. Inclui atributos de acessibilidade. -->
                        <strong>Quebra-blocos</strong><span>Rebata a bola, limpe os blocos e alcance novas fases.</span> <!-- Explicação DevIN: Declara elemento de marcação. A linha reúne múltiplos elementos. -->
                    </button> <!-- Explicação DevIN: Fecha elemento button do documento. -->
                    <button class="portal-game-card" type="button" data-launch-game="memory" data-genre="Raciocínio" aria-pressed="false"> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                        <span class="portal-game-icon" aria-hidden="true">◇</span> <!-- Explicação DevIN: Declara trecho em linha. Inclui atributos de acessibilidade. -->
                        <strong>Memória</strong><span>Encontre os pares com o menor número de tentativas.</span> <!-- Explicação DevIN: Declara elemento de marcação. A linha reúne múltiplos elementos. -->
                    </button> <!-- Explicação DevIN: Fecha elemento button do documento. -->
                    <button class="portal-game-card" type="button" data-launch-game="runner" data-genre="Reflexos" aria-pressed="false"> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                        <span class="portal-game-icon" aria-hidden="true">◆</span> <!-- Explicação DevIN: Declara trecho em linha. Inclui atributos de acessibilidade. -->
                        <strong>Salto de Ritmo</strong><span>Corra, pule obstáculos e avance por trechos cada vez mais rápidos.</span> <!-- Explicação DevIN: Declara elemento de marcação. A linha reúne múltiplos elementos. -->
                    </button> <!-- Explicação DevIN: Fecha elemento button do documento. -->
                    <!-- Novo jogo do catálogo: o motor é registrado pelo arcade-extra-games.js. -->
                    <button class="portal-game-card" type="button" data-launch-game="pong" data-genre="Habilidade" aria-pressed="false"> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                        <span class="portal-game-icon" aria-hidden="true">🏓</span> <!-- Explicação DevIN: Declara trecho em linha. Inclui atributos de acessibilidade. -->
                        <strong>Pong Solo</strong><span>Rebata a bola e mantenha a sequência viva.</span> <!-- Explicação DevIN: Declara elemento de marcação. A linha reúne múltiplos elementos. -->
                        <small class="arcade-tag">Habilidade · Novo</small> <!-- Explicação DevIN: Declara elemento de marcação. -->
                    </button> <!-- Explicação DevIN: Fecha elemento button do documento. -->
                    <!-- Novo jogo do catálogo: o motor é registrado pelo arcade-extra-games.js. -->
                    <button class="portal-game-card" type="button" data-launch-game="dodge" data-genre="Reflexos" aria-pressed="false"> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                        <span class="portal-game-icon" aria-hidden="true">☄️</span> <!-- Explicação DevIN: Declara trecho em linha. Inclui atributos de acessibilidade. -->
                        <strong>Chuva de Meteoros</strong><span>Desvie de perigos que caem cada vez mais rápido.</span> <!-- Explicação DevIN: Declara elemento de marcação. A linha reúne múltiplos elementos. -->
                        <small class="arcade-tag">Reflexos · Novo</small> <!-- Explicação DevIN: Declara elemento de marcação. -->
                    </button> <!-- Explicação DevIN: Fecha elemento button do documento. -->
                    <!-- Novo jogo do catálogo: o motor é registrado pelo arcade-extra-games.js. -->
                    <button class="portal-game-card" type="button" data-launch-game="flappy" data-genre="Arcade" aria-pressed="false"> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                        <span class="portal-game-icon" aria-hidden="true">🐦</span> <!-- Explicação DevIN: Declara trecho em linha. Inclui atributos de acessibilidade. -->
                        <strong>Voo Pixel</strong><span>Voe entre barreiras e bata seu recorde.</span> <!-- Explicação DevIN: Declara elemento de marcação. A linha reúne múltiplos elementos. -->
                        <small class="arcade-tag">Arcade · Novo</small> <!-- Explicação DevIN: Declara elemento de marcação. -->
                    </button> <!-- Explicação DevIN: Fecha elemento button do documento. -->
                    <!-- Novo jogo do catálogo: o motor é registrado pelo arcade-extra-games.js. -->
                    <button class="portal-game-card" type="button" data-launch-game="target" data-genre="Reflexos" aria-pressed="false"> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                        <span class="portal-game-icon" aria-hidden="true">🎯</span> <!-- Explicação DevIN: Declara trecho em linha. Inclui atributos de acessibilidade. -->
                        <strong>Alvo Rápido</strong><span>Acerte o máximo de alvos em 45 segundos.</span> <!-- Explicação DevIN: Declara elemento de marcação. A linha reúne múltiplos elementos. -->
                        <small class="arcade-tag">Reflexos · Novo</small> <!-- Explicação DevIN: Declara elemento de marcação. -->
                    </button> <!-- Explicação DevIN: Fecha elemento button do documento. -->
                    <!-- Novo jogo do catálogo: o motor é registrado pelo arcade-extra-games.js. -->
                    <button class="portal-game-card" type="button" data-launch-game="collector" data-genre="Aventura" aria-pressed="false"> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                        <span class="portal-game-icon" aria-hidden="true">⭐</span> <!-- Explicação DevIN: Declara trecho em linha. Inclui atributos de acessibilidade. -->
                        <strong>Caça às Estrelas</strong><span>Colete tesouros e escape dos perseguidores.</span> <!-- Explicação DevIN: Declara elemento de marcação. A linha reúne múltiplos elementos. -->
                        <small class="arcade-tag">Aventura · Novo</small> <!-- Explicação DevIN: Declara elemento de marcação. -->
                    </button> <!-- Explicação DevIN: Fecha elemento button do documento. -->
                    <!-- Novo jogo do catálogo: o motor é registrado pelo arcade-extra-games.js. -->
                    <button class="portal-game-card" type="button" data-launch-game="racer" data-genre="Corrida" aria-pressed="false"> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                        <span class="portal-game-icon" aria-hidden="true">🏎️</span> <!-- Explicação DevIN: Declara trecho em linha. Inclui atributos de acessibilidade. -->
                        <strong>Desvio Turbo</strong><span>Troque de faixa e escape do trânsito.</span> <!-- Explicação DevIN: Declara elemento de marcação. A linha reúne múltiplos elementos. -->
                        <small class="arcade-tag">Corrida · Novo</small> <!-- Explicação DevIN: Declara elemento de marcação. -->
                    </button> <!-- Explicação DevIN: Fecha elemento button do documento. -->
                    <!-- Novo jogo do catálogo: o motor é registrado pelo arcade-extra-games.js. -->
                    <button class="portal-game-card" type="button" data-launch-game="simon" data-genre="Raciocínio" aria-pressed="false"> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                        <span class="portal-game-icon" aria-hidden="true">🧠</span> <!-- Explicação DevIN: Declara trecho em linha. Inclui atributos de acessibilidade. -->
                        <strong>Sequência de Cores</strong><span>Memorize as cores e repita a ordem.</span> <!-- Explicação DevIN: Declara elemento de marcação. A linha reúne múltiplos elementos. -->
                        <small class="arcade-tag">Raciocínio · Novo</small> <!-- Explicação DevIN: Declara elemento de marcação. -->
                    </button> <!-- Explicação DevIN: Fecha elemento button do documento. -->
                    <!-- Novo jogo do catálogo: o motor é registrado pelo arcade-extra-games.js. -->
                    <button class="portal-game-card" type="button" data-launch-game="basket" data-genre="Habilidade" aria-pressed="false"> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                        <span class="portal-game-icon" aria-hidden="true">🧺</span> <!-- Explicação DevIN: Declara trecho em linha. Inclui atributos de acessibilidade. -->
                        <strong>Cesta Relâmpago</strong><span>Pegue bolas douradas e evite as perigosas.</span> <!-- Explicação DevIN: Declara elemento de marcação. A linha reúne múltiplos elementos. -->
                        <small class="arcade-tag">Habilidade · Novo</small> <!-- Explicação DevIN: Declara elemento de marcação. -->
                    </button> <!-- Explicação DevIN: Fecha elemento button do documento. -->
                    </div> <!-- Explicação DevIN: Fecha elemento div do documento. -->
                    <p class="arcade-empty" data-arcade-empty hidden>Nenhum jogo encontrado. Experimente outra palavra ou categoria.</p> <!-- Explicação DevIN: Declara parágrafo. -->
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
                    <!-- Controles auxiliares garantem que os jogos sejam acessíveis no celular. -->
                    <div class="arcade-touch-controls" aria-label="Controles móveis"> <!-- Explicação DevIN: Declara agrupamento de elementos. Inclui atributos de acessibilidade. -->
                        <button type="button" data-arcade-key="ArrowLeft" aria-label="Mover para esquerda">←</button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                        <button type="button" data-arcade-key="ArrowUp" aria-label="Mover para cima ou pular">↑</button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                        <button type="button" data-arcade-key="ArrowDown" aria-label="Mover para baixo">↓</button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                        <button type="button" data-arcade-key="ArrowRight" aria-label="Mover para direita">→</button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
                        <button type="button" data-arcade-key="Space" aria-label="Pular ou acionar">⤒</button> <!-- Explicação DevIN: Declara botão de ação. Inclui atributos de acessibilidade. -->
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
    <script src="../js/arcade-extra-games.js?v=<?= filemtime(__DIR__ . '/../js/arcade-extra-games.js') ?>"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
    <script src="../js/arcade-library.js?v=<?= filemtime(__DIR__ . '/../js/arcade-library.js') ?>"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
    <script src="../js/arcade-games.js?v=<?= filemtime(__DIR__ . '/../js/arcade-games.js') ?>"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
    <script src="../js/lenis-global.js?v=<?= filemtime(__DIR__ . '/../js/lenis-global.js') ?>"></script> <!-- Explicação DevIN: Declara script ou dependência JavaScript. Contém dado dinâmico processado pelo PHP. -->
</body> <!-- Explicação DevIN: Fecha elemento body do documento. -->
</html> <!-- Explicação DevIN: Fecha elemento html do documento. -->
