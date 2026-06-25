<?php

class Jwt
{
    public static function encode(array $payload /*Carrega os dados a serem codificados */, string $secret): string
    {
        $header = [ // header é um array que contem o algoritmo de assinatura e o tipo do token
            'alg' => 'HS256',
            'typ' => 'JWT',
        ];

        $headerEncoded = self::base64UrlEncode(json_encode($header)); // json_encode é uma função que converte um array em uma string json, e a função base64UrlEncode é uma função que codifica a string em base64 e o resultado O resultado fica sem +, / e sem = no final. Isso é necessário para usar o token em URLs e cabeçalhos HTTP.
        $payloadEncoded = self::base64UrlEncode(json_encode($payload)); // faz a mesma coisa que o header, mas para os dados do usuário. Depois trasnforma o array em json e codifica em base64.
        $signature = hash_hmac('sha256', $headerEncoded . '.' . $payloadEncoded, $secret, true); //Cria um HMAC(mecanismo de segurança para garantir a integridade do token) usando SHA-256. Usa a chave secreta $secret. O quarto argumento true pede saída em binário bruto. Isso garante que a assinatura seja segura e não possa ser facilmente falsificada.

        return $headerEncoded . '.' . $payloadEncoded . '.' . self::base64UrlEncode($signature); // Retorna o token JWT completo, que é a combinação do header, payload e assinatura, separados por pontos. A assinatura é codificada em base64 para garantir que seja segura para transporte.
    }

    public static function decode(string $token, string $secret): array // decodifica o token JWT, verificando a assinatura e a validade do token. Retorna o payload decodificado como um array associativo.
    {
        $parts = explode('.', $token); // divide o token em três partes.

        if (count($parts) !== 3) { // verifica se o token tem exatamente três partes
            throw new RuntimeException('Token invalido.');
        }

        [$headerEncoded, $payloadEncoded, $signatureEncoded] = $parts;
        $signature = self::base64UrlDecode($signatureEncoded);
        $expectedSignature = hash_hmac('sha256', $headerEncoded . '.' . $payloadEncoded, $secret, true);

        if (!hash_equals($expectedSignature, $signature)) { // hash_equals é uma função que compara duas strings de forma segura, evitando ataques de tempo. Se a assinatura do token não corresponder à assinatura esperada, lança uma exceção indicando que a assinatura do token é inválida.
            throw new RuntimeException('Assinatura do token invalida.');
        }

        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);

        if (!is_array($payload)) {
            throw new RuntimeException('Payload do token invalido.');
        }

        if (isset($payload['exp']) && time() >= (int) $payload['exp']) { // verifica se o token expirou. Se a data de expiração do token for menor ou igual ao tempo atual, lança uma exceção indicando que o token expirou.
            throw new RuntimeException('Token expirado.');
        }

        return $payload;
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); // gera um base64 seguro para URL, substituindo '+' por '-' e '/' por '_', e removendo '=' do final. Isso é necessário para usar o token em URLs e cabeçalhos HTTP.
    }

    private static function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4; // calcula o resto da divisão do tamanho da string por 4. Isso é necessário para garantir que a string tenha um comprimento válido para decodificação base64.

        if ($remainder > 0) {
            $data .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($data, '-_', '+/'));
    }
}
