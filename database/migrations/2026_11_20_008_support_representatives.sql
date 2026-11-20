-- DevIN: propriedade formal dos chamados de Suporte (migração 008).
-- Execute no phpMyAdmin da Hostinger somente depois de backup completo.
-- Não altera status, conteúdo ou remetentes já existentes; chamados antigos permanecem sem responsável.
-- A alteração da coluna é idempotente mesmo quando executada novamente.
SET @devin_008_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'support_tickets'
      AND COLUMN_NAME = 'assigned_admin_id'
);
SET @devin_008_sql := IF(@devin_008_exists = 0,
    'ALTER TABLE support_tickets ADD COLUMN assigned_admin_id INT NULL DEFAULT NULL AFTER status',
    'SELECT 1');
PREPARE devin008 FROM @devin_008_sql;
EXECUTE devin008;
DEALLOCATE PREPARE devin008;

SET @devin_008_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'support_tickets'
      AND COLUMN_NAME = 'assigned_at'
);
SET @devin_008_sql := IF(@devin_008_exists = 0,
    'ALTER TABLE support_tickets ADD COLUMN assigned_at DATETIME NULL DEFAULT NULL AFTER assigned_admin_id',
    'SELECT 1');
PREPARE devin008 FROM @devin_008_sql;
EXECUTE devin008;
DEALLOCATE PREPARE devin008;

SET @devin_008_exists := (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'support_tickets'
      AND INDEX_NAME = 'idx_support_assigned_admin'
);
SET @devin_008_sql := IF(@devin_008_exists = 0,
    'CREATE INDEX idx_support_assigned_admin ON support_tickets (assigned_admin_id, status, updated_at)',
    'SELECT 1');
PREPARE devin008 FROM @devin_008_sql;
EXECUTE devin008;
DEALLOCATE PREPARE devin008;

-- Log de trocas de representante, incluindo a atribuição automática na primeira resposta.
CREATE TABLE IF NOT EXISTS support_assignment_events (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    ticket_id BIGINT UNSIGNED NOT NULL,
    old_admin_id INT NULL,
    new_admin_id INT NULL,
    changed_by INT NOT NULL,
    event_type ENUM('claimed','taken_over','released') NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_support_assignment_ticket
        FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) ON DELETE CASCADE,
    INDEX idx_support_assignment_ticket (ticket_id, created_at),
    INDEX idx_support_assignment_admin (new_admin_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Diagnóstico (somente leitura):
-- SELECT id, assigned_admin_id, status FROM support_tickets ORDER BY id DESC LIMIT 10;
