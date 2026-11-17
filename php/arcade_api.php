<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/arcade_api.php
 * Finalidade: arcade api.
 * Domínio: processamento no servidor.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


require_once __DIR__ . '/middlewares/auth.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/controllers/ArcadeProgressPolicy.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

header('Content-Type: application/json; charset=utf-8'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
header('Cache-Control: no-store, private'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.

// Detalhe DevIN: Método arcadeRespond: encapsula uma responsabilidade reutilizável do módulo no contexto de arcade api. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function arcadeRespond(array $data, int $status = 200): void // Explicação DevIN: Declara função/método arcadeRespond, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    http_response_code($status); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
    exit; // Explicação DevIN: Continua expressão ou bloco pertencente a função arcadeRespond.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Login permanece obrigatório. O código secreto é somente um atalho
// opcional; o menu Jogos permite acesso direto de pessoas, empresas e ADMs.
function ensureArcadeProgressTable(mysqli $conn): void // Explicação DevIN: Declara função/método ensureArcadeProgressTable, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
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
    '); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Detalhe DevIN: Método arcadeGameLimits: encapsula uma responsabilidade reutilizável do módulo no contexto de arcade api. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function arcadeGameLimits(): array // Explicação DevIN: Declara função/método arcadeGameLimits, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    return [ // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        'maze' => ['score_per_second' => 1200, 'seconds_per_level' => 8], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'snake' => ['score_per_second' => 2000, 'seconds_per_level' => 2], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'tetris' => ['score_per_second' => 5000, 'seconds_per_level' => 12], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'breakout' => ['score_per_second' => 1000, 'seconds_per_level' => 5], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'memory' => ['score_per_second' => 800, 'seconds_per_level' => 5], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'runner' => ['score_per_second' => 350, 'seconds_per_level' => 4], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        // Limites conservadores de plausibilidade para os novos minijogos.
        // Não substituem validação antitrapaça no servidor; bloqueiam abuso evidente.
        'pong' => ['score_per_second' => 1200, 'seconds_per_level' => 3, 'score_per_level' => 125], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'dodge' => ['score_per_second' => 120, 'seconds_per_level' => 5, 'score_per_level' => 150], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'flappy' => ['score_per_second' => 900, 'seconds_per_level' => 7, 'score_per_level' => 120], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'target' => ['score_per_second' => 1800, 'seconds_per_level' => 1, 'score_per_level' => 140], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'collector' => ['score_per_second' => 1800, 'seconds_per_level' => 1, 'score_per_level' => 150], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'racer' => ['score_per_second' => 300, 'seconds_per_level' => 2, 'score_per_level' => 200], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'simon' => ['score_per_second' => 1400, 'seconds_per_level' => 5, 'score_per_level' => 120], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'basket' => ['score_per_second' => 1800, 'seconds_per_level' => 4, 'score_per_level' => 140], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    $usuario = requireWebAuth(); // Explicação DevIN: Atribui um valor à variável PHP $usuario.
    // A autenticação de sessão já foi validada por requireWebAuth(). // Explicação DevIN: Continua expressão ou bloco pertencente a função arcadeGameLimits.
    $conn = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $conn.
    ensureArcadeProgressTable($conn); // Explicação DevIN: Continua expressão ou bloco pertencente a função arcadeGameLimits.

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // O escopo padrão de todos os perfis — inclusive ADM — é pessoal.
        // A lista agregada só fica disponível com escopo explícito e conta ADM.
        $overview = $usuario['tipo'] === 'adm' && ($_GET['escopo'] ?? '') === 'todos'; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if ($overview) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $result = $conn->query("
                SELECT gp.perfil_tipo, gp.perfil_id, gp.jogo, gp.recorde, gp.maior_fase,
                       gp.ultima_atividade, COALESCE(p.nome, e.nome, a.nome, 'Conta removida') AS nome
                FROM progresso_jogos gp
                LEFT JOIN pessoa p ON gp.perfil_tipo = 'pessoa' AND p.id_pessoa = gp.perfil_id
                LEFT JOIN empresa e ON gp.perfil_tipo = 'empresa' AND e.id_empresa = gp.perfil_id
                LEFT JOIN administrador a ON gp.perfil_tipo = 'adm' AND a.id_administrador = gp.perfil_id
                ORDER BY gp.ultima_atividade DESC
                LIMIT 500
            "); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
            // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
            $stmt = $conn->prepare('
                SELECT jogo, recorde, maior_fase, ultima_atividade
                FROM progresso_jogos
                WHERE perfil_tipo = ? AND perfil_id = ?
                ORDER BY ultima_atividade DESC
            '); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            $stmt->bind_param('si', $usuario['tipo'], $usuario['id']); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $result = $stmt->get_result(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $rows = $result->fetch_all(MYSQLI_ASSOC); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        if (!$overview) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função arcadeGameLimits.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $conn->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função arcadeGameLimits.
        arcadeRespond(['sucesso' => true, 'progresso' => $rows, 'escopo' => $overview ? 'todos' : 'proprio']); // Explicação DevIN: Continua expressão ou bloco pertencente a função arcadeGameLimits.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $conn->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função arcadeGameLimits.
        arcadeRespond(['sucesso' => false, 'mensagem' => 'Método não permitido.'], 405); // Explicação DevIN: Continua expressão ou bloco pertencente a função arcadeGameLimits.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    requireValidCsrf(); // Explicação DevIN: Continua expressão ou bloco pertencente a função arcadeGameLimits.
    $action = requestString($_POST, 'acao'); // Explicação DevIN: Atribui um valor à variável PHP $action.
    $allowedGames = array_keys(arcadeGameLimits()); // Explicação DevIN: Atribui um valor à variável PHP $allowedGames.
    if ($action === 'iniciar') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $game = requestString($_POST, 'jogo'); // Explicação DevIN: Atribui um valor à variável PHP $game.
        if (!in_array($game, $allowedGames, true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $conn->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função arcadeGameLimits.
            arcadeRespond(['sucesso' => false, 'mensagem' => 'Jogo inválido.'], 422); // Explicação DevIN: Continua expressão ou bloco pertencente a função arcadeGameLimits.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $_SESSION['arcade_games'][$game] = [ // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            'iniciado_em' => time(), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'recorde' => 0, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'maior_fase' => 1, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
        $conn->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função arcadeGameLimits.
        arcadeRespond(['sucesso' => true]); // Explicação DevIN: Continua expressão ou bloco pertencente a função arcadeGameLimits.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    if ($action === 'salvar') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $game = requestString($_POST, 'jogo'); // Explicação DevIN: Atribui um valor à variável PHP $game.
        $score = filter_var($_POST['recorde'] ?? null, FILTER_VALIDATE_INT); // Explicação DevIN: Atribui um valor à variável PHP $score.
        $level = filter_var($_POST['fase'] ?? null, FILTER_VALIDATE_INT); // Explicação DevIN: Atribui um valor à variável PHP $level.
        if (!in_array($game, $allowedGames, true) || $score === false || $score < 0 || $score > 100000000 // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            || $level === false || $level < 1 || $level > 100000 // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        ) { // Explicação DevIN: Continua expressão ou bloco pertencente a função arcadeGameLimits.
            $conn->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função arcadeGameLimits.
            arcadeRespond(['sucesso' => false, 'mensagem' => 'Dados de progresso inválidos.'], 422); // Explicação DevIN: Continua expressão ou bloco pertencente a função arcadeGameLimits.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $gameSession = $_SESSION['arcade_games'][$game] ?? null; // Explicação DevIN: Atribui um valor à variável PHP $gameSession.
        if (!is_array($gameSession) || !isset($gameSession['iniciado_em'])) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $conn->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função arcadeGameLimits.
            arcadeRespond(['sucesso' => false, 'mensagem' => 'Inicie uma partida válida antes de salvar o progresso.'], 403); // Explicação DevIN: Continua expressão ou bloco pertencente a função arcadeGameLimits.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $elapsed = max(1, time() - (int) $gameSession['iniciado_em']); // Explicação DevIN: Atribui um valor à variável PHP $elapsed.
        $limits = arcadeGameLimits()[$game]; // Explicação DevIN: Atribui um valor à variável PHP $limits.
        // As regras consideram o tempo de sessão, a evolução anterior e
        // o avanço por pontos dos oito minijogos; não confiam em valores
        // enviados pelo navegador como prova de uma partida legítima.
        if (!ArcadeProgressPolicy::isPlausible(
            $score,
            $level,
            $elapsed,
            (int) ($gameSession['recorde'] ?? 0),
            (int) ($gameSession['maior_fase'] ?? 1),
            $limits
        )) {
            $conn->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função arcadeGameLimits.
            arcadeRespond(['sucesso' => false, 'mensagem' => 'Pontuação ou fase incompatível com o tempo de partida.'], 422); // Explicação DevIN: Continua expressão ou bloco pertencente a função arcadeGameLimits.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $_SESSION['arcade_games'][$game]['recorde'] = $score; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        $_SESSION['arcade_games'][$game]['maior_fase'] = $level; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
        $stmt = $conn->prepare('
            INSERT INTO progresso_jogos (perfil_tipo, perfil_id, jogo, recorde, maior_fase, ultima_atividade)
            VALUES (?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                recorde = GREATEST(recorde, VALUES(recorde)),
                maior_fase = GREATEST(maior_fase, VALUES(maior_fase)),
                ultima_atividade = NOW()
        '); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        $stmt->bind_param('sisii', $usuario['tipo'], $usuario['id'], $game, $score, $level); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função arcadeGameLimits.
        $conn->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função arcadeGameLimits.
        arcadeRespond(['sucesso' => true]); // Explicação DevIN: Continua expressão ou bloco pertencente a função arcadeGameLimits.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    if ($action === 'classificacao') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $game = requestString($_POST, 'jogo'); // Explicação DevIN: Atribui um valor à variável PHP $game.
        if (!in_array($game, $allowedGames, true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $conn->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função arcadeGameLimits.
            arcadeRespond(['sucesso' => false, 'mensagem' => 'Jogo inválido.'], 422); // Explicação DevIN: Continua expressão ou bloco pertencente a função arcadeGameLimits.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
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
        "); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        $stmt->bind_param('s', $game); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função arcadeGameLimits.
        $conn->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função arcadeGameLimits.
        arcadeRespond(['sucesso' => true, 'classificacao' => $rows]); // Explicação DevIN: Continua expressão ou bloco pertencente a função arcadeGameLimits.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    if ($action === 'excluir' && $usuario['tipo'] === 'adm') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        // Bloqueia edição de progresso de terceiros até verificação administrativa.
        require_once __DIR__ . '/controllers/Security/AdminStepUp.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
        if (AdminStepUp::enabled() && !AdminStepUp::allowed('site')) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            http_response_code(403); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            header('Content-Type: application/json; charset=utf-8'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            echo json_encode(['sucesso' => false, 'mensagem' => 'Verificação adicional necessária.']); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
            exit; // Explicação DevIN: Continua expressão ou bloco pertencente a função arcadeGameLimits.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $perfilTipo = requestString($_POST, 'perfil_tipo'); // Explicação DevIN: Atribui um valor à variável PHP $perfilTipo.
        $perfilId = filter_var($_POST['perfil_id'] ?? null, FILTER_VALIDATE_INT); // Explicação DevIN: Atribui um valor à variável PHP $perfilId.
        $game = requestString($_POST, 'jogo'); // Explicação DevIN: Atribui um valor à variável PHP $game.
        if (!in_array($perfilTipo, ['pessoa', 'empresa', 'adm'], true) || $perfilId === false || $perfilId <= 0 // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            || !in_array($game, $allowedGames, true) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        ) { // Explicação DevIN: Continua expressão ou bloco pertencente a função arcadeGameLimits.
            $conn->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função arcadeGameLimits.
            arcadeRespond(['sucesso' => false, 'mensagem' => 'Registro inválido.'], 422); // Explicação DevIN: Continua expressão ou bloco pertencente a função arcadeGameLimits.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
        $stmt = $conn->prepare('DELETE FROM progresso_jogos WHERE perfil_tipo = ? AND perfil_id = ? AND jogo = ?'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->bind_param('sis', $perfilTipo, $perfilId, $game); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função arcadeGameLimits.
        $conn->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função arcadeGameLimits.
        arcadeRespond(['sucesso' => true]); // Explicação DevIN: Continua expressão ou bloco pertencente a função arcadeGameLimits.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    $conn->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função arcadeGameLimits.
    arcadeRespond(['sucesso' => false, 'mensagem' => 'Ação não permitida.'], 403); // Explicação DevIN: Continua expressão ou bloco pertencente a função arcadeGameLimits.
} catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    error_log('Erro na API DevIN Arcade: ' . $exception->getMessage()); // Explicação DevIN: Continua expressão ou bloco pertencente a função arcadeGameLimits.
    arcadeRespond(['sucesso' => false, 'mensagem' => 'Não foi possível processar a solicitação do arcade.'], 500); // Explicação DevIN: Continua expressão ou bloco pertencente a função arcadeGameLimits.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
