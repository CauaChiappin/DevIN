/**
 * Regressões do código Konami: erros reiniciam silenciosamente, sem bloquear
 * setas ou teclado de formulários. Executa o script real com DOM simulado;
 * apenas fetch usa resposta falsa para não depender do servidor remoto.
 */
const test = require('node:test'); // Explicação DevIN: Registra cenários independentes de comportamento.
const assert = require('node:assert/strict'); // Explicação DevIN: Compara efeitos observáveis sem coerção.
const fs = require('node:fs'); // Explicação DevIN: Lê o JavaScript versionado em vez de duplicar sua lógica.
const path = require('node:path'); // Explicação DevIN: Resolve o caminho relativo do projeto.
const vm = require('node:vm'); // Explicação DevIN: Executa o navegador mínimo do teste em sandbox.

const sequence = ['ArrowUp','ArrowUp','ArrowDown','ArrowDown','ArrowLeft','ArrowRight','ArrowLeft','ArrowRight','KeyB','KeyA','Enter']; // Explicação DevIN: Sequência pública do Konami que deve continuar funcionando.
function browser() { // Explicação DevIN: Monta o ambiente isolado para observar teclado e navegação.
    const listeners = {}; // Explicação DevIN: Armazena o listener de tecla do programa real.
    const notifications = []; // Explicação DevIN: Detecta qualquer notificação DOM indevida ao errar.
    const requests = []; // Explicação DevIN: Registra os endpoints acionados após sequência completa.
    const redirects = []; // Explicação DevIN: Captura navegação sem acessar site de produção.
    class HTMLElement { constructor(tagName='DIV', editable=false) { this.tagName=tagName; this.isContentEditable=editable; } } // Explicação DevIN: Reproduz a verificação de alvo editável.
    const context = { // Explicação DevIN: Só oferece as APIs exigidas pelo código testado.
        HTMLElement, // Explicação DevIN: Permite identificar campos reais simulados por instanceof.
        document: { // Explicação DevIN: Representa a página e seus elementos.
            addEventListener(name, callback) { listeners[name] = callback; }, // Explicação DevIN: Captura eventos keydown e de foco.
            querySelector() { return null; }, // Explicação DevIN: Nunca existe notificação prévia.
            createElement() { return {dataset:{},setAttribute(){},className:'',textContent:'',hidden:true}; }, // Explicação DevIN: Permite detectar criação de notificações.
            body: {appendChild(item) { notifications.push(item); }}, // Explicação DevIN: Registra qualquer mensagem exibida.
            hidden: false, // Explicação DevIN: Começa com a página visível.
        }, // Explicação DevIN: Encerra a página de testes.
        window: { location: { assign(url) { redirects.push(url); } }, addEventListener(name,fn) { listeners[name]=fn; } }, // Explicação DevIN: Registra destino e eventos de janela.
        fetch: async url => { // Explicação DevIN: Simula somente a API HTTP de sessão e desbloqueio.
            requests.push(url); // Explicação DevIN: Conta quantas requisições ocorreram.
            return {ok:true, json:async()=>url.includes('csrf.php') ? {sucesso:true,token:'local-test'} : {sucesso:true,url:'/php/arcade.php'}}; // Explicação DevIN: Mantém o contrato das respostas do PHP.
        }, // Explicação DevIN: Finaliza o substituto de rede.
        URLSearchParams, // Explicação DevIN: Codifica o POST do script sem modificação.
        JSON, // Explicação DevIN: Serializa a combinação correta para o backend.
        Error, // Explicação DevIN: Permite exceções JavaScript sem efeitos extras.
    }; // Explicação DevIN: Finaliza o contexto.
    vm.runInNewContext(fs.readFileSync(path.resolve(__dirname,'../js/konami-arcade.js'),'utf8'), context); // Explicação DevIN: Executa o arquivo real do Konami.
    function press(code,target=new HTMLElement()) { // Explicação DevIN: Simula o efeito público de pressionar uma tecla.
        let prevented=false; // Explicação DevIN: Inicia sem bloqueio do comportamento padrão.
        listeners.keydown({code,target,repeat:false,preventDefault(){prevented=true;}}); // Explicação DevIN: Envia evento diretamente ao listener instalado.
        return prevented; // Explicação DevIN: Informa se a navegação normal do teclado foi bloqueada.
    } // Explicação DevIN: Finaliza o auxiliar de interação.
    return {press,HTMLElement,requests,redirects,notifications}; // Explicação DevIN: Expõe somente os resultados observáveis do script.
} // Explicação DevIN: Finaliza a construção do ambiente falso.

test('errar uma tecla reinicia sem banner, rede ou bloqueio de setas', () => { // Explicação DevIN: Erro de sequência nunca afeta site normal.
    const env=browser(); // Explicação DevIN: Isola uma tentativa.
    assert.equal(env.press('ArrowUp'),false); // Explicação DevIN: A primeira seta também pode rolar a página normalmente.
    assert.equal(env.press('ArrowUp'),false); // Explicação DevIN: A segunda seta também não é capturada pelo Arcade.
    assert.equal(env.press('ArrowLeft'),false); // Explicação DevIN: Uma seta errada cancela silenciosamente a tentativa.
    for (const code of sequence.slice(2)) env.press(code); // Explicação DevIN: Continuação da sequência parcial não deve desbloquear.
    assert.equal(env.requests.length,0); // Explicação DevIN: Não acessa a API quando a sequência contém erro.
    assert.equal(env.notifications.length,0); // Explicação DevIN: Não exibe aviso de erro nem notificação.
}); // Explicação DevIN: Encerra o teste de reset silencioso.

test('após erro, uma nova sequência correta funciona sem recarregar a página', async () => { // Explicação DevIN: O teclado continua utilizável após tentativa inválida.
    const env=browser(); // Explicação DevIN: Cria sessão isolada.
    env.press('ArrowUp'); env.press('KeyX'); // Explicação DevIN: Inicia e erra de propósito.
    for (const code of sequence.slice(0,-1)) assert.equal(env.press(code),false); // Explicação DevIN: Nenhuma tecla intermediária bloqueia navegação.
    assert.equal(env.press('Enter'),true); // Explicação DevIN: Apenas Enter final é capturado para evitar enviar formulário.
    await new Promise(resolve=>setImmediate(resolve)); // Explicação DevIN: Aguarda a operação assíncrona real do JavaScript.
    assert.equal(env.requests.length,2); // Explicação DevIN: CSRF e desbloqueio são enviados uma única vez.
    assert.equal(env.redirects.length,1); // Explicação DevIN: Uma combinação correta abre o Arcade.
    assert.equal(env.notifications.length,0); // Explicação DevIN: Não mostra banner antes de navegar.
}); // Explicação DevIN: Encerra o teste de recomeço.

test('digitar num campo durante a sequência cancela o progresso anterior', () => { // Explicação DevIN: Interação com formulário não pode ficar presa no estado parcial.
    const env=browser(); // Explicação DevIN: Cria navegador isolado.
    env.press('ArrowUp'); env.press('ArrowUp'); // Explicação DevIN: Avança duas teclas fora do formulário.
    env.press('KeyX',new env.HTMLElement('INPUT')); // Explicação DevIN: Digita em um input e interrompe o código.
    for (const code of sequence.slice(2)) env.press(code); // Explicação DevIN: Tenta completar apenas o restante, o que não deve funcionar.
    assert.equal(env.requests.length,0); // Explicação DevIN: O estado parcial não causa desbloqueio inesperado.
    assert.equal(env.notifications.length,0); // Explicação DevIN: Não mostra mensagem sobre o reset.
}); // Explicação DevIN: Encerra o teste de entrada de texto.
