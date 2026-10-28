<?php

declare(strict_types=1);

function ensureProfileReportsSchema(mysqli $conn): void
{
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
    ");

    $columns = [
        'veredito' => 'ALTER TABLE reports_perfis ADD COLUMN veredito ENUM("manter", "excluir") NULL',
        'parecer' => 'ALTER TABLE reports_perfis ADD COLUMN parecer TEXT NOT NULL DEFAULT ""',
    ];
    $stmt = $conn->prepare('
        SELECT COLUMN_NAME
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = "reports_perfis"
          AND COLUMN_NAME IN ("veredito", "parecer")
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
        DELETE FROM reports_perfis
        WHERE status = 'concluida'
          AND analisado_em IS NOT NULL
          AND analisado_em <= DATE_SUB(NOW(), INTERVAL 1 DAY)
    ");
}

function submitProfileReport(
    mysqli $conn,
    array $reporter,
    string $targetType,
    int $targetId,
    string $reason,
    string $details
): void {
    $allowedReasons = ['assedio', 'fraude', 'falsidade', 'conteudo_inadequado', 'outro'];
    if (
        !in_array($reporter['tipo'] ?? '', ['pessoa', 'empresa'], true)
        || !in_array($targetType, ['pessoa', 'empresa'], true)
        || $targetId < 1
    ) {
        throw new InvalidArgumentException('Perfil inválido para denúncia.');
    }
    if ($reporter['tipo'] === $targetType && (int) $reporter['id'] === $targetId) {
        throw new InvalidArgumentException('Não é possível denunciar seu próprio perfil.');
    }
    if (!in_array($reason, $allowedReasons, true)) {
        throw new InvalidArgumentException('Selecione um motivo válido para a denúncia.');
    }
    if ($details === '' || mb_strlen($details) > 1000) {
        throw new InvalidArgumentException('Descreva o motivo em até 1.000 caracteres.');
    }

    [$table, $idColumn] = $targetType === 'pessoa'
        ? ['pessoa', 'id_pessoa']
        : ['empresa', 'id_empresa'];
    $stmt = $conn->prepare("SELECT nome FROM {$table} WHERE {$idColumn} = ? LIMIT 1");
    $stmt->bind_param('i', $targetId);
    $stmt->execute();
    $target = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$target) {
        throw new InvalidArgumentException('Este perfil não está mais disponível.');
    }

    $stmt = $conn->prepare('
        INSERT INTO reports_perfis (
            tipo_denunciado, id_denunciado, nome_denunciado,
            tipo_denunciante, id_denunciante, nome_denunciante,
            motivo, detalhes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->bind_param(
        'sississs',
        $targetType,
        $targetId,
        $target['nome'],
        $reporter['tipo'],
        $reporter['id'],
        $reporter['nome'],
        $reason,
        $details
    );
    try {
        $stmt->execute();
    } catch (mysqli_sql_exception $exception) {
        if ((int) $exception->getCode() === 1062) {
            throw new InvalidArgumentException('Você já denunciou este perfil.');
        }
        throw $exception;
    } finally {
        $stmt->close();
    }
}

function decideProfileReport(mysqli $conn, int $reportId, string $verdict, int $adminId): void
{
    if ($reportId < 1 || !in_array($verdict, ['manter', 'excluir'], true)) {
        throw new InvalidArgumentException('Decisão de moderação inválida.');
    }
    if ($adminId < 1) {
        throw new InvalidArgumentException('Administrador inválido.');
    }

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare('SELECT * FROM reports_perfis WHERE id = ? FOR UPDATE');
        $stmt->bind_param('i', $reportId);
        $stmt->execute();
        $report = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$report || $report['status'] !== 'pendente') {
            throw new InvalidArgumentException('Esta denúncia já foi analisada ou não existe.');
        }

        if ($verdict === 'excluir') {
            $table = $report['tipo_denunciado'] === 'empresa' ? 'empresa' : 'pessoa';
            $idColumn = $report['tipo_denunciado'] === 'empresa' ? 'id_empresa' : 'id_pessoa';
            $stmt = $conn->prepare("SELECT {$idColumn} FROM {$table} WHERE {$idColumn} = ? LIMIT 1");
            $stmt->bind_param('i', $report['id_denunciado']);
            $stmt->execute();
            $profileExists = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$profileExists) {
                throw new InvalidArgumentException('O perfil denunciado não está mais disponível.');
            }

            $stmt = $conn->prepare("DELETE FROM {$table} WHERE {$idColumn} = ?");
            $stmt->bind_param('i', $report['id_denunciado']);
            $stmt->execute();
            $stmt->close();
        }

        $parecer = $verdict === 'excluir'
            ? 'O perfil foi removido após análise da denúncia.'
            : 'O perfil foi mantido após análise da denúncia.';

        $stmt = $conn->prepare('
            UPDATE reports_perfis
            SET status = "concluida",
                veredito = ?,
                parecer = ?,
                analisado_por = ?,
                analisado_em = NOW()
            WHERE id = ?
        ');
        $stmt->bind_param('ssii', $verdict, $parecer, $adminId, $reportId);
        $stmt->execute();
        $stmt->close();
        $conn->commit();
    } catch (Throwable $exception) {
        $conn->rollback();
        throw $exception;
    }
}
