const test=require('node:test'); // Explicação DevIN: Declara variável JavaScript test para reutilizar valor ou resultado.
const assert=require('node:assert/strict'); // Explicação DevIN: Declara variável JavaScript assert para reutilizar valor ou resultado.
const fs=require('node:fs'); // Explicação DevIN: Declara variável JavaScript fs para reutilizar valor ou resultado.
const path=require('node:path'); // Explicação DevIN: Declara variável JavaScript path para reutilizar valor ou resultado.
const semantic=require('../js/jarvis-semantic.js'); // Explicação DevIN: Declara variável JavaScript semantic para reutilizar valor ou resultado.
const read=name=>fs.readFileSync(path.join(__dirname,'..',name),'utf8'); // Explicação DevIN: Declara variável JavaScript read para reutilizar valor ou resultado.
const valid=[ // Explicação DevIN: Declara variável JavaScript valid para reutilizar valor ou resultado.
  ['support_mine','case','list','mine'], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['support_queue','case','list','support'], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['reports_queue','case','list','report'], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['overview','case','overview',undefined], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['jobs','admin','browse',undefined], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['people','admin','browse',undefined], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['companies','admin','browse',undefined], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['tasks','admin','task_list',undefined], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['drafts','admin','drafts',undefined], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['audit','admin','activity',undefined], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['conversations','admin','conversation_history',undefined], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
for(const [name,target,type,filter] of valid) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
  test('resolução de intenção segura: '+name,()=>{ // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    const result=semantic.proposed(name); // Explicação DevIN: Declara variável JavaScript result para reutilizar valor ou resultado.
    assert.equal(result.target,target); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.equal(result.type,type); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    if(filter) assert.equal(result.filter,filter); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    assert.equal(Object.hasOwn(result,'sql'),false); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.equal(Object.hasOwn(result,'id'),false); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
for(const payload of [null,undefined,'none','delete','send','update','__proto__', // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
  'constructor','toString','task_update','task_create','support_takeover','analyze']) { // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  test('ação não permitida é recusada: '+String(payload), // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    ()=>assert.equal(semantic.proposed(payload),null)); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
const eligible=[ // Explicação DevIN: Declara variável JavaScript eligible para reutilizar valor ou resultado.
  'Poderia fazer um panorama dos indicadores do DevIN?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Consegue localizar os atendimentos em que estou responsável?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Queria um levantamento das empresas da plataforma', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Será que existem vagas cadastradas no portal?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Você poderia puxar as denúncias pendentes do sistema?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Quais conversas foram salvas no DevIN?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
for(const message of eligible) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
  test('pedido de consulta elegível: '+message, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    ()=>assert.equal(semantic.eligible(message),true)); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
const rejected=[ // Explicação DevIN: Declara variável JavaScript rejected para reutilizar valor ou resultado.
  'Olá, tudo bem?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Me explique como melhorar o suporte aos candidatos', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Por que é importante ter um bom currículo?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Envie uma mensagem para a empresa Maria', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Apague todos os dados do banco de dados', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Exclua os candidatos da plataforma', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Me diga a senha da empresa', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Jarvis como fazer uma vaga', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Pode consultar o e-mail pessoa@dominio.com?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
for(const message of rejected) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
  test('não enviar ao classificador: '+message, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    ()=>assert.equal(semantic.eligible(message),false)); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
test('interface não aceita ações criadas pelo LLM nem encaminha argumentos',()=>{ // Explicação DevIN: Declara o tipo não e inicia sua estrutura.
  const js=read('js/jarvis-admin.js'); // Explicação DevIN: Declara variável JavaScript js para reutilizar valor ou resultado.
  const cases=read('js/jarvis-cases.js'); // Explicação DevIN: Declara variável JavaScript cases para reutilizar valor ou resultado.
  const widget=read('php/components/jarvis-admin-widget.php'); // Explicação DevIN: Declara variável JavaScript widget para reutilizar valor ou resultado.
  const page=read('php/adm.php'); // Explicação DevIN: Declara variável JavaScript page para reutilizar valor ou resultado.
  assert.match(js,/semantic\.proposed\(response\.intent\)/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo não.
  assert.match(js,/caseChat\.handle\(value, \{type: suggestion\.type, filter: suggestion\.filter\}\)/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo não.
  assert.match(js,/handleAdminCommand\(value, \{type: suggestion\.type, kind: suggestion\.kind\}\)/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo não.
  assert.match(js,/if \(caseChat\?\.matches\(value\)\)/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo não.
  assert.match(js,/else if \(parseAdminCommand\(value\)\)/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo não.
  assert.match(cases,/authorizedReadIntent \|\| route\(value\)/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo não.
  assert.match(widget,/data-semantic="api\/jarvis\/intent.php"/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo não.
  assert.ok(page.indexOf('jarvis-semantic.js')<page.indexOf('jarvis-admin.js')); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo não.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
