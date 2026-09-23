<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../auth/Jwt.php';

class AuthController
{
    // =====================================================
    // CRIAR SESSÃO E COOKIE JWT
    // =====================================================

    public static function establishSession(array $auth): void
    {
        startSecureSession();
        secureSessionRegenerate();

        $_SESSION['usuario_id']    = $auth['usuario']['id'];
        $_SESSION['usuario_nome']  = $auth['usuario']['nome'];
        $_SESSION['usuario_email'] = $auth['usuario']['email'];
        $_SESSION['usuario_tipo']  = $auth['usuario']['tipo'];
        $_SESSION['logado']        = true;

        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';

        // Cookie JWT protegido
        setcookie(
            JWT_COOKIE_NAME,
            $auth['token'],
            [
                'expires'  => time() + JWT_EXPIRATION_SECONDS,
                'path'     => '/',
                'secure'   => $isHttps,
                'httponly' => true,
                'samesite' => 'Lax',
            ]
        );
    }

    // =====================================================
    // LOGIN
    // =====================================================

    public static function login(string $email, string $senha): array
    {
        $emailValidado = filter_var($email, FILTER_VALIDATE_EMAIL);

        if (!$emailValidado || trim($senha) === '') {
            throw new InvalidArgumentException('Preencha e-mail e senha corretamente.');
        }

        $conn = getDatabaseConnection();
        $usuario = self::findUserByEmail($conn, $emailValidado);
        $conn->close();

        if (!$usuario || !password_verify($senha, $usuario['senha_hash'])) {
            throw new RuntimeException('E-mail ou senha inválidos.');
        }

        // Geração do Payload JWT
        $now = time();
        $payload = [
            'iss'   => JWT_ISSUER,
            'iat'   => $now,
            'exp'   => $now + JWT_EXPIRATION_SECONDS,
            'sub'   => (string) $usuario['id'],
            'nome'  => $usuario['nome'],
            'email' => $usuario['email'],
            'tipo'  => $usuario['tipo'],
        ];

        $token = Jwt::encode($payload, JWT_SECRET);

        return [
            'token'   => $token,
            'usuario' => [
                'id'    => $usuario['id'],
                'nome'  => $usuario['nome'],
                'email' => $usuario['email'],
                'tipo'  => $usuario['tipo'],
            ],
        ];
    }

    // =====================================================
    // PROCURAR USUÁRIO PELO EMAIL
    // =====================================================

    private static function findUserByEmail(mysqli $conn, string $email): ?array
    {
        $sources = [
            ['table' => 'administrador', 'type' => 'adm'],
            ['table' => 'empresa',       'type' => 'empresa'],
            ['table' => 'pessoa',        'type' => 'pessoa'],
        ];

        foreach ($sources as $source) {
            $sql = "SELECT * FROM {$source['table']} WHERE email = ? LIMIT 1";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                continue;
            }

            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {
                $row = $result->fetch_assoc();
                $stmt->close();

                $senhaHash = $row['senha_hash'] ?? $row['senha'] ?? null;

                if (!$senhaHash) {
                    continue;
                }

                return [
                    'id'         => self::extractId($row),
                    'nome'       => $row['nome'] ?? '',
                    'email'      => $row['email'] ?? $email,
                    'senha_hash' => $senhaHash,
                    'tipo'       => $source['type'],
                ];
            }

            $stmt->close();
        }

        return null;
    }

    // =====================================================
    // EXTRAIR ID DINAMICAMENTE
    // =====================================================

    private static function extractId(array $row): int
    {
        $keys = ['id', 'id_administrador', 'id_empresa', 'id_pessoa'];

        foreach ($keys as $key) {
            if (isset($row[$key])) {
                return (int) $row[$key];
            }
        }

        foreach ($row as $key => $value) {
            if (str_starts_with($key, 'id_')) {
                return (int) $value;
            }
        }

        return 0;
    }

    // =====================================================
    // REDIRECIONAMENTO POR PERFIL
    // =====================================================

    public static function redirectByUserType(string $tipo): string
    {
        $baseUrl = defined('APP_BASE_URL') ? APP_BASE_URL : '';

        $routes = [
            'adm'     => $baseUrl . '/php/adm.php',
            'empresa' => $baseUrl . '/php/empresa.php',
            'pessoa'  => $baseUrl . '/php/pessoa.php',
        ];

        return $routes[$tipo] ?? ($baseUrl . '/php/index.php');
    }
}