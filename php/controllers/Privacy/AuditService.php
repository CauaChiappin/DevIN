<?php
declare(strict_types=1);

require_once __DIR__ . '/PrivacyContext.php';

/**
 * Auditoria de segurança sem payloads de requisição, emails, CPF ou senhas.
 * A retenção de auditoria é uma política OPERACIONAL, não a regra do MCI.
 */
final class AuditService
{
    private const EVENTS = [
        'login_success', 'login_failure', 'registration_success',
        'password_reset', 'account_deletion_requested', 'account_deleted',
        'data_export_requested', 'privacy_request_created',
        'legal_document_accepted', 'consent_changed',
        'admin_stepup_site', 'admin_stepup_ai_stage1', 'admin_stepup_ai_complete',
    ];
    private const OUTCOMES = ['success', 'failure', 'blocked'];

    public static function enabled(): bool
    {
        return PrivacyContext::env('DEVIN_LGPD_AUDIT_ENABLED') === '1';
    }

    public static function record(
        mysqli $db,
        string $event,
        string $outcome,
        ?string $actorType = null,
        ?int $actorId = null,
        ?string $subjectType = null,
        ?int $subjectId = null,
        ?string $requestId = null
    ): int {
        if (!in_array($event, self::EVENTS, true) || !in_array($outcome, self::OUTCOMES, true)) {
            throw new InvalidArgumentException('Evento de auditoria inválido.');
        }
        if ($actorType !== null) PrivacyContext::validateAccount($actorType, $actorId ?? 0);
        elseif ($actorId !== null) throw new InvalidArgumentException('Ator inválido.');
        if ($subjectType !== null) PrivacyContext::validateAccount($subjectType, $subjectId ?? 0);
        elseif ($subjectId !== null) throw new InvalidArgumentException('Titular inválido.');
        if ($requestId !== null && preg_match('/^[a-f0-9-]{36}$/iD', $requestId) !== 1) {
            throw new InvalidArgumentException('Identificador de solicitação inválido.');
        }
        $days = (int) (PrivacyContext::env('DEVIN_LGPD_AUDIT_RETENTION_DAYS') ?: '180');
        if ($days < 30 || $days > 1825) {
            throw new RuntimeException('Prazo de auditoria deve ser definido entre 30 e 1825 dias.');
        }
        $ipHash = PrivacyContext::ipHash(PrivacyContext::clientIp());
        $stmt = $db->prepare(
            'INSERT INTO audit_logs '
            . '(event_type,outcome,actor_type,actor_id,subject_type,subject_id,event_at,ip_hash,request_id,metadata_json,retention_until) '
            . 'VALUES (?,?,?,?,?,?,UTC_TIMESTAMP(6),?,?,NULL,DATE_ADD(UTC_TIMESTAMP(6),INTERVAL ? DAY))'
        );
        $stmt->bind_param(
            'sssisissi',
            $event, $outcome, $actorType, $actorId, $subjectType, $subjectId, $ipHash, $requestId, $days
        );
        $stmt->execute();
        $id = (int) $db->insert_id;
        $stmt->close();
        return $id;
    }

    /**
     * Instrumentação opcional e não intrusiva. Até ativar Fase 1 em deploy,
     * fluxos antigos seguem funcionando. Falha de auditoria nunca revela PII.
     */
    public static function tryRecord(
        string $event,
        string $outcome,
        ?string $actorType = null,
        ?int $actorId = null
    ): void {
        if (!self::enabled()) return;
        try {
            require_once __DIR__ . '/../../config/database.php';
            $db = getDatabaseConnection();
            try {
                self::record($db, $event, $outcome, $actorType, $actorId);
            } finally {
                $db->close();
            }
        } catch (Throwable $exception) {
            // Fail-safe de compatibilidade; monitorar falhas de auditoria ao ativar produção.
            error_log('LGPD audit unavailable: event=' . $event . ' class=' . get_class($exception));
        }
    }
}
