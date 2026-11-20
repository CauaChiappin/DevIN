const test=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs');
const path=require('node:path');
const classifier=require('../js/jarvis-admin-natural.js').classify;
const read=filename=>fs.readFileSync(path.join(__dirname,'..',filename),'utf8');
const examples=[
  ['Ache a empresa Acme Tecnologia',{type:'search',kind:'empresa',id:null,name:'Acme Tecnologia'}],
  ['Mostre a empresa ID 8',{type:'search',kind:'empresa',id:8,name:''}],
  ['Localize o candidato João Silva',{type:'search',kind:'pessoa',id:null,name:'João Silva'}],
  ['Me mostre a pessoa Maria Souza',{type:'search',kind:'pessoa',id:null,name:'Maria Souza'}],
  ['Procure a vaga Desenvolvedor PHP',{type:'search',kind:'vaga',id:null,name:'Desenvolvedor PHP'}],
  ['Quero ver as vagas disponíveis',{type:'browse',kind:'vaga'}],
  ['Liste as empresas cadastradas',{type:'browse',kind:'empresa'}],
  ['Mostre todos os candidatos',{type:'browse',kind:'pessoa'}],
  ['Quais vagas existem?',{type:'browse',kind:'vaga'}],
  ['Mostre o anúncio #15',{type:'search',kind:'vaga',id:15,name:''}],
  ['Encontre João Silva',{type:'search',kind:null,id:null,name:'João Silva'}],
  ['Exclua a vaga 12',{type:'delete',kind:'vaga',id:12,name:''}],
  ['Elimine a empresa ID 19',{type:'delete',kind:'empresa',id:19,name:''}],
  ['Remova o candidato João Silva',{type:'delete',kind:'pessoa',id:null,name:'João Silva'}],
  ['Envie mensagem para empresa ID 4 dizendo: Olá, podemos conversar?',
    {type:'message',kind:'empresa',id:4,name:'',body:'Olá, podemos conversar?'}],
  ['Avise a candidata Maria Silva com o texto: Precisamos atualizar o cadastro',
    {type:'message',kind:'pessoa',id:null,name:'Maria Silva',body:'Precisamos atualizar o cadastro'}],
  ['Prepare um recado para João Silva: Olá João',
    {type:'message',kind:null,id:null,name:'João Silva',body:'Olá João'}],
  ['Me mostre minhas tarefas',{type:'task_list'}],
  ['Quais atividades pendentes?',{type:'task_list'}],
  ['Crie uma tarefa para revisar as vagas',{type:'task_create',title:'revisar as vagas'}],
  ['Adicione uma atividade chamada Conferir denúncias',{type:'task_create',title:'Conferir denúncias'}],
  ['Conclua a tarefa #7',{type:'task_update',action:'complete',id:7}],
  ['Inicie a atividade 12',{type:'task_update',action:'start',id:12}],
  ['Cancele a tarefa ID 3',{type:'task_update',action:'cancel',id:3}],
  ['Quero consultar meus rascunhos',{type:'drafts'}],
  ['Minhas mensagens preparadas',{type:'drafts'}],
  ['Mostre o histórico das ações administrativas',{type:'activity'}],
  ['Auditoria de atividades',{type:'activity'}],
  ['Meu histórico de conversas',{type:'conversation_history'}],
  ['Mostre os chats anteriores',{type:'conversation_history'}],
];
for(const [input,expected] of examples){
  test('roteamento natural seguro: '+input,()=>assert.deepEqual(classifier(input),expected));
}
const unaffected=[
  'Olá JARVIS, tudo bem?',
  'Como funciona o currículo?',
  'Preciso de dicas para contratar pessoas.',
  'Exclua tudo agora!',
  'Meu relatório de suporte está estranho.',
  'Escreva um poema sobre empregos.',
  'Como melhorar as minhas vagas?',
  'Tem como melhorar nossa política de privacidade?',
  'Quais são os atendimentos na minha jurisdição?',
  'Mostre os chamados de suporte',
];
for(const input of unaffected){
  test('não intercepta mensagem genérica ou encaminhada ao caso: '+input,
    ()=>assert.equal(classifier(input),null));
}
test('mensagens nunca são enviadas e exclusões nunca são executadas pelo parser',()=>{
  const source=read('js/jarvis-admin.js');
  const module=read('js/jarvis-admin-natural.js');
  assert.match(source,/DevInJarvisAdminNatural\?\.classify/);
  assert.match(source,/action:'manual_draft'/);
  assert.match(source,/approval:'EXCLUIR'/);
  assert.match(source,/approval:'ENVIAR'/);
  assert.match(source,/window\.confirm/);
  assert.doesNotMatch(module,/fetch\(|XMLHttpRequest|DELETE FROM|localStorage|new Function/);
});
test('página carrega o interpretador antes do JARVIS principal',()=>{
  const php=read('php/adm.php');
  const natural=php.indexOf('js/jarvis-admin-natural.js');
  const main=php.indexOf('js/jarvis-admin.js');
  assert.ok(natural>=0 && main>natural);
});
