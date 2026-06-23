<?php
// rotas.php

// 1. Pega a URL que o usuário tentou acessar
$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 2. CORREÇÃO DE PASTA (XAMPP / Wamp)
// Se no seu navegador você acessa como "localhost/DevIN/...", tire as duas barras da linha abaixo:
$url = str_replace('/DevIN', '', $url);


// 3. Sistema de rotas para o DevIN
switch ($url) {
    
    // Rota da Página Inicial (Home/Index)
    case '/':
    case '/index':
    case '/index.html':
    case '/index.php':
    require __DIR__ . '/index.html'; // Quando colocar em php, mude aqui para php.
        break;

    // Rota da Página de Login
    case '/login':
    case '/login.html':
    case '/login.php':
        require __DIR__ . '/login.html';
        break;

    // Rota de Cadastro de Pessoa Física
    case '/cadastro-pessoa':
    case '/cadastro_pessoa.html':
    case '/cadastro_pessoa.php':
        require __DIR__ . '/cadastro_pessoa.html';
        break;

    // Rota de Cadastro de Empresa
    case '/cadastro-empresa':
    case '/cadastro_empresa.html':
    case '/cadastro_empresa.php':
        require __DIR__ . '/cadastro_empresa.html';
        break;

    // Se o usuário digitar qualquer outra coisa, dá erro 404
    default:
        http_response_code(404);
        echo "<h1 style='text-align:center; margin-top:50px; font-family:sans-serif;'>Erro 404: Página não encontrada!</h1>";
        break;
}