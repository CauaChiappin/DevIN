<?php
declare(strict_types=1);

/**
 * Integração do JARVIS Admin contra MySQL isolado e descartável de CI.
 * Nunca execute com o banco do portal. Não usa tokens de API nem sessão real.
 */
if (getenv('JARVIS_TEST_DB') !== 'devin_jarvis_ci') {
    throw new RuntimeException('Este teste só pode usar o banco descartável devin_jarvis_ci.');
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = new mysqli(
    (string) (getenv('JARVIS_TEST_HOST') ?: '127.0.0.1'),
    (string) (getenv('JARVIS_TEST_USER') ?: 'root'),
    (string) (getenv('JARVIS_TEST_PASSWORD') ?: ''),
    'devin_jarvis_ci'
);
$db->set_charset('utf8mb4');
require_once dirname(__DIR__) . '/php/controllers/Jarvis/ConversationRepository.php';
require_once dirname(__DIR__) . '/php/controllers/Jarvis/AdminTaskQueue.php';

function assertJarvis(bool $value, string $message): void
{
    if (!$value) throw new RuntimeException('JARVIS MySQL: ' . $message);
}
function expectJarvisRejection(callable $operation, string $message): void
{
    try {
        $operation();
    } catch (InvalidArgumentException $expected) {
        return;
    }
    throw new RuntimeException('JARVIS não rejeitou: ' . $message);
}

$admin1 = 101;
$admin2 = 102;
$conversationId = ConversationRepository::create($db, $admin1, 'Investigação de vagas');
assertJarvis($conversationId > 0, 'conversa criada');
assertJarvis(ConversationRepository::belongsTo($db, $admin1, $conversationId), 'dono da conversa');
assertJarvis(!ConversationRepository::belongsTo($db, $admin2, $conversationId), 'isolamento da conversa');

ConversationRepository::append($db, $conversationId, 'user', 'Preciso de um resumo das vagas.');
ConversationRepository::append($db, $conversationId, 'assistant', 'Use o resumo local do painel.');
$history = ConversationRepository::latest($db, $admin1, $conversationId);
assertJarvis(count($history) === 2 && $history[0]['role'] === 'user'
    && $history[1]['role'] === 'assistant', 'ordem e conteúdo do histórico');
assertJarvis(count(ConversationRepository::list($db, $admin1, 'resumo vagas')) === 1,
    'pesquisa por termos independentes no histórico');
assertJarvis(count(ConversationRepository::list($db, $admin2, 'vagas')) === 0,
    'pesquisa isolada por ADM');

expectJarvisRejection(
    static fn () => ConversationRepository::latest($db, $admin2, $conversationId),
    'ID de conversa de outro administrador'
);
assertJarvis(!ConversationRepository::delete($db, $admin2, $conversationId), 'exclusão por outro ADM impedida');
expectJarvisRejection(
    static fn () => ConversationRepository::append($db, $conversationId, 'system', 'não permitido'),
    'papel de mensagem indevido'
);

$taskId = AdminTaskQueue::create($db, $admin1, 'Revisar denúncias', 'Somente análise manual');
assertJarvis($taskId > 0, 'atividade criada');
assertJarvis(count(AdminTaskQueue::list($db, $admin1)) === 1, 'lista de atividades do dono');
assertJarvis(AdminTaskQueue::list($db, $admin2) === [], 'nenhuma atividade de outro ADM');
expectJarvisRejection(
    static fn () => AdminTaskQueue::change($db, $admin2, $taskId, 'start'),
    'outro ADM não pode iniciar tarefa'
);
expectJarvisRejection(
    static fn () => AdminTaskQueue::change($db, $admin1, $taskId, 'complete'),
    'não pode concluir antes de iniciar'
);
assertJarvis(AdminTaskQueue::change($db, $admin1, $taskId, 'start') === 'in_progress',
    'transição para em análise');
assertJarvis(AdminTaskQueue::change($db, $admin1, $taskId, 'complete') === 'done',
    'conclusão da tarefa');
expectJarvisRejection(
    static fn () => AdminTaskQueue::change($db, $admin1, $taskId, 'cancel'),
    'tarefa concluída não pode ser cancelada'
);
$audit = $db->query('SELECT action FROM jarvis_task_audit WHERE task_id = ' . $taskId . ' ORDER BY id')
    ->fetch_all(MYSQLI_ASSOC);
assertJarvis(array_column($audit, 'action') === ['created','started','completed'],
    'auditoria das transições');

assertJarvis(ConversationRepository::delete($db, $admin1, $conversationId), 'dono exclui conversa');
$stmt = $db->prepare('SELECT COUNT(*) FROM jarvis_messages WHERE conversation_id = ?');
$stmt->bind_param('i', $conversationId);
$stmt->execute();
$count = (int) $stmt->get_result()->fetch_row()[0];
$stmt->close();
assertJarvis($count === 0, 'exclusão da conversa remove mensagens associadas');
$db->close();

$api = file_get_contents(dirname(__DIR__) . '/php/controllers/Jarvis/Api.php');
$widget = file_get_contents(dirname(__DIR__) . '/php/components/jarvis-admin-widget.php');
$frontend = file_get_contents(dirname(__DIR__) . '/js/jarvis-admin.js');
assertJarvis(str_contains($api, "usuario_tipo") && str_contains($api, "jarvisCsrf"),
    'controle de ADM e CSRF preservados');
foreach ([$api, $widget, $frontend] as $source) {
    assertJarvis(!str_contains($source, 'AdminStepUp')
        && !str_contains($source, 'data-jarvis-security')
        && !str_contains($source, 'Token 2'), 'sem dependência de 2FA');
}
echo "JARVIS Admin: MySQL, permissão ADM, auditoria e modo sem 2FA OK\n";
