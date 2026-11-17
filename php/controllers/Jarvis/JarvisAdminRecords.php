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
     * Pesquisa paginada logicamente e ordenada por relevância para escolha do ADM.
     * Somente nomes, títulos, IDs e nome público da empresa são retornados.
     * A classificação é heurística, não uma probabilidade estatística.
     */
    /**
     * Consulta até 120 registros candidatos por categoria, usando
     * SQL parametrizado e categorias permitidas previamente.
     * A busca permite ID, nome completo, fragmento do título e,
     * para vagas, nome da empresa. Ordena por suggestionScore()
     * e devolve no máximo 12 opções identificadas por ID único.
     * Nomes iguais são mantidos como opções distintas.
     */
    public static function suggest(mysqli $db, string $kind, string $term): array // Explicação DevIN: Declara função/método suggest, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!in_array($kind, ['vaga','pessoa','empresa'], true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Categoria de pesquisa inválida.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $term = trim(preg_replace('/\s+/u', ' ', $term) ?? ''); // Explicação DevIN: Atribui um valor à variável PHP $term.
        if (mb_strlen($term, 'UTF-8') < 2 || mb_strlen($term, 'UTF-8') > 100) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Pesquise usando entre 2 e 100 caracteres.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $exactId = ctype_digit($term) && (int) $term > 0 ? (int) $term : 0; // Explicação DevIN: Atribui um valor à variável PHP $exactId.
        $tokens = preg_split('/\s+/u', $term) ?: []; // Explicação DevIN: Atribui um valor à variável PHP $tokens.
        $first = mb_substr($tokens[0] ?? $term, 0, 40, 'UTF-8'); // Explicação DevIN: Atribui um valor à variável PHP $first.
        $wholeLike = '%' . str_replace(['\\','%','_'], ['\\\\','\\%','\\_'], $term) . '%'; // Explicação DevIN: Atribui um valor à variável PHP $wholeLike.
        $firstLike = '%' . str_replace(['\\','%','_'], ['\\\\','\\%','\\_'], $first) . '%'; // Explicação DevIN: Atribui um valor à variável PHP $firstLike.
        if ($kind === 'vaga') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $stmt = $db->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                'SELECT v.id_vaga AS id, v.titulo AS title, e.nome AS subtitle ' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                . 'FROM vagas v LEFT JOIN empresa e ON e.id_empresa = v.id_empresa ' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                . 'WHERE v.id_vaga=? OR v.titulo LIKE ? OR v.titulo LIKE ? OR e.nome LIKE ? ' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                . 'ORDER BY v.id_vaga DESC LIMIT 120' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $stmt->bind_param('isss', $exactId, $wholeLike, $firstLike, $wholeLike); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
            [$table, $pk] = $kind === 'pessoa' // Explicação DevIN: Continua expressão ou bloco pertencente a função suggest.
                ? ['pessoa','id_pessoa'] : ['empresa','id_empresa']; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            $stmt = $db->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                "SELECT {$pk} AS id, nome AS title FROM {$table} " // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                . "WHERE {$pk}=? OR nome LIKE ? OR nome LIKE ? " // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                . "ORDER BY {$pk} DESC LIMIT 120" // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $stmt->bind_param('iss', $exactId, $wholeLike, $firstLike); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função suggest.
        $result = []; // Explicação DevIN: Atribui um valor à variável PHP $result.
        foreach ($rows as $row) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            $score = self::suggestionScore( // Explicação DevIN: Atribui um valor à variável PHP $score.
                $term, (string) $row['title'], (string) ($row['subtitle'] ?? ''), // Explicação DevIN: Continua expressão ou bloco pertencente a função suggest.
                (int) $row['id'], $exactId // Explicação DevIN: Continua expressão ou bloco pertencente a função suggest.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            if ($score <= 0) continue; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $result[] = [ // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                'id' => (int) $row['id'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'kind' => $kind, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'title' => mb_substr((string) $row['title'], 0, 120, 'UTF-8'), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'subtitle' => mb_substr((string) ($row['subtitle'] ?? ''), 0, 100, 'UTF-8'), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'score' => $score, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'match' => $score >= 90 ? 'Correspondência exata' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    : ($score >= 65 ? 'Correspondência forte' : 'Correspondência relacionada'), // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        usort($result, static fn(array $a, array $b): int => // Explicação DevIN: Continua expressão ou bloco pertencente a função suggest.
            ($b['score'] <=> $a['score']) ?: ($b['id'] <=> $a['id'])); // Explicação DevIN: Continua expressão ou bloco pertencente a função suggest.
        return array_slice($result, 0, 12); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /**
     * Calcula um índice de ordenação, nunca chance estatística:
     * 120 = ID digitado exatamente; 100 = título exato; 85 = título
     * começa pelo termo; 70 = termo dentro do título; 65/55 =
     * nome da empresa exato/contido; demais valores dependem de
     * quantas palavras pesquisadas aparecem no título.
     * O índice não autoriza enviar mensagem nem excluir uma vaga.
     */
    public static function suggestionScore( // Explicação DevIN: Declara função/método suggestionScore, com seus parâmetros e comportamento.
        string $query, string $title, string $subtitle = '', int $id = 0, int $exactId = 0 // Explicação DevIN: Continua expressão ou bloco pertencente a função suggestionScore.
    ): int { // Explicação DevIN: Continua expressão ou bloco pertencente a função suggestionScore.
        if ($exactId > 0 && $id === $exactId) return 120; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $normalize = static function (string $value): string { // Explicação DevIN: Define função anônima usada como operação ou callback.
            $lower = mb_strtolower(trim($value), 'UTF-8'); // Explicação DevIN: Atribui um valor à variável PHP $lower.
            $lower = strtr($lower, [ // Explicação DevIN: Atribui um valor à variável PHP $lower.
                'á'=>'a','à'=>'a','â'=>'a','ã'=>'a','ä'=>'a', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'é'=>'e','ê'=>'e','è'=>'e','í'=>'i','ì'=>'i', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'ó'=>'o','ô'=>'o','õ'=>'o','ú'=>'u','ü'=>'u','ç'=>'c', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            ]); // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
            return trim(preg_replace('/\s+/u', ' ', $lower) ?? ''); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $q = $normalize($query); // Explicação DevIN: Atribui um valor à variável PHP $q.
        $t = $normalize($title); // Explicação DevIN: Atribui um valor à variável PHP $t.
        $sub = $normalize($subtitle); // Explicação DevIN: Atribui um valor à variável PHP $sub.
        if ($q === '' || $t === '') return 0; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if ($t === $q) return 100; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if (str_starts_with($t, $q)) return 85; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if (str_contains($t, $q)) return 70; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if ($sub !== '' && $sub === $q) return 65; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if ($sub !== '' && str_contains($sub, $q)) return 55; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $words = array_values(array_filter(explode(' ', $q), static fn(string $w): bool => mb_strlen($w) >= 3)); // Explicação DevIN: Atribui um valor à variável PHP $words.
        if ($words === []) return 0; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $found = 0; // Explicação DevIN: Atribui um valor à variável PHP $found.
        foreach ($words as $word) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            if (str_contains($t, $word)) $found++; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if ($found === 0) return 0; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        return (int) round(15 + 45 * $found / count($words)); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
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
