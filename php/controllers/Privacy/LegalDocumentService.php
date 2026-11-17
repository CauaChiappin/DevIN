<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/controllers/Privacy/LegalDocumentService.php
 * Finalidade: legal document service.
 * Domínio: privacidade e proteção de dados.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


require_once __DIR__ . '/PrivacyContext.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

/**
 * Versões aprovadas devem ser publicadas em processo jurídico/editorial.
 * Esta classe NUNCA cria documentos legais ou inventa aceites.
 */
final class LegalDocumentService // Explicação DevIN: Declara o tipo LegalDocumentService e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    private const TYPES = ['terms', 'privacy', 'cookies']; // Explicação DevIN: Define membro, visibilidade ou método de uma classe.

    /** @return array{version_code:string,content_sha256:string} */
    // Detalhe DevIN: Método active: encapsula uma responsabilidade reutilizável do módulo no contexto de legal document service. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function active(mysqli $db, string $type): array // Explicação DevIN: Declara função/método active, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!in_array($type, self::TYPES, true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Tipo de documento inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
        $stmt = $db->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            'SELECT version_code, HEX(content_sha256) AS content_sha256 ' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            . 'FROM legal_document_versions WHERE document_type = ? ' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            . 'AND is_active = 1 AND published_at <= UTC_TIMESTAMP(6) LIMIT 2' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $stmt->bind_param('s', $type); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função active.
        if (count($rows) !== 1) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            // Falha fechada quando não há documento aprovado ou dois foram ativados.
            throw new RuntimeException('Documento legal ativo não configurado de modo único.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return $rows[0]; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /** Requer dados pré-aprovados; operação transacional de publicação manual. */
    // Detalhe DevIN: Método publishApproved: encapsula uma responsabilidade reutilizável do módulo no contexto de legal document service. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function publishApproved(mysqli $db, string $type, string $version, string $sha256Hex): void // Explicação DevIN: Declara função/método publishApproved, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!in_array($type, self::TYPES, true) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            || preg_match('/^[A-Za-z0-9._-]{1,40}$/D', $version) !== 1 // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            || preg_match('/^[a-fA-F0-9]{64}$/D', $sha256Hex) !== 1) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            throw new InvalidArgumentException('Versão ou hash do documento inválidos.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        // Detalhe DevIN: Inicia uma transação para manter alterações relacionadas consistentes.
        $db->begin_transaction(); // Explicação DevIN: Continua expressão ou bloco pertencente a função publishApproved.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
            $stmt = $db->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                'SELECT LOWER(HEX(content_sha256)) AS sha FROM legal_document_versions ' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                . 'WHERE document_type = ? AND version_code = ? FOR UPDATE' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $stmt->bind_param('ss', $type, $version); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $existing = $stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função publishApproved.
            // Detalhe DevIN: Compara hashes ou segredos sem diferenças de tempo evitáveis.
            if ($existing !== null && !hash_equals($existing['sha'], strtolower($sha256Hex))) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new DomainException('Não é permitido alterar o conteúdo de versão legal publicada.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
            $stmt = $db->prepare('UPDATE legal_document_versions SET is_active = 0 WHERE document_type = ?'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->bind_param('s', $type); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função publishApproved.

            // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
            $stmt = $db->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                'INSERT INTO legal_document_versions (document_type,version_code,content_sha256,published_at,is_active) ' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                . 'VALUES (?, ?, UNHEX(?), UTC_TIMESTAMP(6), 1) ' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                . 'ON DUPLICATE KEY UPDATE is_active = 1' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $stmt->bind_param('sss', $type, $version, $sha256Hex); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função publishApproved.
            // Detalhe DevIN: Confirma alterações realizadas no mesmo contexto transacional.
            $db->commit(); // Explicação DevIN: Continua expressão ou bloco pertencente a função publishApproved.
        } catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            // Detalhe DevIN: Desfaz alterações ainda não confirmadas quando ocorre falha.
            $db->rollback(); // Explicação DevIN: Continua expressão ou bloco pertencente a função publishApproved.
            throw $exception; // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
