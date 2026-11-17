<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/rotas/rotas.php
 * Finalidade: rotas.
 * Domínio: processamento no servidor.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


require_once __DIR__ . '/../config/auth.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

$requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '/'); // Explicação DevIN: Atribui um valor à variável PHP $requestUri.
/* Normaliza somente o caminho da URL para procurar uma entrada no mapa; parâmetros como
 * ?pagina=... são mantidos separados e acrescentados ao redirecionamento depois. */
$urlPath = (string) (parse_url($requestUri, PHP_URL_PATH) ?? '/'); // Explicação DevIN: Atribui um valor à variável PHP $urlPath.
$projectPath = rtrim((string) (parse_url(APP_BASE_URL, PHP_URL_PATH) ?? ''), '/'); // Explicação DevIN: Atribui um valor à variável PHP $projectPath.

if ($projectPath !== '' && strncasecmp($urlPath, $projectPath, strlen($projectPath)) === 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    // Remove o prefixo de instalação quando o projeto está hospedado em uma subpasta.
    $nextCharacter = $urlPath[strlen($projectPath)] ?? ''; // Explicação DevIN: Atribui um valor à variável PHP $nextCharacter.
    if ($nextCharacter === '' || $nextCharacter === '/') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $urlPath = substr($urlPath, strlen($projectPath)); // Explicação DevIN: Atribui um valor à variável PHP $urlPath.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

$url = strtolower($urlPath); // Explicação DevIN: Atribui um valor à variável PHP $url.
// Uniformiza barra final e caixa para aceitar variações equivalentes do mesmo endereço.
if ($url !== '/' && str_ends_with($url, '/')) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    $url = rtrim($url, '/'); // Explicação DevIN: Atribui um valor à variável PHP $url.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

$routes = [ // Explicação DevIN: Atribui um valor à variável PHP $routes.
    /* Vários formatos antigos e amigáveis apontam ao mesmo arquivo para manter links existentes
     * funcionando sem duplicar a implementação de cada página. */
    '' => 'index.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '/' => 'index.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '/index' => 'index.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '/index.php' => 'index.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '/index.html' => 'index.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '/adm' => 'adm.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '/adm.php' => 'adm.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '/cadastrar_curriculo' => 'cadastrar_curriculo.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '/cadastrar_curriculo.php' => 'cadastrar_curriculo.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '/login' => 'login.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '/login.php' => 'login.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '/login.html' => 'login.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '/empresa' => 'empresa.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '/empresa.php' => 'empresa.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '/pessoa' => 'pessoa.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '/pessoa.php' => 'pessoa.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '/logout' => 'logout.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '/logout.php' => 'logout.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '/csrf' => 'csrf.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '/csrf.php' => 'csrf.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '/codigo-senha' => 'codigo-senha.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '/codigo-senha.php' => 'codigo-senha.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '/recuperacao' => 'recuperacao.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '/recuperacao.php' => 'recuperacao.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '/redefinir' => 'redefinir.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '/redefinir.php' => 'redefinir.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '/processar' => 'processar.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '/processar.php' => 'processar.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '/cadastro' => 'cadastro_pessoa.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '/cadastro-pessoa' => 'cadastro_pessoa.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '/cadastro_pessoa' => 'cadastro_pessoa.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '/cadastro_pessoa.php' => 'cadastro_pessoa.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '/cadastro-empresa' => 'cadastro_empresa.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '/cadastro_empresa' => 'cadastro_empresa.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '/cadastro_empresa.php' => 'cadastro_empresa.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '/politica-privacidade' => 'politica_privacidade.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '/politica_privacidade' => 'politica_privacidade.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    '/politica_privacidade.php' => 'politica_privacidade.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.

if (isset($routes[$url])) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    $targetUrl = APP_BASE_URL . '/php/' . $routes[$url]; // Explicação DevIN: Atribui um valor à variável PHP $targetUrl.
    $queryString = parse_url($requestUri, PHP_URL_QUERY); // Explicação DevIN: Atribui um valor à variável PHP $queryString.
    // Mantém os parâmetros da URL ao redirecionar para o destino canônico.
    if (is_string($queryString) && $queryString !== '') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $targetUrl .= '?' . $queryString; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    header('Location: ' . $targetUrl, true, 307); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

http_response_code(404); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
// Escapa o endereço de retorno antes de interpolá-lo no HTML da resposta 404.
$homeUrl = htmlspecialchars(APP_BASE_URL . '/php/index.php', ENT_QUOTES, 'UTF-8'); // Explicação DevIN: Atribui um valor à variável PHP $homeUrl.
$lenisScriptUrl = htmlspecialchars(APP_BASE_URL . '/js/lenis-global.js', ENT_QUOTES, 'UTF-8'); // Explicação DevIN: Atribui um valor à variável PHP $lenisScriptUrl.
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
    <script src='{$lenisScriptUrl}'></script>
</body>
</html>"; // Explicação DevIN: Fecha elemento html do documento.
