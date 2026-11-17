-- Central de Suporte DevIN: executar somente após backup e validação em homologação.
-- As duas tabelas guardam chamados e respostas sem modificar cadastros existentes.
-- Um chamado pertence a uma pessoa OU empresa; IDs são separados por tipo.
-- A ausência proposital de FK polimórfica preserva histórico se a conta sair.
CREATE TABLE IF NOT EXISTS support_tickets ( -- Explicação DevIN: Cria tabela persistente no banco de dados, se aplicável com proteção de existência.
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, -- Explicação DevIN: Declara a chave primária para identificar registros sem duplicação.
    requester_type ENUM('pessoa', 'empresa') NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    requester_id INT NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    requester_name VARCHAR(130) NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    requester_email VARCHAR(190) NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    category ENUM('erro', 'duvida', 'conta', 'vaga', 'seguranca', 'sugestao', 'outro') NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    description TEXT NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    attachment_token CHAR(48) NULL, -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
    attachment_name VARCHAR(180) NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    attachment_mime VARCHAR(90) NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    attachment_bytes INT UNSIGNED NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    status ENUM('aberto', 'respondido', 'fechado') NOT NULL DEFAULT 'aberto', -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    INDEX idx_support_status (status, updated_at), -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
    INDEX idx_support_owner (requester_type, requester_id, created_at) -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; -- Explicação DevIN: Define mecanismo de armazenamento ou conjunto de caracteres da tabela.

-- Cada resposta associa um administrador identificado a um chamado.
-- Os estados de entrega são independentes: e-mail pode falhar sem perder a resposta.
CREATE TABLE IF NOT EXISTS support_replies ( -- Explicação DevIN: Cria tabela persistente no banco de dados, se aplicável com proteção de existência.
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, -- Explicação DevIN: Declara a chave primária para identificar registros sem duplicação.
    ticket_id BIGINT UNSIGNED NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    admin_id INT NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    message TEXT NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    chat_sent_at DATETIME NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    email_sent_at DATETIME NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    email_attempts SMALLINT UNSIGNED NOT NULL DEFAULT 0, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    CONSTRAINT fk_support_reply_ticket FOREIGN KEY (ticket_id) -- Explicação DevIN: Declara relação de integridade referencial entre tabelas.
        REFERENCES support_tickets(id) ON DELETE CASCADE, -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
    INDEX idx_support_reply_ticket (ticket_id, created_at), -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
    INDEX idx_support_delivery (chat_sent_at, email_sent_at) -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; -- Explicação DevIN: Define mecanismo de armazenamento ou conjunto de caracteres da tabela.
