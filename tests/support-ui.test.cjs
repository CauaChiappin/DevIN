/**
 * Verificações do contrato visual e de autorização da Central de Suporte.
 * Teste estático complementar: a integração real é validada por MySQL/PHP.
 */
const test = require('node:test'); // Executor nativo dos testes JavaScript.
const assert = require('node:assert/strict'); // Comparações estritas.
const fs = require('node:fs'); // Lê o código-fonte versionado.
const path = require('node:path'); // Evita caminhos dependentes do sistema.
const root = path.resolve(__dirname,'..'); // Localiza o repositório.
const read = name => fs.readFileSync(path.join(root,name),'utf8'); // Carrega arquivo.

test('interface exige assunto e descrição e anexo opcional de 20 MB', () => {
    const page = read('php/suporte.php');
    const js = read('js/support-ui.js');
    assert.match(page, /name="category"\s+required/);
    assert.match(page, /name="description"[\s\S]*?required/);
    assert.match(page, /name="attachment"\s+type="file"/);
    assert.match(js, /20\s*\*\s*1024\s*\*\s*1024/);
    assert.match(read('php/controllers/SupportDesk.php'), /20\s*\*\s*1024\s*\*\s*1024/);
    assert.match(read('php/support_api.php'), /requireValidCsrf\(\)/);
});

test('ADM e usuários acessam suporte por menu e botão de Ajuda', () => {
    const admin = read('php/adm.php');
    assert.match(admin,/adm\.php\?pagina=suporte/);
    assert.match(admin, /support-admin\.php/);
    assert.match(admin,/dashboardIcon\('flag'\)[\s\S]*?Reports/);
    for(const page of ['pessoa','empresa']) {
        const html = read('php/'+page+'.php');
        assert.match(html,/href="suporte\.php"/);
        assert.match(html,/class="support-floating-help"/);
        assert.match(html,/dashboardIcon\('lifebuoy'\)/);
    }
});

test('anexos privados e exibição de texto sem HTML dinâmico', () => {
    const src=read('js/support-ui.js');
    const api=read('php/support_api.php');
    const desk=read('php/controllers/SupportDesk.php');
    assert.doesNotMatch(src,/\.innerHTML\s*=/);
    assert.match(src,/textContent\s*=/);
    assert.match(api,/SupportDesk::attachment/);
    assert.match(api,/Content-Disposition:\s*attachment/);
    assert.match(desk,/is_uploaded_file/);
    assert.match(desk,/move_uploaded_file/);
    assert.match(desk,/FILEINFO_MIME_TYPE/);
    assert.match(read('storage/.htaccess'),/Require all denied/);
});

test('ADM responde e usuário recebe canais de notificação independentes', () => {
    const api=read('php/support_api.php');
    const desk=read('php/controllers/SupportDesk.php');
    assert.match(api,/SupportDesk::reply/);
    assert.match(desk,/appendPlatformChatNotification/);
    assert.match(desk,/MailerHelper::enviar/);
    assert.match(desk,/chat_sent_at/);
    assert.match(desk,/email_sent_at/);
    assert.match(read('php/mensagens_api.php'),/Suporte DevIN/);
});

test('JARVIS só consulta a central, sem escrever ou baixar anexos', () => {
    const tools=read('php/api/jarvis/admin_tools.php');
    const chat=read('js/jarvis-admin.js');
    assert.match(tools,/action==='support'/);
    assert.match(tools,/SupportDesk::listAdmin/);
    assert.match(chat,/action=support/);
    assert.doesNotMatch(tools,/action==='support_delete'/);
});
