<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

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
function supportJson(int $status, array $payload): never // Explicação DevIN: Declara função/método supportJson, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    http_response_code($status); // Estado HTTP corresponde à situação real.
    header('Content-Type: application/json; charset=utf-8'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
    exit; // Explicação DevIN: Continua expressão ou bloco pertencente a função supportJson.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Permitir leitura apenas a usuário logado e com tipo de conta reconhecido.
$actor = requireWebAuth(); // Explicação DevIN: Atribui um valor à variável PHP $actor.
if (!in_array($actor['tipo'] ?? '', ['pessoa','empresa','adm'], true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    supportJson(403, ['sucesso'=>false,'mensagem'=>'Perfil sem permissão para suporte.']); // Explicação DevIN: Continua expressão ou bloco pertencente a função supportJson.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Converter IDs da URL/FormData sem coerção de strings arbitrárias.
$idValue = static function (mixed $raw): int { // Explicação DevIN: Define função anônima usada como operação ou callback.
    $value = filter_var($raw, FILTER_VALIDATE_INT); // Explicação DevIN: Atribui um valor à variável PHP $value.
    return $value === false || $value < 1 ? 0 : $value; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
}; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    // Controla 20 MiB de arquivo, mais margem para dados multipart.
    if ($_SERVER['REQUEST_METHOD'] === 'POST' // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 23 * 1024 * 1024) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        supportJson(413, ['sucesso'=>false,'mensagem'=>'O formulário ultrapassa o limite de 20 MB por anexo.']); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    $db = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $db.
    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        if ($_SERVER['REQUEST_METHOD'] === 'GET') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $action = (string)($_GET['action'] ?? 'list'); // Explicação DevIN: Atribui um valor à variável PHP $action.
            if ($action === 'list') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                // A consulta de ADM permite visão geral; usuários veem somente os próprios registros.
                $rows = $actor['tipo'] === 'adm' // Explicação DevIN: Atribui um valor à variável PHP $rows.
                    ? SupportDesk::listAdmin($db) : SupportDesk::listOwn($db, $actor); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                supportJson(200, ['sucesso'=>true,'tickets'=>$rows]); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            if ($action === 'detail') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                $ticket = SupportDesk::detail($db, $idValue($_GET['id'] ?? null), $actor); // Explicação DevIN: Atribui um valor à variável PHP $ticket.
                supportJson(200, ['sucesso'=>true] + $ticket); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            if ($action === 'download') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                // Nunca fornecer URL direta; o servidor autentica antes de transmitir bytes.
                $attachment = SupportDesk::attachment($db, $idValue($_GET['id'] ?? null), $actor); // Explicação DevIN: Atribui um valor à variável PHP $attachment.
                header('Content-Type: application/octet-stream'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
                header('Content-Length: ' . (int)$attachment['bytes']); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
                header("Content-Disposition: attachment; filename=\"anexo-suporte\"; filename*=UTF-8''" . // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
                    rawurlencode((string)$attachment['name'])); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                readfile((string)$attachment['path']); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                exit; // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            supportJson(404, ['sucesso'=>false,'mensagem'=>'Consulta não reconhecida.']); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            supportJson(405, ['sucesso'=>false,'mensagem'=>'Método HTTP não permitido.']); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        requireValidCsrf(); // Sem CSRF válido, nenhuma gravação é realizada.
        $action = (string)($_POST['action'] ?? ''); // Explicação DevIN: Atribui um valor à variável PHP $action.
        if ($action === 'create' && $actor['tipo'] !== 'adm') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            // Categoria e descrição são obrigatórias mesmo se o HTML for adulterado.
            $ticketId = SupportDesk::create($db, $actor, // Explicação DevIN: Atribui um valor à variável PHP $ticketId.
                (string)($_POST['category'] ?? ''), // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                (string)($_POST['description'] ?? ''), // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                isset($_FILES['attachment']) && is_array($_FILES['attachment']) // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                    ? $_FILES['attachment'] : null // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            supportJson(201, ['sucesso'=>true,'id'=>$ticketId, // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                'mensagem'=>'Chamado #' . $ticketId . ' recebido pelo Suporte DevIN.']); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if ($actor['tipo'] !== 'adm') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            supportJson(403, ['sucesso'=>false,'mensagem'=>'Apenas ADMs podem responder ou encerrar chamados.']); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if ($action === 'reply') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            // O texto é validado pelo serviço e sua autoria fica registrada pelo ID ADM.
            $result = SupportDesk::reply($db, (int)$actor['id'], // Explicação DevIN: Atribui um valor à variável PHP $result.
                $idValue($_POST['id'] ?? null), (string)($_POST['message'] ?? '')); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
            supportJson(200, ['sucesso'=>true] + $result + [ // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                'mensagem'=>'Resposta registrada. Verifique os estados de entrega.' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            ]); // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if ($action === 'retry') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            // Reenvia somente canais ainda pendentes, sem duplicar o texto no banco.
            $result = SupportDesk::notify($db, $idValue($_POST['reply_id'] ?? null)); // Explicação DevIN: Atribui um valor à variável PHP $result.
            supportJson(200, ['sucesso'=>true] + $result); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if ($action === 'close') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            SupportDesk::close($db, $idValue($_POST['id'] ?? null)); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
            supportJson(200, ['sucesso'=>true,'mensagem'=>'Chamado encerrado.']); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        supportJson(400, ['sucesso'=>false,'mensagem'=>'Operação inválida para esta conta.']); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
        $db->close(); // Fecha conexão mesmo em caso de erro recuperável.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} catch (InvalidArgumentException $error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    supportJson(422, ['sucesso'=>false,'mensagem'=>$error->getMessage()]); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
} catch (DomainException $error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    supportJson(403, ['sucesso'=>false,'mensagem'=>$error->getMessage()]); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
} catch (Throwable $error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    // Não revelar SQL, paths privados, endereço de servidor ou credenciais.
    error_log('Falha Suporte DevIN: ' . get_class($error) . ': ' . $error->getMessage()); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    supportJson(503, ['sucesso'=>false, // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        'mensagem'=>'Não foi possível concluir a solicitação. Verifique a configuração do suporte.']); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
