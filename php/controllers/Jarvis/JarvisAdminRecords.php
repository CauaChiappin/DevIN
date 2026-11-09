<?php
declare(strict_types=1);

/**
 * Acesso de leitura fechado: nenhum SQL é gerado pelo modelo.
 * Não retorna CPF/CNPJ, e-mail, fotos, senhas, currículos ou contatos.
 */
final class JarvisAdminRecords
{
    private const KINDS = ['pessoa','empresa','vaga','reports_vagas','reports_perfis'];

    public static function validKind(string $kind): bool
    {
        return in_array($kind, self::KINDS, true);
    }

    public static function recipient(mysqli $db, string $type, int $id): ?array
    {
        if (!in_array($type, ['pessoa','empresa'], true) || $id <= 0) {
            throw new InvalidArgumentException('Destinatário inválido.');
        }
        [$table, $column] = $type === 'pessoa'
            ? ['pessoa','id_pessoa'] : ['empresa','id_empresa'];
        $stmt = $db->prepare("SELECT {$column} AS id, nome FROM {$table} WHERE {$column} = ? LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc() ?: null;
        $stmt->close();
        return $row === null ? null : [
            'id'=>(int)$row['id'], 'type'=>$type,
            'name'=>mb_substr((string)$row['nome'], 0, 100),
        ];
    }

    /** @return list<array<string,mixed>> */
    public static function search(mysqli $db, string $kind, string $query): array
    {
        if (!self::validKind($kind) || mb_strlen($query) > 100) {
            throw new InvalidArgumentException('Consulta inválida.');
        }
        $query = trim($query);
        $exactId = ctype_digit($query) && (int)$query > 0 ? (int)$query : 0;
        $like = '%' . str_replace(['\\','%','_'], ['\\\\','\\%','\\_'], $query) . '%';
        if ($kind === 'pessoa' || $kind === 'empresa') {
            [$table,$col] = $kind === 'pessoa'
                ? ['pessoa','id_pessoa'] : ['empresa','id_empresa'];
            $stmt = $db->prepare(
                "SELECT {$col} AS id,nome AS title FROM {$table} "
                . "WHERE nome LIKE ? OR {$col} = ? ORDER BY {$col} DESC LIMIT 12"
            );
            $stmt->bind_param('si', $like, $exactId);
        } elseif ($kind === 'vaga') {
            $stmt = $db->prepare(
                'SELECT v.id_vaga AS id,v.titulo AS title,e.nome AS subtitle '
                . 'FROM vagas v LEFT JOIN empresa e ON e.id_empresa=v.id_empresa '
                . 'WHERE v.titulo LIKE ? OR v.id_vaga=? ORDER BY v.id_vaga DESC LIMIT 12'
            );
            $stmt->bind_param('si', $like, $exactId);
        } elseif ($kind === 'reports_vagas') {
            $stmt = $db->prepare(
                'SELECT id,titulo_vaga AS title,motivo,status FROM reports_vagas '
                . "WHERE id=? OR titulo_vaga LIKE ? ORDER BY id DESC LIMIT 12"
            );
            $stmt->bind_param('is', $exactId, $like);
        } else {
            $stmt = $db->prepare(
                'SELECT id,nome_denunciado AS title,motivo,status,tipo_denunciado '
                . 'FROM reports_perfis WHERE id=? OR nome_denunciado LIKE ? ORDER BY id DESC LIMIT 12'
            );
            $stmt->bind_param('is', $exactId, $like);
        }
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return array_map(static function(array $row): array {
            $row['id'] = (int)$row['id'];
            foreach ($row as $key=>$value) {
                if (is_string($value)) $row[$key] = mb_substr($value,0,180);
            }
            return $row;
        }, $result);
    }

    /**
     * Resumo explícito selecionado pelo ADM. Somente este texto pode ser
     * incluído na chamada externa de IA após clique em Analisar.
     */
    public static function inspect(mysqli $db, string $kind, int $id): ?array
    {
        if (!self::validKind($kind) || $id < 1) {
            throw new InvalidArgumentException('Registro inválido.');
        }
        if ($kind === 'pessoa' || $kind === 'empresa') {
            return self::recipient($db, $kind, $id);
        }
        $queries = [
            'vaga' => 'SELECT id_vaga AS id,titulo AS title,LEFT(descricao,450) AS description '
                . 'FROM vagas WHERE id_vaga=? LIMIT 1',
            'reports_vagas' => 'SELECT id,motivo,status,id_vaga,titulo_vaga AS title '
                . 'FROM reports_vagas WHERE id=? LIMIT 1',
            'reports_perfis' => 'SELECT id,motivo,status,tipo_denunciado,id_denunciado '
                . 'FROM reports_perfis WHERE id=? LIMIT 1',
        ];
        $stmt = $db->prepare($queries[$kind]);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc() ?: null;
        $stmt->close();
        if ($row === null) return null;
        foreach ($row as $key=>$value) {
            if (is_string($value)) $row[$key] = mb_substr($value,0,450);
        }
        return $row;
    }
}
