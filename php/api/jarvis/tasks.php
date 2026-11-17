<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/api/jarvis/tasks.php
 * Finalidade: tasks.
 * Domínio: assistência administrativa.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


require_once __DIR__ . '/../../controllers/Jarvis/Api.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/../../controllers/Jarvis/AdminTaskQueue.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

$adminId = jarvisAdmin(); // Explicação DevIN: Atribui um valor à variável PHP $adminId.
$method = $_SERVER['REQUEST_METHOD'] ?? ''; // Explicação DevIN: Atribui um valor à variável PHP $method.
if ($method === 'POST') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    jarvisCsrf(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    jarvisLimit($adminId, 'tasks-write', 60); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    $body = jarvisBody(); // Explicação DevIN: Atribui um valor à variável PHP $body.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
if (!in_array($method, ['GET', 'POST'], true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    header('Allow: GET, POST'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    jarvisJson(405, ['success' => false, 'message' => 'Método não permitido.']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
session_write_close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    $db = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $db.
    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        if ($method === 'GET') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            jarvisJson(200, ['success' => true, 'tasks' => AdminTaskQueue::list($db, $adminId)]); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $action = $body['action'] ?? null; // Explicação DevIN: Atribui um valor à variável PHP $action.
        if ($action === 'create') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $title = $body['title'] ?? null; // Explicação DevIN: Atribui um valor à variável PHP $title.
            $description = $body['description'] ?? ''; // Explicação DevIN: Atribui um valor à variável PHP $description.
            if (!is_string($title) || !is_string($description)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                jarvisJson(400, ['success' => false, 'message' => 'Informe texto válido.']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $id = AdminTaskQueue::create($db, $adminId, $title, $description); // Explicação DevIN: Atribui um valor à variável PHP $id.
            jarvisJson(200, ['success' => true, 'task_id' => $id]); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (in_array($action, ['start', 'complete', 'cancel'], true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $taskId = filter_var($body['task_id'] ?? null, FILTER_VALIDATE_INT); // Explicação DevIN: Atribui um valor à variável PHP $taskId.
            if (!$taskId || $taskId < 1) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                jarvisJson(400, ['success' => false, 'message' => 'ID de atividade inválido.']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            // Somente atualiza o andamento na fila; não remove vagas, perfis nem outros dados DevIN.
            $state = AdminTaskQueue::change($db, $adminId, $taskId, $action); // Explicação DevIN: Atribui um valor à variável PHP $state.
            jarvisJson(200, ['success' => true, 'status' => $state]); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        jarvisJson(400, ['success' => false, 'message' => 'Ação não permitida.']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
        $db->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} catch (InvalidArgumentException $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    jarvisJson(409, ['success' => false, 'message' => $exception->getMessage()]); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    error_log('JARVIS tasks: ' . $exception->getMessage()); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    jarvisJson(503, ['success' => false, 'message' => 'Fila indisponível. A migração SQL já foi aplicada?']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
