<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

require_once __DIR__ . '/PrivacyContext.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

/**
 * Registra recibo DEPOIS da execução real (fase 3).
 * Nunca é um mecanismo para apagar perfis, arquivos ou histórico.
 */
final class ErasureReceiptService // Explicação DevIN: Declara o tipo ErasureReceiptService e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    private const OUTCOMES = ['deleted','anonymized','partially_retained']; // Explicação DevIN: Define membro, visibilidade ou método de uma classe.

    /** @param list<string> $retainedCategories */
    public static function record( // Explicação DevIN: Declara função/método record, com seus parâmetros e comportamento.
        mysqli $db, int $adminId, int $requestId, string $outcome, // Explicação DevIN: Continua expressão ou bloco pertencente a função record.
        array $retainedCategories = [], ?string $basisCode = null // Explicação DevIN: Continua expressão ou bloco pertencente a função record.
    ): string { // Explicação DevIN: Continua expressão ou bloco pertencente a função record.
        PrivacyContext::validateAccount('adm', $adminId); // Explicação DevIN: Continua expressão ou bloco pertencente a função record.
        if ($requestId < 1 || !in_array($outcome, self::OUTCOMES, true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Resultado de eliminação inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if ($outcome === 'partially_retained' && (!$retainedCategories || !$basisCode)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Retenção parcial exige categoria e fundamento.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if ($basisCode !== null && preg_match('/^[a-z0-9_:-]{1,80}$/D', $basisCode) !== 1) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Fundamento inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $allowed = ['audit','security','legal_hold','tax','complaints','contracts','backups']; // Explicação DevIN: Atribui um valor à variável PHP $allowed.
        if (count($retainedCategories) > count($allowed) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            || array_diff($retainedCategories, $allowed) !== []) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            throw new InvalidArgumentException('Categorias de retenção inválidas.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $db->begin_transaction(); // Explicação DevIN: Continua expressão ou bloco pertencente a função record.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            PrivacyContext::requireAccount($db, 'adm', $adminId, true); // Explicação DevIN: Continua expressão ou bloco pertencente a função record.
            $stmt = $db->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                'SELECT request_type,status FROM privacy_requests WHERE id = ? LIMIT 1 FOR UPDATE' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $stmt->bind_param('i', $requestId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $request = $stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função record.
            if ($request === null || !in_array($request['request_type'], ['erasure','anonymization'], true) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                || $request['status'] !== 'completed') { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                throw new DomainException('Recibo permitido apenas após tratamento efetivamente concluído.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $ref = bin2hex(random_bytes(16)); // Explicação DevIN: Atribui um valor à variável PHP $ref.
            $categories = $retainedCategories ? json_encode(array_values(array_unique($retainedCategories)), JSON_THROW_ON_ERROR) : null; // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
            $stmt = $db->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                'INSERT INTO privacy_erasure_receipts ' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                . '(request_id,receipt_reference,outcome,retained_categories_json,legal_retention_basis,processed_at) ' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                . 'VALUES (?,?,?,?,?,UTC_TIMESTAMP(6))' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $stmt->bind_param('issss', $requestId, $ref, $outcome, $categories, $basisCode); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função record.
            $db->commit(); // Explicação DevIN: Continua expressão ou bloco pertencente a função record.
            return $ref; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            $db->rollback(); // Explicação DevIN: Continua expressão ou bloco pertencente a função record.
            throw $exception; // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
