<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/controllers/ProfileLanguagePreference.php
 * Finalidade: profile language preference.
 * Domínio: processamento no servidor.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


class ProfileLanguagePreference // Explicação DevIN: Declara o tipo ProfileLanguagePreference e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    private const DEFAULT_LANGUAGE = 'pt-BR'; // Explicação DevIN: Define membro, visibilidade ou método de uma classe.
    private const SUPPORTED_LANGUAGES = ['pt-BR', 'en', 'es']; // Explicação DevIN: Define membro, visibilidade ou método de uma classe.
    private const PROFILE_TYPES = ['pessoa', 'empresa', 'adm']; // Explicação DevIN: Define membro, visibilidade ou método de uma classe.

    // Detalhe DevIN: Método load: consulta e organiza dados sem presumir que o resultado seja único no contexto de profile language preference. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function load(mysqli $conn, string $tipo, int $id): string // Explicação DevIN: Declara função/método load, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        self::validateProfile($tipo, $id); // Explicação DevIN: Continua expressão ou bloco pertencente a função load.

        foreach (['preferencias_dashboard', 'preferencias_perfil'] as $tableName) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
                $stmt = $conn->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                    'SELECT idioma FROM ' . $tableName . ' WHERE ' . ( // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                        $tableName === 'preferencias_dashboard' // Explicação DevIN: Atribui um valor à variável PHP $tableName.
                            ? 'perfil_tipo = ? AND perfil_id = ?' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                            : 'tipo_perfil = ? AND perfil_id = ?' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    ) . ' LIMIT 1' // Explicação DevIN: Continua expressão ou bloco pertencente a função load.
                ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                $stmt->bind_param('si', $tipo, $id); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $preference = $stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
                $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função load.

                $language = $preference['idioma'] ?? self::DEFAULT_LANGUAGE; // Explicação DevIN: Atribui um valor à variável PHP $language.
                return in_array($language, self::SUPPORTED_LANGUAGES, true) // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
                    ? $language // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    : self::DEFAULT_LANGUAGE; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            } catch (mysqli_sql_exception $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
                if ((int) $exception->getCode() !== 1146) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    throw $exception; // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        return self::DEFAULT_LANGUAGE; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Método save: cria registros após checagem da entrada no contexto de profile language preference. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    public static function save(mysqli $conn, string $tipo, int $id, string $idioma): void // Explicação DevIN: Declara função/método save, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        self::validateProfile($tipo, $id); // Explicação DevIN: Continua expressão ou bloco pertencente a função save.
        if (!in_array($idioma, self::SUPPORTED_LANGUAGES, true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Idioma inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        foreach (['preferencias_dashboard', 'preferencias_perfil'] as $tableName) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            $conn->query( // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
                $tableName === 'preferencias_dashboard' // Explicação DevIN: Atribui um valor à variável PHP $tableName.
                    ? 'CREATE TABLE IF NOT EXISTS preferencias_dashboard (
                        perfil_tipo VARCHAR(16) NOT NULL,
                        perfil_id INT UNSIGNED NOT NULL,
                        idioma VARCHAR(5) NOT NULL DEFAULT \'pt-BR\',
                        atualizado_em TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        PRIMARY KEY (perfil_tipo, perfil_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci' // Explicação DevIN: Continua expressão ou bloco pertencente a função save.
                    : 'CREATE TABLE IF NOT EXISTS preferencias_perfil (
                        tipo_perfil VARCHAR(20) NOT NULL,
                        perfil_id INT NOT NULL,
                        idioma VARCHAR(10) NOT NULL DEFAULT \'pt-BR\',
                        PRIMARY KEY (tipo_perfil, perfil_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4' // Explicação DevIN: Continua expressão ou bloco pertencente a função save.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
            $stmt = $conn->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $tableName === 'preferencias_dashboard' // Explicação DevIN: Atribui um valor à variável PHP $tableName.
                    ? 'INSERT INTO preferencias_dashboard (perfil_tipo, perfil_id, idioma)
                       VALUES (?, ?, ?)
                       ON DUPLICATE KEY UPDATE idioma = VALUES(idioma)' // Explicação DevIN: Continua expressão ou bloco pertencente a função save.
                    : 'INSERT INTO preferencias_perfil (tipo_perfil, perfil_id, idioma)
                       VALUES (?, ?, ?)
                       ON DUPLICATE KEY UPDATE idioma = VALUES(idioma)' // Explicação DevIN: Continua expressão ou bloco pertencente a função save.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $stmt->bind_param('sis', $tipo, $id, $idioma); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função save.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Detalhe DevIN: Método validateProfile: verifica condições antes de liberar o próximo passo no contexto de profile language preference. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
    private static function validateProfile(string $tipo, int $id): void // Explicação DevIN: Declara função/método validateProfile, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!in_array($tipo, self::PROFILE_TYPES, true) || $id <= 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Perfil inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
