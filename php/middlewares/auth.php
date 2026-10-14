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

    // APIs aceitam Bearer no cabeçalho; páginas web podem usar o cookie HttpOnly.
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
    // Middleware de API: converte falhas de token em HTTP 401 JSON e interrompe a execução protegida.
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
    /* Middleware de páginas: verifica sessão, expiração e tipo de conta antes de fornecer
     * os dados autenticados ao restante do script. */
    startSecureSession();

    $loginUrl = APP_BASE_URL . '/php/login.php';

    if (!empty($_SESSION['logado'])) {
        $now = time();
        $lastActivity = (int) ($_SESSION['last_activity'] ?? 0);
        $authTime = (int) ($_SESSION['auth_time'] ?? 0);

        // Encerra sessões inativas após 30 min e sessões com mais de 12 horas.
        if ($lastActivity === 0 || $authTime === 0
            || $now - $lastActivity > 1800
            || $now - $authTime > 43200
        ) {
            $_SESSION = [];
            session_destroy();
            header('Location: ' . $loginUrl . '?erro=sessao_expirada');
            exit;
        }

        $_SESSION['last_activity'] = $now;
    }

    if (empty($_SESSION['logado']) || empty($_SESSION['usuario_id'])) {
        header('Location: ' . $loginUrl);
        exit;
    }

    $tipo = (string) ($_SESSION['usuario_tipo'] ?? '');

    // Usuários autenticados são enviados ao painel do próprio perfil, sem acesso cruzado.
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
    // Reaproveita as verificações de sessão e acrescenta a regra de currículo obrigatório.
    $usuario = requireWebAuth('pessoa');
    $possuiCurriculo = false;

    try {
        $conn = getDatabaseConnection();
        // O dashboard só fica disponível depois que existe currículo associado à conta.
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
