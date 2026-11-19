-- DevIN: somente leitura; NAO altera contas ou registros.
SELECT DATABASE() AS banco_em_uso, VERSION() AS versao_servidor; -- Explicação DevIN: Seleciona campos ou resultados para uma consulta ao banco.
SHOW TABLES; -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
SHOW COLUMNS FROM administrador; -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
SELECT table_name -- Explicação DevIN: Seleciona campos ou resultados para uma consulta ao banco.
FROM information_schema.tables -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
WHERE table_schema = DATABASE() -- Explicação DevIN: Restringe os registros afetados pela condição SQL.
  AND table_name IN ( -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
      'pessoa','empresa','administrador','vagas','candidatura','curriculo', -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
      'devin_chat_state','devin_employment_confirmations','devin_application_withdrawals', -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
      'support_tickets','support_replies','jarvis_conversations','jarvis_messages', -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
      'jarvis_tasks','jarvis_task_audit','jarvis_action_events','jarvis_platform_outbox', -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
      'jarvis_moderation_audit','audit_logs','privacy_requests' -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
  ) -- Explicação DevIN: Continua a definição, restrição ou comando SQL do bloco atual.
ORDER BY table_name; -- Explicação DevIN: Completa cláusula de consulta ou manipulação SQL.
