<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

require_once __DIR__ . '/PrivacyContext.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

/**
 * Marco Civil: registro de acesso à aplicação não equivale a log de auditoria.
 * Preserva IP recuperável (inet6_aton) quando o art. 15 for aplicável.
 * Não é ativado globalmente nesta fase; configurar middleware na fase 4.
 */
final class AccessLogService // Explicação DevIN: Declara o tipo AccessLogService e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    public static function cleanPath(string $requestUri): string // Explicação DevIN: Declara função/método cleanPath, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $path = parse_url($requestUri, PHP_URL_PATH); // Explicação DevIN: Atribui um valor à variável PHP $path.
        if (!is_string($path) || $path === '') return '/'; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        // Sem query string, cookies, fragmentos nem dados de formulários.
        $path = preg_replace('/[\x00-\x1F\x7F]/', '', $path); // Explicação DevIN: Atribui um valor à variável PHP $path.
        return substr((string) $path, 0, 255) ?: '/'; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function record( // Explicação DevIN: Declara função/método record, com seus parâmetros e comportamento.
        mysqli $db, // Explicação DevIN: Continua expressão ou bloco pertencente a função record.
        string $clientIp, // Explicação DevIN: Continua expressão ou bloco pertencente a função record.
        string $method, // Explicação DevIN: Continua expressão ou bloco pertencente a função record.
        string $requestUri, // Explicação DevIN: Continua expressão ou bloco pertencente a função record.
        int $status, // Explicação DevIN: Continua expressão ou bloco pertencente a função record.
        ?string $actorType = null, // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        ?int $actorId = null // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    ): int { // Explicação DevIN: Continua expressão ou bloco pertencente a função record.
        if (!filter_var($clientIp, FILTER_VALIDATE_IP)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Endereço de acesso inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $method = strtoupper($method); // Explicação DevIN: Atribui um valor à variável PHP $method.
        if (!in_array($method, ['GET','HEAD','POST','PUT','PATCH','DELETE','OPTIONS'], true) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            || $status < 100 || $status > 599) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            throw new InvalidArgumentException('Método ou status inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if ($actorType !== null) PrivacyContext::validateAccount($actorType, $actorId ?? 0); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        elseif ($actorId !== null) throw new InvalidArgumentException('Ator inválido.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

        $path = self::cleanPath($requestUri); // Explicação DevIN: Atribui um valor à variável PHP $path.
        $stmt = $db->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            'INSERT INTO app_access_logs ' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            . '(client_ip,accessed_at,method,path,http_status,actor_type,actor_id,delete_after) ' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            . 'VALUES (INET6_ATON(?),UTC_TIMESTAMP(6),?,?,?,?,?,DATE_ADD(UTC_TIMESTAMP(6),INTERVAL 6 MONTH))' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $stmt->bind_param('sssisi', $clientIp, $method, $path, $status, $actorType, $actorId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $insertId = (int) $db->insert_id; // Explicação DevIN: Atribui um valor à variável PHP $insertId.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função record.
        return $insertId; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /** Usar somente REMOTE_ADDR do servidor, nunca cabeçalhos não confiáveis. */
    public static function recordCurrent(mysqli $db, int $status, ?string $type = null, ?int $id = null): ?int // Explicação DevIN: Declara função/método recordCurrent, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $ip = PrivacyContext::clientIp(); // Explicação DevIN: Atribui um valor à variável PHP $ip.
        if ($ip === null) return null; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        return self::record( // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            $db, $ip, (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'), // Explicação DevIN: Continua expressão ou bloco pertencente a função recordCurrent.
            (string) ($_SERVER['REQUEST_URI'] ?? '/'), $status, $type, $id // Explicação DevIN: Continua expressão ou bloco pertencente a função recordCurrent.
        ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
