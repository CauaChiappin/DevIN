-- Central de Suporte DevIN: executar somente após backup e validação em homologação.
-- As duas tabelas guardam chamados e respostas sem modificar cadastros existentes.
-- Um chamado pertence a uma pessoa OU empresa; IDs são separados por tipo.
-- A ausência proposital de FK polimórfica preserva histórico se a conta sair.
CREATE TABLE IF NOT EXISTS support_tickets (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    requester_type ENUM('pessoa', 'empresa') NOT NULL,
    requester_id INT NOT NULL,
    requester_name VARCHAR(130) NOT NULL,
    requester_email VARCHAR(190) NOT NULL,
    category ENUM('erro', 'duvida', 'conta', 'vaga', 'seguranca', 'sugestao', 'outro') NOT NULL,
    description TEXT NOT NULL,
    attachment_token CHAR(48) NULL,
    attachment_name VARCHAR(180) NULL,
    attachment_mime VARCHAR(90) NULL,
    attachment_bytes INT UNSIGNED NULL,
    status ENUM('aberto', 'respondido', 'fechado') NOT NULL DEFAULT 'aberto',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_support_status (status, updated_at),
    INDEX idx_support_owner (requester_type, requester_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cada resposta associa um administrador identificado a um chamado.
-- Os estados de entrega são independentes: e-mail pode falhar sem perder a resposta.
CREATE TABLE IF NOT EXISTS support_replies (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ticket_id BIGINT UNSIGNED NOT NULL,
    admin_id INT NOT NULL,
    message TEXT NOT NULL,
    chat_sent_at DATETIME NULL,
    email_sent_at DATETIME NULL,
    email_attempts SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_support_reply_ticket FOREIGN KEY (ticket_id)
        REFERENCES support_tickets(id) ON DELETE CASCADE,
    INDEX idx_support_reply_ticket (ticket_id, created_at),
    INDEX idx_support_delivery (chat_sent_at, email_sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
