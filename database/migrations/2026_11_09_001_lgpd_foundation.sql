-- DevIN | FASE 1: Bases de privacidade, consentimento e auditoria
-- MySQL 8.0.16+ / InnoDB / utf8mb4 | Nenhuma tabela existente é alterada.
-- Criar SOMENTE em desenvolvimento, apos backup e revisao.
-- Identidade: pessoa.id_pessoa / empresa.id_empresa / administrador.id_administrador.
-- Referencias polimorficas intencionais: sem FK para tabelas de contas existentes.
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS legal_document_versions (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 document_type ENUM('terms','privacy','cookies') NOT NULL,
 version_code VARCHAR(40) NOT NULL,
 content_sha256 BINARY(32) NOT NULL,
 published_at DATETIME(6) NOT NULL,
 is_active TINYINT(1) NOT NULL DEFAULT 0,
 PRIMARY KEY (id),
 UNIQUE KEY uq_legal_document_version (document_type, version_code),
 KEY idx_legal_document_active (document_type, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_legal_acceptances (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 account_type ENUM('pessoa','empresa','adm') NOT NULL,
 account_id INT UNSIGNED NULL,
 terms_version VARCHAR(40) NOT NULL,
 privacy_notice_version VARCHAR(40) NOT NULL,
 accepted_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
 ip_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NULL,
 user_agent VARCHAR(512) NULL,
 capture_source ENUM('registration','renewal') NOT NULL,
 PRIMARY KEY (id),
 KEY idx_user_legal_account (account_type, account_id, accepted_at),
 KEY idx_user_legal_terms (terms_version, privacy_notice_version)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_consent_preferences (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 account_type ENUM('pessoa','empresa','adm') NOT NULL,
 account_id INT UNSIGNED NOT NULL,
 purpose ENUM('marketing_email','analytics_cookies','ai_resume_transfer','ai_admin_history') NOT NULL,
 is_granted TINYINT(1) NOT NULL DEFAULT 0,
 policy_version VARCHAR(40) NOT NULL,
 updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
 PRIMARY KEY (id),
 UNIQUE KEY uq_user_consent_preference (account_type, account_id, purpose),
 KEY idx_user_consent_purpose (purpose, is_granted),
 CONSTRAINT chk_user_consent_preference_bool CHECK (is_granted IN (0,1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_consent_events (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 account_type ENUM('pessoa','empresa','adm') NOT NULL,
 account_id INT UNSIGNED NULL,
 purpose ENUM('marketing_email','analytics_cookies','ai_resume_transfer','ai_admin_history') NOT NULL,
 event_type ENUM('granted','revoked') NOT NULL,
 policy_version VARCHAR(40) NOT NULL,
 happened_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
 ip_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NULL,
 user_agent VARCHAR(512) NULL,
 PRIMARY KEY (id),
 KEY idx_user_consent_history (account_type, account_id, purpose, happened_at),
 KEY idx_user_consent_event_date (happened_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_logs (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 event_type VARCHAR(64) NOT NULL,
 outcome ENUM('success','failure','blocked') NOT NULL,
 actor_type ENUM('pessoa','empresa','adm') NULL,
 actor_id INT UNSIGNED NULL,
 subject_type ENUM('pessoa','empresa','adm') NULL,
 subject_id INT UNSIGNED NULL,
 event_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
 ip_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NULL,
 request_id CHAR(36) CHARACTER SET ascii COLLATE ascii_bin NULL,
 metadata_json JSON NULL,
 retention_until DATETIME(6) NOT NULL,
 PRIMARY KEY (id),
 KEY idx_audit_event (event_type, event_at),
 KEY idx_audit_actor (actor_type, actor_id, event_at),
 KEY idx_audit_retention (retention_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS app_access_logs (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 client_ip VARBINARY(16) NOT NULL,
 accessed_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
 method VARCHAR(8) NOT NULL,
 path VARCHAR(255) NOT NULL,
 http_status SMALLINT UNSIGNED NOT NULL,
 actor_type ENUM('pessoa','empresa','adm') NULL,
 actor_id INT UNSIGNED NULL,
 delete_after DATETIME(6) NOT NULL,
 legal_hold_until DATETIME(6) NULL,
 PRIMARY KEY (id),
 KEY idx_access_time (accessed_at),
 KEY idx_access_expiration (delete_after, legal_hold_until),
 KEY idx_access_actor (actor_type, actor_id, accessed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS privacy_requests (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 public_reference CHAR(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
 account_type ENUM('pessoa','empresa','adm') NULL,
 account_id INT UNSIGNED NULL,
 request_type ENUM('access','portability','rectification','erasure','anonymization','objection','consent_withdrawal','other') NOT NULL,
 status ENUM('received','verifying_identity','under_review','completed','denied') NOT NULL DEFAULT 'received',
 requested_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
 identity_verified_at DATETIME(6) NULL,
 resolved_at DATETIME(6) NULL,
 decision_basis VARCHAR(255) NULL,
 PRIMARY KEY (id),
 UNIQUE KEY uq_privacy_request_reference (public_reference),
 KEY idx_privacy_request_subject (account_type, account_id, requested_at),
 KEY idx_privacy_request_pending (status, requested_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS privacy_erasure_receipts (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 request_id BIGINT UNSIGNED NOT NULL,
 receipt_reference CHAR(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
 outcome ENUM('deleted','anonymized','partially_retained') NOT NULL,
 retained_categories_json JSON NULL,
 legal_retention_basis VARCHAR(255) NULL,
 processed_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
 PRIMARY KEY (id),
 UNIQUE KEY uq_privacy_erasure_receipt (receipt_reference),
 UNIQUE KEY uq_privacy_erasure_request (request_id),
 CONSTRAINT fk_privacy_erasure_request FOREIGN KEY (request_id)
 REFERENCES privacy_requests (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS privacy_security_incidents (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
 reference_code CHAR(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
 discovered_at DATETIME(6) NOT NULL,
 recorded_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
 risk_level ENUM('under_review','low','relevant') NOT NULL DEFAULT 'under_review',
 affected_data_categories_json JSON NULL,
 affected_subjects_count INT UNSIGNED NULL,
 incident_summary TEXT NOT NULL,
 mitigation_summary TEXT NULL,
 notified_anpd_at DATETIME(6) NULL,
 notified_subjects_at DATETIME(6) NULL,
 retain_until DATETIME(6) NOT NULL,
 PRIMARY KEY (id),
 UNIQUE KEY uq_privacy_security_incident_reference (reference_code),
 KEY idx_privacy_security_incident_date (discovered_at),
 KEY idx_privacy_security_incident_retention (retain_until)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Registrar IP HMAC em consentimentos, IP real em logs de acesso quando aplicavel.
-- Nao importar aceites anteriores nem apagar dados existentes automaticamente.
