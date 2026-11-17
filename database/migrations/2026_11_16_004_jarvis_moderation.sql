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
