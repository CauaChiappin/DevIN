<?php

declare(strict_types=1);

require_once __DIR__ . '/middlewares/auth.php';
require_once __DIR__ . '/config/security.php';
require_once __DIR__ . '/controllers/ProfileLanguagePreference.php';
require_once __DIR__ . '/controllers/ChatStorage.php';
require_once __DIR__ . '/helpers.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');

function responderMensagens(array $dados, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function validarTipoChat(string $tipo): bool
{
    return in_array($tipo, ['pessoa', 'empresa'], true);
}

function chaveAtorChat(array $ator): string
{
    return $ator['tipo'] . ':' . $ator['id'];
}

function registrarConversaChat(array $ator, string $tipo, int $id): void
{
    $participantes = [chaveAtorChat($ator), $tipo . ':' . $id];
    sort($participantes, SORT_STRING);
    $chaveAtor = chaveAtorChat($ator);

    acessarArmazenamentoMensagens(static function (array &$conversas) use ($participantes, $chaveAtor): void {
        foreach ($conversas as &$conversa) {
            if (($conversa['participantes'] ?? null) !== $participantes) {
                continue;
            }

            $ocultadaPara = $conversa['ocultada_para'] ?? [];
            $conversa['ocultada_para'] = array_values(array_filter(
                $ocultadaPara,
                static fn ($participante): bool => $participante !== $chaveAtor
            ));
            unset($conversa);
            return;
        }
        unset($conversa);

        $conversas[] = [
            'participantes' => $participantes,
            'ocultada_para' => [],
        ];
    }, 'conversas');
}

function mensagemOcultaParaAtor(array $mensagem, array $ator): bool
{
    return in_array(chaveAtorChat($ator), $mensagem['ocultada_para'] ?? [], true);
}

function traduzirMensagemMyMemory(string $texto, string $idioma): string
{
    if (!function_exists('curl_init')) {
        responderMensagens([
            'sucesso' => false,
            'mensagem' => 'A extensão cURL do PHP é necessária para traduzir mensagens.',
        ], 503);
    }

    $idiomasMyMemory = ['pt-BR' => 'pt-BR', 'en' => 'en', 'es' => 'es'];
    $idiomaAlvo = $idiomasMyMemory[$idioma] ?? null;
    if ($idiomaAlvo === null) {
        throw new RuntimeException('O idioma selecionado para tradução é inválido.');
    }

    $restante = $texto;
    $trechos = [];
    while (strlen($restante) > 450) {
        $trecho = mb_strcut($restante, 0, 450, 'UTF-8');
        $ultimoEspaco = mb_strrpos($trecho, ' ');
        $tamanhoTrecho = $ultimoEspaco !== false && $ultimoEspaco > 0
            ? $ultimoEspaco + 1
            : mb_strlen($trecho, 'UTF-8');
        $trechos[] = mb_substr($restante, 0, $tamanhoTrecho, 'UTF-8');
        $restante = mb_substr($restante, $tamanhoTrecho, null, 'UTF-8');
    }
    if ($restante !== '') {
        $trechos[] = $restante;
    }

    $traducao = '';
    foreach ($trechos as $trecho) {
        $url = 'https://api.mymemory.translated.net/get?' . http_build_query([
            'q' => $trecho,
            'langpair' => 'autodetect|' . $idiomaAlvo,
        ]);
        $curl = curl_init($url);
        if ($curl === false) {
            throw new RuntimeException('Não foi possível iniciar a conexão com o serviço de tradução.');
        }

        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 12,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $resposta = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $erro = curl_error($curl);
        curl_close($curl);

        if (!is_string($resposta) || $status < 200 || $status >= 300) {
            error_log('Falha no serviço MyMemory: HTTP ' . $status . ($erro !== '' ? ' (' . $erro . ')' : ''));
            responderMensagens([
                'sucesso' => false,
                'mensagem' => 'Não foi possível traduzir agora. Tente novamente.',
            ], 503);
        }

        try {
            $dados = json_decode($resposta, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            error_log('Resposta inválida do serviço MyMemory.');
            throw new RuntimeException('O serviço de tradução retornou uma resposta inválida.', 0, $exception);
        }

        if (!empty($dados['quotaFinished'])) {
            responderMensagens([
                'sucesso' => false,
                'mensagem' => 'Limite gratuito diário de tradução atingido. Tente novamente amanhã.',
            ], 429);
        }

        $statusTraducao = (int) ($dados['responseStatus'] ?? 0);
        $textoTraduzido = $dados['responseData']['translatedText'] ?? null;
        if ($statusTraducao !== 200 || !is_string($textoTraduzido) || $textoTraduzido === '') {
            error_log('MyMemory não retornou tradução válida: ' . (string) ($dados['responseDetails'] ?? ''));
            responderMensagens([
                'sucesso' => false,
                'mensagem' => 'Não foi possível traduzir agora. Tente novamente.',
            ], 503);
        }

        $traducao .= $textoTraduzido;
    }

    return $traducao;
}

function resumirConversasChat(array $mensagens, array $ator, array $conversas = []): array
{
    $contatos = [];

    foreach ($conversas as $conversa) {
        $participantes = $conversa['participantes'] ?? [];
        if (!is_array($participantes) || mensagemOcultaParaAtor($conversa, $ator)) {
            continue;
        }

        $atorChave = chaveAtorChat($ator);
        if (!in_array($atorChave, $participantes, true)) {
            continue;
        }
        $outroParticipante = array_values(array_filter(
            $participantes,
            static fn ($participante): bool => is_string($participante) && $participante !== $atorChave
        ))[0] ?? '';
        [$tipoContato, $idContato] = array_pad(explode(':', $outroParticipante, 2), 2, '');
        $idContatoNumerico = (int) $idContato;
        $contatoSistema = $tipoContato === 'sistema' && $idContatoNumerico === 0;
        $contatoUsuario = in_array($tipoContato, ['pessoa', 'empresa'], true) && $idContatoNumerico > 0;
        if (!$contatoSistema && !$contatoUsuario) {
            continue;
        }

        $chave = $tipoContato . ':' . $idContatoNumerico;
        $contatos[$chave] = ['ultima_mensagem_id' => 0, 'nao_lidas' => 0];
    }

    foreach ($mensagens as $mensagem) {
        if (!isset($mensagem['remetente_tipo'], $mensagem['destinatario_tipo']) || mensagemOcultaParaAtor($mensagem, $ator)) {
            continue;
        }

        $remetenteEhAtor = $mensagem['remetente_tipo'] === $ator['tipo']
            && (int) $mensagem['remetente_id'] === $ator['id'];
        $destinatarioEhAtor = $mensagem['destinatario_tipo'] === $ator['tipo']
            && (int) $mensagem['destinatario_id'] === $ator['id'];
        if (!$remetenteEhAtor && !$destinatarioEhAtor) {
            continue;
        }

        $tipoContato = $remetenteEhAtor ? $mensagem['destinatario_tipo'] : $mensagem['remetente_tipo'];
        $idContato = (int) ($remetenteEhAtor ? $mensagem['destinatario_id'] : $mensagem['remetente_id']);
        $chave = $tipoContato . ':' . $idContato;
        $idMensagem = (int) $mensagem['id'];
        if (!isset($contatos[$chave])) {
            $contatos[$chave] = ['ultima_mensagem_id' => 0, 'nao_lidas' => 0];
        }

        $contatos[$chave]['ultima_mensagem_id'] = max($contatos[$chave]['ultima_mensagem_id'], $idMensagem);
        if ($destinatarioEhAtor && $mensagem['lida_em'] === null) {
            $contatos[$chave]['nao_lidas']++;
        }
    }

    return [
        'contatos' => $contatos,
        'nao_lidas' => array_sum(array_column($contatos, 'nao_lidas')),
    ];
}

function resumirArmazenamentoChat(array $ator): array
{
    $mensagens = acessarArmazenamentoMensagens(static fn (array &$armazenadas): array => $armazenadas);
    $conversas = acessarArmazenamentoMensagens(
        static fn (array &$armazenadas): array => $armazenadas,
        'conversas'
    );

    return resumirConversasChat($mensagens, $ator, $conversas);
}

function buscarUsuarioChat(mysqli $conn, string $tipo, int $id): ?array
{
    if ($tipo === 'sistema' && $id === 0) {
        return ['id' => 0, 'nome' => 'Plataforma DevIN', 'email' => ''];
    }

    if (!validarTipoChat($tipo) || $id < 1) {
        return null;
    }

    $tabela = $tipo === 'pessoa' ? 'pessoa' : 'empresa';
    $coluna = $tipo === 'pessoa' ? 'id_pessoa' : 'id_empresa';
    $stmt = $conn->prepare("SELECT {$coluna} AS id, nome, email FROM {$tabela} WHERE {$coluna} = ? LIMIT 1");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $usuario = $stmt->get_result()->fetch_assoc() ?: null;
    $stmt->close();

    return $usuario;
}

function destinatarioValido(mysqli $conn, array $ator, string $tipo, int $id): array
{
    if (!validarTipoChat($tipo) || $id < 1 || ($tipo === $ator['tipo'] && $id === $ator['id'])) {
        throw new InvalidArgumentException('Destinatário inválido.');
    }

    $destinatario = buscarUsuarioChat($conn, $tipo, $id);
    if ($destinatario === null) {
        throw new InvalidArgumentException('Este usuário não está disponível para conversa.');
    }

    return $destinatario;
}

try {
    $ator = requireWebAuth();
    if (!in_array($ator['tipo'], ['pessoa', 'empresa'], true)) {
        responderMensagens(['sucesso' => false, 'mensagem' => 'Mensagens não estão disponíveis para este perfil.'], 403);
    }

    $conn = getDatabaseConnection();
    try {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $acao = requestString($_GET, 'acao');

            if ($acao === 'foto') {
                $tipo = requestString($_GET, 'tipo');
                $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
                $perfis = [
                    'pessoa' => ['pessoa', 'id_pessoa'],
                    'empresa' => ['empresa', 'id_empresa'],
                ];
                if (!isset($perfis[$tipo]) || $id === false || $id < 1) {
                    throw new InvalidArgumentException('Perfil inválido.');
                }

                [$tabela, $colunaId] = $perfis[$tipo];
                $stmt = $conn->prepare("SELECT foto FROM {$tabela} WHERE {$colunaId} = ? LIMIT 1");
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $foto = $stmt->get_result()->fetch_column();
                $stmt->close();
                $conn->close();

                if (!is_string($foto) || $foto === '') {
                    http_response_code(404);
                    exit;
                }

                $conteudoFoto = $foto;
                $caminhoFoto = profilePhotoFilePath($foto, $tipo, $id);
                if ($caminhoFoto !== null) {
                    $conteudoFoto = file_get_contents($caminhoFoto);
                    if ($conteudoFoto === false) {
                        http_response_code(404);
                        exit;
                    }
                }

                $mime = (new finfo(FILEINFO_MIME_TYPE))->buffer($conteudoFoto);
                if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
                    http_response_code(404);
                    exit;
                }

                $etag = '"' . hash('sha256', $conteudoFoto) . '"';
                header('ETag: ' . $etag);
                header('Cache-Control: private, no-cache, must-revalidate');
                $etagsSolicitadas = array_map(
                    'trim',
                    explode(',', $_SERVER['HTTP_IF_NONE_MATCH'] ?? '')
                );
                if (in_array($etag, $etagsSolicitadas, true) || in_array('*', $etagsSolicitadas, true)) {
                    http_response_code(304);
                    exit;
                }

                header('Content-Type: ' . $mime);
                header('X-Content-Type-Options: nosniff');
                echo $conteudoFoto;
                exit;
            }

            if ($acao === 'buscar_contatos') {
                $termo = requestString($_GET, 'termo');
                if (mb_strlen($termo) < 2 || mb_strlen($termo) > 100) {
                    responderMensagens(['sucesso' => true, 'contatos' => []]);
                }

                $contatosEncontrados = [];
                $busca = '%' . $termo . '%';
                foreach ([
                    'pessoa' => ['pessoa', 'id_pessoa', 'Pessoa'],
                    'empresa' => ['empresa', 'id_empresa', 'Empresa'],
                ] as $tipo => [$tabela, $colunaId, $rotulo]) {
                    $stmt = $conn->prepare(
                        "SELECT {$colunaId} AS id, nome, email FROM {$tabela}
                         WHERE (nome LIKE ? OR email LIKE ?)
                           AND NOT ({$colunaId} = ? AND ? = ?)
                         ORDER BY nome LIMIT 25"
                    );
                    $stmt->bind_param('ssiss', $busca, $busca, $ator['id'], $tipo, $ator['tipo']);
                    $stmt->execute();
                    foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $usuario) {
                        $contatosEncontrados[] = [
                            'id' => (int) $usuario['id'],
                            'tipo' => $tipo,
                            'nome' => (string) $usuario['nome'],
                            'email' => (string) $usuario['email'],
                            'rotulo' => $rotulo,
                            'nao_lidas' => 0,
                            'ultima_mensagem_id' => 0,
                        ];
                    }
                    $stmt->close();
                }

                usort($contatosEncontrados, static fn (array $a, array $b): int =>
                    strnatcasecmp($a['nome'], $b['nome']) ?: strnatcasecmp($a['email'], $b['email'])
                );
                responderMensagens(['sucesso' => true, 'contatos' => array_slice($contatosEncontrados, 0, 30)]);
            }

            if ($acao === 'contatos') {
                $resumo = resumirArmazenamentoChat($ator);
                $contatos = [];
                foreach ($resumo['contatos'] as $chave => $infoContato) {
                    [$tipoContato, $idContato] = explode(':', $chave, 2);
                    $usuario = buscarUsuarioChat($conn, $tipoContato, (int) $idContato);
                    if ($usuario === null) {
                        continue;
                    }
                    switch ($tipoContato) {
                        case 'pessoa':
                            $rotuloContato = 'Pessoa';
                            break;
                        case 'empresa':
                            $rotuloContato = 'Empresa';
                            break;
                        case 'sistema':
                            $rotuloContato = 'Notificação da plataforma';
                            break;
                        default:
                            $rotuloContato = 'Contato';
                    }
                    $contatos[] = [
                        'id' => (int) $usuario['id'],
                        'tipo' => $tipoContato,
                        'nome' => (string) $usuario['nome'],
                        'email' => (string) $usuario['email'],
                        'rotulo' => $rotuloContato,
                        'ultima_mensagem_id' => $infoContato['ultima_mensagem_id'],
                        'nao_lidas' => $infoContato['nao_lidas'],
                    ];
                }

                usort($contatos, static function (array $a, array $b): int {
                    $recencia = $b['ultima_mensagem_id'] <=> $a['ultima_mensagem_id'];
                    return $recencia !== 0 ? $recencia : strnatcasecmp($a['nome'], $b['nome']);
                });
                responderMensagens([
                    'sucesso' => true,
                    'contatos' => $contatos,
                    'nao_lidas' => $resumo['nao_lidas'],
                ]);
            }

            if ($acao === 'nao_lidas') {
                $resumo = resumirArmazenamentoChat($ator);
                responderMensagens(['sucesso' => true, 'nao_lidas' => $resumo['nao_lidas']]);
            }

            if ($acao === 'conversa') {
                $tipo = requestString($_GET, 'tipo');
                $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
                $depoisDe = filter_var($_GET['depois_de'] ?? 0, FILTER_VALIDATE_INT);
                if ($id === false || $depoisDe === false || $depoisDe < 0) {
                    throw new InvalidArgumentException('Parâmetros da conversa inválidos.');
                }
                if (!($tipo === 'sistema' && $id === 0)) {
                    destinatarioValido($conn, $ator, $tipo, $id);
                }

                $resultado = acessarArmazenamentoMensagens(static function (array &$armazenadas) use ($ator, $tipo, $id, $depoisDe): array {
                    $conversa = array_values(array_filter($armazenadas, static function (array $mensagem) use ($ator, $tipo, $id): bool {
                        if (
                            !isset($mensagem['remetente_tipo'], $mensagem['remetente_id'], $mensagem['destinatario_tipo'], $mensagem['destinatario_id'])
                            || mensagemOcultaParaAtor($mensagem, $ator)
                        ) {
                            return false;
                        }

                        return (
                            $mensagem['remetente_tipo'] === $ator['tipo']
                            && (int) $mensagem['remetente_id'] === $ator['id']
                            && $mensagem['destinatario_tipo'] === $tipo
                            && (int) $mensagem['destinatario_id'] === $id
                        ) || (
                            $mensagem['remetente_tipo'] === $tipo
                            && (int) $mensagem['remetente_id'] === $id
                            && $mensagem['destinatario_tipo'] === $ator['tipo']
                            && (int) $mensagem['destinatario_id'] === $ator['id']
                        );
                    }));
                    usort($conversa, static fn (array $a, array $b): int => $a['id'] <=> $b['id']);

                    foreach ($armazenadas as &$mensagem) {
                        if (
                            !isset($mensagem['remetente_tipo'], $mensagem['remetente_id'], $mensagem['destinatario_tipo'], $mensagem['destinatario_id'])
                            || !array_key_exists('lida_em', $mensagem)
                        ) {
                            continue;
                        }
                        if (
                            !mensagemOcultaParaAtor($mensagem, $ator)
                            && $mensagem['remetente_tipo'] === $tipo
                            && (int) $mensagem['remetente_id'] === $id
                            && $mensagem['destinatario_tipo'] === $ator['tipo']
                            && (int) $mensagem['destinatario_id'] === $ator['id']
                            && $mensagem['lida_em'] === null
                        ) {
                            $mensagem['lida_em'] = date(DATE_ATOM);
                        }
                    }
                    unset($mensagem);

                    if ($depoisDe === 0) {
                        $conversa = array_slice($conversa, -50);
                    } else {
                        $recentes = array_slice($conversa, -50);
                        $novas = array_values(array_filter(
                            $conversa,
                            static fn (array $mensagem): bool => $mensagem['id'] > $depoisDe
                        ));
                        $atualizadas = array_values(array_filter(
                            $recentes,
                            static fn (array $mensagem): bool => !empty($mensagem['apagada_para_todos'])
                        ));
                        $porId = [];
                        foreach (array_merge(array_slice($novas, 0, 50), $atualizadas) as $mensagem) {
                            $porId[(int) $mensagem['id']] = $mensagem;
                        }
                        $conversa = array_values($porId);
                        usort($conversa, static fn (array $a, array $b): int => $a['id'] <=> $b['id']);
                    }

                    return array_map(static fn (array $mensagem): array => [
                        'id' => (int) $mensagem['id'],
                        'mensagem' => !empty($mensagem['apagada_para_todos'])
                            ? ''
                            : (string) $mensagem['mensagem'],
                        'enviada_em' => $mensagem['enviada_em'],
                        'minha' => $mensagem['remetente_tipo'] === $ator['tipo']
                            && (int) $mensagem['remetente_id'] === $ator['id'],
                        'apagada_para_todos' => !empty($mensagem['apagada_para_todos']),
                    ], $conversa);
                });

                responderMensagens(['sucesso' => true, 'mensagens' => $resultado]);
            }

            responderMensagens(['sucesso' => false, 'mensagem' => 'Ação não reconhecida.'], 400);
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Allow: GET, POST');
            responderMensagens(['sucesso' => false, 'mensagem' => 'Método não permitido.'], 405);
        }

        requireValidCsrf();
        $entrada = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($entrada)) {
            throw new InvalidArgumentException('Não foi possível ler os dados da mensagem.');
        }

        $acao = is_string($entrada['acao'] ?? null) ? $entrada['acao'] : '';
        if ($acao === 'iniciar_conversa') {
            $tipo = is_string($entrada['tipo'] ?? null) ? $entrada['tipo'] : '';
            $id = filter_var($entrada['id'] ?? null, FILTER_VALIDATE_INT);
            if ($id === false || $id < 1) {
                throw new InvalidArgumentException('Parâmetros da conversa inválidos.');
            }

            $destinatario = destinatarioValido($conn, $ator, $tipo, $id);
            registrarConversaChat($ator, $tipo, $id);
            responderMensagens([
                'sucesso' => true,
                'contato' => [
                    'id' => $id,
                    'tipo' => $tipo,
                    'nome' => (string) $destinatario['nome'],
                    'email' => (string) $destinatario['email'],
                    'rotulo' => $tipo === 'pessoa' ? 'Pessoa' : 'Empresa',
                    'nao_lidas' => 0,
                    'ultima_mensagem_id' => 0,
                ],
            ]);
        }

        if ($acao === 'traduzir') {
            $texto = is_string($entrada['texto'] ?? null) ? trim($entrada['texto']) : '';
            if ($texto === '' || mb_strlen($texto) > 2000) {
                throw new InvalidArgumentException('A mensagem precisa ter entre 1 e 2.000 caracteres para ser traduzida.');
            }

            $idioma = ProfileLanguagePreference::load($conn, $ator['tipo'], $ator['id']);
            responderMensagens([
                'sucesso' => true,
                'traducao' => traduzirMensagemMyMemory($texto, $idioma),
                'idioma' => $idioma,
            ]);
        }

        if ($acao === 'excluir_mensagem') {
            $idMensagem = filter_var($entrada['id_mensagem'] ?? null, FILTER_VALIDATE_INT);
            if ($idMensagem === false || $idMensagem < 1) {
                throw new InvalidArgumentException('Mensagem inválida.');
            }

            $excluida = acessarArmazenamentoMensagens(static function (array &$armazenadas) use ($ator, $idMensagem): bool {
                foreach ($armazenadas as &$mensagem) {
                    if ((int) ($mensagem['id'] ?? 0) !== $idMensagem) {
                        continue;
                    }

                    $atorEhParticipante = (
                        $mensagem['remetente_tipo'] === $ator['tipo']
                        && (int) $mensagem['remetente_id'] === $ator['id']
                    ) || (
                        $mensagem['destinatario_tipo'] === $ator['tipo']
                        && (int) $mensagem['destinatario_id'] === $ator['id']
                    );
                    if (!$atorEhParticipante) {
                        return false;
                    }

                    $chaveAtor = chaveAtorChat($ator);
                    $ocultadaPara = $mensagem['ocultada_para'] ?? [];
                    if (!in_array($chaveAtor, $ocultadaPara, true)) {
                        $ocultadaPara[] = $chaveAtor;
                        $mensagem['ocultada_para'] = $ocultadaPara;
                    }

                    return true;
                }
                unset($mensagem);

                return false;
            });
            if (!$excluida) {
                responderMensagens(['sucesso' => false, 'mensagem' => 'Mensagem não encontrada.'], 404);
            }

            responderMensagens(['sucesso' => true]);
        }

        if ($acao === 'excluir_para_todos') {
            $idMensagem = filter_var($entrada['id_mensagem'] ?? null, FILTER_VALIDATE_INT);
            if ($idMensagem === false || $idMensagem < 1) {
                throw new InvalidArgumentException('Mensagem inválida.');
            }

            $excluida = acessarArmazenamentoMensagens(static function (array &$armazenadas) use ($ator, $idMensagem): bool {
                foreach ($armazenadas as &$mensagem) {
                    if (
                        (int) ($mensagem['id'] ?? 0) !== $idMensagem
                        || $mensagem['remetente_tipo'] !== $ator['tipo']
                        || (int) $mensagem['remetente_id'] !== $ator['id']
                        || !empty($mensagem['apagada_para_todos'])
                    ) {
                        continue;
                    }

                    $mensagem['mensagem'] = '';
                    $mensagem['apagada_para_todos'] = true;
                    return true;
                }
                unset($mensagem);

                return false;
            });
            if (!$excluida) {
                responderMensagens(['sucesso' => false, 'mensagem' => 'Mensagem não encontrada ou não pode ser excluída para todos.'], 404);
            }

            responderMensagens(['sucesso' => true]);
        }

        if ($acao === 'excluir_conversa') {
            $tipo = is_string($entrada['tipo'] ?? null) ? $entrada['tipo'] : '';
            $id = filter_var($entrada['id'] ?? null, FILTER_VALIDATE_INT);
            if ($id === false) {
                throw new InvalidArgumentException('Parâmetros da conversa inválidos.');
            }
            if (!($tipo === 'sistema' && $id === 0)) {
                destinatarioValido($conn, $ator, $tipo, $id);
            }

            acessarArmazenamentoMensagens(static function (array &$armazenadas) use ($ator, $tipo, $id): void {
                $chaveAtor = chaveAtorChat($ator);
                foreach ($armazenadas as &$mensagem) {
                    if (
                        !isset($mensagem['remetente_tipo'], $mensagem['remetente_id'], $mensagem['destinatario_tipo'], $mensagem['destinatario_id'])
                    ) {
                        continue;
                    }
                    $mensagemPertenceAConversa = (
                        $mensagem['remetente_tipo'] === $ator['tipo']
                        && (int) $mensagem['remetente_id'] === $ator['id']
                        && $mensagem['destinatario_tipo'] === $tipo
                        && (int) $mensagem['destinatario_id'] === $id
                    ) || (
                        $mensagem['remetente_tipo'] === $tipo
                        && (int) $mensagem['remetente_id'] === $id
                        && $mensagem['destinatario_tipo'] === $ator['tipo']
                        && (int) $mensagem['destinatario_id'] === $ator['id']
                    );
                    if (!$mensagemPertenceAConversa) {
                        continue;
                    }

                    $ocultadaPara = $mensagem['ocultada_para'] ?? [];
                    if (!in_array($chaveAtor, $ocultadaPara, true)) {
                        $ocultadaPara[] = $chaveAtor;
                        $mensagem['ocultada_para'] = $ocultadaPara;
                    }
                }
                unset($mensagem);
            });
            acessarArmazenamentoMensagens(static function (array &$conversas) use ($ator, $tipo, $id): void {
                $participantes = [chaveAtorChat($ator), $tipo . ':' . $id];
                sort($participantes, SORT_STRING);
                $chaveAtor = chaveAtorChat($ator);
                foreach ($conversas as &$conversa) {
                    if (($conversa['participantes'] ?? null) !== $participantes) {
                        continue;
                    }
                    $ocultadaPara = $conversa['ocultada_para'] ?? [];
                    if (!in_array($chaveAtor, $ocultadaPara, true)) {
                        $ocultadaPara[] = $chaveAtor;
                        $conversa['ocultada_para'] = $ocultadaPara;
                    }
                }
                unset($conversa);
            }, 'conversas');

            responderMensagens(['sucesso' => true]);
        }

        if ($acao !== 'enviar') {
            responderMensagens(['sucesso' => false, 'mensagem' => 'Ação não reconhecida.'], 400);
        }

        $tipo = is_string($entrada['tipo'] ?? null) ? $entrada['tipo'] : '';
        $id = filter_var($entrada['id'] ?? null, FILTER_VALIDATE_INT);
        $mensagem = is_string($entrada['mensagem'] ?? null) ? trim($entrada['mensagem']) : '';
        if ($id === false || $mensagem === '' || mb_strlen($mensagem) > 2000) {
            throw new InvalidArgumentException('Escreva uma mensagem de até 2.000 caracteres.');
        }

        destinatarioValido($conn, $ator, $tipo, $id);
        registrarConversaChat($ator, $tipo, $id);
        $novaMensagem = acessarArmazenamentoMensagens(static function (array &$armazenadas) use ($ator, $tipo, $id, $mensagem): array {
            $proximoId = 1;
            foreach ($armazenadas as $armazenada) {
                $proximoId = max($proximoId, (int) $armazenada['id'] + 1);
            }

            $novaMensagem = [
                'id' => $proximoId,
                'remetente_tipo' => $ator['tipo'],
                'remetente_id' => $ator['id'],
                'destinatario_tipo' => $tipo,
                'destinatario_id' => $id,
                'mensagem' => $mensagem,
                'enviada_em' => date(DATE_ATOM),
                'lida_em' => null,
            ];
            $armazenadas[] = $novaMensagem;

            return $novaMensagem;
        });

        responderMensagens([
            'sucesso' => true,
            'mensagem' => [
                'id' => $novaMensagem['id'],
                'mensagem' => $mensagem,
                'enviada_em' => $novaMensagem['enviada_em'],
                'minha' => true,
            ],
        ], 201);
    } finally {
        $conn->close();
    }
} catch (InvalidArgumentException $exception) {
    responderMensagens(['sucesso' => false, 'mensagem' => $exception->getMessage()], 400);
} catch (Throwable $exception) {
    error_log('Erro na API de mensagens: ' . $exception->getMessage());
    responderMensagens(['sucesso' => false, 'mensagem' => 'Não foi possível concluir a operação de mensagens.'], 500);
}
