<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

/**
 * MÓDULO DEVIN — AUTH CONTROLLER.
 * Responsabilidade: parte do subsistema de dados de perfis, currículos, vagas e interações do DevIN.
 * Leia os métodos abaixo em sequência: entrada validada -> processamento ->
 * resposta ao chamador. Comentários detalhados descrevem decisões, limites
 * e efeitos colaterais; os comentários gerados por linha foram preservados.
 * Atenção: chamadas de banco e ações administrativas exigem autorização no
 * endpoint controlador; métodos isolados não constituem permissão para a IA.
 */


require_once __DIR__ . '/../config/database.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/../config/security.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/../config/auth.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/../auth/Jwt.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/ProfileLanguagePreference.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

class AuthController // Explicação DevIN: Declara o tipo AuthController e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    // =====================================================
    // CRIAR SESSÃO E COOKIE JWT
    // =====================================================

    // Detalhamento DevIN: centraliza a operação «establishSession» no contexto de dados de perfis, currículos, vagas e interações do DevIN; os argumentos recebidos definem o alvo ou os dados processados, e o retorno/erro sinaliza o resultado ao chamador.
    // Entrada e saída: confira a assinatura abaixo; falhas devem ser tratadas pelo controlador que chamou esta rotina.
    public static function establishSession(array $auth): void // Explicação DevIN: Declara função/método establishSession, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // Troca o ID da sessão após login para impedir reutilização de um identificador anterior.
        startSecureSession(); // Explicação DevIN: Continua expressão ou bloco pertencente a função establishSession.
        secureSessionRegenerate(); // Explicação DevIN: Continua expressão ou bloco pertencente a função establishSession.
        unset($_SESSION['arcade_unlock'], $_SESSION['admin_stepup_grant_site'], // Explicação DevIN: Continua expressão ou bloco pertencente a função establishSession.
            $_SESSION['admin_stepup_grant_ai'], $_SESSION['admin_stepup_ai_stage'], // Explicação DevIN: Continua expressão ou bloco pertencente a função establishSession.
            $_SESSION['admin_stepup_pending']); // Explicação DevIN: Continua expressão ou bloco pertencente a função establishSession.

        // Mantém na sessão os dados usados pelos dashboards e marca os tempos de validade/atividade.
        $_SESSION['usuario_id']    = $auth['usuario']['id']; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        $_SESSION['usuario_nome']  = $auth['usuario']['nome']; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        $_SESSION['usuario_email'] = $auth['usuario']['email']; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        $_SESSION['usuario_tipo']  = $auth['usuario']['tipo']; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        $_SESSION['idioma']        = $auth['usuario']['idioma'] ?? 'pt-BR'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        $_SESSION['logado']        = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        $_SESSION['auth_time']     = time(); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        $_SESSION['last_activity'] = time(); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.

        // Cookie JWT protegido
        setcookie( // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            JWT_COOKIE_NAME, // Explicação DevIN: Continua expressão ou bloco pertencente a função establishSession.
            $auth['token'], // Explicação DevIN: Continua expressão ou bloco pertencente a função establishSession.
            [ // Explicação DevIN: Continua expressão ou bloco pertencente a função establishSession.
                'expires'  => time() + JWT_EXPIRATION_SECONDS, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'path'     => '/', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'secure'   => isSecureRequest(), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'httponly' => true, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'samesite' => 'Lax', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            ] // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // =====================================================
    // LOGIN
    // =====================================================

    // Detalhamento DevIN: registra auditoria a operação «login» no contexto de dados de perfis, currículos, vagas e interações do DevIN; os argumentos recebidos definem o alvo ou os dados processados, e o retorno/erro sinaliza o resultado ao chamador.
    // Entrada e saída: confira a assinatura abaixo; falhas devem ser tratadas pelo controlador que chamou esta rotina.
    public static function login(string $email, string $senha): array // Explicação DevIN: Declara função/método login, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // Valida formato antes de consultar o banco e devolve sempre a mesma falha para credenciais incorretas.
        $emailValidado = filter_var($email, FILTER_VALIDATE_EMAIL); // Explicação DevIN: Atribui um valor à variável PHP $emailValidado.

        if (!$emailValidado || trim($senha) === '') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Preencha e-mail e senha corretamente.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $conn = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $conn.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            $usuario = self::findUserByEmail($conn, $emailValidado); // Explicação DevIN: Atribui um valor à variável PHP $usuario.
            if ($usuario) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                $usuario['idioma'] = ProfileLanguagePreference::load( // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    $conn, // Explicação DevIN: Continua expressão ou bloco pertencente a função login.
                    $usuario['tipo'], // Explicação DevIN: Continua expressão ou bloco pertencente a função login.
                    (int) $usuario['id'] // Explicação DevIN: Continua expressão ou bloco pertencente a função login.
                ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
            $conn->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função login.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        // password_verify confere a senha contra o hash sem armazenar a senha original.
        // Detalhamento DevIN: Verificação de senha por hash, evitando comparar ou armazenar a senha em texto puro.
        if (!$usuario || !password_verify($senha, $usuario['senha_hash'])) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new RuntimeException('E-mail ou senha inválidos.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        // Geração do Payload JWT
        // O JWT contém emissor, início, validade e ID/tipo; o middleware verifica esses campos em cada uso.
        $now = time(); // Explicação DevIN: Atribui um valor à variável PHP $now.
        $payload = [ // Explicação DevIN: Atribui um valor à variável PHP $payload.
            'iss'   => JWT_ISSUER, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'iat'   => $now, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'exp'   => $now + JWT_EXPIRATION_SECONDS, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'sub'   => (string) $usuario['id'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'tipo'  => $usuario['tipo'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.

        $token = Jwt::encode($payload, JWT_SECRET); // Explicação DevIN: Atribui um valor à variável PHP $token.

        return [ // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            'token'   => $token, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'usuario' => [ // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'id'     => $usuario['id'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'nome'   => $usuario['nome'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'email'  => $usuario['email'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'tipo'   => $usuario['tipo'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'idioma' => $usuario['idioma'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            ], // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // =====================================================
    // PROCURAR USUÁRIO PELO EMAIL
    // =====================================================

    // Detalhamento DevIN: localiza a operação «findUserByEmail» no contexto de dados de perfis, currículos, vagas e interações do DevIN; os argumentos recebidos definem o alvo ou os dados processados, e o retorno/erro sinaliza o resultado ao chamador.
    // Entrada e saída: confira a assinatura abaixo; falhas devem ser tratadas pelo controlador que chamou esta rotina.
    private static function findUserByEmail(mysqli $conn, string $email): ?array // Explicação DevIN: Declara função/método findUserByEmail, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $sources = [ // Explicação DevIN: Atribui um valor à variável PHP $sources.
            ['table' => 'administrador', 'type' => 'adm'], // Explicação DevIN: Continua expressão ou bloco pertencente a função findUserByEmail.
            ['table' => 'empresa',       'type' => 'empresa'], // Explicação DevIN: Continua expressão ou bloco pertencente a função findUserByEmail.
            ['table' => 'pessoa',        'type' => 'pessoa'], // Explicação DevIN: Continua expressão ou bloco pertencente a função findUserByEmail.
        ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.

        // As tabelas vêm de uma lista fixa, enquanto o e-mail é vinculado como parâmetro.
        foreach ($sources as $source) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            $sql = "SELECT * FROM {$source['table']} WHERE email = ? LIMIT 1"; // Explicação DevIN: Atribui um valor à variável PHP $sql.
            $stmt = $conn->prepare($sql); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.

            if (!$stmt) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                continue; // Explicação DevIN: Controla a continuação ou saída de laço/seleção.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $stmt->bind_param('s', $email); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $result = $stmt->get_result(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.

            if ($result && $result->num_rows === 1) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                $row = $result->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
                $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função findUserByEmail.

                // Never accept legacy/plaintext password columns as credentials.
                $senhaHash = $row['senha_hash'] ?? null; // Explicação DevIN: Atribui um valor à variável PHP $senhaHash.

                if (!$senhaHash) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    continue; // Explicação DevIN: Controla a continuação ou saída de laço/seleção.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

                return [ // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
                    'id'         => self::extractId($row), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    'nome'       => $row['nome'] ?? '', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    'email'      => $row['email'] ?? $email, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    'senha_hash' => $senhaHash, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    'tipo'       => $source['type'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função findUserByEmail.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        return null; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // =====================================================
    // EXTRAIR ID DINAMICAMENTE
    // =====================================================

    // Detalhamento DevIN: centraliza a operação «extractId» no contexto de dados de perfis, currículos, vagas e interações do DevIN; os argumentos recebidos definem o alvo ou os dados processados, e o retorno/erro sinaliza o resultado ao chamador.
    // Entrada e saída: confira a assinatura abaixo; falhas devem ser tratadas pelo controlador que chamou esta rotina.
    private static function extractId(array $row): int // Explicação DevIN: Declara função/método extractId, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // Os esquemas usam nomes de chave diferentes, então procura as colunas de ID conhecidas.
        $keys = ['id', 'id_administrador', 'id_empresa', 'id_pessoa']; // Explicação DevIN: Atribui um valor à variável PHP $keys.

        foreach ($keys as $key) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            if (isset($row[$key])) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                return (int) $row[$key]; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        foreach ($row as $key => $value) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            if (str_starts_with($key, 'id_')) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                return (int) $value; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        return 0; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // =====================================================
    // REDIRECIONAMENTO POR PERFIL
    // =====================================================

    // Detalhamento DevIN: centraliza a operação «redirectByUserType» no contexto de dados de perfis, currículos, vagas e interações do DevIN; os argumentos recebidos definem o alvo ou os dados processados, e o retorno/erro sinaliza o resultado ao chamador.
    // Entrada e saída: confira a assinatura abaixo; falhas devem ser tratadas pelo controlador que chamou esta rotina.
    public static function redirectByUserType(string $tipo): string // Explicação DevIN: Declara função/método redirectByUserType, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // Roteia cada perfil para o painel correspondente e usa a página inicial como fallback.
        $baseUrl = defined('APP_BASE_URL') ? APP_BASE_URL : ''; // Explicação DevIN: Atribui um valor à variável PHP $baseUrl.

        $routes = [ // Explicação DevIN: Atribui um valor à variável PHP $routes.
            'adm'     => $baseUrl . '/php/adm.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'empresa' => $baseUrl . '/php/empresa.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'pessoa'  => $baseUrl . '/php/pessoa.php', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.

        return $routes[$tipo] ?? ($baseUrl . '/php/index.php'); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
