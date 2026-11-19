-- DevIN: armazenamento durável de mensagens e conversas (etapa de transição).
-- Aplicar SOMENTE após backup do banco e cópia dos arquivos JSON legados.
-- A ativação requer DEVIN_CHAT_MYSQL_ENABLED=1; por padrão o código mantém o armazenamento antigo.
-- Esta estrutura usa dois documentos JSON em InnoDB para preservar a API atual; não é a futura modelagem relacional por mensagem.
CREATE TABLE IF NOT EXISTS devin_chat_state ( -- Explicação DevIN: Cria tabela persistente no banco de dados, se aplicável com proteção de existência.
    resource VARCHAR(16) NOT NULL PRIMARY KEY, -- Explicação DevIN: Declara a chave primária para identificar registros sem duplicação.
    payload LONGTEXT NOT NULL, -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP -- Explicação DevIN: Especifica coluna, tipo, valor padrão ou restrição da tabela SQL.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; -- Explicação DevIN: Define mecanismo de armazenamento ou conjunto de caracteres da tabela.
