<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

/**
 * Integração do JARVIS Admin contra MySQL isolado e descartável de CI.
 * Nunca execute com o banco do portal. Não usa tokens de API nem sessão real.
 */
if (getenv('JARVIS_TEST_DB') !== 'devin_jarvis_ci') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    throw new RuntimeException('Este teste só pode usar o banco descartável devin_jarvis_ci.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
$db = new mysqli( // Explicação DevIN: Atribui um valor à variável PHP $db.
    (string) (getenv('JARVIS_TEST_HOST') ?: '127.0.0.1'), // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    (string) (getenv('JARVIS_TEST_USER') ?: 'root'), // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    (string) (getenv('JARVIS_TEST_PASSWORD') ?: ''), // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    'devin_jarvis_ci' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$db->set_charset('utf8mb4'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
require_once dirname(__DIR__) . '/php/controllers/Jarvis/ConversationRepository.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once dirname(__DIR__) . '/php/controllers/Jarvis/AdminTaskQueue.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

function assertJarvis(bool $value, string $message): void // Explicação DevIN: Declara função/método assertJarvis, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if (!$value) throw new RuntimeException('JARVIS MySQL: ' . $message); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
function expectJarvisRejection(callable $operation, string $message): void // Explicação DevIN: Declara função/método expectJarvisRejection, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        $operation(); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectJarvisRejection.
    } catch (InvalidArgumentException $expected) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    throw new RuntimeException('JARVIS não rejeitou: ' . $message); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

$admin1 = 101; // Explicação DevIN: Atribui um valor à variável PHP $admin1.
$admin2 = 102; // Explicação DevIN: Atribui um valor à variável PHP $admin2.
$conversationId = ConversationRepository::create($db, $admin1, 'Investigação de vagas'); // Explicação DevIN: Atribui um valor à variável PHP $conversationId.
assertJarvis($conversationId > 0, 'conversa criada'); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectJarvisRejection.
assertJarvis(ConversationRepository::belongsTo($db, $admin1, $conversationId), 'dono da conversa'); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectJarvisRejection.
assertJarvis(!ConversationRepository::belongsTo($db, $admin2, $conversationId), 'isolamento da conversa'); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectJarvisRejection.

ConversationRepository::append($db, $conversationId, 'user', 'Preciso de um resumo das vagas.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectJarvisRejection.
ConversationRepository::append($db, $conversationId, 'assistant', 'Use o resumo local do painel.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectJarvisRejection.
$history = ConversationRepository::latest($db, $admin1, $conversationId); // Explicação DevIN: Atribui um valor à variável PHP $history.
assertJarvis(count($history) === 2 && $history[0]['role'] === 'user' // Explicação DevIN: Continua expressão ou bloco pertencente a função expectJarvisRejection.
    && $history[1]['role'] === 'assistant', 'ordem e conteúdo do histórico'); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
assertJarvis(count(ConversationRepository::list($db, $admin1, 'resumo vagas')) === 1, // Explicação DevIN: Continua expressão ou bloco pertencente a função expectJarvisRejection.
    'pesquisa por termos independentes no histórico'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
assertJarvis(count(ConversationRepository::list($db, $admin2, 'vagas')) === 0, // Explicação DevIN: Continua expressão ou bloco pertencente a função expectJarvisRejection.
    'pesquisa isolada por ADM'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.

expectJarvisRejection( // Explicação DevIN: Continua expressão ou bloco pertencente a função expectJarvisRejection.
    static fn () => ConversationRepository::latest($db, $admin2, $conversationId), // Explicação DevIN: Continua expressão ou bloco pertencente a função expectJarvisRejection.
    'ID de conversa de outro administrador' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
assertJarvis(!ConversationRepository::delete($db, $admin2, $conversationId), 'exclusão por outro ADM impedida'); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectJarvisRejection.
expectJarvisRejection( // Explicação DevIN: Continua expressão ou bloco pertencente a função expectJarvisRejection.
    static fn () => ConversationRepository::append($db, $conversationId, 'system', 'não permitido'), // Explicação DevIN: Continua expressão ou bloco pertencente a função expectJarvisRejection.
    'papel de mensagem indevido' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

$taskId = AdminTaskQueue::create($db, $admin1, 'Revisar denúncias', 'Somente análise manual'); // Explicação DevIN: Atribui um valor à variável PHP $taskId.
assertJarvis($taskId > 0, 'atividade criada'); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectJarvisRejection.
assertJarvis(count(AdminTaskQueue::list($db, $admin1)) === 1, 'lista de atividades do dono'); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectJarvisRejection.
assertJarvis(AdminTaskQueue::list($db, $admin2) === [], 'nenhuma atividade de outro ADM'); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectJarvisRejection.
expectJarvisRejection( // Explicação DevIN: Continua expressão ou bloco pertencente a função expectJarvisRejection.
    static fn () => AdminTaskQueue::change($db, $admin2, $taskId, 'start'), // Explicação DevIN: Continua expressão ou bloco pertencente a função expectJarvisRejection.
    'outro ADM não pode iniciar tarefa' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
expectJarvisRejection( // Explicação DevIN: Continua expressão ou bloco pertencente a função expectJarvisRejection.
    static fn () => AdminTaskQueue::change($db, $admin1, $taskId, 'complete'), // Explicação DevIN: Continua expressão ou bloco pertencente a função expectJarvisRejection.
    'não pode concluir antes de iniciar' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
assertJarvis(AdminTaskQueue::change($db, $admin1, $taskId, 'start') === 'in_progress', // Explicação DevIN: Continua expressão ou bloco pertencente a função expectJarvisRejection.
    'transição para em análise'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
assertJarvis(AdminTaskQueue::change($db, $admin1, $taskId, 'complete') === 'done', // Explicação DevIN: Continua expressão ou bloco pertencente a função expectJarvisRejection.
    'conclusão da tarefa'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
expectJarvisRejection( // Explicação DevIN: Continua expressão ou bloco pertencente a função expectJarvisRejection.
    static fn () => AdminTaskQueue::change($db, $admin1, $taskId, 'cancel'), // Explicação DevIN: Continua expressão ou bloco pertencente a função expectJarvisRejection.
    'tarefa concluída não pode ser cancelada' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$audit = $db->query('SELECT action FROM jarvis_task_audit WHERE task_id = ' . $taskId . ' ORDER BY id') // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
    ->fetch_all(MYSQLI_ASSOC); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
assertJarvis(array_column($audit, 'action') === ['created','started','completed'], // Explicação DevIN: Continua expressão ou bloco pertencente a função expectJarvisRejection.
    'auditoria das transições'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.

assertJarvis(ConversationRepository::delete($db, $admin1, $conversationId), 'dono exclui conversa'); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectJarvisRejection.
$stmt = $db->prepare('SELECT COUNT(*) FROM jarvis_messages WHERE conversation_id = ?'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
$stmt->bind_param('i', $conversationId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
$stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
$count = (int) $stmt->get_result()->fetch_row()[0]; // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
$stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectJarvisRejection.
assertJarvis($count === 0, 'exclusão da conversa remove mensagens associadas'); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectJarvisRejection.
$db->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função expectJarvisRejection.

$api = file_get_contents(dirname(__DIR__) . '/php/controllers/Jarvis/Api.php'); // Explicação DevIN: Atribui um valor à variável PHP $api.
$widget = file_get_contents(dirname(__DIR__) . '/php/components/jarvis-admin-widget.php'); // Explicação DevIN: Atribui um valor à variável PHP $widget.
$frontend = file_get_contents(dirname(__DIR__) . '/js/jarvis-admin.js'); // Explicação DevIN: Atribui um valor à variável PHP $frontend.
assertJarvis(str_contains($api, "usuario_tipo") && str_contains($api, "jarvisCsrf"), // Explicação DevIN: Continua expressão ou bloco pertencente a função expectJarvisRejection.
    'controle de ADM e CSRF preservados'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
// Agora há Token 2 opcional e obrigatório quando a flag está ativa.
assertJarvis(str_contains($api, "AdminStepUp::enabled()")
    && str_contains($api, "AdminStepUp::allowed('ai')"),
    'API protege JARVIS ao ativar Token 2');
assertJarvis(str_contains($widget, 'admin_security_ai.php'),
    'widget oferece desbloqueio seguro');
assertJarvis(!str_contains($frontend, 'DEVIN_JARVIS_API_KEY'),
    'frontend não contém credenciais de IA');
echo "JARVIS Admin: MySQL, permissão ADM, auditoria e Token 2 opcional OK\n"; // Explicação DevIN: Produz saída ou conteúdo da resposta do programa.
