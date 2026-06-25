<?php

function getDatabaseConnection(): mysqli // define uma função que retorna tipo mysqli, que é a classe do PHP para trabalhar com banco de dados MySQL.
{
    $host = "localhost";  // talvez devemos mudar as variaveis de ambiente para variaveis de ambiente, para nao deixar exposto no codigo fonte (ex: $_ENV['DB_HOST']), mas por enquanto vamos deixar assim.
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
