<?php
declare(strict_types=1);

/**
 * Execução EXCLUSIVA por CLI: emite um código de ativação temporário
 * para que cada administrador defina o próprio Token 2 em uma página protegida.
 * Exibe o código UMA vez ao operador; o banco guarda somente hash.
 *
 * php scripts/issue-admin-token2-activation.php <id_administrador>
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/php/config/database.php';

$id = filter_var($argv[1] ?? null, FILTER_VALIDATE_INT);
if (!$id || $id < 1 || count($argv) !== 2) {
    fwrite(STDERR, "Uso: php scripts/issue-admin-token2-activation.php <id_administrador>\n");
    exit(2);
}

try {
    $db = getDatabaseConnection();
    $db->begin_transaction();
    $stmt = $db->prepare('SELECT id_administrador FROM administrador WHERE id_administrador = ? FOR UPDATE');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $exists = $stmt->get_result()->fetch_row() !== null;
    $stmt->close();
    if (!$exists) {
        throw new InvalidArgumentException('Conta administrativa não encontrada.');
    }

    // 24 bytes -> 32 caracteres URL-safe; um código por ADM, com duração curta.
    $activation = rtrim(strtr(base64_encode(random_bytes(24)), '+/', '-_'), '=');
    $algorithm = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
    $hash = password_hash($activation, $algorithm);
    if (!is_string($hash)) {
        throw new RuntimeException('Falha ao proteger o código.');
    }

    $stmt = $db->prepare(
        'INSERT INTO admin_security_enrollments (admin_id,activation_hash,issued_at,expires_at) '
        . 'VALUES (?, ?, UTC_TIMESTAMP(6), DATE_ADD(UTC_TIMESTAMP(6), INTERVAL 30 MINUTE)) '
        . 'ON DUPLICATE KEY UPDATE activation_hash=VALUES(activation_hash), '
        . 'issued_at=UTC_TIMESTAMP(6), expires_at=VALUES(expires_at)'
    );
    $stmt->bind_param('is', $id, $hash);
    $stmt->execute();
    $stmt->close();
    $db->commit();

    fwrite(STDOUT, "ADM ID: {$id}\nCódigo de ativação (uso único, 30 minutos): {$activation}\n");
    fwrite(STDOUT, "Entregue exclusivamente ao ADM por canal seguro; não envie por chat, Git ou logs.\n");
} catch (Throwable $exception) {
    if (isset($db) && $db instanceof mysqli) {
        $db->rollback();
    }
    fwrite(STDERR, "Não foi possível emitir o código. Confira a conta e a migration.\n");
    exit(1);
} finally {
    if (isset($db) && $db instanceof mysqli) {
        $db->close();
    }
}
