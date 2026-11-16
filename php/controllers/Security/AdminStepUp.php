<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

require_once __DIR__ . '/../../config/database.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/../../config/security.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/../../config/RateLimiter.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/AdminToken2Hasher.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

/**
 * Verificação adicional ADM. Nenhuma credencial é exposta ao navegador nem ao modelo de IA.
 * O número do token é apenas um marcador de etapa, não revela sua natureza na interface.
 */
final class AdminStepUp // Explicação DevIN: Declara o tipo AdminStepUp e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    private const MAX_IDLE_SECONDS = 1800; // Explicação DevIN: Define membro, visibilidade ou método de uma classe.
    private const MAX_LIFETIME_SECONDS = 28800; // Explicação DevIN: Define membro, visibilidade ou método de uma classe.
    private const STAGE_TTL_SECONDS = 300; // Explicação DevIN: Define membro, visibilidade ou método de uma classe.

    public static function enabled(): bool // Explicação DevIN: Declara função/método enabled, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $value = $_ENV['DEVIN_ADMIN_STEPUP_ENABLED'] ?? $_SERVER['DEVIN_ADMIN_STEPUP_ENABLED'] ?? getenv('DEVIN_ADMIN_STEPUP_ENABLED'); // Explicação DevIN: Atribui um valor à variável PHP $value.
        return (string) $value === '1'; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /** Chave de sessão pertence ao ADM autenticado, jamais aceita ID fornecido pelo browser. */
    public static function adminId(): int // Explicação DevIN: Declara função/método adminId, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        startSecureSession(); // Explicação DevIN: Continua expressão ou bloco pertencente a função adminId.
        $now = time(); // Explicação DevIN: Atribui um valor à variável PHP $now.
        $id = (int) ($_SESSION['usuario_id'] ?? 0); // Explicação DevIN: Atribui um valor à variável PHP $id.
        $last = (int) ($_SESSION['last_activity'] ?? 0); // Explicação DevIN: Atribui um valor à variável PHP $last.
        $auth = (int) ($_SESSION['auth_time'] ?? 0); // Explicação DevIN: Atribui um valor à variável PHP $auth.
        if (empty($_SESSION['logado']) || ($_SESSION['usuario_tipo'] ?? '') !== 'adm' || $id < 1 // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            || $last <= 0 || $auth <= 0 || $now - $last > 1800 || $now - $auth > 43200) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            throw new DomainException('Sessão não autorizada.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return $id; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /** Retorna apenas revisões/segredos já protegidos por hash armazenados no banco. */
    private static function credentials(mysqli $db, int $id): ?array // Explicação DevIN: Declara função/método credentials, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $stmt = $db->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            'SELECT senha_hash AS password_hash, token2_hash, ' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            . 'token2_version AS credential_version ' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            . 'FROM administrador WHERE id_administrador = ? LIMIT 1' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $stmt->bind_param('i', $id); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $row = $stmt->get_result()->fetch_assoc() ?: null; // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função credentials.
        if (!$row || !is_string($row['password_hash']) || $row['password_hash'] === '') return null; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        return $row; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    private static function revision(array $credentials): string // Explicação DevIN: Declara função/método revision, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return hash('sha256', $credentials['password_hash'] . "\0" // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            . ($credentials['token2_hash'] ?? '') . "\0" // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            . (string) ($credentials['credential_version'] ?? '0')); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    private static function sessionKey(string $scope): string // Explicação DevIN: Declara função/método sessionKey, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!in_array($scope, ['site','ai'], true)) throw new InvalidArgumentException('Escopo inválido.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        return 'admin_stepup_grant_' . $scope; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /** Compara dados atuais do banco, então rotação de credencial revoga imediatamente. */
    public static function allowed(string $scope): bool // Explicação DevIN: Declara função/método allowed, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!self::enabled()) return false; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $id = self::adminId(); // Explicação DevIN: Atribui um valor à variável PHP $id.
        $key = self::sessionKey($scope); // Explicação DevIN: Atribui um valor à variável PHP $key.
        $grant = $_SESSION[$key] ?? null; // Explicação DevIN: Atribui um valor à variável PHP $grant.
        if (!is_array($grant)) return false; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $now = time(); // Explicação DevIN: Atribui um valor à variável PHP $now.
        if ((int) ($grant['admin_id'] ?? 0) !== $id // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            || (int) ($grant['auth_time'] ?? 0) !== (int) ($_SESSION['auth_time'] ?? 0) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            || $now - (int) ($grant['issued_at'] ?? 0) > self::MAX_LIFETIME_SECONDS // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            || $now - (int) ($grant['used_at'] ?? 0) > self::MAX_IDLE_SECONDS) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            unset($_SESSION[$key]); // Explicação DevIN: Continua expressão ou bloco pertencente a função allowed.
            return false; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $db = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $db.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            $credentials = self::credentials($db, $id); // Explicação DevIN: Atribui um valor à variável PHP $credentials.
        } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
            $db->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função allowed.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!$credentials || !$credentials['token2_hash'] // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            || !hash_equals((string) ($grant['revision'] ?? ''), self::revision($credentials))) { // Explicação DevIN: Executa operação criptográfica de senha ou comparação protegida.
            unset($_SESSION[$key]); // Explicação DevIN: Continua expressão ou bloco pertencente a função allowed.
            return false; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $_SESSION[$key]['used_at'] = $now; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        return true; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function requireAllowed(string $scope): void // Explicação DevIN: Declara função/método requireAllowed, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!self::allowed($scope)) throw new DomainException('Verificação adicional necessária.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /**
     * Identificador apenas de etapa; o frontend só mostra o campo designado.
     * Não retorna hash, senha, hash de sessão nem motivo detalhado de falha.
     */
    public static function nextAiToken(): int // Explicação DevIN: Declara função/método nextAiToken, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $id = self::adminId(); // Explicação DevIN: Atribui um valor à variável PHP $id.
        $stage = $_SESSION['admin_stepup_ai_stage'] ?? null; // Explicação DevIN: Atribui um valor à variável PHP $stage.
        if (!is_array($stage) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            || ($stage['admin_id'] ?? null) !== $id // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            || time() - (int) ($stage['at'] ?? 0) > self::STAGE_TTL_SECONDS) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            unset($_SESSION['admin_stepup_ai_stage']); // Explicação DevIN: Continua expressão ou bloco pertencente a função nextAiToken.
            return 1; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $db = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $db.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            $credentials = self::credentials($db, $id); // Explicação DevIN: Atribui um valor à variável PHP $credentials.
        } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
            $db->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função nextAiToken.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!$credentials // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            || !hash_equals((string) ($stage['revision'] ?? ''), self::revision($credentials))) { // Explicação DevIN: Executa operação criptográfica de senha ou comparação protegida.
            unset($_SESSION['admin_stepup_ai_stage']); // Explicação DevIN: Continua expressão ou bloco pertencente a função nextAiToken.
            return 1; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return 2; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    private static function limit(int $id): void // Explicação DevIN: Declara função/método limit, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $ip = RateLimiter::clientIp(); // Explicação DevIN: Atribui um valor à variável PHP $ip.
        // Bloqueio combinado por conta e rede, nunca por token fornecido pelo cliente.
        if (RateLimiter::consume('stepup:admin:' . $id, 8, 900) > 0 // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            || RateLimiter::consume('stepup:ip:' . $ip, 24, 900) > 0) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            throw new RuntimeException('Limite de tentativas excedido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /** Usado apenas no desbloqueio do site, sem passar pelo modelo externo. */
    public static function verifySite(string $token): bool // Explicação DevIN: Declara função/método verifySite, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $id = self::adminId(); // Explicação DevIN: Atribui um valor à variável PHP $id.
        self::limit($id); // Explicação DevIN: Continua expressão ou bloco pertencente a função verifySite.
        if ($token === '' || strlen($token) > AdminToken2Hasher::MAX_INPUT_BYTES) return false; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $db = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $db.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            $credentials = self::credentials($db, $id); // Explicação DevIN: Atribui um valor à variável PHP $credentials.
        } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
            $db->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função verifySite.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!$credentials || !is_string($credentials['token2_hash']) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            || $credentials['token2_hash'] === '' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            || !AdminToken2Hasher::verify($token, $credentials['token2_hash'])) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            self::auditStep('admin_stepup_site', 'failure', $id); // Explicação DevIN: Continua expressão ou bloco pertencente a função verifySite.
            return false; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        self::grant('site', $id, $credentials); // Explicação DevIN: Continua expressão ou bloco pertencente a função verifySite.
        RateLimiter::clear('stepup:admin:' . $id); // Explicação DevIN: Continua expressão ou bloco pertencente a função verifySite.
        self::auditStep('admin_stepup_site', 'success', $id); // Explicação DevIN: Continua expressão ou bloco pertencente a função verifySite.
        return true; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /** Somente Token 1 pode criar o estágio intermediário da IA. */
    public static function verifyAiFirst(string $token): bool // Explicação DevIN: Declara função/método verifyAiFirst, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $id = self::adminId(); // Explicação DevIN: Atribui um valor à variável PHP $id.
        self::limit($id); // Explicação DevIN: Continua expressão ou bloco pertencente a função verifyAiFirst.
        unset($_SESSION['admin_stepup_ai_stage']); // Explicação DevIN: Continua expressão ou bloco pertencente a função verifyAiFirst.
        if ($token === '' || strlen($token) > 512) return false; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $db = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $db.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            $credentials = self::credentials($db, $id); // Explicação DevIN: Atribui um valor à variável PHP $credentials.
        } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
            $db->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função verifyAiFirst.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!$credentials || !$credentials['token2_hash'] // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            || !password_verify($token, $credentials['password_hash'])) { // Explicação DevIN: Executa operação criptográfica de senha ou comparação protegida.
            self::auditStep('admin_stepup_ai_stage1', 'failure', $id); // Explicação DevIN: Continua expressão ou bloco pertencente a função verifyAiFirst.
            return false; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        RateLimiter::clear('stepup:admin:' . $id); // Explicação DevIN: Continua expressão ou bloco pertencente a função verifyAiFirst.
        self::auditStep('admin_stepup_ai_stage1', 'success', $id); // Explicação DevIN: Continua expressão ou bloco pertencente a função verifyAiFirst.
        $_SESSION['admin_stepup_ai_stage'] = [ // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            'admin_id' => $id, 'at' => time(), 'revision' => self::revision($credentials), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
        return true; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /** Token 2 somente é aceito após estágio 1 válido e ainda vigente. */
    public static function verifyAiSecond(string $token): bool // Explicação DevIN: Declara função/método verifyAiSecond, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (self::nextAiToken() !== 2) return false; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $id = self::adminId(); // Explicação DevIN: Atribui um valor à variável PHP $id.
        self::limit($id); // Explicação DevIN: Continua expressão ou bloco pertencente a função verifyAiSecond.
        if ($token === '' || strlen($token) > AdminToken2Hasher::MAX_INPUT_BYTES) return false; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $db = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $db.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            $credentials = self::credentials($db, $id); // Explicação DevIN: Atribui um valor à variável PHP $credentials.
        } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
            $db->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função verifyAiSecond.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!$credentials || !$credentials['token2_hash'] // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            || !AdminToken2Hasher::verify($token, $credentials['token2_hash'])) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            self::auditStep('admin_stepup_ai_complete', 'failure', $id); // Explicação DevIN: Continua expressão ou bloco pertencente a função verifyAiSecond.
            return false; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        unset($_SESSION['admin_stepup_ai_stage']); // Explicação DevIN: Continua expressão ou bloco pertencente a função verifyAiSecond.
        self::grant('ai', $id, $credentials); // Explicação DevIN: Continua expressão ou bloco pertencente a função verifyAiSecond.
        RateLimiter::clear('stepup:admin:' . $id); // Explicação DevIN: Continua expressão ou bloco pertencente a função verifyAiSecond.
        self::auditStep('admin_stepup_ai_complete', 'success', $id); // Explicação DevIN: Continua expressão ou bloco pertencente a função verifyAiSecond.
        return true; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    private static function auditStep(string $event, string $outcome, int $id): void // Explicação DevIN: Declara função/método auditStep, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        require_once __DIR__ . '/../Privacy/AuditService.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
        AuditService::tryRecord($event, $outcome, 'adm', $id); // Explicação DevIN: Continua expressão ou bloco pertencente a função auditStep.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    private static function grant(string $scope, int $id, array $credentials): void // Explicação DevIN: Declara função/método grant, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $key = self::sessionKey($scope); // Explicação DevIN: Atribui um valor à variável PHP $key.
        // Não guarda os tokens; troca o identificador PHP após elevação de privilégio.
        secureSessionRegenerate(); // Explicação DevIN: Continua expressão ou bloco pertencente a função grant.
        $_SESSION[$key] = [ // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            'admin_id' => $id, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'auth_time' => (int) ($_SESSION['auth_time'] ?? 0), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'revision' => self::revision($credentials), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'issued_at' => time(), 'used_at' => time(), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /** Invocar após troca de credenciais ou suspeita de sessão comprometida. */
    public static function clear(): void // Explicação DevIN: Declara função/método clear, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        startSecureSession(); // Explicação DevIN: Continua expressão ou bloco pertencente a função clear.
        unset($_SESSION['admin_stepup_grant_site'], $_SESSION['admin_stepup_grant_ai'], $_SESSION['admin_stepup_ai_stage']); // Explicação DevIN: Continua expressão ou bloco pertencente a função clear.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
