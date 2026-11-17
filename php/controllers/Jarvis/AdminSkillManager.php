<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/controllers/Jarvis/AdminSkillManager.php
 * Finalidade: admin skill manager.
 * Domínio: assistência administrativa.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


/**
 * MÓDULO DEVIN — ADMIN SKILL MANAGER.
 * Responsabilidade: parte do subsistema de orquestração das ferramentas e respostas do JARVIS para administradores.
 * Leia os métodos abaixo em sequência: entrada validada -> processamento ->
 * resposta ao chamador. Comentários detalhados descrevem decisões, limites
 * e efeitos colaterais; os comentários gerados por linha foram preservados.
 * Atenção: chamadas de banco e ações administrativas exigem autorização no
 * endpoint controlador; métodos isolados não constituem permissão para a IA.
 */


/**
 * Adaptação pequena do SkillManager do JARVIS Code.
 * Somente orienta respostas; NUNCA executa ferramentas, SQL ou ações administrativas.
 */
final class AdminSkillManager // Explicação DevIN: Declara o tipo AdminSkillManager e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    private const MAX_ACTIVE = 2; // Explicação DevIN: Define membro, visibilidade ou método de uma classe.

    private const CATALOG = [ // Explicação DevIN: Define membro, visibilidade ou método de uma classe.
        'reports' => [ // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'label' => 'Triagem de denúncias', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'keywords' => ['denuncia', 'denuncias', 'report', 'reports', 'fraude', 'fraudulenta', 'suspeita', 'moderacao'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'instruction' => 'Ajude a organizar evidências de denúncias. Não declare culpa nem tome decisões automáticas. Solicite verificação humana e não invente conteúdo ou acesso ao banco.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ], // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        'jobs' => [ // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'label' => 'Qualidade de vagas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'keywords' => ['vaga', 'vagas', 'anuncio', 'anuncios', 'salario', 'recrutamento'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'instruction' => 'Ajude a revisar clareza, completude e riscos de anúncios. Não altere vagas nem alegue ter consultado dados reais sem ferramenta.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ], // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        'people' => [ // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'label' => 'Suporte a usuários', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'keywords' => ['candidato', 'candidatos', 'curriculo', 'perfil', 'perfis', 'cadastro', 'conta', 'empresa'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'instruction' => 'Oriente sobre atendimento e cadastros com respeito à privacidade, evitando julgamentos discriminatórios e dados pessoais desnecessários.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ], // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        'metrics' => [ // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'label' => 'Indicadores da plataforma', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'keywords' => ['indicadores', 'estatisticas', 'metricas', 'relatorio', 'relatorios', 'resumo', 'totais'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'instruction' => 'Explique indicadores apenas quando dados reais forem apresentados. Para valores atuais, indique o botão Resumo do DevIN. Não fabrique números.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ], // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.

    // Detalhamento DevIN: normaliza a operação «normalize» no contexto de orquestração das ferramentas e respostas do JARVIS para administradores; os argumentos recebidos definem o alvo ou os dados processados, e o retorno/erro sinaliza o resultado ao chamador.
    // Entrada e saída: confira a assinatura abaixo; falhas devem ser tratadas pelo controlador que chamou esta rotina.
    // Detalhe DevIN: Método normalize: transforma ou normaliza dados para o formato esperado no contexto de admin skill manager. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    private static function normalize(string $value): string // Explicação DevIN: Declara função/método normalize, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $value = mb_strtolower($value, 'UTF-8'); // Explicação DevIN: Atribui um valor à variável PHP $value.
        return strtr($value, [ // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            'á'=>'a', 'à'=>'a', 'ã'=>'a', 'â'=>'a', 'ä'=>'a', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'é'=>'e', 'è'=>'e', 'ê'=>'e', 'ë'=>'e', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'í'=>'i', 'ì'=>'i', 'î'=>'i', 'ï'=>'i', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'ó'=>'o', 'ò'=>'o', 'õ'=>'o', 'ô'=>'o', 'ö'=>'o', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'ú'=>'u', 'ù'=>'u', 'û'=>'u', 'ü'=>'u', 'ç'=>'c', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ]); // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /** @return list<array{id:string,label:string,instruction:string}> */
    // Detalhamento DevIN: seleciona a operação «select» no contexto de orquestração das ferramentas e respostas do JARVIS para administradores; os argumentos recebidos definem o alvo ou os dados processados, e o retorno/erro sinaliza o resultado ao chamador.
    // Entrada e saída: confira a assinatura abaixo; falhas devem ser tratadas pelo controlador que chamou esta rotina.
    // Detalhe DevIN: Método select: encapsula uma responsabilidade reutilizável do módulo no contexto de admin skill manager. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function select(string $message): array // Explicação DevIN: Declara função/método select, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $tokens = preg_split('/[^a-z0-9]+/', self::normalize(mb_substr($message, 0, 3000, 'UTF-8')), -1, PREG_SPLIT_NO_EMPTY) ?: []; // Explicação DevIN: Atribui um valor à variável PHP $tokens.
        $tokens = array_unique(array_filter($tokens, static fn (string $word): bool => strlen($word) >= 4)); // Explicação DevIN: Atribui um valor à variável PHP $tokens.
        $ranked = []; // Explicação DevIN: Atribui um valor à variável PHP $ranked.
        foreach (self::CATALOG as $id => $skill) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            $hits = count(array_intersect($tokens, $skill['keywords'])); // Explicação DevIN: Atribui um valor à variável PHP $hits.
            if ($hits === 0) continue; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            // Trigger vocabularies are intentionally narrow and fixed; no user-supplied skill code.
            $ranked[] = ['score' => $hits, 'id' => $id, 'label' => $skill['label'], 'instruction' => $skill['instruction']]; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        usort($ranked, static fn (array $a, array $b): int => ($b['score'] <=> $a['score']) ?: strcmp($a['id'], $b['id'])); // Explicação DevIN: Continua expressão ou bloco pertencente a função select.
        return array_map( // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            static fn (array $item): array => ['id' => $item['id'], 'label' => $item['label'], 'instruction' => $item['instruction']], // Explicação DevIN: Continua expressão ou bloco pertencente a função select.
            array_slice($ranked, 0, self::MAX_ACTIVE) // Explicação DevIN: Continua expressão ou bloco pertencente a função select.
        ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /** O gerente é sempre ativo, mesmo quando nenhuma habilidade é pertinente. */
    // Detalhamento DevIN: centraliza a operação «prompt» no contexto de orquestração das ferramentas e respostas do JARVIS para administradores; os argumentos recebidos definem o alvo ou os dados processados, e o retorno/erro sinaliza o resultado ao chamador.
    // Entrada e saída: confira a assinatura abaixo; falhas devem ser tratadas pelo controlador que chamou esta rotina.
    // Detalhe DevIN: Método prompt: encapsula uma responsabilidade reutilizável do módulo no contexto de admin skill manager. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function prompt(array $selected): string // Explicação DevIN: Declara função/método prompt, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $base = 'Gerenciador administrativo ativo: ative no máximo duas orientações pertinentes por solicitação. ' // Explicação DevIN: Atribui um valor à variável PHP $base.
            . 'Não execute habilidades como código, nem afirme ter feito pesquisas, consultas ou ações que não ocorreram. ' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            . 'Evite divisão em múltiplas chamadas quando não reduzir custo.'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        foreach ($selected as $skill) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            $base .= "\nHabilidade " . $skill['label'] . ': ' . $skill['instruction']; // Explicação DevIN: Continua expressão ou bloco pertencente a função prompt.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return $base; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /** @return list<array{id:string,label:string}> */
    // Detalhamento DevIN: centraliza a operação «labels» no contexto de orquestração das ferramentas e respostas do JARVIS para administradores; os argumentos recebidos definem o alvo ou os dados processados, e o retorno/erro sinaliza o resultado ao chamador.
    // Entrada e saída: confira a assinatura abaixo; falhas devem ser tratadas pelo controlador que chamou esta rotina.
    // Detalhe DevIN: Método labels: encapsula uma responsabilidade reutilizável do módulo no contexto de admin skill manager. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function labels(array $selected): array // Explicação DevIN: Declara função/método labels, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return array_map(static fn (array $skill): array => ['id' => $skill['id'], 'label' => $skill['label']], $selected); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
