/**
 * Testa as traduções reais carregadas pelo painel DevIN para a Central de Suporte.
 * O suporte deve usar pt-BR, en e es, sem traduzir dados enviados pelo usuário.
 */
const test = require('node:test'); // Explicação DevIN: Executor nativo de testes.
const assert = require('node:assert/strict'); // Explicação DevIN: Comparações sem coerção.
const fs = require('node:fs'); // Explicação DevIN: Lê os arquivos versionados.
const path = require('node:path'); // Explicação DevIN: Normaliza caminhos.
const vm = require('node:vm'); // Explicação DevIN: Executa tradutor em navegador fictício.
const root = path.resolve(__dirname, '..'); // Explicação DevIN: Obtém a pasta raiz.
const read = name => fs.readFileSync(path.join(root,name),'utf8'); // Explicação DevIN: Lê fonte em UTF-8.

function translator(lang) { // Explicação DevIN: Inicializa o tradutor no idioma solicitado.
    const window = {}; // Explicação DevIN: Guarda a função global usada pelas páginas.
    const body = {hasAttribute: () => false, querySelectorAll: () => []}; // Explicação DevIN: Representa documento sem elementos visíveis.
    const document = { // Explicação DevIN: Oferece somente interfaces usadas pelo script.
        documentElement: {lang}, // Explicação DevIN: Seleciona preferência de idioma.
        title: 'DevIN', // Explicação DevIN: Mantém o título da página de teste.
        body, // Explicação DevIN: Fornece raiz para tradução inicial.
        createTreeWalker: () => ({nextNode: () => null}) // Explicação DevIN: Evita depender de navegador real.
    }; // Explicação DevIN: Conclui o contexto do documento.
    class MutationObserver { observe() {} } // Explicação DevIN: Simula observação de mensagens dinâmicas.
    vm.runInNewContext(read('js/dashboard-i18n.js'), {window, document, MutationObserver, NodeFilter: {SHOW_TEXT: 4}}); // Explicação DevIN: Carrega os textos reais do site.
    return window.DevINTranslate; // Explicação DevIN: Entrega a função usada pela Central.
} // Explicação DevIN: Finaliza a inicialização do tradutor.

test('traduz campos e ações do suporte para inglês e espanhol', () => { // Explicação DevIN: Confere rótulos visíveis da tela.
    const en = translator('en'); // Explicação DevIN: Prepara idioma inglês.
    const es = translator('es'); // Explicação DevIN: Prepara idioma espanhol.
    assert.equal(en('Como podemos ajudar?'), 'How can we help?'); // Explicação DevIN: Confere cabeçalho do suporte.
    assert.equal(es('Como podemos ajudar?'), '¿Cómo podemos ayudarte?'); // Explicação DevIN: Confere o mesmo cabeçalho em espanhol.
    assert.equal(en('Meus chamados'), 'My requests'); // Explicação DevIN: Confere o histórico.
    assert.equal(es('Enviar solicitação'), 'Enviar solicitud'); // Explicação DevIN: Confere ação principal.
    assert.equal(en('Selecione uma categoria'), 'Select a category'); // Explicação DevIN: Confere opções do formulário.
    assert.equal(es('Caixa de entrada'), 'Bandeja de entrada'); // Explicação DevIN: Confere interface ADM.
}); // Explicação DevIN: Finaliza teste de campos.

test('traduz avisos, confirmações e mensagens da API sem alterar texto livre', () => { // Explicação DevIN: Confere suporte além do HTML estático.
    const en = translator('en'); // Explicação DevIN: Usa o dicionário inglês.
    const es = translator('es'); // Explicação DevIN: Usa o dicionário espanhol.
    assert.equal(en('Encerrar este chamado? As mensagens continuam registradas.'), 'Close this request? The messages will remain saved.'); // Explicação DevIN: Confere confirmação ADM.
    assert.equal(es('O suporte ainda não foi configurado no banco. Aplique a migração de suporte.'), 'El soporte aún no está configurado en la base de datos. Aplica la migración de soporte.'); // Explicação DevIN: Confere diagnóstico comum.
    assert.equal(en('Descrição enviada'), 'Submitted description'); // Explicação DevIN: Confere detalhes do atendimento.
    assert.equal(es('Histórico do atendimento'), 'Historial de atención'); // Explicação DevIN: Confere histórico.
    const userMessage = 'Meu problema com o pedido #123 não está na lista.'; // Explicação DevIN: Simula conteúdo escrito pelo usuário.
    assert.equal(en(userMessage), userMessage); // Explicação DevIN: Evita editar relatos no dicionário.
}); // Explicação DevIN: Finaliza teste de mensagens.

test('mantém o português original e traduz textos dinâmicos pelo mesmo helper', () => { // Explicação DevIN: Preserva o idioma padrão.
    const pt = translator('pt-BR'); // Explicação DevIN: Seleciona o português.
    assert.equal(pt('Meus chamados'), 'Meus chamados'); // Explicação DevIN: Confere que não altera o idioma.
    const ui = read('js/support-ui.js'); // Explicação DevIN: Inspeciona o JavaScript da central.
    assert.match(ui, /window\.DevINTranslate/); // Explicação DevIN: Confere uso do tradutor compartilhado.
    assert.match(ui, /t\('caracteres'\)/); // Explicação DevIN: Confere contadores de texto.
    assert.match(ui, /t\('Encerrar este chamado/); // Explicação DevIN: Confere tradução da confirmação.
    assert.doesNotMatch(ui, /innerHTML\s*=/); // Explicação DevIN: Garante que relatos não viram HTML.
}); // Explicação DevIN: Finaliza o contrato de tradução.
