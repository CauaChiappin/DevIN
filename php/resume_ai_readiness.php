<?php
declare(strict_types=1);

/**
 * Diagnóstico de prontidão das duas IAs de currículo. Somente candidato logado.
 * Nunca devolve chaves, dados do currículo nem executa chamadas cobradas ao Gemini.
 */
require_once __DIR__ . '/middlewares/auth.php';
require_once __DIR__ . '/config/security.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
    header('Allow: GET');
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
    exit;
}
requireWebAuth('pessoa');

$key = (string) ($_ENV['DEVIN_GEMINI_API_KEY']
    ?? $_SERVER['DEVIN_GEMINI_API_KEY']
    ?? getenv('DEVIN_GEMINI_API_KEY') ?: '');
$model = (string) ($_ENV['DEVIN_GEMINI_MODEL']
    ?? $_SERVER['DEVIN_GEMINI_MODEL']
    ?? getenv('DEVIN_GEMINI_MODEL') ?: 'gemini-2.5-flash-lite');
$curl = function_exists('curl_init');
$validModel = preg_match('/^[A-Za-z0-9._-]{1,100}$/', $model) === 1;
$configured = $key !== '' && $curl && $validModel;
$zip = class_exists(ZipArchive::class);
$dom = class_exists(DOMDocument::class);
http_response_code(200);
echo json_encode([
    'sucesso' => true,
    'geracao' => [
        'pronta_para_teste' => $configured,
        'api_configurada' => $key !== '',
        'curl_disponivel' => $curl,
        'modelo_valido' => $validModel,
    ],
    'revisao' => [
        'pdf_pronto_para_teste' => $configured,
        'docx_pronto_para_teste' => $configured && $zip && $dom,
        'zip_disponivel' => $zip,
        'dom_disponivel' => $dom,
    ],
    'observacao' => 'Pronto para teste não comprova chave válida, quota disponível, resposta do Gemini ou gravação no MySQL. Faça geração e revisão reais com currículo fictício autorizado.',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
