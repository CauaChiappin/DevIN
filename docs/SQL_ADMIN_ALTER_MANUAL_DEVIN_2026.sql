-- DevIN | ALTERACOES MANUAIS ADMINISTRADOR - USO SOMENTE APOS CONFERIR AS COLUNAS.
-- NAO executar automaticamente no banco atual. Cada ALTER adiciona colunas a administrador.
-- Se uma coluna ja existir, o SQL pode falhar com "Duplicate column name".
-- Execute primeiro: SHOW COLUMNS FROM administrador;
-- Aplicar apenas quando as colunas respectivas estiverem AUSENTES e backup confirmado.
-- NAO define senhas ou niveis administrativos automaticamente.
-- Requer a tabela original administrador, que nao esta nas migrations complementares.

-- 1) Token 2 (executar APENAS se token2_hash, token2_version e token2_configured_at nao existem):
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

-- 2) Niveis ADM (executar APENAS se nivel_permissao nao existe):
-- REVISÃO DETALHADA DEVIN — database/migrations/2026_11_16_003_admin_roles.sql
-- Finalidade: 2026 11 16 003 admin roles.
-- Domínio: estrutura e integridade do banco.
-- Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
-- Os comentários ao lado de cada instrução são mantidos; as notas adicionais
-- explicam as decisões e os efeitos do bloco sem modificar o comportamento.

-- Níveis administrativos: implantação aditiva, não atribui privilégios automaticamente.
-- A coluna começa em 'consulta', o nível com menor privilégio.
-- Aplicar no phpMyAdmin somente após backup e conferir os quatro ADMs.
-- Detalhe DevIN: Mudança estrutural; aplicar uma vez após backup e conferir restrições, chaves e índices.
ALTER TABLE administrador -- Explicação DevIN: Inicia mudança de estrutura de tabela existente.
  ADD COLUMN nivel_permissao ENUM('consulta', 'moderacao', 'gestao') NOT NULL DEFAULT 'consulta'; -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.

-- Conferência antes de ativar DEVIN_ADMIN_RBAC_ENABLED=1:
-- SELECT id_administrador, nome, nivel_permissao, token2_configured_at FROM administrador;
-- O operador autorizado deve atribuir os níveis individualmente, após aprovação.

-- A atribuicao de cargos e a configuracao de Token 2 exigem procedimento manual separado.
