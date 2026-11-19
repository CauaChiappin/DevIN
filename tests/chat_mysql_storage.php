<?php // Explicação DevIN: Inicializa teste de persistência do chat.
declare(strict_types=1); // Explicação DevIN: Verifica os tipos usados durante o teste.
if (getenv('CHAT_TEST_DB') !== 'devin_chat_ci') { fwrite(STDERR, "Use somente CHAT_TEST_DB=devin_chat_ci em banco descartável.\n"); exit(2); } // Explicação DevIN: Bloqueia execução involuntária sobre banco real.
putenv('DB_HOST=' . (getenv('CHAT_TEST_HOST') ?: '127.0.0.1')); // Explicação DevIN: Configura host do banco temporário.
putenv('DB_USER=' . (getenv('CHAT_TEST_USER') ?: 'root')); // Explicação DevIN: Configura usuário de testes.
putenv('DB_PASS=' . (getenv('CHAT_TEST_PASSWORD') ?: '')); // Explicação DevIN: Configura senha do banco temporário.
putenv('DB_NAME=devin_chat_ci'); // Explicação DevIN: Isola testes de chat.
$_ENV['DB_HOST'] = getenv('DB_HOST'); // Explicação DevIN: Mantém configuração de conexão sincronizada com dotenv.
$_ENV['DB_USER'] = getenv('DB_USER'); // Explicação DevIN: Mantém usuário de conexão sincronizado.
$_ENV['DB_PASS'] = getenv('DB_PASS'); // Explicação DevIN: Mantém senha de conexão sincronizada.
$_ENV['DB_NAME'] = 'devin_chat_ci'; // Explicação DevIN: Restringe as operações ao banco temporário.
putenv('DEVIN_CHAT_MYSQL_ENABLED=1'); // Explicação DevIN: Habilita o mecanismo MySQL no teste.
$_ENV['DEVIN_CHAT_MYSQL_ENABLED'] = '1'; // Explicação DevIN: Replica ativação no array de ambiente.
require_once __DIR__ . '/../php/controllers/ChatStorage.php'; // Explicação DevIN: Carrega função real de armazenamento.
function checkChat(bool $ok, string $why): void { if (!$ok) throw new RuntimeException($why); } // Explicação DevIN: Garante que falhas terminem os testes.
$path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'devin-mensagens-' . hash('sha256', dirname(__DIR__)) . '.json'; // Explicação DevIN: Reproduz caminho legado para verificar importação.
checkChat(!file_exists($path), 'Arquivo legado em uso; teste não pode sobrescrevê-lo.'); // Explicação DevIN: Preserva dados preexistentes do ambiente.
$legacy = [['id'=>1,'remetente_tipo'=>'pessoa','remetente_id'=>1,'destinatario_tipo'=>'empresa','destinatario_id'=>2,'mensagem'=>'Antes da migração','enviada_em'=>date(DATE_ATOM),'lida_em'=>null]]; // Explicação DevIN: Cria mensagem anterior à migração.
file_put_contents($path, json_encode($legacy, JSON_THROW_ON_ERROR)); // Explicação DevIN: Salva arquivo legado de teste.
try { // Explicação DevIN: Garante exclusão exclusiva do arquivo criado pelo teste.
    $initial = acessarArmazenamentoMensagens(static fn (array &$rows): array => $rows); // Explicação DevIN: Executa importação automática para InnoDB.
    checkChat(count($initial) === 1 && $initial[0]['mensagem'] === 'Antes da migração', 'A importação perdeu o histórico.'); // Explicação DevIN: Confirma que nenhum registro original sumiu.
    $second = acessarArmazenamentoMensagens(static function (array &$rows): int { $rows[] = ['id'=>2,'mensagem'=>'Nova mensagem']; return count($rows); }); // Explicação DevIN: Verifica escrita na base persistente.
    checkChat($second === 2, 'A escrita no MySQL falhou.'); // Explicação DevIN: Valida resultado da gravação.
    $persisted = acessarArmazenamentoMensagens(static fn (array &$rows): array => $rows); // Explicação DevIN: Simula leitura de outra requisição.
    checkChat(count($persisted) === 2, 'A leitura persistente perdeu uma mensagem.'); // Explicação DevIN: Confirma estado após gravação.
    try { acessarArmazenamentoMensagens(static function (array &$rows): void { $rows[] = ['id'=>3]; throw new RuntimeException('rollback esperado'); }); } catch (RuntimeException $ignored) {} // Explicação DevIN: Simula falha após alteração pendente.
    $after = acessarArmazenamentoMensagens(static fn (array &$rows): array => $rows); // Explicação DevIN: Recarrega estado após falha transacional.
    checkChat(count($after) === 2, 'Rollback não protegeu o histórico.'); // Explicação DevIN: Detecta dados parcialmente gravados.
    $conversations = acessarArmazenamentoMensagens(static function (array &$rows): int { $rows[] = ['participantes'=>['pessoa:1','empresa:2']]; return count($rows); }, 'conversas'); // Explicação DevIN: Testa recurso separado de conversas.
    checkChat($conversations === 1, 'Conversas não foram persistidas.'); // Explicação DevIN: Verifica outro recurso.
    checkChat(file_exists($path), 'Arquivo legado não deveria ser excluído.'); // Explicação DevIN: Exige preservação de cópia de recuperação.
    echo "chat-mysql-storage: OK (importação, escrita, leitura, rollback, conversas).\n"; // Explicação DevIN: Registra êxito do teste.
} finally { // Explicação DevIN: Remove apenas o arquivo temporário criado pelo teste.
    unlink($path); // Explicação DevIN: Limpa artefato do teste isolado.
} // Explicação DevIN: Finaliza limpeza de recursos.
