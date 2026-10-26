<?php

declare(strict_types=1);

require_once __DIR__ . '/middlewares/auth.php';
require_once __DIR__ . '/config/security.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');

function arcadeRespond(array $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function requireArcadeUnlock(array $usuario): void
{
    $liberacao = $_SESSION['arcade_unlock'] ?? [];
    if (!is_array($liberacao)
        || (int) ($liberacao['id'] ?? 0) !== $usuario['id']
        || ($liberacao['tipo'] ?? '') !== $usuario['tipo']
        || time() - (int) ($liberacao['liberado_em'] ?? 0) > 7200
    ) {
        unset($_SESSION['arcade_unlock']);
        arcadeRespond(['sucesso' => false, 'mensagem' => 'A área de jogos precisa ser liberada pelo dashboard.'], 403);
    }
}

function ensureArcadeProgressTable(mysqli $conn): void
{
    $conn->query('
        CREATE TABLE IF NOT EXISTS progresso_jogos (
            perfil_tipo VARCHAR(16) NOT NULL,
            perfil_id INT NOT NULL,
            jogo VARCHAR(24) NOT NULL,
            recorde INT UNSIGNED NOT NULL DEFAULT 0,
            maior_fase INT UNSIGNED NOT NULL DEFAULT 1,
            ultima_atividade DATETIME NOT NULL,
            PRIMARY KEY (perfil_tipo, perfil_id, jogo),
            INDEX idx_progresso_jogos_atividade (ultima_atividade)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ');
}

function arcadeGameLimits(): array
{
    return [
        'maze' => ['score_per_second' => 1200, 'seconds_per_level' => 8],
        'snake' => ['score_per_second' => 2000, 'seconds_per_level' => 2],
        'tetris' => ['score_per_second' => 5000, 'seconds_per_level' => 12],
        'breakout' => ['score_per_second' => 1000, 'seconds_per_level' => 5],
        'memory' => ['score_per_second' => 800, 'seconds_per_level' => 5],
        'runner' => ['score_per_second' => 350, 'seconds_per_level' => 4],
    ];
}

try {
    $usuario = requireWebAuth();
    requireArcadeUnlock($usuario);
    $conn = getDatabaseConnection();
    ensureArcadeProgressTable($conn);

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        if ($usuario['tipo'] === 'adm') {
            $result = $conn->query("
                SELECT gp.perfil_tipo, gp.perfil_id, gp.jogo, gp.recorde, gp.maior_fase,
                       gp.ultima_atividade, COALESCE(p.nome, e.nome, a.nome, 'Conta removida') AS nome
                FROM progresso_jogos gp
                LEFT JOIN pessoa p ON gp.perfil_tipo = 'pessoa' AND p.id_pessoa = gp.perfil_id
                LEFT JOIN empresa e ON gp.perfil_tipo = 'empresa' AND e.id_empresa = gp.perfil_id
                LEFT JOIN administrador a ON gp.perfil_tipo = 'adm' AND a.id_administrador = gp.perfil_id
                ORDER BY gp.ultima_atividade DESC
                LIMIT 500
            ");
        } else {
            $stmt = $conn->prepare('
                SELECT jogo, recorde, maior_fase, ultima_atividade
                FROM progresso_jogos
                WHERE perfil_tipo = ? AND perfil_id = ?
                ORDER BY ultima_atividade DESC
            ');
            $stmt->bind_param('si', $usuario['tipo'], $usuario['id']);
            $stmt->execute();
            $result = $stmt->get_result();
        }

        $rows = $result->fetch_all(MYSQLI_ASSOC);
        if ($usuario['tipo'] !== 'adm') {
            $stmt->close();
        }
        $conn->close();
        arcadeRespond(['sucesso' => true, 'progresso' => $rows]);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $conn->close();
        arcadeRespond(['sucesso' => false, 'mensagem' => 'Método não permitido.'], 405);
    }

    requireValidCsrf();
    $action = requestString($_POST, 'acao');
    $allowedGames = array_keys(arcadeGameLimits());
    if ($action === 'iniciar') {
        $game = requestString($_POST, 'jogo');
        if (!in_array($game, $allowedGames, true)) {
            $conn->close();
            arcadeRespond(['sucesso' => false, 'mensagem' => 'Jogo inválido.'], 422);
        }

        $_SESSION['arcade_games'][$game] = [
            'iniciado_em' => time(),
            'recorde' => 0,
            'maior_fase' => 1,
        ];
        $conn->close();
        arcadeRespond(['sucesso' => true]);
    }

    if ($action === 'salvar') {
        $game = requestString($_POST, 'jogo');
        $score = filter_var($_POST['recorde'] ?? null, FILTER_VALIDATE_INT);
        $level = filter_var($_POST['fase'] ?? null, FILTER_VALIDATE_INT);
        if (!in_array($game, $allowedGames, true) || $score === false || $score < 0 || $score > 100000000
            || $level === false || $level < 1 || $level > 100000
        ) {
            $conn->close();
            arcadeRespond(['sucesso' => false, 'mensagem' => 'Dados de progresso inválidos.'], 422);
        }

        $gameSession = $_SESSION['arcade_games'][$game] ?? null;
        if (!is_array($gameSession) || !isset($gameSession['iniciado_em'])) {
            $conn->close();
            arcadeRespond(['sucesso' => false, 'mensagem' => 'Inicie uma partida válida antes de salvar o progresso.'], 403);
        }

        $elapsed = max(1, time() - (int) $gameSession['iniciado_em']);
        $limits = arcadeGameLimits()[$game];
        $maximumScore = $elapsed * $limits['score_per_second'];
        $maximumLevel = 1 + (int) floor($elapsed / $limits['seconds_per_level']);
        if ($score > $maximumScore || $level > $maximumLevel
            || $score < (int) ($gameSession['recorde'] ?? 0)
            || $level < (int) ($gameSession['maior_fase'] ?? 1)
        ) {
            $conn->close();
            arcadeRespond(['sucesso' => false, 'mensagem' => 'Pontuação ou fase incompatível com o tempo de partida.'], 422);
        }

        $_SESSION['arcade_games'][$game]['recorde'] = $score;
        $_SESSION['arcade_games'][$game]['maior_fase'] = $level;
        $stmt = $conn->prepare('
            INSERT INTO progresso_jogos (perfil_tipo, perfil_id, jogo, recorde, maior_fase, ultima_atividade)
            VALUES (?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                recorde = GREATEST(recorde, VALUES(recorde)),
                maior_fase = GREATEST(maior_fase, VALUES(maior_fase)),
                ultima_atividade = NOW()
        ');
        $stmt->bind_param('sisii', $usuario['tipo'], $usuario['id'], $game, $score, $level);
        $stmt->execute();
        $stmt->close();
        $conn->close();
        arcadeRespond(['sucesso' => true]);
    }

    if ($action === 'classificacao') {
        $game = requestString($_POST, 'jogo');
        if (!in_array($game, $allowedGames, true)) {
            $conn->close();
            arcadeRespond(['sucesso' => false, 'mensagem' => 'Jogo inválido.'], 422);
        }

        $stmt = $conn->prepare("
            SELECT COALESCE(NULLIF(p.nome, ''), NULLIF(e.nome, ''), NULLIF(a.nome, ''), 'Conta removida') AS nome,
                   gp.recorde, gp.maior_fase
            FROM progresso_jogos gp
            LEFT JOIN pessoa p ON gp.perfil_tipo = 'pessoa' AND p.id_pessoa = gp.perfil_id
            LEFT JOIN empresa e ON gp.perfil_tipo = 'empresa' AND e.id_empresa = gp.perfil_id
            LEFT JOIN administrador a ON gp.perfil_tipo = 'adm' AND a.id_administrador = gp.perfil_id
            WHERE gp.jogo = ?
            ORDER BY gp.recorde DESC, gp.maior_fase DESC, gp.ultima_atividade ASC
            LIMIT 10
        ");
        $stmt->bind_param('s', $game);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $conn->close();
        arcadeRespond(['sucesso' => true, 'classificacao' => $rows]);
    }

    if ($action === 'excluir' && $usuario['tipo'] === 'adm') {
        $perfilTipo = requestString($_POST, 'perfil_tipo');
        $perfilId = filter_var($_POST['perfil_id'] ?? null, FILTER_VALIDATE_INT);
        $game = requestString($_POST, 'jogo');
        if (!in_array($perfilTipo, ['pessoa', 'empresa', 'adm'], true) || $perfilId === false || $perfilId <= 0
            || !in_array($game, ['maze', 'snake', 'tetris', 'breakout', 'memory', 'runner'], true)
        ) {
            $conn->close();
            arcadeRespond(['sucesso' => false, 'mensagem' => 'Registro inválido.'], 422);
        }

        $stmt = $conn->prepare('DELETE FROM progresso_jogos WHERE perfil_tipo = ? AND perfil_id = ? AND jogo = ?');
        $stmt->bind_param('sis', $perfilTipo, $perfilId, $game);
        $stmt->execute();
        $stmt->close();
        $conn->close();
        arcadeRespond(['sucesso' => true]);
    }

    $conn->close();
    arcadeRespond(['sucesso' => false, 'mensagem' => 'Ação não permitida.'], 403);
} catch (Throwable $exception) {
    error_log('Erro na API DevIN Arcade: ' . $exception->getMessage());
    arcadeRespond(['sucesso' => false, 'mensagem' => 'Não foi possível processar a solicitação do arcade.'], 500);
}
