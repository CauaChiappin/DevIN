<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

/**
 * CPF/CNPJ: valida dígitos verificadores, NÃO identidade nem situação cadastral.
 * Nunca consulta se outro usuário tem determinado documento; prevenir enumeração.
 * CEP: somente formato + existência no ViaCEP; não é geolocalização exata.
 */
final class BrazilianRegistry // Explicação DevIN: Declara o tipo BrazilianRegistry e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    public static function digits(string $raw): string // Explicação DevIN: Declara função/método digits, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return preg_replace('/\D+/', '', $raw) ?? ''; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function cpf(string $raw): bool // Explicação DevIN: Declara função/método cpf, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $n=self::digits($raw); // Explicação DevIN: Atribui um valor à variável PHP $n.
        if (strlen($n)!==11 || preg_match('/^(\d)\1{10}$/',$n)) return false; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        for ($length=9;$length<=10;$length++) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            $sum=0; // Explicação DevIN: Atribui um valor à variável PHP $sum.
            for($i=0;$i<$length;$i++) $sum += (int)$n[$i]*($length+1-$i); // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            $digit=($sum*10)%11; // Explicação DevIN: Atribui um valor à variável PHP $digit.
            if ($digit===10) $digit=0; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            if ($digit !== (int)$n[$length]) return false; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return true; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function cnpj(string $raw): bool // Explicação DevIN: Declara função/método cnpj, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $n=self::digits($raw); // Explicação DevIN: Atribui um valor à variável PHP $n.
        // O algoritmo numérico não comprova autenticidade nem situação cadastral.
        if(strlen($n)!==14 || preg_match('/^(\d)\1{13}$/',$n)) return false; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        foreach ([[5,4,3,2,9,8,7,6,5,4,3,2],[6,5,4,3,2,9,8,7,6,5,4,3,2]] as $index=>$weights) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            $sum=0; // Explicação DevIN: Atribui um valor à variável PHP $sum.
            foreach($weights as $i=>$weight) $sum+=(int)$n[$i]*$weight; // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            $r=$sum%11; // Explicação DevIN: Atribui um valor à variável PHP $r.
            $check=$r<2?0:11-$r; // Explicação DevIN: Atribui um valor à variável PHP $check.
            if ((int)$n[12+$index]!==$check) return false; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return true; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function cepFormat(string $raw): bool // Explicação DevIN: Declara função/método cepFormat, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return preg_match('/^\d{8}$/', self::digits($raw)) === 1; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function lookupCep(string $raw): ?array // Explicação DevIN: Declara função/método lookupCep, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $cep=self::digits($raw); // Explicação DevIN: Atribui um valor à variável PHP $cep.
        if (!self::cepFormat($cep)) return null; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if (!function_exists('curl_init')) return null; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        // Host e destino fixos, sem redirecionamentos: previne SSRF.
        $curl=curl_init('https://viacep.com.br/ws/'.$cep.'/json/'); // Explicação DevIN: Atribui um valor à variável PHP $curl.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            curl_setopt_array($curl,[ // Explicação DevIN: Continua expressão ou bloco pertencente a função lookupCep.
                CURLOPT_RETURNTRANSFER=>true, // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                CURLOPT_CONNECTTIMEOUT=>3, // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                CURLOPT_TIMEOUT=>6, // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                CURLOPT_FOLLOWLOCATION=>false, // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                CURLOPT_SSL_VERIFYPEER=>true, // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                CURLOPT_SSL_VERIFYHOST=>2, // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                CURLOPT_HTTPHEADER=>['Accept: application/json'], // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            ]); // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
            $response=curl_exec($curl); // Explicação DevIN: Atribui um valor à variável PHP $response.
            $status=(int)curl_getinfo($curl,CURLINFO_RESPONSE_CODE); // Explicação DevIN: Atribui um valor à variável PHP $status.
            if (!is_string($response)||strlen($response)>20000||$status!==200) return null; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $data=json_decode($response,true); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
            if (!is_array($data)||!empty($data['erro'])) return null; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            return ['cep'=>$cep,'city'=>mb_substr((string)($data['localidade']??''),0,100), // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
                'state'=>substr((string)($data['uf']??''),0,2), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'district'=>mb_substr((string)($data['bairro']??''),0,120)]; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
            curl_close($curl); // Explicação DevIN: Continua expressão ou bloco pertencente a função lookupCep.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
