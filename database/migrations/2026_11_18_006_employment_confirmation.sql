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
