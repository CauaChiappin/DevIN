<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

require_once __DIR__ . '/config/security.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
startSecureSession(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

require_once __DIR__ . '/config/auth.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

// 1. Limpa todas as variáveis de sessão
// Remove os dados da sessão atual antes de invalidar os identificadores no navegador.
$_SESSION = []; // Explicação DevIN: Atribui um valor à variável PHP $_SESSION.

// 2. Invalida o cookie de sessão do navegador
if (ini_get('session.use_cookies')) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    $params = session_get_cookie_params(); // Explicação DevIN: Atribui um valor à variável PHP $params.

    setcookie( // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        session_name(), // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        '', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        [ // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            'expires'  => time() - 42000, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'path'     => $params['path'] ?: '/', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'domain'   => $params['domain'] ?? '', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'secure'   => isSecureRequest(), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'httponly' => (bool) $params['httponly'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'samesite' => $params['samesite'] ?? 'Lax', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ] // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// 3. Destrói a sessão no servidor
session_destroy(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

// 4. Invalida o cookie JWT (utiliza detecção completa de HTTPS)
setcookie( // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    JWT_COOKIE_NAME, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    '', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    [ // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        'expires'  => time() - 3600, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'path'     => '/', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'secure'   => isSecureRequest(), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'httponly' => true, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'samesite' => 'Lax', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ] // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// 5. Redireciona dentro da pasta atual, sem aceitar URLs externas.
$redirectPath = ($_GET['destino'] ?? '') === 'index' ? 'index.php' : 'login.php'; // Explicação DevIN: Atribui um valor à variável PHP $redirectPath.
header('Location: ' . $redirectPath); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
