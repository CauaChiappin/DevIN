<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/controllers/Jarvis/JarvisModeration.php
 * Finalidade: validar propostas de exclusão e registrar auditoria.
 * Domínio: assistência administrativa.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


/**
 * MÓDULO DEVIN — JARVIS MODERATION.
 * Responsabilidade: parte do subsistema de orquestração das ferramentas e respostas do JARVIS para administradores.
 * Leia os métodos abaixo em sequência: entrada validada -> processamento ->
 * resposta ao chamador. Comentários detalhados descrevem decisões, limites
 * e efeitos colaterais; os comentários gerados por linha foram preservados.
 * Atenção: chamadas de banco e ações administrativas exigem autorização no
 * endpoint controlador; métodos isolados não constituem permissão para a IA.
 */


/** Fluxo de exclusão supervisionado por ADM, nunca pelo modelo externo. */
final class JarvisModeration // Explicação DevIN: Declara o tipo JarvisModeration e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    // Detalhe DevIN: Mapa de entidades que podem ser excluídas: tabela e chave primária vêm de constante do servidor, não do texto da IA.
    private const MAP = [ // Explicação DevIN: Define membro, visibilidade ou método de uma classe.
        'vaga' => ['vagas', 'id_vaga', 'titulo'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'pessoa' => ['pessoa', 'id_pessoa', 'nome'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'empresa' => ['empresa', 'id_empresa', 'nome'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.

    // Detalhe DevIN: Valida se o tipo escolhido está entre vaga, pessoa e empresa; impede exclusões de administradores ou tabelas extras.
    // Detalhamento DevIN: centraliza a operação «validKind» no contexto de orquestração das ferramentas e respostas do JARVIS para administradores; os argumentos recebidos definem o alvo ou os dados processados, e o retorno/erro sinaliza o resultado ao chamador.
    // Entrada e saída: confira a assinatura abaixo; falhas devem ser tratadas pelo controlador que chamou esta rotina.
    // Detalhe DevIN: Método validKind: encapsula uma responsabilidade reutilizável do módulo no contexto de validar propostas de exclusão e registrar auditoria. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function validKind(string $kind): bool // Explicação DevIN: Declara função/método validKind, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return isset(self::MAP[$kind]); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Lê somente ID e título/nome para apresentar prévia; FOR UPDATE opcional é usado quando a operação deve bloquear a linha.
    // Detalhamento DevIN: centraliza a operação «target» no contexto de orquestração das ferramentas e respostas do JARVIS para administradores; os argumentos recebidos definem o alvo ou os dados processados, e o retorno/erro sinaliza o resultado ao chamador.
    // Entrada e saída: confira a assinatura abaixo; falhas devem ser tratadas pelo controlador que chamou esta rotina.
    // Detalhe DevIN: Método target: encapsula uma responsabilidade reutilizável do módulo no contexto de validar propostas de exclusão e registrar auditoria. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function target(mysqli $db, string $kind, int $id, bool $lock = false): ?array // Explicação DevIN: Declara função/método target, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // Detalhe DevIN: Recusa tipo desconhecido e IDs abaixo de 1 para impedir consultas perigosas ou sem alvo.
        if (!self::validKind($kind) || $id < 1) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Alvo inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // Detalhe DevIN: Extrai nomes de tabela/coluna exclusivamente da lista fechada; valores variáveis continuam parametrizados.
        [$table, $primary, $title] = self::MAP[$kind]; // Explicação DevIN: Continua expressão ou bloco pertencente a função target.
        $query = "SELECT {$primary} AS id, {$title} AS title FROM {$table} " // Explicação DevIN: Atribui um valor à variável PHP $query.
            . "WHERE {$primary}=? LIMIT 1" . ($lock ? " FOR UPDATE" : ""); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        // Detalhe DevIN: Prepara SQL cujo esquema é pré-aprovado; o ID fornecido pelo administrador é ligado depois como parâmetro.
        // Detalhamento DevIN: SQL parametrizado: os dados variáveis devem ser vinculados por bind_param, impedindo SQL arbitrário.
        // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
        $stmt = $db->prepare($query); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->bind_param('i', $id); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $row = $stmt->get_result()->fetch_assoc() ?: null; // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função target.
        // Detalhe DevIN: Entrega uma representação restrita do cadastro para confirmação humana, sem dados pessoais desnecessários.
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
    // Detalhe DevIN: A primeira etapa nunca exclui: cria uma proposta com nonce aleatório, vínculo com administrador e validade de 180 s.
    // Detalhamento DevIN: prepara a operação «prepare» no contexto de orquestração das ferramentas e respostas do JARVIS para administradores; os argumentos recebidos definem o alvo ou os dados processados, e o retorno/erro sinaliza o resultado ao chamador.
    // Entrada e saída: confira a assinatura abaixo; falhas devem ser tratadas pelo controlador que chamou esta rotina.
    // Detalhe DevIN: Método prepare: encapsula uma responsabilidade reutilizável do módulo no contexto de validar propostas de exclusão e registrar auditoria. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function prepare(mysqli $db, int $admin, string $kind, int $id): array // Explicação DevIN: Declara função/método prepare, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // Detalhe DevIN: Confirma a existência do registro antes de oferecer botão de exclusão.
        $record = self::target($db, $kind, $id); // Explicação DevIN: Atribui um valor à variável PHP $record.
        if ($record === null) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new DomainException('Registro não encontrado.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // Detalhe DevIN: Cria um token de uso único imprevisível; a sessão guarda apenas seu hash SHA-256, não o valor entregue ao frontend.
        // Detalhamento DevIN: Geração criptograficamente segura de bytes aleatórios para tokens, nonces ou segredos.
        $nonce = bin2hex(random_bytes(24)); // Explicação DevIN: Atribui um valor à variável PHP $nonce.
        // Detalhe DevIN: Associa token, ADM, categoria, ID, resumo do título e expiração à sessão autenticada.
        $_SESSION['jarvis_delete_pending'] = [ // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            'nonce_hash' => hash('sha256', $nonce), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'admin' => $admin, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'kind' => $kind, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'id' => $id, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            // Detalhe DevIN: Registra um hash do nome/título para detectar alteração entre a prévia e a confirmação.
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

    // Detalhe DevIN: A segunda etapa só pode excluir após validar proposta não expirada, nonce correto, ADM, categoria e ID.
    // Detalhamento DevIN: executa a operação «execute» no contexto de orquestração das ferramentas e respostas do JARVIS para administradores; os argumentos recebidos definem o alvo ou os dados processados, e o retorno/erro sinaliza o resultado ao chamador.
    // Entrada e saída: confira a assinatura abaixo; falhas devem ser tratadas pelo controlador que chamou esta rotina.
    // Detalhe DevIN: Método execute: orquestra uma etapa do fluxo e suas possíveis falhas no contexto de validar propostas de exclusão e registrar auditoria. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function execute( // Explicação DevIN: Declara função/método execute, com seus parâmetros e comportamento.
        mysqli $db, // Explicação DevIN: Continua expressão ou bloco pertencente a função execute.
        int $admin, // Explicação DevIN: Continua expressão ou bloco pertencente a função execute.
        string $kind, // Explicação DevIN: Continua expressão ou bloco pertencente a função execute.
        int $id, // Explicação DevIN: Continua expressão ou bloco pertencente a função execute.
        string $nonce, // Explicação DevIN: Continua expressão ou bloco pertencente a função execute.
        string $approval // Explicação DevIN: Continua expressão ou bloco pertencente a função execute.
    ): array { // Explicação DevIN: Continua expressão ou bloco pertencente a função execute.
        $pending = $_SESSION['jarvis_delete_pending'] ?? null; // Explicação DevIN: Atribui um valor à variável PHP $pending.
        // Detalhe DevIN: Consome a proposta antes de validar ou alterar dados, evitando reutilização do mesmo token em novas tentativas.
        unset($_SESSION['jarvis_delete_pending']); // Explicação DevIN: Continua expressão ou bloco pertencente a função execute.
        // Detalhe DevIN: Confere todos os campos de confirmação, inclusive a palavra EXCLUIR, para bloquear mutações não intencionais.
        if (!is_array($pending) || $approval !== 'EXCLUIR' // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            || !preg_match('/^[a-f0-9]{48}$/D', $nonce) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            // Detalhamento DevIN: Comparação resistente a diferenças de tempo, usada para verificar segredo/token sem exposição.
            // Detalhe DevIN: Compara hashes ou segredos sem diferenças de tempo evitáveis.
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
        // Detalhe DevIN: Inicia transação: a exclusão e o registro de auditoria devem ser confirmados juntos ou revertidos.
        // Detalhamento DevIN: Início de transação: alterações relacionadas precisam ser confirmadas juntas ou desfeitas em caso de erro.
        // Detalhe DevIN: Inicia uma transação para manter alterações relacionadas consistentes.
        $db->begin_transaction(); // Explicação DevIN: Continua expressão ou bloco pertencente a função execute.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            // Detalhe DevIN: Recarrega e bloqueia a mesma linha com FOR UPDATE, evitando agir com base em prévia desatualizada.
            $record = self::target($db, $kind, $id, true); // Explicação DevIN: Atribui um valor à variável PHP $record.
            // Detalhe DevIN: Cancela se o registro desapareceu ou seu título mudou desde a proposta original.
            if ($record === null // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                // Detalhamento DevIN: Comparação resistente a diferenças de tempo, usada para verificar segredo/token sem exposição.
                // Detalhe DevIN: Compara hashes ou segredos sem diferenças de tempo evitáveis.
                || !hash_equals((string) $pending['title_hash'], hash('sha256', $record['title']))) { // Explicação DevIN: Executa operação criptográfica de senha ou comparação protegida.
                throw new DomainException('Registro alterado ou indisponível. Faça nova consulta.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            // Detalhe DevIN: Remove somente uma linha da tabela autorizada e do ID confirmado; vínculos do banco ainda podem impedir a operação.
            // Detalhamento DevIN: SQL parametrizado: os dados variáveis devem ser vinculados por bind_param, impedindo SQL arbitrário.
            // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
            $stmt = $db->prepare("DELETE FROM {$table} WHERE {$primary}=?"); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->bind_param('i', $id); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $affected = $stmt->affected_rows; // Explicação DevIN: Atribui um valor à variável PHP $affected.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função execute.
            // Detalhe DevIN: Exige exatamente uma linha afetada; zero linhas implica operação não concluída, que é revertida.
            if ($affected !== 1) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new DomainException('Não foi possível excluir o registro selecionado.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            // Detalhe DevIN: Prepara o evento de auditoria na mesma transação, com ADM, alvo e tipo da operação executada.
            // Detalhamento DevIN: SQL parametrizado: os dados variáveis devem ser vinculados por bind_param, impedindo SQL arbitrário.
            // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
            $stmt = $db->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                'INSERT INTO jarvis_moderation_audit ' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                . '(admin_id,target_type,target_id,action_code) VALUES (?,?,?,?)' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $action = 'deleted'; // Explicação DevIN: Atribui um valor à variável PHP $action.
            $stmt->bind_param('isis', $admin, $kind, $id, $action); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função execute.
            // Detalhe DevIN: Confirma a transação apenas após exclusão e auditoria terem sido concluídas corretamente.
            // Detalhamento DevIN: Confirmação transacional: só persiste o conjunto de alterações depois da sequência anterior concluir.
            // Detalhe DevIN: Confirma alterações realizadas no mesmo contexto transacional.
            $db->commit(); // Explicação DevIN: Continua expressão ou bloco pertencente a função execute.
            return ['success' => true, 'message' => 'Registro excluído após confirmação do administrador.', // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
                'record' => $record]; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        } catch (Throwable $error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            // Detalhe DevIN: Qualquer erro desfaz alterações não confirmadas para preservar consistência entre os dados e o histórico.
            // Detalhamento DevIN: Reversão transacional: restaura o estado do banco quando uma etapa falha.
            // Detalhe DevIN: Desfaz alterações ainda não confirmadas quando ocorre falha.
            $db->rollback(); // Explicação DevIN: Continua expressão ou bloco pertencente a função execute.
            throw $error; // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
