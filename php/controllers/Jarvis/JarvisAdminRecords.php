<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/controllers/Jarvis/JarvisAdminRecords.php
 * Finalidade: consultar registros e ordenar resultados semelhantes.
 * Domínio: assistência administrativa.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


/**
 * MÓDULO DEVIN — JARVIS ADMIN RECORDS.
 * Responsabilidade: parte do subsistema de orquestração das ferramentas e respostas do JARVIS para administradores.
 * Leia os métodos abaixo em sequência: entrada validada -> processamento ->
 * resposta ao chamador. Comentários detalhados descrevem decisões, limites
 * e efeitos colaterais; os comentários gerados por linha foram preservados.
 * Atenção: chamadas de banco e ações administrativas exigem autorização no
 * endpoint controlador; métodos isolados não constituem permissão para a IA.
 */


/**
 * Acesso de leitura fechado: nenhum SQL é gerado pelo modelo.
 * Não retorna CPF/CNPJ, e-mail, fotos, senhas, currículos ou contatos.
 */
final class JarvisAdminRecords // Explicação DevIN: Declara o tipo JarvisAdminRecords e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    // Detalhe DevIN: Lista fechada de entidades que esta classe pode consultar: impede que o navegador ou a IA definam livremente nomes de tabelas SQL.
    private const KINDS = ['pessoa','empresa','vaga','reports_vagas','reports_perfis']; // Explicação DevIN: Define membro, visibilidade ou método de uma classe.

    // Detalhamento DevIN: centraliza a operação «validKind» no contexto de orquestração das ferramentas e respostas do JARVIS para administradores; os argumentos recebidos definem o alvo ou os dados processados, e o retorno/erro sinaliza o resultado ao chamador.
    // Entrada e saída: confira a assinatura abaixo; falhas devem ser tratadas pelo controlador que chamou esta rotina.
    // Detalhe DevIN: Método validKind: encapsula uma responsabilidade reutilizável do módulo no contexto de consultar registros e ordenar resultados semelhantes. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function validKind(string $kind): bool // Explicação DevIN: Declara função/método validKind, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return in_array($kind, self::KINDS, true); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Consulta o nome público do destinatário a partir de um ID validado. Não entrega e-mail, CPF/CNPJ, senha nem informações privadas ao chat.
    // Detalhamento DevIN: centraliza a operação «recipient» no contexto de orquestração das ferramentas e respostas do JARVIS para administradores; os argumentos recebidos definem o alvo ou os dados processados, e o retorno/erro sinaliza o resultado ao chamador.
    // Entrada e saída: confira a assinatura abaixo; falhas devem ser tratadas pelo controlador que chamou esta rotina.
    // Detalhe DevIN: Método recipient: encapsula uma responsabilidade reutilizável do módulo no contexto de consultar registros e ordenar resultados semelhantes. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function recipient(mysqli $db, string $type, int $id): ?array // Explicação DevIN: Declara função/método recipient, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // Detalhe DevIN: Bloqueia tipos fora de pessoa/empresa e IDs inválidos antes de qualquer consulta, evitando destinatários arbitrários.
        if (!in_array($type, ['pessoa','empresa'], true) || $id <= 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Destinatário inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        [$table, $column] = $type === 'pessoa' // Explicação DevIN: Continua expressão ou bloco pertencente a função recipient.
            ? ['pessoa','id_pessoa'] : ['empresa','id_empresa']; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        // Detalhamento DevIN: SQL parametrizado: os dados variáveis devem ser vinculados por bind_param, impedindo SQL arbitrário.
        // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
        $stmt = $db->prepare("SELECT {$column} AS id, nome FROM {$table} WHERE {$column} = ? LIMIT 1"); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        // Detalhe DevIN: Associa o ID como inteiro a um parâmetro preparado: o valor do usuário nunca é interpolado diretamente no comando SQL.
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
    // Detalhe DevIN: Busca legada utilizada na aba Consultar: combina trecho do nome/título com busca por ID, devolvendo registros limitados.
    // Detalhamento DevIN: pesquisa a operação «search» no contexto de orquestração das ferramentas e respostas do JARVIS para administradores; os argumentos recebidos definem o alvo ou os dados processados, e o retorno/erro sinaliza o resultado ao chamador.
    // Entrada e saída: confira a assinatura abaixo; falhas devem ser tratadas pelo controlador que chamou esta rotina.
    // Detalhe DevIN: Método search: consulta e organiza dados sem presumir que o resultado seja único no contexto de consultar registros e ordenar resultados semelhantes. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function search(mysqli $db, string $kind, string $query): array // Explicação DevIN: Declara função/método search, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!self::validKind($kind) || mb_strlen($query) > 100) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Consulta inválida.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $query = trim($query); // Explicação DevIN: Atribui um valor à variável PHP $query.
        // Detalhe DevIN: Se o termo digitado contém apenas algarismos positivos, também compara o ID exato; demais textos não são convertidos para ID.
        $exactId = ctype_digit($query) && (int)$query > 0 ? (int)$query : 0; // Explicação DevIN: Atribui um valor à variável PHP $exactId.
        // Detalhe DevIN: Escapa caracteres especiais de LIKE (% e _) para que o texto digitado seja interpretado como termo, não como padrão SQL arbitrário.
        $like = '%' . str_replace(['\\','%','_'], ['\\\\','\\%','\\_'], $query) . '%'; // Explicação DevIN: Atribui um valor à variável PHP $like.
        if ($kind === 'pessoa' || $kind === 'empresa') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            [$table,$col] = $kind === 'pessoa' // Explicação DevIN: Continua expressão ou bloco pertencente a função search.
                ? ['pessoa','id_pessoa'] : ['empresa','id_empresa']; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            // Detalhamento DevIN: SQL parametrizado: os dados variáveis devem ser vinculados por bind_param, impedindo SQL arbitrário.
            // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
            $stmt = $db->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                "SELECT {$col} AS id,nome AS title FROM {$table} " // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                . "WHERE nome LIKE ? OR {$col} = ? ORDER BY {$col} DESC LIMIT 12" // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $stmt->bind_param('si', $like, $exactId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        } elseif ($kind === 'vaga') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            // Detalhamento DevIN: SQL parametrizado: os dados variáveis devem ser vinculados por bind_param, impedindo SQL arbitrário.
            // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
            $stmt = $db->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                'SELECT v.id_vaga AS id,v.titulo AS title,e.nome AS subtitle ' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                . 'FROM vagas v LEFT JOIN empresa e ON e.id_empresa=v.id_empresa ' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                . 'WHERE v.titulo LIKE ? OR v.id_vaga=? ORDER BY v.id_vaga DESC LIMIT 12' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $stmt->bind_param('si', $like, $exactId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        } elseif ($kind === 'reports_vagas') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            // Detalhamento DevIN: SQL parametrizado: os dados variáveis devem ser vinculados por bind_param, impedindo SQL arbitrário.
            // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
            $stmt = $db->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                'SELECT id,titulo_vaga AS title,motivo,status FROM reports_vagas ' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                . "WHERE id=? OR titulo_vaga LIKE ? ORDER BY id DESC LIMIT 12" // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $stmt->bind_param('is', $exactId, $like); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
            // Detalhamento DevIN: SQL parametrizado: os dados variáveis devem ser vinculados por bind_param, impedindo SQL arbitrário.
            // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
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
    // Detalhe DevIN: Entrada específica da busca inteligente do chat: recebe categoria e termo, mas não tem nenhuma operação INSERT, UPDATE ou DELETE.
    // Detalhamento DevIN: sugere a operação «suggest» no contexto de orquestração das ferramentas e respostas do JARVIS para administradores; os argumentos recebidos definem o alvo ou os dados processados, e o retorno/erro sinaliza o resultado ao chamador.
    // Entrada e saída: confira a assinatura abaixo; falhas devem ser tratadas pelo controlador que chamou esta rotina.
    // Detalhe DevIN: Método suggest: consulta e organiza dados sem presumir que o resultado seja único no contexto de consultar registros e ordenar resultados semelhantes. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function suggest(mysqli $db, string $kind, string $term): array // Explicação DevIN: Declara função/método suggest, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!in_array($kind, ['vaga','pessoa','empresa'], true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Categoria de pesquisa inválida.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // Detalhe DevIN: Normaliza espaços seguidos e remove espaços nas pontas; evita resultados inconsistentes causados apenas por digitação.
        $term = trim(preg_replace('/\s+/u', ' ', $term) ?? ''); // Explicação DevIN: Atribui um valor à variável PHP $term.
        // Detalhe DevIN: Exige termos entre 2 e 100 caracteres UTF-8 para restringir consultas excessivamente amplas e entradas enormes.
        if (mb_strlen($term, 'UTF-8') < 2 || mb_strlen($term, 'UTF-8') > 100) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Pesquise usando entre 2 e 100 caracteres.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $exactId = ctype_digit($term) && (int) $term > 0 ? (int) $term : 0; // Explicação DevIN: Atribui um valor à variável PHP $exactId.
        $tokens = preg_split('/\s+/u', $term) ?: []; // Explicação DevIN: Atribui um valor à variável PHP $tokens.
        // Detalhe DevIN: Obtém a primeira palavra do termo para aceitar correspondências parciais quando o nome completo digitado não aparece literalmente.
        $first = mb_substr($tokens[0] ?? $term, 0, 40, 'UTF-8'); // Explicação DevIN: Atribui um valor à variável PHP $first.
        // Detalhe DevIN: Monta um LIKE de expressão completa, escapando % e _ literais; busca o nome/título dentro do conteúdo armazenado.
        $wholeLike = '%' . str_replace(['\\','%','_'], ['\\\\','\\%','\\_'], $term) . '%'; // Explicação DevIN: Atribui um valor à variável PHP $wholeLike.
        // Detalhe DevIN: Cria uma segunda opção de busca mais ampla usando somente o primeiro termo, para sugerir possíveis resultados próximos.
        $firstLike = '%' . str_replace(['\\','%','_'], ['\\\\','\\%','\\_'], $first) . '%'; // Explicação DevIN: Atribui um valor à variável PHP $firstLike.
        // Detalhe DevIN: Para vagas, junta a tabela de empresas a fim de exibir o empregador e diferenciar anúncios com títulos repetidos.
        if ($kind === 'vaga') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            // Detalhamento DevIN: SQL parametrizado: os dados variáveis devem ser vinculados por bind_param, impedindo SQL arbitrário.
            // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
            $stmt = $db->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                'SELECT v.id_vaga AS id, v.titulo AS title, e.nome AS subtitle ' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                . 'FROM vagas v LEFT JOIN empresa e ON e.id_empresa = v.id_empresa ' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                . 'WHERE v.id_vaga=? OR v.titulo LIKE ? OR v.titulo LIKE ? OR e.nome LIKE ? ' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                . 'ORDER BY v.id_vaga DESC LIMIT 120' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            // Detalhe DevIN: Usa ID inteiro e três strings ligadas ao prepared statement, sem concatenar texto do ADM nas cláusulas WHERE.
            $stmt->bind_param('isss', $exactId, $wholeLike, $firstLike, $wholeLike); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
            [$table, $pk] = $kind === 'pessoa' // Explicação DevIN: Continua expressão ou bloco pertencente a função suggest.
                ? ['pessoa','id_pessoa'] : ['empresa','id_empresa']; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            // Detalhamento DevIN: SQL parametrizado: os dados variáveis devem ser vinculados por bind_param, impedindo SQL arbitrário.
            // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
            $stmt = $db->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                "SELECT {$pk} AS id, nome AS title FROM {$table} " // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                . "WHERE {$pk}=? OR nome LIKE ? OR nome LIKE ? " // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                . "ORDER BY {$pk} DESC LIMIT 120" // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $stmt->bind_param('iss', $exactId, $wholeLike, $firstLike); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        // Detalhe DevIN: Limita a lista preliminar a 120 registros retornados pelo banco antes de aplicar o ranking no PHP; não é paginação completa.
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função suggest.
        $result = []; // Explicação DevIN: Atribui um valor à variável PHP $result.
        foreach ($rows as $row) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            // Detalhe DevIN: Calcula relevância de cada candidato com título, empresa e ID; é heurística, nunca porcentagem de probabilidade nem autorização.
            $score = self::suggestionScore( // Explicação DevIN: Atribui um valor à variável PHP $score.
                $term, (string) $row['title'], (string) ($row['subtitle'] ?? ''), // Explicação DevIN: Continua expressão ou bloco pertencente a função suggest.
                (int) $row['id'], $exactId // Explicação DevIN: Continua expressão ou bloco pertencente a função suggest.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            // Detalhe DevIN: Descarta registros sem similaridade segundo as regras conhecidas antes de montar os cartões de seleção.
            if ($score <= 0) continue; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $result[] = [ // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                // Detalhe DevIN: Preserva o identificador estável do banco: mesmo títulos iguais aparecem como registros distintos e exigem seleção individual.
                'id' => (int) $row['id'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'kind' => $kind, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'title' => mb_substr((string) $row['title'], 0, 120, 'UTF-8'), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'subtitle' => mb_substr((string) ($row['subtitle'] ?? ''), 0, 100, 'UTF-8'), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'score' => $score, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                // Detalhe DevIN: Converte a pontuação em rótulos interpretáveis para o ADM. Esses rótulos não representam certeza de identidade.
                'match' => $score >= 90 ? 'Correspondência exata' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    : ($score >= 65 ? 'Correspondência forte' : 'Correspondência relacionada'), // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // Detalhe DevIN: Ordena primeiro pela pontuação decrescente e usa o ID mais alto apenas como critério estável de desempate.
        usort($result, static fn(array $a, array $b): int => // Explicação DevIN: Continua expressão ou bloco pertencente a função suggest.
            ($b['score'] <=> $a['score']) ?: ($b['id'] <=> $a['id'])); // Explicação DevIN: Continua expressão ou bloco pertencente a função suggest.
        // Detalhe DevIN: Envia no máximo doze alternativas ao chat para evitar sobrecarga visual; outras vagas podem existir fora desse recorte.
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
    // Detalhe DevIN: Função determinística do ranking: recebe termo, título, empresa e ID; não consulta LLM nem executa ações administrativas.
    // Detalhamento DevIN: sugere a operação «suggestionScore» no contexto de orquestração das ferramentas e respostas do JARVIS para administradores; os argumentos recebidos definem o alvo ou os dados processados, e o retorno/erro sinaliza o resultado ao chamador.
    // Entrada e saída: confira a assinatura abaixo; falhas devem ser tratadas pelo controlador que chamou esta rotina.
    // Detalhe DevIN: Método suggestionScore: consulta e organiza dados sem presumir que o resultado seja único no contexto de consultar registros e ordenar resultados semelhantes. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function suggestionScore( // Explicação DevIN: Declara função/método suggestionScore, com seus parâmetros e comportamento.
        string $query, string $title, string $subtitle = '', int $id = 0, int $exactId = 0 // Explicação DevIN: Continua expressão ou bloco pertencente a função suggestionScore.
    ): int { // Explicação DevIN: Continua expressão ou bloco pertencente a função suggestionScore.
        // Detalhe DevIN: Um ID exato tem precedência sobre coincidências textuais porque identifica unicamente um registro dentro da categoria.
        if ($exactId > 0 && $id === $exactId) return 120; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        // Detalhe DevIN: Remove diferenças comuns de caixa, acentuação e espaços antes de comparar nomes, sem alterar os textos originais no banco.
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
        // Detalhe DevIN: Correspondência textual integral recebe maior pontuação entre os casos de nome/título, inclusive se existirem vários homônimos.
        if ($t === $q) return 100; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        // Detalhe DevIN: Quando o título começa pelo termo informado, a correspondência é forte, mas menos específica do que o título inteiro.
        if (str_starts_with($t, $q)) return 85; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if (str_contains($t, $q)) return 70; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        // Detalhe DevIN: O nome público da empresa pode ajudar a localizar vagas; coincidências na empresa recebem menor peso que no título.
        if ($sub !== '' && $sub === $q) return 65; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if ($sub !== '' && str_contains($sub, $q)) return 55; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $words = array_values(array_filter(explode(' ', $q), static fn(string $w): bool => mb_strlen($w) >= 3)); // Explicação DevIN: Atribui um valor à variável PHP $words.
        if ($words === []) return 0; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $found = 0; // Explicação DevIN: Atribui um valor à variável PHP $found.
        foreach ($words as $word) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            if (str_contains($t, $word)) $found++; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if ($found === 0) return 0; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        // Detalhe DevIN: Calcula relevância parcial proporcional à fração das palavras da pesquisa encontradas no título; não é medição estatística.
        return (int) round(15 + 45 * $found / count($words)); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /**
     * Resumo explícito selecionado pelo ADM. Somente este texto pode ser
     * incluído na chamada externa de IA após clique em Analisar.
     */
    // Detalhe DevIN: Só depois da escolha do ADM expõe um resumo limitado de campos do registro. A seleção não implica exclusão nem envio.
    // Detalhamento DevIN: centraliza a operação «inspect» no contexto de orquestração das ferramentas e respostas do JARVIS para administradores; os argumentos recebidos definem o alvo ou os dados processados, e o retorno/erro sinaliza o resultado ao chamador.
    // Entrada e saída: confira a assinatura abaixo; falhas devem ser tratadas pelo controlador que chamou esta rotina.
    // Detalhe DevIN: Método inspect: encapsula uma responsabilidade reutilizável do módulo no contexto de consultar registros e ordenar resultados semelhantes. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
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
        // Detalhamento DevIN: SQL parametrizado: os dados variáveis devem ser vinculados por bind_param, impedindo SQL arbitrário.
        // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
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
