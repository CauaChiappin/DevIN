const test=require('node:test'); // Explicação DevIN: Declara variável JavaScript test para reutilizar valor ou resultado.
const assert=require('node:assert/strict'); // Explicação DevIN: Declara variável JavaScript assert para reutilizar valor ou resultado.
const fs=require('node:fs'); // Explicação DevIN: Declara variável JavaScript fs para reutilizar valor ou resultado.
const path=require('node:path'); // Explicação DevIN: Declara variável JavaScript path para reutilizar valor ou resultado.
const classifier=require('../js/jarvis-admin-natural.js').classify; // Explicação DevIN: Declara variável JavaScript classifier para reutilizar valor ou resultado.
const read=filename=>fs.readFileSync(path.join(__dirname,'..',filename),'utf8'); // Explicação DevIN: Declara variável JavaScript read para reutilizar valor ou resultado.
const examples=[ // Explicação DevIN: Declara variável JavaScript examples para reutilizar valor ou resultado.
  ['Ache a empresa Acme Tecnologia',{type:'search',kind:'empresa',id:null,name:'Acme Tecnologia'}], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['Mostre a empresa ID 8',{type:'search',kind:'empresa',id:8,name:''}], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['Localize o candidato João Silva',{type:'search',kind:'pessoa',id:null,name:'João Silva'}], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['Me mostre a pessoa Maria Souza',{type:'search',kind:'pessoa',id:null,name:'Maria Souza'}], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['Procure a vaga Desenvolvedor PHP',{type:'search',kind:'vaga',id:null,name:'Desenvolvedor PHP'}], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['Quero ver as vagas disponíveis',{type:'browse',kind:'vaga'}], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['Liste as empresas cadastradas',{type:'browse',kind:'empresa'}], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['Mostre todos os candidatos',{type:'browse',kind:'pessoa'}], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['Quais vagas existem?',{type:'browse',kind:'vaga'}],
  ['Jarvis, consulte as vagas cadastradas no DevIN e mostre os resultados.',{type:'browse',kind:'vaga'}], // Explicação DevIN: Processa a etapa correspondente do fluxo administrativo.
  ['Mostre as vagas do DevIN',{type:'browse',kind:'vaga'}], // Explicação DevIN: Processa a etapa correspondente do fluxo administrativo.
  ['Consulte todas as vagas cadastradas',{type:'browse',kind:'vaga'}], // Explicação DevIN: Processa a etapa correspondente do fluxo administrativo.
  ['Quais empresas estão cadastradas?',{type:'browse',kind:'empresa'}], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['Mostre o anúncio #15',{type:'search',kind:'vaga',id:15,name:''}], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['Encontre João Silva',{type:'search',kind:null,id:null,name:'João Silva'}], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['Exclua a vaga 12',{type:'delete',kind:'vaga',id:12,name:''}], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['Elimine a empresa ID 19',{type:'delete',kind:'empresa',id:19,name:''}], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['Remova o candidato João Silva',{type:'delete',kind:'pessoa',id:null,name:'João Silva'}], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['Envie mensagem para empresa ID 4 dizendo: Olá, podemos conversar?', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    {type:'message',kind:'empresa',id:4,name:'',body:'Olá, podemos conversar?'}], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['Avise a candidata Maria Silva com o texto: Precisamos atualizar o cadastro', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    {type:'message',kind:'pessoa',id:null,name:'Maria Silva',body:'Precisamos atualizar o cadastro'}], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['Prepare um recado para João Silva: Olá João', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    {type:'message',kind:null,id:null,name:'João Silva',body:'Olá João'}], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['Me mostre minhas tarefas',{type:'task_list'}], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['Quais atividades pendentes?',{type:'task_list'}], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['Crie uma tarefa para revisar as vagas',{type:'task_create',title:'revisar as vagas'}], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['Adicione uma atividade chamada Conferir denúncias',{type:'task_create',title:'Conferir denúncias'}], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['Conclua a tarefa #7',{type:'task_update',action:'complete',id:7}], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['Inicie a atividade 12',{type:'task_update',action:'start',id:12}], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['Cancele a tarefa ID 3',{type:'task_update',action:'cancel',id:3}], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['Quero consultar meus rascunhos',{type:'drafts'}], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['Minhas mensagens preparadas',{type:'drafts'}], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['Mostre o histórico das ações administrativas',{type:'activity'}], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['Auditoria de atividades',{type:'activity'}], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['Meu histórico de conversas',{type:'conversation_history'}], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['Mostre os chats anteriores',{type:'conversation_history'}], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
for(const [input,expected] of examples){ // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
  test('roteamento natural seguro: '+input,()=>assert.deepEqual(classifier(input),expected)); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
const unaffected=[ // Explicação DevIN: Declara variável JavaScript unaffected para reutilizar valor ou resultado.
  'Olá JARVIS, tudo bem?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Como funciona o currículo?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Preciso de dicas para contratar pessoas.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Exclua tudo agora!', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Meu relatório de suporte está estranho.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Escreva um poema sobre empregos.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Como melhorar as minhas vagas?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Tem como melhorar nossa política de privacidade?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Quais são os atendimentos na minha jurisdição?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Mostre os chamados de suporte', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
for(const input of unaffected){ // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
  test('não intercepta mensagem genérica ou encaminhada ao caso: '+input, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    ()=>assert.equal(classifier(input),null)); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
test('mensagens nunca são enviadas e exclusões nunca são executadas pelo parser',()=>{ // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  const source=read('js/jarvis-admin.js'); // Explicação DevIN: Declara variável JavaScript source para reutilizar valor ou resultado.
  const module=read('js/jarvis-admin-natural.js'); // Explicação DevIN: Declara variável JavaScript module para reutilizar valor ou resultado.
  assert.match(source,/DevInJarvisAdminNatural\?\.classify/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(source,/action:'manual_draft'/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(source,/approval:'EXCLUIR'/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(source,/approval:'ENVIAR'/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(source,/window\.confirm/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.doesNotMatch(module,/fetch\(|XMLHttpRequest|DELETE FROM|localStorage|new Function/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
test('página carrega o interpretador antes do JARVIS principal',()=>{ // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  const php=read('php/adm.php'); // Explicação DevIN: Declara variável JavaScript php para reutilizar valor ou resultado.
  const natural=php.indexOf('js/jarvis-admin-natural.js'); // Explicação DevIN: Declara variável JavaScript natural para reutilizar valor ou resultado.
  const main=php.indexOf('js/jarvis-admin.js'); // Explicação DevIN: Declara variável JavaScript main para reutilizar valor ou resultado.
  assert.ok(natural>=0 && main>natural); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.


test('formulários JARVIS possuem recuperação de estado, timeout e painel ADM', () => { // Explicação DevIN: Processa a etapa correspondente do fluxo administrativo.
  const app=read('js/jarvis-admin.js'); // Explicação DevIN: Processa a etapa correspondente do fluxo administrativo.
  const ui=read('php/components/jarvis-admin-widget.php'); // Explicação DevIN: Processa a etapa correspondente do fluxo administrativo.
  const api=read('php/api/jarvis/admin_tools.php'); // Explicação DevIN: Processa a etapa correspondente do fluxo administrativo.
  assert.match(app,/AbortController/); // Explicação DevIN: Processa a etapa correspondente do fluxo administrativo.
  assert.match(app,/submit\.disabled = false/); // Explicação DevIN: Processa a etapa correspondente do fluxo administrativo.
  assert.match(app,/submit\.disabled=false/); // Explicação DevIN: Processa a etapa correspondente do fluxo administrativo.
  assert.match(app,/loadAdminOverview/); // Explicação DevIN: Processa a etapa correspondente do fluxo administrativo.
  assert.match(ui,/data-jarvis-tab="overview"/); // Explicação DevIN: Processa a etapa correspondente do fluxo administrativo.
  assert.match(ui,/data-jarvis-pane="overview"/); // Explicação DevIN: Processa a etapa correspondente do fluxo administrativo.
  assert.match(api,/session_write_close\(\)/); // Explicação DevIN: Processa a etapa correspondente do fluxo administrativo.
}); // Explicação DevIN: Processa a etapa correspondente do fluxo administrativo.

test('.env.example contém somente comentários e atribuições dotenv', () => { // Explicação DevIN: Processa a etapa correspondente do fluxo administrativo.
  const env=read('.env.example'); // Explicação DevIN: Processa a etapa correspondente do fluxo administrativo.
  for (const line of env.split(/\r?\n/)) { // Explicação DevIN: Processa a etapa correspondente do fluxo administrativo.
    assert.ok(!line.trim() || line.trim().startsWith('#') || /^[A-Za-z_][A-Za-z0-9_]*=/.test(line), line); // Explicação DevIN: Processa a etapa correspondente do fluxo administrativo.
  } // Explicação DevIN: Processa a etapa correspondente do fluxo administrativo.
}); // Explicação DevIN: Processa a etapa correspondente do fluxo administrativo.
