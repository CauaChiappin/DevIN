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
function requireValidCsrf(bool $asJson = true): void // Explicação DevIN: Declara função/método requireValidCsrf, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    // Tenta obter o token do POST ou dos cabeçalhos HTTP (para solicitações AJAX)
    $token = $_POST['csrf_token']  // Explicação DevIN: Atribui um valor à variável PHP $token.
        ?? $_SERVER['HTTP_X_CSRF_TOKEN']  // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        ?? $_SERVER['HTTP_X_XSRF_TOKEN']  // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        ?? null; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.

    if (!is_string($token) || !verifyCsrfToken($token)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        // PHP descarta POST e token CSRF se post_max_size for excedido.
        $serverLimit = trim((string) ini_get('post_max_size')); // Lê o limite de upload do servidor.
        $unit = strtoupper(substr($serverLimit, -1)); // Extrai a unidade.
        $multiplier = match ($unit) { 'K' => 1024, 'M' => 1048576, 'G' => 1073741824, default => 1 }; // Calcula bytes.
        $limitBytes = (int) ((float) $serverLimit * $multiplier); // Zero representa limite não definido.
        $requestTooLarge = $limitBytes > 0 && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > $limitBytes; // Identifica envio acima do limite.
        $message = $requestTooLarge // Prepara mensagem segura para erro de envio.
            ? 'O envio ultrapassou o limite do servidor. Tente com uma imagem menor ou contate o suporte.' // Explica tamanho.
            : 'Sua sessão expirou ou a página ficou desatualizada. Recarregue a página e tente novamente.'; // Explica token.
        http_response_code($requestTooLarge ? 413 : 403); // Define status HTTP adequado.
        header('Cache-Control: no-store'); // Evita cache de respostas de erro.
        if (!$asJson) { // Formulários normais não devem mostrar JSON bruto.
            header('Content-Type: text/html; charset=utf-8'); // Responde com HTML acessível.
            $back = basename((string) ($_SERVER['SCRIPT_NAME'] ?? 'login.php')); // Usa caminho local seguro.
            if (!preg_match('/^[a-z0-9_-]+\\.php$/i', $back)) $back = 'login.php'; // Impede link externo.
            $safeBack = htmlspecialchars($back, ENT_QUOTES, 'UTF-8'); // Escapa URL local.
            $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); // Escapa texto.
            echo '<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>DevIN | Falha no envio</title>'
                . '<style>body{font:16px/1.55 system-ui,sans-serif;background:#f1f6fc;color:#123457;min-height:100vh;display:grid;place-items:center;margin:0;padding:20px;box-sizing:border-box}main{width:min(100%,490px);background:white;padding:28px;border-radius:16px;box-shadow:0 8px 28px #0b46751a}h1{font-size:22px;margin:0 0 12px}p{margin:0 0 20px}a{display:inline-block;background:#09529a;color:white;padding:11px 18px;border-radius:9px;text-decoration:none}</style></head>'
                . '<body><main><h1>Não foi possível concluir a solicitação</h1><p role="alert">' . $safeMessage . '</p><a href="' . $safeBack . '">Voltar ao DevIN</a></main></body></html>'; // Exibe recuperação.
            exit; // Não prossegue sem token CSRF.
        } // Fim do HTML específico para formulários.
        header('Content-Type: application/json; charset=utf-8'); // Preserva a resposta das APIs.
        echo json_encode(['sucesso' => false, 'mensagem' => $message], JSON_UNESCAPED_UNICODE); // Envia erro JSON.
        exit; // Não prossegue com a ação.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Detalhe DevIN: Método secureSessionRegenerate: encapsula uma responsabilidade reutilizável do módulo no contexto de security. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function secureSessionRegenerate(): void // Explicação DevIN: Declara função/método secureSessionRegenerate, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    startSecureSession(); // Explicação DevIN: Continua expressão ou bloco pertencente a função secureSessionRegenerate.
    session_regenerate_id(true); // Explicação DevIN: Continua expressão ou bloco pertencente a função secureSessionRegenerate.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
