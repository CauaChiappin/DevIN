<?php
declare(strict_types=1);

/**
 * CPF/CNPJ: valida dígitos verificadores, NÃO identidade nem situação cadastral.
 * Nunca consulta se outro usuário tem determinado documento; prevenir enumeração.
 * CEP: somente formato + existência no ViaCEP; não é geolocalização exata.
 */
final class BrazilianRegistry
{
    public static function digits(string $raw): string
    {
        return preg_replace('/\D+/', '', $raw) ?? '';
    }

    public static function cpf(string $raw): bool
    {
        $n=self::digits($raw);
        if (strlen($n)!==11 || preg_match('/^(\d)\1{10}$/',$n)) return false;
        for ($length=9;$length<=10;$length++) {
            $sum=0;
            for($i=0;$i<$length;$i++) $sum += (int)$n[$i]*($length+1-$i);
            $digit=($sum*10)%11;
            if ($digit===10) $digit=0;
            if ($digit !== (int)$n[$length]) return false;
        }
        return true;
    }

    public static function cnpj(string $raw): bool
    {
        $n=self::digits($raw);
        // O algoritmo numérico não comprova autenticidade nem situação cadastral.
        if(strlen($n)!==14 || preg_match('/^(\d)\1{13}$/',$n)) return false;
        foreach ([[5,4,3,2,9,8,7,6,5,4,3,2],[6,5,4,3,2,9,8,7,6,5,4,3,2]] as $index=>$weights) {
            $sum=0;
            foreach($weights as $i=>$weight) $sum+=(int)$n[$i]*$weight;
            $r=$sum%11;
            $check=$r<2?0:11-$r;
            if ((int)$n[12+$index]!==$check) return false;
        }
        return true;
    }

    public static function cepFormat(string $raw): bool
    {
        return preg_match('/^\d{8}$/', self::digits($raw)) === 1;
    }

    public static function lookupCep(string $raw): ?array
    {
        $cep=self::digits($raw);
        if (!self::cepFormat($cep)) return null;
        if (!function_exists('curl_init')) return null;
        // Host e destino fixos, sem redirecionamentos: previne SSRF.
        $curl=curl_init('https://viacep.com.br/ws/'.$cep.'/json/');
        try {
            curl_setopt_array($curl,[
                CURLOPT_RETURNTRANSFER=>true,
                CURLOPT_CONNECTTIMEOUT=>3,
                CURLOPT_TIMEOUT=>6,
                CURLOPT_FOLLOWLOCATION=>false,
                CURLOPT_SSL_VERIFYPEER=>true,
                CURLOPT_SSL_VERIFYHOST=>2,
                CURLOPT_HTTPHEADER=>['Accept: application/json'],
            ]);
            $response=curl_exec($curl);
            $status=(int)curl_getinfo($curl,CURLINFO_RESPONSE_CODE);
            if (!is_string($response)||strlen($response)>20000||$status!==200) return null;
            $data=json_decode($response,true);
            if (!is_array($data)||!empty($data['erro'])) return null;
            return ['cep'=>$cep,'city'=>mb_substr((string)($data['localidade']??''),0,100),
                'state'=>substr((string)($data['uf']??''),0,2),
                'district'=>mb_substr((string)($data['bairro']??''),0,120)];
        } finally {
            curl_close($curl);
        }
    }
}
