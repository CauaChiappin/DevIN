-- Auditoria para exclusões supervisionadas pelo JarvisDevIN.
-- Executar somente após backup no banco correto e antes de liberar exclusões.
CREATE TABLE IF NOT EXISTS jarvis_moderation_audit (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    target_type ENUM('vaga','pessoa','empresa') NOT NULL,
    target_id INT NOT NULL,
    action_code ENUM('deleted') NOT NULL,
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    INDEX idx_jarvis_moderation_admin (admin_id, created_at),
    INDEX idx_jarvis_moderation_target (target_type, target_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
