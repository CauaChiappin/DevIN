-- Auditoria para exclusões supervisionadas pelo JarvisDevIN.
-- Executar somente após backup no banco correto e antes de liberar exclusões.
CREATE TABLE IF NOT EXISTS jarvis_moderation_audit ( -- Explicação DevIN: Cria tabela persistente no banco de dados, se aplicável com proteção de existência.
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, -- Explicação DevIN: Declara a chave primária para identificar registros sem duplicação.
    admin_id INT NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    target_type ENUM('vaga','pessoa','empresa') NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    target_id INT NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    action_code ENUM('deleted') NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    INDEX idx_jarvis_moderation_admin (admin_id, created_at), -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
    INDEX idx_jarvis_moderation_target (target_type, target_id) -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; -- Explicação DevIN: Define mecanismo de armazenamento ou conjunto de caracteres da tabela.
