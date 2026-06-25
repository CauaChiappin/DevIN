<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../auth/Jwt.php';

class AuthController
{
    public static function login(string $email, string $senha): array // metodo que valida email e senha, busca usuario no banco de dados, gera token JWT e retorna um array com token e dados do usuario
    {
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);

        if (!$email || trim($senha) === '') {
            throw new InvalidArgumentException('Preencha email e senha.');
        }

        $conn = getDatabaseConnection();
        $usuario = self::findUserByEmail($conn, $email); // busca usuario no banco de dados, se nao encontrar, retorna null
        $conn->close();

        if (!$usuario) {
            throw new RuntimeException('Usuario nao encontrado.');
        }

        if (!password_verify($senha, $usuario['senha_hash'])) {
            throw new RuntimeException('Senha incorreta.');
        }

        $now = time();
        $payload = [ /*Carrega os dados a serem codificados, como está em colchetes ele é um json(serve para armazenar dados e transportar dados) */
            'iss' => JWT_ISSUER, // Emissor do token
            'iat' => $now, // Data de emissão do token
            'exp' => $now + JWT_EXPIRATION_SECONDS, // Data de expiração do token
            'sub' => (string) $usuario['id'], // Identificador do usuário
            'nome' => $usuario['nome'], // Nome do usuário ......
            'email' => $usuario['email'],
            'tipo' => $usuario['tipo'],
        ];

        $token = Jwt::encode($payload, JWT_SECRET); // encode é o metodo que codifica o token, ele recebe o payload e a chave secreta para gerar o token

        return [
            'token' => $token, // retorna o token JWT gerado
            'usuario' => [ // retorna os dados do usuario
                'id' => $usuario['id'],
                'nome' => $usuario['nome'],
                'email' => $usuario['email'],
                'tipo' => $usuario['tipo'],
            ],
        ];
    }

    private static function findUserByEmail(mysqli $conn, string $email): ?array // metodo que busca usuario no banco de dados pelo email, retorna um array com os dados do usuario ou null se nao encontrar
    {
        $sources = [ // array que contem as tabelas do banco de dados e o tipo de usuario correspondente
            ['table' => 'administrador', 'type' => 'adm'],
            ['table' => 'Empresa', 'type' => 'empresa'],
            ['table' => 'empresa', 'type' => 'empresa'],
            ['table' => 'Pessoa', 'type' => 'pessoa'],
            ['table' => 'pessoa_fisica', 'type' => 'pessoa'],
        ];

        foreach ($sources as $source) { // percorre o array de tabelas e tipos de usuario, buscando o usuario pelo email em cada tabela
            $sql = "SELECT * FROM {$source['table']} WHERE email = ? LIMIT 1"; // faz uma busca na tabela especificada pelo email, limitando a busca a 1 resultado
            $stmt = $conn->prepare($sql); // prepara a query para ser executada, evitando SQL Injection

            if (!$stmt) { // se a query nao puder ser preparada, continua para a proxima tabela
                continue;
            }

            $stmt->bind_param("s", $email); // vincula o parametro email a query, evitando SQL Injection
            $stmt->execute(); // executa a query no banco de dados
            $result = $stmt->get_result(); // obtém o resultado da query executada, que é um objeto mysqli_result

            if ($result && $result->num_rows === 1) {
                $row = $result->fetch_assoc();
                $stmt->close(); // fecha a query preparada, liberando recursos do banco de dados

                $senhaHash = $row['senha_hash'] ?? $row['senha'] ?? null; // verifica se a coluna senha_hash ou senha existe no resultado da query, se não existir, continua para a próxima tabela

                if (!$senhaHash) {
                    continue;
                }

                return [
                    'id' => self::extractId($row), // extrai o id do usuario, verificando se existe a coluna id, id_administrador, id_empresa ou id_pessoa, caso nao exista, retorna 0
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
        foreach (['id', 'id_administrador', 'id_empresa', 'id_pessoa'] as $key) { // percorre o array de chaves que podem conter o id do usuario, verificando se a chave existe no resultado da query, caso exista, retorna o valor da chave como inteiro
            if (isset($row[$key])) { // verifica se a chave existe no resultado da query
                return (int) $row[$key];
            }
        }

        foreach ($row as $key => $value) { // percorre o array de resultados da query, verificando se a chave começa com "id_", caso comece, retorna o valor da chave como inteiro
            if (strpos($key, 'id_') === 0) {
                return (int) $value;
            }
        }

        return 0;
    }

    public static function redirectByUserType(string $tipo): string // metodo que redireciona o usuario para a pagina correspondente ao seu tipo, retorna a url da pagina
    {
        $routes = [ // array que contem as rotas correspondentes a cada tipo de usuario
            'adm' => 'dashboard_adm.php',
            'empresa' => 'dashboard_empresa.php',
            'pessoa' => 'dashboard_pessoa.php',
        ];

        return $routes[$tipo] ?? 'index.php';
    }
    
      
}
