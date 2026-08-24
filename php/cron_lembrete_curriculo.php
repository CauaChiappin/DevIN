<?php

require_once __DIR__ . '/php/config/database.php';
require_once __DIR__ . '/MailerHelper.php';

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

if ($result === false) {
    echo "[ERRO] Falha ao consultar pessoas pendentes: " . $conn->error . PHP_EOL;
    $conn->close();
    exit(1);
}

/*
 * Prepara o UPDATE uma única vez.
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
    $nome = $pessoa['nome'];
    $email = $pessoa['email'];

    /*
     * Envia o lembrete para a pessoa.
     */
    $enviado = MailerHelper::enviarLembreteCurriculoPendente(
        $email,
        $nome
    );

    if ($enviado) {

        /*
         * Marca o lembrete como enviado somente
         * depois que o e-mail foi enviado com sucesso.
         */
        $stmtUpdate->bind_param('i', $idPessoa);

        if ($stmtUpdate->execute()) {

            echo "[OK] Lembrete enviado para: {$email}" . PHP_EOL;

        } else {

            echo "[ERRO] O e-mail foi enviado, mas não foi possível marcar o lembrete como enviado para: {$email}" . PHP_EOL;
        }

    } else {

        echo "[ERRO] Falha ao enviar lembrete para: {$email}" . PHP_EOL;
    }
}

$stmtUpdate->close();

$result->free();

$conn->close();

if (!$encontrouPendentes) {
    echo "[INFO] Nenhum candidato pendente de lembrete no momento." . PHP_EOL;
}