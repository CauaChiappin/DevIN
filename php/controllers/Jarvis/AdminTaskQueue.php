<?php
declare(strict_types=1);

/** Fila de atividades administrativas, sem permissão de executar ações reais no DevIN. */
final class AdminTaskQueue
{
    public static function list(mysqli $db, int $adminId): array
    {
        $stmt = $db->prepare('SELECT id, title, description, status, created_at, updated_at FROM jarvis_tasks WHERE admin_id = ? ORDER BY FIELD(status, \'pending\', \'in_progress\', \'done\', \'cancelled\'), updated_at DESC, id DESC LIMIT 50');
        $stmt->bind_param('i', $adminId);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $items;
    }

    public static function create(mysqli $db, int $adminId, string $title, string $description): int
    {
        $title = trim($title);
        $description = trim($description);
        if ($title === '' || mb_strlen($title, 'UTF-8') > 150
            || mb_strlen($description, 'UTF-8') > 1200) {
            throw new InvalidArgumentException('Informe um título de até 150 caracteres e descrição de até 1.200.');
        }
        $db->begin_transaction();
        try {
            $stmt = $db->prepare('INSERT INTO jarvis_tasks (admin_id, title, description) VALUES (?, ?, ?)');
            $stmt->bind_param('iss', $adminId, $title, $description);
            $stmt->execute();
            $id = (int) $db->insert_id;
            $stmt->close();
            self::audit($db, $id, $adminId, 'created');
            $db->commit();
            return $id;
        } catch (Throwable $exception) {
            $db->rollback();
            throw $exception;
        }
    }

    public static function change(mysqli $db, int $adminId, int $taskId, string $action): string
    {
        $allowed = [
            'start' => ['from' => 'pending', 'to' => 'in_progress', 'audit' => 'started'],
            'complete' => ['from' => 'in_progress', 'to' => 'done', 'audit' => 'completed'],
        ];
        if ($action === 'cancel') {
            $allowed['cancel'] = ['from' => '', 'to' => 'cancelled', 'audit' => 'cancelled'];
        }
        if ($taskId <= 0 || !isset($allowed[$action])) {
            throw new InvalidArgumentException('Ação inválida.');
        }
        $db->begin_transaction();
        try {
            $stmt = $db->prepare('SELECT status FROM jarvis_tasks WHERE id = ? AND admin_id = ? FOR UPDATE');
            $stmt->bind_param('ii', $taskId, $adminId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if (!$row) throw new InvalidArgumentException('Atividade não encontrada.');
            $current = (string) $row['status'];
            if ($action === 'cancel' ? !in_array($current, ['pending', 'in_progress'], true) : $current !== $allowed[$action]['from']) {
                throw new InvalidArgumentException('Esta atividade já mudou de estado. Atualize a lista.');
            }
            $next = $allowed[$action]['to'];
            $stmt = $db->prepare('UPDATE jarvis_tasks SET status = ? WHERE id = ? AND admin_id = ?');
            $stmt->bind_param('sii', $next, $taskId, $adminId);
            $stmt->execute();
            $stmt->close();
            self::audit($db, $taskId, $adminId, $allowed[$action]['audit']);
            $db->commit();
            return $next;
        } catch (Throwable $exception) {
            $db->rollback();
            throw $exception;
        }
    }

    private static function audit(mysqli $db, int $taskId, int $adminId, string $action): void
    {
        $stmt = $db->prepare('INSERT INTO jarvis_task_audit (task_id, admin_id, action) VALUES (?, ?, ?)');
        $stmt->bind_param('iis', $taskId, $adminId, $action);
        $stmt->execute();
        $stmt->close();
    }
}
