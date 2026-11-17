<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.

/**
 * Integração de suporte em banco DESCARTÁVEL, nunca no MySQL da Hostinger.
 * Testa isolamento entre candidatos/empresas, categorias, limites, resposta
 * administrativa, encerramento e entrega idempotente via chat/e-mail simulados.
 */
if (getenv('SUPPORT_TEST_DB') !== 'devin_support_ci') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    throw new RuntimeException('Banco de teste de suporte não autorizado.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
$db = new mysqli(getenv('SUPPORT_TEST_HOST') ?: '127.0.0.1', // Explicação DevIN: Atribui um valor à variável PHP $db.
    getenv('SUPPORT_TEST_USER') ?: 'root', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    getenv('SUPPORT_TEST_PASSWORD') ?: '', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    'devin_support_ci'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
$db->set_charset('utf8mb4'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

// Simulações intencionais: testes não entregam dados a SMTP ou a usuários reais.
$chatEvents = []; // Explicação DevIN: Atribui um valor à variável PHP $chatEvents.
$mailEvents = []; // Explicação DevIN: Atribui um valor à variável PHP $mailEvents.
function appendPlatformChatNotification(string $key, string $type, int $id, string $message): void // Explicação DevIN: Declara função/método appendPlatformChatNotification, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    global $chatEvents; // Explicação DevIN: Continua expressão ou bloco pertencente a função appendPlatformChatNotification.
    $chatEvents[$key] = compact('type','id','message'); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
final class MailerHelper // Explicação DevIN: Declara o tipo MailerHelper e inicia sua estrutura.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    public static function enviar(string $email, string $name, string $subject, string $html): bool // Explicação DevIN: Declara função/método enviar, com seus parâmetros e comportamento.
    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        global $mailEvents; // Explicação DevIN: Continua expressão ou bloco pertencente a função enviar.
        $mailEvents[] = compact('email','name','subject','html'); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        return true; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
require_once dirname(__DIR__) . '/php/controllers/SupportDesk.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

// Falhar com mensagem clara se qualquer requisito deixar de ser atendido.
function supportAssert(bool $condition, string $why): void // Explicação DevIN: Declara função/método supportAssert, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if (!$condition) throw new RuntimeException('SupportDesk: ' . $why); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
function supportDenied(callable $operation, string $why): void // Explicação DevIN: Declara função/método supportDenied, com seus parâmetros e comportamento.
{ // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        $operation(); // Explicação DevIN: Continua expressão ou bloco pertencente a função supportDenied.
    } catch (DomainException|InvalidArgumentException $expected) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    throw new RuntimeException('SupportDesk aceitou ação proibida: ' . $why); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

$db->query('CREATE TABLE pessoa(id_pessoa INT PRIMARY KEY,nome VARCHAR(130),email VARCHAR(190))'); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
$db->query('CREATE TABLE empresa(id_empresa INT PRIMARY KEY,nome VARCHAR(130),email VARCHAR(190))'); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
$db->query("INSERT INTO pessoa VALUES (10,'Candidata Ana','ana@example.test'),
    (11,'Candidata Bia','bia@example.test')"); // Explicação DevIN: Continua expressão ou bloco pertencente a função supportDenied.
$db->query("INSERT INTO empresa VALUES (20,'Empresa Teste','empresa@example.test')"); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
SupportDesk::requireSchema($db); // Explicação DevIN: Continua expressão ou bloco pertencente a função supportDenied.

// O cliente não informa nome/e-mail: o servidor resolve os valores da conta.
$ana = ['tipo'=>'pessoa','id'=>10,'nome'=>'Falsificado','email'=>'fake@example.net']; // Explicação DevIN: Atribui um valor à variável PHP $ana.
$bia = ['tipo'=>'pessoa','id'=>11]; // Explicação DevIN: Atribui um valor à variável PHP $bia.
$empresa = ['tipo'=>'empresa','id'=>20]; // Explicação DevIN: Atribui um valor à variável PHP $empresa.
$first = SupportDesk::create($db,$ana,'erro','A página não exibe o formulário corretamente.',null); // Explicação DevIN: Atribui um valor à variável PHP $first.
supportAssert($first > 0,'criar chamado de pessoa'); // Explicação DevIN: Continua expressão ou bloco pertencente a função supportDenied.
$row = SupportDesk::detail($db,$first,$ana); // Explicação DevIN: Atribui um valor à variável PHP $row.
supportAssert($row['ticket']['requester_name'] === 'Candidata Ana','nome confiável'); // Explicação DevIN: Continua expressão ou bloco pertencente a função supportDenied.
supportAssert($row['ticket']['requester_email'] === 'ana@example.test','e-mail confiável'); // Explicação DevIN: Continua expressão ou bloco pertencente a função supportDenied.
supportAssert($row['ticket']['attachment_name'] === null,'anexo opcional'); // Explicação DevIN: Continua expressão ou bloco pertencente a função supportDenied.
supportAssert(count(SupportDesk::listOwn($db,$ana)) === 1,'listar próprios'); // Explicação DevIN: Continua expressão ou bloco pertencente a função supportDenied.
supportAssert(SupportDesk::listOwn($db,$bia) === [],'outras contas não veem chamados'); // Explicação DevIN: Continua expressão ou bloco pertencente a função supportDenied.
supportDenied(static fn()=>SupportDesk::detail($db,$first,$bia),'leitura cruzada de chamados'); // Explicação DevIN: Continua expressão ou bloco pertencente a função supportDenied.
supportDenied(static fn()=>SupportDesk::detail($db,$first,$empresa),'leitura cruzada entre tipo'); // Explicação DevIN: Continua expressão ou bloco pertencente a função supportDenied.
supportDenied(static fn()=>SupportDesk::create($db,$ana,'qualquer','Descrição válida.',null), // Explicação DevIN: Continua expressão ou bloco pertencente a função supportDenied.
    'categoria fora da lista'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
supportDenied(static fn()=>SupportDesk::create($db,$ana,'erro','curto',null), // Explicação DevIN: Continua expressão ou bloco pertencente a função supportDenied.
    'descrição curta'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
supportDenied(static fn()=>SupportDesk::create($db,$ana,'erro','Outra solicitação válida.',null), // Explicação DevIN: Continua expressão ou bloco pertencente a função supportDenied.
    'limite por minuto'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.

// Um arquivo PHP simulado não é um upload legítimo, mesmo com tamanho baixo.
supportDenied(static fn()=>SupportDesk::upload([ // Explicação DevIN: Continua expressão ou bloco pertencente a função supportDenied.
    'error'=>UPLOAD_ERR_OK,'size'=>100,'tmp_name'=>'/tmp/not-upload', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    'name'=>'script.php' // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
]),'arquivo não enviado pelo navegador'); // Explicação DevIN: Continua expressão ou bloco pertencente a função supportDenied.
supportAssert(SupportDesk::MAX_ATTACHMENT_BYTES === 20971520,'limite exato 20 MiB'); // Explicação DevIN: Continua expressão ou bloco pertencente a função supportDenied.

// O ADM vê a identidade completa e pode responder, mas não se passa pelo cliente.
$all = SupportDesk::listAdmin($db); // Explicação DevIN: Atribui um valor à variável PHP $all.
supportAssert(count($all) === 1 && $all[0]['requester_email'] === 'ana@example.test', // Explicação DevIN: Continua expressão ou bloco pertencente a função supportDenied.
    'lista administrativa com nome e e-mail'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
$sent = SupportDesk::reply($db,101,$first,'Olá, Ana. Nossa equipe verificará o erro.'); // Explicação DevIN: Atribui um valor à variável PHP $sent.
supportAssert($sent['chat_sent'] && $sent['email_sent'],'canais de aviso'); // Explicação DevIN: Continua expressão ou bloco pertencente a função supportDenied.
supportAssert(count($chatEvents) === 1 && count($mailEvents) === 1,'uma notificação por canal'); // Explicação DevIN: Continua expressão ou bloco pertencente a função supportDenied.
supportAssert(str_contains(current($chatEvents)['message'],'Suporte DevIN'),'remetente de suporte'); // Explicação DevIN: Continua expressão ou bloco pertencente a função supportDenied.
supportAssert(str_contains($mailEvents[0]['subject'],'Suporte DevIN'),'assunto de e-mail'); // Explicação DevIN: Continua expressão ou bloco pertencente a função supportDenied.
supportAssert(count(SupportDesk::detail($db,$first,$ana)['replies']) === 1,'histórico retornado ao dono'); // Explicação DevIN: Continua expressão ou bloco pertencente a função supportDenied.
$again = SupportDesk::notify($db,$sent['reply_id']); // Explicação DevIN: Atribui um valor à variável PHP $again.
supportAssert($again['chat_sent'] && $again['email_sent'],'reenvio idempotente'); // Explicação DevIN: Continua expressão ou bloco pertencente a função supportDenied.
supportAssert(count($chatEvents) === 1 && count($mailEvents) === 1,'sem duplicar notificação'); // Explicação DevIN: Continua expressão ou bloco pertencente a função supportDenied.

SupportDesk::close($db,$first); // Explicação DevIN: Continua expressão ou bloco pertencente a função supportDenied.
supportAssert(SupportDesk::detail($db,$first,$ana)['ticket']['status'] === 'fechado', // Explicação DevIN: Continua expressão ou bloco pertencente a função supportDenied.
    'status de encerramento'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
supportDenied(static fn()=>SupportDesk::reply($db,101,$first,'Resposta tardia'), // Explicação DevIN: Continua expressão ou bloco pertencente a função supportDenied.
    'não responder chamado fechado'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
supportDenied(static fn()=>SupportDesk::attachment($db,$first,$bia), // Explicação DevIN: Continua expressão ou bloco pertencente a função supportDenied.
    'não permitir baixar anexo de outra conta'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
$db->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função supportDenied.
echo "SupportDesk MySQL integration: OK\n"; // Explicação DevIN: Produz saída ou conteúdo da resposta do programa.
