<?php
declare(strict_types=1);

require_once __DIR__ . '/PrivacyContext.php';
require_once __DIR__ . '/LegalDocumentService.php';

/**
 * Todos os métodos esperam identidades derivadas da sessão validada ou do
 * cadastro recém-criado. Não aceitar account_id enviado pelo cliente.
 */
final class ConsentService
{
    /**
     * Insere o aceite explícito em conexão já transacionada pelo cadastro.
     * Não inicia/fecha transação: o caller deve salvar a conta e o aceite
     * atomicamente. Não deve ser chamado sem checkbox verdadeiro.
     */
    public static function recordLegalAcceptance(
        mysqli $db,
        string $accountType,
        int $accountId,
        bool $termsChecked,
        string $source = 'registration'
    ): int {
        PrivacyContext::validateAccount($accountType, $accountId);
        if (!$termsChecked || !in_array($source, ['registration','renewal'], true)) {
            throw new InvalidArgumentException('É necessário aceite expresso dos Termos e origem válida.');
        }
        PrivacyContext::requireAccount($db, $accountType, $accountId);
        $terms = LegalDocumentService::active($db, 'terms');
        $privacy = LegalDocumentService::active($db, 'privacy');
        $ipHash = PrivacyContext::ipHash(PrivacyContext::clientIp());
        $ua = PrivacyContext::userAgent();

        $stmt = $db->prepare(
            'INSERT INTO user_legal_acceptances '
            . '(account_type,account_id,terms_version,privacy_notice_version,accepted_at,ip_hash,user_agent,capture_source) '
            . 'VALUES (?,?,?,?,UTC_TIMESTAMP(6),?,?,?)'
        );
        $termsVersion = $terms['version_code'];
        $privacyVersion = $privacy['version_code'];
        $stmt->bind_param('sisssss', $accountType, $accountId, $termsVersion, $privacyVersion, $ipHash, $ua, $source);
        $stmt->execute();
        $id = (int) $db->insert_id;
        $stmt->close();
        return $id;
    }

    /** Ausência de preferência significa opt-out; preferências não herdam do cadastro. */
    public static function isGranted(mysqli $db, string $accountType, int $accountId, string $purpose): bool
    {
        PrivacyContext::validateAccount($accountType, $accountId);
        PrivacyContext::validatePurpose($purpose);
        $stmt = $db->prepare(
            'SELECT is_granted FROM user_consent_preferences '
            . 'WHERE account_type = ? AND account_id = ? AND purpose = ? LIMIT 1'
        );
        $stmt->bind_param('sis', $accountType, $accountId, $purpose);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $row !== null && (int) $row['is_granted'] === 1;
    }

    /**
     * Mudança explícita da preferência; atualiza estado e histórico na mesma
     * transação, protegendo contra perda de atualizações concorrentes.
     * Retorna false quando não havia mudança efetiva.
     */
    public static function setPreference(
        mysqli $db,
        string $accountType,
        int $accountId,
        string $purpose,
        bool $grant
    ): bool {
        PrivacyContext::validateAccount($accountType, $accountId);
        PrivacyContext::validatePurpose($purpose);
        $privacy = LegalDocumentService::active($db, 'privacy');
        $ipHash = PrivacyContext::ipHash(PrivacyContext::clientIp());
        $ua = PrivacyContext::userAgent();
        $db->begin_transaction();
        try {
            PrivacyContext::requireAccount($db, $accountType, $accountId, true);
            $stmt = $db->prepare(
                'SELECT is_granted FROM user_consent_preferences '
                . 'WHERE account_type = ? AND account_id = ? AND purpose = ? LIMIT 1 FOR UPDATE'
            );
            $stmt->bind_param('sis', $accountType, $accountId, $purpose);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            // Desativado por padrão: não grava um evento 'revoked' inexistente.
            if (($row === null && !$grant)
                || ($row !== null && ((int) $row['is_granted'] === 1) === $grant)) {
                $db->commit();
                return false;
            }
            $value = $grant ? 1 : 0;
            $policyVersion = $privacy['version_code'];
            $stmt = $db->prepare(
                'INSERT INTO user_consent_preferences '
                . '(account_type,account_id,purpose,is_granted,policy_version,updated_at) '
                . 'VALUES (?,?,?,?,?,UTC_TIMESTAMP(6)) ON DUPLICATE KEY UPDATE '
                . 'is_granted = VALUES(is_granted), policy_version = VALUES(policy_version), updated_at = UTC_TIMESTAMP(6)'
            );
            $stmt->bind_param('sisis', $accountType, $accountId, $purpose, $value, $policyVersion);
            $stmt->execute();
            $stmt->close();

            $event = $grant ? 'granted' : 'revoked';
            $stmt = $db->prepare(
                'INSERT INTO user_consent_events '
                . '(account_type,account_id,purpose,event_type,policy_version,happened_at,ip_hash,user_agent) '
                . 'VALUES (?,?,?,?,?,UTC_TIMESTAMP(6),?,?)'
            );
            $stmt->bind_param('sisssss', $accountType, $accountId, $purpose, $event, $policyVersion, $ipHash, $ua);
            $stmt->execute();
            $stmt->close();
            $db->commit();
            return true;
        } catch (Throwable $exception) {
            $db->rollback();
            throw $exception;
        }
    }
}
