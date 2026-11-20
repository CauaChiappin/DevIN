const test = require('node:test'); // Explicação DevIN: Declara variável JavaScript test para reutilizar valor ou resultado.
const assert = require('node:assert/strict'); // Explicação DevIN: Declara variável JavaScript assert para reutilizar valor ou resultado.
const fs = require('node:fs'); // Explicação DevIN: Declara variável JavaScript fs para reutilizar valor ou resultado.
const path = require('node:path'); // Explicação DevIN: Declara variável JavaScript path para reutilizar valor ou resultado.
const intents = require('../js/jarvis-intents.js'); // Explicação DevIN: Declara variável JavaScript intents para reutilizar valor ou resultado.
const read = p => fs.readFileSync(path.join(__dirname, '..', p), 'utf8'); // Explicação DevIN: Declara variável JavaScript read para reutilizar valor ou resultado.

const sameOwner = [ // Explicação DevIN: Declara variável JavaScript sameOwner para reutilizar valor ou resultado.
  'Quais são os suportes que eu estou de representante?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Me mostre os chamados que estão na minha jurisdição', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Quais são os atendimentos sob minha responsabilidade?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Os tickets que estão comigo', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Você pode puxar minhas solicitações?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Quais chamados eu assumi?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Quem eu estou representando?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Tem algum chamado pra mim?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Mostre os meus atendimentos', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Estou atendendo quais chamados?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Quais casos estão na minha carteira?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Me ajuda com os reports da minha jurisdição', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Meus chamados pendentes', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Me traga minha fila de suporte', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Me diz os suportes atribuídos a mim', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Me mostra os atendimetos que estou atendendo', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Traz os chamdos que assumi', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'O que eu to cuidando no suporte?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Lista minhas ocorrências abertas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Quais tickets eu tenho para resolver?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Quais chamados eu estou acompanhando?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Puxa os reportes da minha responsabilidade', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
for (const question of sameOwner) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
  test('intenção local da carteira ADM: ' + question, () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    const result = intents.classify(question); // Explicação DevIN: Declara variável JavaScript result para reutilizar valor ou resultado.
    assert.equal(result?.type, 'list'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.equal(result.filter, 'mine'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

const sharedSupport = [ // Explicação DevIN: Declara variável JavaScript sharedSupport para reutilizar valor ou resultado.
  'Liste os chamados de suporte', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Quais tickets estão abertos?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Mostre todos os atendimentos', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Suporte', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Chamados de suporte', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'me ajuda com os suportes', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Consulte as solicitações de atendimento', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Quantos chamados existem?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Mostra os suprte', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
for (const question of sharedSupport) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
  test('fila local de suporte: ' + question, () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    const result = intents.classify(question); // Explicação DevIN: Declara variável JavaScript result para reutilizar valor ou resultado.
    assert.equal(result?.type, 'list'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.equal(result.filter, 'support'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
const reports = [ // Explicação DevIN: Declara variável JavaScript reports para reutilizar valor ou resultado.
  'Liste as denúncias de vagas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Mostre os reports pendentes', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Quais denúncias de perfis estão abertas?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Consulte os reportes dos candidatos', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
for (const question of reports) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
  test('denúncias sob controle de permissão ADM: ' + question, () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    const result = intents.classify(question); // Explicação DevIN: Declara variável JavaScript result para reutilizar valor ou resultado.
    assert.equal(result?.type, 'list'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.equal(result.filter, 'report'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

const details = [ // Explicação DevIN: Declara variável JavaScript details para reutilizar valor ou resultado.
  ['Abra o chamado #12', 'support', 12], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['Mostre o suporte 7', 'support', 7], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['Quero ver atendimento numero 9', 'support', 9], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['Consulte denúncia de vaga #16', 'job_report', 16], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  ['Mostre report de perfil #25', 'profile_report', 25], // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
for (const [question, caseType, id] of details) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
  test('detalhes somente leitura: ' + question, () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.deepEqual(intents.classify(question), {type:'detail', caseType, id}); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

const overview = [ // Explicação DevIN: Declara variável JavaScript overview para reutilizar valor ou resultado.
  'Quantas empresas temos cadastradas?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Qual o resumo do DevIN?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Me mostre as estatísticas do site', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Quantas pessoas temos no portal?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Total de vagas', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
for (const question of overview) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
  test('indicadores locais não passam pela OpenRouter: ' + question, () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.equal(intents.classify(question)?.type, 'overview'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

const negative = [ // Explicação DevIN: Declara variável JavaScript negative para reutilizar valor ou resultado.
  'Bom dia JARVIS!', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Como funciona o suporte?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Me explique o conceito de suporte.', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Como melhorar o atendimento ao cliente?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Como analisar candidatos?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Quero procurar vaga de desenvolvedor PHP', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Exclua a vaga ID 15', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Faça uma redação sobre trabalho', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Como instalar o aplicativo?', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  'Envie mensagem para Maria dizendo: olá', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
for (const question of negative) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
  test('não intercepta conversa geral nem ação de outro domínio: ' + question, () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.equal(intents.classify(question), null); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
} // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('perguntas ambíguas não consultam banco nem acionam uma decisão', () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.equal(intents.classify('Quais pendências eu tenho?')?.type, 'clarify'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.equal(intents.classify('Denúncia #1')?.type, 'clarify'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.equal(intents.classify('Quais denúncias estão na minha jurisdição?')?.type, 'clarify'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.equal(intents.classify('Encerre o chamado')?.type, 'clarify'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
test('análises sobre caso selecionado continuam exigindo autorização via interface', () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  const selected = {type:'support',id:1}; // Explicação DevIN: Declara variável JavaScript selected para reutilizar valor ou resultado.
  assert.deepEqual(intents.classify('Analise este caso', {activeCase:selected}), {type:'analyze',draft:false}); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.deepEqual(intents.classify('Escreva uma resposta para este atendimento', {activeCase:selected}), {type:'analyze',draft:true}); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.equal(intents.classify('Bom dia!', {activeCase:selected}), null); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.deepEqual(intents.classify('Me ajuda com reports na minha jurisdição', {activeCase:selected}), {type:'list',filter:'mine'}); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.deepEqual(intents.classify('Mostre meus outros chamados', {activeCase:selected}), {type:'list',filter:'mine'}); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.deepEqual(intents.classify('Analise o chamado #4', {activeCase:selected}), {type:'detail',caseType:'support',id:4}); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
test('integração: roteador de intenção é carregado antes do módulo de casos e do chat', () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  const php = read('php/adm.php'); // Explicação DevIN: Declara variável JavaScript php para reutilizar valor ou resultado.
  const first=php.indexOf('js/jarvis-intents.js'); // Explicação DevIN: Declara variável JavaScript first para reutilizar valor ou resultado.
  const second=php.indexOf('js/jarvis-cases.js'); // Explicação DevIN: Declara variável JavaScript second para reutilizar valor ou resultado.
  const third=php.indexOf('js/jarvis-admin.js'); // Explicação DevIN: Declara variável JavaScript third para reutilizar valor ou resultado.
  assert.ok(first>0 && first<second && second<third); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  const caseJs=read('js/jarvis-cases.js'); // Explicação DevIN: Declara variável JavaScript caseJs para reutilizar valor ou resultado.
  assert.match(caseJs, /DevInJarvisIntents\?\.classify/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(caseJs, /requestedFilter/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(caseJs, /intent\.type === 'clarify'/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(caseJs, /intent\.type === 'overview'/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(caseJs, /intent\.type === 'detail'/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
