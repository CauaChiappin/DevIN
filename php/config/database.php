<?php

function getDatabaseConnection(): mysqli
{
    $host = "localhost";
    $user = "root";
    $pass = "";
    $dbname = "devin";

    $conn = new mysqli($host, $user, $pass, $dbname);

    if ($conn->connect_error) {
        throw new RuntimeException("Falha na conexao com o banco de dados.");
    }

    $conn->set_charset("utf8mb4");

    return $conn;
}
