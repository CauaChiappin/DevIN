<?php
declare(strict_types=1);

/**
 * Triagem somente de leitura de linguagem de vagas para o JARVIS Admin.
 * Não classifica conteúdo como ilícito, não altera vagas e não usa o modelo
 * para executar consultas. Resultados são INDÍCIOS para revisão humana.
 */
final class JobLanguageAudit
{
    private const BATCH_SIZE = 200;
    private const MAX_SCANNED = 2000;
    private const MAX_REPORTED = 20;

    /** Padrões intencionalmente conservadores; termos isolados podem gerar falso positivo. */
    private const RULES = [
        'Possível linguagem insultuosa' => '~\b(?:idiota|imbecil|burro|burra|vagabund[oa]s?|in[uú]te(?:is|l)|ot[aá]ri[oa]s?|retardad[oa]s?)\b~iu',
        'Possível palavrão ou ataque pessoal' => '~\b(?:merda|porra|filh[oa]s? da puta|fdp)\b~iu',
        'Possível exclusão por gênero' => '~\b(?:s[oó] homens|somente homens|apenas homens|s[oó] mulheres|somente mulheres|apenas mulheres|n[aã]o (?:aceitamos|contratamos) (?:homens|mulheres))\b~iu',
        'Possível exclusão por idade' => '~\b(?:idade m[aá]xima|at[eé] \d{2} anos|menores de \d{2} anos n[aã]o)\b~iu',
        'Possível exclusão por deficiência' => '~\b(?:sem deficientes|n[aã]o (?:aceitamos|contratamos) (?:pcd|pessoas com defici[eê]ncia))\b~iu',
        'Exigência subjetiva que merece revisão' => '~\b(?:boa apar[eê]ncia|apar[eê]ncia impec[aá]vel)\b~iu',
    ];

    public static function requested(string $message): bool
    {
        $normalized = mb_strtolower($message, 'UTF-8');
        $normalized = strtr($normalized, ['á'=>'a','à'=>'a','â'=>'a','ã'=>'a','é'=>'e','ê'=>'e','í'=>'i','ó'=>'o','ô'=>'o','õ'=>'o','ú'=>'u','ç'=>'c']);
        return preg_match('~\b(?:vaga|vagas|anuncio|anuncios)\b~u', $normalized) === 1
            && preg_match('~\b(?:ofensiv\w*|inapropriad\w*|inadequad\w*|discriminatori\w*|preconceituos\w*|abusiv\w*|assedi\w*|toxic\w*|impropri\w*)\b~u', $normalized) === 1
            && preg_match('~\b(?:verifi\w*|analis\w*|revis\w*|procur\w*|busc\w*|detect\w*|encontr\w*|identific\w*|varr\w*|examin\w*|exist\w*|tem|ha)\b~u', $normalized) === 1;
    }

    /** @return list<array{categoria:string,trecho:string,campo:string}> */
    public static function reviewText(string $title, string $description): array
    {
        $matches = [];
        foreach (['título' => $title, 'descrição' => $description] as $field => $text) {
            foreach (self::RULES as $label => $regex) {
                if (preg_match($regex, $text, $found, PREG_OFFSET_CAPTURE) !== 1) {
                    continue;
                }
                $byteOffset = (int) $found[0][1];
                $start = max(0, mb_strlen(substr($text, 0, $byteOffset), 'UTF-8') - 55);
                $snippet = mb_substr($text, $start, 150, 'UTF-8');
                $snippet = trim(preg_replace('/\s+/u', ' ', $snippet) ?? '');
                $matches[] = [
                    'categoria' => $label,
                    'trecho' => $snippet,
                    'campo' => $field,
                ];
            }
        }
        return $matches;
    }

    public static function scan(mysqli $db): array
    {
        $total = (int) $db->query('SELECT COUNT(*) FROM vagas')->fetch_row()[0];
        $cursor = PHP_INT_MAX;
        $scanned = 0;
        $flagged = 0;
        $results = [];
        $statement = $db->prepare(
            'SELECT id_vaga, titulo, descricao FROM vagas '
            . 'WHERE id_vaga < ? ORDER BY id_vaga DESC LIMIT ' . self::BATCH_SIZE
        );
        try {
            while ($scanned < self::MAX_SCANNED) {
                $statement->bind_param('i', $cursor);
                $statement->execute();
                $rows = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
                if ($rows === []) {
                    break;
                }
                foreach ($rows as $row) {
                    $cursor = (int) $row['id_vaga'];
                    $scanned++;
                    $indicators = self::reviewText((string) $row['titulo'], (string) ($row['descricao'] ?? ''));
                    if ($indicators !== []) {
                        $flagged++;
                        if (count($results) < self::MAX_REPORTED) {
                            $results[] = [
                                'id' => $cursor,
                                'titulo' => mb_substr((string) $row['titulo'], 0, 100, 'UTF-8'),
                                'indicadores' => array_slice($indicators, 0, 3),
                            ];
                        }
                    }
                }
                if (count($rows) < self::BATCH_SIZE) {
                    break;
                }
            }
        } finally {
            $statement->close();
        }
        return ['total' => $total, 'analisadas' => $scanned, 'sinalizadas' => $flagged,
            'amostra' => $results, 'parcial' => $scanned < $total];
    }

    public static function format(array $scan): string
    {
        $out = 'Triagem de linguagem de vagas (consulta real ao MySQL, somente leitura).' . "\n"
            . 'Vagas verificadas: ' . $scan['analisadas'] . ' de ' . $scan['total']
            . '. Com indícios: ' . $scan['sinalizadas'] . '.' . "\n";
        if ($scan['parcial']) {
            $out .= 'ATENÇÃO: varredura parcial; limite de 2.000 vagas por solicitação. '
                . 'Não é possível concluir sobre as demais.' . "\n";
        }
        if ($scan['sinalizadas'] === 0) {
            $out .= 'Nenhum termo das regras atuais foi encontrado nas vagas verificadas. '
                . 'Isso NÃO comprova ausência de conteúdo ofensivo ou discriminatório.' . "\n";
        }
        foreach ($scan['amostra'] as $job) {
            $out .= "\n" . 'Vaga #' . $job['id'] . ' — ' . $job['titulo'] . "\n";
            foreach ($job['indicadores'] as $indicator) {
                $out .= '- ' . $indicator['categoria'] . ' (' . $indicator['campo']
                    . '): “' . $indicator['trecho'] . '”' . "\n";
            }
        }
        if ($scan['sinalizadas'] > count($scan['amostra'])) {
            $out .= "\n" . 'Exibidas apenas as primeiras ' . count($scan['amostra'])
                . ' vagas sinalizadas para manter o relatório legível.' . "\n";
        }
        return $out . "\n" . 'Atenção: triagem por expressões, sujeita a falsos positivos e negativos. '
            . 'Examine o contexto e a legalidade com revisão humana. '
            . 'Nenhuma vaga foi editada, suspensa ou excluída.';
    }
}
