-- REVISÃO DETALHADA DEVIN — database/migrations/jarvis_admin.sql
-- Finalidade: jarvis admin.
-- Domínio: assistência administrativa.
-- Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
-- Os comentários ao lado de cada instrução são mantidos; as notas adicionais
-- explicam as decisões e os efeitos do bloco sem modificar o comportamento.

-- JARVIS Admin — aplicar uma vez no MySQL do DevIN antes de ativar histórico e atividades.
-- Não modifica tabelas existentes nem executa ações administrativas.
-- Detalhe DevIN: Mudança estrutural; aplicar uma vez após backup e conferir restrições, chaves e índices.
CREATE TABLE IF NOT EXISTS jarvis_conversations ( -- Explicação DevIN: Cria tabela persistente no banco de dados, se aplicável com proteção de existência.
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, -- Explicação DevIN: Declara a chave primária para identificar registros sem duplicação.
    admin_id INT NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    title VARCHAR(150) NOT NULL DEFAULT 'Nova conversa', -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    INDEX idx_jarvis_conversations_admin (admin_id, updated_at) -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; -- Explicação DevIN: Define mecanismo de armazenamento ou conjunto de caracteres da tabela.

-- Detalhe DevIN: Mudança estrutural; aplicar uma vez após backup e conferir restrições, chaves e índices.
CREATE TABLE IF NOT EXISTS jarvis_messages ( -- Explicação DevIN: Cria tabela persistente no banco de dados, se aplicável com proteção de existência.
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, -- Explicação DevIN: Declara a chave primária para identificar registros sem duplicação.
    conversation_id BIGINT UNSIGNED NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    role ENUM('user', 'assistant') NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    content MEDIUMTEXT NOT NULL, -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    INDEX idx_jarvis_messages_conv (conversation_id, id), -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
    CONSTRAINT fk_jarvis_messages_conversation FOREIGN KEY (conversation_id) -- Explicação DevIN: Declara relação de integridade referencial entre tabelas.
        REFERENCES jarvis_conversations(id) ON DELETE CASCADE -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; -- Explicação DevIN: Define mecanismo de armazenamento ou conjunto de caracteres da tabela.

-- Detalhe DevIN: Mudança estrutural; aplicar uma vez após backup e conferir restrições, chaves e índices.
CREATE TABLE IF NOT EXISTS jarvis_tasks ( -- Explicação DevIN: Cria tabela persistente no banco de dados, se aplicável com proteção de existência.
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, -- Explicação DevIN: Declara a chave primária para identificar registros sem duplicação.
    admin_id INT NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    title VARCHAR(150) NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    description TEXT NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    status ENUM('pending', 'in_progress', 'done', 'cancelled') NOT NULL DEFAULT 'pending', -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    INDEX idx_jarvis_tasks_admin (admin_id, status, updated_at) -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; -- Explicação DevIN: Define mecanismo de armazenamento ou conjunto de caracteres da tabela.

-- Detalhe DevIN: Mudança estrutural; aplicar uma vez após backup e conferir restrições, chaves e índices.
CREATE TABLE IF NOT EXISTS jarvis_task_audit ( -- Explicação DevIN: Cria tabela persistente no banco de dados, se aplicável com proteção de existência.
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, -- Explicação DevIN: Declara a chave primária para identificar registros sem duplicação.
    task_id BIGINT UNSIGNED NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    admin_id INT NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    action ENUM('created', 'started', 'completed', 'cancelled') NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    INDEX idx_jarvis_task_audit_task (task_id, created_at), -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
    CONSTRAINT fk_jarvis_task_audit_task FOREIGN KEY (task_id) -- Explicação DevIN: Declara relação de integridade referencial entre tabelas.
        REFERENCES jarvis_tasks(id) ON DELETE CASCADE -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; -- Explicação DevIN: Define mecanismo de armazenamento ou conjunto de caracteres da tabela.
