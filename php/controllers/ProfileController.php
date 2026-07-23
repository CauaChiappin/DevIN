<?php

require_once __DIR__ . '/../config/database.php';

function profileTable(string $tipo): array
{
    return match ($tipo) {
        'empresa' => ['empresa', 'id_empresa'],
        'adm' => ['administrador', 'id_administrador'],
        default => ['pessoa', 'id_pessoa'],
    };
}

function findProfile(string $tipo, int $id): ?array
{
    [$table, $idColumn] = profileTable($tipo);
    $conn = getDatabaseConnection();
    $stmt = $conn->prepare("SELECT * FROM {$table} WHERE {$idColumn} = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $profile = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();
    $conn->close();

    return $profile;
}

function saveProfilePhoto(string $tipo, int $id, ?array $upload, ?string $currentPhoto): ?string
{
    if (!$upload || ($upload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return $currentPhoto;
    if (($upload['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || !is_uploaded_file($upload['tmp_name'])) throw new RuntimeException('Não foi possível enviar a foto.');

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($upload['tmp_name']);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($extensions[$mime])) throw new InvalidArgumentException('Envie uma imagem JPG, PNG ou WEBP.');

    $directory = __DIR__ . '/../uploads';
    if (!is_dir($directory) && !mkdir($directory, 0755, true)) throw new RuntimeException('Não foi possível preparar o armazenamento da foto.');
    $filename = sprintf('%s-%d-%s.%s', $tipo, $id, bin2hex(random_bytes(8)), $extensions[$mime]);
    if (!move_uploaded_file($upload['tmp_name'], $directory . '/' . $filename)) throw new RuntimeException('Não foi possível salvar a foto.');
    return 'uploads/' . $filename;
}

function updateProfile(string $tipo, int $id, array $data, ?array $upload = null): void
{
    [$table, $idColumn] = profileTable($tipo);
    $nome = trim($data['nome'] ?? '');
    $email = filter_var(trim($data['email'] ?? ''), FILTER_VALIDATE_EMAIL);

    if ($nome === '' || !$email) {
        throw new InvalidArgumentException('Informe um nome e e-mail válidos.');
    }

    $currentProfile = findProfile($tipo, $id);
    $foto = saveProfilePhoto($tipo, $id, $upload, $currentProfile['foto'] ?? null);
    $conn = getDatabaseConnection();

    if ($tipo === 'adm') {
        $stmt = $conn->prepare("UPDATE {$table} SET nome = ?, email = ?, foto = ? WHERE {$idColumn} = ?");
        $stmt->bind_param('sssi', $nome, $email, $foto, $id);
    } else {
        $cep = preg_replace('/\D/', '', $data['cep'] ?? '');
        $telefone = preg_replace('/\D/', '', $data['telefone'] ?? '');
        if ($cep === '' || $telefone === '') {
            throw new InvalidArgumentException('Informe CEP e telefone válidos.');
        }
        $stmt = $conn->prepare("UPDATE {$table} SET nome = ?, email = ?, cep = ?, telefone = ?, foto = ? WHERE {$idColumn} = ?");
        $stmt->bind_param('sssssi', $nome, $email, $cep, $telefone, $foto, $id);
    }

    if (!$stmt->execute()) {
        throw new RuntimeException('Não foi possível salvar as alterações. O e-mail pode já estar em uso.');
    }

    $stmt->close();
    $conn->close();
}

function updateLanguage(string $tipo, int $id, string $idioma): void
{
    if (!in_array($idioma, ['pt-BR', 'en', 'es'], true)) throw new InvalidArgumentException('Idioma inválido.');
    [$table, $idColumn] = profileTable($tipo);
    $conn = getDatabaseConnection();
    $stmt = $conn->prepare("UPDATE {$table} SET idioma = ? WHERE {$idColumn} = ?");
    $stmt->bind_param('si', $idioma, $id);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

function deleteProfile(string $tipo, int $id): void
{
    [$table, $idColumn] = profileTable($tipo);
    $conn = getDatabaseConnection();
    $stmt = $conn->prepare("DELETE FROM {$table} WHERE {$idColumn} = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
    $conn->close();
}

function listProfiles(string $tipo): array
{
    [$table, $idColumn] = profileTable($tipo);
    $conn = getDatabaseConnection();
    $result = $conn->query("SELECT {$idColumn} AS id, nome, email FROM {$table} ORDER BY nome");
    $profiles = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    $conn->close();
    return $profiles;
}
