<?php

declare(strict_types=1);

function ensureJobCompensationSchema(mysqli $conn): void
{
    $columns = [
        'salario_mensal' => 'ALTER TABLE vagas ADD COLUMN salario_mensal DECIMAL(10,2) NOT NULL DEFAULT 0.00',
        'limite_aprovados' => 'ALTER TABLE vagas ADD COLUMN limite_aprovados INT UNSIGNED NOT NULL DEFAULT 0',
    ];

    $stmt = $conn->prepare('
        SELECT COLUMN_NAME
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = "vagas"
          AND COLUMN_NAME IN ("salario_mensal", "limite_aprovados")
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
