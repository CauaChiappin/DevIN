<?php
declare(strict_types=1);

/**
 * Idioma JARVIS: utiliza preferências persistidas por ADM (não o navegador).
 * Esta classe traduz mensagens fixas, rótulos e a instrução do modelo remoto.
 */
final class JarvisLocale
{
    public const SUPPORTED = ['pt-BR','en','es'];

    public static function normalize(string $code): string
    {
        return in_array($code, self::SUPPORTED, true) ? $code : 'pt-BR';
    }

    public static function instruction(string $code): string
    {
        return match (self::normalize($code)) {
            'en' => 'Reply entirely in English. Preserve proper names, quotes, record IDs and commands ENVIAR/EXCLUIR exactly.',
            'es' => 'Responde íntegramente en español. Conserva nombres propios, citas, identificadores y comandos ENVIAR/EXCLUIR.',
            default => 'Responda integralmente em português brasileiro e preserve nomes, IDs e confirmações ENVIAR/EXCLUIR.',
        };
    }

    public static function summary(string $code, array $counts): string
    {
        $labels = [
            'pt-BR'=>['title'=>'Resumo atual do DevIN','Candidatos'=>'Candidatos','Empresas'=>'Empresas','Vagas'=>'Vagas','Denúncias de vagas pendentes'=>'Denúncias de vagas pendentes','Denúncias de perfis pendentes'=>'Denúncias de perfis pendentes','indisponível'=>'indisponível'],
            'en'=>['title'=>'Current DevIN summary','Candidatos'=>'Candidates','Empresas'=>'Companies','Vagas'=>'Jobs','Denúncias de vagas pendentes'=>'Pending job reports','Denúncias de perfis pendentes'=>'Pending profile reports','indisponível'=>'unavailable'],
            'es'=>['title'=>'Resumen actual de DevIN','Candidatos'=>'Candidatos','Empresas'=>'Empresas','Vagas'=>'Vacantes','Denúncias de vagas pendentes'=>'Denuncias de vacantes pendientes','Denúncias de perfis pendentes'=>'Denuncias de perfiles pendientes','indisponível'=>'no disponible'],
        ];
        $dict=$labels[self::normalize($code)];
        $lines=[];
        foreach ($counts as $label=>$value) {
            $lines[] = ($dict[$label] ?? $label) . ': ' . (is_numeric($value) ? (string)$value : ($dict[$value] ?? $value));
        }
        return $dict['title'] . ":\n" . implode("\n",$lines);
    }
}
