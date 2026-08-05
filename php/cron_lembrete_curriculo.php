<?php
// cron_lembrete_curriculo.php

require_once __DIR__ . '/php/config/database.php';
require_once __DIR__ . '/MailerHelper.php';

$conn = getDatabaseConnection();

// Busca candidatos cadastrados há mais de 1 hora sem currículo e sem lembrete enviado
$sql = "
    SELECT p.id_pessoa, p.nome, p.email, p.created_at
    FROM pessoa p
    LEFT JOIN curriculo c ON p.id_pessoa = c.id_pessoa
    WHERE c.id_curriculo IS NULL
      AND p.lembrete_enviado = 0
      AND p.created_at <= NOW() - INTERVAL 1 HOUR
";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    while ($pessoa = $result->fetch_assoc()) {
        $enviado = MailerHelper::enviarLembreteCurriculoPendente($pessoa['email'], $pessoa['nome']);

        if ($enviado) {
            // Marca o lembrete como enviado para evitar múltiplos envios
            $stmtUpdate = $conn->prepare("UPDATE pessoa SET lembrete_enviado = 1 WHERE id_pessoa = ?");
            $stmtUpdate->bind_param('i', $pessoa['id_pessoa']);
            $stmtUpdate->execute();
            $stmtUpdate->close();

            echo "[OK] Lembrete enviado para: {$pessoa['email']}\n";
        } else {
            echo "[ERRO] Falha ao enviar lembrete para: {$pessoa['email']}\n";
        }
    }
} else {
    echo "[INFO] Nenhum candidato pendente de lembrete no momento.\n";
}

$conn->close();