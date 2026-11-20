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
    const page = read('php/components/support-user.php'); // Explicação DevIN: Declara variável JavaScript page para reutilizar valor ou resultado.
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
        assert.match(html, new RegExp('href="'+page+'\\.php\\?pagina=suporte"')); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
        assert.match(html,/class="support-floating-help"/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
        assert.match(html,/dashboardIcon\('lifebuoy'\)/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('Central de Ajuda preserva a sidebar e redireciona acessos antigos', () => { // Verifica regressões na integração ao dashboard.
    const redirect = read('php/suporte.php'); // Compatibilidade com links de suporte já enviados.
    assert.match(redirect, /pagina=suporte/); // Links antigos abrem a área integrada.
    for (const account of ['pessoa', 'empresa']) { // Os dois tipos de usuário devem conservar o menu.
        const dashboard = read('php/' + account + '.php'); // Inspeciona o layout real.
        assert.match(dashboard, /components\/support-user\.php/); // Renderiza o formulário dentro do painel.
        assert.match(dashboard, /class="sidebar"/); // O menu continua presente.
        assert.match(dashboard, /support-ui\.js/); // O histórico de chamados continua funcionando.
    } // Finaliza a verificação dos dashboards.
}); // Finaliza o teste de integração visual.

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
    const page = read('php/components/support-user.php'); // Explicação DevIN: Declara variável JavaScript page para reutilizar valor ou resultado.
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
    const user = read('php/components/support-user.php'); // Explicação DevIN: Declara variável JavaScript user para reutilizar valor ou resultado.
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

test('tradução do suporte é única e mantém nome, relatos e arquivo privados', () => { // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    const root = read('php/components/support-user.php'); // Explicação DevIN: Declara variável JavaScript root para reutilizar valor ou resultado.
    const admin = read('php/components/support-admin.php'); // Explicação DevIN: Declara variável JavaScript admin para reutilizar valor ou resultado.
    const locale = read('js/support-locale.js'); // Explicação DevIN: Declara variável JavaScript locale para reutilizar valor ou resultado.
    const global = read('js/dashboard-i18n.js'); // Explicação DevIN: Declara variável JavaScript global para reutilizar valor ou resultado.
    assert.match(root, /translate="no" data-support-root/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(admin, /translate="no" data-support-root/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(locale, /DevINTranslate/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(locale, /data-support-list/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(locale, /tagName === 'STRONG'/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(global, /script, style, textarea, input, \[data-support-root\]/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(root, /data-support-file-name/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(root, /support-file-picker-action/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    for (const role of ['empresa', 'pessoa', 'adm']) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
        assert.match(read('php/' + role + '.php'), /support-locale\.js/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('erro do banco identifica migração pendente sem duplicar alerta de formulário', () => { // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    const api = read('php/support_api.php'); // Explicação DevIN: Declara variável JavaScript api para reutilizar valor ou resultado.
    const service = read('php/controllers/SupportDesk.php'); // Explicação DevIN: Declara variável JavaScript service para reutilizar valor ou resultado.
    const ui = read('js/support-ui.js'); // Explicação DevIN: Declara variável JavaScript ui para reutilizar valor ou resultado.
    assert.match(api, /SUPPORT_SCHEMA_MISSING/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(api, /SUPPORT_UNAVAILABLE/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(service, /SupportSchemaMissingException/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(service, /getCode\(\) === 1146/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(ui, /Não foi possível carregar os chamados/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(ui, /list\.append\(button\(t\('Tentar novamente'/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(ui, /mobilePanels\?\.dataset\.mobileView !== 'create'/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(read('database/migrations/2026_11_17_005_support_center.sql'), /CREATE TABLE IF NOT EXISTS support_tickets/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('suporte ADM diferencia migração pendente de erro comum sem criar tabelas pela interface', () => { // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    const js = read('js/support-ui.js'); // Explicação DevIN: Declara variável JavaScript js para reutilizar valor ou resultado.
    const css = read('css/support.css'); // Explicação DevIN: Declara variável JavaScript css para reutilizar valor ou resultado.
    const api = read('php/support_api.php'); // Explicação DevIN: Declara variável JavaScript api para reutilizar valor ou resultado.
    assert.match(api, /SUPPORT_SCHEMA_MISSING/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(js, /failure\.code = String\(data\?\.codigo/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(js, /error\.code === 'SUPPORT_SCHEMA_MISSING'/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(js, /support-setup-notice/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(js, /support-setup-actions/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(js, /2026_11_17_005_support_center\.sql/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(js, /Chamados indisponíveis/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.doesNotMatch(js, /CREATE TABLE|ALTER TABLE|DROP TABLE/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(css, /\.dashboard-shell\.page-suporte \.support-setup-notice/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
    assert.match(css, /\.dashboard-shell\.page-suporte \.support-admin-head/); // Explicação DevIN: Continua expressão ou bloco pertencente a tipo exige.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
