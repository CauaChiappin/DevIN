<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

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
function acessarArmazenamentoMensagens(callable $operacao, string $recurso = 'mensagens') // Explicação DevIN: Declara função/método acessarArmazenamentoMensagens, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if (!in_array($recurso, ['mensagens', 'conversas'], true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new InvalidArgumentException('Armazenamento de mensagens inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
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

// Detalhamento DevIN: centraliza a operação «appendPlatformChatNotification» no contexto de dados de perfis, currículos, vagas e interações do DevIN; os argumentos recebidos definem o alvo ou os dados processados, e o retorno/erro sinaliza o resultado ao chamador.
// Entrada e saída: confira a assinatura abaixo; falhas devem ser tratadas pelo controlador que chamou esta rotina.
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
