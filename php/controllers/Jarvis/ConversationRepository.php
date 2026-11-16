<?php
declare(strict_types=1);

final class ConversationRepository
{
    public static function create(mysqli $db, int $adminId, string $title = 'Nova conversa'): int
    {
        $title = mb_substr(trim($title), 0, 150, 'UTF-8') ?: 'Nova conversa';
        $stmt = $db->prepare('INSERT INTO jarvis_conversations (admin_id, title) VALUES (?, ?)');
        $stmt->bind_param('is', $adminId, $title);
        $stmt->execute();
        $id = (int) $db->insert_id;
        $stmt->close();
        return $id;
    }

    public static function belongsTo(mysqli $db, int $adminId, int $id): bool
    {
        if ($id <= 0) return false;
        $stmt = $db->prepare('SELECT id FROM jarvis_conversations WHERE id = ? AND admin_id = ?');
        $stmt->bind_param('ii', $id, $adminId);
        $stmt->execute();
        $found = $stmt->get_result()->fetch_row() !== null;
        $stmt->close();
        return $found;
    }

    public static function latest(mysqli $db, int $adminId, int $id): array
    {
        if (!self::belongsTo($db, $adminId, $id)) {
            throw new InvalidArgumentException('Conversa não encontrada.');
        }
        $stmt = $db->prepare('SELECT role, content FROM jarvis_messages WHERE conversation_id = ? ORDER BY id DESC LIMIT 20');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return array_reverse($items);
    }

    public static function append(mysqli $db, int $id, string $role, string $content): void
    {
        if (!in_array($role, ['user', 'assistant'], true)) {
            throw new InvalidArgumentException('Papel de mensagem inválido.');
        }
        $stmt = $db->prepare('INSERT INTO jarvis_messages (conversation_id, role, content) VALUES (?, ?, ?)');
        $stmt->bind_param('iss', $id, $role, $content);
        $stmt->execute();
        $stmt->close();
        $update = $db->prepare('UPDATE jarvis_conversations SET updated_at = NOW() WHERE id = ?');
        $update->bind_param('i', $id);
        $update->execute();
        $update->close();
    }

    public static function list(mysqli $db, int $adminId, string $query): array
    {
        $query = mb_substr(trim($query), 0, 80, 'UTF-8');
        if ($query === '') {
            $stmt = $db->prepare('SELECT id, title, updated_at FROM jarvis_conversations WHERE admin_id = ? ORDER BY updated_at DESC, id DESC LIMIT 25');
            $stmt->bind_param('i', $adminId);
        } else {
            // Pesquisa por termos individuais; não exige coincidência da frase inteira.
            // Não é busca semântica e não garante correção de erros de digitação.
            $tokens = array_slice(preg_split('/\s+/u', $query) ?: [], 0, 4);
            $terms = [];
            foreach ($tokens as $token) {
                if (mb_strlen($token, 'UTF-8') > 1) $terms[] = '%' . $token . '%';
            }
            if (!$terms) $terms = ['%' . $query . '%'];
            $clauses = [];
            $types = 'i';
            $params = [$adminId];
            foreach ($terms as $term) {
                $clauses[] = '(c.title LIKE ? OR EXISTS (SELECT 1 FROM jarvis_messages m WHERE m.conversation_id = c.id AND m.content LIKE ?))';
                $types .= 'ss';
                $params[] = $term;
                $params[] = $term;
            }
            $sql = 'SELECT c.id, c.title, c.updated_at FROM jarvis_conversations c WHERE c.admin_id = ? AND ('
                . implode(' OR ', $clauses) . ') ORDER BY c.updated_at DESC, c.id DESC LIMIT 25';
            $stmt = $db->prepare($sql);
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $data;
    }

    public static function delete(mysqli $db, int $adminId, int $id): bool
    {
        $stmt = $db->prepare('DELETE FROM jarvis_conversations WHERE id = ? AND admin_id = ?');
        $stmt->bind_param('ii', $id, $adminId);
        $stmt->execute();
        $deleted = $stmt->affected_rows === 1;
        $stmt->close();
        return $deleted;
    }
}
