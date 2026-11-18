<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

/**
 * Idioma JARVIS: utiliza preferências persistidas por ADM (não o navegador).
 * Esta classe traduz mensagens fixas, rótulos e a instrução do modelo remoto.
 */
final class JarvisLocale // Explicação DevIN: Declara o tipo JarvisLocale e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    public const SUPPORTED = ['pt-BR','en','es']; // Explicação DevIN: Define membro, visibilidade ou método de uma classe.

    public static function normalize(string $code): string // Explicação DevIN: Declara função/método normalize, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return in_array($code, self::SUPPORTED, true) ? $code : 'pt-BR'; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function instruction(string $code): string // Explicação DevIN: Declara função/método instruction, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return match (self::normalize($code)) { // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            'en' => 'Reply entirely in English. Preserve proper names, quotes, record IDs and commands ENVIAR/EXCLUIR exactly.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'es' => 'Responde íntegramente en español. Conserva nombres propios, citas, identificadores y comandos ENVIAR/EXCLUIR.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            default => 'Responda integralmente em português brasileiro e preserve nomes, IDs e confirmações ENVIAR/EXCLUIR.', // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function summary(string $code, array $counts): string // Explicação DevIN: Declara função/método summary, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $labels = [ // Explicação DevIN: Atribui um valor à variável PHP $labels.
            'pt-BR'=>['title'=>'Resumo atual do DevIN','Candidatos'=>'Candidatos','Empresas'=>'Empresas','Vagas'=>'Vagas','Denúncias de vagas pendentes'=>'Denúncias de vagas pendentes','Denúncias de perfis pendentes'=>'Denúncias de perfis pendentes','indisponível'=>'indisponível'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'en'=>['title'=>'Current DevIN summary','Candidatos'=>'Candidates','Empresas'=>'Companies','Vagas'=>'Jobs','Denúncias de vagas pendentes'=>'Pending job reports','Denúncias de perfis pendentes'=>'Pending profile reports','indisponível'=>'unavailable'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'es'=>['title'=>'Resumen actual de DevIN','Candidatos'=>'Candidatos','Empresas'=>'Empresas','Vagas'=>'Vacantes','Denúncias de vagas pendentes'=>'Denuncias de vacantes pendientes','Denúncias de perfis pendentes'=>'Denuncias de perfiles pendientes','indisponível'=>'no disponible'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
        $dict=$labels[self::normalize($code)]; // Explicação DevIN: Atribui um valor à variável PHP $dict.
        $lines=[]; // Explicação DevIN: Atribui um valor à variável PHP $lines.
        foreach ($counts as $label=>$value) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            $lines[] = ($dict[$label] ?? $label) . ': ' . (is_numeric($value) ? (string)$value : ($dict[$value] ?? $value)); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return $dict['title'] . ":\n" . implode("\n",$lines); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
