<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/controllers/JobReports.php
 * Finalidade: processar denúncias relativas a anúncios.
 * Domínio: processamento no servidor.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


require_once __DIR__ . '/ChatStorage.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/JobSchema.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

// Detalhe DevIN: Método ensureJobReportsSchema: encapsula uma responsabilidade reutilizável do módulo no contexto de processar denúncias relativas a anúncios. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function ensureJobReportsSchema(mysqli $conn): void // Explicação DevIN: Declara função/método ensureJobReportsSchema, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    ensureJobCompensationSchema($conn); // Explicação DevIN: Continua expressão ou bloco pertencente a função ensureJobReportsSchema.

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
    "); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.

    $columns = [ // Explicação DevIN: Atribui um valor à variável PHP $columns.
        'descricao_vaga' => 'ALTER TABLE reports_vagas ADD COLUMN descricao_vaga TEXT NULL', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'salario_mensal' => 'ALTER TABLE reports_vagas ADD COLUMN salario_mensal DECIMAL(10,2) NOT NULL DEFAULT 0.00', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'limite_aprovados' => 'ALTER TABLE reports_vagas ADD COLUMN limite_aprovados INT UNSIGNED NOT NULL DEFAULT 0', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
    // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
    $stmt = $conn->prepare('
        SELECT COLUMN_NAME
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = "reports_vagas"
          AND COLUMN_NAME IN ("descricao_vaga", "salario_mensal", "limite_aprovados")
    '); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
    $existingColumns = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'COLUMN_NAME'); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
    $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função ensureJobReportsSchema.
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
    "); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.

    $conn->query("
        DELETE FROM reports_vagas
        WHERE status <> 'pendente'
          AND analisado_em IS NOT NULL
          AND analisado_em <= DATE_SUB(NOW(), INTERVAL 1 DAY)
    "); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Detalhe DevIN: Método submitJobReport: encapsula uma responsabilidade reutilizável do módulo no contexto de processar denúncias relativas a anúncios. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function submitJobReport(mysqli $conn, array $reporter, int $jobId, string $reason, string $details): array // Explicação DevIN: Declara função/método submitJobReport, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    ensureJobCompensationSchema($conn); // Explicação DevIN: Continua expressão ou bloco pertencente a função submitJobReport.
    $allowedReasons = ['fraude', 'informacao_falsa', 'discriminacao', 'conteudo_inadequado', 'vaga_expirada', 'outro']; // Explicação DevIN: Atribui um valor à variável PHP $allowedReasons.
    if (!in_array($reporter['tipo'] ?? '', ['pessoa', 'empresa'], true) || $jobId < 1) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new InvalidArgumentException('Vaga ou conta inválida.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if (!in_array($reason, $allowedReasons, true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new InvalidArgumentException('Selecione um motivo válido para a denúncia.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if ($details === '' || mb_strlen($details) > 1000) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new InvalidArgumentException('Descreva o motivo em até 1.000 caracteres.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
    $stmt = $conn->prepare('
        SELECT v.titulo, COALESCE(v.descricao, \'\') AS descricao,
               v.salario_mensal, v.limite_aprovados, v.id_empresa, e.nome AS nome_empresa
        FROM vagas v
        INNER JOIN empresa e ON e.id_empresa = v.id_empresa
        WHERE v.id_vaga = ?
        LIMIT 1
    '); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    $stmt->bind_param('i', $jobId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
    $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
    $job = $stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
    $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função submitJobReport.
    if (!$job) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new InvalidArgumentException('Esta vaga não está mais disponível.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if ($reporter['tipo'] === 'empresa' && (int) $job['id_empresa'] === (int) $reporter['id']) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new InvalidArgumentException('Não é possível denunciar uma vaga publicada pela sua própria empresa.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
    $stmt = $conn->prepare('
        INSERT INTO reports_vagas (
            id_vaga, titulo_vaga, descricao_vaga, salario_mensal, limite_aprovados,
            id_empresa, nome_empresa, tipo_denunciante, id_denunciante,
            nome_denunciante, motivo, detalhes, parecer
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, "")
    '); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    $stmt->bind_param( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        'issdiississs', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        $jobId, // Explicação DevIN: Continua expressão ou bloco pertencente a função submitJobReport.
        $job['titulo'], // Explicação DevIN: Continua expressão ou bloco pertencente a função submitJobReport.
        $job['descricao'], // Explicação DevIN: Continua expressão ou bloco pertencente a função submitJobReport.
        $job['salario_mensal'], // Explicação DevIN: Continua expressão ou bloco pertencente a função submitJobReport.
        $job['limite_aprovados'], // Explicação DevIN: Continua expressão ou bloco pertencente a função submitJobReport.
        $job['id_empresa'], // Explicação DevIN: Continua expressão ou bloco pertencente a função submitJobReport.
        $job['nome_empresa'], // Explicação DevIN: Continua expressão ou bloco pertencente a função submitJobReport.
        $reporter['tipo'], // Explicação DevIN: Continua expressão ou bloco pertencente a função submitJobReport.
        $reporter['id'], // Explicação DevIN: Continua expressão ou bloco pertencente a função submitJobReport.
        $reporter['nome'], // Explicação DevIN: Continua expressão ou bloco pertencente a função submitJobReport.
        $reason, // Explicação DevIN: Continua expressão ou bloco pertencente a função submitJobReport.
        $details // Explicação DevIN: Continua expressão ou bloco pertencente a função submitJobReport.
    ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $reportId = (int) $conn->insert_id; // Explicação DevIN: Atribui um valor à variável PHP $reportId.
    } catch (mysqli_sql_exception $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        if ((int) $exception->getCode() === 1062) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Você já denunciou esta vaga.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        throw $exception; // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função submitJobReport.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    return ['id' => $reportId, 'titulo' => (string) $job['titulo']]; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Detalhe DevIN: Método dispatchJobReportNotifications: coordena entrega, limite de uso e tratamento de falhas no contexto de processar denúncias relativas a anúncios. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function dispatchJobReportNotifications(mysqli $conn): bool // Explicação DevIN: Declara função/método dispatchJobReportNotifications, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    $result = $conn->query('
        SELECT id, tipo_destinatario, id_destinatario, mensagem
        FROM reports_vagas_notificacoes
        WHERE entregue_em IS NULL
        ORDER BY id
        LIMIT 100
    '); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    $notifications = $result->fetch_all(MYSQLI_ASSOC); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
    // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
    $stmt = $conn->prepare('UPDATE reports_vagas_notificacoes SET entregue_em = NOW() WHERE id = ? AND entregue_em IS NULL'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
    $allDelivered = true; // Explicação DevIN: Atribui um valor à variável PHP $allDelivered.

    foreach ($notifications as $notification) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
        $notificationId = (int) $notification['id']; // Explicação DevIN: Atribui um valor à variável PHP $notificationId.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            appendPlatformChatNotification( // Explicação DevIN: Continua expressão ou bloco pertencente a função dispatchJobReportNotifications.
                'job-report:' . $notificationId, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                (string) $notification['tipo_destinatario'], // Explicação DevIN: Continua expressão ou bloco pertencente a função dispatchJobReportNotifications.
                (int) $notification['id_destinatario'], // Explicação DevIN: Continua expressão ou bloco pertencente a função dispatchJobReportNotifications.
                (string) $notification['mensagem'] // Explicação DevIN: Continua expressão ou bloco pertencente a função dispatchJobReportNotifications.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $stmt->bind_param('i', $notificationId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        } catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            error_log('Falha ao entregar notificação de denúncia de vaga: ' . $exception->getMessage()); // Explicação DevIN: Continua expressão ou bloco pertencente a função dispatchJobReportNotifications.
            $allDelivered = false; // Explicação DevIN: Atribui um valor à variável PHP $allDelivered.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função dispatchJobReportNotifications.
    return $allDelivered; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Detalhe DevIN: Método decideJobReport: encapsula uma responsabilidade reutilizável do módulo no contexto de processar denúncias relativas a anúncios. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function decideJobReport(mysqli $conn, int $reportId, string $verdict, int $adminId, ?string $customParecer = null): bool // Explicação DevIN: Declara função/método decideJobReport, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if ($reportId < 1 || !in_array($verdict, ['manter', 'excluir'], true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new InvalidArgumentException('Decisão de moderação inválida.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Inicia uma transação para manter alterações relacionadas consistentes.
    $conn->begin_transaction(); // Explicação DevIN: Continua expressão ou bloco pertencente a função decideJobReport.
    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
        $stmt = $conn->prepare('SELECT * FROM reports_vagas WHERE id = ? FOR UPDATE'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->bind_param('i', $reportId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $report = $stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função decideJobReport.
        if (!$report || $report['status'] !== 'pendente') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Esta denúncia já foi analisada ou não existe.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if ($verdict === 'excluir') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
            $stmt = $conn->prepare('DELETE FROM vagas WHERE id_vaga = ?'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->bind_param('i', $report['id_vaga']); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função decideJobReport.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $status = $verdict === 'excluir' ? 'excluida' : 'mantida'; // Explicação DevIN: Atribui um valor à variável PHP $status.
        $parecer = $verdict === 'excluir' // Explicação DevIN: Atribui um valor à variável PHP $parecer.
            ? 'A vaga foi removida após análise da denúncia.' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            : 'A vaga foi mantida após análise da denúncia.';
        if ($customParecer !== null) { // Parecer escrito e aprovado pelo ADM via chat.
            $customParecer = trim($customParecer); // Remove apenas espaços.
            if (mb_strlen($customParecer, 'UTF-8') < 5
                || mb_strlen($customParecer, 'UTF-8') > 3000) {
                throw new InvalidArgumentException('O parecer deve ter entre 5 e 3.000 caracteres.');
            }
            $parecer = $customParecer; // Persiste a sentença revisada, sem modificar o veredito.
        } // Fecha a escolha de parecer.
 // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
        $stmt = $conn->prepare('
            UPDATE reports_vagas
            SET status = ?, parecer = ?, analisado_por = ?, analisado_em = NOW()
            WHERE id = ?
        '); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        $stmt->bind_param('ssii', $status, $parecer, $adminId, $reportId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função decideJobReport.

        $title = (string) $report['titulo_vaga']; // Explicação DevIN: Atribui um valor à variável PHP $title.
        $reporterMessage = $verdict === 'excluir' // Explicação DevIN: Atribui um valor à variável PHP $reporterMessage.
            ? 'Sua denúncia sobre a vaga "' . $title . '" foi aceita. A vaga foi removida da plataforma após análise da equipe DevIN.' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            : 'Sua denúncia sobre a vaga "' . $title . '" foi recusada. Após análise da equipe DevIN, a vaga foi mantida na plataforma.'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        $companyMessage = $verdict === 'excluir' // Explicação DevIN: Atribui um valor à variável PHP $companyMessage.
            ? 'A vaga "' . $title . '" foi removida da DevIN após análise de uma denúncia. O anúncio não está mais disponível na plataforma.' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            : 'A vaga "' . $title . '" foi analisada pela Plataforma DevIN após uma denúncia e permanecerá disponível na plataforma.'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        $notifications = [ // Explicação DevIN: Atribui um valor à variável PHP $notifications.
            [(string) $report['tipo_denunciante'], (int) $report['id_denunciante'], $reporterMessage], // Explicação DevIN: Continua expressão ou bloco pertencente a função decideJobReport.
            ['empresa', (int) $report['id_empresa'], $companyMessage], // Explicação DevIN: Continua expressão ou bloco pertencente a função decideJobReport.
        ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
        // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
        $stmt = $conn->prepare('
            INSERT IGNORE INTO reports_vagas_notificacoes
                (id_report, tipo_destinatario, id_destinatario, mensagem)
            VALUES (?, ?, ?, ?)
        '); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        foreach ($notifications as [$recipientType, $recipientId, $message]) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            $stmt->bind_param('isis', $reportId, $recipientType, $recipientId, $message); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função decideJobReport.

        // Detalhe DevIN: Confirma alterações realizadas no mesmo contexto transacional.
        $conn->commit(); // Explicação DevIN: Continua expressão ou bloco pertencente a função decideJobReport.
    } catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        // Detalhe DevIN: Desfaz alterações ainda não confirmadas quando ocorre falha.
        $conn->rollback(); // Explicação DevIN: Continua expressão ou bloco pertencente a função decideJobReport.
        throw $exception; // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        return dispatchJobReportNotifications($conn); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        error_log('Decisão registrada, mas a entrega das notificações foi adiada: ' . $exception->getMessage()); // Explicação DevIN: Continua expressão ou bloco pertencente a função decideJobReport.
        return false; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
