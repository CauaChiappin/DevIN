<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/controllers/Jarvis/ConversationRepository.php
 * Finalidade: conversation repository.
 * Domínio: assistência administrativa.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


/**
 * MÓDULO DEVIN — CONVERSATION REPOSITORY.
 * Responsabilidade: parte do subsistema de orquestração das ferramentas e respostas do JARVIS para administradores.
 * Leia os métodos abaixo em sequência: entrada validada -> processamento ->
 * resposta ao chamador. Comentários detalhados descrevem decisões, limites
 * e efeitos colaterais; os comentários gerados por linha foram preservados.
 * Atenção: chamadas de banco e ações administrativas exigem autorização no
 * endpoint controlador; métodos isolados não constituem permissão para a IA.
 */


final class ConversationRepository // Explicação DevIN: Declara o tipo ConversationRepository e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    // Detalhamento DevIN: cria a operação «create» no contexto de orquestração das ferramentas e respostas do JARVIS para administradores; os argumentos recebidos definem o alvo ou os dados processados, e o retorno/erro sinaliza o resultado ao chamador.
    // Entrada e saída: confira a assinatura abaixo; falhas devem ser tratadas pelo controlador que chamou esta rotina.
    // Detalhe DevIN: Método create: cria registros após checagem da entrada no contexto de conversation repository. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function create(mysqli $db, int $adminId, string $title = 'Nova conversa'): int // Explicação DevIN: Declara função/método create, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $title = mb_substr(trim($title), 0, 150, 'UTF-8') ?: 'Nova conversa'; // Explicação DevIN: Atribui um valor à variável PHP $title.
        // Detalhamento DevIN: SQL parametrizado: os dados variáveis devem ser vinculados por bind_param, impedindo SQL arbitrário.
        // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
        $stmt = $db->prepare('INSERT INTO jarvis_conversations (admin_id, title) VALUES (?, ?)'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->bind_param('is', $adminId, $title); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $id = (int) $db->insert_id; // Explicação DevIN: Atribui um valor à variável PHP $id.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função create.
        return $id; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhamento DevIN: centraliza a operação «belongsTo» no contexto de orquestração das ferramentas e respostas do JARVIS para administradores; os argumentos recebidos definem o alvo ou os dados processados, e o retorno/erro sinaliza o resultado ao chamador.
    // Entrada e saída: confira a assinatura abaixo; falhas devem ser tratadas pelo controlador que chamou esta rotina.
    // Detalhe DevIN: Método belongsTo: encapsula uma responsabilidade reutilizável do módulo no contexto de conversation repository. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function belongsTo(mysqli $db, int $adminId, int $id): bool // Explicação DevIN: Declara função/método belongsTo, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if ($id <= 0) return false; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        // Detalhamento DevIN: SQL parametrizado: os dados variáveis devem ser vinculados por bind_param, impedindo SQL arbitrário.
        // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
        $stmt = $db->prepare('SELECT id FROM jarvis_conversations WHERE id = ? AND admin_id = ?'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->bind_param('ii', $id, $adminId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $found = $stmt->get_result()->fetch_row() !== null; // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função belongsTo.
        return $found; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhamento DevIN: centraliza a operação «latest» no contexto de orquestração das ferramentas e respostas do JARVIS para administradores; os argumentos recebidos definem o alvo ou os dados processados, e o retorno/erro sinaliza o resultado ao chamador.
    // Entrada e saída: confira a assinatura abaixo; falhas devem ser tratadas pelo controlador que chamou esta rotina.
    // Detalhe DevIN: Método latest: encapsula uma responsabilidade reutilizável do módulo no contexto de conversation repository. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function latest(mysqli $db, int $adminId, int $id): array // Explicação DevIN: Declara função/método latest, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!self::belongsTo($db, $adminId, $id)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Conversa não encontrada.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // Detalhamento DevIN: SQL parametrizado: os dados variáveis devem ser vinculados por bind_param, impedindo SQL arbitrário.
        // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
        $stmt = $db->prepare('SELECT role, content FROM jarvis_messages WHERE conversation_id = ? ORDER BY id DESC LIMIT 20'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->bind_param('i', $id); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função latest.
        return array_reverse($items); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhamento DevIN: centraliza a operação «append» no contexto de orquestração das ferramentas e respostas do JARVIS para administradores; os argumentos recebidos definem o alvo ou os dados processados, e o retorno/erro sinaliza o resultado ao chamador.
    // Entrada e saída: confira a assinatura abaixo; falhas devem ser tratadas pelo controlador que chamou esta rotina.
    // Detalhe DevIN: Método append: encapsula uma responsabilidade reutilizável do módulo no contexto de conversation repository. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function append(mysqli $db, int $id, string $role, string $content): void // Explicação DevIN: Declara função/método append, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!in_array($role, ['user', 'assistant'], true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Papel de mensagem inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // Detalhamento DevIN: SQL parametrizado: os dados variáveis devem ser vinculados por bind_param, impedindo SQL arbitrário.
        // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
        $stmt = $db->prepare('INSERT INTO jarvis_messages (conversation_id, role, content) VALUES (?, ?, ?)'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->bind_param('iss', $id, $role, $content); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função append.
        // Detalhamento DevIN: SQL parametrizado: os dados variáveis devem ser vinculados por bind_param, impedindo SQL arbitrário.
        // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
        $update = $db->prepare('UPDATE jarvis_conversations SET updated_at = NOW() WHERE id = ?'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $update->bind_param('i', $id); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $update->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $update->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função append.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhamento DevIN: lista a operação «list» no contexto de orquestração das ferramentas e respostas do JARVIS para administradores; os argumentos recebidos definem o alvo ou os dados processados, e o retorno/erro sinaliza o resultado ao chamador.
    // Entrada e saída: confira a assinatura abaixo; falhas devem ser tratadas pelo controlador que chamou esta rotina.
    // Detalhe DevIN: Método list: consulta e organiza dados sem presumir que o resultado seja único no contexto de conversation repository. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function list(mysqli $db, int $adminId, string $query): array // Explicação DevIN: Declara função/método list, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $query = mb_substr(trim($query), 0, 80, 'UTF-8'); // Explicação DevIN: Atribui um valor à variável PHP $query.
        if ($query === '') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            // Detalhamento DevIN: SQL parametrizado: os dados variáveis devem ser vinculados por bind_param, impedindo SQL arbitrário.
            // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
            $stmt = $db->prepare('SELECT id, title, updated_at FROM jarvis_conversations WHERE admin_id = ? ORDER BY updated_at DESC, id DESC LIMIT 25'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->bind_param('i', $adminId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
            // Pesquisa por termos individuais; não exige coincidência da frase inteira.
            // Não é busca semântica e não garante correção de erros de digitação.
            $tokens = array_slice(preg_split('/\s+/u', $query) ?: [], 0, 4); // Explicação DevIN: Atribui um valor à variável PHP $tokens.
            $terms = []; // Explicação DevIN: Atribui um valor à variável PHP $terms.
            foreach ($tokens as $token) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                if (mb_strlen($token, 'UTF-8') > 1) $terms[] = '%' . $token . '%'; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            if (!$terms) $terms = ['%' . $query . '%']; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $clauses = []; // Explicação DevIN: Atribui um valor à variável PHP $clauses.
            $types = 'i'; // Explicação DevIN: Atribui um valor à variável PHP $types.
            $params = [$adminId]; // Explicação DevIN: Atribui um valor à variável PHP $params.
            foreach ($terms as $term) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                $clauses[] = '(c.title LIKE ? OR EXISTS (SELECT 1 FROM jarvis_messages m WHERE m.conversation_id = c.id AND m.content LIKE ?))'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                $types .= 'ss'; // Explicação DevIN: Continua expressão ou bloco pertencente a função list.
                $params[] = $term; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                $params[] = $term; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $sql = 'SELECT c.id, c.title, c.updated_at FROM jarvis_conversations c WHERE c.admin_id = ? AND (' // Explicação DevIN: Atribui um valor à variável PHP $sql.
                . implode(' OR ', $clauses) . ') ORDER BY c.updated_at DESC, c.id DESC LIMIT 25'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            // Detalhamento DevIN: SQL parametrizado: os dados variáveis devem ser vinculados por bind_param, impedindo SQL arbitrário.
            // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
            $stmt = $db->prepare($sql); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->bind_param($types, ...$params); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função list.
        return $data; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhamento DevIN: remove a operação «delete» no contexto de orquestração das ferramentas e respostas do JARVIS para administradores; os argumentos recebidos definem o alvo ou os dados processados, e o retorno/erro sinaliza o resultado ao chamador.
    // Entrada e saída: confira a assinatura abaixo; falhas devem ser tratadas pelo controlador que chamou esta rotina.
    // Detalhe DevIN: Método delete: trata remoção sujeita às permissões e integridade do banco no contexto de conversation repository. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function delete(mysqli $db, int $adminId, int $id): bool // Explicação DevIN: Declara função/método delete, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // Detalhamento DevIN: SQL parametrizado: os dados variáveis devem ser vinculados por bind_param, impedindo SQL arbitrário.
        // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
        $stmt = $db->prepare('DELETE FROM jarvis_conversations WHERE id = ? AND admin_id = ?'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->bind_param('ii', $id, $adminId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $deleted = $stmt->affected_rows === 1; // Explicação DevIN: Atribui um valor à variável PHP $deleted.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função delete.
        return $deleted; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
