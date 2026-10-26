<?php

declare(strict_types=1);

final class ResumeDocument
{
    public static function ensureSchema(mysqli $connection): void
    {
        $connection->query(
            'CREATE TABLE IF NOT EXISTS curriculo_documentos (
                id_pessoa INT NOT NULL PRIMARY KEY,
                conteudo_json LONGTEXT NULL,
                arquivo_nome VARCHAR(255) NULL,
                arquivo_caminho VARCHAR(255) NULL,
                mime_type VARCHAR(100) NULL,
                atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
    }

    public static function find(mysqli $connection, int $personId): array
    {
        self::ensureSchema($connection);
        $statement = $connection->prepare(
            'SELECT conteudo_json, arquivo_nome, arquivo_caminho, mime_type, atualizado_em
             FROM curriculo_documentos WHERE id_pessoa = ? LIMIT 1'
        );
        $statement->bind_param('i', $personId);
        $statement->execute();
        $document = $statement->get_result()->fetch_assoc() ?? [];
        $statement->close();
        return $document;
    }

    public static function hasResume(mysqli $connection, int $personId): bool
    {
        $statement = $connection->prepare('SELECT id_curriculo FROM curriculo WHERE id_pessoa = ? LIMIT 1');
        $statement->bind_param('i', $personId);
        $statement->execute();
        $hasLegacyResume = $statement->get_result()->num_rows > 0;
        $statement->close();
        if ($hasLegacyResume) {
            return true;
        }

        $document = self::find($connection, $personId);
        return !empty($document['arquivo_caminho']) || !empty($document['conteudo_json']);
    }

    public static function saveGenerated(mysqli $connection, int $personId, array $resume): void
    {
        self::ensureSchema($connection);
        $content = json_encode($resume, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $statement = $connection->prepare(
            'INSERT INTO curriculo_documentos (id_pessoa, conteudo_json) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE conteudo_json = VALUES(conteudo_json)'
        );
        $statement->bind_param('is', $personId, $content);
        $statement->execute();
        $statement->close();
    }

    public static function replaceUpload(
        mysqli $connection,
        int $personId,
        string $originalName,
        string $relativePath,
        string $mimeType
    ): ?string {
        self::ensureSchema($connection);
        $previous = self::find($connection, $personId);
        $statement = $connection->prepare(
            'INSERT INTO curriculo_documentos (id_pessoa, conteudo_json, arquivo_nome, arquivo_caminho, mime_type)
             VALUES (?, NULL, ?, ?, ?)
             ON DUPLICATE KEY UPDATE conteudo_json = NULL, arquivo_nome = VALUES(arquivo_nome),
             arquivo_caminho = VALUES(arquivo_caminho), mime_type = VALUES(mime_type)'
        );
        $statement->bind_param('isss', $personId, $originalName, $relativePath, $mimeType);
        $statement->execute();
        $statement->close();

        $previousPath = (string) ($previous['arquivo_caminho'] ?? '');
        return $previousPath !== '' && $previousPath !== $relativePath ? $previousPath : null;
    }

    public static function storageDirectory(): string
    {
        $directory = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'resumes';
        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
            throw new RuntimeException('Não foi possível preparar o armazenamento do currículo.');
        }
        return $directory;
    }

    public static function absolutePath(string $relativePath): ?string
    {
        if (preg_match('~^storage/resumes/[a-f0-9]{32}\.(?:pdf|doc|docx)$~', $relativePath) !== 1) {
            return null;
        }
        $path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        return is_file($path) ? $path : null;
    }

    public static function removeFile(?string $relativePath): void
    {
        if ($relativePath === null) {
            return;
        }
        $path = self::absolutePath($relativePath);
        if ($path !== null) {
            @unlink($path);
        }
    }
}