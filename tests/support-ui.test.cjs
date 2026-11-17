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

test('interface exige assunto e descrição e anexo opcional de 20 MB', () => { // Explicação DevIN: Declara o tipo exige e inicia sua estrutura.
    const page = read('php/suporte.php'); // Explicação DevIN: Declara variável JavaScript page para reutilizar valor ou resultado.
    const js = read('js/support-ui.js'); // Explicação DevIN: Declara variável JavaScript js para reutilizar valor ou resultado.
    assert.match(page, /name="category"\s+required/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(page, /name="description"[\s\S]*?required/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(page, /name="attachment"\s+type="file"/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(js, /20\s*\*\s*1024\s*\*\s*1024/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(read('php/controllers/SupportDesk.php'), /20\s*\*\s*1024\s*\*\s*1024/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(read('php/support_api.php'), /requireValidCsrf\(\)/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('ADM e usuários acessam suporte por menu e botão de Ajuda', () => { // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    const admin = read('php/adm.php'); // Explicação DevIN: Declara variável JavaScript admin para reutilizar valor ou resultado.
    assert.match(admin,/adm\.php\?pagina=suporte/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(admin, /support-admin\.php/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(admin,/dashboardIcon\('flag'\)[\s\S]*?Reports/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    for(const page of ['pessoa','empresa']) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
        const html = read('php/'+page+'.php'); // Explicação DevIN: Declara variável JavaScript html para reutilizar valor ou resultado.
        assert.match(html,/href="suporte\.php"/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
        assert.match(html,/class="support-floating-help"/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
        assert.match(html,/dashboardIcon\('lifebuoy'\)/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('anexos privados e exibição de texto sem HTML dinâmico', () => { // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    const src=read('js/support-ui.js'); // Explicação DevIN: Declara variável JavaScript src para reutilizar valor ou resultado.
    const api=read('php/support_api.php'); // Explicação DevIN: Declara variável JavaScript api para reutilizar valor ou resultado.
    const desk=read('php/controllers/SupportDesk.php'); // Explicação DevIN: Declara variável JavaScript desk para reutilizar valor ou resultado.
    assert.doesNotMatch(src,/\.innerHTML\s*=/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(src,/textContent\s*=/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(api,/SupportDesk::attachment/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(api,/Content-Disposition:\s*attachment/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(desk,/is_uploaded_file/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(desk,/move_uploaded_file/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(desk,/FILEINFO_MIME_TYPE/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(read('storage/.htaccess'),/Require all denied/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('ADM responde e usuário recebe canais de notificação independentes', () => { // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    const api=read('php/support_api.php'); // Explicação DevIN: Declara variável JavaScript api para reutilizar valor ou resultado.
    const desk=read('php/controllers/SupportDesk.php'); // Explicação DevIN: Declara variável JavaScript desk para reutilizar valor ou resultado.
    assert.match(api,/SupportDesk::reply/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(desk,/appendPlatformChatNotification/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(desk,/MailerHelper::enviar/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(desk,/chat_sent_at/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(desk,/email_sent_at/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(read('php/mensagens_api.php'),/Suporte DevIN/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('JARVIS só consulta a central, sem escrever ou baixar anexos', () => { // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    const tools=read('php/api/jarvis/admin_tools.php'); // Explicação DevIN: Declara variável JavaScript tools para reutilizar valor ou resultado.
    const chat=read('js/jarvis-admin.js'); // Explicação DevIN: Declara variável JavaScript chat para reutilizar valor ou resultado.
    assert.match(tools,/action==='support'/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(tools,/SupportDesk::listAdmin/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(chat,/action=support/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.doesNotMatch(tools,/action==='support_delete'/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
