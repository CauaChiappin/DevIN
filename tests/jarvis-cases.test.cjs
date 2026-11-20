const test = require('node:test'); // Explicação DevIN: Declara variável JavaScript test para reutilizar valor ou resultado.
const assert = require('node:assert/strict'); // Explicação DevIN: Declara variável JavaScript assert para reutilizar valor ou resultado.
const fs = require('node:fs'); // Explicação DevIN: Declara variável JavaScript fs para reutilizar valor ou resultado.
const path = require('node:path'); // Explicação DevIN: Declara variável JavaScript path para reutilizar valor ou resultado.
const root = path.resolve(__dirname, '..'); // Explicação DevIN: Declara variável JavaScript root para reutilizar valor ou resultado.
const read = file => fs.readFileSync(path.join(root, file), 'utf8'); // Explicação DevIN: Declara variável JavaScript read para reutilizar valor ou resultado.

test('JARVIS centraliza casos no chat, sem IA embutida na tela Suporte', () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  const support = read('php/components/support-admin.php'); // Explicação DevIN: Declara variável JavaScript support para reutilizar valor ou resultado.
  const ui = read('js/jarvis-admin.js'); // Explicação DevIN: Declara variável JavaScript ui para reutilizar valor ou resultado.
  const widget = read('php/components/jarvis-admin-widget.php'); // Explicação DevIN: Declara variável JavaScript widget para reutilizar valor ou resultado.
  const dashboard = read('php/adm.php'); // Explicação DevIN: Declara variável JavaScript dashboard para reutilizar valor ou resultado.
  assert.doesNotMatch(support, /data-support-ai/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(ui, /DevInJarvisCases/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(ui, /caseChat\?\.matches\(value\)/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(widget, /data-cases="api\/jarvis\/cases.php"/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(dashboard, /jarvis-cases\.js/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('ferramentas de caso garantem sessão ADM, CSRF, permissão e confirmação separada', () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  const endpoint = read('php/api/jarvis/cases.php'); // Explicação DevIN: Declara variável JavaScript endpoint para reutilizar valor ou resultado.
  const logic = read('php/controllers/Jarvis/JarvisCaseDesk.php'); // Explicação DevIN: Declara variável JavaScript logic para reutilizar valor ou resultado.
  const chat = read('js/jarvis-cases.js'); // Explicação DevIN: Declara variável JavaScript chat para reutilizar valor ou resultado.
  assert.match(endpoint, /jarvisAdmin\(\)/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(endpoint, /jarvisCsrf\(\)/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(endpoint, /hash_equals/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(endpoint, /devin_jarvis_case_pending/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(endpoint, /random_bytes\(24\)/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(endpoint, /\$action === 'confirm'/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(endpoint, /\$approval/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(endpoint, /SupportDesk::reply\(/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(endpoint, /decideJobReport\(/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(endpoint, /decideProfileReport\(/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(logic, /AdminRolePolicy::requireAction/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(logic, /scope/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(logic, /caseContext/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(chat, /window\.prompt\(/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(chat, /action:'prepare'/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(chat, /action:'confirm'/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.doesNotMatch(chat, /fetch\([^\n]*\bDELETE\b/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('JARVIS aceita prints somente mediante autorização e sem metadados EXIF', () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  const api = read('php/api/jarvis/cases.php'); // Explicação DevIN: Declara variável JavaScript api para reutilizar valor ou resultado.
  const chat = read('js/jarvis-cases.js'); // Explicação DevIN: Declara variável JavaScript chat para reutilizar valor ou resultado.
  assert.match(api, /\$action === 'vision'/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(api, /imagecreatefromstring/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(api, /imagejpeg\(/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(api, /SupportDesk::attachment\(/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(api, /image\/jpeg/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(api, /4 \* 1024 \* 1024/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(chat, /consent\.checked/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(chat, /X-CSRF-Token/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(chat, /type = 'file'/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(chat, /FormData/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('decisão com sentença humana é preservada nos reports e não muda o fluxo existente', () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  const jobs = read('php/controllers/JobReports.php'); // Explicação DevIN: Declara variável JavaScript jobs para reutilizar valor ou resultado.
  const people = read('php/controllers/ProfileReports.php'); // Explicação DevIN: Declara variável JavaScript people para reutilizar valor ou resultado.
  for (const source of [jobs, people]) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
    assert.match(source, /\?string \$customParecer = null/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(source, /\$parecer = \$customParecer/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('diagnóstico da IA de currículo não consulta chave nem expõe documento', () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  const status = read('php/resume_ai_readiness.php'); // Explicação DevIN: Declara variável JavaScript status para reutilizar valor ou resultado.
  const generate = read('php/gerar_curriculo_api.php'); // Explicação DevIN: Declara variável JavaScript generate para reutilizar valor ou resultado.
  const review = read('php/analisar_curriculo_abnt_api.php'); // Explicação DevIN: Declara variável JavaScript review para reutilizar valor ou resultado.
  assert.match(status, /requireWebAuth\('pessoa'\)/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(status, /DEVIN_GEMINI_API_KEY/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.doesNotMatch(status, /'key'\s*=>\s*\$key|api_key'\s*=>\s*\$key/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(generate, /responseMimeType/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(generate, /ResumeDocument::saveGenerated/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(review, /resume_abnt_consent/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(review, /ResumeReviewResult::normalize/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('minha jurisdição é suporte atribuído ao representante, não todos os reports', () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  const svc = read('php/controllers/Jarvis/JarvisCaseDesk.php'); // Explicação DevIN: Declara variável JavaScript svc para reutilizar valor ou resultado.
  const api = read('php/api/jarvis/cases.php'); // Explicação DevIN: Declara variável JavaScript api para reutilizar valor ou resultado.
  const js = read('js/jarvis-cases.js'); // Explicação DevIN: Declara variável JavaScript js para reutilizar valor ou resultado.
  const migration = read('database/migrations/2026_11_20_008_support_representatives.sql'); // Explicação DevIN: Declara variável JavaScript migration para reutilizar valor ou resultado.
  assert.match(svc, /SupportRepresentative::myTickets/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(svc, /\$scope === 'mine'/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(svc, /\$scope === 'mine' \? \[\]/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(api, /\$_GET\['scope'\]/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(api, /SupportRepresentative::assign/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(js, /jurisdicao/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(js, /scope=/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(js, /representante/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(js, /'takeover'/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(js, /'release'/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(migration, /support_assignment_events/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  assert.match(migration, /assigned_admin_id/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('detalhes do JARVIS retornam representante igual à fila do suporte', () => { // Explicação DevIN: Testa a equivalência entre titular exibido no chat e na central ADM.
  const source = read('php/controllers/Jarvis/JarvisCaseDesk.php'); // Explicação DevIN: Lê o serviço real em busca do contrato de detalhe.
  const method = source.slice(source.indexOf('public static function detail(')); // Explicação DevIN: Isola o método que monta o caso selecionado.
  const body = method.slice(0, method.indexOf("if ($type !== 'support')") > 0 ? method.indexOf("if ($type !== 'support')") : 1900); // Explicação DevIN: Mantém a inspeção dentro do bloco de detalhe.
  assert.match(body, /'assigned_admin_id'\s*=>\s*isset\(\$ticket\['assigned_admin_id'\]\)/); // Explicação DevIN: Exige ID de representante presente no detalhe.
  assert.match(body, /'assigned_to_me'\s*=>\s*\(bool\)/); // Explicação DevIN: Garante comparação contextual com ADM logado.
  const listBlock = source.slice(source.indexOf('public static function listing('), source.indexOf('public static function detail(')); // Explicação DevIN: Isola lista dos casos.
  assert.equal((listBlock.match(/'assigned_admin_id'\s*=>/g) || []).length, 1); // Explicação DevIN: Impede campos de proprietário duplicados.
});

test('revisão mobile do JARVIS destaca representante e libera espaço na conversa', () => { // Explicação DevIN: Protege correção de usabilidade no celular.
  const css = read('css/jarvis-admin.css'); // Explicação DevIN: Examina o CSS isolado do JARVIS.
  const ui = read('js/jarvis-cases.js'); // Explicação DevIN: Examina evento ao selecionar o caso.
  assert.match(css, /jarvis-admin__case-assignment/); // Explicação DevIN: Exige botão de assumir com visual personalizado.
  assert.match(css, /\.jarvis-admin\.is-case-focused \.jarvis-admin__tabs/); // Explicação DevIN: As abas passam a usar uma fileira navegável.
  assert.match(css, /\.jarvis-admin\.is-case-focused \.jarvis-admin__search-wrap/); // Explicação DevIN: Busca é ocultada apenas enquanto o caso está selecionado.
  assert.match(css, /overflow-x:auto/); // Explicação DevIN: Conserva navegação em telas estreitas.
  assert.match(ui, /root\.classList\.add\('is-case-focused'\)/); // Explicação DevIN: Ativa o modo de foco.
  assert.match(ui, /root\.classList\.remove\('is-case-focused'\)/); // Explicação DevIN: Garante restauração da interface.
});
