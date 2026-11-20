const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const root = path.resolve(__dirname, '..');
const read = file => fs.readFileSync(path.join(root, file), 'utf8');

test('JARVIS centraliza casos no chat, sem IA embutida na tela Suporte', () => {
  const support = read('php/components/support-admin.php');
  const ui = read('js/jarvis-admin.js');
  const widget = read('php/components/jarvis-admin-widget.php');
  const dashboard = read('php/adm.php');
  assert.doesNotMatch(support, /data-support-ai/);
  assert.match(ui, /DevInJarvisCases/);
  assert.match(ui, /caseChat\?\.matches\(value\)/);
  assert.match(widget, /data-cases="api\/jarvis\/cases.php"/);
  assert.match(dashboard, /jarvis-cases\.js/);
});

test('ferramentas de caso garantem sessão ADM, CSRF, permissão e confirmação separada', () => {
  const endpoint = read('php/api/jarvis/cases.php');
  const logic = read('php/controllers/Jarvis/JarvisCaseDesk.php');
  const chat = read('js/jarvis-cases.js');
  assert.match(endpoint, /jarvisAdmin\(\)/);
  assert.match(endpoint, /jarvisCsrf\(\)/);
  assert.match(endpoint, /hash_equals/);
  assert.match(endpoint, /devin_jarvis_case_pending/);
  assert.match(endpoint, /random_bytes\(24\)/);
  assert.match(endpoint, /\$action === 'confirm'/);
  assert.match(endpoint, /\$approval/);
  assert.match(endpoint, /SupportDesk::reply\(/);
  assert.match(endpoint, /decideJobReport\(/);
  assert.match(endpoint, /decideProfileReport\(/);
  assert.match(logic, /AdminRolePolicy::requireAction/);
  assert.match(logic, /scope/);
  assert.match(logic, /caseContext/);
  assert.match(chat, /window\.prompt\(/);
  assert.match(chat, /action:'prepare'/);
  assert.match(chat, /action:'confirm'/);
  assert.doesNotMatch(chat, /fetch\([^\n]*\bDELETE\b/);
});

test('JARVIS aceita prints somente mediante autorização e sem metadados EXIF', () => {
  const api = read('php/api/jarvis/cases.php');
  const chat = read('js/jarvis-cases.js');
  assert.match(api, /\$action === 'vision'/);
  assert.match(api, /imagecreatefromstring/);
  assert.match(api, /imagejpeg\(/);
  assert.match(api, /SupportDesk::attachment\(/);
  assert.match(api, /image\/jpeg/);
  assert.match(api, /4 \* 1024 \* 1024/);
  assert.match(chat, /consent\.checked/);
  assert.match(chat, /X-CSRF-Token/);
  assert.match(chat, /type = 'file'/);
  assert.match(chat, /FormData/);
});

test('decisão com sentença humana é preservada nos reports e não muda o fluxo existente', () => {
  const jobs = read('php/controllers/JobReports.php');
  const people = read('php/controllers/ProfileReports.php');
  for (const source of [jobs, people]) {
    assert.match(source, /\?string \$customParecer = null/);
    assert.match(source, /\$parecer = \$customParecer/);
  }
});

test('diagnóstico da IA de currículo não consulta chave nem expõe documento', () => {
  const status = read('php/resume_ai_readiness.php');
  const generate = read('php/gerar_curriculo_api.php');
  const review = read('php/analisar_curriculo_abnt_api.php');
  assert.match(status, /requireWebAuth\('pessoa'\)/);
  assert.match(status, /DEVIN_GEMINI_API_KEY/);
  assert.doesNotMatch(status, /'key'\s*=>\s*\$key|api_key'\s*=>\s*\$key/);
  assert.match(generate, /responseMimeType/);
  assert.match(generate, /ResumeDocument::saveGenerated/);
  assert.match(review, /resume_abnt_consent/);
  assert.match(review, /ResumeReviewResult::normalize/);
});
