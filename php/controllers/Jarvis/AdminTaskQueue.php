<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

/**
 * MÓDULO DEVIN — ADMIN TASK QUEUE.
 * Responsabilidade: parte do subsistema de orquestração das ferramentas e respostas do JARVIS para administradores.
 * Leia os métodos abaixo em sequência: entrada validada -> processamento ->
 * resposta ao chamador. Comentários detalhados descrevem decisões, limites
 * e efeitos colaterais; os comentários gerados por linha foram preservados.
 * Atenção: chamadas de banco e ações administrativas exigem autorização no
 * endpoint controlador; métodos isolados não constituem permissão para a IA.
 */


/** Fila de atividades administrativas, sem permissão de executar ações reais no DevIN. */
final class AdminTaskQueue // Explicação DevIN: Declara o tipo AdminTaskQueue e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    // Detalhamento DevIN: lista a operação «list» no contexto de orquestração das ferramentas e respostas do JARVIS para administradores; os argumentos recebidos definem o alvo ou os dados processados, e o retorno/erro sinaliza o resultado ao chamador.
    // Entrada e saída: confira a assinatura abaixo; falhas devem ser tratadas pelo controlador que chamou esta rotina.
    public static function list(mysqli $db, int $adminId): array // Explicação DevIN: Declara função/método list, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // Detalhamento DevIN: SQL parametrizado: os dados variáveis devem ser vinculados por bind_param, impedindo SQL arbitrário.
        $stmt = $db->prepare('SELECT id, title, description, status, created_at, updated_at FROM jarvis_tasks WHERE admin_id = ? ORDER BY FIELD(status, \'pending\', \'in_progress\', \'done\', \'cancelled\'), updated_at DESC, id DESC LIMIT 50'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->bind_param('i', $adminId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função list.
        return $items; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhamento DevIN: cria a operação «create» no contexto de orquestração das ferramentas e respostas do JARVIS para administradores; os argumentos recebidos definem o alvo ou os dados processados, e o retorno/erro sinaliza o resultado ao chamador.
    // Entrada e saída: confira a assinatura abaixo; falhas devem ser tratadas pelo controlador que chamou esta rotina.
    public static function create(mysqli $db, int $adminId, string $title, string $description): int // Explicação DevIN: Declara função/método create, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $title = trim($title); // Explicação DevIN: Atribui um valor à variável PHP $title.
        $description = trim($description); // Explicação DevIN: Atribui um valor à variável PHP $description.
        if ($title === '' || mb_strlen($title, 'UTF-8') > 150 // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            || mb_strlen($description, 'UTF-8') > 1200) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            throw new InvalidArgumentException('Informe um título de até 150 caracteres e descrição de até 1.200.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // Detalhamento DevIN: Início de transação: alterações relacionadas precisam ser confirmadas juntas ou desfeitas em caso de erro.
        $db->begin_transaction(); // Explicação DevIN: Continua expressão ou bloco pertencente a função create.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            // Detalhamento DevIN: SQL parametrizado: os dados variáveis devem ser vinculados por bind_param, impedindo SQL arbitrário.
            $stmt = $db->prepare('INSERT INTO jarvis_tasks (admin_id, title, description) VALUES (?, ?, ?)'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->bind_param('iss', $adminId, $title, $description); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $id = (int) $db->insert_id; // Explicação DevIN: Atribui um valor à variável PHP $id.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função create.
            self::audit($db, $id, $adminId, 'created'); // Explicação DevIN: Continua expressão ou bloco pertencente a função create.
            // Detalhamento DevIN: Confirmação transacional: só persiste o conjunto de alterações depois da sequência anterior concluir.
            $db->commit(); // Explicação DevIN: Continua expressão ou bloco pertencente a função create.
            return $id; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            // Detalhamento DevIN: Reversão transacional: restaura o estado do banco quando uma etapa falha.
            $db->rollback(); // Explicação DevIN: Continua expressão ou bloco pertencente a função create.
            throw $exception; // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhamento DevIN: centraliza a operação «change» no contexto de orquestração das ferramentas e respostas do JARVIS para administradores; os argumentos recebidos definem o alvo ou os dados processados, e o retorno/erro sinaliza o resultado ao chamador.
    // Entrada e saída: confira a assinatura abaixo; falhas devem ser tratadas pelo controlador que chamou esta rotina.
    public static function change(mysqli $db, int $adminId, int $taskId, string $action): string // Explicação DevIN: Declara função/método change, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $allowed = [ // Explicação DevIN: Atribui um valor à variável PHP $allowed.
            'start' => ['from' => 'pending', 'to' => 'in_progress', 'audit' => 'started'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'complete' => ['from' => 'in_progress', 'to' => 'done', 'audit' => 'completed'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
        if ($action === 'cancel') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $allowed['cancel'] = ['from' => '', 'to' => 'cancelled', 'audit' => 'cancelled']; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if ($taskId <= 0 || !isset($allowed[$action])) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Ação inválida.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // Detalhamento DevIN: Início de transação: alterações relacionadas precisam ser confirmadas juntas ou desfeitas em caso de erro.
        $db->begin_transaction(); // Explicação DevIN: Continua expressão ou bloco pertencente a função change.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            // Detalhamento DevIN: SQL parametrizado: os dados variáveis devem ser vinculados por bind_param, impedindo SQL arbitrário.
            $stmt = $db->prepare('SELECT status FROM jarvis_tasks WHERE id = ? AND admin_id = ? FOR UPDATE'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->bind_param('ii', $taskId, $adminId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $row = $stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função change.
            if (!$row) throw new InvalidArgumentException('Atividade não encontrada.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $current = (string) $row['status']; // Explicação DevIN: Atribui um valor à variável PHP $current.
            if ($action === 'cancel' ? !in_array($current, ['pending', 'in_progress'], true) : $current !== $allowed[$action]['from']) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new InvalidArgumentException('Esta atividade já mudou de estado. Atualize a lista.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $next = $allowed[$action]['to']; // Explicação DevIN: Atribui um valor à variável PHP $next.
            // Detalhamento DevIN: SQL parametrizado: os dados variáveis devem ser vinculados por bind_param, impedindo SQL arbitrário.
            $stmt = $db->prepare('UPDATE jarvis_tasks SET status = ? WHERE id = ? AND admin_id = ?'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->bind_param('sii', $next, $taskId, $adminId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função change.
            self::audit($db, $taskId, $adminId, $allowed[$action]['audit']); // Explicação DevIN: Continua expressão ou bloco pertencente a função change.
            // Detalhamento DevIN: Confirmação transacional: só persiste o conjunto de alterações depois da sequência anterior concluir.
            $db->commit(); // Explicação DevIN: Continua expressão ou bloco pertencente a função change.
            return $next; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            // Detalhamento DevIN: Reversão transacional: restaura o estado do banco quando uma etapa falha.
            $db->rollback(); // Explicação DevIN: Continua expressão ou bloco pertencente a função change.
            throw $exception; // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhamento DevIN: audita a operação «audit» no contexto de orquestração das ferramentas e respostas do JARVIS para administradores; os argumentos recebidos definem o alvo ou os dados processados, e o retorno/erro sinaliza o resultado ao chamador.
    // Entrada e saída: confira a assinatura abaixo; falhas devem ser tratadas pelo controlador que chamou esta rotina.
    private static function audit(mysqli $db, int $taskId, int $adminId, string $action): void // Explicação DevIN: Declara função/método audit, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // Detalhamento DevIN: SQL parametrizado: os dados variáveis devem ser vinculados por bind_param, impedindo SQL arbitrário.
        $stmt = $db->prepare('INSERT INTO jarvis_task_audit (task_id, admin_id, action) VALUES (?, ?, ?)'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->bind_param('iis', $taskId, $adminId, $action); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função audit.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
