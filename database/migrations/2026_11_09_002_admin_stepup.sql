-- Token 2 persistente no cadastro de cada administrador.
-- A migration roda UMA VEZ, por operador no banco de desenvolvimento, não pela página.
-- Não cria senhas e não modifica senhas existentes.
ALTER TABLE administrador -- Explicação DevIN: Inicia mudança de estrutura de tabela existente.
  ADD COLUMN token2_hash VARCHAR(255) NULL DEFAULT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
  ADD COLUMN token2_version BIGINT UNSIGNED NOT NULL DEFAULT 0, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
  ADD COLUMN token2_configured_at DATETIME(6) NULL DEFAULT NULL; -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
