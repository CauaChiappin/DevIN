<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

/**
 * Central de Suporte DevIN — persistência, validação e entrega.
 *
 * Segurança: o servidor determina identidade, nome e e-mail pela sessão e
 * pelo MySQL; nenhuma entrada do cliente concede privilégios de ADM.
 * O arquivo opcional nunca recebe nome executável nem URL pública.
 * Uma resposta é gravada antes de notificar; falhas de SMTP são registradas
 * separadamente para permitir uma nova tentativa sem duplicar a resposta.
 */
final class SupportDesk // Explicação DevIN: Declara o tipo SupportDesk e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    // Limite solicitado: 20 MiB reais, validado no servidor além do HTML.
    public const MAX_ATTACHMENT_BYTES = 20 * 1024 * 1024; // Explicação DevIN: Define membro, visibilidade ou método de uma classe.

    // Lista fechada: evita que a categoria se torne HTML arbitrário ou SQL.
    public const CATEGORIES = [ // Explicação DevIN: Define membro, visibilidade ou método de uma classe.
        'erro' => 'Erro no site', 'duvida' => 'Dúvida', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'conta' => 'Conta e acesso', 'vaga' => 'Vagas e candidaturas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'seguranca' => 'Segurança e privacidade', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'sugestao' => 'Sugestão', 'outro' => 'Outros assuntos', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.

    // Extensões e tipos reais identificados pelo Fileinfo; nunca aceitar PHP/HTML/SVG.
    private const FILE_TYPES = [ // Explicação DevIN: Define membro, visibilidade ou método de uma classe.
        'pdf' => ['application/pdf'], 'png' => ['image/png'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'jpg' => ['image/jpeg'], 'jpeg' => ['image/jpeg'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'webp' => ['image/webp'], 'txt' => ['text/plain'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'log' => ['text/plain'], 'docx' => ['application/zip', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'zip' => ['application/zip', 'application/x-zip-compressed'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.

    // Conexão deve ser disponibilizada pelo controlador autenticado.
    public static function requireSchema(mysqli $db): void // Explicação DevIN: Declara função/método requireSchema, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            $db->query('SELECT id FROM support_tickets LIMIT 0'); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            $db->query('SELECT id FROM support_replies LIMIT 0'); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        } catch (mysqli_sql_exception $error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            throw new RuntimeException( // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
                'O suporte ainda não foi configurado no banco. Aplique a migração de suporte.', 0, $error // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Obtém a identidade a partir da conta real; jamais confia no nome/e-mail do POST.
    private static function owner(mysqli $db, array $actor): array // Explicação DevIN: Declara função/método owner, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $kind = (string) ($actor['tipo'] ?? ''); // Explicação DevIN: Atribui um valor à variável PHP $kind.
        $id = (int) ($actor['id'] ?? 0); // Explicação DevIN: Atribui um valor à variável PHP $id.
        if (!in_array($kind, ['pessoa','empresa'], true) || $id < 1) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new DomainException('Somente pessoas e empresas podem abrir chamados.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $table = $kind === 'pessoa' ? 'pessoa' : 'empresa'; // Explicação DevIN: Atribui um valor à variável PHP $table.
        $pk = $kind === 'pessoa' ? 'id_pessoa' : 'id_empresa'; // Explicação DevIN: Atribui um valor à variável PHP $pk.
        $stmt = $db->prepare("SELECT nome,email FROM {$table} WHERE {$pk}=? LIMIT 1"); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->bind_param('i', $id); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $owner = $stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função owner.
        if (!$owner || !filter_var($owner['email'], FILTER_VALIDATE_EMAIL)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new DomainException('Conta sem endereço de e-mail válido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return ['type'=>$kind, 'id'=>$id, 'name'=>(string)$owner['nome'], 'email'=>(string)$owner['email']]; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Diretório protegido por .htaccess: em produção, preferir caminho externo
    // ao public_html através de DEVIN_SUPPORT_STORAGE_DIR no .env privado.
    public static function storageDirectory(): string // Explicação DevIN: Declara função/método storageDirectory, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $configured = $_ENV['DEVIN_SUPPORT_STORAGE_DIR'] // Explicação DevIN: Atribui um valor à variável PHP $configured.
            ?? $_SERVER['DEVIN_SUPPORT_STORAGE_DIR'] // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            ?? getenv('DEVIN_SUPPORT_STORAGE_DIR') ?: ''; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        $folder = $configured !== '' // Explicação DevIN: Atribui um valor à variável PHP $folder.
            ? (string) $configured // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            : dirname(__DIR__, 2) . '/storage/support-attachments'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        if (!is_dir($folder) && !mkdir($folder, 0700, true) && !is_dir($folder)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new RuntimeException('Não foi possível preparar a pasta protegida de anexos.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $real = realpath($folder); // Explicação DevIN: Atribui um valor à variável PHP $real.
        if ($real === false || !is_writable($real)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new RuntimeException('A pasta de anexos não pode ser utilizada.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // Em Apache, nega qualquer acesso HTTP mesmo se a pasta for movida.
        $deny = $real . '/.htaccess'; // Explicação DevIN: Atribui um valor à variável PHP $deny.
        if (!file_exists($deny)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            file_put_contents($deny, "Require all denied\nOptions -Indexes\n", LOCK_EX); // Explicação DevIN: Continua expressão ou bloco pertencente a função storageDirectory.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return $real; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /**
     * Armazena somente tipos permitidos, com token aleatório sem extensão.
     * @return array{token:string,name:string,mime:string,bytes:int}|null
     */
    public static function upload(?array $file): ?array // Explicação DevIN: Declara função/método upload, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!$file || (int)($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if ((int)$file['error'] !== UPLOAD_ERR_OK) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Falha no envio do anexo. Verifique o tamanho máximo do servidor.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $size = (int) ($file['size'] ?? 0); // Explicação DevIN: Atribui um valor à variável PHP $size.
        $tmp = (string) ($file['tmp_name'] ?? ''); // Explicação DevIN: Atribui um valor à variável PHP $tmp.
        if ($size < 1 || $size > self::MAX_ATTACHMENT_BYTES || !is_uploaded_file($tmp)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('O anexo deve ter até 20 MB e ser enviado pelo formulário.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $original = basename(str_replace('\\', '/', (string) ($file['name'] ?? ''))); // Explicação DevIN: Atribui um valor à variável PHP $original.
        $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION)); // Explicação DevIN: Atribui um valor à variável PHP $ext.
        if (!isset(self::FILE_TYPES[$ext])) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Use PDF, imagem PNG/JPG/WEBP, TXT, LOG, DOCX ou ZIP.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp); // Explicação DevIN: Atribui um valor à variável PHP $mime.
        if (!is_string($mime) || !in_array($mime, self::FILE_TYPES[$ext], true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('O formato real do arquivo difere da extensão.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $token = bin2hex(random_bytes(24)); // Explicação DevIN: Atribui um valor à variável PHP $token.
        $dest = self::storageDirectory() . '/' . $token; // Explicação DevIN: Atribui um valor à variável PHP $dest.
        if (!move_uploaded_file($tmp, $dest)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new RuntimeException('Falha ao salvar o anexo em armazenamento protegido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        chmod($dest, 0600); // Explicação DevIN: Continua expressão ou bloco pertencente a função upload.
        return [ // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            'token'=>$token, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'name'=>mb_substr($original, 0, 180, 'UTF-8'), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'mime'=>$mime, 'bytes'=>$size, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Regras de formulário e antispam são aplicadas antes de criar um registro.
    public static function create(mysqli $db, array $actor, string $category, string $description, ?array $file): int // Explicação DevIN: Declara função/método create, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        self::requireSchema($db); // Explicação DevIN: Continua expressão ou bloco pertencente a função create.
        $owner = self::owner($db, $actor); // Explicação DevIN: Atribui um valor à variável PHP $owner.
        $description = trim($description); // Explicação DevIN: Atribui um valor à variável PHP $description.
        if (!array_key_exists($category, self::CATEGORIES)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Selecione uma categoria de suporte.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (mb_strlen($description, 'UTF-8') < 10 || mb_strlen($description, 'UTF-8') > 4000) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Descreva sua solicitação entre 10 e 4.000 caracteres.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // Limite por conta: até oito chamados em 24 horas e um a cada minuto.
        $stmt = $db->prepare('SELECT COUNT(*) AS total, MAX(created_at) AS last_at
            FROM support_tickets WHERE requester_type=? AND requester_id=?
            AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)'); // Explicação DevIN: Continua expressão ou bloco pertencente a função create.
        $stmt->bind_param('si', $owner['type'], $owner['id']); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $rate = $stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função create.
        if ((int)($rate['total'] ?? 0) >= 8 // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            || (!empty($rate['last_at']) && strtotime($rate['last_at']) > time()-60)) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            throw new DomainException('Limite de solicitações atingido. Tente novamente mais tarde.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $attachment = self::upload($file); // Explicação DevIN: Atribui um valor à variável PHP $attachment.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            $stmt = $db->prepare('INSERT INTO support_tickets (
                requester_type,requester_id,requester_name,requester_email,category,description,
                attachment_token,attachment_name,attachment_mime,attachment_bytes
            ) VALUES (?,?,?,?,?,?,?,?,?,?)'); // Explicação DevIN: Continua expressão ou bloco pertencente a função create.
            $token = $attachment['token'] ?? null; // Explicação DevIN: Atribui um valor à variável PHP $token.
            $filename = $attachment['name'] ?? null; // Explicação DevIN: Atribui um valor à variável PHP $filename.
            $mime = $attachment['mime'] ?? null; // Explicação DevIN: Atribui um valor à variável PHP $mime.
            $bytes = $attachment['bytes'] ?? null; // Explicação DevIN: Atribui um valor à variável PHP $bytes.
            $stmt->bind_param('sisssssssi', // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $owner['type'], $owner['id'], $owner['name'], $owner['email'], // Explicação DevIN: Continua expressão ou bloco pertencente a função create.
                $category, $description, $token, $filename, $mime, $bytes // Explicação DevIN: Continua expressão ou bloco pertencente a função create.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $id = (int) $db->insert_id; // Explicação DevIN: Atribui um valor à variável PHP $id.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função create.
            return $id; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            // Não deixar arquivo órfão quando a escrita MySQL falhar.
            if ($attachment) @unlink(self::storageDirectory() . '/' . $attachment['token']); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw $exception; // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Lista de chamados visível aos ADMs, limitada para não sobrecarregar a página.
    public static function listAdmin(mysqli $db): array // Explicação DevIN: Declara função/método listAdmin, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        self::requireSchema($db); // Explicação DevIN: Continua expressão ou bloco pertencente a função listAdmin.
        return $db->query('SELECT id,requester_name,requester_email,requester_type,
            category,description,status,attachment_name,created_at,updated_at
            FROM support_tickets ORDER BY FIELD(status,"aberto","respondido","fechado"),
            updated_at DESC LIMIT 150')->fetch_all(MYSQLI_ASSOC); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Só retorna chamados da conta autenticada, mesmo que outros IDs sejam conhecidos.
    public static function listOwn(mysqli $db, array $actor): array // Explicação DevIN: Declara função/método listOwn, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        self::requireSchema($db); // Explicação DevIN: Continua expressão ou bloco pertencente a função listOwn.
        $owner = self::owner($db, $actor); // Explicação DevIN: Atribui um valor à variável PHP $owner.
        $stmt = $db->prepare('SELECT id,category,description,status,attachment_name,
            created_at,updated_at FROM support_tickets
            WHERE requester_type=? AND requester_id=? ORDER BY id DESC LIMIT 60'); // Explicação DevIN: Continua expressão ou bloco pertencente a função listOwn.
        $stmt->bind_param('si', $owner['type'], $owner['id']); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função listOwn.
        return $rows; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // A checagem de proprietário acontece antes de revelar descrição ou anexo.
    public static function detail(mysqli $db, int $ticketId, array $actor): array // Explicação DevIN: Declara função/método detail, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        self::requireSchema($db); // Explicação DevIN: Continua expressão ou bloco pertencente a função detail.
        if ($ticketId < 1) throw new InvalidArgumentException('Chamado inválido.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $stmt = $db->prepare('SELECT * FROM support_tickets WHERE id=? LIMIT 1'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->bind_param('i', $ticketId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $ticket = $stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função detail.
        if (!$ticket) throw new DomainException('Chamado não encontrado.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if (($actor['tipo'] ?? '') !== 'adm' // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            && (!(in_array($actor['tipo'] ?? '', ['pessoa','empresa'], true)) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                || $actor['tipo'] !== $ticket['requester_type'] // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                || (int)$actor['id'] !== (int)$ticket['requester_id'])) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            throw new DomainException('Você não tem permissão para consultar esse chamado.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $stmt = $db->prepare('SELECT id,admin_id,message,created_at,chat_sent_at,email_sent_at
            FROM support_replies WHERE ticket_id=? ORDER BY id ASC'); // Explicação DevIN: Continua expressão ou bloco pertencente a função detail.
        $stmt->bind_param('i', $ticketId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $replies = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função detail.
        // Token interno e caminhos do filesystem jamais são enviados ao navegador.
        unset($ticket['attachment_token']); // Explicação DevIN: Continua expressão ou bloco pertencente a função detail.
        return ['ticket'=>$ticket, 'replies'=>$replies]; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // A resposta é persistida em transação antes da entrega via e-mail e chat.
    public static function reply(mysqli $db, int $adminId, int $ticketId, string $message): array // Explicação DevIN: Declara função/método reply, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        self::requireSchema($db); // Explicação DevIN: Continua expressão ou bloco pertencente a função reply.
        if ($adminId < 1) throw new DomainException('Acesso administrativo obrigatório.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $message = trim($message); // Explicação DevIN: Atribui um valor à variável PHP $message.
        if ($ticketId < 1 || mb_strlen($message, 'UTF-8') < 2 // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            || mb_strlen($message, 'UTF-8') > 3000) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            throw new InvalidArgumentException('Escreva uma resposta entre 2 e 3.000 caracteres.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $db->begin_transaction(); // Explicação DevIN: Continua expressão ou bloco pertencente a função reply.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            $stmt = $db->prepare('SELECT id,status FROM support_tickets WHERE id=? FOR UPDATE'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->bind_param('i', $ticketId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $ticket = $stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função reply.
            if (!$ticket || $ticket['status'] === 'fechado') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new DomainException('O chamado não existe ou já está fechado.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $stmt = $db->prepare('INSERT INTO support_replies(ticket_id,admin_id,message)
                VALUES (?,?,?)'); // Explicação DevIN: Continua expressão ou bloco pertencente a função reply.
            $stmt->bind_param('iis', $ticketId, $adminId, $message); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $replyId = (int)$db->insert_id; // Explicação DevIN: Atribui um valor à variável PHP $replyId.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função reply.
            $stmt = $db->prepare('UPDATE support_tickets SET status="respondido",
                updated_at=NOW() WHERE id=?'); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            $stmt->bind_param('i', $ticketId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função reply.
            $db->commit(); // Explicação DevIN: Continua expressão ou bloco pertencente a função reply.
        } catch (Throwable $error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            $db->rollback(); // Explicação DevIN: Continua expressão ou bloco pertencente a função reply.
            throw $error; // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return ['reply_id'=>$replyId] + self::notify($db, $replyId); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Entrega idempotente: o par ticket/reply dá uma chave única na caixa de mensagens.
    // O e-mail é uma tentativa isolada; falha SMTP não cancela a resposta já registrada.
    public static function notify(mysqli $db, int $replyId): array // Explicação DevIN: Declara função/método notify, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $stmt = $db->prepare('SELECT r.*,t.requester_type,t.requester_id,
            t.requester_name,t.requester_email FROM support_replies r
            JOIN support_tickets t ON t.id=r.ticket_id WHERE r.id=? LIMIT 1'); // Explicação DevIN: Continua expressão ou bloco pertencente a função notify.
        $stmt->bind_param('i', $replyId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $row = $stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função notify.
        if (!$row) throw new DomainException('Resposta não localizada.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $chat = !empty($row['chat_sent_at']); // Explicação DevIN: Atribui um valor à variável PHP $chat.
        $email = !empty($row['email_sent_at']); // Explicação DevIN: Atribui um valor à variável PHP $email.
        if (!$chat) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                // O contato "sistema" aparece com o nome Suporte DevIN no chat.
                appendPlatformChatNotification( // Explicação DevIN: Continua expressão ou bloco pertencente a função notify.
                    'support-reply:' . $replyId, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    (string)$row['requester_type'], // Explicação DevIN: Continua expressão ou bloco pertencente a função notify.
                    (int)$row['requester_id'], // Explicação DevIN: Continua expressão ou bloco pertencente a função notify.
                    "Suporte DevIN — resposta ao chamado #{$row['ticket_id']}:\n" . $row['message'] // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                $db->query('UPDATE support_replies SET chat_sent_at=NOW()
                    WHERE id=' . (int)$replyId . ' AND chat_sent_at IS NULL'); // Explicação DevIN: Continua expressão ou bloco pertencente a função notify.
                $chat = true; // Explicação DevIN: Atribui um valor à variável PHP $chat.
            } catch (Throwable $error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
                error_log('Suporte: entrega interna falhou para resposta ' . $replyId); // Explicação DevIN: Continua expressão ou bloco pertencente a função notify.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!$email && (int)$row['email_attempts'] < 5) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            // Não incluir anexos no e-mail; enviar apenas resposta e ID do chamado.
            $safeName = htmlspecialchars((string)$row['requester_name'], ENT_QUOTES, 'UTF-8'); // Explicação DevIN: Atribui um valor à variável PHP $safeName.
            $safeMessage = nl2br(htmlspecialchars((string)$row['message'], ENT_QUOTES, 'UTF-8')); // Explicação DevIN: Atribui um valor à variável PHP $safeMessage.
            $body = '<p>Olá, ' . $safeName . '.</p><p>O <strong>Suporte DevIN</strong> respondeu ao seu chamado <strong>#' // Explicação DevIN: Atribui um valor à variável PHP $body.
                . (int)$row['ticket_id'] . '</strong>:</p><blockquote>' . $safeMessage // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                . '</blockquote><p>A resposta também está disponível nas Mensagens e na Central de Suporte.</p>'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            $stmt = $db->prepare('UPDATE support_replies SET email_attempts=email_attempts+1 WHERE id=?'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->bind_param('i', $replyId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função notify.
            if (MailerHelper::enviar( // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                (string)$row['requester_email'], (string)$row['requester_name'], // Explicação DevIN: Continua expressão ou bloco pertencente a função notify.
                'Suporte DevIN — resposta ao chamado #' . $row['ticket_id'], $body // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            )) { // Explicação DevIN: Continua expressão ou bloco pertencente a função notify.
                $stmt = $db->prepare('UPDATE support_replies SET email_sent_at=NOW() WHERE id=?'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->bind_param('i', $replyId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função notify.
                $email = true; // Explicação DevIN: Atribui um valor à variável PHP $email.
            } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
                error_log('Suporte: e-mail pendente para resposta ' . $replyId); // Explicação DevIN: Continua expressão ou bloco pertencente a função notify.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return ['chat_sent'=>$chat, 'email_sent'=>$email]; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Somente ADM pode encerrar; encerramento não apaga histórico nem anexos.
    public static function close(mysqli $db, int $ticketId): void // Explicação DevIN: Declara função/método close, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if ($ticketId < 1) throw new InvalidArgumentException('Chamado inválido.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $stmt = $db->prepare('UPDATE support_tickets SET status="fechado" WHERE id=?'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->bind_param('i', $ticketId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $changed = $stmt->affected_rows; // Explicação DevIN: Atribui um valor à variável PHP $changed.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função close.
        if ($changed < 1) throw new DomainException('Chamado já fechado ou inexistente.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Consulta de anexo exige o mesmo controle de acesso que a descrição.
    public static function attachment(mysqli $db, int $ticketId, array $actor): array // Explicação DevIN: Declara função/método attachment, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        self::detail($db, $ticketId, $actor); // Explicação DevIN: Continua expressão ou bloco pertencente a função attachment.
        $stmt = $db->prepare('SELECT attachment_token,attachment_name,attachment_mime,
            attachment_bytes FROM support_tickets WHERE id=? LIMIT 1'); // Explicação DevIN: Continua expressão ou bloco pertencente a função attachment.
        $stmt->bind_param('i', $ticketId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $data = $stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função attachment.
        if (!$data || !$data['attachment_token'] // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            || !preg_match('/^[a-f0-9]{48}$/', $data['attachment_token'])) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            throw new DomainException('Este chamado não possui anexo.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $folder = self::storageDirectory(); // Explicação DevIN: Atribui um valor à variável PHP $folder.
        $file = $folder . '/' . $data['attachment_token']; // Explicação DevIN: Atribui um valor à variável PHP $file.
        if (!is_file($file) || is_link($file)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new DomainException('Arquivo não encontrado no armazenamento protegido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return ['path'=>$file, 'name'=>$data['attachment_name'], // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            'mime'=>$data['attachment_mime'], 'bytes'=>(int)$data['attachment_bytes']]; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
