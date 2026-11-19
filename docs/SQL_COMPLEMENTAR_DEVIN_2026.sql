-- DevIN | PACOTE DE TABELAS COMPLEMENTARES | base verificada: main de 19/11/2026.
-- ATENÇÃO: não cria o banco, nem as tabelas originais pessoa/empresa/vagas/candidatura.
-- Essas tabelas de negócio devem vir de um backup/exportação do banco original do DevIN.
-- Use o banco CERTO já selecionado no phpMyAdmin da Hostinger.
-- ANTES de executar: backup completo do banco e dos JSON antigos do chat.
-- Preferencialmente aplicar primeiro em cópia de homologação.
-- Este pacote é composto apenas pelas sete migrations de CREATE TABLE IF NOT EXISTS.
-- ALTER TABLE administrador fica num arquivo separado: NÃO execute sem conferir colunas existentes.
-- CREATE TABLE IF NOT EXISTS evita erro por tabela já existente, mas NÃO valida estrutura divergente.
-- Recursos opcionais precisam de flags do .env e testes separados.
-- Compatibilidade: LGPD prevê MySQL 8.0.16+; confirme a versão/engine na Hostinger.
-- NÃO habilite JARVIS ações/deleções, RBAC, step-up ou auditoria apenas porque existem tabelas.
-- Nenhum INSERT de credenciais ou de administradores está incluído.


-- ================================================================
-- 01 — LGPD, auditoria e privacidade
-- Fonte: database/migrations/2026_11_09_001_lgpd_foundation.sql
-- ================================================================
-- REVISÃO DETALHADA DEVIN — database/migrations/2026_11_09_001_lgpd_foundation.sql
-- Finalidade: 2026 11 09 001 lgpd foundation.
-- Domínio: estrutura e integridade do banco.
-- Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
-- Os comentários ao lado de cada instrução são mantidos; as notas adicionais
-- explicam as decisões e os efeitos do bloco sem modificar o comportamento.

-- DevIN | FASE 1: Bases de privacidade, consentimento e auditoria
-- MySQL 8.0.16+ / InnoDB / utf8mb4 | Nenhuma tabela existente é alterada.
-- Criar SOMENTE em desenvolvimento, apos backup e revisao.
-- Identidade: pessoa.id_pessoa / empresa.id_empresa / administrador.id_administrador.
-- Referencias polimorficas intencionais: sem FK para tabelas de contas existentes.
SET NAMES utf8mb4; -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.

-- Detalhe DevIN: Mudança estrutural; aplicar uma vez após backup e conferir restrições, chaves e índices.
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

-- Detalhe DevIN: Mudança estrutural; aplicar uma vez após backup e conferir restrições, chaves e índices.
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

-- Detalhe DevIN: Mudança estrutural; aplicar uma vez após backup e conferir restrições, chaves e índices.
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

-- Detalhe DevIN: Mudança estrutural; aplicar uma vez após backup e conferir restrições, chaves e índices.
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

-- Detalhe DevIN: Mudança estrutural; aplicar uma vez após backup e conferir restrições, chaves e índices.
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

-- Detalhe DevIN: Mudança estrutural; aplicar uma vez após backup e conferir restrições, chaves e índices.
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

-- Detalhe DevIN: Mudança estrutural; aplicar uma vez após backup e conferir restrições, chaves e índices.
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

-- Detalhe DevIN: Mudança estrutural; aplicar uma vez após backup e conferir restrições, chaves e índices.
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

-- Detalhe DevIN: Mudança estrutural; aplicar uma vez após backup e conferir restrições, chaves e índices.
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


-- ================================================================
-- 02 — Histórico e atividades JARVIS
-- Fonte: database/migrations/jarvis_admin.sql
-- ================================================================
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


-- ================================================================
-- 03 — Ações supervisionadas JARVIS
-- Fonte: database/migrations/jarvis_admin_controlled_actions.sql
-- ================================================================
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


-- ================================================================
-- 04 — Auditoria de moderação JARVIS
-- Fonte: database/migrations/2026_11_16_004_jarvis_moderation.sql
-- ================================================================
-- REVISÃO DETALHADA DEVIN — database/migrations/2026_11_16_004_jarvis_moderation.sql
-- Finalidade: 2026 11 16 004 jarvis moderation.
-- Domínio: assistência administrativa.
-- Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
-- Os comentários ao lado de cada instrução são mantidos; as notas adicionais
-- explicam as decisões e os efeitos do bloco sem modificar o comportamento.

-- Auditoria para exclusões supervisionadas pelo JarvisDevIN.
-- Executar somente após backup no banco correto e antes de liberar exclusões.
-- Detalhe DevIN: Mudança estrutural; aplicar uma vez após backup e conferir restrições, chaves e índices.
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


-- ================================================================
-- 05 — Tickets e respostas do Suporte
-- Fonte: database/migrations/2026_11_17_005_support_center.sql
-- ================================================================
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
    -- Bloqueia tentativas SMTP concorrentes; expira após 2 minutos caso o PHP caia.
    email_claimed_at DATETIME NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    CONSTRAINT fk_support_reply_ticket FOREIGN KEY (ticket_id) -- Explicação DevIN: Declara relação de integridade referencial entre tabelas.
        REFERENCES support_tickets(id) ON DELETE CASCADE, -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
    INDEX idx_support_reply_ticket (ticket_id, created_at), -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
    INDEX idx_support_delivery (chat_sent_at, email_sent_at) -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; -- Explicação DevIN: Define mecanismo de armazenamento ou conjunto de caracteres da tabela.


-- ================================================================
-- 06 — Confirmações de contratação e retiradas
-- Fonte: database/migrations/2026_11_18_006_employment_confirmation.sql
-- ================================================================
-- DevIN: contratação confirmada é diferente de candidatura aprovada.
-- Não reescreve candidatura.status, preservando histórico e compatibilidade.
-- A aplicação de migrations exige backup e conferência em homologação.
CREATE TABLE IF NOT EXISTS devin_employment_confirmations ( -- Explicação DevIN: Cria tabela persistente no banco de dados, se aplicável com proteção de existência.
    application_id INT NOT NULL PRIMARY KEY, -- Explicação DevIN: Declara a chave primária para identificar registros sem duplicação.
    employer_confirmed_at DATETIME NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    candidate_confirmed_at DATETIME NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    candidate_closed_others TINYINT(1) NOT NULL DEFAULT 0, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    INDEX idx_devin_hiring_employer (employer_confirmed_at), -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
    INDEX idx_devin_hiring_candidate (candidate_confirmed_at) -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; -- Explicação DevIN: Define mecanismo de armazenamento ou conjunto de caracteres da tabela.

-- Um registro por candidatura retirada, mantendo o conteúdo original sem DELETE.
-- Motivo e vínculo facilitam revisão e eventual reativação de candidaturas.
CREATE TABLE IF NOT EXISTS devin_application_withdrawals ( -- Explicação DevIN: Cria tabela persistente no banco de dados, se aplicável com proteção de existência.
    application_id INT NOT NULL PRIMARY KEY, -- Explicação DevIN: Declara a chave primária para identificar registros sem duplicação.
    employment_application_id INT NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    candidate_id INT NOT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    reason VARCHAR(36) NOT NULL DEFAULT 'contratacao_confirmada', -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    withdrawn_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
    INDEX idx_devin_withdrawal_owner (candidate_id, withdrawn_at) -- Explicação DevIN: Cria índice para auxiliar buscas ou ordenação no banco.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; -- Explicação DevIN: Define mecanismo de armazenamento ou conjunto de caracteres da tabela.


-- ================================================================
-- 07 — Mensagens persistentes no MySQL
-- Fonte: database/migrations/2026_11_19_007_chat_mysql_state.sql
-- ================================================================
-- DevIN: armazenamento durável de mensagens e conversas (etapa de transição).
-- Aplicar SOMENTE após backup do banco e cópia dos arquivos JSON legados.
-- A ativação requer DEVIN_CHAT_MYSQL_ENABLED=1; por padrão o código mantém o armazenamento antigo.
-- Esta estrutura usa dois documentos JSON em InnoDB para preservar a API atual; não é a futura modelagem relacional por mensagem.
CREATE TABLE IF NOT EXISTS devin_chat_state ( -- Explicação DevIN: Cria tabela persistente no banco de dados, se aplicável com proteção de existência.
    resource VARCHAR(16) NOT NULL PRIMARY KEY, -- Explicação DevIN: Declara a chave primária para identificar registros sem duplicação.
    payload LONGTEXT NOT NULL, -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; -- Explicação DevIN: Define mecanismo de armazenamento ou conjunto de caracteres da tabela.

