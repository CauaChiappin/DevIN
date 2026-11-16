<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

/**
 * Helper puro: nunca confia em X-Forwarded-For, cookies ou ids de cliente.
 * A chave HMAC é separada da senha JWT e não pode aparecer no banco ou no Git.
 */
final class PrivacyContext // Explicação DevIN: Declara o tipo PrivacyContext e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    private const ACCOUNT_TABLES = [ // Explicação DevIN: Define membro, visibilidade ou método de uma classe.
        'pessoa' => ['pessoa', 'id_pessoa'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'empresa' => ['empresa', 'id_empresa'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'adm' => ['administrador', 'id_administrador'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
    private const PURPOSES = ['marketing_email','analytics_cookies','ai_resume_transfer','ai_admin_history']; // Explicação DevIN: Define membro, visibilidade ou método de uma classe.

    public static function env(string $name): string // Explicação DevIN: Declara função/método env, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return trim((string) ($_ENV[$name] ?? $_SERVER[$name] ?? getenv($name) ?: '')); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function validateAccount(string $type, int $id): void // Explicação DevIN: Declara função/método validateAccount, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!array_key_exists($type, self::ACCOUNT_TABLES) || $id < 1) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Conta inválida.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function requireAccount(mysqli $db, string $type, int $id, bool $lock = false): void // Explicação DevIN: Declara função/método requireAccount, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        self::validateAccount($type, $id); // Explicação DevIN: Continua expressão ou bloco pertencente a função requireAccount.
        [$table, $key] = self::ACCOUNT_TABLES[$type]; // Explicação DevIN: Continua expressão ou bloco pertencente a função requireAccount.
        $stmt = $db->prepare("SELECT {$key} FROM {$table} WHERE {$key} = ? LIMIT 1" . ($lock ? ' FOR UPDATE' : '')); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->bind_param('i', $id); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $exists = $stmt->get_result()->fetch_row() !== null; // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função requireAccount.
        if (!$exists) throw new InvalidArgumentException('Conta não encontrada.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function validatePurpose(string $purpose): void // Explicação DevIN: Declara função/método validatePurpose, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!in_array($purpose, self::PURPOSES, true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Finalidade não autorizada.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function clientIp(?array $server = null): ?string // Explicação DevIN: Declara função/método clientIp, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $source = $server ?? $_SERVER; // Explicação DevIN: Atribui um valor à variável PHP $source.
        $ip = $source['REMOTE_ADDR'] ?? null; // Explicação DevIN: Atribui um valor à variável PHP $ip.
        return is_string($ip) && filter_var($ip, FILTER_VALIDATE_IP) ? $ip : null; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function ipHash(?string $ip, ?string $overrideKey = null): ?string // Explicação DevIN: Declara função/método ipHash, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if ($ip === null || !filter_var($ip, FILTER_VALIDATE_IP)) return null; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $key = $overrideKey ?? self::env('DEVIN_LGPD_IP_HASH_KEY'); // Explicação DevIN: Atribui um valor à variável PHP $key.
        if (strlen($key) < 32) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new RuntimeException('Chave HMAC de privacidade ausente ou fraca.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // inet_pton normaliza IPv4 e IPv6. HMAC evita hash público reversível por dicionário.
        $packed = inet_pton($ip); // Explicação DevIN: Atribui um valor à variável PHP $packed.
        if ($packed === false) return null; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        return hash_hmac('sha256', $packed, $key); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function userAgent(?string $value = null): ?string // Explicação DevIN: Declara função/método userAgent, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $value = $value ?? ($_SERVER['HTTP_USER_AGENT'] ?? null); // Explicação DevIN: Atribui um valor à variável PHP $value.
        if (!is_string($value) || trim($value) === '') return null; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        // Remove caracteres de controle e limita a largura para a coluna de auditoria.
        $safe = preg_replace('/[\x00-\x1F\x7F]/', '', $value); // Explicação DevIN: Atribui um valor à variável PHP $safe.
        return substr((string) $safe, 0, 512); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function requestId(): string // Explicação DevIN: Declara função/método requestId, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $hex = bin2hex(random_bytes(16)); // Explicação DevIN: Atribui um valor à variável PHP $hex.
        return substr($hex,0,8) . '-' . substr($hex,8,4) . '-' . substr($hex,12,4) // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            . '-' . substr($hex,16,4) . '-' . substr($hex,20,12); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
