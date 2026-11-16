<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

require_once __DIR__ . '/PrivacyContext.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

/** Registro de incidentes: retenção de pelo menos cinco anos, com acesso restrito a ADMs. */
final class SecurityIncidentService // Explicação DevIN: Declara o tipo SecurityIncidentService e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    public static function register( // Explicação DevIN: Declara função/método register, com seus parâmetros e comportamento.
        mysqli $db, int $adminId, string $discoveredUtc, string $summary, // Explicação DevIN: Continua expressão ou bloco pertencente a função register.
        array $categories, ?int $affectedCount = null // Explicação DevIN: Continua expressão ou bloco pertencente a função register.
    ): string { // Explicação DevIN: Continua expressão ou bloco pertencente a função register.
        PrivacyContext::validateAccount('adm', $adminId); // Explicação DevIN: Continua expressão ou bloco pertencente a função register.
        if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/D', $discoveredUtc) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            || strlen(trim($summary)) < 15 || strlen($summary) > 2000 // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            || ($affectedCount !== null && $affectedCount < 0)) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            throw new InvalidArgumentException('Dados de incidente inválidos.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $allowed = ['identification','contact','credentials','curriculum','applications','messages','technical','other']; // Explicação DevIN: Atribui um valor à variável PHP $allowed.
        if (count($categories) > 8 || array_diff($categories, $allowed) !== []) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Categoria de dados inválida.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        PrivacyContext::requireAccount($db, 'adm', $adminId); // Explicação DevIN: Continua expressão ou bloco pertencente a função register.
        // Texto de resumo pode conter dado pessoal: validar e revisar antes da inserção.
        $reference = bin2hex(random_bytes(16)); // Explicação DevIN: Atribui um valor à variável PHP $reference.
        $json = json_encode(array_values(array_unique($categories)), JSON_THROW_ON_ERROR); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
        $stmt = $db->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            'INSERT INTO privacy_security_incidents ' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            . '(reference_code,discovered_at,risk_level,affected_data_categories_json,affected_subjects_count,' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            . 'incident_summary,recorded_at,retain_until) ' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            . "VALUES (?,?,'under_review',?,?,?,UTC_TIMESTAMP(6),DATE_ADD(UTC_TIMESTAMP(6),INTERVAL 5 YEAR))" // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $stmt->bind_param('sssis', $reference, $discoveredUtc, $json, $affectedCount, $summary); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função register.
        return $reference; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
