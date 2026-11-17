<?php
declare(strict_types=1);

/**
 * API do Suporte DevIN. Todas as ações passam por autenticação e controle
 * de proprietário ou perfil administrativo; IDs do navegador não autorizam
 * leitura de outros usuários. As mutações exigem token CSRF da sessão.
 */
require_once __DIR__ . '/middlewares/auth.php'; // Autentica o usuário pela sessão real.
require_once __DIR__ . '/config/security.php'; // Oferece o CSRF obrigatório.
require_once __DIR__ . '/config/database.php'; // Abre MySQL com charset utf8mb4.
require_once __DIR__ . '/controllers/SupportDesk.php'; // Regras de suporte e anexos.
require_once __DIR__ . '/controllers/ChatStorage.php'; // Notificações internas "Suporte DevIN".
require_once __DIR__ . '/MailerHelper.php'; // Correio SMTP configurado no servidor.

header('Cache-Control: no-store, private'); // Respostas têm dados pessoais.
header('X-Content-Type-Options: nosniff'); // Não deixar o navegador inferir HTML em anexo.

// Resposta JSON padronizada, usada tanto para sucesso quanto para erros.
function supportJson(int $status, array $payload): never
{
    http_response_code($status); // Estado HTTP corresponde à situação real.
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

// Permitir leitura apenas a usuário logado e com tipo de conta reconhecido.
$actor = requireWebAuth();
if (!in_array($actor['tipo'] ?? '', ['pessoa','empresa','adm'], true)) {
    supportJson(403, ['sucesso'=>false,'mensagem'=>'Perfil sem permissão para suporte.']);
}

// Converter IDs da URL/FormData sem coerção de strings arbitrárias.
$idValue = static function (mixed $raw): int {
    $value = filter_var($raw, FILTER_VALIDATE_INT);
    return $value === false || $value < 1 ? 0 : $value;
};

try {
    // Controla 20 MiB de arquivo, mais margem para dados multipart.
    if ($_SERVER['REQUEST_METHOD'] === 'POST'
        && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 23 * 1024 * 1024) {
        supportJson(413, ['sucesso'=>false,'mensagem'=>'O formulário ultrapassa o limite de 20 MB por anexo.']);
    }
    $db = getDatabaseConnection();
    try {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $action = (string)($_GET['action'] ?? 'list');
            if ($action === 'list') {
                // A consulta de ADM permite visão geral; usuários veem somente os próprios registros.
                $rows = $actor['tipo'] === 'adm'
                    ? SupportDesk::listAdmin($db) : SupportDesk::listOwn($db, $actor);
                supportJson(200, ['sucesso'=>true,'tickets'=>$rows]);
            }
            if ($action === 'detail') {
                $ticket = SupportDesk::detail($db, $idValue($_GET['id'] ?? null), $actor);
                supportJson(200, ['sucesso'=>true] + $ticket);
            }
            if ($action === 'download') {
                // Nunca fornecer URL direta; o servidor autentica antes de transmitir bytes.
                $attachment = SupportDesk::attachment($db, $idValue($_GET['id'] ?? null), $actor);
                header('Content-Type: application/octet-stream');
                header('Content-Length: ' . (int)$attachment['bytes']);
                header("Content-Disposition: attachment; filename=\"anexo-suporte\"; filename*=UTF-8''" .
                    rawurlencode((string)$attachment['name']));
                readfile((string)$attachment['path']);
                exit;
            }
            supportJson(404, ['sucesso'=>false,'mensagem'=>'Consulta não reconhecida.']);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            supportJson(405, ['sucesso'=>false,'mensagem'=>'Método HTTP não permitido.']);
        }
        requireValidCsrf(); // Sem CSRF válido, nenhuma gravação é realizada.
        $action = (string)($_POST['action'] ?? '');
        if ($action === 'create' && $actor['tipo'] !== 'adm') {
            // Categoria e descrição são obrigatórias mesmo se o HTML for adulterado.
            $ticketId = SupportDesk::create($db, $actor,
                (string)($_POST['category'] ?? ''),
                (string)($_POST['description'] ?? ''),
                isset($_FILES['attachment']) && is_array($_FILES['attachment'])
                    ? $_FILES['attachment'] : null
            );
            supportJson(201, ['sucesso'=>true,'id'=>$ticketId,
                'mensagem'=>'Chamado #' . $ticketId . ' recebido pelo Suporte DevIN.']);
        }
        if ($actor['tipo'] !== 'adm') {
            supportJson(403, ['sucesso'=>false,'mensagem'=>'Apenas ADMs podem responder ou encerrar chamados.']);
        }
        if ($action === 'reply') {
            // O texto é validado pelo serviço e sua autoria fica registrada pelo ID ADM.
            $result = SupportDesk::reply($db, (int)$actor['id'],
                $idValue($_POST['id'] ?? null), (string)($_POST['message'] ?? ''));
            supportJson(200, ['sucesso'=>true] + $result + [
                'mensagem'=>'Resposta registrada. Verifique os estados de entrega.'
            ]);
        }
        if ($action === 'retry') {
            // Reenvia somente canais ainda pendentes, sem duplicar o texto no banco.
            $result = SupportDesk::notify($db, $idValue($_POST['reply_id'] ?? null));
            supportJson(200, ['sucesso'=>true] + $result);
        }
        if ($action === 'close') {
            SupportDesk::close($db, $idValue($_POST['id'] ?? null));
            supportJson(200, ['sucesso'=>true,'mensagem'=>'Chamado encerrado.']);
        }
        supportJson(400, ['sucesso'=>false,'mensagem'=>'Operação inválida para esta conta.']);
    } finally {
        $db->close(); // Fecha conexão mesmo em caso de erro recuperável.
    }
} catch (InvalidArgumentException $error) {
    supportJson(422, ['sucesso'=>false,'mensagem'=>$error->getMessage()]);
} catch (DomainException $error) {
    supportJson(403, ['sucesso'=>false,'mensagem'=>$error->getMessage()]);
} catch (Throwable $error) {
    // Não revelar SQL, paths privados, endereço de servidor ou credenciais.
    error_log('Falha Suporte DevIN: ' . get_class($error) . ': ' . $error->getMessage());
    supportJson(503, ['sucesso'=>false,
        'mensagem'=>'Não foi possível concluir a solicitação. Verifique a configuração do suporte.']);
}
