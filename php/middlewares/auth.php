<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../auth/Jwt.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';

function getBearerToken(): ?string
{
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $authorization = $headers['Authorization'] ?? $headers['authorization'] ?? '';

    if (preg_match('/Bearer\s+(.+)/', $authorization, $matches)) {
        return trim($matches[1]);
    }

    return $_COOKIE[JWT_COOKIE_NAME] ?? null;
}

function authUser(): array
{
    $token = getBearerToken();

    if (!$token) {
        throw new RuntimeException('Token nao informado.');
    }

    return Jwt::decode($token, JWT_SECRET);
}

function requireAuth(): array
{
    try {
        return authUser();
    } catch (Throwable $exception) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['erro' => 'Nao autorizado.'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

/**
 * Protege paginas PHP renderizadas no navegador usando a sessao.
 * O middleware tambem valida o tipo de usuario para impedir acesso
 * direto a dashboards de outro perfil.
 */
function requireWebAuth(?string $tipoEsperado = null): array
{
    startSecureSession();

    if (empty($_SESSION['logado']) || empty($_SESSION['usuario_id'])) {
        header('Location: login.php');
        exit;
    }

    $tipo = (string) ($_SESSION['usuario_tipo'] ?? '');

    if ($tipoEsperado !== null && $tipo !== $tipoEsperado) {
        $rotas = [
            'adm' => 'adm.php',
            'empresa' => 'empresa.php',
            'pessoa' => 'pessoa.php',
        ];

        header('Location: ' . ($rotas[$tipo] ?? 'login.php'));
        exit;
    }

    return [
        'id' => (int) $_SESSION['usuario_id'],
        'nome' => (string) ($_SESSION['usuario_nome'] ?? ''),
        'email' => (string) ($_SESSION['usuario_email'] ?? ''),
        'tipo' => $tipo,
    ];
}

/**
 * Protege especificamente o dashboard da pessoa.
 * A conta só pode entrar no dashboard depois que existir um currículo.
 */
function requirePessoaComCurriculo(): array
{
    $usuario = requireWebAuth('pessoa');

    try {
        $conn = getDatabaseConnection();
        $stmt = $conn->prepare(
            'SELECT id_curriculo FROM curriculo WHERE id_pessoa = ? LIMIT 1'
        );

        if (!$stmt) {
            throw new RuntimeException('Nao foi possivel verificar o curriculo.');
        }

        $stmt->bind_param('i', $usuario['id']);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $possuiCurriculo = $resultado && $resultado->num_rows > 0;
        $stmt->close();
        $conn->close();
    } catch (Throwable $exception) {
        error_log('Erro ao verificar curriculo: ' . $exception->getMessage());
        http_response_code(500);
        exit('Nao foi possivel verificar o cadastro do curriculo.');
    }

    if (!$possuiCurriculo) {
        header('Location: cadastrar_curriculo.php');
        exit;
    }

    return $usuario;
}
