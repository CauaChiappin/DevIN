<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/controllers/Privacy/ConsentService.php
 * Finalidade: consent service.
 * Domínio: privacidade e proteção de dados.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


require_once __DIR__ . '/PrivacyContext.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/LegalDocumentService.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

/**
 * Todos os métodos esperam identidades derivadas da sessão validada ou do
 * cadastro recém-criado. Não aceitar account_id enviado pelo cliente.
 */
final class ConsentService // Explicação DevIN: Declara o tipo ConsentService e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    /**
     * Insere o aceite explícito em conexão já transacionada pelo cadastro.
     * Não inicia/fecha transação: o caller deve salvar a conta e o aceite
     * atomicamente. Não deve ser chamado sem checkbox verdadeiro.
     */
    // Detalhe DevIN: Método recordLegalAcceptance: registra evidências para acompanhamento no contexto de consent service. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function recordLegalAcceptance( // Explicação DevIN: Declara função/método recordLegalAcceptance, com seus parâmetros e comportamento.
        mysqli $db, // Explicação DevIN: Continua expressão ou bloco pertencente a função recordLegalAcceptance.
        string $accountType, // Explicação DevIN: Continua expressão ou bloco pertencente a função recordLegalAcceptance.
        int $accountId, // Explicação DevIN: Continua expressão ou bloco pertencente a função recordLegalAcceptance.
        bool $termsChecked, // Explicação DevIN: Continua expressão ou bloco pertencente a função recordLegalAcceptance.
        string $shownTermsVersion, // Explicação DevIN: Continua expressão ou bloco pertencente a função recordLegalAcceptance.
        string $shownPrivacyVersion, // Explicação DevIN: Continua expressão ou bloco pertencente a função recordLegalAcceptance.
        string $source = 'registration' // Explicação DevIN: Continua expressão ou bloco pertencente a função recordLegalAcceptance.
    ): int { // Explicação DevIN: Continua expressão ou bloco pertencente a função recordLegalAcceptance.
        PrivacyContext::validateAccount($accountType, $accountId); // Explicação DevIN: Continua expressão ou bloco pertencente a função recordLegalAcceptance.
        if (!$termsChecked || !in_array($source, ['registration','renewal'], true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('É necessário aceite expresso dos Termos e origem válida.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        PrivacyContext::requireAccount($db, $accountType, $accountId); // Explicação DevIN: Continua expressão ou bloco pertencente a função recordLegalAcceptance.
        $terms = LegalDocumentService::active($db, 'terms'); // Explicação DevIN: Atribui um valor à variável PHP $terms.
        $privacy = LegalDocumentService::active($db, 'privacy'); // Explicação DevIN: Atribui um valor à variável PHP $privacy.
        // O backend não atribui ao titular uma versão que ele nunca viu.
        if ($shownTermsVersion !== $terms['version_code'] // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            || $shownPrivacyVersion !== $privacy['version_code']) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            throw new DomainException('Documentos legais atualizados. Recarregue e aceite a versão exibida.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $ipHash = PrivacyContext::ipHash(PrivacyContext::clientIp()); // Explicação DevIN: Atribui um valor à variável PHP $ipHash.
        $ua = PrivacyContext::userAgent(); // Explicação DevIN: Atribui um valor à variável PHP $ua.

        // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
        $stmt = $db->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            'INSERT INTO user_legal_acceptances ' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            . '(account_type,account_id,terms_version,privacy_notice_version,accepted_at,ip_hash,user_agent,capture_source) ' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            . 'VALUES (?,?,?,?,UTC_TIMESTAMP(6),?,?,?)' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $termsVersion = $terms['version_code']; // Explicação DevIN: Atribui um valor à variável PHP $termsVersion.
        $privacyVersion = $privacy['version_code']; // Explicação DevIN: Atribui um valor à variável PHP $privacyVersion.
        $stmt->bind_param('sisssss', $accountType, $accountId, $termsVersion, $privacyVersion, $ipHash, $ua, $source); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $id = (int) $db->insert_id; // Explicação DevIN: Atribui um valor à variável PHP $id.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função recordLegalAcceptance.
        return $id; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /** Ausência de preferência significa opt-out; preferências não herdam do cadastro. */
    // Detalhe DevIN: Método isGranted: verifica condições antes de liberar o próximo passo no contexto de consent service. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function isGranted(mysqli $db, string $accountType, int $accountId, string $purpose): bool // Explicação DevIN: Declara função/método isGranted, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        PrivacyContext::validateAccount($accountType, $accountId); // Explicação DevIN: Continua expressão ou bloco pertencente a função isGranted.
        PrivacyContext::validatePurpose($purpose); // Explicação DevIN: Continua expressão ou bloco pertencente a função isGranted.
        // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
        $stmt = $db->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            'SELECT is_granted FROM user_consent_preferences ' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            . 'WHERE account_type = ? AND account_id = ? AND purpose = ? LIMIT 1' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $stmt->bind_param('sis', $accountType, $accountId, $purpose); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $row = $stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função isGranted.
        return $row !== null && (int) $row['is_granted'] === 1; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    /**
     * Mudança explícita da preferência; atualiza estado e histórico na mesma
     * transação, protegendo contra perda de atualizações concorrentes.
     * Retorna false quando não havia mudança efetiva.
     */
    // Detalhe DevIN: Método setPreference: encapsula uma responsabilidade reutilizável do módulo no contexto de consent service. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function setPreference( // Explicação DevIN: Declara função/método setPreference, com seus parâmetros e comportamento.
        mysqli $db, // Explicação DevIN: Continua expressão ou bloco pertencente a função setPreference.
        string $accountType, // Explicação DevIN: Continua expressão ou bloco pertencente a função setPreference.
        int $accountId, // Explicação DevIN: Continua expressão ou bloco pertencente a função setPreference.
        string $purpose, // Explicação DevIN: Continua expressão ou bloco pertencente a função setPreference.
        bool $grant // Explicação DevIN: Continua expressão ou bloco pertencente a função setPreference.
    ): bool { // Explicação DevIN: Continua expressão ou bloco pertencente a função setPreference.
        PrivacyContext::validateAccount($accountType, $accountId); // Explicação DevIN: Continua expressão ou bloco pertencente a função setPreference.
        PrivacyContext::validatePurpose($purpose); // Explicação DevIN: Continua expressão ou bloco pertencente a função setPreference.
        $privacy = LegalDocumentService::active($db, 'privacy'); // Explicação DevIN: Atribui um valor à variável PHP $privacy.
        $ipHash = PrivacyContext::ipHash(PrivacyContext::clientIp()); // Explicação DevIN: Atribui um valor à variável PHP $ipHash.
        $ua = PrivacyContext::userAgent(); // Explicação DevIN: Atribui um valor à variável PHP $ua.
        // Detalhe DevIN: Inicia uma transação para manter alterações relacionadas consistentes.
        $db->begin_transaction(); // Explicação DevIN: Continua expressão ou bloco pertencente a função setPreference.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            PrivacyContext::requireAccount($db, $accountType, $accountId, true); // Explicação DevIN: Continua expressão ou bloco pertencente a função setPreference.
            // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
            $stmt = $db->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                'SELECT is_granted FROM user_consent_preferences ' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                . 'WHERE account_type = ? AND account_id = ? AND purpose = ? LIMIT 1 FOR UPDATE' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $stmt->bind_param('sis', $accountType, $accountId, $purpose); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $row = $stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função setPreference.
            // Desativado por padrão: não grava um evento 'revoked' inexistente.
            if (($row === null && !$grant) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                || ($row !== null && ((int) $row['is_granted'] === 1) === $grant)) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                // Detalhe DevIN: Confirma alterações realizadas no mesmo contexto transacional.
                $db->commit(); // Explicação DevIN: Continua expressão ou bloco pertencente a função setPreference.
                return false; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $value = $grant ? 1 : 0; // Explicação DevIN: Atribui um valor à variável PHP $value.
            $policyVersion = $privacy['version_code']; // Explicação DevIN: Atribui um valor à variável PHP $policyVersion.
            // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
            $stmt = $db->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                'INSERT INTO user_consent_preferences ' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                . '(account_type,account_id,purpose,is_granted,policy_version,updated_at) ' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                . 'VALUES (?,?,?,?,?,UTC_TIMESTAMP(6)) ON DUPLICATE KEY UPDATE ' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                . 'is_granted = VALUES(is_granted), policy_version = VALUES(policy_version), updated_at = UTC_TIMESTAMP(6)' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $stmt->bind_param('sisis', $accountType, $accountId, $purpose, $value, $policyVersion); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função setPreference.

            $event = $grant ? 'granted' : 'revoked'; // Explicação DevIN: Atribui um valor à variável PHP $event.
            // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
            $stmt = $db->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                'INSERT INTO user_consent_events ' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                . '(account_type,account_id,purpose,event_type,policy_version,happened_at,ip_hash,user_agent) ' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                . 'VALUES (?,?,?,?,?,UTC_TIMESTAMP(6),?,?)' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $stmt->bind_param('sisssss', $accountType, $accountId, $purpose, $event, $policyVersion, $ipHash, $ua); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função setPreference.
            // Detalhe DevIN: Confirma alterações realizadas no mesmo contexto transacional.
            $db->commit(); // Explicação DevIN: Continua expressão ou bloco pertencente a função setPreference.
            return true; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            // Detalhe DevIN: Desfaz alterações ainda não confirmadas quando ocorre falha.
            $db->rollback(); // Explicação DevIN: Continua expressão ou bloco pertencente a função setPreference.
            throw $exception; // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
