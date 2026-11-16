<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

require_once __DIR__ . '/ChatStorage.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

function ensureProfileReportsSchema(mysqli $conn): void // Explicação DevIN: Declara função/método ensureProfileReportsSchema, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    $conn->query("
        CREATE TABLE IF NOT EXISTS reports_perfis (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            tipo_denunciado ENUM('pessoa', 'empresa') NOT NULL,
            id_denunciado INT NOT NULL,
            nome_denunciado VARCHAR(255) NOT NULL,
            tipo_denunciante ENUM('pessoa', 'empresa') NOT NULL,
            id_denunciante INT NOT NULL,
            nome_denunciante VARCHAR(255) NOT NULL,
            motivo VARCHAR(40) NOT NULL,
            detalhes TEXT NOT NULL,
            status ENUM('pendente', 'concluida') NOT NULL DEFAULT 'pendente',
            veredito ENUM('manter', 'excluir') NULL,
            parecer TEXT NOT NULL DEFAULT '',
            analisado_por INT NULL,
            criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            analisado_em DATETIME NULL,
            UNIQUE KEY uq_report_perfil_denunciante (
                tipo_denunciado, id_denunciado, tipo_denunciante, id_denunciante
            ),
            INDEX idx_reports_perfis_status (tipo_denunciado, status, criado_em)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    "); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.

    $columns = [ // Explicação DevIN: Atribui um valor à variável PHP $columns.
        'veredito' => 'ALTER TABLE reports_perfis ADD COLUMN veredito ENUM("manter", "excluir") NULL', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'parecer' => 'ALTER TABLE reports_perfis ADD COLUMN parecer TEXT NOT NULL DEFAULT ""', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
    $stmt = $conn->prepare('
        SELECT COLUMN_NAME
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = "reports_perfis"
          AND COLUMN_NAME IN ("veredito", "parecer")
    '); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
    $existingColumns = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'COLUMN_NAME'); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
    $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função ensureProfileReportsSchema.

    foreach ($columns as $column => $alterSql) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
        if (!in_array($column, $existingColumns, true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                $conn->query($alterSql); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            } catch (mysqli_sql_exception $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
                if ($exception->getCode() !== 1060) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    throw $exception; // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    $conn->query("
        CREATE TABLE IF NOT EXISTS reports_perfis_notificacoes (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_report BIGINT UNSIGNED NOT NULL,
            tipo_destinatario ENUM('pessoa', 'empresa') NOT NULL,
            id_destinatario INT NOT NULL,
            mensagem TEXT NOT NULL,
            entregue_em DATETIME NULL,
            UNIQUE KEY uq_report_perfil_notificacao_destinatario (id_report, tipo_destinatario, id_destinatario),
            INDEX idx_reports_perfis_notificacoes_pendentes (entregue_em)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    "); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.

    $conn->query("
        DELETE FROM reports_perfis
        WHERE status = 'concluida'
          AND analisado_em IS NOT NULL
          AND analisado_em <= DATE_SUB(NOW(), INTERVAL 1 DAY)
    "); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

function dispatchProfileReportNotifications(mysqli $conn): bool // Explicação DevIN: Declara função/método dispatchProfileReportNotifications, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    $result = $conn->query('
        SELECT id, tipo_destinatario, id_destinatario, mensagem
        FROM reports_perfis_notificacoes
        WHERE entregue_em IS NULL
        ORDER BY id
        LIMIT 100
    '); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    $notifications = $result->fetch_all(MYSQLI_ASSOC); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
    $stmt = $conn->prepare('UPDATE reports_perfis_notificacoes SET entregue_em = NOW() WHERE id = ? AND entregue_em IS NULL'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
    $allDelivered = true; // Explicação DevIN: Atribui um valor à variável PHP $allDelivered.

    foreach ($notifications as $notification) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
        $notificationId = (int) $notification['id']; // Explicação DevIN: Atribui um valor à variável PHP $notificationId.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            appendPlatformChatNotification( // Explicação DevIN: Continua expressão ou bloco pertencente a função dispatchProfileReportNotifications.
                'profile-report:' . $notificationId, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                (string) $notification['tipo_destinatario'], // Explicação DevIN: Continua expressão ou bloco pertencente a função dispatchProfileReportNotifications.
                (int) $notification['id_destinatario'], // Explicação DevIN: Continua expressão ou bloco pertencente a função dispatchProfileReportNotifications.
                (string) $notification['mensagem'] // Explicação DevIN: Continua expressão ou bloco pertencente a função dispatchProfileReportNotifications.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $stmt->bind_param('i', $notificationId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        } catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            error_log('Falha ao entregar notificação de denúncia de perfil: ' . $exception->getMessage()); // Explicação DevIN: Continua expressão ou bloco pertencente a função dispatchProfileReportNotifications.
            $allDelivered = false; // Explicação DevIN: Atribui um valor à variável PHP $allDelivered.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função dispatchProfileReportNotifications.
    return $allDelivered; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

function submitProfileReport( // Explicação DevIN: Declara função/método submitProfileReport, com seus parâmetros e comportamento.
    mysqli $conn, // Explicação DevIN: Continua expressão ou bloco pertencente a função submitProfileReport.
    array $reporter, // Explicação DevIN: Continua expressão ou bloco pertencente a função submitProfileReport.
    string $targetType, // Explicação DevIN: Continua expressão ou bloco pertencente a função submitProfileReport.
    int $targetId, // Explicação DevIN: Continua expressão ou bloco pertencente a função submitProfileReport.
    string $reason, // Explicação DevIN: Continua expressão ou bloco pertencente a função submitProfileReport.
    string $details // Explicação DevIN: Continua expressão ou bloco pertencente a função submitProfileReport.
): array { // Explicação DevIN: Continua expressão ou bloco pertencente a função submitProfileReport.
    $allowedReasons = ['assedio', 'fraude', 'falsidade', 'conteudo_inadequado', 'outro']; // Explicação DevIN: Atribui um valor à variável PHP $allowedReasons.
    if ( // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        !in_array($reporter['tipo'] ?? '', ['pessoa', 'empresa'], true) // Explicação DevIN: Continua expressão ou bloco pertencente a função submitProfileReport.
        || !in_array($targetType, ['pessoa', 'empresa'], true) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        || $targetId < 1 // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    ) { // Explicação DevIN: Continua expressão ou bloco pertencente a função submitProfileReport.
        throw new InvalidArgumentException('Perfil inválido para denúncia.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if ($reporter['tipo'] === $targetType && (int) $reporter['id'] === $targetId) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new InvalidArgumentException('Não é possível denunciar seu próprio perfil.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if (!in_array($reason, $allowedReasons, true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new InvalidArgumentException('Selecione um motivo válido para a denúncia.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if ($details === '' || mb_strlen($details) > 1000) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new InvalidArgumentException('Descreva o motivo em até 1.000 caracteres.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    [$table, $idColumn] = $targetType === 'pessoa' // Explicação DevIN: Continua expressão ou bloco pertencente a função submitProfileReport.
        ? ['pessoa', 'id_pessoa'] // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        : ['empresa', 'id_empresa']; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    $stmt = $conn->prepare("SELECT nome FROM {$table} WHERE {$idColumn} = ? LIMIT 1"); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
    $stmt->bind_param('i', $targetId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
    $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
    $target = $stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
    $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função submitProfileReport.
    if (!$target) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new InvalidArgumentException('Este perfil não está mais disponível.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    $stmt = $conn->prepare('
        INSERT INTO reports_perfis (
            tipo_denunciado, id_denunciado, nome_denunciado,
            tipo_denunciante, id_denunciante, nome_denunciante,
            motivo, detalhes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    '); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    $stmt->bind_param( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        'sississs', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        $targetType, // Explicação DevIN: Continua expressão ou bloco pertencente a função submitProfileReport.
        $targetId, // Explicação DevIN: Continua expressão ou bloco pertencente a função submitProfileReport.
        $target['nome'], // Explicação DevIN: Continua expressão ou bloco pertencente a função submitProfileReport.
        $reporter['tipo'], // Explicação DevIN: Continua expressão ou bloco pertencente a função submitProfileReport.
        $reporter['id'], // Explicação DevIN: Continua expressão ou bloco pertencente a função submitProfileReport.
        $reporter['nome'], // Explicação DevIN: Continua expressão ou bloco pertencente a função submitProfileReport.
        $reason, // Explicação DevIN: Continua expressão ou bloco pertencente a função submitProfileReport.
        $details // Explicação DevIN: Continua expressão ou bloco pertencente a função submitProfileReport.
    ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $reportId = (int) $conn->insert_id; // Explicação DevIN: Atribui um valor à variável PHP $reportId.
    } catch (mysqli_sql_exception $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        if ((int) $exception->getCode() === 1062) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Você já denunciou este perfil.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        throw $exception; // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função submitProfileReport.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    return ['id' => $reportId, 'nome' => (string) $target['nome']]; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

function decideProfileReport(mysqli $conn, int $reportId, string $verdict, int $adminId): bool // Explicação DevIN: Declara função/método decideProfileReport, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if ($reportId < 1 || !in_array($verdict, ['manter', 'excluir'], true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new InvalidArgumentException('Decisão de moderação inválida.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if ($adminId < 1) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new InvalidArgumentException('Administrador inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    $conn->begin_transaction(); // Explicação DevIN: Continua expressão ou bloco pertencente a função decideProfileReport.
    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        $stmt = $conn->prepare('SELECT * FROM reports_perfis WHERE id = ? FOR UPDATE'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->bind_param('i', $reportId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $report = $stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função decideProfileReport.

        if (!$report || $report['status'] !== 'pendente') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Esta denúncia já foi analisada ou não existe.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if ($verdict === 'excluir') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $table = $report['tipo_denunciado'] === 'empresa' ? 'empresa' : 'pessoa'; // Explicação DevIN: Atribui um valor à variável PHP $table.
            $idColumn = $report['tipo_denunciado'] === 'empresa' ? 'id_empresa' : 'id_pessoa'; // Explicação DevIN: Atribui um valor à variável PHP $idColumn.
            $stmt = $conn->prepare("SELECT {$idColumn} FROM {$table} WHERE {$idColumn} = ? LIMIT 1"); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->bind_param('i', $report['id_denunciado']); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $profileExists = $stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função decideProfileReport.

            if (!$profileExists) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new InvalidArgumentException('O perfil denunciado não está mais disponível.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $stmt = $conn->prepare("DELETE FROM {$table} WHERE {$idColumn} = ?"); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->bind_param('i', $report['id_denunciado']); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função decideProfileReport.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $parecer = $verdict === 'excluir' // Explicação DevIN: Atribui um valor à variável PHP $parecer.
            ? 'O perfil foi removido após análise da denúncia.' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            : 'O perfil foi mantido após análise da denúncia.'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.

        $stmt = $conn->prepare('
            UPDATE reports_perfis
            SET status = "concluida",
                veredito = ?,
                parecer = ?,
                analisado_por = ?,
                analisado_em = NOW()
            WHERE id = ?
        '); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        $stmt->bind_param('ssii', $verdict, $parecer, $adminId, $reportId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função decideProfileReport.

        $reporterMessage = $verdict === 'excluir' // Explicação DevIN: Atribui um valor à variável PHP $reporterMessage.
            ? 'Sua denúncia sobre o perfil "' . $report['nome_denunciado'] . '" foi aceita. O perfil foi removido da plataforma após análise da equipe DevIN.' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            : 'Sua denúncia sobre o perfil "' . $report['nome_denunciado'] . '" foi recusada. Após análise da equipe DevIN, o perfil foi mantido na plataforma.'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        $stmt = $conn->prepare('
            INSERT IGNORE INTO reports_perfis_notificacoes
                (id_report, tipo_destinatario, id_destinatario, mensagem)
            VALUES (?, ?, ?, ?)
        '); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        $stmt->bind_param( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            'isis', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            $reportId, // Explicação DevIN: Continua expressão ou bloco pertencente a função decideProfileReport.
            $report['tipo_denunciante'], // Explicação DevIN: Continua expressão ou bloco pertencente a função decideProfileReport.
            $report['id_denunciante'], // Explicação DevIN: Continua expressão ou bloco pertencente a função decideProfileReport.
            $reporterMessage // Explicação DevIN: Continua expressão ou bloco pertencente a função decideProfileReport.
        ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função decideProfileReport.
        $conn->commit(); // Explicação DevIN: Continua expressão ou bloco pertencente a função decideProfileReport.
    } catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        $conn->rollback(); // Explicação DevIN: Continua expressão ou bloco pertencente a função decideProfileReport.
        throw $exception; // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        return dispatchProfileReportNotifications($conn); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        error_log('Decisão de denúncia de perfil registrada, mas a mensagem ao denunciante falhou: ' . $exception->getMessage()); // Explicação DevIN: Continua expressão ou bloco pertencente a função decideProfileReport.
        return false; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
