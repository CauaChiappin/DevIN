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
    // O bloqueio versionado vale para todo o diretório storage, mesmo sem arquivo interno.
    assert.match(read('.htaccess'), /RewriteRule \^\(\?:storage/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
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

/**
 * Contrato mobile: as duas versões do suporte não podem exigir rolagem
 * horizontal e devem permitir navegação por botões acessíveis ao toque.
 */
test('central do usuário usa abas de chamado e histórico em celulares', () => { // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    const page = read('php/suporte.php'); // Explicação DevIN: Declara variável JavaScript page para reutilizar valor ou resultado.
    const script = read('js/support-ui.js'); // Explicação DevIN: Declara variável JavaScript script para reutilizar valor ou resultado.
    assert.match(page, /data-support-mobile-tabs/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(page, /data-support-mobile-target="create"/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(page, /data-support-mobile-target="history"/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(page, /data-mobile-view="create"/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(page, /data-support-file-preview/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(page, /data-support-description-counter/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(script, /function setMobileView\(view, scroll = false\)/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(script, /loadDetail\(Number\(ticket\.id\), true\)/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(script, /loadDetail\(response\.id, true\)/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('atendimento ADM alterna fila e detalhe e oferece retorno pelo touch', () => { // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    const admin = read('php/components/support-admin.php'); // Explicação DevIN: Declara variável JavaScript admin para reutilizar valor ou resultado.
    assert.match(admin, /data-support-mobile-target="inbox"/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(admin, /data-support-mobile-target="detail"/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(admin, /data-support-back/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(admin, /data-mobile-view="inbox"/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(read('js/support-ui.js'), /setMobileView\('inbox', true\)/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('layout responsivo possui teclado legível e proteção da barra inferior', () => { // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    const css = read('css/support.css'); // Explicação DevIN: Declara variável JavaScript css para reutilizar valor ou resultado.
    assert.match(css, /max-width:\s*767px/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(css, /support-mobile-tabs/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(css, /support-mobile-panels\[data-mobile-view="history"\]/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(css, /support-mobile-panels\[data-mobile-view="detail"\]/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(css, /font-size:\s*16px/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(css, /safe-area-inset-bottom/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(css, /overflow-wrap:\s*anywhere/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

/**
 * Regressões mobile: a pessoa deve abrir o atendimento sem rolar toda a lista
 * e o administrador deve poder filtrar a fila e responder com contador claro.
 */
test('a leitura de um chamado recolhe a lista longa somente no celular', () => { // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    const user = read('php/suporte.php'); // Explicação DevIN: Declara variável JavaScript user para reutilizar valor ou resultado.
    const code = read('js/support-ui.js'); // Explicação DevIN: Declara variável JavaScript code para reutilizar valor ou resultado.
    const css = read('css/support.css'); // Explicação DevIN: Declara variável JavaScript css para reutilizar valor ou resultado.
    assert.match(user, /data-support-history-overview/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(user, /data-support-history-back/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(code, /function setHistoryView\(view\)/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(code, /setHistoryView\('detail'\)/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(code, /setHistoryView\('list'\)/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(css, /data-support-history-view="detail"/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
test('ADM filtra chamados por situação sem sair da página', () => { // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    const view = read('php/components/support-admin.php'); // Explicação DevIN: Declara variável JavaScript view para reutilizar valor ou resultado.
    const code = read('js/support-ui.js'); // Explicação DevIN: Declara variável JavaScript code para reutilizar valor ou resultado.
    assert.match(view, /data-support-status-filter/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(view, /value="aberto"/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(view, /value="respondido"/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(code, /desiredStatus/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(code, /statusFilter\?\.addEventListener\('change', renderList\)/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
test('resposta administrativa tem contagem e área de ações móvel', () => { // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    const view = read('php/components/support-admin.php'); // Explicação DevIN: Declara variável JavaScript view para reutilizar valor ou resultado.
    const code = read('js/support-ui.js'); // Explicação DevIN: Declara variável JavaScript code para reutilizar valor ou resultado.
    const css = read('css/support.css'); // Explicação DevIN: Declara variável JavaScript css para reutilizar valor ou resultado.
    assert.match(view, /data-support-reply-counter/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(code, /function updateReplyCounter\(\)/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(code, /updateReplyCounter\(\);/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(css, /support-admin-view \.support-actions\s*\{\s*position:\s*sticky/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
