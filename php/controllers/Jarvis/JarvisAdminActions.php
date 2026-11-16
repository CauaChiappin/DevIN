<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

require_once __DIR__ . '/JarvisAdminRecords.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/../ChatStorage.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

/**
 * Trilhas de auditoria append-only no aplicativo. Nunca registrar texto da
 * mensagem, prompts, email, CPF, senha, token ou conteúdo de denúncia.
 */
final class JarvisAdminActivity // Explicação DevIN: Declara o tipo JarvisAdminActivity e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    private const EVENTS = [ // Explicação DevIN: Define membro, visibilidade ou método de uma classe.
        'search','inspect','analysis','draft_generated','draft_manual', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'draft_revised','message_sent','message_failed','draft_cancelled','action_denied' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
    public static function log(mysqli $db, int $admin, string $origin, string $event, // Explicação DevIN: Declara função/método log, com seus parâmetros e comportamento.
        string $status, string $targetType='none', ?int $targetId=null, string $detail=''): void // Explicação DevIN: Continua expressão ou bloco pertencente a função log.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if ($admin < 1 || !in_array($origin,['ai','admin','system'],true) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            || !in_array($event,self::EVENTS,true) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            || !in_array($status,['success','failure','denied'],true) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            || preg_match('/^[a-z0-9_-]{0,60}$/D',$detail)!==1 // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            || preg_match('/^[a-z0-9_-]{1,24}$/D',$targetType)!==1) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            throw new InvalidArgumentException('Evento de auditoria inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $stmt=$db->prepare('INSERT INTO jarvis_action_events ' // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            . '(admin_id,origin,event_name,status,target_type,target_id,detail_code) ' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            . 'VALUES (?,?,?,?,?,?,?)'); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        $stmt->bind_param('issssis',$admin,$origin,$event,$status,$targetType,$targetId,$detail); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função log.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function latest(mysqli $db, int $admin): array // Explicação DevIN: Declara função/método latest, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $stmt=$db->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            'SELECT id,origin,event_name,status,target_type,target_id,detail_code,created_at ' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            . 'FROM jarvis_action_events WHERE admin_id=? ORDER BY id DESC LIMIT 80' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $stmt->bind_param('i',$admin); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $rows=$stmt->get_result()->fetch_all(MYSQLI_ASSOC); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função latest.
        return $rows; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /**
     * Painel de fiscalização: qualquer um dos quatro ADMs pode verificar
     * o que foi registrado pela IA e o que foi aprovado por outros ADMs.
     * O log omite o texto e os dados privados do destinatário.
     */
    public static function latestAll(mysqli $db): array // Explicação DevIN: Declara função/método latestAll, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $result=$db->query('SELECT id,admin_id,origin,event_name,status,' // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            . 'target_type,target_id,detail_code,created_at ' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            . 'FROM jarvis_action_events ORDER BY id DESC LIMIT 100'); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        return $result->fetch_all(MYSQLI_ASSOC); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

/**
 * IA só pode REDIGIR. A entrega oficial exige uma ação POST separada do ADM,
 * revisão do texto/identidade, CSRF e confirmação adicional na interface.
 * Sem operações de suspensão, exclusão, banimento ou envio em massa.
 */
final class JarvisPlatformOutbox // Explicação DevIN: Declara o tipo JarvisPlatformOutbox e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    public const MAX_DAILY = 15; // Explicação DevIN: Define membro, visibilidade ou método de uma classe.
    public const MAX_PER_RECIPIENT = 2; // Explicação DevIN: Define membro, visibilidade ou método de uma classe.

    public static function validateMessage(string $message): string // Explicação DevIN: Declara função/método validateMessage, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $message=trim($message); // Explicação DevIN: Atribui um valor à variável PHP $message.
        if ($message==='' || mb_strlen($message,'UTF-8')>1200 // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            || preg_match('/https?:\/\/|www\./iu',$message) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            || preg_match('/(?:informe|envie|compartilhe|digite|revele)\s+(?:sua?|seus?)\s+(?:senha|token|c[oó]digo|cpf)/iu',$message)) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            throw new InvalidArgumentException( // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
                'Mensagem inválida. Não inclua links nem solicite senhas, tokens ou documentos.' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return $message; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function create(mysqli $db, int $admin, string $type, int $recipient, // Explicação DevIN: Declara função/método create, com seus parâmetros e comportamento.
        string $message, string $source): array // Explicação DevIN: Continua expressão ou bloco pertencente a função create.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!in_array($source,['ai','admin'],true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Origem inválida.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $target=JarvisAdminRecords::recipient($db,$type,$recipient); // Explicação DevIN: Atribui um valor à variável PHP $target.
        if (!$target) throw new InvalidArgumentException('Destinatário não encontrado.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $message=self::validateMessage($message); // Explicação DevIN: Atribui um valor à variável PHP $message.
        $db->begin_transaction(); // Explicação DevIN: Continua expressão ou bloco pertencente a função create.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            $stmt=$db->prepare('INSERT INTO jarvis_platform_outbox ' // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                . '(admin_id,recipient_type,recipient_id,body,source,status) ' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                . "VALUES (?,?,?,?,?,'draft')"); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            $stmt->bind_param('isiss',$admin,$type,$recipient,$message,$source); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $id=(int)$db->insert_id; // Explicação DevIN: Atribui um valor à variável PHP $id.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função create.
            JarvisAdminActivity::log($db,$admin,$source,$source==='ai'?'draft_generated':'draft_manual', // Explicação DevIN: Continua expressão ou bloco pertencente a função create.
                'success',$type,$recipient); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            $db->commit(); // Explicação DevIN: Continua expressão ou bloco pertencente a função create.
            return ['id'=>$id,'recipient'=>$target,'body'=>$message,'status'=>'draft','source'=>$source]; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } catch(Throwable $error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            $db->rollback(); // Explicação DevIN: Continua expressão ou bloco pertencente a função create.
            throw $error; // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function listOwn(mysqli $db, int $admin): array // Explicação DevIN: Declara função/método listOwn, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $stmt=$db->prepare('SELECT id,recipient_type,recipient_id,body,source,status,' // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            . 'created_at,sent_at,approved_by FROM jarvis_platform_outbox ' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            . 'WHERE admin_id=? ORDER BY id DESC LIMIT 40'); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        $stmt->bind_param('i',$admin); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $rows=$stmt->get_result()->fetch_all(MYSQLI_ASSOC); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função listOwn.
        return $rows; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function revise(mysqli $db,int $admin,int $id,string $message): void // Explicação DevIN: Declara função/método revise, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $message=self::validateMessage($message); // Explicação DevIN: Atribui um valor à variável PHP $message.
        $db->begin_transaction(); // Explicação DevIN: Continua expressão ou bloco pertencente a função revise.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            $stmt=$db->prepare('SELECT recipient_type,recipient_id,status FROM jarvis_platform_outbox ' // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                . 'WHERE id=? AND admin_id=? FOR UPDATE'); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            $stmt->bind_param('ii',$id,$admin); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $row=$stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função revise.
            if (!$row || $row['status']!=='draft') throw new DomainException('Rascunho indisponível.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $stmt=$db->prepare('UPDATE jarvis_platform_outbox SET body=? WHERE id=? AND admin_id=?'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->bind_param('sii',$message,$id,$admin); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função revise.
            JarvisAdminActivity::log($db,$admin,'admin','draft_revised','success', // Explicação DevIN: Continua expressão ou bloco pertencente a função revise.
                $row['recipient_type'],(int)$row['recipient_id']); // Explicação DevIN: Continua expressão ou bloco pertencente a função revise.
            $db->commit(); // Explicação DevIN: Continua expressão ou bloco pertencente a função revise.
        } catch(Throwable $e) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            $db->rollback(); // Explicação DevIN: Continua expressão ou bloco pertencente a função revise.
            throw $e; // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function cancel(mysqli $db,int $admin,int $id): void // Explicação DevIN: Declara função/método cancel, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $db->begin_transaction(); // Explicação DevIN: Continua expressão ou bloco pertencente a função cancel.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            $stmt=$db->prepare("SELECT recipient_type,recipient_id,status FROM jarvis_platform_outbox " // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                . "WHERE id=? AND admin_id=? FOR UPDATE"); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            $stmt->bind_param('ii',$id,$admin); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $row=$stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função cancel.
            if (!$row || !in_array($row['status'],['draft','failed'],true)) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new DomainException('Rascunho indisponível.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            $stmt=$db->prepare("UPDATE jarvis_platform_outbox SET status='cancelled' WHERE id=? AND admin_id=?"); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->bind_param('ii',$id,$admin); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função cancel.
            JarvisAdminActivity::log($db,$admin,'admin','draft_cancelled','success', // Explicação DevIN: Continua expressão ou bloco pertencente a função cancel.
                $row['recipient_type'],(int)$row['recipient_id']); // Explicação DevIN: Continua expressão ou bloco pertencente a função cancel.
            $db->commit(); // Explicação DevIN: Continua expressão ou bloco pertencente a função cancel.
        } catch(Throwable $e) {$db->rollback();throw $e;} // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /**
     * Identificador do outbox vira chave de evento única no ChatStorage.
     * Se o salvamento do status falhar após a entrega, repetir o POST com
     * aprovação explícita não duplica a notificação.
     */
    public static function send(mysqli $db,int $admin,int $id): void // Explicação DevIN: Declara função/método send, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if ($id<1 || $admin<1) throw new InvalidArgumentException('Solicitação inválida.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $name='jarvis_send_'.$admin; // Explicação DevIN: Atribui um valor à variável PHP $name.
        $stmt=$db->prepare('SELECT GET_LOCK(?,3) AS granted'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->bind_param('s',$name); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $locked=(int)$stmt->get_result()->fetch_assoc()['granted']===1; // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função send.
        if (!$locked) throw new RuntimeException('O envio está ocupado. Tente novamente.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            $db->begin_transaction(); // Explicação DevIN: Continua expressão ou bloco pertencente a função send.
            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                $stmt=$db->prepare('SELECT recipient_type,recipient_id,body,status FROM jarvis_platform_outbox ' // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                    . 'WHERE id=? AND admin_id=? FOR UPDATE'); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                $stmt->bind_param('ii',$id,$admin); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $row=$stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
                $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função send.
                if (!$row || !in_array($row['status'],['draft','failed'],true)) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    throw new DomainException('Rascunho indisponível ou já enviado.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
                $target=JarvisAdminRecords::recipient($db,$row['recipient_type'],(int)$row['recipient_id']); // Explicação DevIN: Atribui um valor à variável PHP $target.
                if (!$target) throw new DomainException('Destinatário indisponível.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                self::validateMessage($row['body']); // Explicação DevIN: Continua expressão ou bloco pertencente a função send.

                $stmt=$db->prepare('SELECT COUNT(*) FROM jarvis_platform_outbox WHERE admin_id=? ' // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                    . "AND status IN ('sent','sending') AND " // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    . 'COALESCE(sent_at,reviewed_at)>=UTC_TIMESTAMP()-INTERVAL 1 DAY'); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                $stmt->bind_param('i',$admin); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $daily=(int)$stmt->get_result()->fetch_row()[0]; // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
                $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função send.
                if ($daily>=self::MAX_DAILY) throw new DomainException('Limite diário de envios atingido.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                $stmt=$db->prepare('SELECT COUNT(*) FROM jarvis_platform_outbox ' // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                    . "WHERE recipient_type=? AND recipient_id=? AND status IN ('sent','sending') " // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    . 'AND COALESCE(sent_at,reviewed_at)>=UTC_TIMESTAMP()-INTERVAL 1 DAY'); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                $stmt->bind_param('si',$row['recipient_type'],$row['recipient_id']); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $perRecipient=(int)$stmt->get_result()->fetch_row()[0]; // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
                $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função send.
                if ($perRecipient>=self::MAX_PER_RECIPIENT) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    throw new DomainException('Limite de mensagens para este destinatário atingido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.

                $stmt=$db->prepare("UPDATE jarvis_platform_outbox SET status='sending', " // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                    . "reviewed_at=UTC_TIMESTAMP(6),approved_by=?,last_error_code=NULL " // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    . "WHERE id=? AND admin_id=?"); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                $stmt->bind_param('iii',$admin,$id,$admin); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função send.
                $db->commit(); // Explicação DevIN: Continua expressão ou bloco pertencente a função send.
            } catch(Throwable $error) {$db->rollback();throw $error;} // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.

            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                appendPlatformChatNotification( // Explicação DevIN: Continua expressão ou bloco pertencente a função send.
                    'jarvis-platform-message:'.$id, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    $row['recipient_type'],(int)$row['recipient_id'],$row['body'] // Explicação DevIN: Continua expressão ou bloco pertencente a função send.
                ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            } catch(Throwable $error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
                $stmt=$db->prepare("UPDATE jarvis_platform_outbox SET status='failed'," // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                    . "last_error_code='storage' WHERE id=? AND admin_id=? AND status='sending'"); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                $stmt->bind_param('ii',$id,$admin); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função send.
                JarvisAdminActivity::log($db,$admin,'system','message_failed','failure', // Explicação DevIN: Continua expressão ou bloco pertencente a função send.
                    $row['recipient_type'],(int)$row['recipient_id'],'delivery'); // Explicação DevIN: Continua expressão ou bloco pertencente a função send.
                throw new RuntimeException('Não foi possível entregar a mensagem.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $db->begin_transaction(); // Explicação DevIN: Continua expressão ou bloco pertencente a função send.
            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                $stmt=$db->prepare("UPDATE jarvis_platform_outbox SET status='sent'," // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                    . "sent_at=UTC_TIMESTAMP(6),last_error_code=NULL " // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    . "WHERE id=? AND admin_id=? AND status='sending'"); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                $stmt->bind_param('ii',$id,$admin); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função send.
                JarvisAdminActivity::log($db,$admin,'admin','message_sent','success', // Explicação DevIN: Continua expressão ou bloco pertencente a função send.
                    $row['recipient_type'],(int)$row['recipient_id']); // Explicação DevIN: Continua expressão ou bloco pertencente a função send.
                $db->commit(); // Explicação DevIN: Continua expressão ou bloco pertencente a função send.
            } catch(Throwable $error) {$db->rollback();throw $error;} // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
            $stmt=$db->prepare('SELECT RELEASE_LOCK(?)'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->bind_param('s',$name); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função send.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
