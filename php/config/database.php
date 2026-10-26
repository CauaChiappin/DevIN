<?php

declare(strict_types=1);

// Garante que o .env seja carregado antes de tentar ler as variáveis
require_once __DIR__ . '/auth.php';

function getDatabaseConnection(): mysqli
{
    /* Resolve credenciais primeiro nas variáveis modernas, depois nos nomes legados e por fim
     * nos valores padrão. Assim o mesmo código funciona em instalações locais e hospedadas. */
    // Aceita nomes atuais e legados das variáveis para manter compatibilidade entre ambientes.
    $environmentValue = static function (string $name, string $legacyName, string $default): string {
        $value = $_ENV[$name] ?? $_SERVER[$name] ?? getenv($name);
        if ($value === false || $value === null || $value === '') {
            $value = $_ENV[$legacyName] ?? $_SERVER[$legacyName] ?? getenv($legacyName);
        }

        return $value === false || $value === null || $value === '' ? $default : (string) $value;
    };

    $host   = $environmentValue('DB_HOST', 'DEVIN_DB_HOST', 'localhost');
    $user   = $environmentValue('DB_USER', 'DEVIN_DB_USER', 'u413309708_devin');
    $pass   = $environmentValue('DB_PASS', 'DEVIN_DB_PASS', 'Caje031019');
    $dbname = $environmentValue('DB_NAME', 'DEVIN_DB_NAME', 'u413309708_devin');
    $port   = (int) $environmentValue('DB_PORT', 'DEVIN_DB_PORT', '3306');

    // Faz erros do MySQL virarem exceções, permitindo tratamento uniforme pelo chamador.
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        // Centraliza a criação da conexão para que páginas usem a mesma configuração e charset.
        $conn = new mysqli($host, $user, $pass, $dbname, $port);
        // Garante suporte a acentos e caracteres Unicode em consultas e resultados.
        $conn->set_charset('utf8mb4');
        return $conn;
    } catch (mysqli_sql_exception $e) {
        error_log('Falha de conexão com o banco DevIN: ' . $e->getMessage());
        throw new RuntimeException('Falha na conexão com o banco de dados.');
    }
}
