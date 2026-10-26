<?php

declare(strict_types=1);

function ensureJobCompensationSchema(mysqli $conn): void
{
    $textColumns = [
        'titulo' => 50,
        'descricao' => 1000,
    ];
    $stmt = $conn->prepare('
        SELECT COLUMN_NAME, CHARACTER_MAXIMUM_LENGTH, IS_NULLABLE, COLUMN_DEFAULT,
               CHARACTER_SET_NAME, COLLATION_NAME
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = "vagas"
          AND COLUMN_NAME IN ("titulo", "descricao")
    ');
    $stmt->execute();
    $existingTextColumns = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    foreach ($existingTextColumns as $column) {
        $name = (string) $column['COLUMN_NAME'];
        $maxLength = $textColumns[$name] ?? null;
        $currentLength = $column['CHARACTER_MAXIMUM_LENGTH'];
        if ($maxLength === null || $currentLength === null || (int) $currentLength >= $maxLength) {
            continue;
        }

        $definition = 'VARCHAR(' . $maxLength . ')';
        if (!empty($column['CHARACTER_SET_NAME'])) {
            $definition .= ' CHARACTER SET ' . $column['CHARACTER_SET_NAME'];
        }
        if (!empty($column['COLLATION_NAME'])) {
            $definition .= ' COLLATE ' . $column['COLLATION_NAME'];
        }
        $definition .= $column['IS_NULLABLE'] === 'YES' ? ' NULL' : ' NOT NULL';
        if ($column['COLUMN_DEFAULT'] !== null) {
            $default = $conn->real_escape_string((string) $column['COLUMN_DEFAULT']);
            $definition .= " DEFAULT '" . $default . "'";
        }

        $conn->query('ALTER TABLE vagas MODIFY COLUMN `' . $name . '` ' . $definition);
    }

    $columns = [
        'salario_mensal' => 'ALTER TABLE vagas ADD COLUMN salario_mensal DECIMAL(10,2) NOT NULL DEFAULT 0.00',
        'limite_aprovados' => 'ALTER TABLE vagas ADD COLUMN limite_aprovados INT UNSIGNED NOT NULL DEFAULT 0',
        'criterio_encerramento' => "ALTER TABLE vagas ADD COLUMN criterio_encerramento VARCHAR(16) NOT NULL DEFAULT 'nenhum'",
        'data_encerramento' => 'ALTER TABLE vagas ADD COLUMN data_encerramento DATETIME NULL',
        'encerrada_em' => 'ALTER TABLE vagas ADD COLUMN encerrada_em DATETIME NULL',
    ];

    $stmt = $conn->prepare('
        SELECT COLUMN_NAME
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = "vagas"
          AND COLUMN_NAME IN ("salario_mensal", "limite_aprovados", "criterio_encerramento", "data_encerramento", "encerrada_em")
    ');
    $stmt->execute();
    $existingColumns = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'COLUMN_NAME');
    $stmt->close();

    foreach ($columns as $column => $alterSql) {
        if (!in_array($column, $existingColumns, true)) {
            try {
                $conn->query($alterSql);
            } catch (mysqli_sql_exception $exception) {
                if ($exception->getCode() !== 1060) {
                    throw $exception;
                }
            }
        }
    }

    atualizarVagasEncerradas($conn);
}

function atualizarVagasEncerradas(mysqli $conn): void
{
    $conn->query("
        UPDATE vagas v
        SET encerrada_em = CURRENT_TIMESTAMP
        WHERE v.encerrada_em IS NULL
          AND (
              (
                  v.criterio_encerramento = 'limite'
                  AND v.limite_aprovados > 0
                  AND (
                      SELECT COUNT(*)
                      FROM candidatura c
                      WHERE c.id_vaga = v.id_vaga AND c.status = 'aprovado'
                  ) >= v.limite_aprovados
              )
              OR (
                  v.criterio_encerramento = 'prazo'
                  AND v.data_encerramento IS NOT NULL
                  AND v.data_encerramento <= CURRENT_TIMESTAMP
              )
              OR (
                  v.criterio_encerramento = 'qualquer'
                  AND (
                      (
                          v.limite_aprovados > 0
                          AND (
                              SELECT COUNT(*)
                              FROM candidatura c
                              WHERE c.id_vaga = v.id_vaga AND c.status = 'aprovado'
                          ) >= v.limite_aprovados
                      )
                      OR (
                          v.data_encerramento IS NOT NULL
                          AND v.data_encerramento <= CURRENT_TIMESTAMP
                      )
                  )
              )
          )
    ");
}

function validarSalarioMensal(string $valor): string
{
    if (!preg_match('/^\d{1,8}(?:\.\d{1,2})?$/D', $valor) || (float) $valor > 99999999.99) {
        throw new InvalidArgumentException('Informe um salário mensal válido, com até duas casas decimais.');
    }

    return number_format((float) $valor, 2, '.', '');
}

function validarLimiteAprovados(string $valor, bool $permitirSemLimite = false): int
{
    $limite = filter_var($valor, FILTER_VALIDATE_INT);
    $menorLimite = $permitirSemLimite ? 0 : 1;
    if ($limite === false || $limite < $menorLimite || $limite > 2147483647) {
        throw new InvalidArgumentException('Informe um limite válido de candidatos aprovados.');
    }

    return $limite;
}

function validarConfiguracaoEncerramento(string $criterio, string $dataEncerramento, int $limiteAprovados): ?string
{
    if (!in_array($criterio, ['limite', 'prazo', 'qualquer', 'nenhum'], true)) {
        throw new InvalidArgumentException('Selecione uma situação válida para encerrar a vaga.');
    }

    if (in_array($criterio, ['limite', 'qualquer'], true) && $limiteAprovados < 1) {
        throw new InvalidArgumentException('Informe um limite de candidatos aprovados para usar esse critério.');
    }

    if (!in_array($criterio, ['prazo', 'qualquer'], true)) {
        return null;
    }

    $data = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $dataEncerramento);
    $erros = DateTimeImmutable::getLastErrors();
    if (
        $data === false
        || ($erros !== false && ($erros['warning_count'] > 0 || $erros['error_count'] > 0))
        || $data->format('Y-m-d\TH:i') !== $dataEncerramento
    ) {
        throw new InvalidArgumentException('Informe uma data e hora válidas para encerrar a vaga.');
    }

    if ($data <= new DateTimeImmutable()) {
        throw new InvalidArgumentException('A data e hora de encerramento devem estar no futuro.');
    }

    return $data->format('Y-m-d H:i:s');
}
