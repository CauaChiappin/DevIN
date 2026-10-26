<?php

declare(strict_types=1);

function mensagemConclusaoReportExpirada(array $mensagem, ?int $agora = null): bool
{
    if (strpos((string) ($mensagem['chave_evento'] ?? ''), 'job-report:') !== 0) {
        return false;
    }

    $expiraEm = $mensagem['expira_em'] ?? null;
    if (!is_string($expiraEm) || $expiraEm === '') {
        $enviadaEm = $mensagem['enviada_em'] ?? null;
        if (!is_string($enviadaEm) || $enviadaEm === '') {
            return false;
        }
        $instanteEnvio = strtotime($enviadaEm);
        if ($instanteEnvio === false) {
            return false;
        }
        $instanteExpiracao = $instanteEnvio + 86400;
    } else {
        $instanteExpiracao = strtotime($expiraEm);
        if ($instanteExpiracao === false) {
            return false;
        }
    }

    return $instanteExpiracao <= ($agora ?? time());
}

/** @return mixed Retorna o resultado produzido pela operação sobre o armazenamento. */
function acessarArmazenamentoMensagens(callable $operacao, string $recurso = 'mensagens')
{
    if (!in_array($recurso, ['mensagens', 'conversas'], true)) {
        throw new InvalidArgumentException('Armazenamento de mensagens inválido.');
    }

    $caminho = sys_get_temp_dir() . DIRECTORY_SEPARATOR
        . 'devin-' . $recurso . '-' . hash('sha256', dirname(__DIR__, 2)) . '.json';
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

        if ($recurso === 'mensagens') {
            $mensagensAtuais = count($mensagens);
            $mensagens = array_values(array_filter(
                $mensagens,
                static fn (array $mensagem): bool => !mensagemConclusaoReportExpirada($mensagem)
            ));
            if (count($mensagens) !== $mensagensAtuais) {
                $conteudo = '';
            }
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

function appendPlatformChatNotification(string $eventKey, string $recipientType, int $recipientId, string $message): void
{
    if ($eventKey === '' || !in_array($recipientType, ['pessoa', 'empresa'], true) || $recipientId < 1) {
        throw new InvalidArgumentException('Destinatário de notificação inválido.');
    }

    acessarArmazenamentoMensagens(static function (array &$messages) use ($eventKey, $recipientType, $recipientId, $message): void {
        foreach ($messages as $storedMessage) {
            if (($storedMessage['chave_evento'] ?? '') === $eventKey) {
                return;
            }
        }

        $nextId = 1;
        foreach ($messages as $storedMessage) {
            $nextId = max($nextId, (int) ($storedMessage['id'] ?? 0) + 1);
        }

        $messages[] = [
            'id' => $nextId,
            'remetente_tipo' => 'sistema',
            'remetente_id' => 0,
            'destinatario_tipo' => $recipientType,
            'destinatario_id' => $recipientId,
            'mensagem' => $message,
            'enviada_em' => date(DATE_ATOM),
            'lida_em' => null,
            'ocultada_para' => [],
            'chave_evento' => $eventKey,
            'expira_em' => strpos($eventKey, 'job-report:') === 0
                ? date(DATE_ATOM, time() + 86400)
                : null,
        ];
    });
}
