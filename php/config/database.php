<?php

declare(strict_types=1);

function getDatabaseConnection(): mysqli
{
    $host = getenv('DEVIN_DB_HOST') ?: 'localhost';
    $user = getenv('DEVIN_DB_USER') ?: 'root';
    $pass = getenv('DEVIN_DB_PASS') ?: '';
    $dbname = getenv('DEVIN_DB_NAME') ?: 'devin';
    $port = (int) (getenv('DEVIN_DB_PORT') ?: 3306);

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
