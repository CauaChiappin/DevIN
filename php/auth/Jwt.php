<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
/**
 * REVISÃO DETALHADA DEVIN — php/auth/Jwt.php
 * Finalidade: jwt.
 * Domínio: processamento no servidor.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


require_once __DIR__ . '/../config/auth.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

class Jwt // Explicação DevIN: Declara o tipo Jwt e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    // Detalhe DevIN: Método encode: encapsula uma responsabilidade reutilizável do módulo no contexto de jwt. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function encode(array $payload, string $secret): string // Explicação DevIN: Declara função/método encode, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        /* Serializa cabeçalho e dados em Base64 URL-safe e assina as duas partes com HMAC-SHA256.
         * A assinatura detecta alterações, mas não criptografa o conteúdo do payload. */
        $header = [ // Explicação DevIN: Atribui um valor à variável PHP $header.
            'alg' => 'HS256', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'typ' => 'JWT', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.

        $headerJson = json_encode( // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
            $header, // Explicação DevIN: Continua expressão ou bloco pertencente a função encode.
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES // Explicação DevIN: Continua expressão ou bloco pertencente a função encode.
        ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $payloadJson = json_encode( // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
            $payload, // Explicação DevIN: Continua expressão ou bloco pertencente a função encode.
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES // Explicação DevIN: Continua expressão ou bloco pertencente a função encode.
        ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if ($headerJson === false || $payloadJson === false) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new RuntimeException('Erro ao criar o token JWT.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $headerEncoded = self::base64UrlEncode($headerJson); // Explicação DevIN: Atribui um valor à variável PHP $headerEncoded.
        $payloadEncoded = self::base64UrlEncode($payloadJson); // Explicação DevIN: Atribui um valor à variável PHP $payloadEncoded.

        // Assina header e payload juntos para detectar qualquer alteração no token.
        $signature = hash_hmac( // Explicação DevIN: Atribui um valor à variável PHP $signature.
            'sha256', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            $headerEncoded . '.' . $payloadEncoded, // Explicação DevIN: Continua expressão ou bloco pertencente a função encode.
            $secret, // Explicação DevIN: Continua expressão ou bloco pertencente a função encode.
            true // Explicação DevIN: Continua expressão ou bloco pertencente a função encode.
        ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $signatureEncoded = self::base64UrlEncode($signature); // Explicação DevIN: Atribui um valor à variável PHP $signatureEncoded.

        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Método decode: encapsula uma responsabilidade reutilizável do módulo no contexto de jwt. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function decode(string $token, string $secret): array // Explicação DevIN: Declara função/método decode, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // Um JWT tem exatamente três partes separadas por ponto: cabeçalho, dados e assinatura.
        $parts = explode('.', $token); // Explicação DevIN: Atribui um valor à variável PHP $parts.

        if (count($parts) !== 3) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new RuntimeException('Token inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        [ // Explicação DevIN: Continua expressão ou bloco pertencente a função decode.
            $headerEncoded, // Explicação DevIN: Continua expressão ou bloco pertencente a função decode.
            $payloadEncoded, // Explicação DevIN: Continua expressão ou bloco pertencente a função decode.
            $signatureEncoded // Explicação DevIN: Continua expressão ou bloco pertencente a função decode.
        ] = $parts; // Explicação DevIN: Continua expressão ou bloco pertencente a função decode.

        if ( // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $headerEncoded === '' || // Explicação DevIN: Atribui um valor à variável PHP $headerEncoded.
            $payloadEncoded === '' || // Explicação DevIN: Atribui um valor à variável PHP $payloadEncoded.
            $signatureEncoded === '' // Explicação DevIN: Atribui um valor à variável PHP $signatureEncoded.
        ) { // Explicação DevIN: Continua expressão ou bloco pertencente a função decode.
            throw new RuntimeException('Token JWT inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        // Recalcula a assinatura esperada com o segredo do servidor antes de confiar nos claims.
        $signature = self::base64UrlDecode($signatureEncoded); // Explicação DevIN: Atribui um valor à variável PHP $signature.

        $expectedSignature = hash_hmac( // Explicação DevIN: Atribui um valor à variável PHP $expectedSignature.
            'sha256', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            $headerEncoded . '.' . $payloadEncoded, // Explicação DevIN: Continua expressão ou bloco pertencente a função decode.
            $secret, // Explicação DevIN: Continua expressão ou bloco pertencente a função decode.
            true // Explicação DevIN: Continua expressão ou bloco pertencente a função decode.
        ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        // Só interpreta os dados depois de confirmar a assinatura com comparação segura.
        // Detalhe DevIN: Compara hashes ou segredos sem diferenças de tempo evitáveis.
        if (!hash_equals($expectedSignature, $signature)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new RuntimeException('Assinatura do token inválida.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $headerJson = self::base64UrlDecode($headerEncoded); // Explicação DevIN: Atribui um valor à variável PHP $headerJson.
        $payloadJson = self::base64UrlDecode($payloadEncoded); // Explicação DevIN: Atribui um valor à variável PHP $payloadJson.

        $header = json_decode($headerJson, true); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
        $payload = json_decode($payloadJson, true); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.

        if (!is_array($header)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new RuntimeException('Header do token inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if (!is_array($payload)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new RuntimeException('Payload do token inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if ( // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            !isset($header['alg']) || // Explicação DevIN: Continua expressão ou bloco pertencente a função decode.
            $header['alg'] !== 'HS256' // Explicação DevIN: Continua expressão ou bloco pertencente a função decode.
        ) { // Explicação DevIN: Continua expressão ou bloco pertencente a função decode.
            throw new RuntimeException('Algoritmo do token inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if ( // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            !isset($header['typ']) || // Explicação DevIN: Continua expressão ou bloco pertencente a função decode.
            $header['typ'] !== 'JWT' // Explicação DevIN: Continua expressão ou bloco pertencente a função decode.
        ) { // Explicação DevIN: Continua expressão ou bloco pertencente a função decode.
            throw new RuntimeException('Tipo do token inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        // Confere emissor, campos obrigatórios e datas para rejeitar token alterado ou expirado.
        // Detalhe DevIN: Compara hashes ou segredos sem diferenças de tempo evitáveis.
        if (!isset($payload['iss']) || !hash_equals(JWT_ISSUER, (string) $payload['iss'])) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new RuntimeException('Emissor do token inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if (!isset($payload['iat'], $payload['exp'], $payload['sub'])) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new RuntimeException('Claims obrigatórias ausentes no token.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $now = time(); // Explicação DevIN: Atribui um valor à variável PHP $now.
        if ((int) $payload['iat'] > $now + 30) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new RuntimeException('Token com data inválida.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if ($now >= (int) $payload['exp']) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new RuntimeException('Token expirado.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        return $payload; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Método base64UrlEncode: encapsula uma responsabilidade reutilizável do módulo no contexto de jwt. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    private static function base64UrlEncode(string $data): string // Explicação DevIN: Declara função/método base64UrlEncode, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // JWT troca caracteres do Base64 padrão e remove padding para ser seguro em URLs e cabeçalhos.
        return rtrim( // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            strtr(base64_encode($data), '+/', '-_'), // Explicação DevIN: Continua expressão ou bloco pertencente a função base64UrlEncode.
            '=' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Método base64UrlDecode: encapsula uma responsabilidade reutilizável do módulo no contexto de jwt. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    private static function base64UrlDecode(string $data): string // Explicação DevIN: Declara função/método base64UrlDecode, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // Restaura o alfabeto e o padding exigido pelo decodificador estrito do PHP.
        $data = strtr($data, '-_', '+/'); // Explicação DevIN: Atribui um valor à variável PHP $data.

        $remainder = strlen($data) % 4; // Explicação DevIN: Atribui um valor à variável PHP $remainder.

        if ($remainder > 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $data .= str_repeat('=', 4 - $remainder); // Explicação DevIN: Continua expressão ou bloco pertencente a função base64UrlDecode.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $decoded = base64_decode($data, true); // Explicação DevIN: Atribui um valor à variável PHP $decoded.

        if ($decoded === false) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new RuntimeException('Base64 inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        return $decoded; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
