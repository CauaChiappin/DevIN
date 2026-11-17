-- REVISÃO DETALHADA DEVIN — database/migrations/jarvis_admin_controlled_actions.sql
-- Finalidade: jarvis admin controlled actions.
-- Domínio: assistência administrativa.
-- Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
-- Os comentários ao lado de cada instrução são mantidos; as notas adicionais
-- explicam as decisões e os efeitos do bloco sem modificar o comportamento.

-- JARVIS Admin — observabilidade e mensagens oficiais com revisão humana.
-- Migration ADITIVA. Aplicar somente após backup e em ambiente de desenvolvimento.
-- Não altera usuários, vagas, denúncias ou o histórico anterior.
-- Detalhe DevIN: Mudança estrutural; aplicar uma vez após backup e conferir restrições, chaves e índices.
CREATE TABLE IF NOT EXISTS jarvis_action_events ( -- Explicação DevIN: Cria tabela persistente no banco de dados, se aplicável com proteção de existência.
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, -- Explicação DevIN: Declara a chave primária para identificar registros sem duplicação.
    admin_id INT NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    origin ENUM('ai','admin','system') NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    event_name ENUM('search','inspect','analysis','draft_generated','draft_manual', -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
        'draft_revised','message_sent','message_failed','draft_cancelled','action_denied') NOT NULL, -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
    status ENUM('success','failure','denied') NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    target_type VARCHAR(24) NOT NULL DEFAULT 'none', -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    target_id BIGINT UNSIGNED NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    detail_code VARCHAR(60) NOT NULL DEFAULT '', -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    INDEX idx_jarvis_action_events_admin (admin_id,created_at,id), -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
    INDEX idx_jarvis_action_events_kind (event_name,created_at) -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; -- Explicação DevIN: Define mecanismo de armazenamento ou conjunto de caracteres da tabela.

-- Detalhe DevIN: Mudança estrutural; aplicar uma vez após backup e conferir restrições, chaves e índices.
CREATE TABLE IF NOT EXISTS jarvis_platform_outbox ( -- Explicação DevIN: Cria tabela persistente no banco de dados, se aplicável com proteção de existência.
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, -- Explicação DevIN: Declara a chave primária para identificar registros sem duplicação.
    admin_id INT NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    recipient_type ENUM('pessoa','empresa') NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    recipient_id INT NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    body TEXT NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    source ENUM('ai','admin') NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    status ENUM('draft','sending','sent','failed','cancelled') NOT NULL DEFAULT 'draft', -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    reviewed_at DATETIME(6) NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    sent_at DATETIME(6) NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    approved_by INT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    last_error_code VARCHAR(32) NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    INDEX idx_jarvis_outbox_owner (admin_id,created_at,id), -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
    INDEX idx_jarvis_outbox_target (recipient_type,recipient_id,sent_at), -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
    INDEX idx_jarvis_outbox_quota (admin_id,status,sent_at) -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; -- Explicação DevIN: Define mecanismo de armazenamento ou conjunto de caracteres da tabela.
