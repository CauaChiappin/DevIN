<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

/** Fluxo de exclusão supervisionado por ADM, nunca pelo modelo externo. */
final class JarvisModeration // Explicação DevIN: Declara o tipo JarvisModeration e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    private const MAP = [ // Explicação DevIN: Define membro, visibilidade ou método de uma classe.
        'vaga' => ['vagas', 'id_vaga', 'titulo'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'pessoa' => ['pessoa', 'id_pessoa', 'nome'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'empresa' => ['empresa', 'id_empresa', 'nome'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.

    public static function validKind(string $kind): bool // Explicação DevIN: Declara função/método validKind, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return isset(self::MAP[$kind]); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function target(mysqli $db, string $kind, int $id, bool $lock = false): ?array // Explicação DevIN: Declara função/método target, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!self::validKind($kind) || $id < 1) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Alvo inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        [$table, $primary, $title] = self::MAP[$kind]; // Explicação DevIN: Continua expressão ou bloco pertencente a função target.
        $query = "SELECT {$primary} AS id, {$title} AS title FROM {$table} " // Explicação DevIN: Atribui um valor à variável PHP $query.
            . "WHERE {$primary}=? LIMIT 1" . ($lock ? " FOR UPDATE" : ""); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        $stmt = $db->prepare($query); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->bind_param('i', $id); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $row = $stmt->get_result()->fetch_assoc() ?: null; // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função target.
        return $row === null ? null : [ // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            'id' => (int) $row['id'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'kind' => $kind, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'title' => mb_substr((string) $row['title'], 0, 120, 'UTF-8'), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /**
     * Gerar proposta vinculada à sessão e ao objeto atual, com prazo curto.
     * O título só é exibido; ele nunca é executado como instrução.
     */
    public static function prepare(mysqli $db, int $admin, string $kind, int $id): array // Explicação DevIN: Declara função/método prepare, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $record = self::target($db, $kind, $id); // Explicação DevIN: Atribui um valor à variável PHP $record.
        if ($record === null) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new DomainException('Registro não encontrado.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $nonce = bin2hex(random_bytes(24)); // Explicação DevIN: Atribui um valor à variável PHP $nonce.
        $_SESSION['jarvis_delete_pending'] = [ // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            'nonce_hash' => hash('sha256', $nonce), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'admin' => $admin, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'kind' => $kind, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'id' => $id, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'title_hash' => hash('sha256', $record['title']), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'expires_at' => time() + 180, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
        return [ // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            'record' => $record, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'confirmation' => $nonce, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'expires_in' => 180, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'warning' => 'Exclusão definitiva: pode afetar registros relacionados. ' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                . 'Confira o cadastro e mantenha backup antes de confirmar.', // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function execute( // Explicação DevIN: Declara função/método execute, com seus parâmetros e comportamento.
        mysqli $db, // Explicação DevIN: Continua expressão ou bloco pertencente a função execute.
        int $admin, // Explicação DevIN: Continua expressão ou bloco pertencente a função execute.
        string $kind, // Explicação DevIN: Continua expressão ou bloco pertencente a função execute.
        int $id, // Explicação DevIN: Continua expressão ou bloco pertencente a função execute.
        string $nonce, // Explicação DevIN: Continua expressão ou bloco pertencente a função execute.
        string $approval // Explicação DevIN: Continua expressão ou bloco pertencente a função execute.
    ): array { // Explicação DevIN: Continua expressão ou bloco pertencente a função execute.
        $pending = $_SESSION['jarvis_delete_pending'] ?? null; // Explicação DevIN: Atribui um valor à variável PHP $pending.
        unset($_SESSION['jarvis_delete_pending']); // Explicação DevIN: Continua expressão ou bloco pertencente a função execute.
        if (!is_array($pending) || $approval !== 'EXCLUIR' // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            || !preg_match('/^[a-f0-9]{48}$/D', $nonce) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            || !hash_equals((string) ($pending['nonce_hash'] ?? ''), hash('sha256', $nonce)) // Explicação DevIN: Executa operação criptográfica de senha ou comparação protegida.
            || (int) ($pending['expires_at'] ?? 0) < time() // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            || (int) ($pending['admin'] ?? 0) !== $admin // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            || ($pending['kind'] ?? null) !== $kind // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            || (int) ($pending['id'] ?? 0) !== $id) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            throw new DomainException('Confirmação expirada ou inválida. Solicite nova prévia.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        [$table, $primary] = self::MAP[$kind] ?? [null, null]; // Explicação DevIN: Continua expressão ou bloco pertencente a função execute.
        if ($table === null || $id < 1) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Alvo inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $db->begin_transaction(); // Explicação DevIN: Continua expressão ou bloco pertencente a função execute.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            $record = self::target($db, $kind, $id, true); // Explicação DevIN: Atribui um valor à variável PHP $record.
            if ($record === null // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                || !hash_equals((string) $pending['title_hash'], hash('sha256', $record['title']))) { // Explicação DevIN: Executa operação criptográfica de senha ou comparação protegida.
                throw new DomainException('Registro alterado ou indisponível. Faça nova consulta.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $stmt = $db->prepare("DELETE FROM {$table} WHERE {$primary}=?"); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->bind_param('i', $id); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $affected = $stmt->affected_rows; // Explicação DevIN: Atribui um valor à variável PHP $affected.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função execute.
            if ($affected !== 1) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new DomainException('Não foi possível excluir o registro selecionado.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $stmt = $db->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                'INSERT INTO jarvis_moderation_audit ' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                . '(admin_id,target_type,target_id,action_code) VALUES (?,?,?,?)' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $action = 'deleted'; // Explicação DevIN: Atribui um valor à variável PHP $action.
            $stmt->bind_param('isis', $admin, $kind, $id, $action); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função execute.
            $db->commit(); // Explicação DevIN: Continua expressão ou bloco pertencente a função execute.
            return ['success' => true, 'message' => 'Registro excluído após confirmação do administrador.', // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
                'record' => $record]; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        } catch (Throwable $error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            $db->rollback(); // Explicação DevIN: Continua expressão ou bloco pertencente a função execute.
            throw $error; // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
