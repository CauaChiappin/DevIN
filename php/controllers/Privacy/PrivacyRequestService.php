<?php
declare(strict_types=1);

require_once __DIR__ . '/PrivacyContext.php';

/** Fase 1: protocolo de solicitação e acompanhamento; endpoints do titular na Fase 3. */
final class PrivacyRequestService
{
    private const TYPES = [
        'access','portability','rectification','erasure','anonymization',
        'objection','consent_withdrawal','other'
    ];

    /** Cria para conta autenticada após validação da sessão pelo controlador. */
    public static function create(mysqli $db, string $accountType, int $accountId, string $requestType): string
    {
        PrivacyContext::validateAccount($accountType, $accountId);
        if (!in_array($requestType, self::TYPES, true)) {
            throw new InvalidArgumentException('Direito solicitado inválido.');
        }
        PrivacyContext::requireAccount($db, $accountType, $accountId);
        $reference = bin2hex(random_bytes(16));
        $stmt = $db->prepare(
            'INSERT INTO privacy_requests '
            . '(public_reference,account_type,account_id,request_type,status,requested_at) '
            . "VALUES (?,?,?,?,'received',UTC_TIMESTAMP(6))"
        );
        $stmt->bind_param('ssis', $reference, $accountType, $accountId, $requestType);
        $stmt->execute();
        $stmt->close();
        return $reference;
    }

    /** Uma pessoa/empresa nunca obtém solicitações de outras contas por ID. */
    public static function listOwn(mysqli $db, string $accountType, int $accountId): array
    {
        PrivacyContext::validateAccount($accountType, $accountId);
        $stmt = $db->prepare(
            'SELECT public_reference,request_type,status,requested_at,resolved_at '
            . 'FROM privacy_requests WHERE account_type = ? AND account_id = ? '
            . 'ORDER BY id DESC LIMIT 50'
        );
        $stmt->bind_param('si', $accountType, $accountId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    /**
     * Revisão exclusivamente por admin autenticado; transições irreversíveis
     * exigem justificativa por CÓDIGO controlado, sem texto pessoal no log.
     * A marca 'completed' atesta conclusão do processo externo, NÃO executa eliminação.
     */
    public static function updateStatus(
        mysqli $db, int $adminId, int $requestId, string $next, ?string $basisCode = null
    ): void {
        PrivacyContext::validateAccount('adm', $adminId);
        if ($requestId < 1) throw new InvalidArgumentException('Solicitação inválida.');
        $allowed = [
            'received' => ['verifying_identity'],
            'verifying_identity' => ['under_review'],
            'under_review' => ['completed','denied'],
        ];
        if ($basisCode !== null && preg_match('/^[a-z0-9_:-]{1,80}$/D', $basisCode) !== 1) {
            throw new InvalidArgumentException('Código de justificativa inválido.');
        }
        if (in_array($next, ['completed','denied'], true) && !$basisCode) {
            throw new InvalidArgumentException('Justificativa codificada obrigatória.');
        }
        $db->begin_transaction();
        try {
            PrivacyContext::requireAccount($db, 'adm', $adminId, true);
            $stmt = $db->prepare('SELECT status FROM privacy_requests WHERE id = ? LIMIT 1 FOR UPDATE');
            $stmt->bind_param('i', $requestId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($row === null || !in_array($next, $allowed[$row['status']] ?? [], true)) {
                throw new DomainException('Transição de solicitação não autorizada.');
            }
            $stmt = $db->prepare(
                'UPDATE privacy_requests SET status = ?, '
                . "identity_verified_at = IF(? = 'under_review',UTC_TIMESTAMP(6),identity_verified_at), "
                . "resolved_at = IF(? IN ('completed','denied'),UTC_TIMESTAMP(6),resolved_at), "
                . 'decision_basis = ? WHERE id = ?'
            );
            $stmt->bind_param('ssssi', $next, $next, $next, $basisCode, $requestId);
            $stmt->execute();
            $stmt->close();
            $db->commit();
        } catch (Throwable $exception) {
            $db->rollback();
            throw $exception;
        }
    }
}
