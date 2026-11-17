<?php // Explicação DevIN: Abre código PHP interpretado no servidor.

declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — php/mensagens_api.php
 * Finalidade: operar mensagens do sistema.
 * Domínio: processamento no servidor.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */


require_once __DIR__ . '/middlewares/auth.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/config/security.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/controllers/ProfileLanguagePreference.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/controllers/ChatStorage.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/controllers/ProfileReports.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/MailerHelper.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
require_once __DIR__ . '/helpers.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

header('Content-Type: application/json; charset=utf-8'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
header('Cache-Control: no-store, private'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.

// Detalhe DevIN: Método responderMensagens: encapsula uma responsabilidade reutilizável do módulo no contexto de operar mensagens do sistema. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function responderMensagens(array $dados, int $status = 200): void // Explicação DevIN: Declara função/método responderMensagens, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    http_response_code($status); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
    exit; // Explicação DevIN: Continua expressão ou bloco pertencente a função responderMensagens.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Detalhe DevIN: Método validarTipoChat: encapsula uma responsabilidade reutilizável do módulo no contexto de operar mensagens do sistema. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function validarTipoChat(string $tipo): bool // Explicação DevIN: Declara função/método validarTipoChat, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    return in_array($tipo, ['pessoa', 'empresa'], true); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Detalhe DevIN: Método chaveAtorChat: encapsula uma responsabilidade reutilizável do módulo no contexto de operar mensagens do sistema. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function chaveAtorChat(array $ator): string // Explicação DevIN: Declara função/método chaveAtorChat, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    return $ator['tipo'] . ':' . $ator['id']; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Detalhe DevIN: Método registrarConversaChat: encapsula uma responsabilidade reutilizável do módulo no contexto de operar mensagens do sistema. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function registrarConversaChat(array $ator, string $tipo, int $id): void // Explicação DevIN: Declara função/método registrarConversaChat, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    $participantes = [chaveAtorChat($ator), $tipo . ':' . $id]; // Explicação DevIN: Atribui um valor à variável PHP $participantes.
    sort($participantes, SORT_STRING); // Explicação DevIN: Continua expressão ou bloco pertencente a função registrarConversaChat.
    $chaveAtor = chaveAtorChat($ator); // Explicação DevIN: Atribui um valor à variável PHP $chaveAtor.

    acessarArmazenamentoMensagens(static function (array &$conversas) use ($participantes, $chaveAtor): void { // Explicação DevIN: Define função anônima usada como operação ou callback.
        foreach ($conversas as &$conversa) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            if (($conversa['participantes'] ?? null) !== $participantes) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                continue; // Explicação DevIN: Controla a continuação ou saída de laço/seleção.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $ocultadaPara = $conversa['ocultada_para'] ?? []; // Explicação DevIN: Atribui um valor à variável PHP $ocultadaPara.
            $conversa['ocultada_para'] = array_values(array_filter( // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                $ocultadaPara, // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                static fn ($participante): bool => $participante !== $chaveAtor // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
            )); // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
            unset($conversa); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
            return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        unset($conversa); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.

        $conversas[] = [ // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            'participantes' => $participantes, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'ocultada_para' => [], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
    }, 'conversas'); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Detalhe DevIN: Método mensagemOcultaParaAtor: encapsula uma responsabilidade reutilizável do módulo no contexto de operar mensagens do sistema. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function mensagemOcultaParaAtor(array $mensagem, array $ator): bool // Explicação DevIN: Declara função/método mensagemOcultaParaAtor, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    return in_array(chaveAtorChat($ator), $mensagem['ocultada_para'] ?? [], true); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Detalhe DevIN: Método traduzirMensagemMyMemory: encapsula uma responsabilidade reutilizável do módulo no contexto de operar mensagens do sistema. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function traduzirMensagemMyMemory(string $texto, string $idioma): string // Explicação DevIN: Declara função/método traduzirMensagemMyMemory, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if (!function_exists('curl_init')) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        responderMensagens([ // Explicação DevIN: Continua expressão ou bloco pertencente a função traduzirMensagemMyMemory.
            'sucesso' => false, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'mensagem' => 'A extensão cURL do PHP é necessária para traduzir mensagens.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ], 503); // Explicação DevIN: Continua expressão ou bloco pertencente a função traduzirMensagemMyMemory.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    $idiomasMyMemory = ['pt-BR' => 'pt-BR', 'en' => 'en', 'es' => 'es']; // Explicação DevIN: Atribui um valor à variável PHP $idiomasMyMemory.
    $idiomaAlvo = $idiomasMyMemory[$idioma] ?? null; // Explicação DevIN: Atribui um valor à variável PHP $idiomaAlvo.
    if ($idiomaAlvo === null) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new RuntimeException('O idioma selecionado para tradução é inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    $restante = $texto; // Explicação DevIN: Atribui um valor à variável PHP $restante.
    $trechos = []; // Explicação DevIN: Atribui um valor à variável PHP $trechos.
    while (strlen($restante) > 450) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
        $trecho = mb_strcut($restante, 0, 450, 'UTF-8'); // Explicação DevIN: Atribui um valor à variável PHP $trecho.
        $ultimoEspaco = mb_strrpos($trecho, ' '); // Explicação DevIN: Atribui um valor à variável PHP $ultimoEspaco.
        $tamanhoTrecho = $ultimoEspaco !== false && $ultimoEspaco > 0 // Explicação DevIN: Atribui um valor à variável PHP $tamanhoTrecho.
            ? $ultimoEspaco + 1 // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            : mb_strlen($trecho, 'UTF-8'); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        $trechos[] = mb_substr($restante, 0, $tamanhoTrecho, 'UTF-8'); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        $restante = mb_substr($restante, $tamanhoTrecho, null, 'UTF-8'); // Explicação DevIN: Atribui um valor à variável PHP $restante.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if ($restante !== '') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        $trechos[] = $restante; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    $traducao = ''; // Explicação DevIN: Atribui um valor à variável PHP $traducao.
    foreach ($trechos as $trecho) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
        $url = 'https://api.mymemory.translated.net/get?' . http_build_query([ // Explicação DevIN: Atribui um valor à variável PHP $url.
            'q' => $trecho, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'langpair' => 'autodetect|' . $idiomaAlvo, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        ]); // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
        $curl = curl_init($url); // Explicação DevIN: Atribui um valor à variável PHP $curl.
        if ($curl === false) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new RuntimeException('Não foi possível iniciar a conexão com o serviço de tradução.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        curl_setopt_array($curl, [ // Explicação DevIN: Continua expressão ou bloco pertencente a função traduzirMensagemMyMemory.
            CURLOPT_RETURNTRANSFER => true, // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            CURLOPT_CONNECTTIMEOUT => 5, // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            CURLOPT_TIMEOUT => 12, // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            CURLOPT_SSL_VERIFYPEER => true, // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            CURLOPT_SSL_VERIFYHOST => 2, // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        ]); // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.

        $resposta = curl_exec($curl); // Explicação DevIN: Atribui um valor à variável PHP $resposta.
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE); // Explicação DevIN: Atribui um valor à variável PHP $status.
        $erro = curl_error($curl); // Explicação DevIN: Atribui um valor à variável PHP $erro.
        curl_close($curl); // Explicação DevIN: Continua expressão ou bloco pertencente a função traduzirMensagemMyMemory.

        if (!is_string($resposta) || $status < 200 || $status >= 300) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            error_log('Falha no serviço MyMemory: HTTP ' . $status . ($erro !== '' ? ' (' . $erro . ')' : '')); // Explicação DevIN: Continua expressão ou bloco pertencente a função traduzirMensagemMyMemory.
            responderMensagens([ // Explicação DevIN: Continua expressão ou bloco pertencente a função traduzirMensagemMyMemory.
                'sucesso' => false, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'mensagem' => 'Não foi possível traduzir agora. Tente novamente.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            ], 503); // Explicação DevIN: Continua expressão ou bloco pertencente a função traduzirMensagemMyMemory.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            $dados = json_decode($resposta, true, 512, JSON_THROW_ON_ERROR); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
        } catch (JsonException $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            error_log('Resposta inválida do serviço MyMemory.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função traduzirMensagemMyMemory.
            throw new RuntimeException('O serviço de tradução retornou uma resposta inválida.', 0, $exception); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if (!empty($dados['quotaFinished'])) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            responderMensagens([ // Explicação DevIN: Continua expressão ou bloco pertencente a função traduzirMensagemMyMemory.
                'sucesso' => false, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'mensagem' => 'Limite gratuito diário de tradução atingido. Tente novamente amanhã.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            ], 429); // Explicação DevIN: Continua expressão ou bloco pertencente a função traduzirMensagemMyMemory.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $statusTraducao = (int) ($dados['responseStatus'] ?? 0); // Explicação DevIN: Atribui um valor à variável PHP $statusTraducao.
        $textoTraduzido = $dados['responseData']['translatedText'] ?? null; // Explicação DevIN: Atribui um valor à variável PHP $textoTraduzido.
        if ($statusTraducao !== 200 || !is_string($textoTraduzido) || $textoTraduzido === '') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            error_log('MyMemory não retornou tradução válida: ' . (string) ($dados['responseDetails'] ?? '')); // Explicação DevIN: Continua expressão ou bloco pertencente a função traduzirMensagemMyMemory.
            responderMensagens([ // Explicação DevIN: Continua expressão ou bloco pertencente a função traduzirMensagemMyMemory.
                'sucesso' => false, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'mensagem' => 'Não foi possível traduzir agora. Tente novamente.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            ], 503); // Explicação DevIN: Continua expressão ou bloco pertencente a função traduzirMensagemMyMemory.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $traducao .= $textoTraduzido; // Explicação DevIN: Continua expressão ou bloco pertencente a função traduzirMensagemMyMemory.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    return $traducao; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Detalhe DevIN: Método resumirConversasChat: encapsula uma responsabilidade reutilizável do módulo no contexto de operar mensagens do sistema. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function resumirConversasChat(array $mensagens, array $ator, array $conversas = []): array // Explicação DevIN: Declara função/método resumirConversasChat, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    $contatos = []; // Explicação DevIN: Atribui um valor à variável PHP $contatos.

    foreach ($conversas as $conversa) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
        $participantes = $conversa['participantes'] ?? []; // Explicação DevIN: Atribui um valor à variável PHP $participantes.
        if (!is_array($participantes) || mensagemOcultaParaAtor($conversa, $ator)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            continue; // Explicação DevIN: Controla a continuação ou saída de laço/seleção.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $atorChave = chaveAtorChat($ator); // Explicação DevIN: Atribui um valor à variável PHP $atorChave.
        if (!in_array($atorChave, $participantes, true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            continue; // Explicação DevIN: Controla a continuação ou saída de laço/seleção.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        $outroParticipante = array_values(array_filter( // Explicação DevIN: Atribui um valor à variável PHP $outroParticipante.
            $participantes, // Explicação DevIN: Continua expressão ou bloco pertencente a função resumirConversasChat.
            static fn ($participante): bool => is_string($participante) && $participante !== $atorChave // Explicação DevIN: Continua expressão ou bloco pertencente a função resumirConversasChat.
        ))[0] ?? ''; // Explicação DevIN: Continua expressão ou bloco pertencente a função resumirConversasChat.
        [$tipoContato, $idContato] = array_pad(explode(':', $outroParticipante, 2), 2, ''); // Explicação DevIN: Continua expressão ou bloco pertencente a função resumirConversasChat.
        $idContatoNumerico = (int) $idContato; // Explicação DevIN: Atribui um valor à variável PHP $idContatoNumerico.
        $contatoSistema = $tipoContato === 'sistema' && $idContatoNumerico === 0; // Explicação DevIN: Atribui um valor à variável PHP $contatoSistema.
        $contatoUsuario = in_array($tipoContato, ['pessoa', 'empresa'], true) && $idContatoNumerico > 0; // Explicação DevIN: Atribui um valor à variável PHP $contatoUsuario.
        if (!$contatoSistema && !$contatoUsuario) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            continue; // Explicação DevIN: Controla a continuação ou saída de laço/seleção.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $chave = $tipoContato . ':' . $idContatoNumerico; // Explicação DevIN: Atribui um valor à variável PHP $chave.
        $contatos[$chave] = ['ultima_mensagem_id' => 0, 'nao_lidas' => 0]; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    foreach ($mensagens as $mensagem) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
        if (!isset($mensagem['remetente_tipo'], $mensagem['destinatario_tipo']) || mensagemOcultaParaAtor($mensagem, $ator)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            continue; // Explicação DevIN: Controla a continuação ou saída de laço/seleção.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $remetenteEhAtor = $mensagem['remetente_tipo'] === $ator['tipo'] // Explicação DevIN: Atribui um valor à variável PHP $remetenteEhAtor.
            && (int) $mensagem['remetente_id'] === $ator['id']; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        $destinatarioEhAtor = $mensagem['destinatario_tipo'] === $ator['tipo'] // Explicação DevIN: Atribui um valor à variável PHP $destinatarioEhAtor.
            && (int) $mensagem['destinatario_id'] === $ator['id']; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        if (!$remetenteEhAtor && !$destinatarioEhAtor) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            continue; // Explicação DevIN: Controla a continuação ou saída de laço/seleção.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $tipoContato = $remetenteEhAtor ? $mensagem['destinatario_tipo'] : $mensagem['remetente_tipo']; // Explicação DevIN: Atribui um valor à variável PHP $tipoContato.
        $idContato = (int) ($remetenteEhAtor ? $mensagem['destinatario_id'] : $mensagem['remetente_id']); // Explicação DevIN: Atribui um valor à variável PHP $idContato.
        $chave = $tipoContato . ':' . $idContato; // Explicação DevIN: Atribui um valor à variável PHP $chave.
        $idMensagem = (int) $mensagem['id']; // Explicação DevIN: Atribui um valor à variável PHP $idMensagem.
        if (!isset($contatos[$chave])) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $contatos[$chave] = ['ultima_mensagem_id' => 0, 'nao_lidas' => 0]; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $contatos[$chave]['ultima_mensagem_id'] = max($contatos[$chave]['ultima_mensagem_id'], $idMensagem); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        if ($destinatarioEhAtor && $mensagem['lida_em'] === null) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $contatos[$chave]['nao_lidas']++; // Explicação DevIN: Continua expressão ou bloco pertencente a função resumirConversasChat.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    return [ // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        'contatos' => $contatos, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        'nao_lidas' => array_sum(array_column($contatos, 'nao_lidas')), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Detalhe DevIN: Método resumirArmazenamentoChat: encapsula uma responsabilidade reutilizável do módulo no contexto de operar mensagens do sistema. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function resumirArmazenamentoChat(array $ator): array // Explicação DevIN: Declara função/método resumirArmazenamentoChat, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    $mensagens = acessarArmazenamentoMensagens(static fn (array &$armazenadas): array => $armazenadas); // Explicação DevIN: Atribui um valor à variável PHP $mensagens.
    $conversas = acessarArmazenamentoMensagens( // Explicação DevIN: Atribui um valor à variável PHP $conversas.
        static fn (array &$armazenadas): array => $armazenadas, // Explicação DevIN: Continua expressão ou bloco pertencente a função resumirArmazenamentoChat.
        'conversas' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    return resumirConversasChat($mensagens, $ator, $conversas); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Detalhe DevIN: Método buscarUsuarioChat: encapsula uma responsabilidade reutilizável do módulo no contexto de operar mensagens do sistema. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function buscarUsuarioChat(mysqli $conn, string $tipo, int $id): ?array // Explicação DevIN: Declara função/método buscarUsuarioChat, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if ($tipo === 'sistema' && $id === 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        return ['id' => 0, 'nome' => 'Suporte DevIN']; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    if (!validarTipoChat($tipo) || $id < 1) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        return null; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    $tabela = $tipo === 'pessoa' ? 'pessoa' : 'empresa'; // Explicação DevIN: Atribui um valor à variável PHP $tabela.
    $coluna = $tipo === 'pessoa' ? 'id_pessoa' : 'id_empresa'; // Explicação DevIN: Atribui um valor à variável PHP $coluna.
    // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
    $stmt = $conn->prepare("SELECT {$coluna} AS id, nome FROM {$tabela} WHERE {$coluna} = ? LIMIT 1"); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
    $stmt->bind_param('i', $id); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
    $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
    $usuario = $stmt->get_result()->fetch_assoc() ?: null; // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
    $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função buscarUsuarioChat.

    return $usuario; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Detalhe DevIN: Método destinatarioValido: encapsula uma responsabilidade reutilizável do módulo no contexto de operar mensagens do sistema. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function destinatarioValido(mysqli $conn, array $ator, string $tipo, int $id): array // Explicação DevIN: Declara função/método destinatarioValido, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if (!validarTipoChat($tipo) || $id < 1 || ($tipo === $ator['tipo'] && $id === $ator['id'])) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new InvalidArgumentException('Destinatário inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    $destinatario = buscarUsuarioChat($conn, $tipo, $id); // Explicação DevIN: Atribui um valor à variável PHP $destinatario.
    if ($destinatario === null) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        throw new InvalidArgumentException('Este usuário não está disponível para conversa.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    return $destinatario; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

// Detalhe DevIN: Método atorTemConversaChat: encapsula uma responsabilidade reutilizável do módulo no contexto de operar mensagens do sistema. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function atorTemConversaChat(array $ator, string $tipo, int $id): bool // Explicação DevIN: Declara função/método atorTemConversaChat, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    $resumo = resumirArmazenamentoChat($ator); // Explicação DevIN: Atribui um valor à variável PHP $resumo.
    return isset($resumo['contatos'][$tipo . ':' . $id]); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    $ator = requireWebAuth(); // Explicação DevIN: Atribui um valor à variável PHP $ator.
    if (!in_array($ator['tipo'], ['pessoa', 'empresa'], true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        responderMensagens(['sucesso' => false, 'mensagem' => 'Mensagens não estão disponíveis para este perfil.'], 403); // Explicação DevIN: Continua expressão ou bloco pertencente a função atorTemConversaChat.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    $conn = getDatabaseConnection(); // Explicação DevIN: Atribui um valor à variável PHP $conn.
    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        if ($_SERVER['REQUEST_METHOD'] === 'GET') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $acao = requestString($_GET, 'acao'); // Explicação DevIN: Atribui um valor à variável PHP $acao.

            if ($acao === 'perfil') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                $tipo = requestString($_GET, 'tipo'); // Explicação DevIN: Atribui um valor à variável PHP $tipo.
                $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT); // Explicação DevIN: Atribui um valor à variável PHP $id.
                if ($id === false || $id < 1 || !validarTipoChat($tipo)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    throw new InvalidArgumentException('Perfil inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                if ( // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    ($tipo === $ator['tipo'] && $id === $ator['id']) // Explicação DevIN: Continua expressão ou bloco pertencente a função atorTemConversaChat.
                    || !atorTemConversaChat($ator, $tipo, $id) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                ) { // Explicação DevIN: Continua expressão ou bloco pertencente a função atorTemConversaChat.
                    responderMensagens(['sucesso' => false, 'mensagem' => 'Este perfil não está disponível.'], 404); // Explicação DevIN: Continua expressão ou bloco pertencente a função atorTemConversaChat.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

                $perfil = buscarUsuarioChat($conn, $tipo, $id); // Explicação DevIN: Atribui um valor à variável PHP $perfil.
                if ($perfil === null) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    responderMensagens(['sucesso' => false, 'mensagem' => 'Este perfil não está disponível.'], 404); // Explicação DevIN: Continua expressão ou bloco pertencente a função atorTemConversaChat.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

                responderMensagens([ // Explicação DevIN: Continua expressão ou bloco pertencente a função atorTemConversaChat.
                    'sucesso' => true, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    'perfil' => [ // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                        'id' => (int) $perfil['id'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                        'tipo' => $tipo, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                        'nome' => (string) $perfil['nome'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                        'rotulo' => $tipo === 'pessoa' ? 'Pessoa' : 'Empresa', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    ], // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                ]); // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            if ($acao === 'foto') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                $tipo = requestString($_GET, 'tipo'); // Explicação DevIN: Atribui um valor à variável PHP $tipo.
                $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT); // Explicação DevIN: Atribui um valor à variável PHP $id.
                $perfis = [ // Explicação DevIN: Atribui um valor à variável PHP $perfis.
                    'pessoa' => ['pessoa', 'id_pessoa'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    'empresa' => ['empresa', 'id_empresa'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
                if (!isset($perfis[$tipo]) || $id === false || $id < 1) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    throw new InvalidArgumentException('Perfil inválido.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                if ( // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    ($tipo !== $ator['tipo'] || $id !== $ator['id']) // Explicação DevIN: Continua expressão ou bloco pertencente a função atorTemConversaChat.
                    && !atorTemConversaChat($ator, $tipo, $id) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                ) { // Explicação DevIN: Continua expressão ou bloco pertencente a função atorTemConversaChat.
                    http_response_code(404); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
                    exit; // Explicação DevIN: Continua expressão ou bloco pertencente a função atorTemConversaChat.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

                [$tabela, $colunaId] = $perfis[$tipo]; // Explicação DevIN: Continua expressão ou bloco pertencente a função atorTemConversaChat.
                // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
                $stmt = $conn->prepare("SELECT foto FROM {$tabela} WHERE {$colunaId} = ? LIMIT 1"); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->bind_param('i', $id); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                $resultadoFoto = $stmt->get_result()->fetch_assoc(); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
                $foto = $resultadoFoto['foto'] ?? null; // Explicação DevIN: Atribui um valor à variável PHP $foto.
                $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função atorTemConversaChat.
                $conn->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função atorTemConversaChat.

                if (!is_string($foto) || $foto === '') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    http_response_code(404); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
                    exit; // Explicação DevIN: Continua expressão ou bloco pertencente a função atorTemConversaChat.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

                $conteudoFoto = $foto; // Explicação DevIN: Atribui um valor à variável PHP $conteudoFoto.
                $caminhoFoto = profilePhotoFilePath($foto, $tipo, $id); // Explicação DevIN: Atribui um valor à variável PHP $caminhoFoto.
                if ($caminhoFoto !== null) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    $conteudoFoto = file_get_contents($caminhoFoto); // Explicação DevIN: Atribui um valor à variável PHP $conteudoFoto.
                    if ($conteudoFoto === false) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        http_response_code(404); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
                        exit; // Explicação DevIN: Continua expressão ou bloco pertencente a função atorTemConversaChat.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

                $mime = (new finfo(FILEINFO_MIME_TYPE))->buffer($conteudoFoto); // Explicação DevIN: Atribui um valor à variável PHP $mime.
                if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    http_response_code(404); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
                    exit; // Explicação DevIN: Continua expressão ou bloco pertencente a função atorTemConversaChat.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

                $etag = '"' . hash('sha256', $conteudoFoto) . '"'; // Explicação DevIN: Atribui um valor à variável PHP $etag.
                header('ETag: ' . $etag); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
                header('Cache-Control: private, no-cache, must-revalidate'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
                $etagsSolicitadas = array_map( // Explicação DevIN: Atribui um valor à variável PHP $etagsSolicitadas.
                    'trim', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    explode(',', $_SERVER['HTTP_IF_NONE_MATCH'] ?? '') // Explicação DevIN: Continua expressão ou bloco pertencente a função atorTemConversaChat.
                ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                if (in_array($etag, $etagsSolicitadas, true) || in_array('*', $etagsSolicitadas, true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    http_response_code(304); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
                    exit; // Explicação DevIN: Continua expressão ou bloco pertencente a função atorTemConversaChat.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

                header('Content-Type: ' . $mime); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
                header('X-Content-Type-Options: nosniff'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
                echo $conteudoFoto; // Explicação DevIN: Produz saída ou conteúdo da resposta do programa.
                exit; // Explicação DevIN: Continua expressão ou bloco pertencente a função atorTemConversaChat.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            if ($acao === 'buscar_contatos') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                $termo = requestString($_GET, 'termo'); // Explicação DevIN: Atribui um valor à variável PHP $termo.
                if (mb_strlen($termo) < 2 || mb_strlen($termo) > 100) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    responderMensagens(['sucesso' => true, 'contatos' => []]); // Explicação DevIN: Continua expressão ou bloco pertencente a função atorTemConversaChat.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

                $contatosEncontrados = []; // Explicação DevIN: Atribui um valor à variável PHP $contatosEncontrados.
                $busca = '%' . $termo . '%'; // Explicação DevIN: Atribui um valor à variável PHP $busca.
                foreach ([ // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                    'pessoa' => ['pessoa', 'id_pessoa', 'Pessoa'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    'empresa' => ['empresa', 'id_empresa', 'Empresa'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                ] as $tipo => [$tabela, $colunaId, $rotulo]) { // Explicação DevIN: Continua expressão ou bloco pertencente a função atorTemConversaChat.
                    // Detalhe DevIN: Prepara SQL para receber valores vinculados; evita interpolar dados do usuário na consulta.
                    $stmt = $conn->prepare( // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                        "SELECT {$colunaId} AS id, nome FROM {$tabela}
                         WHERE (nome LIKE ? OR email LIKE ?)
                           AND NOT ({$colunaId} = ? AND ? = ?)
                         ORDER BY nome LIMIT 25" // Explicação DevIN: Continua expressão ou bloco pertencente a função atorTemConversaChat.
                    ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                    $stmt->bind_param('ssiss', $busca, $busca, $ator['id'], $tipo, $ator['tipo']); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                    $stmt->execute(); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
                    foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $usuario) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                        $contatosEncontrados[] = [ // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                            'id' => (int) $usuario['id'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                            'tipo' => $tipo, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                            'nome' => (string) $usuario['nome'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                            'rotulo' => $rotulo, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                            'nao_lidas' => 0, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                            'ultima_mensagem_id' => 0, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                        ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                    $stmt->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função atorTemConversaChat.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

                usort($contatosEncontrados, static fn (array $a, array $b): int => // Explicação DevIN: Continua expressão ou bloco pertencente a função atorTemConversaChat.
                    strnatcasecmp($a['nome'], $b['nome']) // Explicação DevIN: Continua expressão ou bloco pertencente a função atorTemConversaChat.
                ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                responderMensagens(['sucesso' => true, 'contatos' => array_slice($contatosEncontrados, 0, 30)]); // Explicação DevIN: Continua expressão ou bloco pertencente a função atorTemConversaChat.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            if ($acao === 'contatos') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                $resumo = resumirArmazenamentoChat($ator); // Explicação DevIN: Atribui um valor à variável PHP $resumo.
                $contatos = []; // Explicação DevIN: Atribui um valor à variável PHP $contatos.
                foreach ($resumo['contatos'] as $chave => $infoContato) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                    [$tipoContato, $idContato] = explode(':', $chave, 2); // Explicação DevIN: Continua expressão ou bloco pertencente a função atorTemConversaChat.
                    $usuario = buscarUsuarioChat($conn, $tipoContato, (int) $idContato); // Explicação DevIN: Atribui um valor à variável PHP $usuario.
                    if ($usuario === null) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        continue; // Explicação DevIN: Controla a continuação ou saída de laço/seleção.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                    switch ($tipoContato) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                        case 'pessoa': // Explicação DevIN: Define um dos caminhos possíveis de uma seleção de casos.
                            $rotuloContato = 'Pessoa'; // Explicação DevIN: Atribui um valor à variável PHP $rotuloContato.
                            break; // Explicação DevIN: Controla a continuação ou saída de laço/seleção.
                        case 'empresa': // Explicação DevIN: Define um dos caminhos possíveis de uma seleção de casos.
                            $rotuloContato = 'Empresa'; // Explicação DevIN: Atribui um valor à variável PHP $rotuloContato.
                            break; // Explicação DevIN: Controla a continuação ou saída de laço/seleção.
                        case 'sistema': // Explicação DevIN: Define um dos caminhos possíveis de uma seleção de casos.
                            $rotuloContato = 'Notificação da plataforma'; // Explicação DevIN: Atribui um valor à variável PHP $rotuloContato.
                            break; // Explicação DevIN: Controla a continuação ou saída de laço/seleção.
                        default: // Explicação DevIN: Define um dos caminhos possíveis de uma seleção de casos.
                            $rotuloContato = 'Contato'; // Explicação DevIN: Atribui um valor à variável PHP $rotuloContato.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                    $contatos[] = [ // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                        'id' => (int) $usuario['id'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                        'tipo' => $tipoContato, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                        'nome' => (string) $usuario['nome'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                        'rotulo' => $rotuloContato, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                        'ultima_mensagem_id' => $infoContato['ultima_mensagem_id'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                        'nao_lidas' => $infoContato['nao_lidas'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

                usort($contatos, static function (array $a, array $b): int { // Explicação DevIN: Define função anônima usada como operação ou callback.
                    $recencia = $b['ultima_mensagem_id'] <=> $a['ultima_mensagem_id']; // Explicação DevIN: Atribui um valor à variável PHP $recencia.
                    return $recencia !== 0 ? $recencia : strnatcasecmp($a['nome'], $b['nome']); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
                }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                responderMensagens([ // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                    'sucesso' => true, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    'contatos' => $contatos, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    'nao_lidas' => $resumo['nao_lidas'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                ]); // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            if ($acao === 'nao_lidas') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                $resumo = resumirArmazenamentoChat($ator); // Explicação DevIN: Atribui um valor à variável PHP $resumo.
                responderMensagens(['sucesso' => true, 'nao_lidas' => $resumo['nao_lidas']]); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            if ($acao === 'conversa') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                $tipo = requestString($_GET, 'tipo'); // Explicação DevIN: Atribui um valor à variável PHP $tipo.
                $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT); // Explicação DevIN: Atribui um valor à variável PHP $id.
                $depoisDe = filter_var($_GET['depois_de'] ?? 0, FILTER_VALIDATE_INT); // Explicação DevIN: Atribui um valor à variável PHP $depoisDe.
                if ($id === false || $depoisDe === false || $depoisDe < 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    throw new InvalidArgumentException('Parâmetros da conversa inválidos.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                if (!($tipo === 'sistema' && $id === 0)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    destinatarioValido($conn, $ator, $tipo, $id); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

                $resultado = acessarArmazenamentoMensagens(static function (array &$armazenadas) use ($ator, $tipo, $id, $depoisDe): array { // Explicação DevIN: Define função anônima usada como operação ou callback.
                    $conversa = array_values(array_filter($armazenadas, static function (array $mensagem) use ($ator, $tipo, $id): bool { // Explicação DevIN: Define função anônima usada como operação ou callback.
                        if ( // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                            !isset($mensagem['remetente_tipo'], $mensagem['remetente_id'], $mensagem['destinatario_tipo'], $mensagem['destinatario_id']) // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                            || mensagemOcultaParaAtor($mensagem, $ator) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                        ) { // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                            return false; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
                        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

                        return ( // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
                            $mensagem['remetente_tipo'] === $ator['tipo'] // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                            && (int) $mensagem['remetente_id'] === $ator['id'] // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                            && $mensagem['destinatario_tipo'] === $tipo // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                            && (int) $mensagem['destinatario_id'] === $id // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                        ) || ( // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                            $mensagem['remetente_tipo'] === $tipo // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                            && (int) $mensagem['remetente_id'] === $id // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                            && $mensagem['destinatario_tipo'] === $ator['tipo'] // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                            && (int) $mensagem['destinatario_id'] === $ator['id'] // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                        ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                    })); // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
                    usort($conversa, static fn (array $a, array $b): int => $a['id'] <=> $b['id']); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.

                    foreach ($armazenadas as &$mensagem) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                        if ( // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                            !isset($mensagem['remetente_tipo'], $mensagem['remetente_id'], $mensagem['destinatario_tipo'], $mensagem['destinatario_id']) // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                            || !array_key_exists('lida_em', $mensagem) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                        ) { // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                            continue; // Explicação DevIN: Controla a continuação ou saída de laço/seleção.
                        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                        if ( // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                            !mensagemOcultaParaAtor($mensagem, $ator) // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                            && $mensagem['remetente_tipo'] === $tipo // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                            && (int) $mensagem['remetente_id'] === $id // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                            && $mensagem['destinatario_tipo'] === $ator['tipo'] // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                            && (int) $mensagem['destinatario_id'] === $ator['id'] // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                            && $mensagem['lida_em'] === null // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                        ) { // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                            $mensagem['lida_em'] = date(DATE_ATOM); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                    unset($mensagem); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.

                    if ($depoisDe === 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        $conversa = array_slice($conversa, -50); // Explicação DevIN: Atribui um valor à variável PHP $conversa.
                    } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
                        $recentes = array_slice($conversa, -50); // Explicação DevIN: Atribui um valor à variável PHP $recentes.
                        $novas = array_values(array_filter( // Explicação DevIN: Atribui um valor à variável PHP $novas.
                            $conversa, // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                            static fn (array $mensagem): bool => $mensagem['id'] > $depoisDe // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                        )); // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
                        $atualizadas = array_values(array_filter( // Explicação DevIN: Atribui um valor à variável PHP $atualizadas.
                            $recentes, // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                            static fn (array $mensagem): bool => !empty($mensagem['apagada_para_todos']) // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                        )); // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
                        $porId = []; // Explicação DevIN: Atribui um valor à variável PHP $porId.
                        foreach (array_merge(array_slice($novas, 0, 50), $atualizadas) as $mensagem) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                            $porId[(int) $mensagem['id']] = $mensagem; // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                        $conversa = array_values($porId); // Explicação DevIN: Atribui um valor à variável PHP $conversa.
                        usort($conversa, static fn (array $a, array $b): int => $a['id'] <=> $b['id']); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

                    return array_map(static fn (array $mensagem): array => [ // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
                        'id' => (int) $mensagem['id'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                        'mensagem' => !empty($mensagem['apagada_para_todos']) // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                            ? '' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                            : (string) $mensagem['mensagem'], // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                        'enviada_em' => $mensagem['enviada_em'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                        'minha' => $mensagem['remetente_tipo'] === $ator['tipo'] // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                            && (int) $mensagem['remetente_id'] === $ator['id'], // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                        'apagada_para_todos' => !empty($mensagem['apagada_para_todos']), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    ], $conversa); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

                responderMensagens(['sucesso' => true, 'mensagens' => $resultado]); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            responderMensagens(['sucesso' => false, 'mensagem' => 'Ação não reconhecida.'], 400); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            header('Allow: GET, POST'); // Explicação DevIN: Ajusta cabeçalho, sessão ou resposta HTTP do servidor.
            responderMensagens(['sucesso' => false, 'mensagem' => 'Método não permitido.'], 405); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        requireValidCsrf(); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        $entrada = json_decode((string) file_get_contents('php://input'), true); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
        if (!is_array($entrada)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Não foi possível ler os dados da mensagem.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $acao = is_string($entrada['acao'] ?? null) ? $entrada['acao'] : ''; // Explicação DevIN: Atribui um valor à variável PHP $acao.
        if ($acao === 'denunciar_perfil') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $tipo = is_string($entrada['tipo'] ?? null) ? $entrada['tipo'] : ''; // Explicação DevIN: Atribui um valor à variável PHP $tipo.
            $id = filter_var($entrada['id'] ?? null, FILTER_VALIDATE_INT); // Explicação DevIN: Atribui um valor à variável PHP $id.
            $motivo = is_string($entrada['motivo'] ?? null) ? trim($entrada['motivo']) : ''; // Explicação DevIN: Atribui um valor à variável PHP $motivo.
            $detalhes = is_string($entrada['detalhes'] ?? null) ? trim($entrada['detalhes']) : ''; // Explicação DevIN: Atribui um valor à variável PHP $detalhes.
            if ($id === false || $id < 1 || !validarTipoChat($tipo)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new InvalidArgumentException('Perfil inválido para denúncia.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            if ( // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                ($tipo === $ator['tipo'] && $id === $ator['id']) // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                || !atorTemConversaChat($ator, $tipo, $id) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            ) { // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                responderMensagens(['sucesso' => false, 'mensagem' => 'Este perfil não está disponível para denúncia.'], 404); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            ensureProfileReportsSchema($conn); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
            $language = ProfileLanguagePreference::load($conn, $ator['tipo'], (int) $ator['id']); // Explicação DevIN: Atribui um valor à variável PHP $language.
            $submittedReport = submitProfileReport($conn, $ator, $tipo, $id, $motivo, $detalhes); // Explicação DevIN: Atribui um valor à variável PHP $submittedReport.
            $reportId = $submittedReport['id']; // Explicação DevIN: Atribui um valor à variável PHP $reportId.
            $reportedName = $submittedReport['nome']; // Explicação DevIN: Atribui um valor à variável PHP $reportedName.
            $warnings = []; // Explicação DevIN: Atribui um valor à variável PHP $warnings.
            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                appendPlatformChatNotification( // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                    'profile-report-submitted:' . $reportId, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    (string) $ator['tipo'], // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                    (int) $ator['id'], // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                    'Support DevIN: Recebemos sua denúncia sobre o perfil "' . $reportedName . '". Ela está sob análise da nossa equipe. Você receberá outra mensagem quando a análise for concluída.' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            } catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
                error_log('Falha ao enviar confirmação interna da denúncia de perfil: ' . $exception->getMessage()); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                $warnings[] = 'Não foi possível enviar a confirmação na aba de mensagens.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            if (!MailerHelper::enviarConfirmacaoDenuncia( // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                (string) $ator['email'], // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                (string) $ator['nome'], // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                'perfil', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                $reportedName, // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                $language // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
            )) { // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                $warnings[] = 'Não foi possível enviar o e-mail de confirmação.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            $response = [ // Explicação DevIN: Atribui um valor à variável PHP $response.
                'sucesso' => true, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'mensagem' => 'Denúncia enviada para análise da equipe DevIN.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
            if ($warnings !== []) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                $response['avisos'] = $warnings; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            responderMensagens($response, 201); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if ($acao === 'iniciar_conversa') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $tipo = is_string($entrada['tipo'] ?? null) ? $entrada['tipo'] : ''; // Explicação DevIN: Atribui um valor à variável PHP $tipo.
            $id = filter_var($entrada['id'] ?? null, FILTER_VALIDATE_INT); // Explicação DevIN: Atribui um valor à variável PHP $id.
            if ($id === false || $id < 1) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new InvalidArgumentException('Parâmetros da conversa inválidos.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $destinatario = destinatarioValido($conn, $ator, $tipo, $id); // Explicação DevIN: Atribui um valor à variável PHP $destinatario.
            registrarConversaChat($ator, $tipo, $id); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
            responderMensagens([ // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                'sucesso' => true, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'contato' => [ // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    'id' => $id, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    'tipo' => $tipo, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    'nome' => (string) $destinatario['nome'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    'rotulo' => $tipo === 'pessoa' ? 'Pessoa' : 'Empresa', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    'nao_lidas' => 0, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    'ultima_mensagem_id' => 0, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                ], // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            ]); // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if ($acao === 'traduzir') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $texto = is_string($entrada['texto'] ?? null) ? trim($entrada['texto']) : ''; // Explicação DevIN: Atribui um valor à variável PHP $texto.
            if ($texto === '' || mb_strlen($texto) > 2000) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new InvalidArgumentException('A mensagem precisa ter entre 1 e 2.000 caracteres para ser traduzida.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $idioma = ProfileLanguagePreference::load($conn, $ator['tipo'], $ator['id']); // Explicação DevIN: Atribui um valor à variável PHP $idioma.
            responderMensagens([ // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                'sucesso' => true, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'traducao' => traduzirMensagemMyMemory($texto, $idioma), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'idioma' => $idioma, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            ]); // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if ($acao === 'excluir_mensagem') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $idMensagem = filter_var($entrada['id_mensagem'] ?? null, FILTER_VALIDATE_INT); // Explicação DevIN: Atribui um valor à variável PHP $idMensagem.
            if ($idMensagem === false || $idMensagem < 1) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new InvalidArgumentException('Mensagem inválida.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $excluida = acessarArmazenamentoMensagens(static function (array &$armazenadas) use ($ator, $idMensagem): bool { // Explicação DevIN: Define função anônima usada como operação ou callback.
                foreach ($armazenadas as &$mensagem) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                    if ((int) ($mensagem['id'] ?? 0) !== $idMensagem) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        continue; // Explicação DevIN: Controla a continuação ou saída de laço/seleção.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

                    $atorEhParticipante = ( // Explicação DevIN: Atribui um valor à variável PHP $atorEhParticipante.
                        $mensagem['remetente_tipo'] === $ator['tipo'] // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                        && (int) $mensagem['remetente_id'] === $ator['id'] // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    ) || ( // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                        $mensagem['destinatario_tipo'] === $ator['tipo'] // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                        && (int) $mensagem['destinatario_id'] === $ator['id'] // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                    if (!$atorEhParticipante) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        return false; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

                    $chaveAtor = chaveAtorChat($ator); // Explicação DevIN: Atribui um valor à variável PHP $chaveAtor.
                    $ocultadaPara = $mensagem['ocultada_para'] ?? []; // Explicação DevIN: Atribui um valor à variável PHP $ocultadaPara.
                    if (!in_array($chaveAtor, $ocultadaPara, true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        $ocultadaPara[] = $chaveAtor; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                        $mensagem['ocultada_para'] = $ocultadaPara; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

                    return true; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                unset($mensagem); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.

                return false; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            if (!$excluida) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                responderMensagens(['sucesso' => false, 'mensagem' => 'Mensagem não encontrada.'], 404); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            responderMensagens(['sucesso' => true]); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if ($acao === 'excluir_para_todos') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $idMensagem = filter_var($entrada['id_mensagem'] ?? null, FILTER_VALIDATE_INT); // Explicação DevIN: Atribui um valor à variável PHP $idMensagem.
            if ($idMensagem === false || $idMensagem < 1) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new InvalidArgumentException('Mensagem inválida.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $excluida = acessarArmazenamentoMensagens(static function (array &$armazenadas) use ($ator, $idMensagem): bool { // Explicação DevIN: Define função anônima usada como operação ou callback.
                foreach ($armazenadas as &$mensagem) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                    if ( // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        (int) ($mensagem['id'] ?? 0) !== $idMensagem // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                        || $mensagem['remetente_tipo'] !== $ator['tipo'] // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                        || (int) $mensagem['remetente_id'] !== $ator['id'] // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                        || !empty($mensagem['apagada_para_todos']) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    ) { // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                        continue; // Explicação DevIN: Controla a continuação ou saída de laço/seleção.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

                    $mensagem['mensagem'] = ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    $mensagem['apagada_para_todos'] = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    return true; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                unset($mensagem); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.

                return false; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            if (!$excluida) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                responderMensagens(['sucesso' => false, 'mensagem' => 'Mensagem não encontrada ou não pode ser excluída para todos.'], 404); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            responderMensagens(['sucesso' => true]); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if ($acao === 'excluir_conversa') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            $tipo = is_string($entrada['tipo'] ?? null) ? $entrada['tipo'] : ''; // Explicação DevIN: Atribui um valor à variável PHP $tipo.
            $id = filter_var($entrada['id'] ?? null, FILTER_VALIDATE_INT); // Explicação DevIN: Atribui um valor à variável PHP $id.
            if ($id === false) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new InvalidArgumentException('Parâmetros da conversa inválidos.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            if (!($tipo === 'sistema' && $id === 0)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                destinatarioValido($conn, $ator, $tipo, $id); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            acessarArmazenamentoMensagens(static function (array &$armazenadas) use ($ator, $tipo, $id): void { // Explicação DevIN: Define função anônima usada como operação ou callback.
                $chaveAtor = chaveAtorChat($ator); // Explicação DevIN: Atribui um valor à variável PHP $chaveAtor.
                foreach ($armazenadas as &$mensagem) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                    if ( // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        !isset($mensagem['remetente_tipo'], $mensagem['remetente_id'], $mensagem['destinatario_tipo'], $mensagem['destinatario_id']) // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                    ) { // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                        continue; // Explicação DevIN: Controla a continuação ou saída de laço/seleção.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                    $mensagemPertenceAConversa = ( // Explicação DevIN: Atribui um valor à variável PHP $mensagemPertenceAConversa.
                        $mensagem['remetente_tipo'] === $ator['tipo'] // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                        && (int) $mensagem['remetente_id'] === $ator['id'] // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                        && $mensagem['destinatario_tipo'] === $tipo // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                        && (int) $mensagem['destinatario_id'] === $id // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    ) || ( // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                        $mensagem['remetente_tipo'] === $tipo // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                        && (int) $mensagem['remetente_id'] === $id // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                        && $mensagem['destinatario_tipo'] === $ator['tipo'] // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                        && (int) $mensagem['destinatario_id'] === $ator['id'] // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                    if (!$mensagemPertenceAConversa) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        continue; // Explicação DevIN: Controla a continuação ou saída de laço/seleção.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

                    $ocultadaPara = $mensagem['ocultada_para'] ?? []; // Explicação DevIN: Atribui um valor à variável PHP $ocultadaPara.
                    if (!in_array($chaveAtor, $ocultadaPara, true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        $ocultadaPara[] = $chaveAtor; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                        $mensagem['ocultada_para'] = $ocultadaPara; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                unset($mensagem); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            acessarArmazenamentoMensagens(static function (array &$conversas) use ($ator, $tipo, $id): void { // Explicação DevIN: Define função anônima usada como operação ou callback.
                $participantes = [chaveAtorChat($ator), $tipo . ':' . $id]; // Explicação DevIN: Atribui um valor à variável PHP $participantes.
                sort($participantes, SORT_STRING); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
                $chaveAtor = chaveAtorChat($ator); // Explicação DevIN: Atribui um valor à variável PHP $chaveAtor.
                foreach ($conversas as &$conversa) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                    if (($conversa['participantes'] ?? null) !== $participantes) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        continue; // Explicação DevIN: Controla a continuação ou saída de laço/seleção.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                    $ocultadaPara = $conversa['ocultada_para'] ?? []; // Explicação DevIN: Atribui um valor à variável PHP $ocultadaPara.
                    if (!in_array($chaveAtor, $ocultadaPara, true)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                        $ocultadaPara[] = $chaveAtor; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                        $conversa['ocultada_para'] = $ocultadaPara; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                unset($conversa); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
            }, 'conversas'); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.

            responderMensagens(['sucesso' => true]); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if ($acao !== 'enviar') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            responderMensagens(['sucesso' => false, 'mensagem' => 'Ação não reconhecida.'], 400); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        $tipo = is_string($entrada['tipo'] ?? null) ? $entrada['tipo'] : ''; // Explicação DevIN: Atribui um valor à variável PHP $tipo.
        $id = filter_var($entrada['id'] ?? null, FILTER_VALIDATE_INT); // Explicação DevIN: Atribui um valor à variável PHP $id.
        $mensagem = is_string($entrada['mensagem'] ?? null) ? trim($entrada['mensagem']) : ''; // Explicação DevIN: Atribui um valor à variável PHP $mensagem.
        if ($id === false || $mensagem === '' || mb_strlen($mensagem) > 2000) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new InvalidArgumentException('Escreva uma mensagem de até 2.000 caracteres.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        destinatarioValido($conn, $ator, $tipo, $id); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        registrarConversaChat($ator, $tipo, $id); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
        $novaMensagem = acessarArmazenamentoMensagens(static function (array &$armazenadas) use ($ator, $tipo, $id, $mensagem): array { // Explicação DevIN: Define função anônima usada como operação ou callback.
            $proximoId = 1; // Explicação DevIN: Atribui um valor à variável PHP $proximoId.
            foreach ($armazenadas as $armazenada) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
                $proximoId = max($proximoId, (int) $armazenada['id'] + 1); // Explicação DevIN: Atribui um valor à variável PHP $proximoId.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            $novaMensagem = [ // Explicação DevIN: Atribui um valor à variável PHP $novaMensagem.
                'id' => $proximoId, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'remetente_tipo' => $ator['tipo'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'remetente_id' => $ator['id'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'destinatario_tipo' => $tipo, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'destinatario_id' => $id, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'mensagem' => $mensagem, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'enviada_em' => date(DATE_ATOM), // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'lida_em' => null, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
            $armazenadas[] = $novaMensagem; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.

            return $novaMensagem; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        responderMensagens([ // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
            'sucesso' => true, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            'mensagem' => [ // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'id' => $novaMensagem['id'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'mensagem' => $mensagem, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'enviada_em' => $novaMensagem['enviada_em'], // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                'minha' => true, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            ], // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        ], 201); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
        $conn->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} catch (InvalidArgumentException $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    responderMensagens(['sucesso' => false, 'mensagem' => $exception->getMessage()], 400); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
} catch (Throwable $exception) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    error_log('Erro na API de mensagens: ' . $exception->getMessage()); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    responderMensagens(['sucesso' => false, 'mensagem' => 'Não foi possível concluir a operação de mensagens.'], 500); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
