<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

function profileTable(string $tipo): array
{
    return match ($tipo) {
        'empresa' => ['empresa', 'id_empresa'],
        'adm' => ['administrador', 'id_administrador'],
        'pessoa' => ['pessoa', 'id_pessoa'],
        default => throw new InvalidArgumentException('Tipo de perfil inválido.'),
    };
}

function findProfile(string $tipo, int $id): ?array
{
    [$table, $idColumn] = profileTable($tipo);
    $conn = getDatabaseConnection();

    try {
        $stmt = $conn->prepare("SELECT * FROM {$table} WHERE {$idColumn} = ? LIMIT 1");
        if (!$stmt) {
            throw new RuntimeException('Não foi possível carregar o perfil.');
        }

        $stmt->bind_param('i', $id);
        $stmt->execute();
        $profile = $stmt->get_result()->fetch_assoc() ?: null;
        $stmt->close();
        return $profile;
    } finally {
        $conn->close();
    }
}

function saveProfilePhoto(string $tipo, int $id, ?array $upload, ?string $currentPhoto): ?string
{
    if (!$upload || ($upload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return $currentPhoto;
    }

    if (($upload['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || !is_uploaded_file($upload['tmp_name'])) {
        throw new RuntimeException('Não foi possível enviar a foto.');
    }

    $maxBytes = 5 * 1024 * 1024;
    if (($upload['size'] ?? 0) <= 0 || ($upload['size'] ?? 0) > $maxBytes) {
        throw new InvalidArgumentException('A foto deve ter no máximo 5 MB.');
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($upload['tmp_name']);
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($extensions[$mime]) || @getimagesize($upload['tmp_name']) === false) {
        throw new InvalidArgumentException('Envie uma imagem JPG, PNG ou WEBP válida.');
    }

    $directory = __DIR__ . '/../uploads';
    if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
        throw new RuntimeException('Não foi possível preparar o armazenamento da foto.');
    }

    $filename = sprintf(
        '%s-%d-%s.%s',
        $tipo,
        $id,
        bin2hex(random_bytes(8)),
        $extensions[$mime]
    );

    $destination = $directory . '/' . $filename;
    if (!move_uploaded_file($upload['tmp_name'], $destination)) {
        throw new RuntimeException('Não foi possível salvar a foto.');
    }

    if (is_string($currentPhoto) && str_starts_with($currentPhoto, 'uploads/')) {
        $oldPath = __DIR__ . '/../' . $currentPhoto;
        if (is_file($oldPath)) {
            @unlink($oldPath);
        }
    }

    return 'uploads/' . $filename;
}

function ensureUniqueEmail(string $tipo, int $id, string $email): void
{
    $tables = [
        'pessoa' => ['id_pessoa', 'pessoa'],
        'empresa' => ['id_empresa', 'empresa'],
        'adm' => ['id_administrador', 'administrador'],
    ];

    $conn = getDatabaseConnection();

    try {
        foreach ($tables as $tableTipo => [$idColumn, $table]) {
            $stmt = $conn->prepare("SELECT {$idColumn} FROM {$table} WHERE email = ? LIMIT 1");
            if (!$stmt) {
                throw new RuntimeException('Não foi possível validar o e-mail.');
            }

            $stmt->bind_param('s', $email);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($row && !($tableTipo === $tipo && (int) $row[$idColumn] === $id)) {
                throw new InvalidArgumentException('Este e-mail já está em uso por outra conta.');
            }
        }
    } finally {
        $conn->close();
    }
}

function updateProfile(string $tipo, int $id, array $data, ?array $upload = null): void
{
    [$table, $idColumn] = profileTable($tipo);

    $currentProfile = findProfile($tipo, $id);
    if (!$currentProfile) {
        throw new RuntimeException('Perfil não encontrado.');
    }

    $nome = trim($data['nome'] ?? '');
    $email = filter_var(trim($data['email'] ?? ''), FILTER_VALIDATE_EMAIL);

    if ($nome === '' || !$email) {
        throw new InvalidArgumentException('Informe um nome e e-mail válidos.');
    }

    ensureUniqueEmail($tipo, $id, $email);
    $foto = saveProfilePhoto($tipo, $id, $upload, $currentProfile['foto'] ?? null);

    $conn = getDatabaseConnection();

    try {
        if ($tipo === 'adm') {
            $stmt = $conn->prepare("UPDATE {$table} SET nome = ?, email = ?, foto = ? WHERE {$idColumn} = ?");
            $stmt->bind_param('sssi', $nome, $email, $foto, $id);
        } else {
            $cep = preg_replace('/\D/', '', $data['cep'] ?? '');
            $telefone = preg_replace('/\D/', '', $data['telefone'] ?? '');

            if (strlen($cep) !== 8 || strlen($telefone) < 10) {
                throw new InvalidArgumentException('Informe CEP e telefone válidos.');
            }

            $stmt = $conn->prepare("UPDATE {$table} SET nome = ?, email = ?, cep = ?, telefone = ?, foto = ? WHERE {$idColumn} = ?");
            $stmt->bind_param('sssssi', $nome, $email, $cep, $telefone, $foto, $id);
        }

        if (!$stmt->execute()) {
            throw new RuntimeException('Não foi possível salvar as alterações.');
        }

        $stmt->close();
    } catch (mysqli_sql_exception $e) {
        error_log('Erro ao atualizar perfil: ' . $e->getMessage());
        throw new RuntimeException('Não foi possível salvar as alterações.');
    } finally {
        $conn->close();
    }
}

function updateLanguage(string $tipo, int $id, string $idioma): void
{
    if (!in_array($idioma, ['pt-BR', 'en', 'es'], true)) {
        throw new InvalidArgumentException('Idioma inválido.');
    }

    [$table, $idColumn] = profileTable($tipo);
    $conn = getDatabaseConnection();

    try {
        $stmt = $conn->prepare("UPDATE {$table} SET idioma = ? WHERE {$idColumn} = ?");
        $stmt->bind_param('si', $idioma, $id);
        $stmt->execute();
        $stmt->close();
    } finally {
        $conn->close();
    }
}

function deleteProfile(string $tipo, int $id): void
{
    [$table, $idColumn] = profileTable($tipo);
    $conn = getDatabaseConnection();

    try {
        $stmt = $conn->prepare("DELETE FROM {$table} WHERE {$idColumn} = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
    } finally {
        $conn->close();
    }
}

function listProfiles(string $tipo): array
{
    [$table, $idColumn] = profileTable($tipo);
    $conn = getDatabaseConnection();

    try {
        $result = $conn->query("SELECT {$idColumn} AS id, nome, email FROM {$table} ORDER BY nome");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    } finally {
        $conn->close();
    }
}
