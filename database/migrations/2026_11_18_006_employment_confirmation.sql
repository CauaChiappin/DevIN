-- DevIN: contratação confirmada é diferente de candidatura aprovada.
-- Não reescreve candidatura.status, preservando histórico e compatibilidade.
-- A aplicação de migrations exige backup e conferência em homologação.
CREATE TABLE IF NOT EXISTS devin_employment_confirmations (
    application_id INT NOT NULL PRIMARY KEY,
    employer_confirmed_at DATETIME NULL,
    candidate_confirmed_at DATETIME NULL,
    candidate_closed_others TINYINT(1) NOT NULL DEFAULT 0,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_devin_hiring_employer (employer_confirmed_at),
    INDEX idx_devin_hiring_candidate (candidate_confirmed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Um registro por candidatura retirada, mantendo o conteúdo original sem DELETE.
-- Motivo e vínculo facilitam revisão e eventual reativação de candidaturas.
CREATE TABLE IF NOT EXISTS devin_application_withdrawals (
    application_id INT NOT NULL PRIMARY KEY,
    employment_application_id INT NOT NULL,
    candidate_id INT NOT NULL,
    reason VARCHAR(36) NOT NULL DEFAULT 'contratacao_confirmada',
    withdrawn_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_devin_withdrawal_owner (candidate_id, withdrawn_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
