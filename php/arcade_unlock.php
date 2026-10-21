<?php

declare(strict_types=1);

require_once __DIR__ . '/middlewares/auth.php';
require_once __DIR__ . '/config/security.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$usuario = requireWebAuth();
requireValidCsrf();

$sequenciaEsperada = [
    'ArrowUp', 'ArrowUp', 'ArrowDown', 'ArrowDown',
    'ArrowLeft', 'ArrowRight', 'ArrowLeft', 'ArrowRight',
    'KeyB', 'KeyA', 'Enter',
];
$sequenciaRecebida = json_decode(requestString($_POST, 'sequencia'), true);

if (!is_array($sequenciaRecebida) || $sequenciaRecebida !== $sequenciaEsperada) {
    http_response_code(403);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Sequência inválida.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$_SESSION['arcade_unlock'] = [
    'id' => $usuario['id'],
    'tipo' => $usuario['tipo'],
    'liberado_em' => time(),
];

echo json_encode([
    'sucesso' => true,
    'url' => APP_BASE_URL . '/php/arcade.php',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
