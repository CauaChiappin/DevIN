<?php
declare(strict_types=1);

require_once __DIR__ . '/PrivacyContext.php';

/** Registro de incidentes: retenção de pelo menos cinco anos, com acesso restrito a ADMs. */
final class SecurityIncidentService
{
    public static function register(
        mysqli $db, int $adminId, string $discoveredUtc, string $summary,
        array $categories, ?int $affectedCount = null
    ): string {
        PrivacyContext::validateAccount('adm', $adminId);
        if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/D', $discoveredUtc)
            || strlen(trim($summary)) < 15 || strlen($summary) > 2000
            || ($affectedCount !== null && $affectedCount < 0)) {
            throw new InvalidArgumentException('Dados de incidente inválidos.');
        }
        $allowed = ['identification','contact','credentials','curriculum','applications','messages','technical','other'];
        if (count($categories) > 8 || array_diff($categories, $allowed) !== []) {
            throw new InvalidArgumentException('Categoria de dados inválida.');
        }
        PrivacyContext::requireAccount($db, 'adm', $adminId);
        // Texto de resumo pode conter dado pessoal: validar e revisar antes da inserção.
        $reference = bin2hex(random_bytes(16));
        $json = json_encode(array_values(array_unique($categories)), JSON_THROW_ON_ERROR);
        $stmt = $db->prepare(
            'INSERT INTO privacy_security_incidents '
            . '(reference_code,discovered_at,risk_level,affected_data_categories_json,affected_subjects_count,'
            . 'incident_summary,recorded_at,retain_until) '
            . "VALUES (?,?,'under_review',?,?,?,UTC_TIMESTAMP(6),DATE_ADD(UTC_TIMESTAMP(6),INTERVAL 5 YEAR))"
        );
        $stmt->bind_param('sssis', $reference, $discoveredUtc, $json, $affectedCount, $summary);
        $stmt->execute();
        $stmt->close();
        return $reference;
    }
}
