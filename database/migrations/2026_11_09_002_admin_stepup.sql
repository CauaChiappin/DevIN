-- REVISÃO DETALHADA DEVIN — database/migrations/2026_11_09_002_admin_stepup.sql
-- Finalidade: 2026 11 09 002 admin stepup.
-- Domínio: estrutura e integridade do banco.
-- Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
-- Os comentários ao lado de cada instrução são mantidos; as notas adicionais
-- explicam as decisões e os efeitos do bloco sem modificar o comportamento.

-- Token 2 persistente no cadastro de cada administrador.
-- A migration roda UMA VEZ, por operador no banco de desenvolvimento, não pela página.
-- Não cria senhas e não modifica senhas existentes.
-- Detalhe DevIN: Mudança estrutural; aplicar uma vez após backup e conferir restrições, chaves e índices.
ALTER TABLE administrador -- Explicação DevIN: Inicia mudança de estrutura de tabela existente.
  ADD COLUMN token2_hash VARCHAR(255) NULL DEFAULT NULL, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
  ADD COLUMN token2_version BIGINT UNSIGNED NOT NULL DEFAULT 0, -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
  ADD COLUMN token2_configured_at DATETIME(6) NULL DEFAULT NULL; -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
