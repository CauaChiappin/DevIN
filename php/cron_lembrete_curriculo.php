<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/cron_lembrete_curriculo.php
 * Finalidade: cron lembrete curriculo.
 * Domínio: processamento no servidor.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


// Este script altera e-mails e dados; só pode ser executado pelo agendador no terminal.
if (PHP_SAPI !== 'cli') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    http_response_code(404); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    exit; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

require_once __DIR__ . '/config/database.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/MailerHelper.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    $conn = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $conn.

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
    "; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.

    $result = $conn->query($sql); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.

    /*
     * Prepara o UPDATE uma única vez fora do loop.
     */
    // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
    $stmtUpdate = $conn->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        "UPDATE pessoa
         SET lembrete_enviado = 1
         WHERE id_pessoa = ?" // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    if ($stmtUpdate === false) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        echo "[ERRO] Não foi possível preparar a atualização: " . $conn->error . PHP_EOL; // Explicação DevIN: Produz saída ou conteúdo da resposta do programa.
        $conn->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        exit(1); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    $encontrouPendentes = false; // Explicação DevIN: Atribui um valor à variável PHP $encontrouPendentes.

    // Marca o lembrete somente depois de enviar, evitando registrar como enviado em falhas SMTP.
    while ($pessoa = $result->fetch_assoc()) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
        $encontrouPendentes = true; // Explicação DevIN: Atribui um valor à variável PHP $encontrouPendentes.

        $idPessoa = (int) $pessoa['id_pessoa']; // Explicação DevIN: Atribui um valor à variável PHP $idPessoa.
        $nome     = (string) $pessoa['nome']; // Explicação DevIN: Atribui um valor à variável PHP $nome.
        $email    = (string) $pessoa['email']; // Explicação DevIN: Atribui um valor à variável PHP $email.

        // Envia o lembrete por e-mail
        $idioma = ProfileLanguagePreference::load($conn, 'pessoa', $idPessoa); // Explicação DevIN: Atribui um valor à variável PHP $idioma.
        $enviado = MailerHelper::enviarLembreteCurriculoPendente($email, $nome, $idioma); // Explicação DevIN: Atribui um valor à variável PHP $enviado.

        if ($enviado) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $stmtUpdate->bind_param('i', $idPessoa); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.

            if ($stmtUpdate->execute()) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                echo "[OK] Lembrete enviado para: {$email}" . PHP_EOL; // Explicação DevIN: Produz saída ou conteúdo da resposta do programa.
            } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
                echo "[ERRO] O e-mail foi enviado, mas não foi possível marcar no banco para: {$email}" . PHP_EOL; // Explicação DevIN: Produz saída ou conteúdo da resposta do programa.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
            echo "[ERRO] Falha ao enviar e-mail de lembrete para: {$email}" . PHP_EOL; // Explicação DevIN: Produz saída ou conteúdo da resposta do programa.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    $stmtUpdate->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    $result->free(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    $conn->close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

    if (!$encontrouPendentes) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        echo "[INFO] Nenhum candidato pendente de lembrete no momento." . PHP_EOL; // Explicação DevIN: Produz saída ou conteúdo da resposta do programa.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

} catch (Throwable $e) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    echo "[ERRO CRÍTICO] Execução do Cron interrompida: " . $e->getMessage() . PHP_EOL; // Explicação DevIN: Produz saída ou conteúdo da resposta do programa.
    exit(1); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
