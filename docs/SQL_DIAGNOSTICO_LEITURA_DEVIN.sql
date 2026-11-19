-- DevIN: somente leitura; NAO altera contas ou registros.
SELECT DATABASE() AS banco_em_uso, VERSION() AS versao_servidor;
SHOW TABLES;
SHOW COLUMNS FROM administrador;
SELECT table_name
FROM information_schema.tables
WHERE table_schema = DATABASE()
  AND table_name IN (
      'pessoa','empresa','administrador','vagas','candidatura','curriculo',
      'devin_chat_state','devin_employment_confirmations','devin_application_withdrawals',
      'support_tickets','support_replies','jarvis_conversations','jarvis_messages',
      'jarvis_tasks','jarvis_task_audit','jarvis_action_events','jarvis_platform_outbox',
      'jarvis_moderation_audit','audit_logs','privacy_requests'
  )
ORDER BY table_name;
