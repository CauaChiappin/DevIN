<?php
declare(strict_types=1);

/**
 * Rotina CLI controlada futura. NÃO há endpoint web nem agendamento automático
 * nesta fase. Logs sob retenção legal são preservados.
 */
final class RetentionService
{
    /** @return array{access_logs:int,audit_logs:int} */
    public static function expiredCounts(mysqli $db): array
    {
        $access = $db->query(
            'SELECT COUNT(*) FROM app_access_logs WHERE delete_after < UTC_TIMESTAMP(6) '
            . 'AND (legal_hold_until IS NULL OR legal_hold_until < UTC_TIMESTAMP(6))'
        );
        $audit = $db->query(
            'SELECT COUNT(*) FROM audit_logs WHERE retention_until < UTC_TIMESTAMP(6)'
        );
        return [
            'access_logs' => (int) $access->fetch_row()[0],
            'audit_logs' => (int) $audit->fetch_row()[0],
        ];
    }

    /**
     * Apaga no máximo 500 linhas por tabela, dentro da mesma transação.
     * Uso SOMENTE após aprovação de política de retenção e legal holds.
     */
    public static function pruneBatch(mysqli $db, bool $confirmed = false): array
    {
        if (!$confirmed) {
            throw new DomainException('Exige confirmação explícita de política e operador.');
        }
        $db->begin_transaction();
        try {
            $db->query(
                'DELETE FROM app_access_logs WHERE delete_after < UTC_TIMESTAMP(6) '
                . 'AND (legal_hold_until IS NULL OR legal_hold_until < UTC_TIMESTAMP(6)) '
                . 'ORDER BY id LIMIT 500'
            );
            $access = $db->affected_rows;
            $db->query(
                'DELETE FROM audit_logs WHERE retention_until < UTC_TIMESTAMP(6) '
                . 'ORDER BY id LIMIT 500'
            );
            $audit = $db->affected_rows;
            $db->commit();
            return ['access_logs' => $access, 'audit_logs' => $audit];
        } catch (Throwable $exception) {
            $db->rollback();
            throw $exception;
        }
    }
}
