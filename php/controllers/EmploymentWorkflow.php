<?php
declare(strict_types=1);

/**
 * Confirmação de contratação, independente da aprovação da candidatura.
 * A empresa confirma primeiro e a pessoa confirma depois. Candidaturas
 * pendentes em outras vagas só são retiradas quando a pessoa autoriza.
 * Nenhum registro de candidatura é apagado ou reclassificado retroativamente.
 */
final class EmploymentWorkflow
{
    public static function ensureInstalled(mysqli $db): void
    {
        // Falhar de forma clara se as migrações ainda não foram instaladas.
        try {
            $db->query('SELECT application_id FROM devin_employment_confirmations LIMIT 0');
            $db->query('SELECT application_id FROM devin_application_withdrawals LIMIT 0');
        } catch (mysqli_sql_exception $error) {
            throw new RuntimeException('A confirmação de contratação ainda não foi instalada no banco.', 0, $error);
        }
    }

    public static function employerConfirm(mysqli $db, int $employerId, int $applicationId): void
    {
        self::ensureInstalled($db);
        if ($employerId < 1 || $applicationId < 1) {
            throw new InvalidArgumentException('Conta ou candidatura inválida.');
        }
        $db->begin_transaction();
        try {
            // A empresa só confirma uma candidatura que já aprovou e que lhe pertence.
            $stmt = $db->prepare('SELECT c.status FROM candidatura c
                JOIN vagas v ON v.id_vaga=c.id_vaga
                WHERE c.id_candidatura=? AND v.id_empresa=? FOR UPDATE');
            $stmt->bind_param('ii', $applicationId, $employerId);
            $stmt->execute();
            $candidate = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$candidate || $candidate['status'] !== 'aprovado') {
                throw new DomainException('Só é possível confirmar contratações de candidaturas aprovadas da sua empresa.');
            }

            // Evita dar como contratada uma pessoa que retirou a candidatura.
            $stmt = $db->prepare('SELECT application_id FROM devin_application_withdrawals
                WHERE application_id=? LIMIT 1');
            $stmt->bind_param('i', $applicationId);
            $stmt->execute();
            $withdrawn = $stmt->get_result()->num_rows > 0;
            $stmt->close();
            if ($withdrawn) throw new DomainException('Esta candidatura já foi retirada.');

            // Repetição do POST é idempotente; confirmação existente não é perdida.
            $stmt = $db->prepare('INSERT INTO devin_employment_confirmations
                (application_id,employer_confirmed_at) VALUES (?,NOW())
                ON DUPLICATE KEY UPDATE employer_confirmed_at=COALESCE(employer_confirmed_at,VALUES(employer_confirmed_at))');
            $stmt->bind_param('i', $applicationId);
            $stmt->execute();
            $stmt->close();
            $db->commit();
        } catch (Throwable $error) {
            $db->rollback();
            throw $error;
        }
    }

    public static function candidateConfirm(
        mysqli $db, int $personId, int $applicationId, bool $closePending
    ): int {
        self::ensureInstalled($db);
        if ($personId < 1 || $applicationId < 1) {
            throw new InvalidArgumentException('Conta ou candidatura inválida.');
        }
        $db->begin_transaction();
        try {
            // O ID do candidato vem exclusivamente da sessão, não do formulário.
            $stmt = $db->prepare('SELECT status FROM candidatura WHERE
                id_candidatura=? AND id_pessoa=? FOR UPDATE');
            $stmt->bind_param('ii', $applicationId, $personId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$row || $row['status'] !== 'aprovado') {
                throw new DomainException('Somente candidaturas aprovadas podem confirmar contratação.');
            }

            // A primeira confirmação da empresa é obrigatória.
            $stmt = $db->prepare('SELECT employer_confirmed_at, candidate_confirmed_at
                FROM devin_employment_confirmations WHERE application_id=? FOR UPDATE');
            $stmt->bind_param('i', $applicationId);
            $stmt->execute();
            $confirmation = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$confirmation || !$confirmation['employer_confirmed_at']) {
                throw new DomainException('A empresa ainda não confirmou a contratação.');
            }

            // Só processa o consentimento de retirada uma vez; não duplica histórico.
            if (!empty($confirmation['candidate_confirmed_at'])) {
                return self::finishExisting($db);
            }
            $stmt = $db->prepare('UPDATE devin_employment_confirmations
                SET candidate_confirmed_at=NOW(), candidate_closed_others=? WHERE application_id=?');
            $closeInt = $closePending ? 1 : 0;
            $stmt->bind_param('ii', $closeInt, $applicationId);
            $stmt->execute();
            $stmt->close();

            $withdrawals = 0;
            if ($closePending) {
                // Apenas outras candidaturas PENDENTES. Aprovações e contratos são mantidos.
                // A tabela de retiradas preserva a evidência e não apaga candidatura/status.
                $stmt = $db->prepare('INSERT INTO devin_application_withdrawals
                    (application_id, employment_application_id, candidate_id)
                    SELECT c.id_candidatura, ?, ? FROM candidatura c
                    LEFT JOIN devin_application_withdrawals w ON w.application_id=c.id_candidatura
                    WHERE c.id_pessoa=? AND c.id_candidatura<>? AND c.status="pendente"
                    AND w.application_id IS NULL');
                $stmt->bind_param('iiii', $applicationId, $personId, $personId, $applicationId);
                $stmt->execute();
                $withdrawals = $stmt->affected_rows;
                $stmt->close();
            }
            $db->commit();
            return $withdrawals;
        } catch (Throwable $error) {
            $db->rollback();
            throw $error;
        }
    }

    private static function finishExisting(mysqli $db): int
    {
        // Repetir a ação não deve reabrir nem cancelar mais candidaturas.
        $db->commit();
        return 0;
    }
}
