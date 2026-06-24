<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../auth/Jwt.php';

class AuthController
{
    public static function login(string $email, string $senha): array
    {
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);

        if (!$email || trim($senha) === '') {
            throw new InvalidArgumentException('Preencha email e senha.');
        }

        $conn = getDatabaseConnection();
        $usuario = self::findUserByEmail($conn, $email);
        $conn->close();

        if (!$usuario) {
            throw new RuntimeException('Usuario nao encontrado.');
        }

        if (!password_verify($senha, $usuario['senha_hash'])) {
            throw new RuntimeException('Senha incorreta.');
        }

        $now = time();
        $payload = [
            'iss' => JWT_ISSUER,
            'iat' => $now,
            'exp' => $now + JWT_EXPIRATION_SECONDS,
            'sub' => (string) $usuario['id'],
            'nome' => $usuario['nome'],
            'email' => $usuario['email'],
            'tipo' => $usuario['tipo'],
        ];

        $token = Jwt::encode($payload, JWT_SECRET);

        return [
            'token' => $token,
            'usuario' => [
                'id' => $usuario['id'],
                'nome' => $usuario['nome'],
                'email' => $usuario['email'],
                'tipo' => $usuario['tipo'],
            ],
        ];
    }

    private static function findUserByEmail(mysqli $conn, string $email): ?array
    {
        $sources = [
            ['table' => 'administrador', 'type' => 'adm'],
            ['table' => 'Empresa', 'type' => 'empresa'],
            ['table' => 'empresa', 'type' => 'empresa'],
            ['table' => 'Pessoa', 'type' => 'pessoa'],
            ['table' => 'pessoa_fisica', 'type' => 'pessoa'],
        ];

        foreach ($sources as $source) {
            $sql = "SELECT * FROM {$source['table']} WHERE email = ? LIMIT 1";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                continue;
            }

            $stmt->bind_param("s", $email);
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
                    'id' => self::extractId($row),
                    'nome' => $row['nome'] ?? '',
                    'email' => $row['email'] ?? $email,
                    'senha_hash' => $senhaHash,
                    'tipo' => $source['type'],
                ];
            }

            $stmt->close();
        }

        return null;
    }

    private static function extractId(array $row): int
    {
        foreach (['id', 'id_administrador', 'id_empresa', 'id_pessoa'] as $key) {
            if (isset($row[$key])) {
                return (int) $row[$key];
            }
        }

        foreach ($row as $key => $value) {
            if (strpos($key, 'id_') === 0) {
                return (int) $value;
            }
        }

        return 0;
    }

    public static function redirectByUserType(string $tipo): string
    {
        $routes = [
            'adm' => 'dashboard_adm.php',
            'empresa' => 'dashboard_empresa.php',
            'pessoa' => 'dashboard_pessoa.php',
        ];

        return $routes[$tipo] ?? 'index.php';
    }
}
