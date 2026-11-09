<?php
declare(strict_types=1);

require_once __DIR__ . '/PrivacyContext.php';

/**
 * Registra recibo DEPOIS da execução real (fase 3).
 * Nunca é um mecanismo para apagar perfis, arquivos ou histórico.
 */
final class ErasureReceiptService
{
    private const OUTCOMES = ['deleted','anonymized','partially_retained'];

    /** @param list<string> $retainedCategories */
    public static function record(
        mysqli $db, int $adminId, int $requestId, string $outcome,
        array $retainedCategories = [], ?string $basisCode = null
    ): string {
        PrivacyContext::validateAccount('adm', $adminId);
        if ($requestId < 1 || !in_array($outcome, self::OUTCOMES, true)) {
            throw new InvalidArgumentException('Resultado de eliminação inválido.');
        }
        if ($outcome === 'partially_retained' && (!$retainedCategories || !$basisCode)) {
            throw new InvalidArgumentException('Retenção parcial exige categoria e fundamento.');
        }
        if ($basisCode !== null && preg_match('/^[a-z0-9_:-]{1,80}$/D', $basisCode) !== 1) {
            throw new InvalidArgumentException('Fundamento inválido.');
        }
        $allowed = ['audit','security','legal_hold','tax','complaints','contracts','backups'];
        if (count($retainedCategories) > count($allowed)
            || array_diff($retainedCategories, $allowed) !== []) {
            throw new InvalidArgumentException('Categorias de retenção inválidas.');
        }
        $db->begin_transaction();
        try {
            PrivacyContext::requireAccount($db, 'adm', $adminId, true);
            $stmt = $db->prepare(
                'SELECT request_type,status FROM privacy_requests WHERE id = ? LIMIT 1 FOR UPDATE'
            );
            $stmt->bind_param('i', $requestId);
            $stmt->execute();
            $request = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($request === null || !in_array($request['request_type'], ['erasure','anonymization'], true)
                || $request['status'] !== 'completed') {
                throw new DomainException('Recibo permitido apenas após tratamento efetivamente concluído.');
            }
            $ref = bin2hex(random_bytes(16));
            $categories = $retainedCategories ? json_encode(array_values(array_unique($retainedCategories)), JSON_THROW_ON_ERROR) : null;
            $stmt = $db->prepare(
                'INSERT INTO privacy_erasure_receipts '
                . '(request_id,receipt_reference,outcome,retained_categories_json,legal_retention_basis,processed_at) '
                . 'VALUES (?,?,?,?,?,UTC_TIMESTAMP(6))'
            );
            $stmt->bind_param('issss', $requestId, $ref, $outcome, $categories, $basisCode);
            $stmt->execute();
            $stmt->close();
            $db->commit();
            return $ref;
        } catch (Throwable $exception) {
            $db->rollback();
            throw $exception;
        }
    }
}
