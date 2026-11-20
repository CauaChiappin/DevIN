-- DevIN: propriedade formal dos chamados de Suporte (migração 008).
-- Execute no phpMyAdmin da Hostinger somente depois de backup completo.
-- Não altera status, conteúdo ou remetentes já existentes; chamados antigos permanecem sem responsável.
-- A alteração da coluna é idempotente mesmo quando executada novamente.
SET @devin_008_exists := ( -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS -- Explicação DevIN: Seleciona campos ou resultados para uma consulta ao banco.
    WHERE TABLE_SCHEMA = DATABASE() -- Explicação DevIN: Restringe os registros afetados pela condição SQL.
      AND TABLE_NAME = 'support_tickets' -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
      AND COLUMN_NAME = 'assigned_admin_id' -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
); -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
SET @devin_008_sql := IF(@devin_008_exists = 0, -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
    'ALTER TABLE support_tickets ADD COLUMN assigned_admin_id INT NULL DEFAULT NULL AFTER status', -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    'SELECT 1'); -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
PREPARE devin008 FROM @devin_008_sql; -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
EXECUTE devin008; -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
DEALLOCATE PREPARE devin008; -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.

SET @devin_008_exists := ( -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS -- Explicação DevIN: Seleciona campos ou resultados para uma consulta ao banco.
    WHERE TABLE_SCHEMA = DATABASE() -- Explicação DevIN: Restringe os registros afetados pela condição SQL.
      AND TABLE_NAME = 'support_tickets' -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
      AND COLUMN_NAME = 'assigned_at' -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
); -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
SET @devin_008_sql := IF(@devin_008_exists = 0, -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
    'ALTER TABLE support_tickets ADD COLUMN assigned_at DATETIME NULL DEFAULT NULL AFTER assigned_admin_id', -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    'SELECT 1'); -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
PREPARE devin008 FROM @devin_008_sql; -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
EXECUTE devin008; -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
DEALLOCATE PREPARE devin008; -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.

SET @devin_008_exists := ( -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS -- Explicação DevIN: Seleciona campos ou resultados para uma consulta ao banco.
    WHERE TABLE_SCHEMA = DATABASE() -- Explicação DevIN: Restringe os registros afetados pela condição SQL.
      AND TABLE_NAME = 'support_tickets' -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
      AND INDEX_NAME = 'idx_support_assigned_admin' -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
); -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
SET @devin_008_sql := IF(@devin_008_exists = 0, -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
    'CREATE INDEX idx_support_assigned_admin ON support_tickets (assigned_admin_id, status, updated_at)', -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
    'SELECT 1'); -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
PREPARE devin008 FROM @devin_008_sql; -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
EXECUTE devin008; -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
DEALLOCATE PREPARE devin008; -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.

-- Log de trocas de representante, incluindo a atribuição automática na primeira resposta.
CREATE TABLE IF NOT EXISTS support_assignment_events ( -- Explicação DevIN: Cria tabela persistente no banco de dados, se aplicável com proteção de existência.
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, -- Explicação DevIN: Declara a chave primária para identificar registros sem duplicação.
    ticket_id BIGINT UNSIGNED NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    old_admin_id INT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    new_admin_id INT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    changed_by INT NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    event_type ENUM('claimed','taken_over','released') NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    CONSTRAINT fk_support_assignment_ticket -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
        FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE, -- Explicação DevIN: Declara relação de integridade referencial entre tabelas.
    INDEX idx_support_assignment_ticket (ticket_id, created_at), -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
    INDEX idx_support_assignment_admin (new_admin_id, created_at) -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; -- Explicação DevIN: Define mecanismo de armazenamento ou conjunto de caracteres da tabela.

-- Diagnóstico (somente leitura):
-- SELECT id, assigned_admin_id, status FROM support_tickets ORDER BY id DESC LIMIT 10;
