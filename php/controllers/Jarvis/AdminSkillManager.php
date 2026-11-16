<?php
declare(strict_types=1);

/**
 * Adaptação pequena do SkillManager do JARVIS Code.
 * Somente orienta respostas; NUNCA executa ferramentas, SQL ou ações administrativas.
 */
final class AdminSkillManager
{
    private const MAX_ACTIVE = 2;

    private const CATALOG = [
        'reports' => [
            'label' => 'Triagem de denúncias',
            'keywords' => ['denuncia', 'denuncias', 'report', 'reports', 'fraude', 'fraudulenta', 'suspeita', 'moderacao'],
            'instruction' => 'Ajude a organizar evidências de denúncias. Não declare culpa nem tome decisões automáticas. Solicite verificação humana e não invente conteúdo ou acesso ao banco.',
        ],
        'jobs' => [
            'label' => 'Qualidade de vagas',
            'keywords' => ['vaga', 'vagas', 'anuncio', 'anuncios', 'salario', 'recrutamento'],
            'instruction' => 'Ajude a revisar clareza, completude e riscos de anúncios. Não altere vagas nem alegue ter consultado dados reais sem ferramenta.',
        ],
        'people' => [
            'label' => 'Suporte a usuários',
            'keywords' => ['candidato', 'candidatos', 'curriculo', 'perfil', 'perfis', 'cadastro', 'conta', 'empresa'],
            'instruction' => 'Oriente sobre atendimento e cadastros com respeito à privacidade, evitando julgamentos discriminatórios e dados pessoais desnecessários.',
        ],
        'metrics' => [
            'label' => 'Indicadores da plataforma',
            'keywords' => ['indicadores', 'estatisticas', 'metricas', 'relatorio', 'relatorios', 'resumo', 'totais'],
            'instruction' => 'Explique indicadores apenas quando dados reais forem apresentados. Para valores atuais, indique o botão Resumo do DevIN. Não fabrique números.',
        ],
    ];

    private static function normalize(string $value): string
    {
        $value = mb_strtolower($value, 'UTF-8');
        return strtr($value, [
            'á'=>'a', 'à'=>'a', 'ã'=>'a', 'â'=>'a', 'ä'=>'a',
            'é'=>'e', 'è'=>'e', 'ê'=>'e', 'ë'=>'e',
            'í'=>'i', 'ì'=>'i', 'î'=>'i', 'ï'=>'i',
            'ó'=>'o', 'ò'=>'o', 'õ'=>'o', 'ô'=>'o', 'ö'=>'o',
            'ú'=>'u', 'ù'=>'u', 'û'=>'u', 'ü'=>'u', 'ç'=>'c',
        ]);
    }

    /** @return list<array{id:string,label:string,instruction:string}> */
    public static function select(string $message): array
    {
        $tokens = preg_split('/[^a-z0-9]+/', self::normalize(mb_substr($message, 0, 3000, 'UTF-8')), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $tokens = array_unique(array_filter($tokens, static fn (string $word): bool => strlen($word) >= 4));
        $ranked = [];
        foreach (self::CATALOG as $id => $skill) {
            $hits = count(array_intersect($tokens, $skill['keywords']));
            if ($hits === 0) continue;
            // Trigger vocabularies are intentionally narrow and fixed; no user-supplied skill code.
            $ranked[] = ['score' => $hits, 'id' => $id, 'label' => $skill['label'], 'instruction' => $skill['instruction']];
        }
        usort($ranked, static fn (array $a, array $b): int => ($b['score'] <=> $a['score']) ?: strcmp($a['id'], $b['id']));
        return array_map(
            static fn (array $item): array => ['id' => $item['id'], 'label' => $item['label'], 'instruction' => $item['instruction']],
            array_slice($ranked, 0, self::MAX_ACTIVE)
        );
    }

    /** O gerente é sempre ativo, mesmo quando nenhuma habilidade é pertinente. */
    public static function prompt(array $selected): string
    {
        $base = 'Gerenciador administrativo ativo: ative no máximo duas orientações pertinentes por solicitação. '
            . 'Não execute habilidades como código, nem afirme ter feito pesquisas, consultas ou ações que não ocorreram. '
            . 'Evite divisão em múltiplas chamadas quando não reduzir custo.';
        foreach ($selected as $skill) {
            $base .= "\nHabilidade " . $skill['label'] . ': ' . $skill['instruction'];
        }
        return $base;
    }

    /** @return list<array{id:string,label:string}> */
    public static function labels(array $selected): array
    {
        return array_map(static fn (array $skill): array => ['id' => $skill['id'], 'label' => $skill['label']], $selected);
    }
}
