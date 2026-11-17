<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/controllers/ResumeDocument.php
 * Finalidade: resume document.
 * Domínio: processamento no servidor.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


final class ResumeDocument // Explicação DevIN: Declara o tipo ResumeDocument e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    // Detalhe DevIN: Método ensureSchema: encapsula uma responsabilidade reutilizável do módulo no contexto de resume document. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function ensureSchema(mysqli $connection): void // Explicação DevIN: Declara função/método ensureSchema, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $connection->query( // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            'CREATE TABLE IF NOT EXISTS curriculo_documentos (
                id_pessoa INT NOT NULL PRIMARY KEY,
                conteudo_json LONGTEXT NULL,
                arquivo_nome VARCHAR(255) NULL,
                arquivo_caminho VARCHAR(255) NULL,
                mime_type VARCHAR(100) NULL,
                atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci' // Explicação DevIN: Continua expressão ou bloco pertencente a função ensureSchema.
        ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Método find: consulta e organiza dados sem presumir que o resultado seja único no contexto de resume document. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function find(mysqli $connection, int $personId): array // Explicação DevIN: Declara função/método find, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        self::ensureSchema($connection); // Explicação DevIN: Continua expressão ou bloco pertencente a função find.
        // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
        $statement = $connection->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            'SELECT conteudo_json, arquivo_nome, arquivo_caminho, mime_type, atualizado_em
             FROM curriculo_documentos WHERE id_pessoa = ? LIMIT 1' // Explicação DevIN: Continua expressão ou bloco pertencente a função find.
        ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $statement->bind_param('i', $personId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $statement->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $document = $statement->get_result()->fetch_assoc() ?? []; // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $statement->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função find.
        return $document; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Método hasResume: verifica condições antes de liberar o próximo passo no contexto de resume document. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function hasResume(mysqli $connection, int $personId): bool // Explicação DevIN: Declara função/método hasResume, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
        $statement = $connection->prepare('SELECT id_curriculo FROM curriculo WHERE id_pessoa = ? LIMIT 1'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $statement->bind_param('i', $personId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $statement->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $hasLegacyResume = $statement->get_result()->num_rows > 0; // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $statement->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função hasResume.
        if ($hasLegacyResume) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            return true; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $document = self::find($connection, $personId); // Explicação DevIN: Atribui um valor à variável PHP $document.
        return !empty($document['arquivo_caminho']) || !empty($document['conteudo_json']); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Método saveGenerated: cria registros após checagem da entrada no contexto de resume document. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function saveGenerated(mysqli $connection, int $personId, array $resume): void // Explicação DevIN: Declara função/método saveGenerated, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        self::ensureSchema($connection); // Explicação DevIN: Continua expressão ou bloco pertencente a função saveGenerated.
        $content = json_encode($resume, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
        // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
        $statement = $connection->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            'INSERT INTO curriculo_documentos (id_pessoa, conteudo_json) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE conteudo_json = VALUES(conteudo_json)' // Explicação DevIN: Continua expressão ou bloco pertencente a função saveGenerated.
        ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $statement->bind_param('is', $personId, $content); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $statement->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $statement->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função saveGenerated.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Método replaceUpload: encapsula uma responsabilidade reutilizável do módulo no contexto de resume document. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function replaceUpload( // Explicação DevIN: Declara função/método replaceUpload, com seus parâmetros e comportamento.
        mysqli $connection, // Explicação DevIN: Continua expressão ou bloco pertencente a função replaceUpload.
        int $personId, // Explicação DevIN: Continua expressão ou bloco pertencente a função replaceUpload.
        string $originalName, // Explicação DevIN: Continua expressão ou bloco pertencente a função replaceUpload.
        string $relativePath, // Explicação DevIN: Continua expressão ou bloco pertencente a função replaceUpload.
        string $mimeType // Explicação DevIN: Continua expressão ou bloco pertencente a função replaceUpload.
    ): ?string { // Explicação DevIN: Continua expressão ou bloco pertencente a função replaceUpload.
        self::ensureSchema($connection); // Explicação DevIN: Continua expressão ou bloco pertencente a função replaceUpload.
        $previous = self::find($connection, $personId); // Explicação DevIN: Atribui um valor à variável PHP $previous.
        // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
        $statement = $connection->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            'INSERT INTO curriculo_documentos (id_pessoa, conteudo_json, arquivo_nome, arquivo_caminho, mime_type)
             VALUES (?, NULL, ?, ?, ?)
             ON DUPLICATE KEY UPDATE conteudo_json = NULL, arquivo_nome = VALUES(arquivo_nome),
             arquivo_caminho = VALUES(arquivo_caminho), mime_type = VALUES(mime_type)' // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $statement->bind_param('isss', $personId, $originalName, $relativePath, $mimeType); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $statement->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $statement->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função replaceUpload.

        $previousPath = (string) ($previous['arquivo_caminho'] ?? ''); // Explicação DevIN: Atribui um valor à variável PHP $previousPath.
        return $previousPath !== '' && $previousPath !== $relativePath ? $previousPath : null; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /** Remove o currículo estruturado, o documento e o arquivo enviado da pessoa. */
    // Detalhe DevIN: Método deleteAll: trata remoção sujeita às permissões e integridade do banco no contexto de resume document. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function deleteAll(mysqli $connection, int $personId): void // Explicação DevIN: Declara função/método deleteAll, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $previous = self::find($connection, $personId); // Explicação DevIN: Atribui um valor à variável PHP $previous.

        // Detalhe DevIN: Inicia uma transação para manter alterações relacionadas consistentes.
        $connection->begin_transaction(); // Explicação DevIN: Continua expressão ou bloco pertencente a função deleteAll.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            foreach (['DELETE FROM curriculo_documentos WHERE id_pessoa = ?', 'DELETE FROM curriculo WHERE id_pessoa = ?'] as $sql) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
                $statement = $connection->prepare($sql); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $statement->bind_param('i', $personId); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $statement->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $statement->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função deleteAll.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            // Detalhe DevIN: Confirma alterações realizadas no mesmo contexto transacional.
            $connection->commit(); // Explicação DevIN: Continua expressão ou bloco pertencente a função deleteAll.
        } catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            // Detalhe DevIN: Desfaz alterações ainda não confirmadas quando ocorre falha.
            $connection->rollback(); // Explicação DevIN: Continua expressão ou bloco pertencente a função deleteAll.
            throw $exception; // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        self::removeFile((string) ($previous['arquivo_caminho'] ?? '') ?: null); // Explicação DevIN: Continua expressão ou bloco pertencente a função deleteAll.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Método storageDirectory: encapsula uma responsabilidade reutilizável do módulo no contexto de resume document. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function storageDirectory(): string // Explicação DevIN: Declara função/método storageDirectory, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $directory = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'resumes'; // Explicação DevIN: Atribui um valor à variável PHP $directory.
        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new RuntimeException('Não foi possível preparar o armazenamento do currículo.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return $directory; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Método absolutePath: encapsula uma responsabilidade reutilizável do módulo no contexto de resume document. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function absolutePath(string $relativePath): ?string // Explicação DevIN: Declara função/método absolutePath, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (preg_match('~^storage/resumes/[a-f0-9]{32}\.(?:pdf|doc|docx)$~', $relativePath) !== 1) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            return null; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath); // Explicação DevIN: Atribui um valor à variável PHP $path.
        return is_file($path) ? $path : null; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Método removeFile: trata remoção sujeita às permissões e integridade do banco no contexto de resume document. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function removeFile(?string $relativePath): void // Explicação DevIN: Declara função/método removeFile, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if ($relativePath === null) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $path = self::absolutePath($relativePath); // Explicação DevIN: Atribui um valor à variável PHP $path.
        if ($path !== null) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            @unlink($path); // Explicação DevIN: Continua expressão ou bloco pertencente a função removeFile.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.