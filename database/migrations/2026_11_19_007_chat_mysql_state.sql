-- DevIN: armazenamento durável de mensagens e conversas (etapa de transição).
-- Aplicar SOMENTE após backup do banco e cópia dos arquivos JSON legados.
-- A ativação requer DEVIN_CHAT_MYSQL_ENABLED=1; por padrão o código mantém o armazenamento antigo.
-- Esta estrutura usa dois documentos JSON em InnoDB para preservar a API atual; não é a futura modelagem relacional por mensagem.
CREATE TABLE IF NOT EXISTS devin_chat_state ( -- Explicação DevIN: Cria tabela de estado transacional para a migração do chat.
    resource VARCHAR(16) NOT NULL PRIMARY KEY, -- Explicação DevIN: Diferencia mensagens e conversas sem misturar seus históricos.
    payload LONGTEXT NOT NULL, -- Explicação DevIN: Armazena documento JSON com dados de conversa existentes.
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP -- Explicação DevIN: Registra horário da última alteração.
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; -- Explicação DevIN: Usa transações para impedir perda em acessos simultâneos.
