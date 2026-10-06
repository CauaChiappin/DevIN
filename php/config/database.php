<?php

declare(strict_types=1);

// Garante que o .env seja carregado antes de tentar ler as variáveis
require_once __DIR__ . '/auth.php';

function getDatabaseConnection(): mysqli
{
    $environmentValue = static function (string $name, string $legacyName, string $default): string {
        $value = $_ENV[$name] ?? $_SERVER[$name] ?? getenv($name);
        if ($value === false || $value === null || $value === '') {
            $value = $_ENV[$legacyName] ?? $_SERVER[$legacyName] ?? getenv($legacyName);
        }

        return $value === false || $value === null || $value === '' ? $default : (string) $value;
    };

    $host   = $environmentValue('DB_HOST', 'DEVIN_DB_HOST', 'localhost');
    $user   = $environmentValue('DB_USER', 'DEVIN_DB_USER', 'root');
    $pass   = $environmentValue('DB_PASS', 'DEVIN_DB_PASS', '');
    $dbname = $environmentValue('DB_NAME', 'DEVIN_DB_NAME', 'devin');
    $port   = (int) $environmentValue('DB_PORT', 'DEVIN_DB_PORT', '3306');

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    try {
        $conn = new mysqli($host, $user, $pass, $dbname, $port);
        $conn->set_charset('utf8mb4');
        return $conn;
    } catch (mysqli_sql_exception $e) {
        error_log('Falha de conexão com o banco DevIN: ' . $e->getMessage());
        throw new RuntimeException('Falha na conexão com o banco de dados.');
    }
}
