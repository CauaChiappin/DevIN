<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/config/security.php
 * Finalidade: security.
 * Domínio: processamento no servidor.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


require_once __DIR__ . '/auth.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

// Detalhe DevIN: Método isSecureRequest: verifica condições antes de liberar o próximo passo no contexto de security. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function isSecureRequest(): bool // Explicação DevIN: Declara função/método isSecureRequest, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    return APP_ENV !== 'development' // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

/**
 * Inicializa a sessão com cookies protegidos.
 */
// Detalhe DevIN: Método startSecureSession: encapsula uma responsabilidade reutilizável do módulo no contexto de security. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function startSecureSession(): void // Explicação DevIN: Declara função/método startSecureSession, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if (session_status() === PHP_SESSION_ACTIVE) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Salva sessões em diretório próprio, fora das pastas públicas do site.
    $sessionPath = dirname(__DIR__, 2) . '/storage/sessions'; // Explicação DevIN: Atribui um valor à variável PHP $sessionPath.
    if (!is_dir($sessionPath)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        @mkdir($sessionPath, 0700, true); // Explicação DevIN: Continua expressão ou bloco pertencente a função startSecureSession.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    if (is_dir($sessionPath) && is_writable($sessionPath)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        session_save_path($sessionPath); // Explicação DevIN: Continua expressão ou bloco pertencente a função startSecureSession.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /* Restringe a sessão a cookies e ativa atributos de proteção contra roubo e envio cross-site.
     * O early return acima evita tentar redefinir parâmetros depois que a sessão já começou. */
    ini_set('session.use_strict_mode', '1'); // Explicação DevIN: Continua expressão ou bloco pertencente a função startSecureSession.
    ini_set('session.use_only_cookies', '1'); // Explicação DevIN: Continua expressão ou bloco pertencente a função startSecureSession.
    ini_set('session.use_trans_sid', '0'); // Explicação DevIN: Continua expressão ou bloco pertencente a função startSecureSession.

    session_set_cookie_params([ // Explicação DevIN: Continua expressão ou bloco pertencente a função startSecureSession.
        'lifetime' => 0, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'path' => '/', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'secure' => isSecureRequest(), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'httponly' => true, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'samesite' => 'Lax', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ]); // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.

    session_start(); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Detalhe DevIN: Método requestString: encapsula uma responsabilidade reutilizável do módulo no contexto de security. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function requestString(array $source, string $key): string // Explicação DevIN: Declara função/método requestString, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    $value = $source[$key] ?? ''; // Explicação DevIN: Atribui um valor à variável PHP $value.
    return is_string($value) ? trim($value) : ''; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Detalhe DevIN: Método csrfToken: encapsula uma responsabilidade reutilizável do módulo no contexto de security. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function csrfToken(): string // Explicação DevIN: Declara função/método csrfToken, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    startSecureSession(); // Explicação DevIN: Continua expressão ou bloco pertencente a função csrfToken.

    if (empty($_SESSION['csrf_token'])) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        // Gera token imprevisível e o mantém na sessão para comparar no envio do formulário.
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    return $_SESSION['csrf_token']; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Detalhe DevIN: Método verifyCsrfToken: verifica condições antes de liberar o próximo passo no contexto de security. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function verifyCsrfToken(?string $token): bool // Explicação DevIN: Declara função/método verifyCsrfToken, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    startSecureSession(); // Explicação DevIN: Continua expressão ou bloco pertencente a função verifyCsrfToken.

    // hash_equals compara em tempo constante para não revelar o token por diferenças de tempo.
    return is_string($token) // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        && $token !== '' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        && !empty($_SESSION['csrf_token']) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        // Detalhe DevIN: Compara hashes ou segredos sem diferenças de tempo evitáveis.
        && hash_equals($_SESSION['csrf_token'], $token); // Explicação DevIN: Executa operação criptográfica de senha ou comparação protegida.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Detalhe DevIN: Método requireValidCsrf: verifica condições antes de liberar o próximo passo no contexto de security. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function requireValidCsrf(): void // Explicação DevIN: Declara função/método requireValidCsrf, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    // Tenta obter o token do POST ou dos cabeçalhos HTTP (para solicitações AJAX)
    $token = $_POST['csrf_token']  // Explicação DevIN: Atribui um valor à variável PHP $token.
        ?? $_SERVER['HTTP_X_CSRF_TOKEN']  // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        ?? $_SERVER['HTTP_X_XSRF_TOKEN']  // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        ?? null; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.

    if (!is_string($token) || !verifyCsrfToken($token)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        http_response_code(403); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        header('Content-Type: application/json; charset=utf-8'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        echo json_encode([ // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
            'sucesso' => false, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'mensagem' => 'Solicitação inválida ou token CSRF expirado. Atualize a página e tente novamente.' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ]); // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
        exit; // Explicação DevIN: Continua expressão ou bloco pertencente a função requireValidCsrf.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Detalhe DevIN: Método secureSessionRegenerate: encapsula uma responsabilidade reutilizável do módulo no contexto de security. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function secureSessionRegenerate(): void // Explicação DevIN: Declara função/método secureSessionRegenerate, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    startSecureSession(); // Explicação DevIN: Continua expressão ou bloco pertencente a função secureSessionRegenerate.
    session_regenerate_id(true); // Explicação DevIN: Continua expressão ou bloco pertencente a função secureSessionRegenerate.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
