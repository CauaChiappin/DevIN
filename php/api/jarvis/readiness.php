<?php
declare(strict_types=1);

/**
 * Diagnóstico somente leitura: identifica os pré-requisitos para habilitar
 * ferramentas JARVIS com segurança. Jamais retorna API keys ou credenciais.
 */
require_once __DIR__ . '/../../controllers/Jarvis/Api.php';
require_once __DIR__ . '/../../controllers/Jarvis/ModelProvider.php';
require_once __DIR__ . '/../../controllers/Security/AdminRolePolicy.php';
require_once __DIR__ . '/../../controllers/Security/AdminStepUp.php';
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') jarvisJson(405,['success'=>false,'message'=>'Método não permitido.']);
$adminId=jarvisAdmin(); // O servidor revalida sessão e confirmação adicional quando exigida.
jarvisLimit($adminId,'readiness',30);
$enabled=static fn(string $key): bool =>
    (string)($_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: '0')==='1';
$flags=[
    'chat_model_configured'=>ModelProvider::configured(),
    'tools_enabled'=>$enabled('DEVIN_JARVIS_ACTIONS_ENABLED'),
    'supervised_deletions_enabled'=>$enabled('DEVIN_JARVIS_DELETIONS_ENABLED'),
    'admin_roles_enabled'=>AdminRolePolicy::enabled(),
    'stepup_enabled'=>AdminStepUp::enabled(),
];
$required=['jarvis_conversations','jarvis_messages','jarvis_tasks','jarvis_task_audit',
    'jarvis_action_events','jarvis_platform_outbox','jarvis_moderation_audit'];
try {
    $db=getDatabaseConnection();
    try {
        $present=[];
        $stmt=$db->prepare('SELECT TABLE_NAME FROM information_schema.TABLES
            WHERE TABLE_SCHEMA=DATABASE()');
        $stmt->execute();
        foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $table) {
            $present[(string)$table['TABLE_NAME']]=true;
        }
        $stmt->close();
    } finally {
        $db->close();
    }
    $missing=array_values(array_filter($required,static fn($table)=>!isset($present[$table])));
    $flags['schema_ready']=$missing===[];
    jarvisJson(200,['success'=>true,'flags'=>$flags,'missing_tables'=>$missing,
        'ready_for_controlled_actions'=>$flags['tools_enabled'] && $flags['schema_ready'],
        'note'=>'Diagnóstico de configuração; não testa SMTP, disponibilidade da IA nem autorização granular.']);
} catch(Throwable $error) {
    error_log('JARVIS readiness failed: '.get_class($error));
    jarvisJson(503,['success'=>false,'message'=>'Não foi possível verificar os pré-requisitos do JARVIS.']);
}
