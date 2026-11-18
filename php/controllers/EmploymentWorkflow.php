<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

/**
 * Confirmação de contratação, independente da aprovação da candidatura.
 * A empresa confirma primeiro e a pessoa confirma depois. Candidaturas
 * pendentes em outras vagas só são retiradas quando a pessoa autoriza.
 * Nenhum registro de candidatura é apagado ou reclassificado retroativamente.
 */
final class EmploymentWorkflow // Explicação DevIN: Declara o tipo EmploymentWorkflow e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    public static function ensureInstalled(mysqli $db): void // Explicação DevIN: Declara função/método ensureInstalled, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // Falhar de forma clara se as migrações ainda não foram instaladas.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            $db->query('SELECT application_id FROM devin_employment_confirmations LIMIT 0'); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            $db->query('SELECT application_id FROM devin_application_withdrawals LIMIT 0'); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        } catch (mysqli_sql_exception $error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            throw new RuntimeException('A confirmação de contratação ainda não foi instalada no banco.', 0, $error); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /**
     * Verifica marcação histórica de retirada sem exigir migração em sites antigos.
     * A tabela não existente significa que não houve retiradas registradas pelo recurso.
     */
    private static function withdrawalsAvailable(mysqli $db): bool
    {
        $stmt = $db->prepare('SELECT COUNT(*) AS existing FROM information_schema.TABLES
            WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME="devin_application_withdrawals"');
        $stmt->execute();
        $present = (int) $stmt->get_result()->fetch_assoc()['existing'];
        $stmt->close();
        return $present === 1;
    }

    /** Bloqueia novas decisões de empresas sobre candidatura voluntariamente retirada. */
    public static function isWithdrawn(mysqli $db, int $applicationId): bool
    {
        if ($applicationId < 1) throw new InvalidArgumentException('Candidatura inválida.');
        if (!self::withdrawalsAvailable($db)) return false;
        $stmt = $db->prepare('SELECT application_id FROM devin_application_withdrawals
            WHERE application_id=? LIMIT 1');
        $stmt->bind_param('i', $applicationId);
        $stmt->execute();
        $withdrawn = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        return $withdrawn;
    }

    /** Retorna IDs retirados somente de vagas pertencentes à empresa autenticada. */
    public static function withdrawnForCompany(mysqli $db, int $employerId): array
    {
        if ($employerId < 1) throw new InvalidArgumentException('Empresa inválida.');
        if (!self::withdrawalsAvailable($db)) return [];
        $stmt = $db->prepare('SELECT w.application_id FROM devin_application_withdrawals w
            JOIN candidatura c ON c.id_candidatura=w.application_id
            JOIN vagas v ON v.id_vaga=c.id_vaga
            WHERE v.id_empresa=?');
        $stmt->bind_param('i', $employerId);
        $stmt->execute();
        $ids = [];
        foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $row) {
            $ids[(int) $row['application_id']] = true;
        }
        $stmt->close();
        return $ids;
    }

    public static function employerConfirm(mysqli $db, int $employerId, int $applicationId): void // Explicação DevIN: Declara função/método employerConfirm, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        self::ensureInstalled($db); // Explicação DevIN: Continua expressão ou bloco pertencente a função employerConfirm.
        if ($employerId < 1 || $applicationId < 1) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Conta ou candidatura inválida.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $db->begin_transaction(); // Explicação DevIN: Continua expressão ou bloco pertencente a função employerConfirm.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            // A empresa só confirma uma candidatura que já aprovou e que lhe pertence.
            $stmt = $db->prepare('SELECT c.status FROM candidatura c
                JOIN vagas v ON v.id_vaga=c.id_vaga
                WHERE c.id_candidatura=? AND v.id_empresa=? FOR UPDATE'); // Explicação DevIN: Continua expressão ou bloco pertencente a função employerConfirm.
            $stmt->bind_param('ii', $applicationId, $employerId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $candidate = $stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função employerConfirm.
            if (!$candidate || $candidate['status'] !== 'aprovado') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new DomainException('Só é possível confirmar contratações de candidaturas aprovadas da sua empresa.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            // Evita dar como contratada uma pessoa que retirou a candidatura.
            $stmt = $db->prepare('SELECT application_id FROM devin_application_withdrawals
                WHERE application_id=? LIMIT 1'); // Explicação DevIN: Continua expressão ou bloco pertencente a função employerConfirm.
            $stmt->bind_param('i', $applicationId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $withdrawn = $stmt->get_result()->num_rows > 0; // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função employerConfirm.
            if ($withdrawn) throw new DomainException('Esta candidatura já foi retirada.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

            // Repetição do POST é idempotente; confirmação existente não é perdida.
            $stmt = $db->prepare('INSERT INTO devin_employment_confirmations
                (application_id,employer_confirmed_at) VALUES (?,NOW())
                ON DUPLICATE KEY UPDATE employer_confirmed_at=COALESCE(employer_confirmed_at,VALUES(employer_confirmed_at))'); // Explicação DevIN: Continua expressão ou bloco pertencente a função employerConfirm.
            $stmt->bind_param('i', $applicationId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função employerConfirm.
            $db->commit(); // Explicação DevIN: Continua expressão ou bloco pertencente a função employerConfirm.
        } catch (Throwable $error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            $db->rollback(); // Explicação DevIN: Continua expressão ou bloco pertencente a função employerConfirm.
            throw $error; // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    public static function candidateConfirm( // Explicação DevIN: Declara função/método candidateConfirm, com seus parâmetros e comportamento.
        mysqli $db, int $personId, int $applicationId, bool $closePending // Explicação DevIN: Continua expressão ou bloco pertencente a função candidateConfirm.
    ): int { // Explicação DevIN: Continua expressão ou bloco pertencente a função candidateConfirm.
        self::ensureInstalled($db); // Explicação DevIN: Continua expressão ou bloco pertencente a função candidateConfirm.
        if ($personId < 1 || $applicationId < 1) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Conta ou candidatura inválida.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $db->begin_transaction(); // Explicação DevIN: Continua expressão ou bloco pertencente a função candidateConfirm.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            // O ID do candidato vem exclusivamente da sessão, não do formulário.
            $stmt = $db->prepare('SELECT status FROM candidatura WHERE
                id_candidatura=? AND id_pessoa=? FOR UPDATE'); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            $stmt->bind_param('ii', $applicationId, $personId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $row = $stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função candidateConfirm.
            if (!$row || $row['status'] !== 'aprovado') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new DomainException('Somente candidaturas aprovadas podem confirmar contratação.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            // A primeira confirmação da empresa é obrigatória.
            $stmt = $db->prepare('SELECT employer_confirmed_at, candidate_confirmed_at
                FROM devin_employment_confirmations WHERE application_id=? FOR UPDATE'); // Explicação DevIN: Continua expressão ou bloco pertencente a função candidateConfirm.
            $stmt->bind_param('i', $applicationId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $confirmation = $stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função candidateConfirm.
            if (!$confirmation || !$confirmation['employer_confirmed_at']) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new DomainException('A empresa ainda não confirmou a contratação.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            // Só processa o consentimento de retirada uma vez; não duplica histórico.
            if (!empty($confirmation['candidate_confirmed_at'])) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                return self::finishExisting($db); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $stmt = $db->prepare('UPDATE devin_employment_confirmations
                SET candidate_confirmed_at=NOW(), candidate_closed_others=? WHERE application_id=?'); // Explicação DevIN: Continua expressão ou bloco pertencente a função candidateConfirm.
            $closeInt = $closePending ? 1 : 0; // Explicação DevIN: Atribui um valor à variável PHP $closeInt.
            $stmt->bind_param('ii', $closeInt, $applicationId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função candidateConfirm.

            $withdrawals = 0; // Explicação DevIN: Atribui um valor à variável PHP $withdrawals.
            if ($closePending) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                // Apenas outras candidaturas PENDENTES. Aprovações e contratos são mantidos.
                // A tabela de retiradas preserva a evidência e não apaga candidatura/status.
                $stmt = $db->prepare('INSERT INTO devin_application_withdrawals
                    (application_id, employment_application_id, candidate_id)
                    SELECT c.id_candidatura, ?, ? FROM candidatura c
                    LEFT JOIN devin_application_withdrawals w ON w.application_id=c.id_candidatura
                    WHERE c.id_pessoa=? AND c.id_candidatura<>? AND c.status="pendente"
                    AND w.application_id IS NULL'); // Explicação DevIN: Continua expressão ou bloco pertencente a função candidateConfirm.
                $stmt->bind_param('iiii', $applicationId, $personId, $personId, $applicationId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $withdrawals = $stmt->affected_rows; // Explicação DevIN: Atribui um valor à variável PHP $withdrawals.
                $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função candidateConfirm.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $db->commit(); // Explicação DevIN: Continua expressão ou bloco pertencente a função candidateConfirm.
            return $withdrawals; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } catch (Throwable $error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            $db->rollback(); // Explicação DevIN: Continua expressão ou bloco pertencente a função candidateConfirm.
            throw $error; // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    private static function finishExisting(mysqli $db): int // Explicação DevIN: Declara função/método finishExisting, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // Repetir a ação não deve reabrir nem cancelar mais candidaturas.
        $db->commit(); // Explicação DevIN: Continua expressão ou bloco pertencente a função finishExisting.
        return 0; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
