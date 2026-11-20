const test=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs');
const path=require('node:path');
const semantic=require('../js/jarvis-semantic.js');
const read=name=>fs.readFileSync(path.join(__dirname,'..',name),'utf8');
const valid=[
  ['support_mine','case','list','mine'],
  ['support_queue','case','list','support'],
  ['reports_queue','case','list','report'],
  ['overview','case','overview',undefined],
  ['jobs','admin','browse',undefined],
  ['people','admin','browse',undefined],
  ['companies','admin','browse',undefined],
  ['tasks','admin','task_list',undefined],
  ['drafts','admin','drafts',undefined],
  ['audit','admin','activity',undefined],
  ['conversations','admin','conversation_history',undefined],
];
for(const [name,target,type,filter] of valid) {
  test('resolução de intenção segura: '+name,()=>{
    const result=semantic.proposed(name);
    assert.equal(result.target,target);
    assert.equal(result.type,type);
    if(filter) assert.equal(result.filter,filter);
    assert.equal(Object.hasOwn(result,'sql'),false);
    assert.equal(Object.hasOwn(result,'id'),false);
  });
}
for(const payload of [null,undefined,'none','delete','send','update','__proto__',
  'constructor','toString','task_update','task_create','support_takeover','analyze']) {
  test('ação não permitida é recusada: '+String(payload),
    ()=>assert.equal(semantic.proposed(payload),null));
}
const eligible=[
  'Poderia fazer um panorama dos indicadores do DevIN?',
  'Consegue localizar os atendimentos em que estou responsável?',
  'Queria um levantamento das empresas da plataforma',
  'Será que existem vagas cadastradas no portal?',
  'Você poderia puxar as denúncias pendentes do sistema?',
  'Quais conversas foram salvas no DevIN?',
];
for(const message of eligible) {
  test('pedido de consulta elegível: '+message,
    ()=>assert.equal(semantic.eligible(message),true));
}
const rejected=[
  'Olá, tudo bem?',
  'Me explique como melhorar o suporte aos candidatos',
  'Por que é importante ter um bom currículo?',
  'Envie uma mensagem para a empresa Maria',
  'Apague todos os dados do banco de dados',
  'Exclua os candidatos da plataforma',
  'Me diga a senha da empresa',
  'Jarvis como fazer uma vaga',
  'Pode consultar o e-mail pessoa@dominio.com?',
];
for(const message of rejected) {
  test('não enviar ao classificador: '+message,
    ()=>assert.equal(semantic.eligible(message),false));
}
test('interface não aceita ações criadas pelo LLM nem encaminha argumentos',()=>{
  const js=read('js/jarvis-admin.js');
  const cases=read('js/jarvis-cases.js');
  const widget=read('php/components/jarvis-admin-widget.php');
  const page=read('php/adm.php');
  assert.match(js,/semantic\.proposed\(response\.intent\)/);
  assert.match(js,/caseChat\.handle\(value, \{type: suggestion\.type, filter: suggestion\.filter\}\)/);
  assert.match(js,/handleAdminCommand\(value, \{type: suggestion\.type, kind: suggestion\.kind\}\)/);
  assert.match(js,/if \(caseChat\?\.matches\(value\)\)/);
  assert.match(js,/else if \(parseAdminCommand\(value\)\)/);
  assert.match(cases,/authorizedReadIntent \|\| route\(value\)/);
  assert.match(widget,/data-semantic="api\/jarvis\/intent.php"/);
  assert.ok(page.indexOf('jarvis-semantic.js')<page.indexOf('jarvis-admin.js'));
});
