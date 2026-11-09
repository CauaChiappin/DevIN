<?php
declare(strict_types=1);

/**
 * SOMENTE CLI, fora do webroot: gera segredo aleatório individual e guarda
 * SOMENTE seu hash Argon2id/bcrypt. O segredo aparece uma única vez no terminal.
 * Uso: php scripts/provision-admin-token2.php 123
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/php/config/database.php';

$id = filter_var($argv[1] ?? null, FILTER_VALIDATE_INT);
if (!$id || $id < 1 || count($argv) !== 2) {
    fwrite(STDERR, "Uso: php scripts/provision-admin-token2.php <id_administrador>\n");
    exit(2);
}

try {
    $db = getDatabaseConnection();
    $db->begin_transaction();
    $stmt = $db->prepare('SELECT id_administrador FROM administrador WHERE id_administrador = ? FOR UPDATE');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $found = $stmt->get_result()->fetch_row() !== null;
    $stmt->close();
    if (!$found) {
        throw new InvalidArgumentException('Conta administrativa não encontrada.');
    }
    // 32 bytes aleatórios, 43 caracteres URL-safe, exclusivos por ADM.
    $raw = random_bytes(32);
    $secret = rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    $algorithm = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
    $hash = password_hash($secret, $algorithm);
    if (!is_string($hash)) throw new RuntimeException('Não foi possível proteger a credencial.');
    $stmt = $db->prepare(
        'INSERT INTO admin_security_credentials '
        . '(admin_id,token2_hash,credential_version,assigned_at,rotated_at) '
        . 'VALUES (?,?,1,UTC_TIMESTAMP(6),UTC_TIMESTAMP(6)) '
        . 'ON DUPLICATE KEY UPDATE token2_hash=VALUES(token2_hash), '
        . 'credential_version=credential_version+1, rotated_at=UTC_TIMESTAMP(6)'
    );
    $stmt->bind_param('is', $id, $hash);
    $stmt->execute();
    $stmt->close();
    $db->commit();
    fwrite(STDOUT, "ADM ID: {$id}\nCredencial gerada (exibir uma única vez): {$secret}\nArmazene e entregue por canal seguro. Rotação invalida permissões anteriores.\n");
} catch (Throwable $exception) {
    if (isset($db) && $db instanceof mysqli) $db->rollback();
    fwrite(STDERR, 'Falha ao provisionar credencial. Confirme a migration, o ADM e o acesso ao MySQL.' . PHP_EOL);
    exit(1);
} finally {
    if (isset($db) && $db instanceof mysqli) $db->close();
}
