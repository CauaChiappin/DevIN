const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const intents = require('../js/jarvis-intents.js');
const read = p => fs.readFileSync(path.join(__dirname, '..', p), 'utf8');

const sameOwner = [
  'Quais são os suportes que eu estou de representante?',
  'Me mostre os chamados que estão na minha jurisdição',
  'Quais são os atendimentos sob minha responsabilidade?',
  'Os tickets que estão comigo',
  'Você pode puxar minhas solicitações?',
  'Quais chamados eu assumi?',
  'Quem eu estou representando?',
  'Tem algum chamado pra mim?',
  'Mostre os meus atendimentos',
  'Estou atendendo quais chamados?',
  'Quais casos estão na minha carteira?',
  'Me ajuda com os reports da minha jurisdição',
  'Meus chamados pendentes',
  'Me diz os suportes atribuídos a mim',
  'Me mostra os atendimetos que estou atendendo',
  'Traz os chamdos que assumi',
  'Quais denúncias estão na minha jurisdição?',
  'O que eu to cuidando no suporte?',
  'Lista minhas ocorrências abertas',
  'Quais tickets eu tenho para resolver?',
  'Puxa os reportes da minha responsabilidade',
];
for (const question of sameOwner) {
  test('intenção local da carteira ADM: ' + question, () => {
    const result = intents.classify(question);
    assert.equal(result?.type, 'list');
    assert.equal(result.filter, 'mine');
  });
}

const sharedSupport = [
  'Liste os chamados de suporte',
  'Quais tickets estão abertos?',
  'Mostre todos os atendimentos',
  'Suporte',
  'Chamados de suporte',
  'me ajuda com os suportes',
  'Consulte as solicitações de atendimento',
  'Quantos chamados existem?',
  'Mostra os suprte',
];
for (const question of sharedSupport) {
  test('fila local de suporte: ' + question, () => {
    const result = intents.classify(question);
    assert.equal(result?.type, 'list');
    assert.equal(result.filter, 'support');
  });
}
const reports = [
  'Liste as denúncias de vagas',
  'Mostre os reports pendentes',
  'Quais denúncias de perfis estão abertas?',
  'Consulte os reportes dos candidatos',
];
for (const question of reports) {
  test('denúncias sob controle de permissão ADM: ' + question, () => {
    const result = intents.classify(question);
    assert.equal(result?.type, 'list');
    assert.equal(result.filter, 'report');
  });
}

const details = [
  ['Abra o chamado #12', 'support', 12],
  ['Mostre o suporte 7', 'support', 7],
  ['Quero ver atendimento numero 9', 'support', 9],
  ['Consulte denúncia de vaga #16', 'job_report', 16],
  ['Mostre report de perfil #25', 'profile_report', 25],
];
for (const [question, caseType, id] of details) {
  test('detalhes somente leitura: ' + question, () => {
    assert.deepEqual(intents.classify(question), {type:'detail', caseType, id});
  });
}

const overview = [
  'Quantas empresas temos cadastradas?',
  'Qual o resumo do DevIN?',
  'Me mostre as estatísticas do site',
  'Quantas pessoas temos no portal?',
  'Total de vagas',
];
for (const question of overview) {
  test('indicadores locais não passam pela OpenRouter: ' + question, () => {
    assert.equal(intents.classify(question)?.type, 'overview');
  });
}

const negative = [
  'Bom dia JARVIS!',
  'Como funciona o suporte?',
  'Me explique o conceito de suporte.',
  'Como melhorar o atendimento ao cliente?',
  'Como analisar candidatos?',
  'Quero procurar vaga de desenvolvedor PHP',
  'Exclua a vaga ID 15',
  'Faça uma redação sobre trabalho',
  'Como instalar o aplicativo?',
  'Envie mensagem para Maria dizendo: olá',
];
for (const question of negative) {
  test('não intercepta conversa geral nem ação de outro domínio: ' + question, () => {
    assert.equal(intents.classify(question), null);
  });
}

test('perguntas ambíguas não consultam banco nem acionam uma decisão', () => {
  assert.equal(intents.classify('Quais pendências eu tenho?')?.type, 'clarify');
  assert.equal(intents.classify('Denúncia #1')?.type, 'clarify');
  assert.equal(intents.classify('Encerre o chamado')?.type, 'clarify');
});
test('análises sobre caso selecionado continuam exigindo autorização via interface', () => {
  const selected = {type:'support',id:1};
  assert.deepEqual(intents.classify('Analise este caso', {activeCase:selected}), {type:'analyze',draft:false});
  assert.deepEqual(intents.classify('Escreva uma resposta para este atendimento', {activeCase:selected}), {type:'analyze',draft:true});
  assert.equal(intents.classify('Bom dia!', {activeCase:selected}), null);
});
test('integração: roteador de intenção é carregado antes do módulo de casos e do chat', () => {
  const php = read('php/adm.php');
  const first=php.indexOf('js/jarvis-intents.js');
  const second=php.indexOf('js/jarvis-cases.js');
  const third=php.indexOf('js/jarvis-admin.js');
  assert.ok(first>0 && first<second && second<third);
  const caseJs=read('js/jarvis-cases.js');
  assert.match(caseJs, /DevInJarvisIntents\?\.classify/);
  assert.match(caseJs, /requestedFilter/);
  assert.match(caseJs, /intent\.type === 'clarify'/);
  assert.match(caseJs, /intent\.type === 'overview'/);
  assert.match(caseJs, /intent\.type === 'detail'/);
});
