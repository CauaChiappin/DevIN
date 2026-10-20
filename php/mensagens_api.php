<?php

declare(strict_types=1);

require_once __DIR__ . '/middlewares/auth.php';
require_once __DIR__ . '/config/security.php';

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

function mensagemOcultaParaAtor(array $mensagem, array $ator): bool
{
    return in_array(chaveAtorChat($ator), $mensagem['ocultada_para'] ?? [], true);
}

function resumirConversasChat(array $mensagens, array $ator): array
{
    $contatos = [];

    foreach ($mensagens as $mensagem) {
        if (mensagemOcultaParaAtor($mensagem, $ator)) {
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

function acessarArmazenamentoMensagens(callable $operacao): mixed
{
    $caminho = sys_get_temp_dir() . DIRECTORY_SEPARATOR
        . 'devin-mensagens-' . hash('sha256', dirname(__DIR__)) . '.json';
    $arquivo = fopen($caminho, 'c+');
    if ($arquivo === false) {
        throw new RuntimeException('Não foi possível abrir o armazenamento temporário de mensagens.');
    }

    try {
        if (!chmod($caminho, 0600)) {
            throw new RuntimeException('Não foi possível proteger o armazenamento temporário de mensagens.');
        }
        if (!flock($arquivo, LOCK_EX)) {
            throw new RuntimeException('Não foi possível bloquear o armazenamento temporário de mensagens.');
        }

        rewind($arquivo);
        $conteudo = stream_get_contents($arquivo);
        if ($conteudo === false) {
            throw new RuntimeException('Não foi possível ler o armazenamento temporário de mensagens.');
        }

        $mensagens = $conteudo === ''
            ? []
            : json_decode($conteudo, true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($mensagens)) {
            throw new RuntimeException('O armazenamento temporário de mensagens está inválido.');
        }

        $resultado = $operacao($mensagens);
        $novoConteudo = json_encode($mensagens, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        if ($novoConteudo !== $conteudo) {
            rewind($arquivo);
            if (!ftruncate($arquivo, 0)) {
                throw new RuntimeException('Não foi possível salvar a mensagem.');
            }

            $offset = 0;
            while ($offset < strlen($novoConteudo)) {
                $escritos = fwrite($arquivo, substr($novoConteudo, $offset));
                if ($escritos === false || $escritos === 0) {
                    throw new RuntimeException('Não foi possível salvar a mensagem.');
                }
                $offset += $escritos;
            }
            if (!fflush($arquivo)) {
                throw new RuntimeException('Não foi possível salvar a mensagem.');
            }
        }

        flock($arquivo, LOCK_UN);
        return $resultado;
    } finally {
        fclose($arquivo);
    }
}

function buscarUsuarioChat(mysqli $conn, string $tipo, int $id): ?array
{
    if (!validarTipoChat($tipo) || $id < 1) {
        return null;
    }

    $tabela = $tipo === 'pessoa' ? 'pessoa' : 'empresa';
    $coluna = $tipo === 'pessoa' ? 'id_pessoa' : 'id_empresa';
    $stmt = $conn->prepare("SELECT {$coluna} AS id, nome FROM {$tabela} WHERE {$coluna} = ? LIMIT 1");
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

                $mime = (new finfo(FILEINFO_MIME_TYPE))->buffer($foto);
                if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
                    http_response_code(404);
                    exit;
                }

                header('Content-Type: ' . $mime);
                header('Cache-Control: private, max-age=300');
                header('X-Content-Type-Options: nosniff');
                echo $foto;
                exit;
            }

            if ($acao === 'contatos') {
                $resumo = acessarArmazenamentoMensagens(
                    static fn (array &$armazenadas): array => resumirConversasChat($armazenadas, $ator)
                );
                $contatos = [];
                $resultPessoas = $conn->query('SELECT id_pessoa AS id, nome FROM pessoa ORDER BY nome');
                foreach ($resultPessoas->fetch_all(MYSQLI_ASSOC) as $pessoa) {
                    if ($ator['tipo'] !== 'pessoa' || (int) $pessoa['id'] !== $ator['id']) {
                        $chave = 'pessoa:' . (int) $pessoa['id'];
                        $contatos[] = [
                            'id' => (int) $pessoa['id'],
                            'tipo' => 'pessoa',
                            'nome' => (string) $pessoa['nome'],
                            'rotulo' => 'Pessoa',
                            'ultima_mensagem_id' => $resumo['contatos'][$chave]['ultima_mensagem_id'] ?? 0,
                            'nao_lidas' => $resumo['contatos'][$chave]['nao_lidas'] ?? 0,
                        ];
                    }
                }

                $resultEmpresas = $conn->query('SELECT id_empresa AS id, nome FROM empresa ORDER BY nome');
                foreach ($resultEmpresas->fetch_all(MYSQLI_ASSOC) as $empresa) {
                    if ($ator['tipo'] !== 'empresa' || (int) $empresa['id'] !== $ator['id']) {
                        $chave = 'empresa:' . (int) $empresa['id'];
                        $contatos[] = [
                            'id' => (int) $empresa['id'],
                            'tipo' => 'empresa',
                            'nome' => (string) $empresa['nome'],
                            'rotulo' => 'Empresa',
                            'ultima_mensagem_id' => $resumo['contatos'][$chave]['ultima_mensagem_id'] ?? 0,
                            'nao_lidas' => $resumo['contatos'][$chave]['nao_lidas'] ?? 0,
                        ];
                    }
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
                $resumo = acessarArmazenamentoMensagens(
                    static fn (array &$armazenadas): array => resumirConversasChat($armazenadas, $ator)
                );
                responderMensagens(['sucesso' => true, 'nao_lidas' => $resumo['nao_lidas']]);
            }

            if ($acao === 'conversa') {
                $tipo = requestString($_GET, 'tipo');
                $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
                $depoisDe = filter_var($_GET['depois_de'] ?? 0, FILTER_VALIDATE_INT);
                if ($id === false || $depoisDe === false || $depoisDe < 0) {
                    throw new InvalidArgumentException('Parâmetros da conversa inválidos.');
                }
                destinatarioValido($conn, $ator, $tipo, $id);

                $resultado = acessarArmazenamentoMensagens(static function (array &$armazenadas) use ($ator, $tipo, $id, $depoisDe): array {
                    $conversa = array_values(array_filter($armazenadas, static function (array $mensagem) use ($ator, $tipo, $id): bool {
                        if (mensagemOcultaParaAtor($mensagem, $ator)) {
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
            destinatarioValido($conn, $ator, $tipo, $id);

            acessarArmazenamentoMensagens(static function (array &$armazenadas) use ($ator, $tipo, $id): void {
                $chaveAtor = chaveAtorChat($ator);
                foreach ($armazenadas as &$mensagem) {
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
