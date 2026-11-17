<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/controllers/Privacy/AuditService.php
 * Finalidade: audit service.
 * Domínio: privacidade e proteção de dados.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


require_once __DIR__ . '/PrivacyContext.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

/**
 * Auditoria de segurança sem payloads de requisição, emails, CPF ou senhas.
 * A retenção de auditoria é uma política OPERACIONAL, não a regra do MCI.
 */
final class AuditService // Explicação DevIN: Declara o tipo AuditService e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    private const EVENTS = [ // Explicação DevIN: Define membro, visibilidade ou método de uma classe.
        'login_success', 'login_failure', 'registration_success', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'password_reset', 'account_deletion_requested', 'account_deleted', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'data_export_requested', 'privacy_request_created', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'legal_document_accepted', 'consent_changed', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'admin_stepup_site', 'admin_stepup_ai_stage1', 'admin_stepup_ai_complete', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'admin_token2_enrolled', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
    private const OUTCOMES = ['success', 'failure', 'blocked']; // Explicação DevIN: Define membro, visibilidade ou método de uma classe.

    // Detalhe DevIN: Método enabled: encapsula uma responsabilidade reutilizável do módulo no contexto de audit service. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function enabled(): bool // Explicação DevIN: Declara função/método enabled, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return PrivacyContext::env('DEVIN_LGPD_AUDIT_ENABLED') === '1'; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Método record: registra evidências para acompanhamento no contexto de audit service. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function record( // Explicação DevIN: Declara função/método record, com seus parâmetros e comportamento.
        mysqli $db, // Explicação DevIN: Continua expressão ou bloco pertencente a função record.
        string $event, // Explicação DevIN: Continua expressão ou bloco pertencente a função record.
        string $outcome, // Explicação DevIN: Continua expressão ou bloco pertencente a função record.
        ?string $actorType = null, // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        ?int $actorId = null, // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        ?string $subjectType = null, // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        ?int $subjectId = null, // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        ?string $requestId = null // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    ): int { // Explicação DevIN: Continua expressão ou bloco pertencente a função record.
        if (!in_array($event, self::EVENTS, true) || !in_array($outcome, self::OUTCOMES, true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Evento de auditoria inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if ($actorType !== null) PrivacyContext::validateAccount($actorType, $actorId ?? 0); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        elseif ($actorId !== null) throw new InvalidArgumentException('Ator inválido.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if ($subjectType !== null) PrivacyContext::validateAccount($subjectType, $subjectId ?? 0); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        elseif ($subjectId !== null) throw new InvalidArgumentException('Titular inválido.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if ($requestId !== null && preg_match('/^[a-f0-9-]{36}$/iD', $requestId) !== 1) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Identificador de solicitação inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $days = (int) (PrivacyContext::env('DEVIN_LGPD_AUDIT_RETENTION_DAYS') ?: '180'); // Explicação DevIN: Atribui um valor à variável PHP $days.
        if ($days < 30 || $days > 1825) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new RuntimeException('Prazo de auditoria deve ser definido entre 30 e 1825 dias.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $ipHash = PrivacyContext::ipHash(PrivacyContext::clientIp()); // Explicação DevIN: Atribui um valor à variável PHP $ipHash.
        // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
        $stmt = $db->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            'INSERT INTO audit_logs ' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            . '(event_type,outcome,actor_type,actor_id,subject_type,subject_id,event_at,ip_hash,request_id,metadata_json,retention_until) ' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            . 'VALUES (?,?,?,?,?,?,UTC_TIMESTAMP(6),?,?,NULL,DATE_ADD(UTC_TIMESTAMP(6),INTERVAL ? DAY))' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $stmt->bind_param( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            'sssisissi', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            $event, $outcome, $actorType, $actorId, $subjectType, $subjectId, $ipHash, $requestId, $days // Explicação DevIN: Continua expressão ou bloco pertencente a função record.
        ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $id = (int) $db->insert_id; // Explicação DevIN: Atribui um valor à variável PHP $id.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função record.
        return $id; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /**
     * Instrumentação opcional e não intrusiva. Até ativar Fase 1 em deploy,
     * fluxos antigos seguem funcionando. Falha de auditoria nunca revela PII.
     */
    // Detalhe DevIN: Método tryRecord: encapsula uma responsabilidade reutilizável do módulo no contexto de audit service. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function tryRecord( // Explicação DevIN: Declara função/método tryRecord, com seus parâmetros e comportamento.
        string $event, // Explicação DevIN: Continua expressão ou bloco pertencente a função tryRecord.
        string $outcome, // Explicação DevIN: Continua expressão ou bloco pertencente a função tryRecord.
        ?string $actorType = null, // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        ?int $actorId = null // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    ): void { // Explicação DevIN: Continua expressão ou bloco pertencente a função tryRecord.
        if (!self::enabled()) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            require_once __DIR__ . '/../../config/database.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
            $db = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $db.
            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                self::record($db, $event, $outcome, $actorType, $actorId); // Explicação DevIN: Continua expressão ou bloco pertencente a função tryRecord.
            } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
                $db->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função tryRecord.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            // Fail-safe de compatibilidade; monitorar falhas de auditoria ao ativar produção.
            error_log('LGPD audit unavailable: event=' . $event . ' class=' . get_class($exception)); // Explicação DevIN: Continua expressão ou bloco pertencente a função tryRecord.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
