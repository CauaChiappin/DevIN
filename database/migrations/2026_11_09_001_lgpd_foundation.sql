-- DevIN | FASE 1: Bases de privacidade, consentimento e auditoria
-- MySQL 8.0.16+ / InnoDB / utf8mb4 | Nenhuma tabela existente é alterada.
-- Criar SOMENTE em desenvolvimento, apos backup e revisao.
-- Identidade: pessoa.id_pessoa / empresa.id_empresa / administrador.id_administrador.
-- Referencias polimorficas intencionais: sem FK para tabelas de contas existentes.
SET NAMES utf8mb4; -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.

CREATE TABLE IF NOT EXISTS legal_document_versions ( -- Explicação DevIN: Cria tabela persistente no banco de dados, se aplicável com proteção de existência.
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 document_type ENUM('terms','privacy','cookies') NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 version_code VARCHAR(40) NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 content_sha256 BINARY(32) NOT NULL, -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
 published_at DATETIME(6) NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 is_active TINYINT(1) NOT NULL DEFAULT 0, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 PRIMARY KEY (id), -- Explicação DevIN: Declara a chave primária para identificar registros sem duplicação.
 UNIQUE KEY uq_legal_document_version (document_type, version_code), -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
 KEY idx_legal_document_active (document_type, is_active) -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; -- Explicação DevIN: Define mecanismo de armazenamento ou conjunto de caracteres da tabela.

CREATE TABLE IF NOT EXISTS user_legal_acceptances ( -- Explicação DevIN: Cria tabela persistente no banco de dados, se aplicável com proteção de existência.
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 account_type ENUM('pessoa','empresa','adm') NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 account_id INT UNSIGNED NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 terms_version VARCHAR(40) NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 privacy_notice_version VARCHAR(40) NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 accepted_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 ip_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NULL, -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
 user_agent VARCHAR(512) NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 capture_source ENUM('registration','renewal') NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 PRIMARY KEY (id), -- Explicação DevIN: Declara a chave primária para identificar registros sem duplicação.
 KEY idx_user_legal_account (account_type, account_id, accepted_at), -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
 KEY idx_user_legal_terms (terms_version, privacy_notice_version) -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; -- Explicação DevIN: Define mecanismo de armazenamento ou conjunto de caracteres da tabela.

CREATE TABLE IF NOT EXISTS user_consent_preferences ( -- Explicação DevIN: Cria tabela persistente no banco de dados, se aplicável com proteção de existência.
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 account_type ENUM('pessoa','empresa','adm') NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 account_id INT UNSIGNED NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 purpose ENUM('marketing_email','analytics_cookies','ai_resume_transfer','ai_admin_history') NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 is_granted TINYINT(1) NOT NULL DEFAULT 0, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 policy_version VARCHAR(40) NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 updated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 PRIMARY KEY (id), -- Explicação DevIN: Declara a chave primária para identificar registros sem duplicação.
 UNIQUE KEY uq_user_consent_preference (account_type, account_id, purpose), -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
 KEY idx_user_consent_purpose (purpose, is_granted), -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
 CONSTRAINT chk_user_consent_preference_bool CHECK (is_granted IN (0,1)) -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; -- Explicação DevIN: Define mecanismo de armazenamento ou conjunto de caracteres da tabela.

CREATE TABLE IF NOT EXISTS user_consent_events ( -- Explicação DevIN: Cria tabela persistente no banco de dados, se aplicável com proteção de existência.
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 account_type ENUM('pessoa','empresa','adm') NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 account_id INT UNSIGNED NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 purpose ENUM('marketing_email','analytics_cookies','ai_resume_transfer','ai_admin_history') NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 event_type ENUM('granted','revoked') NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 policy_version VARCHAR(40) NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 happened_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 ip_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NULL, -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
 user_agent VARCHAR(512) NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 PRIMARY KEY (id), -- Explicação DevIN: Declara a chave primária para identificar registros sem duplicação.
 KEY idx_user_consent_history (account_type, account_id, purpose, happened_at), -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
 KEY idx_user_consent_event_date (happened_at) -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; -- Explicação DevIN: Define mecanismo de armazenamento ou conjunto de caracteres da tabela.

CREATE TABLE IF NOT EXISTS audit_logs ( -- Explicação DevIN: Cria tabela persistente no banco de dados, se aplicável com proteção de existência.
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 event_type VARCHAR(64) NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 outcome ENUM('success','failure','blocked') NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 actor_type ENUM('pessoa','empresa','adm') NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 actor_id INT UNSIGNED NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 subject_type ENUM('pessoa','empresa','adm') NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 subject_id INT UNSIGNED NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 event_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 ip_hash CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NULL, -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
 request_id CHAR(36) CHARACTER SET ascii COLLATE ascii_bin NULL, -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
 metadata_json JSON NULL, -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
 retention_until DATETIME(6) NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 PRIMARY KEY (id), -- Explicação DevIN: Declara a chave primária para identificar registros sem duplicação.
 KEY idx_audit_event (event_type, event_at), -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
 KEY idx_audit_actor (actor_type, actor_id, event_at), -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
 KEY idx_audit_retention (retention_until) -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; -- Explicação DevIN: Define mecanismo de armazenamento ou conjunto de caracteres da tabela.

CREATE TABLE IF NOT EXISTS app_access_logs ( -- Explicação DevIN: Cria tabela persistente no banco de dados, se aplicável com proteção de existência.
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 client_ip VARBINARY(16) NOT NULL, -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
 accessed_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 method VARCHAR(8) NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 path VARCHAR(255) NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 http_status SMALLINT UNSIGNED NOT NULL, -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
 actor_type ENUM('pessoa','empresa','adm') NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 actor_id INT UNSIGNED NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 delete_after DATETIME(6) NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 legal_hold_until DATETIME(6) NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 PRIMARY KEY (id), -- Explicação DevIN: Declara a chave primária para identificar registros sem duplicação.
 KEY idx_access_time (accessed_at), -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
 KEY idx_access_expiration (delete_after, legal_hold_until), -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
 KEY idx_access_actor (actor_type, actor_id, accessed_at) -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; -- Explicação DevIN: Define mecanismo de armazenamento ou conjunto de caracteres da tabela.

CREATE TABLE IF NOT EXISTS privacy_requests ( -- Explicação DevIN: Cria tabela persistente no banco de dados, se aplicável com proteção de existência.
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 public_reference CHAR(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL, -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
 account_type ENUM('pessoa','empresa','adm') NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 account_id INT UNSIGNED NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 request_type ENUM('access','portability','rectification','erasure','anonymization','objection','consent_withdrawal','other') NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 status ENUM('received','verifying_identity','under_review','completed','denied') NOT NULL DEFAULT 'received', -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 requested_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 identity_verified_at DATETIME(6) NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 resolved_at DATETIME(6) NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 decision_basis VARCHAR(255) NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 PRIMARY KEY (id), -- Explicação DevIN: Declara a chave primária para identificar registros sem duplicação.
 UNIQUE KEY uq_privacy_request_reference (public_reference), -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
 KEY idx_privacy_request_subject (account_type, account_id, requested_at), -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
 KEY idx_privacy_request_pending (status, requested_at) -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; -- Explicação DevIN: Define mecanismo de armazenamento ou conjunto de caracteres da tabela.

CREATE TABLE IF NOT EXISTS privacy_erasure_receipts ( -- Explicação DevIN: Cria tabela persistente no banco de dados, se aplicável com proteção de existência.
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 request_id BIGINT UNSIGNED NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 receipt_reference CHAR(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL, -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
 outcome ENUM('deleted','anonymized','partially_retained') NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 retained_categories_json JSON NULL, -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
 legal_retention_basis VARCHAR(255) NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 processed_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 PRIMARY KEY (id), -- Explicação DevIN: Declara a chave primária para identificar registros sem duplicação.
 UNIQUE KEY uq_privacy_erasure_receipt (receipt_reference), -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
 UNIQUE KEY uq_privacy_erasure_request (request_id), -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
 CONSTRAINT fk_privacy_erasure_request FOREIGN KEY (request_id) -- Explicação DevIN: Declara relação de integridade referencial entre tabelas.
 REFERENCES privacy_requests (id) ON DELETE RESTRICT -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; -- Explicação DevIN: Define mecanismo de armazenamento ou conjunto de caracteres da tabela.

CREATE TABLE IF NOT EXISTS privacy_security_incidents ( -- Explicação DevIN: Cria tabela persistente no banco de dados, se aplicável com proteção de existência.
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 reference_code CHAR(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL, -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
 discovered_at DATETIME(6) NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 recorded_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6), -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 risk_level ENUM('under_review','low','relevant') NOT NULL DEFAULT 'under_review', -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 affected_data_categories_json JSON NULL, -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
 affected_subjects_count INT UNSIGNED NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 incident_summary TEXT NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 mitigation_summary TEXT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 notified_anpd_at DATETIME(6) NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 notified_subjects_at DATETIME(6) NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 retain_until DATETIME(6) NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
 PRIMARY KEY (id), -- Explicação DevIN: Declara a chave primária para identificar registros sem duplicação.
 UNIQUE KEY uq_privacy_security_incident_reference (reference_code), -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
 KEY idx_privacy_security_incident_date (discovered_at), -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
 KEY idx_privacy_security_incident_retention (retain_until) -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; -- Explicação DevIN: Define mecanismo de armazenamento ou conjunto de caracteres da tabela.
-- Registrar IP HMAC em consentimentos, IP real em logs de acesso quando aplicavel.
-- Nao importar aceites anteriores nem apagar dados existentes automaticamente.
