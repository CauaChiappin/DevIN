-- JARVIS Admin — observabilidade e mensagens oficiais com revisão humana.
-- Migration ADITIVA. Aplicar somente após backup e em ambiente de desenvolvimento.
-- Não altera usuários, vagas, denúncias ou o histórico anterior.
CREATE TABLE IF NOT EXISTS jarvis_action_events (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    origin ENUM('ai','admin','system') NOT NULL,
    event_name ENUM('search','inspect','analysis','draft_generated','draft_manual',
        'draft_revised','message_sent','message_failed','draft_cancelled','action_denied') NOT NULL,
    status ENUM('success','failure','denied') NOT NULL,
    target_type VARCHAR(24) NOT NULL DEFAULT 'none',
    target_id BIGINT UNSIGNED NULL,
    detail_code VARCHAR(60) NOT NULL DEFAULT '',
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    INDEX idx_jarvis_action_events_admin (admin_id,created_at,id),
    INDEX idx_jarvis_action_events_kind (event_name,created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS jarvis_platform_outbox (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    recipient_type ENUM('pessoa','empresa') NOT NULL,
    recipient_id INT NOT NULL,
    body TEXT NOT NULL,
    source ENUM('ai','admin') NOT NULL,
    status ENUM('draft','sending','sent','failed','cancelled') NOT NULL DEFAULT 'draft',
    created_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    reviewed_at DATETIME(6) NULL,
    sent_at DATETIME(6) NULL,
    approved_by INT NULL,
    last_error_code VARCHAR(32) NULL,
    INDEX idx_jarvis_outbox_owner (admin_id,created_at,id),
    INDEX idx_jarvis_outbox_target (recipient_type,recipient_id,sent_at),
    INDEX idx_jarvis_outbox_quota (admin_id,status,sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
