<?php

declare(strict_types=1);

if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/plain; charset=utf-8');
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/MailerHelper.php';

try {
    $conn = getDatabaseConnection();

    /*
     * Busca pessoas cadastradas há mais de 1 hora,
     * que ainda não possuem currículo e que ainda
     * não receberam o lembrete.
     */
    $sql = "
        SELECT
            p.id_pessoa,
            p.nome,
            p.email,
            p.created_at
        FROM pessoa p
        LEFT JOIN curriculo c
            ON p.id_pessoa = c.id_pessoa
        WHERE c.id_curriculo IS NULL
          AND p.lembrete_enviado = 0
          AND p.created_at <= NOW() - INTERVAL 1 HOUR
    ";

    $result = $conn->query($sql);

    /*
     * Prepara o UPDATE uma única vez fora do loop.
     */
    $stmtUpdate = $conn->prepare(
        "UPDATE pessoa
         SET lembrete_enviado = 1
         WHERE id_pessoa = ?"
    );

    if ($stmtUpdate === false) {
        echo "[ERRO] Não foi possível preparar a atualização: " . $conn->error . PHP_EOL;
        $conn->close();
        exit(1);
    }

    $encontrouPendentes = false;

    while ($pessoa = $result->fetch_assoc()) {
        $encontrouPendentes = true;

        $idPessoa = (int) $pessoa['id_pessoa'];
        $nome     = (string) $pessoa['nome'];
        $email    = (string) $pessoa['email'];

        // Envia o lembrete por e-mail
        $enviado = MailerHelper::enviarLembreteCurriculoPendente($email, $nome);

        if ($enviado) {
            $stmtUpdate->bind_param('i', $idPessoa);

            if ($stmtUpdate->execute()) {
                echo "[OK] Lembrete enviado para: {$email}" . PHP_EOL;
            } else {
                echo "[ERRO] O e-mail foi enviado, mas não foi possível marcar no banco para: {$email}" . PHP_EOL;
            }
        } else {
            echo "[ERRO] Falha ao enviar e-mail de lembrete para: {$email}" . PHP_EOL;
        }
    }

    $stmtUpdate->close();
    $result->free();
    $conn->close();

    if (!$encontrouPendentes) {
        echo "[INFO] Nenhum candidato pendente de lembrete no momento." . PHP_EOL;
    }

} catch (Throwable $e) {
    echo "[ERRO CRÍTICO] Execução do Cron interrompida: " . $e->getMessage() . PHP_EOL;
    exit(1);
}