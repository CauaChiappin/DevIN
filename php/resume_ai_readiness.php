<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

/**
 * Diagnóstico de prontidão das duas IAs de currículo. Somente candidato logado.
 * Nunca devolve chaves, dados do currículo nem executa chamadas cobradas ao Gemini.
 */
require_once __DIR__ . '/middlewares/auth.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/config/security.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

header('Content-Type: application/json; charset=utf-8'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
header('Cache-Control: no-store, private'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    header('Allow: GET'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    http_response_code(405); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
    exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
requireWebAuth('pessoa'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

$key = (string) ($_ENV['DEVIN_GEMINI_API_KEY'] // Explicação DevIN: Atribui um valor à variável PHP $key.
    ?? $_SERVER['DEVIN_GEMINI_API_KEY'] // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    ?? getenv('DEVIN_GEMINI_API_KEY') ?: ''); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
$model = (string) ($_ENV['DEVIN_GEMINI_MODEL'] // Explicação DevIN: Atribui um valor à variável PHP $model.
    ?? $_SERVER['DEVIN_GEMINI_MODEL'] // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    ?? getenv('DEVIN_GEMINI_MODEL') ?: 'gemini-2.5-flash-lite'); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
$curl = function_exists('curl_init'); // Explicação DevIN: Atribui um valor à variável PHP $curl.
$validModel = preg_match('/^[A-Za-z0-9._-]{1,100}$/', $model) === 1; // Explicação DevIN: Atribui um valor à variável PHP $validModel.
$configured = $key !== '' && $curl && $validModel; // Explicação DevIN: Atribui um valor à variável PHP $configured.
$zip = class_exists(ZipArchive::class); // Explicação DevIN: Atribui um valor à variável PHP $zip.
$dom = class_exists(DOMDocument::class); // Explicação DevIN: Atribui um valor à variável PHP $dom.
http_response_code(200); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
echo json_encode([ // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
    'sucesso' => true, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'geracao' => [ // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'pronta_para_teste' => $configured, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'api_configurada' => $key !== '', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'curl_disponivel' => $curl, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'modelo_valido' => $validModel, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ], // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    'revisao' => [ // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'pdf_pronto_para_teste' => $configured, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'docx_pronto_para_teste' => $configured && $zip && $dom, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'zip_disponivel' => $zip, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'dom_disponivel' => $dom, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ], // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    'observacao' => 'Pronto para teste não comprova chave válida, quota disponível, resposta do Gemini ou gravação no MySQL. Faça geração e revisão reais com currículo fictício autorizado.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
