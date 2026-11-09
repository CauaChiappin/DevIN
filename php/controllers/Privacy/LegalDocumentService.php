<?php
declare(strict_types=1);

require_once __DIR__ . '/PrivacyContext.php';

/**
 * Versões aprovadas devem ser publicadas em processo jurídico/editorial.
 * Esta classe NUNCA cria documentos legais ou inventa aceites.
 */
final class LegalDocumentService
{
    private const TYPES = ['terms', 'privacy', 'cookies'];

    /** @return array{version_code:string,content_sha256:string} */
    public static function active(mysqli $db, string $type): array
    {
        if (!in_array($type, self::TYPES, true)) {
            throw new InvalidArgumentException('Tipo de documento inválido.');
        }
        $stmt = $db->prepare(
            'SELECT version_code, HEX(content_sha256) AS content_sha256 '
            . 'FROM legal_document_versions WHERE document_type = ? '
            . 'AND is_active = 1 AND published_at <= UTC_TIMESTAMP(6) LIMIT 2'
        );
        $stmt->bind_param('s', $type);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        if (count($rows) !== 1) {
            // Falha fechada quando não há documento aprovado ou dois foram ativados.
            throw new RuntimeException('Documento legal ativo não configurado de modo único.');
        }
        return $rows[0];
    }

    /** Requer dados pré-aprovados; operação transacional de publicação manual. */
    public static function publishApproved(mysqli $db, string $type, string $version, string $sha256Hex): void
    {
        if (!in_array($type, self::TYPES, true)
            || preg_match('/^[A-Za-z0-9._-]{1,40}$/D', $version) !== 1
            || preg_match('/^[a-fA-F0-9]{64}$/D', $sha256Hex) !== 1) {
            throw new InvalidArgumentException('Versão ou hash do documento inválidos.');
        }
        $db->begin_transaction();
        try {
            $stmt = $db->prepare(
                'SELECT LOWER(HEX(content_sha256)) AS sha FROM legal_document_versions '
                . 'WHERE document_type = ? AND version_code = ? FOR UPDATE'
            );
            $stmt->bind_param('ss', $type, $version);
            $stmt->execute();
            $existing = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($existing !== null && !hash_equals($existing['sha'], strtolower($sha256Hex))) {
                throw new DomainException('Não é permitido alterar o conteúdo de versão legal publicada.');
            }
            $stmt = $db->prepare('UPDATE legal_document_versions SET is_active = 0 WHERE document_type = ?');
            $stmt->bind_param('s', $type);
            $stmt->execute();
            $stmt->close();

            $stmt = $db->prepare(
                'INSERT INTO legal_document_versions (document_type,version_code,content_sha256,published_at,is_active) '
                . 'VALUES (?, ?, UNHEX(?), UTC_TIMESTAMP(6), 1) '
                . 'ON DUPLICATE KEY UPDATE is_active = 1'
            );
            $stmt->bind_param('sss', $type, $version, $sha256Hex);
            $stmt->execute();
            $stmt->close();
            $db->commit();
        } catch (Throwable $exception) {
            $db->rollback();
            throw $exception;
        }
    }
}
