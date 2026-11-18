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
function ensureUniqueEmail(string $tipo, int $id, string $email, ?mysqli $connection = null): void // Explicação DevIN: Valida o endereço em todas as categorias usando a transação ativa quando fornecida.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    $tables = [ // Explicação DevIN: Atribui um valor à variável PHP $tables.
        'pessoa'  => ['id_pessoa', 'pessoa'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'empresa' => ['id_empresa', 'empresa'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'adm'     => ['id_administrador', 'administrador'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.

    $ownsConnection = $connection === null; // Explicação DevIN: Evita fechar a conexão transacional do chamador.
    $conn = $connection ?? getDatabaseConnection(); // Explicação DevIN: Usa a mesma conexão da alteração de e-mail.

    try { // Explicação DevIN: Verifica colisões entre pessoa, empresa e ADM.
        // Permite manter o próprio e-mail, mas nunca assumir o endereço de outra conta.
        foreach ($tables as $tableTipo => [$idColumn, $table]) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
            $stmt = $conn->prepare("SELECT {$idColumn} FROM {$table} WHERE LOWER(email) = ? LIMIT 1"); // Explicação DevIN: Compara endereços sem diferenciar maiúsculas e minúsculas.
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
    } finally { // Explicação DevIN: Libera a conexão somente se a função a abriu.
        if ($ownsConnection) $conn->close(); // Explicação DevIN: Preserva transações do atualizador de perfil.
    } // Explicação DevIN: Conclui a verificação de e-mail.
} // Explicação DevIN: Finaliza a validação compartilhada.

// Atualiza e-mail com confirmação de senha, transação e sessão sincronizada somente após COMMIT.
function updateProfile(string $tipo, int $id, array $data, ?array $upload = null): array // Explicação DevIN: Retorna o nome e e-mail realmente gravados para atualizar a sessão.
{ // Explicação DevIN: Inicia a alteração segura do perfil.
    [$table, $idColumn] = profileTable($tipo); // Explicação DevIN: Escolhe tabela e chave primária de lista autorizada.
    $nome = trim(requestString($data, 'nome')); // Explicação DevIN: Normaliza o nome enviado pelo formulário.
    $email = strtolower(trim(requestString($data, 'email'))); // Explicação DevIN: Uniformiza o endereço utilizado no login.
    if ($nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) { // Explicação DevIN: Evita endereços inválidos ou maiores que o limite SMTP.
        throw new InvalidArgumentException('Informe um nome e e-mail válidos (até 254 caracteres).'); // Explicação DevIN: Informa a correção sem escrever no banco.
    } // Explicação DevIN: Encerra as validações iniciais.
    $cep = ''; // Explicação DevIN: Inicializa campo opcional de acordo com o perfil.
    $telefone = ''; // Explicação DevIN: Inicializa contato antes de abrir a transação.
    if ($tipo !== 'adm') { // Explicação DevIN: Valida os campos comuns de pessoa e empresa.
        $cep = preg_replace('/\\D/', '', requestString($data, 'cep')); // Explicação DevIN: Extrai somente os dígitos do CEP.
        $telefone = preg_replace('/\\D/', '', requestString($data, 'telefone')); // Explicação DevIN: Extrai somente os dígitos do telefone.
        if (strlen($cep) !== 8 || strlen($telefone) < 10 || strlen($telefone) > 11) { // Explicação DevIN: Evita atualizações parciais com contato inválido.
            throw new InvalidArgumentException('Informe CEP (8 dígitos) e Telefone (10 ou 11 dígitos) válidos.'); // Explicação DevIN: Exige contato coerente.
        } // Explicação DevIN: Finaliza a validação de contato.
    } // Explicação DevIN: Finaliza campos de pessoa e empresa.

    $conn = getDatabaseConnection(); // Explicação DevIN: Usa uma única conexão MySQL para leitura, validação e escrita.
    $foto = null; // Explicação DevIN: Armazena somente o caminho de uma foto validada.
    $saved = false; // Explicação DevIN: Impede deixar upload órfão quando a transação falhar.
    $transaction = false; // Explicação DevIN: Indica se ainda existe uma transação para reverter.
    $lockName = null; // Explicação DevIN: Identifica o bloqueio temporário para o novo endereço.
    try { // Explicação DevIN: Protege todo o conjunto de operações por tratamento de falhas.
        $conn->begin_transaction(); // Explicação DevIN: Inicia atualização atômica no banco.
        $transaction = true; // Explicação DevIN: Habilita rollback se alguma etapa falhar.
        $stmt = $conn->prepare("SELECT nome, email, senha_hash FROM {$table} WHERE {$idColumn} = ? FOR UPDATE"); // Explicação DevIN: Bloqueia a conta específica durante a edição.
        $stmt->bind_param('i', $id); // Explicação DevIN: Usa ID autenticado, nunca o ID enviado pelo navegador.
        $stmt->execute(); // Explicação DevIN: Obtém a versão atual da conta dentro da transação.
        $current = $stmt->get_result()->fetch_assoc(); // Explicação DevIN: Lê somente os campos necessários.
        $stmt->close(); // Explicação DevIN: Libera o comando preparado.
        if (!$current) { // Explicação DevIN: Impede editar uma conta removida.
            throw new RuntimeException('Perfil não encontrado.'); // Explicação DevIN: Preserva integridade sem criar novas contas.
        } // Explicação DevIN: Encerra a verificação de existência.

        $emailChanged = strcasecmp((string) $current['email'], $email) !== 0; // Explicação DevIN: Detecta alteração efetiva de login.
        if ($emailChanged) { // Explicação DevIN: Aplica validações reforçadas apenas à troca de e-mail.
            $password = (string) ($data['senha_atual'] ?? ''); // Explicação DevIN: Lê a senha atual somente da requisição.
            if ($password === '' || !password_verify($password, (string) ($current['senha_hash'] ?? ''))) { // Explicação DevIN: Exige a senha correta antes de alterar a identidade.
                throw new InvalidArgumentException('Confirme sua senha atual para alterar o e-mail.'); // Explicação DevIN: Não altera o e-mail quando não houver confirmação.
            } // Explicação DevIN: Finaliza a autenticação da alteração.
            $lockName = 'devin_profile_email_' . substr(hash('sha256', $email), 0, 40); // Explicação DevIN: Nome curto e sem dados pessoais para sincronizar atualizações.
            $mutex = $conn->prepare('SELECT GET_LOCK(?, 5) AS acquired'); // Explicação DevIN: Serializa alterações concorrentes ao mesmo e-mail entre perfis.
            $mutex->bind_param('s', $lockName); // Explicação DevIN: Vincula o identificador sem interpolar entrada de usuário.
            $mutex->execute(); // Explicação DevIN: Aguarda no máximo cinco segundos pelo bloqueio.
            $acquired = $mutex->get_result()->fetch_assoc(); // Explicação DevIN: Verifica se o bloqueio foi concedido.
            $mutex->close(); // Explicação DevIN: Fecha a consulta de bloqueio.
            if ((int) ($acquired['acquired'] ?? 0) !== 1) { // Explicação DevIN: Evita conflitos quando outra alteração está em andamento.
                $lockName = null; // Explicação DevIN: Não tenta liberar bloqueio que não foi adquirido.
                throw new RuntimeException('Outra atualização de e-mail está em andamento. Tente novamente.'); // Explicação DevIN: Solicita nova tentativa sem sobrescrever dados.
            } // Explicação DevIN: Finaliza a aquisição do bloqueio.
            ensureUniqueEmail($tipo, $id, $email, $conn); // Explicação DevIN: Impede reutilizar endereços de pessoa, empresa ou ADM.
        } // Explicação DevIN: Encerra as validações de identidade.

        $column = $conn->prepare("SELECT CHARACTER_MAXIMUM_LENGTH AS maxlen FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = 'email' LIMIT 1"); // Explicação DevIN: Consulta o tamanho real da coluna na instalação MySQL.
        $column->bind_param('s', $table); // Explicação DevIN: Usa apenas tabela do mapa autorizado.
        $column->execute(); // Explicação DevIN: Lê metadados sem alterar a estrutura do banco.
        $limit = $column->get_result()->fetch_assoc(); // Explicação DevIN: Descobre o limite do cadastro em produção.
        $column->close(); // Explicação DevIN: Finaliza a consulta dos metadados.
        if (!$limit || (int) ($limit['maxlen'] ?? 0) < strlen($email)) { // Explicação DevIN: Evita truncamento e inconsistência do e-mail.
            throw new InvalidArgumentException('O e-mail ultrapassa o tamanho permitido pelo cadastro. Informe um endereço menor.'); // Explicação DevIN: Não modifica nenhum dado.
        } // Explicação DevIN: Finaliza a validação do esquema existente.

        $foto = $tipo === 'adm' ? null : saveProfilePhoto($tipo, $id, $upload); // Explicação DevIN: Valida o anexo somente depois da identidade.
        if ($tipo === 'adm') { // Explicação DevIN: Administradores alteram somente nome e e-mail.
            $stmt = $conn->prepare("UPDATE {$table} SET nome = ?, email = ? WHERE {$idColumn} = ?"); // Explicação DevIN: Evita afetar outros registros.
            $stmt->bind_param('ssi', $nome, $email, $id); // Explicação DevIN: Vincula somente campos validados.
        } elseif ($foto === null) { // Explicação DevIN: Não altera foto quando não há upload.
            $stmt = $conn->prepare("UPDATE {$table} SET nome = ?, email = ?, cep = ?, telefone = ? WHERE {$idColumn} = ?"); // Explicação DevIN: Atualiza contato e identidade na mesma transação.
            $stmt->bind_param('ssssi', $nome, $email, $cep, $telefone, $id); // Explicação DevIN: Evita interpolar conteúdo no SQL.
        } else { // Explicação DevIN: Atualiza foto validada junto dos demais campos.
            $stmt = $conn->prepare("UPDATE {$table} SET nome = ?, email = ?, cep = ?, telefone = ?, foto = ? WHERE {$idColumn} = ?"); // Explicação DevIN: Grava somente campos conhecidos.
            $stmt->bind_param('sssssi', $nome, $email, $cep, $telefone, $foto, $id); // Explicação DevIN: Vincula o caminho protegido da foto.
        } // Explicação DevIN: Conclui a montagem do UPDATE.
        $stmt->execute(); // Explicação DevIN: Executa operação SQL dentro da transação.
        $stmt->close(); // Explicação DevIN: Libera o comando antes de validar o resultado.
        $stmt = $conn->prepare("SELECT nome, email FROM {$table} WHERE {$idColumn} = ? LIMIT 1"); // Explicação DevIN: Confere os dados persistidos antes de confirmar.
        $stmt->bind_param('i', $id); // Explicação DevIN: Lê a mesma conta autenticada.
        $stmt->execute(); // Explicação DevIN: Consulta identidade efetivamente armazenada.
        $persisted = $stmt->get_result()->fetch_assoc(); // Explicação DevIN: Obtém valores retornados pelo MySQL.
        $stmt->close(); // Explicação DevIN: Finaliza a conferência.
        if (!$persisted || $persisted['email'] !== $email || $persisted['nome'] !== $nome) { // Explicação DevIN: Impede sucesso falso em caso de truncamento ou trigger.
            throw new RuntimeException('O banco não confirmou os dados do perfil. Nenhuma alteração foi aplicada.'); // Explicação DevIN: Garante rollback.
        } // Explicação DevIN: Conclui a conferência do banco.
        $conn->commit(); // Explicação DevIN: Torna todas as mudanças visíveis de forma atômica.
        $transaction = false; // Explicação DevIN: Impede rollback após confirmação.
        $saved = true; // Explicação DevIN: Preserva o arquivo anexado após persistência confirmada.
        return ['nome' => (string) $persisted['nome'], 'email' => (string) $persisted['email'], 'email_alterado' => $emailChanged]; // Explicação DevIN: Informa os dados reais ao controlador HTTP.
    } catch (mysqli_sql_exception $error) { // Explicação DevIN: Traduz erros comuns do banco em mensagens seguras.
        if ((int) $error->getCode() === 1062) { // Explicação DevIN: Identifica conflito em índice único de e-mail.
            throw new InvalidArgumentException('Este e-mail já está em uso por outra conta.', 0, $error); // Explicação DevIN: Não expõe detalhes internos do MySQL.
        } // Explicação DevIN: Finaliza a interpretação do conflito.
        error_log('Falha na atualização de perfil DevIN (MySQL ' . (int) $error->getCode() . ').'); // Explicação DevIN: Registra apenas o código técnico, sem dados pessoais.
        throw new RuntimeException('Não foi possível salvar as alterações. O cadastro foi mantido.', 0, $error); // Explicação DevIN: Evita exibir o SQL ao usuário.
    } finally { // Explicação DevIN: Executa limpeza mesmo em caso de erro ou retorno antecipado.
        if ($transaction) { // Explicação DevIN: Só desfaz a transação se ainda não houve commit.
            try { $conn->rollback(); } catch (Throwable $cleanupError) { error_log('Falha ao reverter perfil DevIN.'); } // Explicação DevIN: A limpeza não mascara a falha original.
        } // Explicação DevIN: Encerra a reversão da operação.
        if (is_string($lockName)) { // Explicação DevIN: Verifica se um bloqueio exclusivo foi adquirido.
            try { // Explicação DevIN: Uma falha na liberação não pode desfazer um commit confirmado.
                $unlock = $conn->prepare('SELECT RELEASE_LOCK(?)'); // Explicação DevIN: Libera bloqueio de e-mail após commit ou rollback.
                $unlock->bind_param('s', $lockName); // Explicação DevIN: Vincula o nome do bloqueio.
                $unlock->execute(); // Explicação DevIN: Libera a reserva do endereço.
                $unlock->close(); // Explicação DevIN: Fecha o comando de liberação.
            } catch (Throwable $cleanupError) { // Explicação DevIN: Registra somente a falha de limpeza.
                error_log('Falha ao liberar reserva de e-mail DevIN.'); // Explicação DevIN: Não exibe dados sensíveis nem causa falso erro após sucesso.
            } // Explicação DevIN: Encerra a liberação protegida.
        } // Explicação DevIN: Finaliza o desbloqueio de e-mail.
        $conn->close(); // Explicação DevIN: Fecha somente a conexão utilizada nesta operação.
        if (!$saved && is_string($foto) && str_starts_with($foto, 'uploads/profile-' . $tipo . '-' . $id . '-')) { // Explicação DevIN: Remove upload criado quando o banco não confirmou.
            @unlink(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . basename($foto)); // Explicação DevIN: Evita acumular fotos órfãs.
        } // Explicação DevIN: Finaliza a limpeza do arquivo.
    } // Explicação DevIN: Conclui transação e limpeza.
} // Explicação DevIN: Finaliza a atualização segura do perfil.

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
