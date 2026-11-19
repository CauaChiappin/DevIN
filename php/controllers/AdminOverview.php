<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * Consultas agregadas de administração, sem alterar registros nem revelar dados pessoais.
 * Recursos ainda não instalados retornam null em vez de falsamente informar zero.
 */
function devinAdminOverview(mysqli $db): array // Explicação DevIN: Declara função/método devinAdminOverview, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    $queries = [ // Explicação DevIN: Atribui um valor à variável PHP $queries.
        'empresas' => 'SELECT COUNT(*) FROM empresa', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'pessoas' => 'SELECT COUNT(*) FROM pessoa', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'vagas' => 'SELECT COUNT(*) FROM vagas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'candidaturas' => 'SELECT COUNT(*) FROM candidatura', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'contratacoes' => 'SELECT COUNT(*) FROM devin_employment_confirmations WHERE candidate_confirmed_at IS NOT NULL', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'denuncias_vagas' => "SELECT COUNT(*) FROM reports_vagas WHERE status = 'pendente'", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'denuncias_perfis' => "SELECT COUNT(*) FROM reports_perfis WHERE status = 'pendente'", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'suporte_aberto' => "SELECT COUNT(*) FROM support_tickets WHERE status = 'aberto'", // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
    $result = []; // Explicação DevIN: Atribui um valor à variável PHP $result.
    foreach ($queries as $key => $sql) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            $response = $db->query($sql); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            $result[$key] = (int) $response->fetch_row()[0]; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            $response->free(); // Explicação DevIN: Continua expressão ou bloco pertencente a função devinAdminOverview.
        } catch (mysqli_sql_exception $error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            $result[$key] = null; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            error_log('DevIN ADM: métrica ' . $key . ' indisponível (MySQL ' . $error->getCode() . ').'); // Explicação DevIN: Continua expressão ou bloco pertencente a função devinAdminOverview.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    $result['denuncias_pendentes'] = $result['denuncias_vagas'] !== null && $result['denuncias_perfis'] !== null // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        ? $result['denuncias_vagas'] + $result['denuncias_perfis'] : null; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    $result['top_empresas'] = []; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        $ranking = $db->query('SELECT e.nome, COUNT(v.id_vaga) AS vagas FROM empresa e LEFT JOIN vagas v ON v.id_empresa = e.id_empresa GROUP BY e.id_empresa, e.nome ORDER BY vagas DESC, e.nome ASC LIMIT 8'); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $result['top_empresas'] = $ranking->fetch_all(MYSQLI_ASSOC); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $ranking->free(); // Explicação DevIN: Continua expressão ou bloco pertencente a função devinAdminOverview.
    } catch (mysqli_sql_exception $error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        error_log('DevIN ADM: ranking de empresas indisponível (MySQL ' . $error->getCode() . ').'); // Explicação DevIN: Continua expressão ou bloco pertencente a função devinAdminOverview.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    return $result; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
