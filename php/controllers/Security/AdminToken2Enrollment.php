<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

require_once __DIR__ . '/AdminStepUp.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/AdminToken2Hasher.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

/**
 * Token 2 permanente, cadastrado SOMENTE pelo próprio ADM autenticado.
 * O primeiro cadastro exige Token 1; toda troca exige Token 1 + Token 2 atual.
 * Não são usados códigos temporários ou dados de terceiros fornecidos no POST.
 */
final class AdminToken2Enrollment // Explicação DevIN: Declara o tipo AdminToken2Enrollment e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    /**
     * Libera o cadastro somente para uma sessão ADM válida cuja conta ainda
     * existe na tabela administrativa. Não confiar apenas em usuario_tipo na
     * sessão nem aceitar IDs informados por GET ou POST.
     */
    public static function requireVerifiedAdminAccount(): int // Explicação DevIN: Declara função/método requireVerifiedAdminAccount, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $id = AdminStepUp::adminId(); // verifica login, tipo e validade da sessão
        $db = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $db.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            $stmt = $db->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                'SELECT senha_hash FROM administrador WHERE id_administrador = ? LIMIT 1' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $stmt->bind_param('i', $id); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $account = $stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função requireVerifiedAdminAccount.
        } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
            $db->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função requireVerifiedAdminAccount.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!is_array($account) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            || !is_string($account['senha_hash'] ?? null) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            || $account['senha_hash'] === '') { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            throw new DomainException('Conta administrativa não autorizada.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return $id; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function configured(): bool // Explicação DevIN: Declara função/método configured, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $id = AdminStepUp::adminId(); // Explicação DevIN: Atribui um valor à variável PHP $id.
        $db = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $db.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            $stmt = $db->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                'SELECT token2_hash FROM administrador WHERE id_administrador = ? LIMIT 1' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $stmt->bind_param('i', $id); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $row = $stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função configured.
            return is_array($row) && is_string($row['token2_hash']) // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
                && $row['token2_hash'] !== ''; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
            $db->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função configured.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    private static function limit(int $id): void // Explicação DevIN: Declara função/método limit, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $ip = RateLimiter::clientIp(); // Explicação DevIN: Atribui um valor à variável PHP $ip.
        if (RateLimiter::consume('stepup:admin:' . $id, 8, 900) > 0 // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            || RateLimiter::consume('stepup:ip:' . $ip, 24, 900) > 0 // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            || RateLimiter::consume('stepup:enroll:' . $id, 6, 900) > 0) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            throw new RuntimeException('Limite de tentativas excedido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function validateNewToken(string $newToken2, string $confirmation): void // Explicação DevIN: Declara função/método validateNewToken, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // Sem limite ARBITRÁRIO de caracteres. Teto técnico 64 KiB evita negação
        // de serviço por formulários imensos; a interface não impõe maxlength.
        if ($newToken2 !== $confirmation || $newToken2 === '' // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            || strlen($newToken2) > AdminToken2Hasher::MAX_INPUT_BYTES // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            || preg_match('//u', $newToken2) !== 1 // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            || preg_match('/[\x00-\x1F\x7F]/', $newToken2) === 1 // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            || preg_match_all('/./us', $newToken2) < 12) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            throw new InvalidArgumentException('Token 2 inválido ou confirmação diferente.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /**
     * true: cadastrado ou alterado; false: verificação recusada.
     * Dados sensíveis nunca são gravados em logs nem devolvidos ao chamador.
     */
    public static function save( // Explicação DevIN: Declara função/método save, com seus parâmetros e comportamento.
        string $token1, ?string $currentToken2, // Explicação DevIN: Continua expressão ou bloco pertencente a função save.
        string $newToken2, string $confirmation // Explicação DevIN: Continua expressão ou bloco pertencente a função save.
    ): bool { // Explicação DevIN: Continua expressão ou bloco pertencente a função save.
        $id = self::requireVerifiedAdminAccount(); // Explicação DevIN: Atribui um valor à variável PHP $id.
        self::validateNewToken($newToken2, $confirmation); // Explicação DevIN: Continua expressão ou bloco pertencente a função save.
        if ($token1 === '' || strlen($token1) > 512) return false; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        self::limit($id); // Explicação DevIN: Continua expressão ou bloco pertencente a função save.

        $db = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $db.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            $db->begin_transaction(); // Explicação DevIN: Continua expressão ou bloco pertencente a função save.
            $stmt = $db->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                'SELECT senha_hash, token2_hash FROM administrador ' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                . 'WHERE id_administrador = ? LIMIT 1 FOR UPDATE' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $stmt->bind_param('i', $id); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $account = $stmt->get_result()->fetch_assoc() ?: null; // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função save.

            if (!$account || !is_string($account['senha_hash']) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                || !password_verify($token1, $account['senha_hash']) // Explicação DevIN: Executa operação criptográfica de senha ou comparação protegida.
                || password_verify($newToken2, $account['senha_hash'])) { // Explicação DevIN: Executa operação criptográfica de senha ou comparação protegida.
                $db->rollback(); // Explicação DevIN: Continua expressão ou bloco pertencente a função save.
                return false; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $hasPrevious = is_string($account['token2_hash']) // Explicação DevIN: Atribui um valor à variável PHP $hasPrevious.
                && $account['token2_hash'] !== ''; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            if ($hasPrevious && ( // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                $currentToken2 === null // Explicação DevIN: Atribui um valor à variável PHP $currentToken2.
                || !AdminToken2Hasher::verify($currentToken2, $account['token2_hash']) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                || AdminToken2Hasher::verify($newToken2, $account['token2_hash']) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            )) { // Explicação DevIN: Continua expressão ou bloco pertencente a função save.
                $db->rollback(); // Explicação DevIN: Continua expressão ou bloco pertencente a função save.
                return false; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            // Poucos ADMs: impedir reutilização da mesma chave por outro ADM.
            $stmt = $db->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                'SELECT token2_hash FROM administrador WHERE id_administrador <> ? ' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                . 'AND token2_hash IS NOT NULL' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $stmt->bind_param('i', $id); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $others = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função save.
            foreach ($others as $other) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                if (AdminToken2Hasher::verify($newToken2, $other['token2_hash'])) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    $db->rollback(); // Explicação DevIN: Continua expressão ou bloco pertencente a função save.
                    return false; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $hash = AdminToken2Hasher::create($newToken2); // Explicação DevIN: Atribui um valor à variável PHP $hash.
            $stmt = $db->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                'UPDATE administrador SET token2_hash = ?, ' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                . 'token2_version = token2_version + 1, ' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                . 'token2_configured_at = UTC_TIMESTAMP(6) ' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                . 'WHERE id_administrador = ?' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $stmt->bind_param('si', $hash, $id); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função save.
            $db->commit(); // Explicação DevIN: Continua expressão ou bloco pertencente a função save.

            // As autorizações de site e IA desta sessão perdem validade agora;
            // outras sessões serão invalidadas pela mudança de versão/hash.
            AdminStepUp::clear(); // Explicação DevIN: Continua expressão ou bloco pertencente a função save.
            RateLimiter::clear('stepup:admin:' . $id); // Explicação DevIN: Continua expressão ou bloco pertencente a função save.
            RateLimiter::clear('stepup:enroll:' . $id); // Explicação DevIN: Continua expressão ou bloco pertencente a função save.
            require_once __DIR__ . '/../Privacy/AuditService.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
            AuditService::tryRecord('admin_token2_enrolled', 'success', 'adm', $id); // Explicação DevIN: Continua expressão ou bloco pertencente a função save.
            return true; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            $db->rollback(); // Explicação DevIN: Continua expressão ou bloco pertencente a função save.
            throw $exception; // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
            $db->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função save.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
