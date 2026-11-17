const test=require('node:test');
const assert=require('node:assert/strict');
const fs=require('node:fs');
const vm=require('node:vm');
const source=fs.readFileSync(require('node:path').join(__dirname,'../js/jarvis-admin.js'),'utf8');
const begin=source.indexOf('  function parseAdminCommand(message) {');
const finish=source.indexOf('  function chatActionCard(',begin);
assert.ok(begin>0&&finish>begin,'parser de comandos presente');
const sandbox={};
vm.runInNewContext(source.slice(begin,finish)+'\nglobalResult=parseAdminCommand;',sandbox);
const parse=sandbox.globalResult;
test('identifica exclusao explicita de vaga',()=>{
  const x=parse('Jarvis, exclua a vaga ID 15');
  assert.equal(x.type,'delete'); assert.equal(x.kind,'vaga');assert.equal(x.id,15);
});
test('identifica exclusao de pessoa por ID',()=>{
  const x=parse('Exclua o candidato 23');
  assert.equal(x.type,'delete');assert.equal(x.kind,'pessoa');assert.equal(x.id,23);
});
test('identifica rascunho de mensagem sem enviar',()=>{
  const x=parse('Envie uma mensagem para a empresa ID 8 dizendo: Olá, precisamos conversar.');
  assert.equal(x.type,'message');assert.equal(x.kind,'empresa');assert.equal(x.id,8);
  assert.equal(x.body,'Olá, precisamos conversar.');
});
test('solicita ID para pedidos ambíguos',()=>assert.equal(parse('Exclua a empresa').type,'incomplete'));
test('não intercepta conversas normais',()=>assert.equal(parse('Como analisar candidatos?'),null));
test('não aceita envio de mensagem para vaga',()=>assert.equal(parse('Envie mensagem para vaga ID 12 dizendo: teste').type,'unsupported'));
