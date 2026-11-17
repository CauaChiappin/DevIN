<?php
declare(strict_types=1);

/**
 * Integração de suporte em banco DESCARTÁVEL, nunca no MySQL da Hostinger.
 * Testa isolamento entre candidatos/empresas, categorias, limites, resposta
 * administrativa, encerramento e entrega idempotente via chat/e-mail simulados.
 */
if (getenv('SUPPORT_TEST_DB') !== 'devin_support_ci') {
    throw new RuntimeException('Banco de teste de suporte não autorizado.');
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = new mysqli(getenv('SUPPORT_TEST_HOST') ?: '127.0.0.1',
    getenv('SUPPORT_TEST_USER') ?: 'root',
    getenv('SUPPORT_TEST_PASSWORD') ?: '',
    'devin_support_ci');
$db->set_charset('utf8mb4');

// Simulações intencionais: testes não entregam dados a SMTP ou a usuários reais.
$chatEvents = [];
$mailEvents = [];
function appendPlatformChatNotification(string $key, string $type, int $id, string $message): void
{
    global $chatEvents;
    $chatEvents[$key] = compact('type','id','message');
}
final class MailerHelper
{
    public static function enviar(string $email, string $name, string $subject, string $html): bool
    {
        global $mailEvents;
        $mailEvents[] = compact('email','name','subject','html');
        return true;
    }
}
require_once dirname(__DIR__) . '/php/controllers/SupportDesk.php';

// Falhar com mensagem clara se qualquer requisito deixar de ser atendido.
function supportAssert(bool $condition, string $why): void
{
    if (!$condition) throw new RuntimeException('SupportDesk: ' . $why);
}
function supportDenied(callable $operation, string $why): void
{
    try {
        $operation();
    } catch (DomainException|InvalidArgumentException $expected) {
        return;
    }
    throw new RuntimeException('SupportDesk aceitou ação proibida: ' . $why);
}

$db->query('CREATE TABLE pessoa(id_pessoa INT PRIMARY KEY,nome VARCHAR(130),email VARCHAR(190))');
$db->query('CREATE TABLE empresa(id_empresa INT PRIMARY KEY,nome VARCHAR(130),email VARCHAR(190))');
$db->query("INSERT INTO pessoa VALUES (10,'Candidata Ana','ana@example.test'),
    (11,'Candidata Bia','bia@example.test')");
$db->query("INSERT INTO empresa VALUES (20,'Empresa Teste','empresa@example.test')");
SupportDesk::requireSchema($db);

// O cliente não informa nome/e-mail: o servidor resolve os valores da conta.
$ana = ['tipo'=>'pessoa','id'=>10,'nome'=>'Falsificado','email'=>'fake@example.net'];
$bia = ['tipo'=>'pessoa','id'=>11];
$empresa = ['tipo'=>'empresa','id'=>20];
$first = SupportDesk::create($db,$ana,'erro','A página não exibe o formulário corretamente.',null);
supportAssert($first > 0,'criar chamado de pessoa');
$row = SupportDesk::detail($db,$first,$ana);
supportAssert($row['ticket']['requester_name'] === 'Candidata Ana','nome confiável');
supportAssert($row['ticket']['requester_email'] === 'ana@example.test','e-mail confiável');
supportAssert($row['ticket']['attachment_name'] === null,'anexo opcional');
supportAssert(count(SupportDesk::listOwn($db,$ana)) === 1,'listar próprios');
supportAssert(SupportDesk::listOwn($db,$bia) === [],'outras contas não veem chamados');
supportDenied(static fn()=>SupportDesk::detail($db,$first,$bia),'leitura cruzada de chamados');
supportDenied(static fn()=>SupportDesk::detail($db,$first,$empresa),'leitura cruzada entre tipo');
supportDenied(static fn()=>SupportDesk::create($db,$ana,'qualquer','Descrição válida.',null),
    'categoria fora da lista');
supportDenied(static fn()=>SupportDesk::create($db,$ana,'erro','curto',null),
    'descrição curta');
supportDenied(static fn()=>SupportDesk::create($db,$ana,'erro','Outra solicitação válida.',null),
    'limite por minuto');

// Um arquivo PHP simulado não é um upload legítimo, mesmo com tamanho baixo.
supportDenied(static fn()=>SupportDesk::upload([
    'error'=>UPLOAD_ERR_OK,'size'=>100,'tmp_name'=>'/tmp/not-upload',
    'name'=>'script.php'
]),'arquivo não enviado pelo navegador');
supportAssert(SupportDesk::MAX_ATTACHMENT_BYTES === 20971520,'limite exato 20 MiB');

// O ADM vê a identidade completa e pode responder, mas não se passa pelo cliente.
$all = SupportDesk::listAdmin($db);
supportAssert(count($all) === 1 && $all[0]['requester_email'] === 'ana@example.test',
    'lista administrativa com nome e e-mail');
$sent = SupportDesk::reply($db,101,$first,'Olá, Ana. Nossa equipe verificará o erro.');
supportAssert($sent['chat_sent'] && $sent['email_sent'],'canais de aviso');
supportAssert(count($chatEvents) === 1 && count($mailEvents) === 1,'uma notificação por canal');
supportAssert(str_contains(current($chatEvents)['message'],'Suporte DevIN'),'remetente de suporte');
supportAssert(str_contains($mailEvents[0]['subject'],'Suporte DevIN'),'assunto de e-mail');
supportAssert(count(SupportDesk::detail($db,$first,$ana)['replies']) === 1,'histórico retornado ao dono');
$again = SupportDesk::notify($db,$sent['reply_id']);
supportAssert($again['chat_sent'] && $again['email_sent'],'reenvio idempotente');
supportAssert(count($chatEvents) === 1 && count($mailEvents) === 1,'sem duplicar notificação');

SupportDesk::close($db,$first);
supportAssert(SupportDesk::detail($db,$first,$ana)['ticket']['status'] === 'fechado',
    'status de encerramento');
supportDenied(static fn()=>SupportDesk::reply($db,101,$first,'Resposta tardia'),
    'não responder chamado fechado');
supportDenied(static fn()=>SupportDesk::attachment($db,$first,$bia),
    'não permitir baixar anexo de outra conta');
$db->close();
echo "SupportDesk MySQL integration: OK\n";
