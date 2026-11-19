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

    $flag = $_ENV['DEVIN_CHAT_MYSQL_ENABLED'] ?? $_SERVER['DEVIN_CHAT_MYSQL_ENABLED'] ?? getenv('DEVIN_CHAT_MYSQL_ENABLED'); // Explicação DevIN: Lê a migração opt-in após carregar a configuração.
    if (in_array(strtolower((string) $flag), ['1', 'true', 'yes', 'on'], true)) { // Explicação DevIN: Mantém o armazenamento antigo até completar a instalação no servidor.
        return acessarArmazenamentoMensagensMysql($operacao, $recurso); // Explicação DevIN: Executa a mesma operação transacional usando MySQL.
    } // Explicação DevIN: Finaliza seleção segura do armazenamento.

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
function acessarArmazenamentoMensagensMysql(callable $operacao, string $recurso) // Explicação DevIN: Define o backend persistente de conversas.
{ // Explicação DevIN: Abre o escopo transacional do armazenamento.
    require_once __DIR__ . '/../config/database.php'; // Explicação DevIN: Obtém conexão MySQL com as credenciais existentes.
    $conn = getDatabaseConnection(); // Explicação DevIN: Abre conexão independente para não interferir na transação da API.
    $inTransaction = false; // Explicação DevIN: Controla rollback apenas quando a transação está ativa.
    try { // Explicação DevIN: Garante fechamento de conexão mesmo diante de erros.
        $conn->begin_transaction(); // Explicação DevIN: Preserva atomicidade de leitura e atualização do histórico.
        $inTransaction = true; // Explicação DevIN: Registra abertura da transação.
        $legacyPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR
            . 'devin-' . $recurso . '-' . hash('sha256', dirname(__DIR__, 2)) . '.json'; // Explicação DevIN: Reconstrói o mesmo nome usado pelo armazenamento anterior.
        $legacyData = '[]'; // Explicação DevIN: Novo ambiente pode iniciar com histórico vazio.
        if (file_exists($legacyPath)) { // Explicação DevIN: Importa histórico legado quando o arquivo ainda existe.
            $legacyFile = fopen($legacyPath, 'rb'); // Explicação DevIN: Abre o arquivo somente para leitura, sem o modificar.
            if ($legacyFile === false) throw new RuntimeException('O arquivo legado de mensagens está inacessível.'); // Explicação DevIN: Impede migrar silenciosamente um arquivo ilegível.
            try { // Explicação DevIN: Libera o arquivo mesmo quando há erro.
                if (!flock($legacyFile, LOCK_SH)) throw new RuntimeException('Não foi possível proteger a importação do histórico.'); // Explicação DevIN: Evita ler enquanto outra operação grava.
                $content = stream_get_contents($legacyFile); // Explicação DevIN: Lê a cópia original para importação.
                if ($content === false) throw new RuntimeException('Não foi possível ler o histórico legado.'); // Explicação DevIN: Não descarta conversas por erro de leitura.
                if ($content !== '') { // Explicação DevIN: Trata arquivo novo como array vazio.
                    $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR); // Explicação DevIN: Verifica integridade do histórico antes da importação.
                    if (!is_array($decoded)) throw new RuntimeException('O histórico legado não contém um array válido.'); // Explicação DevIN: Interrompe migração inválida.
                    $legacyData = $content; // Explicação DevIN: Preserva exatamente os dados antigos.
                } // Explicação DevIN: Encerra a validação do arquivo legado.
            } finally { // Explicação DevIN: Libera lock e descritor ao terminar a leitura.
                flock($legacyFile, LOCK_UN); // Explicação DevIN: Finaliza bloqueio compartilhado.
                fclose($legacyFile); // Explicação DevIN: Fecha arquivo sem removê-lo.
            } // Explicação DevIN: Encerra importação protegida.
        } // Explicação DevIN: Finaliza checagem do histórico anterior.
        $insert = $conn->prepare('INSERT IGNORE INTO devin_chat_state (resource, payload) VALUES (?, ?)'); // Explicação DevIN: Importa somente se o recurso ainda não foi migrado.
        $insert->bind_param('ss', $recurso, $legacyData); // Explicação DevIN: Vincula recurso e dados em parâmetros SQL seguros.
        $insert->execute(); // Explicação DevIN: Preserva o primeiro histórico importado frente a concorrência.
        $insert->close(); // Explicação DevIN: Fecha a instrução de inicialização.
        $select = $conn->prepare('SELECT payload FROM devin_chat_state WHERE resource = ? FOR UPDATE'); // Explicação DevIN: Bloqueia o recurso para impedir gravações concorrentes.
        $select->bind_param('s', $recurso); // Explicação DevIN: Busca apenas mensagens ou conversas.
        $select->execute(); // Explicação DevIN: Carrega histórico persistente.
        $row = $select->get_result()->fetch_assoc(); // Explicação DevIN: Obtém payload serializado.
        $select->close(); // Explicação DevIN: Fecha a consulta.
        if (!$row) throw new RuntimeException('Armazenamento persistente de mensagens indisponível.'); // Explicação DevIN: Impede prosseguir sem estado íntegro.
        $oldPayload = (string) $row['payload']; // Explicação DevIN: Guarda versão anterior para evitar escrita desnecessária.
        $messages = json_decode($oldPayload, true, 512, JSON_THROW_ON_ERROR); // Explicação DevIN: Recupera o mesmo array esperado pelos controladores existentes.
        if (!is_array($messages)) throw new RuntimeException('Histórico persistente de mensagens inválido.'); // Explicação DevIN: Não sobrescreve dados inválidos.
        if ($recurso === 'mensagens') { // Explicação DevIN: Mantém a política anterior de notificações de denúncia expirada.
            $messages = array_values(array_filter($messages, static fn (array $m): bool => !mensagemConclusaoReportExpirada($m))); // Explicação DevIN: Descarta avisos temporários expirados.
        } // Explicação DevIN: Finaliza política de expiração.
        $result = $operacao($messages); // Explicação DevIN: Reutiliza a operação atual de envio, leitura ou exclusão.
        $newPayload = json_encode($messages, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR); // Explicação DevIN: Serializa o novo estado do recurso.
        if ($newPayload !== $oldPayload) { // Explicação DevIN: Evita UPDATE em leituras que não mudaram o histórico.
            $save = $conn->prepare('UPDATE devin_chat_state SET payload = ? WHERE resource = ?'); // Explicação DevIN: Grava o estado modificado na mesma transação.
            $save->bind_param('ss', $newPayload, $recurso); // Explicação DevIN: Usa parâmetros sem concatenação SQL.
            $save->execute(); // Explicação DevIN: Persiste envio, leitura ou remoção.
            $save->close(); // Explicação DevIN: Libera a instrução de UPDATE.
        } // Explicação DevIN: Finaliza a escrita condicional.
        $conn->commit(); // Explicação DevIN: Confirma alterações consistentes para todas as sessões.
        $inTransaction = false; // Explicação DevIN: Desativa rollback após confirmação.
        return $result; // Explicação DevIN: Preserva o retorno público do armazenamento antigo.
    } catch (Throwable $error) { // Explicação DevIN: Interrompe operações sem arriscar substituição do histórico.
        if ($inTransaction) $conn->rollback(); // Explicação DevIN: Desfaz alterações parciais.
        throw new RuntimeException('Não foi possível acessar as mensagens persistentes. Verifique a migration e a conexão MySQL.', 0, $error); // Explicação DevIN: Não faz fallback invisível para JSON.
    } finally { // Explicação DevIN: Finaliza a conexão independente.
        $conn->close(); // Explicação DevIN: Libera recursos do banco MySQL.
    } // Explicação DevIN: Encerra a transação de mensagens.
} // Explicação DevIN: Finaliza a função de persistência.

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
