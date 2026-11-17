<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/config/RateLimiter.php
 * Finalidade: rate limiter.
 * Domínio: processamento no servidor.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


/**
 * Lightweight fixed-window limiter shared by PHP requests on this host.
 * The key is hashed before it is used as a filename, so email addresses and
 * client IPs are not written to disk in plain text.
 */
final class RateLimiter // Explicação DevIN: Declara o tipo RateLimiter e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    // Detalhe DevIN: Método consume: encapsula uma responsabilidade reutilizável do módulo no contexto de rate limiter. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function consume(string $key, int $limit, int $windowSeconds): int // Explicação DevIN: Declara função/método consume, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        /* Conta chamadas em uma janela fixa. O arquivo é travado durante leitura e gravação
         * para que requisições simultâneas não percam incrementos da contagem. */
        // Cada chave tem seu próprio arquivo; o hash não deixa IPs ou e-mails visíveis no nome.
        $directory = dirname(__DIR__, 2) . '/storage/rate-limits'; // Explicação DevIN: Atribui um valor à variável PHP $directory.
        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new RuntimeException('Rate limiter storage is unavailable.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $path = $directory . '/' . hash('sha256', $key) . '.json'; // Explicação DevIN: Atribui um valor à variável PHP $path.
        $handle = fopen($path, 'c+'); // Explicação DevIN: Atribui um valor à variável PHP $handle.
        if ($handle === false || !flock($handle, LOCK_EX)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            if (is_resource($handle)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                fclose($handle); // Explicação DevIN: Continua expressão ou bloco pertencente a função consume.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            throw new RuntimeException('Rate limiter storage is unavailable.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            // O bloqueio exclusivo evita que duas requisições alterem a contagem ao mesmo tempo.
            rewind($handle); // Explicação DevIN: Continua expressão ou bloco pertencente a função consume.
            $contents = stream_get_contents($handle); // Explicação DevIN: Atribui um valor à variável PHP $contents.
            $record = is_string($contents) ? json_decode($contents, true) : null; // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
            $now = time(); // Explicação DevIN: Atribui um valor à variável PHP $now.

            if (!is_array($record) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                || !isset($record['started'], $record['count']) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                || !is_int($record['started']) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                || !is_int($record['count']) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                || $record['started'] + $windowSeconds <= $now // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            ) { // Explicação DevIN: Continua expressão ou bloco pertencente a função consume.
                $record = ['started' => $now, 'count' => 0]; // Explicação DevIN: Atribui um valor à variável PHP $record.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $retryAfter = max(0, $record['started'] + $windowSeconds - $now); // Explicação DevIN: Atribui um valor à variável PHP $retryAfter.
            // Retorna segundos restantes quando o limite já foi atingido; zero significa permitido.
            if ($record['count'] >= $limit) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                return $retryAfter; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $record['count']++; // Explicação DevIN: Continua expressão ou bloco pertencente a função consume.
            rewind($handle); // Explicação DevIN: Continua expressão ou bloco pertencente a função consume.
            ftruncate($handle, 0); // Explicação DevIN: Continua expressão ou bloco pertencente a função consume.
            fwrite($handle, json_encode($record, JSON_THROW_ON_ERROR)); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
            fflush($handle); // Explicação DevIN: Continua expressão ou bloco pertencente a função consume.

            return 0; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
            flock($handle, LOCK_UN); // Explicação DevIN: Continua expressão ou bloco pertencente a função consume.
            fclose($handle); // Explicação DevIN: Continua expressão ou bloco pertencente a função consume.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Método clear: encapsula uma responsabilidade reutilizável do módulo no contexto de rate limiter. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function clear(string $key): void // Explicação DevIN: Declara função/método clear, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // Uma autenticação bem-sucedida pode limpar a contagem para que falhas antigas não mantenham o usuário bloqueado.
        $path = dirname(__DIR__, 2) . '/storage/rate-limits/' . hash('sha256', $key) . '.json'; // Explicação DevIN: Atribui um valor à variável PHP $path.
        if (is_file($path)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            @unlink($path); // Explicação DevIN: Continua expressão ou bloco pertencente a função clear.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Método clientIp: encapsula uma responsabilidade reutilizável do módulo no contexto de rate limiter. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function clientIp(): string // Explicação DevIN: Declara função/método clientIp, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // Usa REMOTE_ADDR, que o servidor define, em vez de confiar em cabeçalhos encaminhados pelo cliente.
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'); // Explicação DevIN: Atribui um valor à variável PHP $ip.
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'unknown'; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
