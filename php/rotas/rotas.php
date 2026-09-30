<?php

declare(strict_types=1);

// 1. Captura e limpa o caminho (PATH) da URL requisitada
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$urlPath = parse_url($requestUri, PHP_URL_PATH) ?? '/';

// 2. Normalização da URL
// - Remove prefixo da pasta do projeto caso rode em subpasta (ex: /DevIN)
$url = preg_replace('#^/DevIN#i', '', $urlPath);

// - Remove a barra final (exceto para a raiz '/') e converte para minúsculas
if ($url !== '/' && str_ends_with($url, '/')) {
    $url = rtrim($url, '/');
}
$url = strtolower($url);

// 3. Mapeamento de Rotas
switch ($url) {

    // --- HOME / PÁGINA INICIAL ---
    case '':
    case '/':
    case '/index':
    case '/index.php':
    case '/index.html':
        require __DIR__ . '/../index.php';
        break;

    // --- AUTENTICAÇÃO ---
    case '/login':
    case '/login.php':
    case '/login.html':
        require __DIR__ . '/login.php';
        break;

    // --- RECUPERAÇÃO E REDEFINIÇÃO DE SENHA ---
    case '/recuperacao':
    case '/recuperacao.php':
        require __DIR__ . '/recuperacao.php';
        break;

    case '/redefinir':
    case '/redefinir.php':
        require __DIR__ . '/redefinir.php';
        break;

    // --- PROCESSAMENTO DE FORMULÁRIOS (POST) ---
    case '/processar':
    case '/processar.php':
        require __DIR__ . '/processar.php';
        break;

    // --- CADASTROS ---
    case '/cadastro':
    case '/cadastro-pessoa':
    case '/cadastro_pessoa':
    case '/cadastro_pessoa.php':
        require __DIR__ . '/cadastro_pessoa.php';
        break;

    case '/cadastro-empresa':
    case '/cadastro_empresa':
    case '/cadastro_empresa.php':
        require __DIR__ . '/cadastro_empresa.php';
        break;

    // --- INSTITUCIONAL ---
    case '/politica-privacidade':
    case '/politica_privacidade':
    case '/politica_privacidade.php':
        require __DIR__ . '/politica_privacidade.php';
        break;

    // --- PÁGINA NÃO ENCONTRADA (404) ---
    default:
        http_response_code(404);
        echo "<!DOCTYPE html>
        <html lang='pt-BR'>
        <head>
            <meta charset='UTF-8'>
            <title>404 - Página Não Encontrada</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background-color: #f4f6f9; color: #333; }
                h1 { color: #2b56f5; font-size: 48px; margin-bottom: 10px; }
                p { font-size: 18px; margin-bottom: 20px; }
                a { color: #2b56f5; text-decoration: none; font-weight: bold; }
            </style>
        </head>
        <body>
            <h1>Erro 404</h1>
            <p>A página que você procurou não foi encontrada.</p>
            <a href='/'>Voltar para o início</a>
        </body>
        </html>";
        break;
}