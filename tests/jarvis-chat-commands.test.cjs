const test=require('node:test'); // Explicação DevIN: Declara variável JavaScript test para reutilizar valor ou resultado.
const assert=require('node:assert/strict'); // Explicação DevIN: Declara variável JavaScript assert para reutilizar valor ou resultado.
const fs=require('node:fs'); // Explicação DevIN: Declara variável JavaScript fs para reutilizar valor ou resultado.
const vm=require('node:vm'); // Explicação DevIN: Declara variável JavaScript vm para reutilizar valor ou resultado.
const source=fs.readFileSync(require('node:path').join(__dirname,'../js/jarvis-admin.js'),'utf8'); // Explicação DevIN: Declara variável JavaScript source para reutilizar valor ou resultado.
const begin=source.indexOf('  function parseAdminCommand(message) {'); // Explicação DevIN: Declara função/método parseAdminCommand, com seus parâmetros e comportamento.
const finish=source.indexOf('  function chatActionCard(',begin); // Explicação DevIN: Declara função/método chatActionCard, com seus parâmetros e comportamento.
assert.ok(begin>0&&finish>begin,'parser de comandos presente'); // Explicação DevIN: Continua expressão ou bloco pertencente a função chatActionCard.
const sandbox={}; // Explicação DevIN: Declara variável JavaScript sandbox para reutilizar valor ou resultado.
vm.runInNewContext(source.slice(begin,finish)+'\nglobalResult=parseAdminCommand;',sandbox); // Explicação DevIN: Continua expressão ou bloco pertencente a função chatActionCard.
const parse=sandbox.globalResult; // Explicação DevIN: Declara variável JavaScript parse para reutilizar valor ou resultado.
test('identifica exclusao explicita de vaga',()=>{ // Explicação DevIN: Continua expressão ou bloco pertencente a função chatActionCard.
  const x=parse('Jarvis, exclua a vaga ID 15'); // Explicação DevIN: Declara variável JavaScript x para reutilizar valor ou resultado.
  assert.equal(x.type,'delete'); assert.equal(x.kind,'vaga');assert.equal(x.id,15); // Explicação DevIN: Continua expressão ou bloco pertencente a função chatActionCard.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
test('identifica exclusao de pessoa por ID',()=>{ // Explicação DevIN: Continua expressão ou bloco pertencente a função chatActionCard.
  const x=parse('Exclua o candidato 23'); // Explicação DevIN: Declara variável JavaScript x para reutilizar valor ou resultado.
  assert.equal(x.type,'delete');assert.equal(x.kind,'pessoa');assert.equal(x.id,23); // Explicação DevIN: Continua expressão ou bloco pertencente a função chatActionCard.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
test('identifica rascunho de mensagem sem enviar',()=>{ // Explicação DevIN: Continua expressão ou bloco pertencente a função chatActionCard.
  const x=parse('Envie uma mensagem para a empresa ID 8 dizendo: Olá, precisamos conversar.'); // Explicação DevIN: Declara variável JavaScript x para reutilizar valor ou resultado.
  assert.equal(x.type,'message');assert.equal(x.kind,'empresa');assert.equal(x.id,8); // Explicação DevIN: Continua expressão ou bloco pertencente a função chatActionCard.
  assert.equal(x.body,'Olá, precisamos conversar.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função chatActionCard.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
test('solicita ID para pedidos ambíguos',()=>assert.equal(parse('Exclua a empresa').type,'incomplete')); // Explicação DevIN: Continua expressão ou bloco pertencente a função chatActionCard.
test('não intercepta conversas normais',()=>assert.equal(parse('Como analisar candidatos?'),null)); // Explicação DevIN: Continua expressão ou bloco pertencente a função chatActionCard.
test('não aceita envio de mensagem para vaga',()=>assert.equal(parse('Envie mensagem para vaga ID 12 dizendo: teste').type,'unsupported')); // Explicação DevIN: Continua expressão ou bloco pertencente a função chatActionCard.

test('pesquisa vaga por titulo sem ID',()=>{
  const x=parse('Jarvis, procure vagas Desenvolvedor PHP');
  assert.equal(x.type,'search');assert.equal(x.kind,'vaga');assert.equal(x.name,'Desenvolvedor PHP');
});
test('exclusao por titulo exige selecao',()=>{
  const x=parse('Exclua a vaga Desenvolvedor PHP');
  assert.equal(x.type,'delete');assert.equal(x.kind,'vaga');assert.equal(x.id,null);
  assert.equal(x.name,'Desenvolvedor PHP');
});
test('exclusao de empresa por nome',()=>{
  const x=parse('Exclua a empresa Exemplo Tecnologia');
  assert.equal(x.type,'delete');assert.equal(x.kind,'empresa');assert.equal(x.name,'Exemplo Tecnologia');
});
test('enviar mensagem por nome com texto original preservado',()=>{
  const x=parse('Mande mensagem para candidata Maria Silva dizendo: Olá, tudo bem?');
  assert.equal(x.type,'message');assert.equal(x.kind,'pessoa');assert.equal(x.name,'Maria Silva');
  assert.equal(x.body,'Olá, tudo bem?');
});
test('identificar vaga por ID permanece compativel',()=>{
  const x=parse('Jarvis, exclua a vaga ID 15');
  assert.equal(x.type,'delete');assert.equal(x.id,15);
});
test('busca vaga nome com empresa como resultado depende da selecao, nao da IA',()=>{
  const x=parse('Pesquise vagas Engenharia de Software');
  assert.equal(x.type,'search');assert.equal(x.name,'Engenharia de Software');
});
