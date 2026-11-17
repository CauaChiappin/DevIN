<?php
declare(strict_types=1);

/**
 * Central de Suporte DevIN — persistência, validação e entrega.
 *
 * Segurança: o servidor determina identidade, nome e e-mail pela sessão e
 * pelo MySQL; nenhuma entrada do cliente concede privilégios de ADM.
 * O arquivo opcional nunca recebe nome executável nem URL pública.
 * Uma resposta é gravada antes de notificar; falhas de SMTP são registradas
 * separadamente para permitir uma nova tentativa sem duplicar a resposta.
 */
final class SupportDesk
{
    // Limite solicitado: 20 MiB reais, validado no servidor além do HTML.
    public const MAX_ATTACHMENT_BYTES = 20 * 1024 * 1024;

    // Lista fechada: evita que a categoria se torne HTML arbitrário ou SQL.
    public const CATEGORIES = [
        'erro' => 'Erro no site', 'duvida' => 'Dúvida',
        'conta' => 'Conta e acesso', 'vaga' => 'Vagas e candidaturas',
        'seguranca' => 'Segurança e privacidade',
        'sugestao' => 'Sugestão', 'outro' => 'Outros assuntos',
    ];

    // Extensões e tipos reais identificados pelo Fileinfo; nunca aceitar PHP/HTML/SVG.
    private const FILE_TYPES = [
        'pdf' => ['application/pdf'], 'png' => ['image/png'],
        'jpg' => ['image/jpeg'], 'jpeg' => ['image/jpeg'],
        'webp' => ['image/webp'], 'txt' => ['text/plain'],
        'log' => ['text/plain'], 'docx' => ['application/zip',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'zip' => ['application/zip', 'application/x-zip-compressed'],
    ];

    // Conexão deve ser disponibilizada pelo controlador autenticado.
    public static function requireSchema(mysqli $db): void
    {
        try {
            $db->query('SELECT id FROM support_tickets LIMIT 0');
            $db->query('SELECT id FROM support_replies LIMIT 0');
        } catch (mysqli_sql_exception $error) {
            throw new RuntimeException(
                'O suporte ainda não foi configurado no banco. Aplique a migração de suporte.', 0, $error
            );
        }
    }

    // Obtém a identidade a partir da conta real; jamais confia no nome/e-mail do POST.
    private static function owner(mysqli $db, array $actor): array
    {
        $kind = (string) ($actor['tipo'] ?? '');
        $id = (int) ($actor['id'] ?? 0);
        if (!in_array($kind, ['pessoa','empresa'], true) || $id < 1) {
            throw new DomainException('Somente pessoas e empresas podem abrir chamados.');
        }
        $table = $kind === 'pessoa' ? 'pessoa' : 'empresa';
        $pk = $kind === 'pessoa' ? 'id_pessoa' : 'id_empresa';
        $stmt = $db->prepare("SELECT nome,email FROM {$table} WHERE {$pk}=? LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $owner = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$owner || !filter_var($owner['email'], FILTER_VALIDATE_EMAIL)) {
            throw new DomainException('Conta sem endereço de e-mail válido.');
        }
        return ['type'=>$kind, 'id'=>$id, 'name'=>(string)$owner['nome'], 'email'=>(string)$owner['email']];
    }

    // Diretório protegido por .htaccess: em produção, preferir caminho externo
    // ao public_html através de DEVIN_SUPPORT_STORAGE_DIR no .env privado.
    public static function storageDirectory(): string
    {
        $configured = $_ENV['DEVIN_SUPPORT_STORAGE_DIR']
            ?? $_SERVER['DEVIN_SUPPORT_STORAGE_DIR']
            ?? getenv('DEVIN_SUPPORT_STORAGE_DIR') ?: '';
        $folder = $configured !== ''
            ? (string) $configured
            : dirname(__DIR__, 2) . '/storage/support-attachments';
        if (!is_dir($folder) && !mkdir($folder, 0700, true) && !is_dir($folder)) {
            throw new RuntimeException('Não foi possível preparar a pasta protegida de anexos.');
        }
        $real = realpath($folder);
        if ($real === false || !is_writable($real)) {
            throw new RuntimeException('A pasta de anexos não pode ser utilizada.');
        }
        // Em Apache, nega qualquer acesso HTTP mesmo se a pasta for movida.
        $deny = $real . '/.htaccess';
        if (!file_exists($deny)) {
            file_put_contents($deny, "Require all denied\nOptions -Indexes\n", LOCK_EX);
        }
        return $real;
    }

    /**
     * Armazena somente tipos permitidos, com token aleatório sem extensão.
     * @return array{token:string,name:string,mime:string,bytes:int}|null
     */
    public static function upload(?array $file): ?array
    {
        if (!$file || (int)($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
        if ((int)$file['error'] !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('Falha no envio do anexo. Verifique o tamanho máximo do servidor.');
        }
        $size = (int) ($file['size'] ?? 0);
        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($size < 1 || $size > self::MAX_ATTACHMENT_BYTES || !is_uploaded_file($tmp)) {
            throw new InvalidArgumentException('O anexo deve ter até 20 MB e ser enviado pelo formulário.');
        }
        $original = basename(str_replace('\\', '/', (string) ($file['name'] ?? '')));
        $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        if (!isset(self::FILE_TYPES[$ext])) {
            throw new InvalidArgumentException('Use PDF, imagem PNG/JPG/WEBP, TXT, LOG, DOCX ou ZIP.');
        }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
        if (!is_string($mime) || !in_array($mime, self::FILE_TYPES[$ext], true)) {
            throw new InvalidArgumentException('O formato real do arquivo difere da extensão.');
        }
        $token = bin2hex(random_bytes(24));
        $dest = self::storageDirectory() . '/' . $token;
        if (!move_uploaded_file($tmp, $dest)) {
            throw new RuntimeException('Falha ao salvar o anexo em armazenamento protegido.');
        }
        chmod($dest, 0600);
        return [
            'token'=>$token,
            'name'=>mb_substr($original, 0, 180, 'UTF-8'),
            'mime'=>$mime, 'bytes'=>$size,
        ];
    }

    // Regras de formulário e antispam são aplicadas antes de criar um registro.
    public static function create(mysqli $db, array $actor, string $category, string $description, ?array $file): int
    {
        self::requireSchema($db);
        $owner = self::owner($db, $actor);
        $description = trim($description);
        if (!array_key_exists($category, self::CATEGORIES)) {
            throw new InvalidArgumentException('Selecione uma categoria de suporte.');
        }
        if (mb_strlen($description, 'UTF-8') < 10 || mb_strlen($description, 'UTF-8') > 4000) {
            throw new InvalidArgumentException('Descreva sua solicitação entre 10 e 4.000 caracteres.');
        }
        // Limite por conta: até oito chamados em 24 horas e um a cada minuto.
        $stmt = $db->prepare('SELECT COUNT(*) AS total, MAX(created_at) AS last_at
            FROM support_tickets WHERE requester_type=? AND requester_id=?
            AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)');
        $stmt->bind_param('si', $owner['type'], $owner['id']);
        $stmt->execute();
        $rate = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ((int)($rate['total'] ?? 0) >= 8
            || (!empty($rate['last_at']) && strtotime($rate['last_at']) > time()-60)) {
            throw new DomainException('Limite de solicitações atingido. Tente novamente mais tarde.');
        }
        $attachment = self::upload($file);
        try {
            $stmt = $db->prepare('INSERT INTO support_tickets (
                requester_type,requester_id,requester_name,requester_email,category,description,
                attachment_token,attachment_name,attachment_mime,attachment_bytes
            ) VALUES (?,?,?,?,?,?,?,?,?,?)');
            $token = $attachment['token'] ?? null;
            $filename = $attachment['name'] ?? null;
            $mime = $attachment['mime'] ?? null;
            $bytes = $attachment['bytes'] ?? null;
            $stmt->bind_param('sisssssssi',
                $owner['type'], $owner['id'], $owner['name'], $owner['email'],
                $category, $description, $token, $filename, $mime, $bytes
            );
            $stmt->execute();
            $id = (int) $db->insert_id;
            $stmt->close();
            return $id;
        } catch (Throwable $exception) {
            // Não deixar arquivo órfão quando a escrita MySQL falhar.
            if ($attachment) @unlink(self::storageDirectory() . '/' . $attachment['token']);
            throw $exception;
        }
    }

    // Lista de chamados visível aos ADMs, limitada para não sobrecarregar a página.
    public static function listAdmin(mysqli $db): array
    {
        self::requireSchema($db);
        return $db->query('SELECT id,requester_name,requester_email,requester_type,
            category,description,status,attachment_name,created_at,updated_at
            FROM support_tickets ORDER BY FIELD(status,"aberto","respondido","fechado"),
            updated_at DESC LIMIT 150')->fetch_all(MYSQLI_ASSOC);
    }

    // Só retorna chamados da conta autenticada, mesmo que outros IDs sejam conhecidos.
    public static function listOwn(mysqli $db, array $actor): array
    {
        self::requireSchema($db);
        $owner = self::owner($db, $actor);
        $stmt = $db->prepare('SELECT id,category,description,status,attachment_name,
            created_at,updated_at FROM support_tickets
            WHERE requester_type=? AND requester_id=? ORDER BY id DESC LIMIT 60');
        $stmt->bind_param('si', $owner['type'], $owner['id']);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    // A checagem de proprietário acontece antes de revelar descrição ou anexo.
    public static function detail(mysqli $db, int $ticketId, array $actor): array
    {
        self::requireSchema($db);
        if ($ticketId < 1) throw new InvalidArgumentException('Chamado inválido.');
        $stmt = $db->prepare('SELECT * FROM support_tickets WHERE id=? LIMIT 1');
        $stmt->bind_param('i', $ticketId);
        $stmt->execute();
        $ticket = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$ticket) throw new DomainException('Chamado não encontrado.');
        if (($actor['tipo'] ?? '') !== 'adm'
            && (!(in_array($actor['tipo'] ?? '', ['pessoa','empresa'], true))
                || $actor['tipo'] !== $ticket['requester_type']
                || (int)$actor['id'] !== (int)$ticket['requester_id'])) {
            throw new DomainException('Você não tem permissão para consultar esse chamado.');
        }
        $stmt = $db->prepare('SELECT id,admin_id,message,created_at,chat_sent_at,email_sent_at
            FROM support_replies WHERE ticket_id=? ORDER BY id ASC');
        $stmt->bind_param('i', $ticketId);
        $stmt->execute();
        $replies = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        // Token interno e caminhos do filesystem jamais são enviados ao navegador.
        unset($ticket['attachment_token']);
        return ['ticket'=>$ticket, 'replies'=>$replies];
    }

    // A resposta é persistida em transação antes da entrega via e-mail e chat.
    public static function reply(mysqli $db, int $adminId, int $ticketId, string $message): array
    {
        self::requireSchema($db);
        if ($adminId < 1) throw new DomainException('Acesso administrativo obrigatório.');
        $message = trim($message);
        if ($ticketId < 1 || mb_strlen($message, 'UTF-8') < 2
            || mb_strlen($message, 'UTF-8') > 3000) {
            throw new InvalidArgumentException('Escreva uma resposta entre 2 e 3.000 caracteres.');
        }
        $db->begin_transaction();
        try {
            $stmt = $db->prepare('SELECT id,status FROM support_tickets WHERE id=? FOR UPDATE');
            $stmt->bind_param('i', $ticketId);
            $stmt->execute();
            $ticket = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$ticket || $ticket['status'] === 'fechado') {
                throw new DomainException('O chamado não existe ou já está fechado.');
            }
            $stmt = $db->prepare('INSERT INTO support_replies(ticket_id,admin_id,message)
                VALUES (?,?,?)');
            $stmt->bind_param('iis', $ticketId, $adminId, $message);
            $stmt->execute();
            $replyId = (int)$db->insert_id;
            $stmt->close();
            $stmt = $db->prepare('UPDATE support_tickets SET status="respondido",
                updated_at=NOW() WHERE id=?');
            $stmt->bind_param('i', $ticketId);
            $stmt->execute();
            $stmt->close();
            $db->commit();
        } catch (Throwable $error) {
            $db->rollback();
            throw $error;
        }
        return ['reply_id'=>$replyId] + self::notify($db, $replyId);
    }

    // Entrega idempotente: o par ticket/reply dá uma chave única na caixa de mensagens.
    // O e-mail é uma tentativa isolada; falha SMTP não cancela a resposta já registrada.
    public static function notify(mysqli $db, int $replyId): array
    {
        $stmt = $db->prepare('SELECT r.*,t.requester_type,t.requester_id,
            t.requester_name,t.requester_email FROM support_replies r
            JOIN support_tickets t ON t.id=r.ticket_id WHERE r.id=? LIMIT 1');
        $stmt->bind_param('i', $replyId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$row) throw new DomainException('Resposta não localizada.');
        $chat = !empty($row['chat_sent_at']);
        $email = !empty($row['email_sent_at']);
        if (!$chat) {
            try {
                // O contato "sistema" aparece com o nome Suporte DevIN no chat.
                appendPlatformChatNotification(
                    'support-reply:' . $replyId,
                    (string)$row['requester_type'],
                    (int)$row['requester_id'],
                    "Suporte DevIN — resposta ao chamado #{$row['ticket_id']}:\n" . $row['message']
                );
                $db->query('UPDATE support_replies SET chat_sent_at=NOW()
                    WHERE id=' . (int)$replyId . ' AND chat_sent_at IS NULL');
                $chat = true;
            } catch (Throwable $error) {
                error_log('Suporte: entrega interna falhou para resposta ' . $replyId);
            }
        }
        if (!$email && (int)$row['email_attempts'] < 5) {
            // Não incluir anexos no e-mail; enviar apenas resposta e ID do chamado.
            $safeName = htmlspecialchars((string)$row['requester_name'], ENT_QUOTES, 'UTF-8');
            $safeMessage = nl2br(htmlspecialchars((string)$row['message'], ENT_QUOTES, 'UTF-8'));
            $body = '<p>Olá, ' . $safeName . '.</p><p>O <strong>Suporte DevIN</strong> respondeu ao seu chamado <strong>#'
                . (int)$row['ticket_id'] . '</strong>:</p><blockquote>' . $safeMessage
                . '</blockquote><p>A resposta também está disponível nas Mensagens e na Central de Suporte.</p>';
            $stmt = $db->prepare('UPDATE support_replies SET email_attempts=email_attempts+1 WHERE id=?');
            $stmt->bind_param('i', $replyId);
            $stmt->execute();
            $stmt->close();
            if (MailerHelper::enviar(
                (string)$row['requester_email'], (string)$row['requester_name'],
                'Suporte DevIN — resposta ao chamado #' . $row['ticket_id'], $body
            )) {
                $stmt = $db->prepare('UPDATE support_replies SET email_sent_at=NOW() WHERE id=?');
                $stmt->bind_param('i', $replyId);
                $stmt->execute();
                $stmt->close();
                $email = true;
            } else {
                error_log('Suporte: e-mail pendente para resposta ' . $replyId);
            }
        }
        return ['chat_sent'=>$chat, 'email_sent'=>$email];
    }

    // Somente ADM pode encerrar; encerramento não apaga histórico nem anexos.
    public static function close(mysqli $db, int $ticketId): void
    {
        if ($ticketId < 1) throw new InvalidArgumentException('Chamado inválido.');
        $stmt = $db->prepare('UPDATE support_tickets SET status="fechado" WHERE id=?');
        $stmt->bind_param('i', $ticketId);
        $stmt->execute();
        $changed = $stmt->affected_rows;
        $stmt->close();
        if ($changed < 1) throw new DomainException('Chamado já fechado ou inexistente.');
    }

    // Consulta de anexo exige o mesmo controle de acesso que a descrição.
    public static function attachment(mysqli $db, int $ticketId, array $actor): array
    {
        self::detail($db, $ticketId, $actor);
        $stmt = $db->prepare('SELECT attachment_token,attachment_name,attachment_mime,
            attachment_bytes FROM support_tickets WHERE id=? LIMIT 1');
        $stmt->bind_param('i', $ticketId);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$data || !$data['attachment_token']
            || !preg_match('/^[a-f0-9]{48}$/', $data['attachment_token'])) {
            throw new DomainException('Este chamado não possui anexo.');
        }
        $folder = self::storageDirectory();
        $file = $folder . '/' . $data['attachment_token'];
        if (!is_file($file) || is_link($file)) {
            throw new DomainException('Arquivo não encontrado no armazenamento protegido.');
        }
        return ['path'=>$file, 'name'=>$data['attachment_name'],
            'mime'=>$data['attachment_mime'], 'bytes'=>(int)$data['attachment_bytes']];
    }
}
