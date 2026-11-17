<?php // Explicação DevIN: Abre código PHP interpretado no servidor.
declare(strict_types=1); // Explicação DevIN: Habilita a verificação estrita de tipos no PHP.
/**
 * REVISÃO DETALHADA DEVIN — tests/jarvis_controlled_actions_mysql.php
 * Finalidade: jarvis controlled actions mysql.
 * Domínio: assistência administrativa.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */

/** Integração offline em MySQL descartável. NUNCA usar banco real. */
if (getenv('JARVIS_TEST_DB') !== 'devin_jarvis_ci') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    throw new RuntimeException('Banco de teste não autorizado.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
$db=new mysqli(getenv('JARVIS_TEST_HOST') ?: '127.0.0.1', // Explicação DevIN: Atribui um valor à variável PHP $db.
    getenv('JARVIS_TEST_USER') ?: 'root', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    getenv('JARVIS_TEST_PASSWORD') ?: '', 'devin_jarvis_ci'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
$db->set_charset('utf8mb4'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
require_once dirname(__DIR__).'/php/controllers/Jarvis/JarvisAdminActions.php'; // Explicação DevIN: Inclui dependência PHP necessária à execução do módulo.

// Detalhe DevIN: Método prove: encapsula uma responsabilidade reutilizável do módulo no contexto de jarvis controlled actions mysql. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function prove(bool $condition,string $why): void { // Explicação DevIN: Declara função/método prove, com seus parâmetros e comportamento.
    if (!$condition) throw new RuntimeException('JARVIS controlled actions: '.$why); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
// Detalhe DevIN: Método forbidden: encapsula uma responsabilidade reutilizável do módulo no contexto de jarvis controlled actions mysql. Consultar argumentos da assinatura; erros devem ser tratados pelo chamador.
function forbidden(callable $fn,string $why): void { // Explicação DevIN: Declara função/método forbidden, com seus parâmetros e comportamento.
    try {$fn();} catch(InvalidArgumentException | DomainException $expected) {return;} // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    throw new RuntimeException('Permitido indevidamente: '.$why); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
$db->query('CREATE TABLE pessoa (id_pessoa INT PRIMARY KEY,nome VARCHAR(120) NOT NULL)'); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
$db->query('CREATE TABLE empresa (id_empresa INT PRIMARY KEY,nome VARCHAR(120) NOT NULL)'); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
$db->query('CREATE TABLE vagas (id_vaga INT PRIMARY KEY,id_empresa INT NOT NULL,titulo VARCHAR(120),descricao TEXT)'); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
$db->query('CREATE TABLE reports_vagas (id INT PRIMARY KEY,titulo_vaga VARCHAR(120),motivo VARCHAR(40),status VARCHAR(30),id_vaga INT)'); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
$db->query('CREATE TABLE reports_perfis (id INT PRIMARY KEY,nome_denunciado VARCHAR(120),motivo VARCHAR(40),status VARCHAR(30),tipo_denunciado VARCHAR(20),id_denunciado INT)'); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
$db->query("INSERT INTO pessoa VALUES (11,'Ana Candidata'),(12,'Joana Silva')"); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
$db->query("INSERT INTO empresa VALUES (21,'TechNova')"); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
$db->query("INSERT INTO vagas VALUES (31,21,'Desenvolvedor PHP','Criar aplicação PHP.')"); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
$db->query("INSERT INTO reports_vagas VALUES (41,'Desenvolvedor PHP','spam','pendente',31)"); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
$db->query("INSERT INTO reports_perfis VALUES (51,'Ana Candidata','abuso','pendente','pessoa',11)"); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.

$items=JarvisAdminRecords::search($db,'pessoa','Ana Candidata'); // Explicação DevIN: Atribui um valor à variável PHP $items.
prove(count($items)===1 && (int)$items[0]['id']===11,'busca por nome'); // Explicação DevIN: Continua expressão ou bloco pertencente a função forbidden.
prove(count(JarvisAdminRecords::search($db,'vaga','31'))===1,'busca de vaga pelo ID'); // Explicação DevIN: Continua expressão ou bloco pertencente a função forbidden.
prove(count(JarvisAdminRecords::search($db,'reports_vagas','spam'))===0,'busca não pesquisa motivos sensíveis'); // Explicação DevIN: Continua expressão ou bloco pertencente a função forbidden.
prove(JarvisAdminRecords::inspect($db,'vaga',31)['title']==='Desenvolvedor PHP','inspeção somente leitura'); // Explicação DevIN: Continua expressão ou bloco pertencente a função forbidden.
prove(JarvisAdminRecords::inspect($db,'pessoa',11)['name']==='Ana Candidata','nome mínimo de destinatário'); // Explicação DevIN: Continua expressão ou bloco pertencente a função forbidden.
prove(JarvisAdminRecords::inspect($db,'reports_perfis',51)['id_denunciado']===11,'denúncia resumida'); // Explicação DevIN: Continua expressão ou bloco pertencente a função forbidden.
forbidden(static fn()=>JarvisAdminRecords::search($db,'administrador',''), 'acesso à tabela de admin'); // Explicação DevIN: Continua expressão ou bloco pertencente a função forbidden.
forbidden(static fn()=>JarvisAdminRecords::inspect($db,'administrador',1), 'inspeção de administrador'); // Explicação DevIN: Continua expressão ou bloco pertencente a função forbidden.
forbidden(static fn()=>JarvisPlatformOutbox::validateMessage('Envie sua senha agora'), 'mensagens de phishing'); // Explicação DevIN: Continua expressão ou bloco pertencente a função forbidden.
forbidden(static fn()=>JarvisPlatformOutbox::validateMessage('Visite https://site.net'), 'mensagens com links'); // Explicação DevIN: Continua expressão ou bloco pertencente a função forbidden.
forbidden(static fn()=>JarvisPlatformOutbox::validateMessage(''), 'mensagens vazias'); // Explicação DevIN: Continua expressão ou bloco pertencente a função forbidden.

$admin=301; // Explicação DevIN: Atribui um valor à variável PHP $admin.
$other=302; // Explicação DevIN: Atribui um valor à variável PHP $other.
$draft=JarvisPlatformOutbox::create($db,$admin,'pessoa',11,'Olá Ana! Obrigado por usar o DevIN.','ai'); // Explicação DevIN: Atribui um valor à variável PHP $draft.
prove($draft['status']==='draft' && $draft['source']==='ai','IA só cria rascunho'); // Explicação DevIN: Continua expressão ou bloco pertencente a função forbidden.
prove(count(JarvisPlatformOutbox::listOwn($db,$other))===0,'ADM diferente não vê rascunho'); // Explicação DevIN: Continua expressão ou bloco pertencente a função forbidden.
forbidden(static fn()=>JarvisPlatformOutbox::revise($db,$other,$draft['id'],'Texto adulterado'), 'alteração entre ADMs'); // Explicação DevIN: Continua expressão ou bloco pertencente a função forbidden.
forbidden(static fn()=>JarvisPlatformOutbox::send($db,$other,$draft['id']), 'envio entre ADMs'); // Explicação DevIN: Continua expressão ou bloco pertencente a função forbidden.
prove(count(JarvisAdminActivity::latest($db,$admin))===1,'evento de rascunho registrado'); // Explicação DevIN: Continua expressão ou bloco pertencente a função forbidden.

$updated='Olá Ana! Mensagem personalizada revisada pela equipe DevIN.'; // Explicação DevIN: Atribui um valor à variável PHP $updated.
JarvisPlatformOutbox::revise($db,$admin,$draft['id'],$updated); // Explicação DevIN: Continua expressão ou bloco pertencente a função forbidden.
$messagesBefore=acessarArmazenamentoMensagens(static fn(array &$messages):array=>$messages); // Explicação DevIN: Atribui um valor à variável PHP $messagesBefore.
prove(!array_filter($messagesBefore,static fn(array $m):bool=> // Explicação DevIN: Continua expressão ou bloco pertencente a função forbidden.
    ($m['chave_evento']??'')==='jarvis-platform-message:'.$draft['id']), // Explicação DevIN: Continua expressão ou bloco pertencente a função forbidden.
    'rascunho não produz notificação'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.

JarvisPlatformOutbox::send($db,$admin,$draft['id']); // Explicação DevIN: Continua expressão ou bloco pertencente a função forbidden.
$sent=JarvisPlatformOutbox::listOwn($db,$admin); // Explicação DevIN: Atribui um valor à variável PHP $sent.
prove($sent[0]['status']==='sent' && (int)$sent[0]['approved_by']===$admin, // Explicação DevIN: Continua expressão ou bloco pertencente a função forbidden.
    'mensagem enviada com ADM aprovador'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
$messages=acessarArmazenamentoMensagens(static fn(array &$messages):array=>$messages); // Explicação DevIN: Atribui um valor à variável PHP $messages.
$delivered=array_values(array_filter($messages,static fn(array $m):bool=> // Explicação DevIN: Atribui um valor à variável PHP $delivered.
    ($m['chave_evento']??'')==='jarvis-platform-message:'.$draft['id'])); // Explicação DevIN: Continua expressão ou bloco pertencente a função forbidden.
prove(count($delivered)===1 && $delivered[0]['remetente_tipo']==='sistema' // Explicação DevIN: Continua expressão ou bloco pertencente a função forbidden.
    && (int)$delivered[0]['destinatario_id']===11 // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    && $delivered[0]['mensagem']===$updated, 'mensagem no canal Support DevIN'); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
forbidden(static fn()=>JarvisPlatformOutbox::send($db,$admin,$draft['id']), 'envio duplicado'); // Explicação DevIN: Continua expressão ou bloco pertencente a função forbidden.
$messages2=acessarArmazenamentoMensagens(static fn(array &$messages):array=>$messages); // Explicação DevIN: Atribui um valor à variável PHP $messages2.
prove(count(array_filter($messages2,static fn(array $m):bool=> // Explicação DevIN: Continua expressão ou bloco pertencente a função forbidden.
    ($m['chave_evento']??'')==='jarvis-platform-message:'.$draft['id']))===1, // Explicação DevIN: Continua expressão ou bloco pertencente a função forbidden.
    'notificação duplicada não aparece'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.

$second=JarvisPlatformOutbox::create($db,$admin,'pessoa',11,'Outra atualização individual.','admin'); // Explicação DevIN: Atribui um valor à variável PHP $second.
JarvisPlatformOutbox::send($db,$admin,$second['id']); // Explicação DevIN: Continua expressão ou bloco pertencente a função forbidden.
$third=JarvisPlatformOutbox::create($db,$admin,'pessoa',11,'Terceira atualização.','ai'); // Explicação DevIN: Atribui um valor à variável PHP $third.
forbidden(static fn()=>JarvisPlatformOutbox::send($db,$admin,$third['id']), // Explicação DevIN: Continua expressão ou bloco pertencente a função forbidden.
    'máximo de duas notificações por destinatário por dia'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
JarvisPlatformOutbox::cancel($db,$admin,$third['id']); // Explicação DevIN: Continua expressão ou bloco pertencente a função forbidden.
forbidden(static fn()=>JarvisPlatformOutbox::send($db,$admin,$third['id']),'rascunho cancelado'); // Explicação DevIN: Continua expressão ou bloco pertencente a função forbidden.

$events=JarvisAdminActivity::latest($db,$admin); // Explicação DevIN: Atribui um valor à variável PHP $events.
$eventsNames=array_column($events,'event_name'); // Explicação DevIN: Atribui um valor à variável PHP $eventsNames.
prove(in_array('message_sent',$eventsNames,true) // Explicação DevIN: Continua expressão ou bloco pertencente a função forbidden.
    && in_array('draft_revised',$eventsNames,true) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    && in_array('draft_cancelled',$eventsNames,true), // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    'histórico de envio, revisão e cancelamento'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
prove(count(JarvisAdminActivity::latest($db,$other))===0,'histórico por ADM isolado'); // Explicação DevIN: Continua expressão ou bloco pertencente a função forbidden.
$otherDraft=JarvisPlatformOutbox::create($db,$other,'empresa',21,'Olá TechNova. Comunicado individual.','admin'); // Explicação DevIN: Atribui um valor à variável PHP $otherDraft.
prove($otherDraft['id']>0,'outro ADM pode criar rascunho próprio'); // Explicação DevIN: Continua expressão ou bloco pertencente a função forbidden.
$shared=JarvisAdminActivity::latestAll($db); // Explicação DevIN: Atribui um valor à variável PHP $shared.
prove(in_array($admin,array_map('intval',array_column($shared,'admin_id')),true) // Explicação DevIN: Continua expressão ou bloco pertencente a função forbidden.
    && in_array($other,array_map('intval',array_column($shared,'admin_id')),true), // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    'trilha compartilhada permite fiscalização entre ADMs'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
$apiCode=file_get_contents(dirname(__DIR__).'/php/api/jarvis/admin_tools.php'); // Explicação DevIN: Atribui um valor à variável PHP $apiCode.
prove(!preg_match('/\\b(DELETE FROM (pessoa|empresa|vagas)|UPDATE (pessoa|empresa|vagas))\\b/i',$apiCode), // Explicação DevIN: Continua expressão ou bloco pertencente a função forbidden.
    'API da IA não contém SQL de banimento ou exclusão'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
$logs=json_encode($events,JSON_THROW_ON_ERROR); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
prove(!str_contains($logs,'Mensagem personalizada revisada') // Explicação DevIN: Continua expressão ou bloco pertencente a função forbidden.
    && !str_contains($logs,'Ana Candidata'), 'auditoria sem conteúdo pessoal'); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
$tables=$db->query("SELECT table_name FROM information_schema.tables WHERE table_schema='devin_jarvis_ci'")->fetch_all(MYSQLI_ASSOC); // Explicação DevIN: Executa ou lê resultados de uma consulta ao banco.
prove(count($tables)>=11,'migração aplicada no MySQL descartável'); // Explicação DevIN: Continua expressão ou bloco pertencente a função forbidden.
$db->close(); // Explicação DevIN: Continua expressão ou bloco pertencente a função forbidden.
echo "JARVIS ações, notificações oficiais e auditoria: OK\n"; // Explicação DevIN: Produz saída ou conteúdo da resposta do programa.
