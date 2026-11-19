<?php // Explicação DevIN: Inicializa métricas administrativas somente de leitura.
declare(strict_types=1); // Explicação DevIN: Habilita tipagem estrita.
/**
 * Consultas agregadas de administração, sem alterar registros nem revelar dados pessoais.
 * Recursos ainda não instalados retornam null em vez de falsamente informar zero.
 */
function devinAdminOverview(mysqli $db): array // Explicação DevIN: Consolida métricas para o painel ADM.
{ // Explicação DevIN: Abre o escopo do resumo administrativo.
    $queries = [ // Explicação DevIN: Define uma lista fechada de consultas autorizadas.
        'empresas' => 'SELECT COUNT(*) FROM empresa', // Explicação DevIN: Total de contas empresariais.
        'pessoas' => 'SELECT COUNT(*) FROM pessoa', // Explicação DevIN: Total de pessoas cadastradas.
        'vagas' => 'SELECT COUNT(*) FROM vagas', // Explicação DevIN: Total de vagas publicadas.
        'candidaturas' => 'SELECT COUNT(*) FROM candidatura', // Explicação DevIN: Total de inscrições registradas.
        'contratacoes' => 'SELECT COUNT(*) FROM devin_employment_confirmations WHERE candidate_confirmed_at IS NOT NULL', // Explicação DevIN: Considera contratação concluída após confirmação da pessoa.
        'denuncias_vagas' => "SELECT COUNT(*) FROM reports_vagas WHERE status = 'pendente'", // Explicação DevIN: Conta apenas denúncias de vagas aguardando análise.
        'denuncias_perfis' => "SELECT COUNT(*) FROM reports_perfis WHERE status = 'pendente'", // Explicação DevIN: Conta apenas denúncias de perfis aguardando análise.
        'suporte_aberto' => "SELECT COUNT(*) FROM support_tickets WHERE status = 'aberto'", // Explicação DevIN: Conta tickets ainda abertos.
    ]; // Explicação DevIN: Encerra lista fechada de consultas.
    $result = []; // Explicação DevIN: Armazena os valores acessíveis ao painel.
    foreach ($queries as $key => $sql) { // Explicação DevIN: Executa cada consulta agregada sem dados do visitante.
        try { // Explicação DevIN: Preserva o painel se uma migration opcional não tiver sido instalada.
            $response = $db->query($sql); // Explicação DevIN: Executa SELECT COUNT sem escrita.
            $result[$key] = (int) $response->fetch_row()[0]; // Explicação DevIN: Guarda apenas o número agregado.
            $response->free(); // Explicação DevIN: Libera o resultado da consulta.
        } catch (mysqli_sql_exception $error) { // Explicação DevIN: Separa tabela ausente de contagem igual a zero.
            $result[$key] = null; // Explicação DevIN: Informa indisponibilidade sem inventar dados.
            error_log('DevIN ADM: métrica ' . $key . ' indisponível (MySQL ' . $error->getCode() . ').'); // Explicação DevIN: Registra somente o código de erro.
        } // Explicação DevIN: Finaliza tratamento de ausência de tabelas.
    } // Explicação DevIN: Finaliza coleta de números.
    $result['denuncias_pendentes'] = $result['denuncias_vagas'] !== null && $result['denuncias_perfis'] !== null
        ? $result['denuncias_vagas'] + $result['denuncias_perfis'] : null; // Explicação DevIN: Soma apenas quando ambas as tabelas estão disponíveis.
    $result['top_empresas'] = []; // Explicação DevIN: Reserva ranking de vagas por empresa.
    try { // Explicação DevIN: A classificação não deve bloquear o restante das métricas.
        $ranking = $db->query('SELECT e.nome, COUNT(v.id_vaga) AS vagas FROM empresa e LEFT JOIN vagas v ON v.id_empresa = e.id_empresa GROUP BY e.id_empresa, e.nome ORDER BY vagas DESC, e.nome ASC LIMIT 8'); // Explicação DevIN: Busca empresas com mais vagas sem expor dados privados.
        $result['top_empresas'] = $ranking->fetch_all(MYSQLI_ASSOC); // Explicação DevIN: Recupera apenas nome público e contagem.
        $ranking->free(); // Explicação DevIN: Libera resultado do ranking.
    } catch (mysqli_sql_exception $error) { // Explicação DevIN: Usa ranking vazio se consulta opcional falhar.
        error_log('DevIN ADM: ranking de empresas indisponível (MySQL ' . $error->getCode() . ').'); // Explicação DevIN: Registra erro sem vazamento de SQL ou dados.
    } // Explicação DevIN: Finaliza ranking.
    return $result; // Explicação DevIN: Retorna números sem modificar banco.
} // Explicação DevIN: Finaliza o coletor de métricas.
