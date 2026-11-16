-- Níveis administrativos: implantação aditiva, não atribui privilégios automaticamente.
-- A coluna começa em 'consulta', o nível com menor privilégio.
-- Aplicar no phpMyAdmin somente após backup e conferir os quatro ADMs.
ALTER TABLE administrador
  ADD COLUMN nivel_permissao ENUM('consulta', 'moderacao', 'gestao') NOT NULL DEFAULT 'consulta';

-- Conferência antes de ativar DEVIN_ADMIN_RBAC_ENABLED=1:
-- SELECT id_administrador, nome, nivel_permissao, token2_configured_at FROM administrador;
-- O operador autorizado deve atribuir os níveis individualmente, após aprovação.
