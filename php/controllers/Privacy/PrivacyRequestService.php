<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

require_once __DIR__ . '/PrivacyContext.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

/** Fase 1: protocolo de solicitação e acompanhamento; endpoints do titular na Fase 3. */
final class PrivacyRequestService // Explicação DevIN: Declara o tipo PrivacyRequestService e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    private const TYPES = [ // Explicação DevIN: Define membro, visibilidade ou método de uma classe.
        'access','portability','rectification','erasure','anonymization', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'objection','consent_withdrawal','other' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.

    /** Cria para conta autenticada após validação da sessão pelo controlador. */
    public static function create(mysqli $db, string $accountType, int $accountId, string $requestType): string // Explicação DevIN: Declara função/método create, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        PrivacyContext::validateAccount($accountType, $accountId); // Explicação DevIN: Continua expressão ou bloco pertencente a função create.
        if (!in_array($requestType, self::TYPES, true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Direito solicitado inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        PrivacyContext::requireAccount($db, $accountType, $accountId); // Explicação DevIN: Continua expressão ou bloco pertencente a função create.
        $reference = bin2hex(random_bytes(16)); // Explicação DevIN: Atribui um valor à variável PHP $reference.
        $stmt = $db->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            'INSERT INTO privacy_requests ' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            . '(public_reference,account_type,account_id,request_type,status,requested_at) ' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            . "VALUES (?,?,?,?,'received',UTC_TIMESTAMP(6))" // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $stmt->bind_param('ssis', $reference, $accountType, $accountId, $requestType); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função create.
        return $reference; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /** Uma pessoa/empresa nunca obtém solicitações de outras contas por ID. */
    public static function listOwn(mysqli $db, string $accountType, int $accountId): array // Explicação DevIN: Declara função/método listOwn, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        PrivacyContext::validateAccount($accountType, $accountId); // Explicação DevIN: Continua expressão ou bloco pertencente a função listOwn.
        $stmt = $db->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            'SELECT public_reference,request_type,status,requested_at,resolved_at ' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            . 'FROM privacy_requests WHERE account_type = ? AND account_id = ? ' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            . 'ORDER BY id DESC LIMIT 50' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $stmt->bind_param('si', $accountType, $accountId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função listOwn.
        return $rows; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /**
     * Revisão exclusivamente por admin autenticado; transições irreversíveis
     * exigem justificativa por CÓDIGO controlado, sem texto pessoal no log.
     * A marca 'completed' atesta conclusão do processo externo, NÃO executa eliminação.
     */
    public static function updateStatus( // Explicação DevIN: Declara função/método updateStatus, com seus parâmetros e comportamento.
        mysqli $db, int $adminId, int $requestId, string $next, ?string $basisCode = null // Explicação DevIN: Continua expressão ou bloco pertencente a função updateStatus.
    ): void { // Explicação DevIN: Continua expressão ou bloco pertencente a função updateStatus.
        PrivacyContext::validateAccount('adm', $adminId); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateStatus.
        if ($requestId < 1) throw new InvalidArgumentException('Solicitação inválida.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $allowed = [ // Explicação DevIN: Atribui um valor à variável PHP $allowed.
            'received' => ['verifying_identity'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'verifying_identity' => ['under_review'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'under_review' => ['completed','denied'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
        if ($basisCode !== null && preg_match('/^[a-z0-9_:-]{1,80}$/D', $basisCode) !== 1) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Código de justificativa inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (in_array($next, ['completed','denied'], true) && !$basisCode) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Justificativa codificada obrigatória.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $db->begin_transaction(); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateStatus.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            PrivacyContext::requireAccount($db, 'adm', $adminId, true); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateStatus.
            $stmt = $db->prepare('SELECT status FROM privacy_requests WHERE id = ? LIMIT 1 FOR UPDATE'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->bind_param('i', $requestId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $row = $stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateStatus.
            if ($row === null || !in_array($next, $allowed[$row['status']] ?? [], true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new DomainException('Transição de solicitação não autorizada.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $stmt = $db->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                'UPDATE privacy_requests SET status = ?, ' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                . "identity_verified_at = IF(? = 'under_review',UTC_TIMESTAMP(6),identity_verified_at), " // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                . "resolved_at = IF(? IN ('completed','denied'),UTC_TIMESTAMP(6),resolved_at), " // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                . 'decision_basis = ? WHERE id = ?' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $stmt->bind_param('ssssi', $next, $next, $next, $basisCode, $requestId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateStatus.
            $db->commit(); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateStatus.
        } catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            $db->rollback(); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateStatus.
            throw $exception; // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
