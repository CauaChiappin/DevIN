<?php

declare(strict_types=1);

require_once __DIR__ . '/middlewares/auth.php';
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/controllers/JobReports.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');

function responderDenunciaVaga(array $data, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Allow: POST');
        responderDenunciaVaga(['sucesso' => false, 'mensagem' => 'Método não permitido.'], 405);
    }

    $reporter = requireWebAuth();
    if (!in_array($reporter['tipo'], ['pessoa', 'empresa'], true)) {
        responderDenunciaVaga(['sucesso' => false, 'mensagem' => 'Este perfil não pode denunciar vagas.'], 403);
    }

    requireValidCsrf();
    $input = json_decode((string) file_get_contents('php://input'), true);
    if (!is_array($input)) {
        throw new InvalidArgumentException('Não foi possível ler os dados da denúncia.');
    }

    $jobId = filter_var($input['id_vaga'] ?? null, FILTER_VALIDATE_INT);
    if ($jobId === false) {
        throw new InvalidArgumentException('Vaga inválida.');
    }

    $conn = getDatabaseConnection();
    try {
        ensureJobReportsSchema($conn);
        submitJobReport(
            $conn,
            $reporter,
            $jobId,
            is_string($input['motivo'] ?? null) ? trim($input['motivo']) : '',
            is_string($input['detalhes'] ?? null) ? trim($input['detalhes']) : ''
        );
    } finally {
        $conn->close();
    }

    responderDenunciaVaga([
        'sucesso' => true,
        'mensagem' => 'Denúncia enviada para análise da equipe DevIN.',
    ], 201);
} catch (InvalidArgumentException $exception) {
    responderDenunciaVaga(['sucesso' => false, 'mensagem' => $exception->getMessage()], 400);
} catch (mysqli_sql_exception $exception) {
    error_log('Falha ao salvar denúncia de vaga: ' . $exception->getMessage());
    responderDenunciaVaga(['sucesso' => false, 'mensagem' => 'Não foi possível registrar a denúncia agora. Tente novamente.'], 500);
} catch (Throwable $exception) {
    error_log('Erro ao receber denúncia de vaga: ' . $exception->getMessage());
    responderDenunciaVaga(['sucesso' => false, 'mensagem' => 'Não foi possível registrar a denúncia agora. Tente novamente.'], 500);
}
