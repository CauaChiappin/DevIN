<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/middlewares/auth.php
 * Finalidade: auth.
 * Domínio: processamento no servidor.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


require_once __DIR__ . '/../config/auth.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/../auth/Jwt.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/../config/database.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/../config/security.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

/**
 * Extrai o token JWT dos cabeçalhos HTTP ou do Cookie.
 */
// Detalhe DevIN: Método getBearerToken: consulta e organiza dados sem presumir que o resultado seja único no contexto de auth. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function getBearerToken(): ?string // Explicação DevIN: Declara função/método getBearerToken, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    $authorization = ''; // Explicação DevIN: Atribui um valor à variável PHP $authorization.

    if (function_exists('getallheaders')) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $headers = array_change_key_case(getallheaders(), CASE_LOWER); // Explicação DevIN: Atribui um valor à variável PHP $headers.
        $authorization = $headers['authorization'] ?? ''; // Explicação DevIN: Atribui um valor à variável PHP $authorization.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    if (empty($authorization)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $authorization = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? ''; // Explicação DevIN: Atribui um valor à variável PHP $authorization.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // APIs aceitam Bearer no cabeçalho; páginas web podem usar o cookie HttpOnly.
    if (preg_match('/Bearer\s+(.+)/i', $authorization, $matches)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        return trim($matches[1]); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    return $_COOKIE[JWT_COOKIE_NAME] ?? null; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

/**
 * Decodifica o token JWT do usuário autenticado.
 */
// Detalhe DevIN: Método authUser: encapsula uma responsabilidade reutilizável do módulo no contexto de auth. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function authUser(): array // Explicação DevIN: Declara função/método authUser, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    $token = getBearerToken(); // Explicação DevIN: Atribui um valor à variável PHP $token.

    if (!$token) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new RuntimeException('Token não informado.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    return Jwt::decode($token, JWT_SECRET); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

/**
 * Middleware para proteger rotas da API REST (retorna JSON 401 em caso de falha).
 */
// Detalhe DevIN: Método requireAuth: verifica condições antes de liberar o próximo passo no contexto de auth. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function requireAuth(): array // Explicação DevIN: Declara função/método requireAuth, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    // Middleware de API: converte falhas de token em HTTP 401 JSON e interrompe a execução protegida.
    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        return authUser(); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        http_response_code(401); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        header('Content-Type: application/json; charset=utf-8'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        echo json_encode(['erro' => 'Não autorizado.'], JSON_UNESCAPED_UNICODE); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
        exit; // Explicação DevIN: Continua expressão ou bloco pertencente a função requireAuth.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

/**
 * Protege páginas PHP renderizadas no navegador usando a sessão.
 * O middleware também valida o tipo de usuário para impedir acesso
 * direto a dashboards de outro perfil.
 */
// Detalhe DevIN: Método requireWebAuth: verifica condições antes de liberar o próximo passo no contexto de auth. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function requireWebAuth(?string $tipoEsperado = null): array // Explicação DevIN: Declara função/método requireWebAuth, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    /* Middleware de páginas: verifica sessão, expiração e tipo de conta antes de fornecer
     * os dados autenticados ao restante do script. */
    startSecureSession(); // Explicação DevIN: Continua expressão ou bloco pertencente a função requireWebAuth.

    $loginUrl = APP_BASE_URL . '/php/login.php'; // Explicação DevIN: Atribui um valor à variável PHP $loginUrl.

    if (!empty($_SESSION['logado'])) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $now = time(); // Explicação DevIN: Atribui um valor à variável PHP $now.
        $lastActivity = (int) ($_SESSION['last_activity'] ?? 0); // Explicação DevIN: Atribui um valor à variável PHP $lastActivity.
        $authTime = (int) ($_SESSION['auth_time'] ?? 0); // Explicação DevIN: Atribui um valor à variável PHP $authTime.

        // Encerra sessões inativas após 30 min e sessões com mais de 12 horas.
        if ($lastActivity === 0 || $authTime === 0 // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            || $now - $lastActivity > 1800 // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            || $now - $authTime > 43200 // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        ) { // Explicação DevIN: Continua expressão ou bloco pertencente a função requireWebAuth.
            $_SESSION = []; // Explicação DevIN: Atribui um valor à variável PHP $_SESSION.
            session_destroy(); // Explicação DevIN: Continua expressão ou bloco pertencente a função requireWebAuth.
            header('Location: ' . $loginUrl . '?erro=sessao_expirada'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            exit; // Explicação DevIN: Continua expressão ou bloco pertencente a função requireWebAuth.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $_SESSION['last_activity'] = $now; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    if (empty($_SESSION['logado']) || empty($_SESSION['usuario_id'])) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        header('Location: ' . $loginUrl); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        exit; // Explicação DevIN: Continua expressão ou bloco pertencente a função requireWebAuth.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    $tipo = (string) ($_SESSION['usuario_tipo'] ?? ''); // Explicação DevIN: Atribui um valor à variável PHP $tipo.

    // Usuários autenticados são enviados ao painel do próprio perfil, sem acesso cruzado.
    if ($tipoEsperado !== null && $tipo !== $tipoEsperado) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $rotas = [ // Explicação DevIN: Atribui um valor à variável PHP $rotas.
            'adm'     => APP_BASE_URL . '/php/adm.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'empresa' => APP_BASE_URL . '/php/empresa.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'pessoa'  => APP_BASE_URL . '/php/pessoa.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.

        $redirectUrl = $rotas[$tipo] ?? $loginUrl; // Explicação DevIN: Atribui um valor à variável PHP $redirectUrl.
        header('Location: ' . $redirectUrl); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        exit; // Explicação DevIN: Continua expressão ou bloco pertencente a função requireWebAuth.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    return [ // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        'id'    => (int) $_SESSION['usuario_id'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'nome'  => (string) ($_SESSION['usuario_nome'] ?? ''), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'email' => (string) ($_SESSION['usuario_email'] ?? ''), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'tipo'  => $tipo, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

/**
 * Protege especificamente o dashboard da pessoa.
 * A conta só pode entrar no dashboard depois que existir um currículo.
 */
// Detalhe DevIN: Método requirePessoaComCurriculo: verifica condições antes de liberar o próximo passo no contexto de auth. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function requirePessoaComCurriculo(): array // Explicação DevIN: Declara função/método requirePessoaComCurriculo, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    // Reaproveita as verificações de sessão e acrescenta a regra de currículo obrigatório.
    $usuario = requireWebAuth('pessoa'); // Explicação DevIN: Atribui um valor à variável PHP $usuario.
    $possuiCurriculo = false; // Explicação DevIN: Atribui um valor à variável PHP $possuiCurriculo.

    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        $conn = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $conn.
        // O dashboard só fica disponível depois que existe currículo associado à conta.
        // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
        $stmt = $conn->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            'SELECT id_curriculo FROM curriculo WHERE id_pessoa = ? LIMIT 1' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if ($stmt) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $stmt->bind_param('i', $usuario['id']); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $resultado = $stmt->get_result(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            $possuiCurriculo = $resultado && $resultado->num_rows > 0; // Explicação DevIN: Atribui um valor à variável PHP $possuiCurriculo.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função requirePessoaComCurriculo.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $conn->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função requirePessoaComCurriculo.
    } catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        error_log('Erro ao verificar currículo: ' . $exception->getMessage()); // Explicação DevIN: Continua expressão ou bloco pertencente a função requirePessoaComCurriculo.
        http_response_code(500); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        exit('Não foi possível verificar o cadastro do currículo.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função requirePessoaComCurriculo.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    if (!$possuiCurriculo) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        header('Location: ' . APP_BASE_URL . '/php/cadastrar_curriculo.php'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
        exit; // Explicação DevIN: Continua expressão ou bloco pertencente a função requirePessoaComCurriculo.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    return $usuario; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
