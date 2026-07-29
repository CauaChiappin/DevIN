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

/*
 * Recebe a foto enviada pelo formulário, valida, move para php/uploads e devolve
 * o caminho que será gravado na coluna foto do banco de dados.
 */
function saveProfilePhoto(string $tipo, int $id, ?array $upload, ?string $currentPhoto): ?string
{
    // Sem arquivo novo, preserva o caminho da foto já salva no banco.
    if (!$upload || ($upload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return $currentPhoto;
    // Evita acessar uma chave inexistente caso o upload chegue incompleto.
    $temporaryFile = $upload['tmp_name'] ?? '';
    // Um caminho temporário vazio significa que o PHP não conseguiu receber o arquivo corretamente.
    if (!is_string($temporaryFile) || $temporaryFile === '') {
        throw new RuntimeException('Arquivo de imagem invÃ¡lido.');
    }
    // Confere o código do upload e garante que o arquivo veio realmente por HTTP POST.
    if (($upload['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || !is_uploaded_file($upload['tmp_name'])) throw new RuntimeException('Não foi possível enviar a foto.');

    // Confere o tipo real do arquivo no servidor, sem confiar apenas na extensão enviada.
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($upload['tmp_name']);
    // Define quais tipos são permitidos e qual extensão será usada no nome final.
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($extensions[$mime])) throw new InvalidArgumentException('Envie uma imagem JPG, PNG ou WEBP.');

    // Caminho físico da pasta onde as fotos ficam salvas dentro do projeto.
    $directory = __DIR__ . '/../uploads';
    if (!is_dir($directory) && !mkdir($directory, 0755, true)) throw new RuntimeException('Não foi possível preparar o armazenamento da foto.');
    // Cria nome único: tipo de conta + id + parte aleatória + extensão permitida.
    $filename = sprintf('%s-%d-%s.%s', $tipo, $id, bin2hex(random_bytes(8)), $extensions[$mime]);
    // Move o upload temporário para a pasta pública e retorna o caminho a salvar no banco.
    if (!move_uploaded_file($upload['tmp_name'], $directory . '/' . $filename)) throw new RuntimeException('Não foi possível salvar a foto.');
    // Este caminho relativo é o valor salvo no banco e usado mais tarde pela tag img.
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

    // Busca o perfil atual para manter a foto anterior caso uma nova não seja enviada.
    $currentProfile = findProfile($tipo, $id);
    // Salva a nova foto (ou mantém a anterior) e recebe o caminho final.
    $foto = saveProfilePhoto($tipo, $id, $upload, $currentProfile['foto'] ?? null);
    $conn = getDatabaseConnection();

    if ($tipo === 'adm') {
        $stmt = $conn->prepare("UPDATE {$table} SET nome = ?, email = ?, foto = ? WHERE {$idColumn} = ?");
        // prepare() retorna false se a consulta estiver inválida; não chame bind_param nesse caso.
        if (!$stmt) {
            throw new RuntimeException('NÃ£o foi possÃ­vel preparar a atualizaÃ§Ã£o do perfil: ' . $conn->error);
        }
        $stmt->bind_param('sssi', $nome, $email, $foto, $id);
    } else {
        $cep = preg_replace('/\D/', '', $data['cep'] ?? '');
        $telefone = preg_replace('/\D/', '', $data['telefone'] ?? '');
        if ($cep === '' || $telefone === '') {
            throw new InvalidArgumentException('Informe CEP e telefone válidos.');
        }
        // Os ? são preenchidos depois por bind_param, sem concatenar dados do usuário no SQL.
        $stmt = $conn->prepare("UPDATE {$table} SET nome = ?, email = ?, cep = ?, telefone = ?, foto = ? WHERE {$idColumn} = ?");
        // A coluna foto deve existir na tabela empresa (e pessoa) para salvar o caminho do upload.
        if (!$stmt) {
            throw new RuntimeException('NÃ£o foi possÃ­vel preparar a atualizaÃ§Ã£o do perfil: ' . $conn->error);
        }
        // 'sssssi' informa os tipos: cinco textos e, por último, o id inteiro.
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
