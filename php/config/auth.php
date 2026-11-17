<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/config/auth.php
 * Finalidade: auth.
 * Domínio: processamento no servidor.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


// Carrega o .env da raiz apenas quando ele existe; variáveis do sistema têm prioridade.
$projectRoot = dirname(__DIR__, 2); // Explicação DevIN: Atribui um valor à variável PHP $projectRoot.
require_once $projectRoot . '/vendor/autoload.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
if (is_file($projectRoot . '/.env')) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    // safeLoad mantém o sistema inicializável quando o .env não existe no ambiente.
    Dotenv\Dotenv::createImmutable($projectRoot)->safeLoad(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

$environmentValue = static function (string $name, ?string $legacyName = null): ?string { // Explicação DevIN: Define função anônima usada como operação ou callback.
    // Permite configurar o app pelo servidor sem depender exclusivamente de um arquivo .env.
    $value = $_ENV[$name] ?? $_SERVER[$name] ?? getenv($name); // Explicação DevIN: Atribui um valor à variável PHP $value.
    if (($value === false || $value === null || $value === '') && $legacyName !== null) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $value = $_ENV[$legacyName] ?? $_SERVER[$legacyName] ?? getenv($legacyName); // Explicação DevIN: Atribui um valor à variável PHP $value.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    return $value === false || $value === null || $value === '' ? null : (string) $value; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
}; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// 2. Configurações de Ambiente e JWT
$environment = $environmentValue('APP_ENV', 'DEVIN_APP_ENV') ?? 'production'; // Explicação DevIN: Atribui um valor à variável PHP $environment.
ini_set('display_errors', '0'); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
ini_set('log_errors', '1'); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
$jwtSecret = $environmentValue('DEVIN_JWT_SECRET') ?? ''; // Explicação DevIN: Atribui um valor à variável PHP $jwtSecret.

if ($jwtSecret === '') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    if ($environment !== 'development') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new RuntimeException('Configure DEVIN_JWT_SECRET antes de iniciar a aplicação.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Segredo determinístico facilita o ambiente local; fora dele a aplicação exige um segredo configurado.
    $jwtSecret = hash('sha256', __DIR__ . '|devin-local-secret'); // Explicação DevIN: Atribui um valor à variável PHP $jwtSecret.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Remove a barra final para evitar barras duplicadas nas URLs montadas pelas páginas.
$appBaseUrl = rtrim( // Explicação DevIN: Atribui um valor à variável PHP $appBaseUrl.
    $environmentValue('APP_URL', 'DEVIN_APP_BASE_URL') ?? 'http://localhost:8080/DevIN', // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    '/' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// 3. Definição das constantes globais
define('JWT_SECRET', $jwtSecret); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
define('JWT_ISSUER', 'DevIN'); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
define('JWT_EXPIRATION_SECONDS', 3600); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
define('JWT_COOKIE_NAME', 'devin_token'); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
define('APP_BASE_URL', $appBaseUrl); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
define('APP_ENV', $environment); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
