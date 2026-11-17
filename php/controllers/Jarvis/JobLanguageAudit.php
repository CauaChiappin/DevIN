<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/controllers/Jarvis/JobLanguageAudit.php
 * Finalidade: identificar termos potencialmente ofensivos sem alterar anúncios.
 * Domínio: assistência administrativa.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


/**
 * Triagem somente de leitura de linguagem de vagas para o JARVIS Admin.
 * Não classifica conteúdo como ilícito, não altera vagas e não usa o modelo
 * para executar consultas. Resultados são INDÍCIOS para revisão humana.
 */
final class JobLanguageAudit // Explicação DevIN: Declara o tipo JobLanguageAudit e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    private const BATCH_SIZE = 200; // Explicação DevIN: Define membro, visibilidade ou método de uma classe.
    private const MAX_SCANNED = 2000; // Explicação DevIN: Define membro, visibilidade ou método de uma classe.
    private const MAX_REPORTED = 20; // Explicação DevIN: Define membro, visibilidade ou método de uma classe.

    /** Padrões intencionalmente conservadores; termos isolados podem gerar falso positivo. */
    private const RULES = [ // Explicação DevIN: Define membro, visibilidade ou método de uma classe.
        'Possível linguagem insultuosa' => '~\b(?:idiota|imbecil|burro|burra|vagabund[oa]s?|in[uú]te(?:is|l)|ot[aá]ri[oa]s?|retardad[oa]s?)\b~iu', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'Possível palavrão ou ataque pessoal' => '~\b(?:merda|porra|filh[oa]s? da puta|fdp)\b~iu', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'Possível exclusão por gênero' => '~\b(?:s[oó] homens|somente homens|apenas homens|s[oó] mulheres|somente mulheres|apenas mulheres|n[aã]o (?:aceitamos|contratamos) (?:homens|mulheres))\b~iu', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'Possível exclusão por idade' => '~\b(?:idade m[aá]xima|at[eé] \d{2} anos|menores de \d{2} anos n[aã]o)\b~iu', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'Possível exclusão por deficiência' => '~\b(?:sem deficientes|n[aã]o (?:aceitamos|contratamos) (?:pcd|pessoas com defici[eê]ncia))\b~iu', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'Exigência subjetiva que merece revisão' => '~\b(?:boa apar[eê]ncia|apar[eê]ncia impec[aá]vel)\b~iu', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.

    // Detalhe DevIN: Método requested: encapsula uma responsabilidade reutilizável do módulo no contexto de identificar termos potencialmente ofensivos sem alterar anúncios. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function requested(string $message): bool // Explicação DevIN: Declara função/método requested, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $normalized = mb_strtolower($message, 'UTF-8'); // Explicação DevIN: Atribui um valor à variável PHP $normalized.
        $normalized = strtr($normalized, ['á'=>'a','à'=>'a','â'=>'a','ã'=>'a','é'=>'e','ê'=>'e','í'=>'i','ó'=>'o','ô'=>'o','õ'=>'o','ú'=>'u','ç'=>'c']); // Explicação DevIN: Atribui um valor à variável PHP $normalized.
        return preg_match('~\b(?:vaga|vagas|anuncio|anuncios)\b~u', $normalized) === 1 // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            && preg_match('~\b(?:ofensiv\w*|inapropriad\w*|inadequad\w*|discriminatori\w*|preconceituos\w*|abusiv\w*|assedi\w*|toxic\w*|impropri\w*)\b~u', $normalized) === 1 // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            && preg_match('~\b(?:verifi\w*|analis\w*|revis\w*|procur\w*|busc\w*|detect\w*|encontr\w*|identific\w*|varr\w*|examin\w*|exist\w*|tem|ha)\b~u', $normalized) === 1; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /** @return list<array{categoria:string,trecho:string,campo:string}> */
    // Detalhe DevIN: Método reviewText: encapsula uma responsabilidade reutilizável do módulo no contexto de identificar termos potencialmente ofensivos sem alterar anúncios. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function reviewText(string $title, string $description): array // Explicação DevIN: Declara função/método reviewText, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $matches = []; // Explicação DevIN: Atribui um valor à variável PHP $matches.
        foreach (['título' => $title, 'descrição' => $description] as $field => $text) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            foreach (self::RULES as $label => $regex) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                if (preg_match($regex, $text, $found, PREG_OFFSET_CAPTURE) !== 1) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    continue; // Explicação DevIN: Controla a continuação ou saída de laço/seleção.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                $byteOffset = (int) $found[0][1]; // Explicação DevIN: Atribui um valor à variável PHP $byteOffset.
                $start = max(0, mb_strlen(substr($text, 0, $byteOffset), 'UTF-8') - 55); // Explicação DevIN: Atribui um valor à variável PHP $start.
                $snippet = mb_substr($text, $start, 150, 'UTF-8'); // Explicação DevIN: Atribui um valor à variável PHP $snippet.
                $snippet = trim(preg_replace('/\s+/u', ' ', $snippet) ?? ''); // Explicação DevIN: Atribui um valor à variável PHP $snippet.
                $matches[] = [ // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    'categoria' => $label, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    'trecho' => $snippet, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    'campo' => $field, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return $matches; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Método scan: encapsula uma responsabilidade reutilizável do módulo no contexto de identificar termos potencialmente ofensivos sem alterar anúncios. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function scan(mysqli $db): array // Explicação DevIN: Declara função/método scan, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $total = (int) $db->query('SELECT COUNT(*) FROM vagas')->fetch_row()[0]; // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $cursor = PHP_INT_MAX; // Explicação DevIN: Atribui um valor à variável PHP $cursor.
        $scanned = 0; // Explicação DevIN: Atribui um valor à variável PHP $scanned.
        $flagged = 0; // Explicação DevIN: Atribui um valor à variável PHP $flagged.
        $results = []; // Explicação DevIN: Atribui um valor à variável PHP $results.
        // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
        $statement = $db->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            'SELECT id_vaga, titulo, descricao FROM vagas ' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            . 'WHERE id_vaga < ? ORDER BY id_vaga DESC LIMIT ' . self::BATCH_SIZE // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            while ($scanned < self::MAX_SCANNED) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                $statement->bind_param('i', $cursor); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $statement->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $rows = $statement->get_result()->fetch_all(MYSQLI_ASSOC); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
                if ($rows === []) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    break; // Explicação DevIN: Controla a continuação ou saída de laço/seleção.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                foreach ($rows as $row) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                    $cursor = (int) $row['id_vaga']; // Explicação DevIN: Atribui um valor à variável PHP $cursor.
                    $scanned++; // Explicação DevIN: Continua expressão ou bloco pertencente a função scan.
                    $indicators = self::reviewText((string) $row['titulo'], (string) ($row['descricao'] ?? '')); // Explicação DevIN: Atribui um valor à variável PHP $indicators.
                    if ($indicators !== []) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        $flagged++; // Explicação DevIN: Continua expressão ou bloco pertencente a função scan.
                        if (count($results) < self::MAX_REPORTED) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                            $results[] = [ // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                                'id' => $cursor, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                                'titulo' => mb_substr((string) $row['titulo'], 0, 100, 'UTF-8'), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                                'indicadores' => array_slice($indicators, 0, 3), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                            ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
                        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                if (count($rows) < self::BATCH_SIZE) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    break; // Explicação DevIN: Controla a continuação ou saída de laço/seleção.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
            $statement->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função scan.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return ['total' => $total, 'analisadas' => $scanned, 'sinalizadas' => $flagged, // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            'amostra' => $results, 'parcial' => $scanned < $total]; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Método format: transforma ou normaliza dados para o formato esperado no contexto de identificar termos potencialmente ofensivos sem alterar anúncios. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function format(array $scan): string // Explicação DevIN: Declara função/método format, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $out = 'Triagem de linguagem de vagas (consulta real ao MySQL, somente leitura).' . "\n" // Explicação DevIN: Atribui um valor à variável PHP $out.
            . 'Vagas verificadas: ' . $scan['analisadas'] . ' de ' . $scan['total'] // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            . '. Com indícios: ' . $scan['sinalizadas'] . '.' . "\n"; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        if ($scan['parcial']) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $out .= 'ATENÇÃO: varredura parcial; limite de 2.000 vagas por solicitação. ' // Explicação DevIN: Continua expressão ou bloco pertencente a função format.
                . 'Não é possível concluir sobre as demais.' . "\n"; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if ($scan['sinalizadas'] === 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $out .= 'Nenhum termo das regras atuais foi encontrado nas vagas verificadas. ' // Explicação DevIN: Continua expressão ou bloco pertencente a função format.
                . 'Isso NÃO comprova ausência de conteúdo ofensivo ou discriminatório.' . "\n"; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        foreach ($scan['amostra'] as $job) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            $out .= "\n" . 'Vaga #' . $job['id'] . ' — ' . $job['titulo'] . "\n"; // Explicação DevIN: Continua expressão ou bloco pertencente a função format.
            foreach ($job['indicadores'] as $indicator) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                $out .= '- ' . $indicator['categoria'] . ' (' . $indicator['campo'] // Explicação DevIN: Continua expressão ou bloco pertencente a função format.
                    . '): “' . $indicator['trecho'] . '”' . "\n"; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if ($scan['sinalizadas'] > count($scan['amostra'])) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $out .= "\n" . 'Exibidas apenas as primeiras ' . count($scan['amostra']) // Explicação DevIN: Continua expressão ou bloco pertencente a função format.
                . ' vagas sinalizadas para manter o relatório legível.' . "\n"; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return $out . "\n" . 'Atenção: triagem por expressões, sujeita a falsos positivos e negativos. ' // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            . 'Examine o contexto e a legalidade com revisão humana. ' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            . 'Nenhuma vaga foi editada, suspensa ou excluída.'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
