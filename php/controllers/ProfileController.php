<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/controllers/ProfileController.php
 * Finalidade: profile controller.
 * Domínio: processamento no servidor.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


require_once __DIR__ . '/../config/database.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/../config/security.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/../MailerHelper.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/ProfileLanguagePreference.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

// Detalhe DevIN: Método profileTable: encapsula uma responsabilidade reutilizável do módulo no contexto de profile controller. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function profileTable(string $tipo): array // Explicação DevIN: Declara função/método profileTable, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    // Mapa fechado evita que o tipo recebido controle diretamente nomes de tabela SQL.
    switch ($tipo) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
        case 'empresa': // Explicação DevIN: Define um dos caminhos possíveis de uma seleção de casos.
            return ['empresa', 'id_empresa']; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        case 'adm': // Explicação DevIN: Define um dos caminhos possíveis de uma seleção de casos.
            return ['administrador', 'id_administrador']; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        case 'pessoa': // Explicação DevIN: Define um dos caminhos possíveis de uma seleção de casos.
            return ['pessoa', 'id_pessoa']; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        default: // Explicação DevIN: Define um dos caminhos possíveis de uma seleção de casos.
            throw new InvalidArgumentException('Tipo de perfil inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Detalhe DevIN: Método findProfile: consulta e organiza dados sem presumir que o resultado seja único no contexto de profile controller. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function findProfile(string $tipo, int $id): ?array // Explicação DevIN: Declara função/método findProfile, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    [$table, $idColumn] = profileTable($tipo); // Explicação DevIN: Continua expressão ou bloco pertencente a função findProfile.
    $conn = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $conn.

    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
        $stmt = $conn->prepare("SELECT * FROM {$table} WHERE {$idColumn} = ? LIMIT 1"); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        if (!$stmt) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new RuntimeException('Não foi possível carregar o perfil.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $stmt->bind_param('i', $id); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $profile = $stmt->get_result()->fetch_assoc() ?: null; // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função findProfile.
        return $profile; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
        $conn->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função findProfile.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Detalhe DevIN: Método saveProfilePhoto: cria registros após checagem da entrada no contexto de profile controller. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function saveProfilePhoto(string $tipo, int $id, ?array $upload): ?string // Explicação DevIN: Declara função/método saveProfilePhoto, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    /* Mantém a foto atual quando não há novo envio; quando há, verifica erro, tamanho e conteúdo
     * antes de retornar os bytes que serão gravados no perfil. */
    if (!$upload || ($upload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        return null; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    if (($upload['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK || !is_uploaded_file($upload['tmp_name'])) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new RuntimeException('Não foi possível enviar a foto.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    $maxBytes = 5 * 1024 * 1024; // Limitado a 5 MB
    if (($upload['size'] ?? 0) <= 0 || ($upload['size'] ?? 0) > $maxBytes) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new InvalidArgumentException('A foto deve ter no máximo 5 MB.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Confere o conteúdo real do arquivo; não confia apenas na extensão enviada pelo navegador.
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($upload['tmp_name']); // Explicação DevIN: Atribui um valor à variável PHP $mime.
    $extensions = [ // Explicação DevIN: Atribui um valor à variável PHP $extensions.
        'image/jpeg' => 'jpg', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'image/png'  => 'png', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'image/webp' => 'webp', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.

    if (!isset($extensions[$mime]) || @getimagesize($upload['tmp_name']) === false) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new InvalidArgumentException('Envie uma imagem JPG, PNG ou WEBP válida.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    $uploadDirectory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads'; // Explicação DevIN: Atribui um valor à variável PHP $uploadDirectory.
    if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0750, true) && !is_dir($uploadDirectory)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new RuntimeException('Não foi possível preparar o armazenamento da foto.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    $fileName = 'profile-' . $tipo . '-' . $id . '-' . bin2hex(random_bytes(8)) . '.' . $extensions[$mime]; // Explicação DevIN: Atribui um valor à variável PHP $fileName.
    $filePath = $uploadDirectory . DIRECTORY_SEPARATOR . $fileName; // Explicação DevIN: Atribui um valor à variável PHP $filePath.
    if (!move_uploaded_file($upload['tmp_name'], $filePath)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new RuntimeException('Não foi possível salvar a foto enviada.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    return 'uploads/' . $fileName; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Detalhe DevIN: Método ensureUniqueEmail: encapsula uma responsabilidade reutilizável do módulo no contexto de profile controller. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function ensureUniqueEmail(string $tipo, int $id, string $email): void // Explicação DevIN: Declara função/método ensureUniqueEmail, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    $tables = [ // Explicação DevIN: Atribui um valor à variável PHP $tables.
        'pessoa'  => ['id_pessoa', 'pessoa'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'empresa' => ['id_empresa', 'empresa'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'adm'     => ['id_administrador', 'administrador'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.

    $conn = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $conn.

    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        // Permite que a própria conta mantenha o e-mail, mas bloqueia endereço usado por outra.
        foreach ($tables as $tableTipo => [$idColumn, $table]) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
            $stmt = $conn->prepare("SELECT {$idColumn} FROM {$table} WHERE email = ? LIMIT 1"); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            if (!$stmt) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new RuntimeException('Não foi possível validar o e-mail.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $stmt->bind_param('s', $email); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $row = $stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função ensureUniqueEmail.

            if ($row && !($tableTipo === $tipo && (int) $row[$idColumn] === $id)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new InvalidArgumentException('Este e-mail já está em uso por outra conta.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
        $conn->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função ensureUniqueEmail.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Detalhe DevIN: Método updateProfile: modifica apenas os dados previstos pela operação no contexto de profile controller. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function updateProfile(string $tipo, int $id, array $data, ?array $upload = null): void // Explicação DevIN: Declara função/método updateProfile, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    [$table, $idColumn] = profileTable($tipo); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateProfile.

    // Carrega o registro atual para validar existência e preservar a foto quando não houver upload novo.
    $currentProfile = findProfile($tipo, $id); // Explicação DevIN: Atribui um valor à variável PHP $currentProfile.
    if (!$currentProfile) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new RuntimeException('Perfil não encontrado.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    $nome = requestString($data, 'nome'); // Explicação DevIN: Atribui um valor à variável PHP $nome.
    $email = filter_var(requestString($data, 'email'), FILTER_VALIDATE_EMAIL); // Explicação DevIN: Atribui um valor à variável PHP $email.

    if ($nome === '' || !$email) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new InvalidArgumentException('Informe um nome e e-mail válidos.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Verifica duplicidade antes de salvar e valida foto/contato conforme o perfil.
    ensureUniqueEmail($tipo, $id, $email); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateProfile.

    $foto = $tipo === 'adm' ? null : saveProfilePhoto($tipo, $id, $upload); // Explicação DevIN: Atribui um valor à variável PHP $foto.

    $conn = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $conn.
    $perfilAtualizado = false; // Explicação DevIN: Atribui um valor à variável PHP $perfilAtualizado.

    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        // Administradores têm apenas nome/e-mail; os demais perfis também atualizam contato e foto.
        if ($tipo === 'adm') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
            $stmt = $conn->prepare("UPDATE {$table} SET nome = ?, email = ? WHERE {$idColumn} = ?"); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            if (!$stmt) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new RuntimeException('Falha ao preparar atualização do perfil.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $stmt->bind_param('ssi', $nome, $email, $id); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
            $cep = preg_replace('/\D/', '', requestString($data, 'cep')); // Explicação DevIN: Atribui um valor à variável PHP $cep.
            $telefone = preg_replace('/\D/', '', requestString($data, 'telefone')); // Explicação DevIN: Atribui um valor à variável PHP $telefone.

            if (strlen($cep) !== 8 || strlen($telefone) < 10 || strlen($telefone) > 11) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new InvalidArgumentException('Informe CEP (8 dígitos) e Telefone (10 ou 11 dígitos) válidos.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $stmt = $foto === null // Explicação DevIN: Atribui um valor à variável PHP $stmt.
                // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
                ? $conn->prepare("UPDATE {$table} SET nome = ?, email = ?, cep = ?, telefone = ? WHERE {$idColumn} = ?") // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
                : $conn->prepare("UPDATE {$table} SET nome = ?, email = ?, cep = ?, telefone = ?, foto = ? WHERE {$idColumn} = ?"); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            if (!$stmt) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new RuntimeException('Falha ao preparar atualização do perfil.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            if ($foto === null) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                $stmt->bind_param('ssssi', $nome, $email, $cep, $telefone, $id); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
                $stmt->bind_param('sssssi', $nome, $email, $cep, $telefone, $foto, $id); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if (!$stmt->execute()) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new RuntimeException('Não foi possível salvar as alterações.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateProfile.
        $perfilAtualizado = true; // Explicação DevIN: Atribui um valor à variável PHP $perfilAtualizado.
    } catch (mysqli_sql_exception $e) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        error_log('Erro ao atualizar perfil: ' . $e->getMessage()); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateProfile.
        throw new RuntimeException('Não foi possível salvar as alterações.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
        $conn->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateProfile.
        if (!$perfilAtualizado && is_string($foto)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $uploadDirectory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads'; // Explicação DevIN: Atribui um valor à variável PHP $uploadDirectory.
            $filePath = $uploadDirectory . DIRECTORY_SEPARATOR . basename($foto); // Explicação DevIN: Atribui um valor à variável PHP $filePath.
            if (str_starts_with($foto, 'uploads/profile-' . $tipo . '-' . $id . '-')) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                @unlink($filePath); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateProfile.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Detalhe DevIN: Método ensureLanguagePreferencesTable: encapsula uma responsabilidade reutilizável do módulo no contexto de profile controller. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function ensureLanguagePreferencesTable(mysqli $conn): void // Explicação DevIN: Declara função/método ensureLanguagePreferencesTable, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    static $tableEnsured = false; // Explicação DevIN: Continua expressão ou bloco pertencente a função ensureLanguagePreferencesTable.
    if ($tableEnsured) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    $conn->query("
        CREATE TABLE IF NOT EXISTS preferencias_dashboard (
            perfil_tipo VARCHAR(16) NOT NULL,
            perfil_id INT UNSIGNED NOT NULL,
            idioma VARCHAR(5) NOT NULL DEFAULT 'pt-BR',
            atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (perfil_tipo, perfil_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    "); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    $tableEnsured = true; // Explicação DevIN: Atribui um valor à variável PHP $tableEnsured.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Detalhe DevIN: Método getProfileLanguage: consulta e organiza dados sem presumir que o resultado seja único no contexto de profile controller. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function getProfileLanguage(string $tipo, int $id): string // Explicação DevIN: Declara função/método getProfileLanguage, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    profileTable($tipo); // Explicação DevIN: Continua expressão ou bloco pertencente a função getProfileLanguage.
    $conn = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $conn.

    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        ensureLanguagePreferencesTable($conn); // Explicação DevIN: Continua expressão ou bloco pertencente a função getProfileLanguage.
        // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
        $stmt = $conn->prepare('SELECT idioma FROM preferencias_dashboard WHERE perfil_tipo = ? AND perfil_id = ? LIMIT 1'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->bind_param('si', $tipo, $id); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $preference = $stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função getProfileLanguage.

        $idioma = (string) ($preference['idioma'] ?? 'pt-BR'); // Explicação DevIN: Atribui um valor à variável PHP $idioma.
        if (!in_array($idioma, ['pt-BR', 'en', 'es'], true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new RuntimeException('O idioma salvo para o perfil é inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $_SESSION['idioma'] = $idioma; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        return $idioma; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
        $conn->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função getProfileLanguage.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Detalhe DevIN: Método updateLanguage: modifica apenas os dados previstos pela operação no contexto de profile controller. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function updateLanguage(string $tipo, int $id, string $idioma): void // Explicação DevIN: Declara função/método updateLanguage, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    profileTable($tipo); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateLanguage.
    if (!in_array($idioma, ['pt-BR', 'en', 'es'], true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new InvalidArgumentException('Idioma inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    $conn = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $conn.
    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        ensureLanguagePreferencesTable($conn); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateLanguage.
        // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
        $stmt = $conn->prepare("
            INSERT INTO preferencias_dashboard (perfil_tipo, perfil_id, idioma)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE idioma = VALUES(idioma)
        "); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        $stmt->bind_param('sis', $tipo, $id, $idioma); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateLanguage.
    } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
        $conn->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateLanguage.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    startSecureSession(); // Explicação DevIN: Continua expressão ou bloco pertencente a função updateLanguage.
    $_SESSION['idioma'] = $idioma; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Detalhe DevIN: Método deleteProfile: trata remoção sujeita às permissões e integridade do banco no contexto de profile controller. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function deleteProfile(string $tipo, int $id): void // Explicação DevIN: Declara função/método deleteProfile, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    [$table, $idColumn] = profileTable($tipo); // Explicação DevIN: Continua expressão ou bloco pertencente a função deleteProfile.
    $conn = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $conn.
    $email = ''; // Explicação DevIN: Atribui um valor à variável PHP $email.
    $nome = ''; // Explicação DevIN: Atribui um valor à variável PHP $nome.
    $idioma = 'pt-BR'; // Explicação DevIN: Atribui um valor à variável PHP $idioma.

    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        if (in_array($tipo, ['pessoa', 'empresa'], true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
            $stmtProfile = $conn->prepare("SELECT nome, email FROM {$table} WHERE {$idColumn} = ? LIMIT 1"); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmtProfile->bind_param('i', $id); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmtProfile->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $profile = $stmtProfile->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            $stmtProfile->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função deleteProfile.

            if (!$profile) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new RuntimeException('Perfil não encontrado.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $email = (string) $profile['email']; // Explicação DevIN: Atribui um valor à variável PHP $email.
            $nome = (string) $profile['nome']; // Explicação DevIN: Atribui um valor à variável PHP $nome.
            $idioma = ProfileLanguagePreference::load($conn, $tipo, $id); // Explicação DevIN: Atribui um valor à variável PHP $idioma.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        // O ID é parâmetro preparado; tabela e coluna foram selecionadas pelo mapa fechado.
        // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
        $stmt = $conn->prepare("DELETE FROM {$table} WHERE {$idColumn} = ?"); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        if (!$stmt) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new RuntimeException('Não foi possível excluir o perfil.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $stmt->bind_param('i', $id); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $deleted = $stmt->affected_rows === 1; // Explicação DevIN: Atribui um valor à variável PHP $deleted.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função deleteProfile.
    } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
        $conn->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função deleteProfile.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    if ($deleted && $email !== '' && !MailerHelper::enviarConfirmacaoExclusao($email, $nome, $tipo, $idioma)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        error_log('Falha ao enviar confirmação de exclusão de conta.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função deleteProfile.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Detalhe DevIN: Método listProfiles: consulta e organiza dados sem presumir que o resultado seja único no contexto de profile controller. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function listProfiles(string $tipo): array // Explicação DevIN: Declara função/método listProfiles, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    // Retorna uma lista compacta ordenada para interfaces que precisam apenas identificar perfis.
    [$table, $idColumn] = profileTable($tipo); // Explicação DevIN: Continua expressão ou bloco pertencente a função listProfiles.
    $conn = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $conn.

    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        $result = $conn->query("SELECT {$idColumn} AS id, nome, email FROM {$table} ORDER BY nome"); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : []; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
        $conn->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função listProfiles.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
