<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/controllers/JobSchema.php
 * Finalidade: adaptar consultas ao esquema real de vagas.
 * Domínio: processamento no servidor.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


// Detalhe DevIN: Método ensureJobCompensationSchema: encapsula uma responsabilidade reutilizável do módulo no contexto de adaptar consultas ao esquema real de vagas. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function ensureJobCompensationSchema(mysqli $conn): void // Explicação DevIN: Declara função/método ensureJobCompensationSchema, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    $textColumns = [ // Explicação DevIN: Atribui um valor à variável PHP $textColumns.
        'titulo' => 50, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'descricao' => 1000, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
    // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
    $stmt = $conn->prepare('
        SELECT COLUMN_NAME, CHARACTER_MAXIMUM_LENGTH, IS_NULLABLE, COLUMN_DEFAULT,
               CHARACTER_SET_NAME, COLLATION_NAME
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = "vagas"
          AND COLUMN_NAME IN ("titulo", "descricao")
    '); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
    $existingTextColumns = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
    $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função ensureJobCompensationSchema.

    foreach ($existingTextColumns as $column) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
        $name = (string) $column['COLUMN_NAME']; // Explicação DevIN: Atribui um valor à variável PHP $name.
        $maxLength = $textColumns[$name] ?? null; // Explicação DevIN: Atribui um valor à variável PHP $maxLength.
        $currentLength = $column['CHARACTER_MAXIMUM_LENGTH']; // Explicação DevIN: Atribui um valor à variável PHP $currentLength.
        if ($maxLength === null || $currentLength === null || (int) $currentLength >= $maxLength) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            continue; // Explicação DevIN: Controla a continuação ou saída de laço/seleção.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $definition = 'VARCHAR(' . $maxLength . ')'; // Explicação DevIN: Atribui um valor à variável PHP $definition.
        if (!empty($column['CHARACTER_SET_NAME'])) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $definition .= ' CHARACTER SET ' . $column['CHARACTER_SET_NAME']; // Explicação DevIN: Continua expressão ou bloco pertencente a função ensureJobCompensationSchema.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!empty($column['COLLATION_NAME'])) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $definition .= ' COLLATE ' . $column['COLLATION_NAME']; // Explicação DevIN: Continua expressão ou bloco pertencente a função ensureJobCompensationSchema.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $definition .= $column['IS_NULLABLE'] === 'YES' ? ' NULL' : ' NOT NULL'; // Explicação DevIN: Continua expressão ou bloco pertencente a função ensureJobCompensationSchema.
        if ($column['COLUMN_DEFAULT'] !== null) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $default = $conn->real_escape_string((string) $column['COLUMN_DEFAULT']); // Explicação DevIN: Atribui um valor à variável PHP $default.
            $definition .= " DEFAULT '" . $default . "'"; // Explicação DevIN: Continua expressão ou bloco pertencente a função ensureJobCompensationSchema.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $conn->query('ALTER TABLE vagas MODIFY COLUMN `' . $name . '` ' . $definition); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    $columns = [ // Explicação DevIN: Atribui um valor à variável PHP $columns.
        'salario_mensal' => 'ALTER TABLE vagas ADD COLUMN salario_mensal DECIMAL(10,2) NOT NULL DEFAULT 0.00', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'limite_aprovados' => 'ALTER TABLE vagas ADD COLUMN limite_aprovados INT UNSIGNED NOT NULL DEFAULT 0', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'criterio_encerramento' => "ALTER TABLE vagas ADD COLUMN criterio_encerramento VARCHAR(16) NOT NULL DEFAULT 'nenhum'", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'data_encerramento' => 'ALTER TABLE vagas ADD COLUMN data_encerramento DATETIME NULL', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'encerrada_em' => 'ALTER TABLE vagas ADD COLUMN encerrada_em DATETIME NULL', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.

    // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
    $stmt = $conn->prepare('
        SELECT COLUMN_NAME
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = "vagas"
          AND COLUMN_NAME IN ("salario_mensal", "limite_aprovados", "criterio_encerramento", "data_encerramento", "encerrada_em")
    '); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
    $existingColumns = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'COLUMN_NAME'); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
    $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função ensureJobCompensationSchema.

    foreach ($columns as $column => $alterSql) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
        if (!in_array($column, $existingColumns, true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                $conn->query($alterSql); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            } catch (mysqli_sql_exception $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
                if ($exception->getCode() !== 1060) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    throw $exception; // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    atualizarVagasEncerradas($conn); // Explicação DevIN: Continua expressão ou bloco pertencente a função ensureJobCompensationSchema.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Detalhe DevIN: Método atualizarVagasEncerradas: encapsula uma responsabilidade reutilizável do módulo no contexto de adaptar consultas ao esquema real de vagas. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function atualizarVagasEncerradas(mysqli $conn): void // Explicação DevIN: Declara função/método atualizarVagasEncerradas, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
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
    "); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Detalhe DevIN: Método validarSalarioMensal: encapsula uma responsabilidade reutilizável do módulo no contexto de adaptar consultas ao esquema real de vagas. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function validarSalarioMensal(string $valor): string // Explicação DevIN: Declara função/método validarSalarioMensal, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if (!preg_match('/^\d{1,8}(?:\.\d{1,2})?$/D', $valor) || (float) $valor > 99999999.99) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new InvalidArgumentException('Informe um salário mensal válido, com até duas casas decimais.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    return number_format((float) $valor, 2, '.', ''); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Detalhe DevIN: Método validarLimiteAprovados: encapsula uma responsabilidade reutilizável do módulo no contexto de adaptar consultas ao esquema real de vagas. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function validarLimiteAprovados(string $valor, bool $permitirSemLimite = false): int // Explicação DevIN: Declara função/método validarLimiteAprovados, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    $limite = filter_var($valor, FILTER_VALIDATE_INT); // Explicação DevIN: Atribui um valor à variável PHP $limite.
    $menorLimite = $permitirSemLimite ? 0 : 1; // Explicação DevIN: Atribui um valor à variável PHP $menorLimite.
    if ($limite === false || $limite < $menorLimite || $limite > 2147483647) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new InvalidArgumentException('Informe um limite válido de vagas.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    return $limite; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Detalhe DevIN: Método validarConfiguracaoEncerramento: encapsula uma responsabilidade reutilizável do módulo no contexto de adaptar consultas ao esquema real de vagas. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function validarConfiguracaoEncerramento(string $criterio, string $dataEncerramento, int $limiteAprovados): ?string // Explicação DevIN: Declara função/método validarConfiguracaoEncerramento, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if (!in_array($criterio, ['limite', 'prazo', 'qualquer', 'nenhum'], true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new InvalidArgumentException('Selecione uma situação válida para encerrar a vaga.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    if (in_array($criterio, ['limite', 'qualquer'], true) && $limiteAprovados < 1) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new InvalidArgumentException('Informe um limite de candidatos aprovados para usar esse critério.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    if (!in_array($criterio, ['prazo', 'qualquer'], true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        return null; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    $data = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $dataEncerramento); // Explicação DevIN: Atribui um valor à variável PHP $data.
    $erros = DateTimeImmutable::getLastErrors(); // Explicação DevIN: Atribui um valor à variável PHP $erros.
    if ( // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $data === false // Explicação DevIN: Atribui um valor à variável PHP $data.
        || ($erros !== false && ($erros['warning_count'] > 0 || $erros['error_count'] > 0)) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        || $data->format('Y-m-d\TH:i') !== $dataEncerramento // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    ) { // Explicação DevIN: Continua expressão ou bloco pertencente a função validarConfiguracaoEncerramento.
        throw new InvalidArgumentException('Informe uma data e hora válidas para encerrar a vaga.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    if ($data <= new DateTimeImmutable()) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new InvalidArgumentException('A data e hora de encerramento devem estar no futuro.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    return $data->format('Y-m-d H:i:s'); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
