<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/api/jarvis/conversations.php
 * Finalidade: conversations.
 * Domínio: assistência administrativa.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


require_once __DIR__ . '/../../controllers/Jarvis/Api.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/../../controllers/Jarvis/ConversationRepository.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

$adminId = jarvisAdmin(); // Explicação DevIN: Atribui um valor à variável PHP $adminId.
$method = $_SERVER['REQUEST_METHOD'] ?? ''; // Explicação DevIN: Atribui um valor à variável PHP $method.
if ($method === 'POST') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    jarvisCsrf(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    jarvisLimit($adminId, 'conversations-write', 60); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
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
            $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT); // Explicação DevIN: Atribui um valor à variável PHP $id.
            if ($id !== null && $id !== false && $id > 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                $messages = ConversationRepository::latest($db, $adminId, $id); // Explicação DevIN: Atribui um valor à variável PHP $messages.
                jarvisJson(200, ['success' => true, 'conversation_id' => $id, 'messages' => $messages]); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $search = is_string($_GET['q'] ?? null) ? $_GET['q'] : ''; // Explicação DevIN: Atribui um valor à variável PHP $search.
            $list = ConversationRepository::list($db, $adminId, $search); // Explicação DevIN: Atribui um valor à variável PHP $list.
            jarvisJson(200, ['success' => true, 'conversations' => $list]); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $action = $body['action'] ?? null; // Explicação DevIN: Atribui um valor à variável PHP $action.
        if ($action === 'create') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $id = ConversationRepository::create($db, $adminId); // Explicação DevIN: Atribui um valor à variável PHP $id.
            jarvisJson(200, ['success' => true, 'conversation_id' => $id]); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // Logs de chat local (chamados, respostas em tela e falhas): não são auditoria de ações oficiais.
        if ($action === 'append') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $entries = $body['entries'] ?? null; // Explicação DevIN: Atribui um valor à variável PHP $entries.
            if (!is_array($entries) || !array_is_list($entries) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                || count($entries) < 1 || count($entries) > 4) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                jarvisJson(400, ['success' => false, 'message' => 'Mensagens de histórico inválidas.']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $clean = []; // Explicação DevIN: Atribui um valor à variável PHP $clean.
            foreach ($entries as $entry) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                if (!is_array($entry) || !in_array($entry['role'] ?? null, ['user', 'assistant'], true) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    || !is_string($entry['content'] ?? null)) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    jarvisJson(400, ['success' => false, 'message' => 'Formato de mensagem inválido.']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                $content = trim($entry['content']); // Explicação DevIN: Atribui um valor à variável PHP $content.
                if ($content === '' || mb_strlen($content, 'UTF-8') > 5000) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    jarvisJson(400, ['success' => false, 'message' => 'Mensagem fora do limite de 5.000 caracteres.']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                $clean[] = ['role' => $entry['role'], 'content' => $content]; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $given = $body['conversation_id'] ?? null; // Explicação DevIN: Atribui um valor à variável PHP $given.
            $conversationId = $given === null ? null : filter_var($given, FILTER_VALIDATE_INT); // Explicação DevIN: Atribui um valor à variável PHP $conversationId.
            if ($given !== null && (!$conversationId || $conversationId < 1)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                jarvisJson(400, ['success' => false, 'message' => 'ID de conversa inválido.']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            if ($conversationId !== null && !ConversationRepository::belongsTo($db, $adminId, $conversationId)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                jarvisJson(404, ['success' => false, 'message' => 'Conversa não encontrada.']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $db->begin_transaction(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                if ($conversationId === null) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    $title = is_string($body['title'] ?? null) // Explicação DevIN: Atribui um valor à variável PHP $title.
                        ? mb_substr(trim($body['title']), 0, 80, 'UTF-8') : ''; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    $conversationId = ConversationRepository::create($db, $adminId, // Explicação DevIN: Atribui um valor à variável PHP $conversationId.
                        $title !== '' ? $title : 'Atendimento JARVIS'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                foreach ($clean as $entry) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                    ConversationRepository::append($db, $conversationId, $entry['role'], $entry['content']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                $db->commit(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
                $db->rollback(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                throw $exception; // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            jarvisJson(200, ['success' => true, 'conversation_id' => (int) $conversationId]); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if ($action === 'delete') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $id = filter_var($body['conversation_id'] ?? null, FILTER_VALIDATE_INT); // Explicação DevIN: Atribui um valor à variável PHP $id.
            if (!$id || $id < 1 || !ConversationRepository::delete($db, $adminId, $id)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                jarvisJson(404, ['success' => false, 'message' => 'Conversa não encontrada.']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            jarvisJson(200, ['success' => true]); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        jarvisJson(400, ['success' => false, 'message' => 'Ação desconhecida.']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
        $db->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} catch (InvalidArgumentException $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    jarvisJson(404, ['success' => false, 'message' => 'Conversa não encontrada.']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    error_log('JARVIS conversations: ' . $exception->getMessage()); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    jarvisJson(503, ['success' => false, 'message' => 'Histórico indisponível. A migração SQL já foi aplicada?']); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
