<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
if (getenv('CHAT_TEST_DB') !== 'devin_chat_ci') { fwrite(STDERR, "Use somente CHAT_TEST_DB=devin_chat_ci em banco descartável.\n"); exit(2); } // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
putenv('DB_HOST=' . (getenv('CHAT_TEST_HOST') ?: '127.0.0.1')); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
putenv('DB_USER=' . (getenv('CHAT_TEST_USER') ?: 'root')); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
putenv('DB_PASS=' . (getenv('CHAT_TEST_PASSWORD') ?: '')); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
putenv('DB_NAME=devin_chat_ci'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
$_ENV['DB_HOST'] = getenv('DB_HOST'); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_ENV['DB_USER'] = getenv('DB_USER'); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_ENV['DB_PASS'] = getenv('DB_PASS'); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
$_ENV['DB_NAME'] = 'devin_chat_ci'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
putenv('APP_ENV=development'); // Explicação DevIN: Executa apenas com ambiente descartável de testes.
$_ENV['APP_ENV'] = 'development'; // Explicação DevIN: Evita exigir segredo JWT de produção para o teste isolado.
putenv('DEVIN_CHAT_MYSQL_ENABLED=1'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
$_ENV['DEVIN_CHAT_MYSQL_ENABLED'] = '1'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
require_once __DIR__ . '/../php/controllers/ChatStorage.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.
function checkChat(bool $ok, string $why): void { if (!$ok) throw new RuntimeException($why); } // Explicação DevIN: Declara função/método checkChat, com seus parâmetros e comportamento.
$path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'devin-mensagens-' . hash('sha256', dirname(__DIR__)) . '.json'; // Explicação DevIN: Atribui um valor à variável PHP $path.
checkChat(!file_exists($path), 'Arquivo legado em uso; teste não pode sobrescrevê-lo.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkChat.
$legacy = [['id'=>1,'remetente_tipo'=>'pessoa','remetente_id'=>1,'destinatario_tipo'=>'empresa','destinatario_id'=>2,'mensagem'=>'Antes da migração','enviada_em'=>date(DATE_ATOM),'lida_em'=>null]]; // Explicação DevIN: Atribui um valor à variável PHP $legacy.
file_put_contents($path, json_encode($legacy, JSON_THROW_ON_ERROR)); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    $initial = acessarArmazenamentoMensagens(static fn (array &$rows): array => $rows); // Explicação DevIN: Atribui um valor à variável PHP $initial.
    checkChat(count($initial) === 1 && $initial[0]['mensagem'] === 'Antes da migração', 'A importação perdeu o histórico.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função checkChat.
    $second = acessarArmazenamentoMensagens(static function (array &$rows): int { $rows[] = ['id'=>2,'mensagem'=>'Nova mensagem']; return count($rows); }); // Explicação DevIN: Define função anônima usada como operação ou callback.
    checkChat($second === 2, 'A escrita no MySQL falhou.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    $persisted = acessarArmazenamentoMensagens(static fn (array &$rows): array => $rows); // Explicação DevIN: Atribui um valor à variável PHP $persisted.
    checkChat(count($persisted) === 2, 'A leitura persistente perdeu uma mensagem.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    try { acessarArmazenamentoMensagens(static function (array &$rows): void { $rows[] = ['id'=>3]; throw new RuntimeException('rollback esperado'); }); } catch (RuntimeException $ignored) {} // Explicação DevIN: Define função anônima usada como operação ou callback.
    $after = acessarArmazenamentoMensagens(static fn (array &$rows): array => $rows); // Explicação DevIN: Atribui um valor à variável PHP $after.
    checkChat(count($after) === 2, 'Rollback não protegeu o histórico.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    $conversations = acessarArmazenamentoMensagens(static function (array &$rows): int { $rows[] = ['participantes'=>['pessoa:1','empresa:2']]; return count($rows); }, 'conversas'); // Explicação DevIN: Define função anônima usada como operação ou callback.
    checkChat($conversations === 1, 'Conversas não foram persistidas.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    checkChat(file_exists($path), 'Arquivo legado não deveria ser excluído.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    echo "chat-mysql-storage: OK (importação, escrita, leitura, rollback, conversas).\n"; // Explicação DevIN: Produz saída ou conteúdo da resposta do programa.
} finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
    unlink($path); // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
