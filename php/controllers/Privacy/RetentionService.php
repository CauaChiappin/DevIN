<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/controllers/Privacy/RetentionService.php
 * Finalidade: retention service.
 * Domínio: privacidade e proteção de dados.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


/**
 * Rotina CLI controlada futura. NÃO há endpoint web nem agendamento automático
 * nesta fase. Logs sob retenção legal são preservados.
 */
final class RetentionService // Explicação DevIN: Declara o tipo RetentionService e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    /** @return array{access_logs:int,audit_logs:int} */
    // Detalhe DevIN: Método expiredCounts: encapsula uma responsabilidade reutilizável do módulo no contexto de retention service. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function expiredCounts(mysqli $db): array // Explicação DevIN: Declara função/método expiredCounts, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $access = $db->query( // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            'SELECT COUNT(*) FROM app_access_logs WHERE delete_after < UTC_TIMESTAMP(6) ' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            . 'AND (legal_hold_until IS NULL OR legal_hold_until < UTC_TIMESTAMP(6))' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $audit = $db->query( // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            'SELECT COUNT(*) FROM audit_logs WHERE retention_until < UTC_TIMESTAMP(6)' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return [ // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            'access_logs' => (int) $access->fetch_row()[0], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'audit_logs' => (int) $audit->fetch_row()[0], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /**
     * Apaga no máximo 500 linhas por tabela, dentro da mesma transação.
     * Uso SOMENTE após aprovação de política de retenção e legal holds.
     */
    // Detalhe DevIN: Método pruneBatch: encapsula uma responsabilidade reutilizável do módulo no contexto de retention service. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function pruneBatch(mysqli $db, bool $confirmed = false): array // Explicação DevIN: Declara função/método pruneBatch, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!$confirmed) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new DomainException('Exige confirmação explícita de política e operador.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // Detalhe DevIN: Inicia uma transação para manter alterações relacionadas consistentes.
        $db->begin_transaction(); // Explicação DevIN: Continua expressão ou bloco pertencente a função pruneBatch.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            $db->query( // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
                'DELETE FROM app_access_logs WHERE delete_after < UTC_TIMESTAMP(6) ' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                . 'AND (legal_hold_until IS NULL OR legal_hold_until < UTC_TIMESTAMP(6)) ' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                . 'ORDER BY id LIMIT 500' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $access = $db->affected_rows; // Explicação DevIN: Atribui um valor à variável PHP $access.
            $db->query( // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
                'DELETE FROM audit_logs WHERE retention_until < UTC_TIMESTAMP(6) ' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                . 'ORDER BY id LIMIT 500' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $audit = $db->affected_rows; // Explicação DevIN: Atribui um valor à variável PHP $audit.
            // Detalhe DevIN: Confirma alterações realizadas no mesmo contexto transacional.
            $db->commit(); // Explicação DevIN: Continua expressão ou bloco pertencente a função pruneBatch.
            return ['access_logs' => $access, 'audit_logs' => $audit]; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            // Detalhe DevIN: Desfaz alterações ainda não confirmadas quando ocorre falha.
            $db->rollback(); // Explicação DevIN: Continua expressão ou bloco pertencente a função pruneBatch.
            throw $exception; // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
