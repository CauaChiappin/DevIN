# DevIN — Fase 1 LGPD: fundação de evidências e auditoria

> **Estado:** código proposto na branch `feature/lgpd-phase1-foundation`, baseado na `main`. Sem merge e sem qualquer migração aplicada à hospedagem. Esta fase é uma base técnica, **não um certificado de conformidade LGPD**.

## Inventário verificado da main
- PHP + MySQL mysqli; contas em `pessoa.id_pessoa`, `empresa.id_empresa` e `administrador.id_administrador` (sem tabela única `users`).
- Cadastro `php/cadastro_pessoa.php` e `php/cadastro_empresa.php`: CSRF, hash seguro da senha e limite por IP; **ainda não existe checkbox versionado de aceite**.
- Login `php/login.php`, recuperação `php/processar.php`, exclusão de perfil em `php/controllers/ProfileController.php`.
- Informações também aparecem em `curriculo`, `candidatura`, `vagas`, `reports_vagas`, `reports_perfis`, uploads e arquivos JSON temporários em `php/controllers/ChatStorage.php`.
- A política existente é uma versão-base, **não** uma política jurídico-operacional final.
- `feature/jarvis-admin` acrescenta histórico MySQL do ADM; não foi incorporada à main nem alterada por este PR.

**Não há dump completo, acesso ao MySQL da hospedagem, backup nem validação real de produção nesta análise.** A CI usa banco MySQL 8.0 descartável com tabelas de contas mínimas somente para testar serviços.

## 1. Migration aditiva

Arquivo: `database/migrations/2026_11_09_001_lgpd_foundation.sql`.

Cria nove tabelas:
- `legal_document_versions`: versão e SHA256 do texto realmente publicado, com ativação controlada;
- `user_legal_acceptances`: aceite explícito de Termos + comprovação de ciência do aviso, versões, data, IP HMAC, User-Agent;
- `user_consent_preferences` e `user_consent_events`: estado e histórico de concessão/revogação de finalidades opcionais;
- `audit_logs`: eventos de segurança e direitos, com IP em HMAC, sem payload;
- `app_access_logs`: IP recuperável protegido e data de acesso, separado da auditoria — retenção mínima de seis meses **quando o Marco Civil art. 15 for aplicável**;
- `privacy_requests`: protocolos de titulares sem descrições pessoais em texto livre;
- `privacy_erasure_receipts`: evidência de encerramento, **não realiza a exclusão**;
- `privacy_security_incidents`: acompanhamento de incidentes com retenção base de cinco anos.

Não cria FKs polimórficas com as três contas existentes, não altera tabelas originais e não inventa consentimentos antigos. Os IDs podem ficar órfãos após exclusão: desenhar a anonimização na Fase 3 antes do lançamento real.

### Aplicação no ambiente de DESENVOLVIMENTO

**Pré-requisitos:** confirmar MySQL 8.0.16+ (em MariaDB, fazer revisão de compatibilidade), testar permissões, tirar backup e testar restauração. Confirmar especialmente o esquema real de `pessoa`, `empresa`, `administrador`, `curriculo`, denúncias e outros relacionamentos.

```bash
# SOMENTE no banco de desenvolvimento: NÃO use o banco de produção.
mysql -h 127.0.0.1 -u devin_dev -p devin_dev < database/migrations/2026_11_09_001_lgpd_foundation.sql
```

Publicar o **texto real e juridicamente aprovado** antes de usar `LegalDocumentService::publishApproved()`. O hash do texto/HTML exato publicado é passado pelo operador autorizado. Nunca publicar versão fictícia em produção. A CI usa `2026-11-v1` **somente no banco descartável de teste**.

## 2. Serviços PHP

Diretório `php/controllers/Privacy/`:
- `PrivacyContext.php`: valida tipos de conta, origens IP e HMAC-SHA256 usando chave independente.
- `LegalDocumentService.php`: lê versão publicada e publica exclusivamente versão/hashes já aprovados com transação.
- `ConsentService.php`: aceita Termos só mediante `termsChecked === true` **e versões de Termos/Privacidade idênticas às exibidas no formulário**; se documentos mudarem durante o cadastro a operação é recusada e a página deve ser recarregada. Altera preferências opcionais e grava cada evento na mesma transação. O caller do cadastro deve iniciar a transação antes de criar a conta e confirmar o aceite.
- `AuditService.php`: eventos em lista permitida, retenção operacional própria, integração opcional (`tryRecord`) nos fluxos antigos.
- `AccessLogService.php`: coleta `REMOTE_ADDR` validado, caminho sem query string, seis meses quando aplicável; **a captura global ainda não está ligada**.
- `PrivacyRequestService.php`: cria e consulta protocolos por conta; gestão restrita a ADM com transições.
- `ErasureReceiptService.php`: registra recibos de ações externas *já concluídas*; não apaga dados.
- `SecurityIncidentService.php`: registra incidentes e tipos de dados, requer ADM.
- `RetentionService.php`: oferece contagem e expurgo em lotes sob confirmação explícita. **Nenhuma tarefa cron foi criada nem executada**.

### Instrumentação não intrusiva

`php/login.php`, `php/cadastro_pessoa.php`, `php/cadastro_empresa.php`, `php/processar.php`, `php/pessoa.php` e `php/empresa.php` chamam `AuditService::tryRecord` para login/falha, registro, alteração de senha e fluxo atual de exclusão.

**A auditoria permanece desligada por padrão**, para manter a compatibilidade enquanto as nove tabelas não estiverem no banco de desenvolvimento. Ao ativar, verificar monitoramento: `tryRecord` registra falhas no log técnico sem divulgar dados pessoais, mas não bloqueia o login.

**NÃO ALTERADO NESTA FASE:** lógica atual de apagar conta, validade dos JWTs emitidos, checkboxes de cadastro, páginas jurídicas, exportação de dados, endpoint público de solicitações, política de cookies, retenção executada automaticamente, central de privacidade e atuação do JARVIS Admin. Essas mudanças exigem as fases 2, 3 e 4 e testes adicionais.

## 3. Variáveis de ambiente novas

```dotenv
# Use chave aleatória de 32+ caracteres, separada de JWT/SMTP e não versionada.
DEVIN_LGPD_IP_HASH_KEY=
# 0 mantém instrumentação inativa até rodar migration e revisar as políticas.
DEVIN_LGPD_AUDIT_ENABLED=0
# Padrão operacional inicial em dias; deve ser definido por política de retenção aprovada.
DEVIN_LGPD_AUDIT_RETENTION_DAYS=180
```

Gerar chave usando `php -r 'echo bin2hex(random_bytes(32)), PHP_EOL;'` e armazenar somente no servidor/secret manager, sem colá-la em GitHub/chat. Não reutilizar JWT.

### Procedimento de ativação
1. Validar o esquema real, testar backup e executar a migration em banco de desenvolvimento.
2. Validar docs legais e prazos com responsável jurídico/encarregado.
3. Configurar `DEVIN_LGPD_IP_HASH_KEY` forte; manter `AUDIT_ENABLED=0` durante smoke tests.
4. Executar testes e observar os logs de erros.
5. Só depois ativar `DEVIN_LGPD_AUDIT_ENABLED=1` **no ambiente de desenvolvimento**; testar login, falhas de login, cadastro, reset, exclusão *em contas de teste*.
6. Revisar políticas de retenção, chaves, controle de acesso e planos de incidente antes de produção.

## 4. CI e testes

Workflow: `.github/workflows/lgpd-phase1.yml`.
- Sintaxe PHP de serviços e arquivos modificados.
- `tests/privacy_phase1_unit.php`: HMAC, antifalsificação de IP, User-Agent, finalidade e sanitização de URL.
- MySQL 8.0 descartável aplica a migration e valida as 9 tabelas.
- `tests/privacy_phase1_integration.php`: versões imutáveis, aceite explícito, idempotência, revogação, isolamento por conta, auditoria, log sem query string, protocolo, recibo, incidente e retenção.

O teste de integração **se recusa** a rodar fora de um banco nomeado `devin_lgpd_test`, com credenciais fornecidas apenas pelo workflow. Nenhuma conta real é criada ou alterada.

## 5. Pendências bloqueantes de produção

- Registro de Termos e transparência nas telas reais de cadastro (Fase 2); até lá o serviço de aceite está disponível, **mas não é invocado automaticamente**. Não atribuir aceite retroativo.
- Definir base legal de cada operação; separar consentimento opcional de execução contratual/obrigação legal.
- Inventário completo dos dados pessoais, CPFs, currículos, fotos, mensagens temporárias, denúncias (que copiam nomes), logs, cópias Git e backups.
- Processo de exclusão coordenada, avaliação de retenções, revogação de sessões e tokens ativos, confirmação de identidade e exportação (Fase 3).
- Logging de acesso completo com privacidade, limitação de acesso e expurgo legal, revisão de cabeçalhos e monitoramento (Fase 4).
- Política ANPD para incidentes, avaliação de fornecedores externos de IA (Gemini/JARVIS), comunicação e gestão de possíveis dados de adolescentes.
- Verificar que nenhum arquivo com dados pessoais reais esteja no histórico Git.
- Revisão jurídica: LGPD 13.709/2018, Marco Civil 12.965/2014 e regulamentação ANPD aplicável; a mera existência de tabelas não garante conformidade.

## Resultado da validação automatizada

- Workflow GitHub Actions `LGPD Fase 1 — privacy services and MySQL`, execução de PR no commit `1bd42f8`: **aprovado**. 
- Migração das 9 tabelas executada em **MySQL 8.0 descartável de CI**, sem tocar a base do DevIN.
- Serviços PHP e pontos de integração sem erros de sintaxe; testes puros e integração MySQL aprovados, incluindo rejeição de versão obsoleta de documentos.
- Testes de segurança ponta a ponta, navegador, retenção real e integridade do banco do projeto continuam pendentes antes de qualquer merge.
