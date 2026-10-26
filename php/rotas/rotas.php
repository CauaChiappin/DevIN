<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/auth.php';

$requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
/* Normaliza somente o caminho da URL para procurar uma entrada no mapa; parâmetros como
 * ?pagina=... são mantidos separados e acrescentados ao redirecionamento depois. */
$urlPath = (string) (parse_url($requestUri, PHP_URL_PATH) ?? '/');
$projectPath = rtrim((string) (parse_url(APP_BASE_URL, PHP_URL_PATH) ?? ''), '/');

if ($projectPath !== '' && strncasecmp($urlPath, $projectPath, strlen($projectPath)) === 0) {
    // Remove o prefixo de instalação quando o projeto está hospedado em uma subpasta.
    $nextCharacter = $urlPath[strlen($projectPath)] ?? '';
    if ($nextCharacter === '' || $nextCharacter === '/') {
        $urlPath = substr($urlPath, strlen($projectPath));
    }
}

$url = strtolower($urlPath);
// Uniformiza barra final e caixa para aceitar variações equivalentes do mesmo endereço.
if ($url !== '/' && str_ends_with($url, '/')) {
    $url = rtrim($url, '/');
}

$routes = [
    /* Vários formatos antigos e amigáveis apontam ao mesmo arquivo para manter links existentes
     * funcionando sem duplicar a implementação de cada página. */
    '' => 'index.php',
    '/' => 'index.php',
    '/index' => 'index.php',
    '/index.php' => 'index.php',
    '/index.html' => 'index.php',
    '/adm' => 'adm.php',
    '/adm.php' => 'adm.php',
    '/cadastrar_curriculo' => 'cadastrar_curriculo.php',
    '/cadastrar_curriculo.php' => 'cadastrar_curriculo.php',
    '/login' => 'login.php',
    '/login.php' => 'login.php',
    '/login.html' => 'login.php',
    '/empresa' => 'empresa.php',
    '/empresa.php' => 'empresa.php',
    '/pessoa' => 'pessoa.php',
    '/pessoa.php' => 'pessoa.php',
    '/logout' => 'logout.php',
    '/logout.php' => 'logout.php',
    '/csrf' => 'csrf.php',
    '/csrf.php' => 'csrf.php',
    '/codigo-senha' => 'codigo-senha.php',
    '/codigo-senha.php' => 'codigo-senha.php',
    '/recuperacao' => 'recuperacao.php',
    '/recuperacao.php' => 'recuperacao.php',
    '/redefinir' => 'redefinir.php',
    '/redefinir.php' => 'redefinir.php',
    '/processar' => 'processar.php',
    '/processar.php' => 'processar.php',
    '/cadastro' => 'cadastro_pessoa.php',
    '/cadastro-pessoa' => 'cadastro_pessoa.php',
    '/cadastro_pessoa' => 'cadastro_pessoa.php',
    '/cadastro_pessoa.php' => 'cadastro_pessoa.php',
    '/cadastro-empresa' => 'cadastro_empresa.php',
    '/cadastro_empresa' => 'cadastro_empresa.php',
    '/cadastro_empresa.php' => 'cadastro_empresa.php',
    '/politica-privacidade' => 'politica_privacidade.php',
    '/politica_privacidade' => 'politica_privacidade.php',
    '/politica_privacidade.php' => 'politica_privacidade.php',
];

if (isset($routes[$url])) {
    $targetUrl = APP_BASE_URL . '/php/' . $routes[$url];
    $queryString = parse_url($requestUri, PHP_URL_QUERY);
    // Mantém os parâmetros da URL ao redirecionar para o destino canônico.
    if (is_string($queryString) && $queryString !== '') {
        $targetUrl .= '?' . $queryString;
    }

    header('Location: ' . $targetUrl, true, 307);
    exit;
}

http_response_code(404);
// Escapa o endereço de retorno antes de interpolá-lo no HTML da resposta 404.
$homeUrl = htmlspecialchars(APP_BASE_URL . '/php/index.php', ENT_QUOTES, 'UTF-8');
echo "<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>404 - Página não encontrada</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f4f6f9; color: #333; }
        h1 { color: #2b56f5; font-size: 48px; margin-bottom: 10px; }
        p { font-size: 18px; margin-bottom: 20px; }
        a { color: #2b56f5; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Erro 404</h1>
    <p>A página que você procurou não foi encontrada.</p>
    <a href='{$homeUrl}'>Voltar para o início</a>
</body>
</html>";
