<?php
declare(strict_types=1);
/** Integração offline em MySQL descartável. NUNCA usar banco real. */
if (getenv('JARVIS_TEST_DB') !== 'devin_jarvis_ci') {
    throw new RuntimeException('Banco de teste não autorizado.');
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db=new mysqli(getenv('JARVIS_TEST_HOST') ?: '127.0.0.1',
    getenv('JARVIS_TEST_USER') ?: 'root',
    getenv('JARVIS_TEST_PASSWORD') ?: '', 'devin_jarvis_ci');
$db->set_charset('utf8mb4');
require_once dirname(__DIR__).'/php/controllers/Jarvis/JarvisAdminActions.php';

function prove(bool $condition,string $why): void {
    if (!$condition) throw new RuntimeException('JARVIS controlled actions: '.$why);
}
function forbidden(callable $fn,string $why): void {
    try {$fn();} catch(InvalidArgumentException | DomainException $expected) {return;}
    throw new RuntimeException('Permitido indevidamente: '.$why);
}
$db->query('CREATE TABLE pessoa (id_pessoa INT PRIMARY KEY,nome VARCHAR(120) NOT NULL)');
$db->query('CREATE TABLE empresa (id_empresa INT PRIMARY KEY,nome VARCHAR(120) NOT NULL)');
$db->query('CREATE TABLE vagas (id_vaga INT PRIMARY KEY,id_empresa INT NOT NULL,titulo VARCHAR(120),descricao TEXT)');
$db->query('CREATE TABLE reports_vagas (id INT PRIMARY KEY,titulo_vaga VARCHAR(120),motivo VARCHAR(40),status VARCHAR(30),id_vaga INT)');
$db->query('CREATE TABLE reports_perfis (id INT PRIMARY KEY,nome_denunciado VARCHAR(120),motivo VARCHAR(40),status VARCHAR(30),tipo_denunciado VARCHAR(20),id_denunciado INT)');
$db->query("INSERT INTO pessoa VALUES (11,'Ana Candidata'),(12,'Joana Silva')");
$db->query("INSERT INTO empresa VALUES (21,'TechNova')");
$db->query("INSERT INTO vagas VALUES (31,21,'Desenvolvedor PHP','Criar aplicação PHP.')");
$db->query("INSERT INTO reports_vagas VALUES (41,'Desenvolvedor PHP','spam','pendente',31)");
$db->query("INSERT INTO reports_perfis VALUES (51,'Ana Candidata','abuso','pendente','pessoa',11)");

$items=JarvisAdminRecords::search($db,'pessoa','Ana Candidata');
prove(count($items)===1 && (int)$items[0]['id']===11,'busca por nome');
prove(count(JarvisAdminRecords::search($db,'vaga','31'))===1,'busca de vaga pelo ID');
prove(count(JarvisAdminRecords::search($db,'reports_vagas','spam'))===0,'busca não pesquisa motivos sensíveis');
prove(JarvisAdminRecords::inspect($db,'vaga',31)['title']==='Desenvolvedor PHP','inspeção somente leitura');
prove(JarvisAdminRecords::inspect($db,'pessoa',11)['name']==='Ana Candidata','nome mínimo de destinatário');
prove(JarvisAdminRecords::inspect($db,'reports_perfis',51)['id_denunciado']===11,'denúncia resumida');
forbidden(static fn()=>JarvisAdminRecords::search($db,'administrador',''), 'acesso à tabela de admin');
forbidden(static fn()=>JarvisAdminRecords::inspect($db,'administrador',1), 'inspeção de administrador');
forbidden(static fn()=>JarvisPlatformOutbox::validateMessage('Envie sua senha agora'), 'mensagens de phishing');
forbidden(static fn()=>JarvisPlatformOutbox::validateMessage('Visite https://site.net'), 'mensagens com links');
forbidden(static fn()=>JarvisPlatformOutbox::validateMessage(''), 'mensagens vazias');

$admin=301;
$other=302;
$draft=JarvisPlatformOutbox::create($db,$admin,'pessoa',11,'Olá Ana! Obrigado por usar o DevIN.','ai');
prove($draft['status']==='draft' && $draft['source']==='ai','IA só cria rascunho');
prove(count(JarvisPlatformOutbox::listOwn($db,$other))===0,'ADM diferente não vê rascunho');
forbidden(static fn()=>JarvisPlatformOutbox::revise($db,$other,$draft['id'],'Texto adulterado'), 'alteração entre ADMs');
forbidden(static fn()=>JarvisPlatformOutbox::send($db,$other,$draft['id']), 'envio entre ADMs');
prove(count(JarvisAdminActivity::latest($db,$admin))===1,'evento de rascunho registrado');

$updated='Olá Ana! Mensagem personalizada revisada pela equipe DevIN.';
JarvisPlatformOutbox::revise($db,$admin,$draft['id'],$updated);
$messagesBefore=acessarArmazenamentoMensagens(static fn(array &$messages):array=>$messages);
prove(!array_filter($messagesBefore,static fn(array $m):bool=>
    ($m['chave_evento']??'')==='jarvis-platform-message:'.$draft['id']),
    'rascunho não produz notificação');

JarvisPlatformOutbox::send($db,$admin,$draft['id']);
$sent=JarvisPlatformOutbox::listOwn($db,$admin);
prove($sent[0]['status']==='sent' && (int)$sent[0]['approved_by']===$admin,
    'mensagem enviada com ADM aprovador');
$messages=acessarArmazenamentoMensagens(static fn(array &$messages):array=>$messages);
$delivered=array_values(array_filter($messages,static fn(array $m):bool=>
    ($m['chave_evento']??'')==='jarvis-platform-message:'.$draft['id']));
prove(count($delivered)===1 && $delivered[0]['remetente_tipo']==='sistema'
    && (int)$delivered[0]['destinatario_id']===11
    && $delivered[0]['mensagem']===$updated, 'mensagem no canal Support DevIN');
forbidden(static fn()=>JarvisPlatformOutbox::send($db,$admin,$draft['id']), 'envio duplicado');
$messages2=acessarArmazenamentoMensagens(static fn(array &$messages):array=>$messages);
prove(count(array_filter($messages2,static fn(array $m):bool=>
    ($m['chave_evento']??'')==='jarvis-platform-message:'.$draft['id']))===1,
    'notificação duplicada não aparece');

$second=JarvisPlatformOutbox::create($db,$admin,'pessoa',11,'Outra atualização individual.','admin');
JarvisPlatformOutbox::send($db,$admin,$second['id']);
$third=JarvisPlatformOutbox::create($db,$admin,'pessoa',11,'Terceira atualização.','ai');
forbidden(static fn()=>JarvisPlatformOutbox::send($db,$admin,$third['id']),
    'máximo de duas notificações por destinatário por dia');
JarvisPlatformOutbox::cancel($db,$admin,$third['id']);
forbidden(static fn()=>JarvisPlatformOutbox::send($db,$admin,$third['id']),'rascunho cancelado');

$events=JarvisAdminActivity::latest($db,$admin);
$eventsNames=array_column($events,'event_name');
prove(in_array('message_sent',$eventsNames,true)
    && in_array('draft_revised',$eventsNames,true)
    && in_array('draft_cancelled',$eventsNames,true),
    'histórico de envio, revisão e cancelamento');
prove(count(JarvisAdminActivity::latest($db,$other))===0,'histórico por ADM isolado');
$logs=json_encode($events,JSON_THROW_ON_ERROR);
prove(!str_contains($logs,'Mensagem personalizada revisada')
    && !str_contains($logs,'Ana Candidata'), 'auditoria sem conteúdo pessoal');
$tables=$db->query("SELECT table_name FROM information_schema.tables WHERE table_schema='devin_jarvis_ci'")->fetch_all(MYSQLI_ASSOC);
prove(count($tables)>=11,'migração aplicada no MySQL descartável');
$db->close();
echo "JARVIS ações, notificações oficiais e auditoria: OK\n";
