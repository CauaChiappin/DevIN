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
