<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

/**
 * Diagnóstico somente leitura: identifica os pré-requisitos para habilitar
 * ferramentas JARVIS com segurança. Jamais retorna API keys ou credenciais.
 */
require_once __DIR__ . '/../../controllers/Jarvis/Api.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/../../controllers/Jarvis/ModelProvider.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/../../controllers/Security/AdminRolePolicy.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/../../controllers/Security/AdminStepUp.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') jarvisJson(405,['success'=>false,'message'=>'Método não permitido.']); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
$adminId=jarvisAdmin(); // O servidor revalida sessão e confirmação adicional quando exigida.
jarvisLimit($adminId,'readiness',30); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
$enabled=static fn(string $key): bool => // Explicação DevIN: Atribui um valor à variável PHP $enabled.
    (string)($_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?: '0')==='1'; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
// Diagnóstico de modelo autorizado a ADM; nunca incluir qualquer API key.
$modelProvider=strtolower(trim((string)($_ENV['DEVIN_JARVIS_PROVIDER']
    ?? $_SERVER['DEVIN_JARVIS_PROVIDER'] ?? getenv('DEVIN_JARVIS_PROVIDER') ?: '')));
$modelId=trim((string)($_ENV['DEVIN_JARVIS_MODEL']
    ?? $_SERVER['DEVIN_JARVIS_MODEL'] ?? getenv('DEVIN_JARVIS_MODEL') ?: ''));
$modelDiagnostic=[
    'provider'=>$modelProvider,
    'model'=>mb_substr($modelId,0,120,'UTF-8'),
    'model_identifier_valid'=>ModelRouter::validModel($modelId),
    'free_only_policy'=>$modelProvider === 'openrouter',
    'free_router_recovery'=>$modelProvider === 'openrouter' && $modelId !== 'openrouter/free',
    'curl_available'=>function_exists('curl_init'),
    'live_provider_checked'=>false,
];
$flags=[ // Explicação DevIN: Atribui um valor à variável PHP $flags.
    'chat_model_configured'=>ModelProvider::configured(), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'tools_enabled'=>$enabled('DEVIN_JARVIS_ACTIONS_ENABLED'), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'supervised_deletions_enabled'=>$enabled('DEVIN_JARVIS_DELETIONS_ENABLED'), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'admin_roles_enabled'=>AdminRolePolicy::enabled(), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'stepup_enabled'=>AdminStepUp::enabled(), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
$required=['jarvis_conversations','jarvis_messages','jarvis_tasks','jarvis_task_audit', // Explicação DevIN: Atribui um valor à variável PHP $required.
    'jarvis_action_events','jarvis_platform_outbox','jarvis_moderation_audit']; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    $db=getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $db.
    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        $present=[]; // Explicação DevIN: Atribui um valor à variável PHP $present.
        $stmt=$db->prepare('SELECT TABLE_NAME FROM information_schema.TABLES
            WHERE TABLE_SCHEMA=DATABASE()'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $table) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            $present[(string)$table['TABLE_NAME']]=true; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $stmt->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
        $db->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    $missing=array_values(array_filter($required,static fn($table)=>!isset($present[$table]))); // Explicação DevIN: Atribui um valor à variável PHP $missing.
    $flags['schema_ready']=$missing===[]; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    jarvisJson(200,['success'=>true,'flags'=>$flags,'model_diagnostic'=>$modelDiagnostic,'missing_tables'=>$missing, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        'ready_for_controlled_actions'=>$flags['tools_enabled'] && $flags['schema_ready'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'note'=>'Diagnóstico de configuração; não testa SMTP, disponibilidade da IA nem autorização granular.']); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
} catch(Throwable $error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    error_log('JARVIS readiness failed: '.get_class($error)); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    jarvisJson(503,['success'=>false,'message'=>'Não foi possível verificar os pré-requisitos do JARVIS.']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
