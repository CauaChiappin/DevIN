-- Fase 1 LGPD: ativação individual e de uso único para o Token 2 escolhido pelo ADM.
-- Aplicar somente DEPOIS de 2026_11_09_002_admin_stepup.sql; testar em desenvolvimento.
-- Nenhum segredo em texto puro é armazenado. Não altera contas nem tokens existentes.
CREATE TABLE IF NOT EXISTS admin_security_enrollments (
    admin_id INT NOT NULL,
    activation_hash VARCHAR(255) NOT NULL,
    issued_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    expires_at DATETIME(6) NOT NULL,
    PRIMARY KEY (admin_id),
    CONSTRAINT fk_admin_token2_enrollment_account
        FOREIGN KEY (admin_id) REFERENCES administrador(id_administrador)
        ON DELETE CASCADE,
    KEY idx_admin_enrollment_expiry (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
