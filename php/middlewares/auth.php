<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../auth/Jwt.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';

/**
 * Extrai o token JWT dos cabeçalhos HTTP ou do Cookie.
 */
function getBearerToken(): ?string
{
    $authorization = '';

    if (function_exists('getallheaders')) {
        $headers = array_change_key_case(getallheaders(), CASE_LOWER);
        $authorization = $headers['authorization'] ?? '';
    }

    if (empty($authorization)) {
        $authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    }

    if (preg_match('/Bearer\s+(.+)/i', $authorization, $matches)) {
        return trim($matches[1]);
    }

    return $_COOKIE[JWT_COOKIE_NAME] ?? null;
}

/**
 * Decodifica o token JWT do usuário autenticado.
 */
function authUser(): array
{
    $token = getBearerToken();

    if (!$token) {
        throw new RuntimeException('Token não informado.');
    }

    return Jwt::decode($token, JWT_SECRET);
}

/**
 * Middleware para proteger rotas da API REST (retorna JSON 401 em caso de falha).
 */
function requireAuth(): array
{
    try {
        return authUser();
    } catch (Throwable $exception) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['erro' => 'Não autorizado.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

/**
 * Protege páginas PHP renderizadas no navegador usando a sessão.
 * O middleware também valida o tipo de usuário para impedir acesso
 * direto a dashboards de outro perfil.
 */
function requireWebAuth(?string $tipoEsperado = null): array
{
    startSecureSession();

    $loginUrl = APP_BASE_URL . '/php/login.php';

    if (empty($_SESSION['logado']) || empty($_SESSION['usuario_id'])) {
        header('Location: ' . $loginUrl);
        exit;
    }

    $tipo = (string) ($_SESSION['usuario_tipo'] ?? '');

    if ($tipoEsperado !== null && $tipo !== $tipoEsperado) {
        $rotas = [
            'adm'     => APP_BASE_URL . '/php/adm.php',
            'empresa' => APP_BASE_URL . '/php/empresa.php',
            'pessoa'  => APP_BASE_URL . '/php/pessoa.php',
        ];

        $redirectUrl = $rotas[$tipo] ?? $loginUrl;
        header('Location: ' . $redirectUrl);
        exit;
    }

    return [
        'id'    => (int) $_SESSION['usuario_id'],
        'nome'  => (string) ($_SESSION['usuario_nome'] ?? ''),
        'email' => (string) ($_SESSION['usuario_email'] ?? ''),
        'tipo'  => $tipo,
    ];
}

/**
 * Protege especificamente o dashboard da pessoa.
 * A conta só pode entrar no dashboard depois que existir um currículo.
 */
function requirePessoaComCurriculo(): array
{
    $usuario = requireWebAuth('pessoa');
    $possuiCurriculo = false;

    try {
        $conn = getDatabaseConnection();
        $stmt = $conn->prepare(
            'SELECT id_curriculo FROM curriculo WHERE id_pessoa = ? LIMIT 1'
        );

        if ($stmt) {
            $stmt->bind_param('i', $usuario['id']);
            $stmt->execute();
            $resultado = $stmt->get_result();
            $possuiCurriculo = $resultado && $resultado->num_rows > 0;
            $stmt->close();
        }

        $conn->close();
    } catch (Throwable $exception) {
        error_log('Erro ao verificar currículo: ' . $exception->getMessage());
        http_response_code(500);
        exit('Não foi possível verificar o cadastro do currículo.');
    }

    if (!$possuiCurriculo) {
        header('Location: ' . APP_BASE_URL . '/php/cadastrar_curriculo.php');
        exit;
    }

    return $usuario;
}