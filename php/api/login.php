<?php

require_once __DIR__ . '/../controllers/AuthController.php'; // carrega esse arquivoque contem a lógica de logib e geração de token JWT

header('Content-Type: application/json; charset=utf-8'); // define que a resposta será em formato JSON e com codificação UTF-8, para garantir que os caracteres especiais sejam exibidos corretamente.

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { // se a requisição não for POST, o endpoint recusa, envia um status 405 (recusou o metodo HTPP utilizado), uma mensagem de erro e o exit garante qye o script pare aqui.
    http_response_code(405);
    echo json_encode(['erro' => 'Metodo nao permitido.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true); // Tenta ler JSON enviado no corpo da requisição usando php://input.json_decode(..., true) converte JSON em array PHP. Se não for array (ou seja, se não vier JSON válido), usa os dados em $_POST.

if (!is_array($input)) {
    $input = $_POST;
}

$email = $input['email'] ?? ''; // pega email e senha do array $input, se não existir, coloca string vazia.
$senha = $input['senha'] ?? '';

try {
    $auth = AuthController::login($email, $senha); // chama o metodo login da classe AuthController, que valida email e senha, busca usuario no banco de dados, gera token JWT e retorna um array com token e dados do usuario.

    setcookie(JWT_COOKIE_NAME, $auth['token'], [ // cria um cookie com o token JWT, usando o nome definido em JWT_COOKIE_NAME, o valor do token e algumas opções de segurança.
        'expires' => time() + JWT_EXPIRATION_SECONDS, // define a expiração do cookie, que é o tempo atual mais o tempo de expiração definido em JWT_EXPIRATION_SECONDS.
        'path' => '/', // define que o cookie estará disponível em todo o site.
        'httponly' => true, // esses dois (esse e o de baixo) servem para proteger o site de ataques.
        'samesite' => 'Lax',
    ]);

    echo json_encode($auth); // converte o array $auth em JSON e envia como resposta da requisição, token JWT e dados do usuário.
} catch (Throwable $exception) { // se ocorrer algum erro durante o login, captura a exceção e envia uma resposta de erro com status 401 (não autorizado) e a mensagem de erro.
    http_response_code(401);
    echo json_encode(['erro' => $exception->getMessage()]);
}
