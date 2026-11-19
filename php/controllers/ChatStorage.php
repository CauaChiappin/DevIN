<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/controllers/ChatStorage.php
 * Finalidade: consultar e registrar mensagens de usuários.
 * Domínio: processamento no servidor.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


/**
 * MÓDULO DEVIN — CHAT STORAGE.
 * Responsabilidade: parte do subsistema de dados de perfis, currículos, vagas e interações do DevIN.
 * Leia os métodos abaixo em sequência: entrada validada -> processamento ->
 * resposta ao chamador. Comentários detalhados descrevem decisões, limites
 * e efeitos colaterais; os comentários gerados por linha foram preservados.
 * Atenção: chamadas de banco e ações administrativas exigem autorização no
 * endpoint controlador; métodos isolados não constituem permissão para a IA.
 */


// Detalhamento DevIN: centraliza a operação «mensagemConclusaoReportExpirada» no contexto de dados de perfis, currículos, vagas e interações do DevIN; os argumentos recebidos definem o alvo ou os dados processados, e o retorno/erro sinaliza o resultado ao chamador.
// Entrada e saída: confira a assinatura abaixo; falhas devem ser tratadas pelo controlador que chamou esta rotina.
// Detalhe DevIN: Método mensagemConclusaoReportExpirada: encapsula uma responsabilidade reutilizável do módulo no contexto de consultar e registrar mensagens de usuários. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function mensagemConclusaoReportExpirada(array $mensagem, ?int $agora = null): bool // Explicação DevIN: Declara função/método mensagemConclusaoReportExpirada, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if (strpos((string) ($mensagem['chave_evento'] ?? ''), 'job-report:') !== 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        return false; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    $expiraEm = $mensagem['expira_em'] ?? null; // Explicação DevIN: Atribui um valor à variável PHP $expiraEm.
    if (!is_string($expiraEm) || $expiraEm === '') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $enviadaEm = $mensagem['enviada_em'] ?? null; // Explicação DevIN: Atribui um valor à variável PHP $enviadaEm.
        if (!is_string($enviadaEm) || $enviadaEm === '') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            return false; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $instanteEnvio = strtotime($enviadaEm); // Explicação DevIN: Atribui um valor à variável PHP $instanteEnvio.
        if ($instanteEnvio === false) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            return false; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $instanteExpiracao = $instanteEnvio + 86400; // Explicação DevIN: Atribui um valor à variável PHP $instanteExpiracao.
    } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
        $instanteExpiracao = strtotime($expiraEm); // Explicação DevIN: Atribui um valor à variável PHP $instanteExpiracao.
        if ($instanteExpiracao === false) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            return false; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    return $instanteExpiracao <= ($agora ?? time()); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

/** @return mixed Retorna o resultado produzido pela operação sobre o armazenamento. */
// Detalhamento DevIN: centraliza a operação «acessarArmazenamentoMensagens» no contexto de dados de perfis, currículos, vagas e interações do DevIN; os argumentos recebidos definem o alvo ou os dados processados, e o retorno/erro sinaliza o resultado ao chamador.
// Entrada e saída: confira a assinatura abaixo; falhas devem ser tratadas pelo controlador que chamou esta rotina.
// Detalhe DevIN: Método acessarArmazenamentoMensagens: encapsula uma responsabilidade reutilizável do módulo no contexto de consultar e registrar mensagens de usuários. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function acessarArmazenamentoMensagens(callable $operacao, string $recurso = 'mensagens') // Explicação DevIN: Declara função/método acessarArmazenamentoMensagens, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if (!in_array($recurso, ['mensagens', 'conversas'], true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new InvalidArgumentException('Armazenamento de mensagens inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    $flag = $_ENV['DEVIN_CHAT_MYSQL_ENABLED'] ?? $_SERVER['DEVIN_CHAT_MYSQL_ENABLED'] ?? getenv('DEVIN_CHAT_MYSQL_ENABLED'); // Explicação DevIN: Atribui um valor à variável PHP $flag.
    if (in_array(strtolower((string) $flag), ['1', 'true', 'yes', 'on'], true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        return acessarArmazenamentoMensagensMysql($operacao, $recurso); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    $caminho = sys_get_temp_dir() . DIRECTORY_SEPARATOR // Explicação DevIN: Atribui um valor à variável PHP $caminho.
        . 'devin-' . $recurso . '-' . hash('sha256', dirname(__DIR__, 2)) . '.json'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    $arquivo = fopen($caminho, 'c+'); // Explicação DevIN: Atribui um valor à variável PHP $arquivo.
    if ($arquivo === false) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new RuntimeException('Não foi possível abrir o armazenamento temporário de mensagens.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        if (!chmod($caminho, 0600)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new RuntimeException('Não foi possível proteger o armazenamento temporário de mensagens.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!flock($arquivo, LOCK_EX)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new RuntimeException('Não foi possível bloquear o armazenamento temporário de mensagens.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        rewind($arquivo); // Explicação DevIN: Continua expressão ou bloco pertencente a função acessarArmazenamentoMensagens.
        $conteudo = stream_get_contents($arquivo); // Explicação DevIN: Atribui um valor à variável PHP $conteudo.
        if ($conteudo === false) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new RuntimeException('Não foi possível ler o armazenamento temporário de mensagens.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $mensagens = $conteudo === '' // Explicação DevIN: Atribui um valor à variável PHP $mensagens.
            ? [] // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            : json_decode($conteudo, true, 512, JSON_THROW_ON_ERROR); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
        if (!is_array($mensagens)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new RuntimeException('O armazenamento temporário de mensagens está inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if ($recurso === 'mensagens') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $mensagensAtuais = count($mensagens); // Explicação DevIN: Atribui um valor à variável PHP $mensagensAtuais.
            $mensagens = array_values(array_filter( // Explicação DevIN: Atribui um valor à variável PHP $mensagens.
                $mensagens, // Explicação DevIN: Continua expressão ou bloco pertencente a função acessarArmazenamentoMensagens.
                static fn (array $mensagem): bool => !mensagemConclusaoReportExpirada($mensagem) // Explicação DevIN: Continua expressão ou bloco pertencente a função acessarArmazenamentoMensagens.
            )); // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
            if (count($mensagens) !== $mensagensAtuais) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                $conteudo = ''; // Explicação DevIN: Atribui um valor à variável PHP $conteudo.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $resultado = $operacao($mensagens); // Explicação DevIN: Atribui um valor à variável PHP $resultado.
        $novoConteudo = json_encode($mensagens, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
        if ($novoConteudo !== $conteudo) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            rewind($arquivo); // Explicação DevIN: Continua expressão ou bloco pertencente a função acessarArmazenamentoMensagens.
            if (!ftruncate($arquivo, 0)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new RuntimeException('Não foi possível salvar a mensagem.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $offset = 0; // Explicação DevIN: Atribui um valor à variável PHP $offset.
            while ($offset < strlen($novoConteudo)) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                $escritos = fwrite($arquivo, substr($novoConteudo, $offset)); // Explicação DevIN: Atribui um valor à variável PHP $escritos.
                if ($escritos === false || $escritos === 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    throw new RuntimeException('Não foi possível salvar a mensagem.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                $offset += $escritos; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            if (!fflush($arquivo)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new RuntimeException('Não foi possível salvar a mensagem.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        flock($arquivo, LOCK_UN); // Explicação DevIN: Continua expressão ou bloco pertencente a função acessarArmazenamentoMensagens.
        return $resultado; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
        fclose($arquivo); // Explicação DevIN: Continua expressão ou bloco pertencente a função acessarArmazenamentoMensagens.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

/**
 * Armazenamento persistente opcional: a interface de callbacks é compatível com o JSON antigo.
 * A primeira operação importa a cópia local existente para uma linha InnoDB bloqueada.
 * Não elimina o arquivo legado e não usa fallback silencioso quando o MySQL falha.
 */
function acessarArmazenamentoMensagensMysql(callable $operacao, string $recurso) // Explicação DevIN: Declara função/método acessarArmazenamentoMensagensMysql, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    require_once __DIR__ . '/../config/database.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
    $conn = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $conn.
    $inTransaction = false; // Explicação DevIN: Atribui um valor à variável PHP $inTransaction.
    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        $conn->begin_transaction(); // Explicação DevIN: Continua expressão ou bloco pertencente a função acessarArmazenamentoMensagensMysql.
        $inTransaction = true; // Explicação DevIN: Atribui um valor à variável PHP $inTransaction.
        $legacyPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR // Explicação DevIN: Atribui um valor à variável PHP $legacyPath.
            . 'devin-' . $recurso . '-' . hash('sha256', dirname(__DIR__, 2)) . '.json'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        $legacyData = '[]'; // Explicação DevIN: Atribui um valor à variável PHP $legacyData.
        if (file_exists($legacyPath)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $legacyFile = fopen($legacyPath, 'rb'); // Explicação DevIN: Atribui um valor à variável PHP $legacyFile.
            if ($legacyFile === false) throw new RuntimeException('O arquivo legado de mensagens está inacessível.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                if (!flock($legacyFile, LOCK_SH)) throw new RuntimeException('Não foi possível proteger a importação do histórico.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                $content = stream_get_contents($legacyFile); // Explicação DevIN: Atribui um valor à variável PHP $content.
                if ($content === false) throw new RuntimeException('Não foi possível ler o histórico legado.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                if ($content !== '') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
                    if (!is_array($decoded)) throw new RuntimeException('O histórico legado não contém um array válido.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    $legacyData = $content; // Explicação DevIN: Atribui um valor à variável PHP $legacyData.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
                flock($legacyFile, LOCK_UN); // Explicação DevIN: Continua expressão ou bloco pertencente a função acessarArmazenamentoMensagensMysql.
                fclose($legacyFile); // Explicação DevIN: Continua expressão ou bloco pertencente a função acessarArmazenamentoMensagensMysql.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $insert = $conn->prepare('INSERT IGNORE INTO devin_chat_state (resource, payload) VALUES (?, ?)'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $insert->bind_param('ss', $recurso, $legacyData); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $insert->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $insert->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função acessarArmazenamentoMensagensMysql.
        $select = $conn->prepare('SELECT payload FROM devin_chat_state WHERE resource = ? FOR UPDATE'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $select->bind_param('s', $recurso); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $select->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
        $row = $select->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
        $select->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função acessarArmazenamentoMensagensMysql.
        if (!$row) throw new RuntimeException('Armazenamento persistente de mensagens indisponível.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $oldPayload = (string) $row['payload']; // Explicação DevIN: Atribui um valor à variável PHP $oldPayload.
        $messages = json_decode($oldPayload, true, 512, JSON_THROW_ON_ERROR); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
        if (!is_array($messages)) throw new RuntimeException('Histórico persistente de mensagens inválido.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if ($recurso === 'mensagens') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $messages = array_values(array_filter($messages, static fn (array $m): bool => !mensagemConclusaoReportExpirada($m))); // Explicação DevIN: Atribui um valor à variável PHP $messages.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $result = $operacao($messages); // Explicação DevIN: Atribui um valor à variável PHP $result.
        $newPayload = json_encode($messages, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
        if ($newPayload !== $oldPayload) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $save = $conn->prepare('UPDATE devin_chat_state SET payload = ? WHERE resource = ?'); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $save->bind_param('ss', $newPayload, $recurso); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $save->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
            $save->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função acessarArmazenamentoMensagensMysql.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $conn->commit(); // Explicação DevIN: Continua expressão ou bloco pertencente a função acessarArmazenamentoMensagensMysql.
        $inTransaction = false; // Explicação DevIN: Atribui um valor à variável PHP $inTransaction.
        return $result; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } catch (Throwable $error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        if ($inTransaction) $conn->rollback(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new RuntimeException('Não foi possível acessar as mensagens persistentes. Verifique a migration e a conexão MySQL.', 0, $error); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
        $conn->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função acessarArmazenamentoMensagensMysql.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Detalhamento DevIN: centraliza a operação «appendPlatformChatNotification» no contexto de dados de perfis, currículos, vagas e interações do DevIN; os argumentos recebidos definem o alvo ou os dados processados, e o retorno/erro sinaliza o resultado ao chamador.
// Entrada e saída: confira a assinatura abaixo; falhas devem ser tratadas pelo controlador que chamou esta rotina.
// Detalhe DevIN: Método appendPlatformChatNotification: encapsula uma responsabilidade reutilizável do módulo no contexto de consultar e registrar mensagens de usuários. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function appendPlatformChatNotification(string $eventKey, string $recipientType, int $recipientId, string $message): void // Explicação DevIN: Declara função/método appendPlatformChatNotification, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if ($eventKey === '' || !in_array($recipientType, ['pessoa', 'empresa'], true) || $recipientId < 1) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new InvalidArgumentException('Destinatário de notificação inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    acessarArmazenamentoMensagens(static function (array &$messages) use ($eventKey, $recipientType, $recipientId, $message): void { // Explicação DevIN: Define função anônima usada como operação ou callback.
        foreach ($messages as $storedMessage) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            if (($storedMessage['chave_evento'] ?? '') === $eventKey) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $nextId = 1; // Explicação DevIN: Atribui um valor à variável PHP $nextId.
        foreach ($messages as $storedMessage) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            $nextId = max($nextId, (int) ($storedMessage['id'] ?? 0) + 1); // Explicação DevIN: Atribui um valor à variável PHP $nextId.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $messages[] = [ // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            'id' => $nextId, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'remetente_tipo' => 'sistema', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'remetente_id' => 0, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'destinatario_tipo' => $recipientType, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'destinatario_id' => $recipientId, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'mensagem' => $message, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'enviada_em' => date(DATE_ATOM), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'lida_em' => null, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'ocultada_para' => [], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'chave_evento' => $eventKey, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'expira_em' => strpos($eventKey, 'job-report:') === 0 // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                ? date(DATE_ATOM, time() + 86400) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                : null, // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
