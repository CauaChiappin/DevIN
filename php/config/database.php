<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/config/database.php
 * Finalidade: database.
 * Domínio: processamento no servidor.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


// Garante que o .env seja carregado antes de tentar ler as variáveis
require_once __DIR__ . '/auth.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

// Detalhe DevIN: Método getDatabaseConnection: consulta e organiza dados sem presumir que o resultado seja único no contexto de database. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function getDatabaseConnection(): mysqli // Explicação DevIN: Declara função/método getDatabaseConnection, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    /* Resolve credenciais primeiro nas variáveis modernas, depois nos nomes legados e por fim
     * nos valores padrão. Assim o mesmo código funciona em instalações locais e hospedadas. */
    // Aceita nomes atuais e legados das variáveis para manter compatibilidade entre ambientes.
    $environmentValue = static function (string $name, string $legacyName, string $default): string { // Explicação DevIN: Define função anônima usada como operação ou callback.
        $value = $_ENV[$name] ?? $_SERVER[$name] ?? getenv($name); // Explicação DevIN: Atribui um valor à variável PHP $value.
        if ($value === false || $value === null || $value === '') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $value = $_ENV[$legacyName] ?? $_SERVER[$legacyName] ?? getenv($legacyName); // Explicação DevIN: Atribui um valor à variável PHP $value.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        return $value === false || $value === null || $value === '' ? $default : (string) $value; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    $host   = $environmentValue('DB_HOST', 'DEVIN_DB_HOST', 'localhost'); // Explicação DevIN: Atribui um valor à variável PHP $host.
    $user   = $environmentValue('DB_USER', 'DEVIN_DB_USER', 'root'); // Explicação DevIN: Atribui um valor à variável PHP $user.
    $pass   = $environmentValue('DB_PASS', 'DEVIN_DB_PASS', ''); // Explicação DevIN: Atribui um valor à variável PHP $pass.
    $dbname = $environmentValue('DB_NAME', 'DEVIN_DB_NAME', 'devin'); // Explicação DevIN: Atribui um valor à variável PHP $dbname.
    $port   = (int) $environmentValue('DB_PORT', 'DEVIN_DB_PORT', '3306'); // Explicação DevIN: Atribui um valor à variável PHP $port.

    // Faz erros do MySQL virarem exceções, permitindo tratamento uniforme pelo chamador.
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.

    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        // Centraliza a criação da conexão para que páginas usem a mesma configuração e charset.
        $conn = new mysqli($host, $user, $pass, $dbname, $port); // Explicação DevIN: Atribui um valor à variável PHP $conn.
        // Garante suporte a acentos e caracteres Unicode em consultas e resultados.
        $conn->set_charset('utf8mb4'); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        return $conn; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } catch (mysqli_sql_exception $e) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        error_log('Falha de conexão com o banco DevIN: ' . $e->getMessage()); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        throw new RuntimeException('Falha na conexão com o banco de dados.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
