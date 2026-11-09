-- Camada de verificação adicional para ADMs do DevIN.
-- Aplicar APENAS em desenvolvimento, após backup e conferência do schema.
-- Uma credencial individual por ADM; nenhum valor secreto em texto puro.
-- Dependência: tabela existente administrador(id_administrador).
CREATE TABLE IF NOT EXISTS admin_security_credentials (
    admin_id INT NOT NULL,
    token2_hash VARCHAR(255) NOT NULL,
    credential_version BIGINT UNSIGNED NOT NULL DEFAULT 1,
    assigned_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    rotated_at DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    PRIMARY KEY (admin_id),
    CONSTRAINT fk_admin_security_account
        FOREIGN KEY (admin_id) REFERENCES administrador(id_administrador)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
