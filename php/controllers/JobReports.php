<?php

declare(strict_types=1);

require_once __DIR__ . '/ChatStorage.php';
require_once __DIR__ . '/JobSchema.php';

function ensureJobReportsSchema(mysqli $conn): void
{
    ensureJobCompensationSchema($conn);

    $conn->query("
        CREATE TABLE IF NOT EXISTS reports_vagas (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_vaga INT NOT NULL,
            titulo_vaga VARCHAR(255) NOT NULL,
            descricao_vaga TEXT NULL,
            salario_mensal DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            limite_aprovados INT UNSIGNED NOT NULL DEFAULT 0,
            id_empresa INT NOT NULL,
            nome_empresa VARCHAR(255) NOT NULL,
            tipo_denunciante ENUM('pessoa', 'empresa') NOT NULL,
            id_denunciante INT NOT NULL,
            nome_denunciante VARCHAR(255) NOT NULL,
            motivo VARCHAR(40) NOT NULL,
            detalhes TEXT NOT NULL,
            status ENUM('pendente', 'mantida', 'excluida') NOT NULL DEFAULT 'pendente',
            parecer TEXT NOT NULL,
            analisado_por INT NULL,
            criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            analisado_em DATETIME NULL,
            UNIQUE KEY uq_report_vaga_denunciante (id_vaga, tipo_denunciante, id_denunciante),
            INDEX idx_reports_vagas_status (status, criado_em)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $columns = [
        'descricao_vaga' => 'ALTER TABLE reports_vagas ADD COLUMN descricao_vaga TEXT NULL',
        'salario_mensal' => 'ALTER TABLE reports_vagas ADD COLUMN salario_mensal DECIMAL(10,2) NOT NULL DEFAULT 0.00',
        'limite_aprovados' => 'ALTER TABLE reports_vagas ADD COLUMN limite_aprovados INT UNSIGNED NOT NULL DEFAULT 0',
    ];
    $stmt = $conn->prepare('
        SELECT COLUMN_NAME
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = "reports_vagas"
          AND COLUMN_NAME IN ("descricao_vaga", "salario_mensal", "limite_aprovados")
    ');
    $stmt->execute();
    $existingColumns = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'COLUMN_NAME');
    $stmt->close();
    foreach ($columns as $column => $alterSql) {
        if (!in_array($column, $existingColumns, true)) {
            try {
                $conn->query($alterSql);
            } catch (mysqli_sql_exception $exception) {
                if ($exception->getCode() !== 1060) {
                    throw $exception;
                }
            }
        }
    }

    $conn->query("
        CREATE TABLE IF NOT EXISTS reports_vagas_notificacoes (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            id_report BIGINT UNSIGNED NOT NULL,
            tipo_destinatario ENUM('pessoa', 'empresa') NOT NULL,
            id_destinatario INT NOT NULL,
            mensagem TEXT NOT NULL,
            entregue_em DATETIME NULL,
            UNIQUE KEY uq_report_notificacao_destinatario (id_report, tipo_destinatario, id_destinatario),
            INDEX idx_reports_vagas_notificacoes_pendentes (entregue_em)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $conn->query("
        DELETE FROM reports_vagas
        WHERE status <> 'pendente'
          AND analisado_em IS NOT NULL
          AND analisado_em <= DATE_SUB(NOW(), INTERVAL 1 DAY)
    ");
}

function submitJobReport(mysqli $conn, array $reporter, int $jobId, string $reason, string $details): array
{
    ensureJobCompensationSchema($conn);
    $allowedReasons = ['fraude', 'informacao_falsa', 'discriminacao', 'conteudo_inadequado', 'vaga_expirada', 'outro'];
    if (!in_array($reporter['tipo'] ?? '', ['pessoa', 'empresa'], true) || $jobId < 1) {
        throw new InvalidArgumentException('Vaga ou conta inválida.');
    }
    if (!in_array($reason, $allowedReasons, true)) {
        throw new InvalidArgumentException('Selecione um motivo válido para a denúncia.');
    }
    if ($details === '' || mb_strlen($details) > 1000) {
        throw new InvalidArgumentException('Descreva o motivo em até 1.000 caracteres.');
    }

    $stmt = $conn->prepare('
        SELECT v.titulo, COALESCE(v.descricao, \'\') AS descricao,
               v.salario_mensal, v.limite_aprovados, v.id_empresa, e.nome AS nome_empresa
        FROM vagas v
        INNER JOIN empresa e ON e.id_empresa = v.id_empresa
        WHERE v.id_vaga = ?
        LIMIT 1
    ');
    $stmt->bind_param('i', $jobId);
    $stmt->execute();
    $job = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$job) {
        throw new InvalidArgumentException('Esta vaga não está mais disponível.');
    }
    if ($reporter['tipo'] === 'empresa' && (int) $job['id_empresa'] === (int) $reporter['id']) {
        throw new InvalidArgumentException('Não é possível denunciar uma vaga publicada pela sua própria empresa.');
    }

    $stmt = $conn->prepare('
        INSERT INTO reports_vagas (
            id_vaga, titulo_vaga, descricao_vaga, salario_mensal, limite_aprovados,
            id_empresa, nome_empresa, tipo_denunciante, id_denunciante,
            nome_denunciante, motivo, detalhes, parecer
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "")
    ');
    $stmt->bind_param(
        'issdiississs',
        $jobId,
        $job['titulo'],
        $job['descricao'],
        $job['salario_mensal'],
        $job['limite_aprovados'],
        $job['id_empresa'],
        $job['nome_empresa'],
        $reporter['tipo'],
        $reporter['id'],
        $reporter['nome'],
        $reason,
        $details
    );
    try {
        $stmt->execute();
        $reportId = (int) $conn->insert_id;
    } catch (mysqli_sql_exception $exception) {
        if ((int) $exception->getCode() === 1062) {
            throw new InvalidArgumentException('Você já denunciou esta vaga.');
        }
        throw $exception;
    } finally {
        $stmt->close();
    }

    return ['id' => $reportId, 'titulo' => (string) $job['titulo']];
}

function dispatchJobReportNotifications(mysqli $conn): bool
{
    $result = $conn->query('
        SELECT id, tipo_destinatario, id_destinatario, mensagem
        FROM reports_vagas_notificacoes
        WHERE entregue_em IS NULL
        ORDER BY id
        LIMIT 100
    ');
    $notifications = $result->fetch_all(MYSQLI_ASSOC);
    $stmt = $conn->prepare('UPDATE reports_vagas_notificacoes SET entregue_em = NOW() WHERE id = ? AND entregue_em IS NULL');
    $allDelivered = true;

    foreach ($notifications as $notification) {
        $notificationId = (int) $notification['id'];
        try {
            appendPlatformChatNotification(
                'job-report:' . $notificationId,
                (string) $notification['tipo_destinatario'],
                (int) $notification['id_destinatario'],
                (string) $notification['mensagem']
            );
            $stmt->bind_param('i', $notificationId);
            $stmt->execute();
        } catch (Throwable $exception) {
            error_log('Falha ao entregar notificação de denúncia de vaga: ' . $exception->getMessage());
            $allDelivered = false;
        }
    }
    $stmt->close();
    return $allDelivered;
}

function decideJobReport(mysqli $conn, int $reportId, string $verdict, int $adminId): bool
{
    if ($reportId < 1 || !in_array($verdict, ['manter', 'excluir'], true)) {
        throw new InvalidArgumentException('Decisão de moderação inválida.');
    }

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare('SELECT * FROM reports_vagas WHERE id = ? FOR UPDATE');
        $stmt->bind_param('i', $reportId);
        $stmt->execute();
        $report = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$report || $report['status'] !== 'pendente') {
            throw new InvalidArgumentException('Esta denúncia já foi analisada ou não existe.');
        }

        if ($verdict === 'excluir') {
            $stmt = $conn->prepare('DELETE FROM vagas WHERE id_vaga = ?');
            $stmt->bind_param('i', $report['id_vaga']);
            $stmt->execute();
            $stmt->close();
        }

        $status = $verdict === 'excluir' ? 'excluida' : 'mantida';
        $parecer = $verdict === 'excluir'
            ? 'A vaga foi removida após análise da denúncia.'
            : 'A vaga foi mantida após análise da denúncia.';
        $stmt = $conn->prepare('
            UPDATE reports_vagas
            SET status = ?, parecer = ?, analisado_por = ?, analisado_em = NOW()
            WHERE id = ?
        ');
        $stmt->bind_param('ssii', $status, $parecer, $adminId, $reportId);
        $stmt->execute();
        $stmt->close();

        $title = (string) $report['titulo_vaga'];
        $reporterMessage = $verdict === 'excluir'
            ? 'Sua denúncia sobre a vaga "' . $title . '" foi aceita. A vaga foi removida da plataforma após análise da equipe DevIN.'
            : 'Sua denúncia sobre a vaga "' . $title . '" foi recusada. Após análise da equipe DevIN, a vaga foi mantida na plataforma.';
        $companyMessage = $verdict === 'excluir'
            ? 'A vaga "' . $title . '" foi removida da DevIN após análise de uma denúncia. O anúncio não está mais disponível na plataforma.'
            : 'A vaga "' . $title . '" foi analisada pela Plataforma DevIN após uma denúncia e permanecerá disponível na plataforma.';
        $notifications = [
            [(string) $report['tipo_denunciante'], (int) $report['id_denunciante'], $reporterMessage],
            ['empresa', (int) $report['id_empresa'], $companyMessage],
        ];
        $stmt = $conn->prepare('
            INSERT IGNORE INTO reports_vagas_notificacoes
                (id_report, tipo_destinatario, id_destinatario, mensagem)
            VALUES (?, ?, ?, ?)
        ');
        foreach ($notifications as [$recipientType, $recipientId, $message]) {
            $stmt->bind_param('isis', $reportId, $recipientType, $recipientId, $message);
            $stmt->execute();
        }
        $stmt->close();

        $conn->commit();
    } catch (Throwable $exception) {
        $conn->rollback();
        throw $exception;
    }

    try {
        return dispatchJobReportNotifications($conn);
    } catch (Throwable $exception) {
        error_log('Decisão registrada, mas a entrega das notificações foi adiada: ' . $exception->getMessage());
        return false;
    }
}
