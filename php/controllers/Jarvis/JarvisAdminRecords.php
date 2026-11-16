<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

/**
 * Acesso de leitura fechado: nenhum SQL é gerado pelo modelo.
 * Não retorna CPF/CNPJ, e-mail, fotos, senhas, currículos ou contatos.
 */
final class JarvisAdminRecords // Explicação DevIN: Declara o tipo JarvisAdminRecords e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    private const KINDS = ['pessoa','empresa','vaga','reports_vagas','reports_perfis']; // Explicação DevIN: Define membro, visibilidade ou método de uma classe.

    public static function validKind(string $kind): bool // Explicação DevIN: Declara função/método validKind, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return in_array($kind, self::KINDS, true); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function recipient(mysqli $db, string $type, int $id): ?array // Explicação DevIN: Declara função/método recipient, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!in_array($type, ['pessoa','empresa'], true) || $id <= 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Destinatário inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        [$table, $column] = $type === 'pessoa' // Explicação DevIN: Continua expressão ou bloco pertencente a função recipient.
            ? ['pessoa','id_pessoa'] : ['empresa','id_empresa']; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        $stmt = $db->prepare("SELECT {$column} AS id, nome FROM {$table} WHERE {$column} = ? LIMIT 1"); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->bind_param('i', $id); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $row = $stmt->get_result()->fetch_assoc() ?: null; // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função recipient.
        return $row === null ? null : [ // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            'id'=>(int)$row['id'], 'type'=>$type, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'name'=>mb_substr((string)$row['nome'], 0, 100), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /** @return list<array<string,mixed>> */
    public static function search(mysqli $db, string $kind, string $query): array // Explicação DevIN: Declara função/método search, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!self::validKind($kind) || mb_strlen($query) > 100) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Consulta inválida.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $query = trim($query); // Explicação DevIN: Atribui um valor à variável PHP $query.
        $exactId = ctype_digit($query) && (int)$query > 0 ? (int)$query : 0; // Explicação DevIN: Atribui um valor à variável PHP $exactId.
        $like = '%' . str_replace(['\\','%','_'], ['\\\\','\\%','\\_'], $query) . '%'; // Explicação DevIN: Atribui um valor à variável PHP $like.
        if ($kind === 'pessoa' || $kind === 'empresa') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            [$table,$col] = $kind === 'pessoa' // Explicação DevIN: Continua expressão ou bloco pertencente a função search.
                ? ['pessoa','id_pessoa'] : ['empresa','id_empresa']; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            $stmt = $db->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                "SELECT {$col} AS id,nome AS title FROM {$table} " // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                . "WHERE nome LIKE ? OR {$col} = ? ORDER BY {$col} DESC LIMIT 12" // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $stmt->bind_param('si', $like, $exactId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        } elseif ($kind === 'vaga') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $stmt = $db->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                'SELECT v.id_vaga AS id,v.titulo AS title,e.nome AS subtitle ' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                . 'FROM vagas v LEFT JOIN empresa e ON e.id_empresa=v.id_empresa ' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                . 'WHERE v.titulo LIKE ? OR v.id_vaga=? ORDER BY v.id_vaga DESC LIMIT 12' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $stmt->bind_param('si', $like, $exactId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        } elseif ($kind === 'reports_vagas') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $stmt = $db->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                'SELECT id,titulo_vaga AS title,motivo,status FROM reports_vagas ' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                . "WHERE id=? OR titulo_vaga LIKE ? ORDER BY id DESC LIMIT 12" // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $stmt->bind_param('is', $exactId, $like); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
            $stmt = $db->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                'SELECT id,nome_denunciado AS title,motivo,status,tipo_denunciado ' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                . 'FROM reports_perfis WHERE id=? OR nome_denunciado LIKE ? ORDER BY id DESC LIMIT 12' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $stmt->bind_param('is', $exactId, $like); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função search.
        return array_map(static function(array $row): array { // Explicação DevIN: Define função anônima usada como operação ou callback.
            $row['id'] = (int)$row['id']; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            foreach ($row as $key=>$value) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                if (is_string($value)) $row[$key] = mb_substr($value,0,180); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            return $row; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        }, $result); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /**
     * Resumo explícito selecionado pelo ADM. Somente este texto pode ser
     * incluído na chamada externa de IA após clique em Analisar.
     */
    public static function inspect(mysqli $db, string $kind, int $id): ?array // Explicação DevIN: Declara função/método inspect, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!self::validKind($kind) || $id < 1) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Registro inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if ($kind === 'pessoa' || $kind === 'empresa') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            return self::recipient($db, $kind, $id); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $queries = [ // Explicação DevIN: Atribui um valor à variável PHP $queries.
            'vaga' => 'SELECT id_vaga AS id,titulo AS title,LEFT(descricao,450) AS description ' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                . 'FROM vagas WHERE id_vaga=? LIMIT 1', // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            'reports_vagas' => 'SELECT id,motivo,status,id_vaga,titulo_vaga AS title ' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                . 'FROM reports_vagas WHERE id=? LIMIT 1', // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            'reports_perfis' => 'SELECT id,motivo,status,tipo_denunciado,id_denunciado ' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                . 'FROM reports_perfis WHERE id=? LIMIT 1', // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
        $stmt = $db->prepare($queries[$kind]); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->bind_param('i', $id); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $row = $stmt->get_result()->fetch_assoc() ?: null; // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função inspect.
        if ($row === null) return null; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        foreach ($row as $key=>$value) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            if (is_string($value)) $row[$key] = mb_substr($value,0,450); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return $row; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
