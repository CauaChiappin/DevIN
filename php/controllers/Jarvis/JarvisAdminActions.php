<?php
declare(strict_types=1);

require_once __DIR__ . '/JarvisAdminRecords.php';
require_once __DIR__ . '/../ChatStorage.php';

/**
 * Trilhas de auditoria append-only no aplicativo. Nunca registrar texto da
 * mensagem, prompts, email, CPF, senha, token ou conteúdo de denúncia.
 */
final class JarvisAdminActivity
{
    private const EVENTS = [
        'search','inspect','analysis','draft_generated','draft_manual',
        'draft_revised','message_sent','message_failed','draft_cancelled','action_denied'
    ];
    public static function log(mysqli $db, int $admin, string $origin, string $event,
        string $status, string $targetType='none', ?int $targetId=null, string $detail=''): void
    {
        if ($admin < 1 || !in_array($origin,['ai','admin','system'],true)
            || !in_array($event,self::EVENTS,true)
            || !in_array($status,['success','failure','denied'],true)
            || preg_match('/^[a-z0-9_-]{0,60}$/D',$detail)!==1
            || preg_match('/^[a-z0-9_-]{1,24}$/D',$targetType)!==1) {
            throw new InvalidArgumentException('Evento de auditoria inválido.');
        }
        $stmt=$db->prepare('INSERT INTO jarvis_action_events '
            . '(admin_id,origin,event_name,status,target_type,target_id,detail_code) '
            . 'VALUES (?,?,?,?,?,?,?)');
        $stmt->bind_param('issssis',$admin,$origin,$event,$status,$targetType,$targetId,$detail);
        $stmt->execute();
        $stmt->close();
    }

    public static function latest(mysqli $db, int $admin): array
    {
        $stmt=$db->prepare(
            'SELECT id,origin,event_name,status,target_type,target_id,detail_code,created_at '
            . 'FROM jarvis_action_events WHERE admin_id=? ORDER BY id DESC LIMIT 80'
        );
        $stmt->bind_param('i',$admin);
        $stmt->execute();
        $rows=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }
}

/**
 * IA só pode REDIGIR. A entrega oficial exige uma ação POST separada do ADM,
 * revisão do texto/identidade, CSRF e confirmação adicional na interface.
 * Sem operações de suspensão, exclusão, banimento ou envio em massa.
 */
final class JarvisPlatformOutbox
{
    public const MAX_DAILY = 15;
    public const MAX_PER_RECIPIENT = 2;

    public static function validateMessage(string $message): string
    {
        $message=trim($message);
        if ($message==='' || mb_strlen($message,'UTF-8')>1200
            || preg_match('/https?:\/\/|www\./iu',$message)
            || preg_match('/(?:informe|envie|compartilhe|digite|revele)\s+(?:sua?|seus?)\s+(?:senha|token|c[oó]digo|cpf)/iu',$message)) {
            throw new InvalidArgumentException(
                'Mensagem inválida. Não inclua links nem solicite senhas, tokens ou documentos.'
            );
        }
        return $message;
    }

    public static function create(mysqli $db, int $admin, string $type, int $recipient,
        string $message, string $source): array
    {
        if (!in_array($source,['ai','admin'],true)) {
            throw new InvalidArgumentException('Origem inválida.');
        }
        $target=JarvisAdminRecords::recipient($db,$type,$recipient);
        if (!$target) throw new InvalidArgumentException('Destinatário não encontrado.');
        $message=self::validateMessage($message);
        $db->begin_transaction();
        try {
            $stmt=$db->prepare('INSERT INTO jarvis_platform_outbox '
                . '(admin_id,recipient_type,recipient_id,body,source,status) '
                . "VALUES (?,?,?,?,?,'draft')");
            $stmt->bind_param('isiss',$admin,$type,$recipient,$message,$source);
            $stmt->execute();
            $id=(int)$db->insert_id;
            $stmt->close();
            JarvisAdminActivity::log($db,$admin,$source,$source==='ai'?'draft_generated':'draft_manual',
                'success',$type,$recipient);
            $db->commit();
            return ['id'=>$id,'recipient'=>$target,'body'=>$message,'status'=>'draft','source'=>$source];
        } catch(Throwable $error) {
            $db->rollback();
            throw $error;
        }
    }

    public static function listOwn(mysqli $db, int $admin): array
    {
        $stmt=$db->prepare('SELECT id,recipient_type,recipient_id,body,source,status,'
            . 'created_at,sent_at,approved_by FROM jarvis_platform_outbox '
            . 'WHERE admin_id=? ORDER BY id DESC LIMIT 40');
        $stmt->bind_param('i',$admin);
        $stmt->execute();
        $rows=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public static function revise(mysqli $db,int $admin,int $id,string $message): void
    {
        $message=self::validateMessage($message);
        $db->begin_transaction();
        try {
            $stmt=$db->prepare('SELECT recipient_type,recipient_id,status FROM jarvis_platform_outbox '
                . 'WHERE id=? AND admin_id=? FOR UPDATE');
            $stmt->bind_param('ii',$id,$admin);
            $stmt->execute();
            $row=$stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$row || $row['status']!=='draft') throw new DomainException('Rascunho indisponível.');
            $stmt=$db->prepare('UPDATE jarvis_platform_outbox SET body=? WHERE id=? AND admin_id=?');
            $stmt->bind_param('sii',$message,$id,$admin);
            $stmt->execute();
            $stmt->close();
            JarvisAdminActivity::log($db,$admin,'admin','draft_revised','success',
                $row['recipient_type'],(int)$row['recipient_id']);
            $db->commit();
        } catch(Throwable $e) {
            $db->rollback();
            throw $e;
        }
    }

    public static function cancel(mysqli $db,int $admin,int $id): void
    {
        $db->begin_transaction();
        try {
            $stmt=$db->prepare("SELECT recipient_type,recipient_id,status FROM jarvis_platform_outbox "
                . "WHERE id=? AND admin_id=? FOR UPDATE");
            $stmt->bind_param('ii',$id,$admin);
            $stmt->execute();
            $row=$stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$row || !in_array($row['status'],['draft','failed'],true))
                throw new DomainException('Rascunho indisponível.');
            $stmt=$db->prepare("UPDATE jarvis_platform_outbox SET status='cancelled' WHERE id=? AND admin_id=?");
            $stmt->bind_param('ii',$id,$admin);
            $stmt->execute();
            $stmt->close();
            JarvisAdminActivity::log($db,$admin,'admin','draft_cancelled','success',
                $row['recipient_type'],(int)$row['recipient_id']);
            $db->commit();
        } catch(Throwable $e) {$db->rollback();throw $e;}
    }

    /**
     * Identificador do outbox vira chave de evento única no ChatStorage.
     * Se o salvamento do status falhar após a entrega, repetir o POST com
     * aprovação explícita não duplica a notificação.
     */
    public static function send(mysqli $db,int $admin,int $id): void
    {
        if ($id<1 || $admin<1) throw new InvalidArgumentException('Solicitação inválida.');
        $name='jarvis_send_'.$admin;
        $stmt=$db->prepare('SELECT GET_LOCK(?,3) AS granted');
        $stmt->bind_param('s',$name);
        $stmt->execute();
        $locked=(int)$stmt->get_result()->fetch_assoc()['granted']===1;
        $stmt->close();
        if (!$locked) throw new RuntimeException('O envio está ocupado. Tente novamente.');
        try {
            $db->begin_transaction();
            try {
                $stmt=$db->prepare('SELECT recipient_type,recipient_id,body,status FROM jarvis_platform_outbox '
                    . 'WHERE id=? AND admin_id=? FOR UPDATE');
                $stmt->bind_param('ii',$id,$admin);
                $stmt->execute();
                $row=$stmt->get_result()->fetch_assoc();
                $stmt->close();
                if (!$row || !in_array($row['status'],['draft','failed'],true))
                    throw new DomainException('Rascunho indisponível ou já enviado.');
                $target=JarvisAdminRecords::recipient($db,$row['recipient_type'],(int)$row['recipient_id']);
                if (!$target) throw new DomainException('Destinatário indisponível.');
                self::validateMessage($row['body']);

                $stmt=$db->prepare('SELECT COUNT(*) FROM jarvis_platform_outbox WHERE admin_id=? '
                    . "AND status IN ('sent','sending') AND "
                    . 'COALESCE(sent_at,reviewed_at)>=UTC_TIMESTAMP()-INTERVAL 1 DAY');
                $stmt->bind_param('i',$admin);
                $stmt->execute();
                $daily=(int)$stmt->get_result()->fetch_row()[0];
                $stmt->close();
                if ($daily>=self::MAX_DAILY) throw new DomainException('Limite diário de envios atingido.');
                $stmt=$db->prepare('SELECT COUNT(*) FROM jarvis_platform_outbox '
                    . "WHERE recipient_type=? AND recipient_id=? AND status IN ('sent','sending') "
                    . 'AND COALESCE(sent_at,reviewed_at)>=UTC_TIMESTAMP()-INTERVAL 1 DAY');
                $stmt->bind_param('si',$row['recipient_type'],$row['recipient_id']);
                $stmt->execute();
                $perRecipient=(int)$stmt->get_result()->fetch_row()[0];
                $stmt->close();
                if ($perRecipient>=self::MAX_PER_RECIPIENT)
                    throw new DomainException('Limite de mensagens para este destinatário atingido.');

                $stmt=$db->prepare("UPDATE jarvis_platform_outbox SET status='sending', "
                    . "reviewed_at=UTC_TIMESTAMP(6),approved_by=?,last_error_code=NULL "
                    . "WHERE id=? AND admin_id=?");
                $stmt->bind_param('iii',$admin,$id,$admin);
                $stmt->execute();
                $stmt->close();
                $db->commit();
            } catch(Throwable $error) {$db->rollback();throw $error;}

            try {
                appendPlatformChatNotification(
                    'jarvis-platform-message:'.$id,
                    $row['recipient_type'],(int)$row['recipient_id'],$row['body']
                );
            } catch(Throwable $error) {
                $stmt=$db->prepare("UPDATE jarvis_platform_outbox SET status='failed',"
                    . "last_error_code='storage' WHERE id=? AND admin_id=? AND status='sending'");
                $stmt->bind_param('ii',$id,$admin);
                $stmt->execute();
                $stmt->close();
                JarvisAdminActivity::log($db,$admin,'system','message_failed','failure',
                    $row['recipient_type'],(int)$row['recipient_id'],'delivery');
                throw new RuntimeException('Não foi possível entregar a mensagem.');
            }
            $db->begin_transaction();
            try {
                $stmt=$db->prepare("UPDATE jarvis_platform_outbox SET status='sent',"
                    . "sent_at=UTC_TIMESTAMP(6),last_error_code=NULL "
                    . "WHERE id=? AND admin_id=? AND status='sending'");
                $stmt->bind_param('ii',$id,$admin);
                $stmt->execute();
                $stmt->close();
                JarvisAdminActivity::log($db,$admin,'admin','message_sent','success',
                    $row['recipient_type'],(int)$row['recipient_id']);
                $db->commit();
            } catch(Throwable $error) {$db->rollback();throw $error;}
        } finally {
            $stmt=$db->prepare('SELECT RELEASE_LOCK(?)');
            $stmt->bind_param('s',$name);
            $stmt->execute();
            $stmt->close();
        }
    }
}
