/**
 * Testes do atalho secreto para dispositivos touch dentro de "Sobre nós".
 * O navegador é simulado para provar que mouse, arrasto ou toque curto não
 * abrem o Arcade, enquanto pressão de três segundos mantém o Konami desktop.
 */
const test = require('node:test'); // Explicação DevIN: Usa o executor de testes incluído no Node.js.
const assert = require('node:assert/strict'); // Explicação DevIN: Verifica condições sem conversão implícita de tipos.
const fs = require('node:fs'); // Explicação DevIN: Lê os arquivos versionados no repositório.
const path = require('node:path'); // Explicação DevIN: Resolve caminhos independentes do sistema operacional.
const vm = require('node:vm'); // Explicação DevIN: Executa o código de UI em ambiente de navegador simulado.
const root = path.resolve(__dirname, '..'); // Explicação DevIN: Usa o diretório do projeto como base comum.
const read = file => fs.readFileSync(path.join(root, file), 'utf8'); // Explicação DevIN: Carrega os arquivos reais, não cópias dos testes.

function simulate(touch = true) { // Explicação DevIN: Constrói ambiente isolado com eventos e relógio determinístico.
    const handlers = {}; // Explicação DevIN: Guarda os eventos registrados no botão oculto.
    const timers = new Map(); // Explicação DevIN: Permite disparar ou cancelar os tempos sem esperar na CI.
    const classes = new Set(); // Explicação DevIN: Representa os estados visuais do botão.
    const redirects = []; // Explicação DevIN: Registra possíveis navegações sem abrir navegador real.
    let nextTimer = 1; // Explicação DevIN: Cria identificadores diferentes para cada agendamento.
    const button = { // Explicação DevIN: Representa somente as APIs DOM utilizadas pelo módulo.
        addEventListener(name, callback) { handlers[name] = callback; }, // Explicação DevIN: Permite invocar os gestos nos cenários simulados.
        classList: { add(name) { classes.add(name); }, remove(name) { classes.delete(name); } }, // Explicação DevIN: Controla a aparência do botão sem precisar de HTML real.
        setAttribute() {}, // Explicação DevIN: Simula a atualização do rótulo acessível.
    }; // Explicação DevIN: Finaliza a estrutura do elemento de teste.
    const documentListeners = {}; // Explicação DevIN: Armazena observadores de mudança de aba.
    const windowListeners = {}; // Explicação DevIN: Armazena observadores de rolagem e perda de foco.
    const context = { // Explicação DevIN: Exibe ao código somente APIs do navegador necessárias ao teste.
        document: { // Explicação DevIN: Implementa consulta ao botão e eventos de visibilidade.
            querySelector: selector => selector === '[data-mobile-arcade-secret]' ? button : null, // Explicação DevIN: Entrega o botão apenas no seletor esperado.
            addEventListener: (name, callback) => { documentListeners[name] = callback; }, // Explicação DevIN: Registra controle de segundo plano.
            hidden: false, // Explicação DevIN: Mantém a aba inicialmente visível.
        }, // Explicação DevIN: Fecha o documento falso.
        window: { // Explicação DevIN: Simula APIs do dispositivo mobile e navegação.
            matchMedia: () => ({ matches: touch }), // Explicação DevIN: Alterna entre tela touch e desktop com mouse.
            setTimeout: (fn, ms) => { const id = nextTimer++; timers.set(id, { fn, ms }); return id; }, // Explicação DevIN: Programa eventos sem usar relógio real.
            clearTimeout: id => timers.delete(id), // Explicação DevIN: Permite que pointerup e arrasto interrompam o segredo.
            addEventListener: (name, callback) => { windowListeners[name] = callback; }, // Explicação DevIN: Registra evento de scroll e blur.
            location: { href: 'https://example.org/Trabalhos/3D/DevIN/php/pessoa.php?pagina=sobre', assign: url => redirects.push(url) }, // Explicação DevIN: Verifica destino sem fazer requisições externas.
        }, // Explicação DevIN: Fecha a janela simulada.
        navigator: { vibrate() {} }, // Explicação DevIN: Evita exigir que o aparelho suporte vibração.
        URL, // Explicação DevIN: Resolve a URL relativa como um navegador real.
        Math, // Explicação DevIN: Suporta a tolerância de deslocamento do toque.
    }; // Explicação DevIN: Finaliza o ambiente de execução simulado.
    vm.runInNewContext(read('js/mobile-arcade-secret.js'), context); // Explicação DevIN: Executa o módulo real e registra seus listeners.
    return { // Explicação DevIN: Fornece controles aos testes de navegação e cancelamento.
        handlers, timers, classes, redirects, documentListeners, windowListeners, context, // Explicação DevIN: Expõe o estado observável do ambiente falso.
        advance(ms) { // Explicação DevIN: Dispara somente o timer solicitado pela duração prevista.
            const hit = [...timers.entries()].find(([, value]) => value.ms === ms); // Explicação DevIN: Localiza o temporizador de pressão ou navegação.
            assert.ok(hit, 'temporizador esperado: ' + ms); // Explicação DevIN: Falha quando não existe o timer exigido pelo cenário.
            timers.delete(hit[0]); // Explicação DevIN: Remove o timer para impedir execução duplicada.
            hit[1].fn(); // Explicação DevIN: Executa o callback no instante simulado.
        }, // Explicação DevIN: Fecha o avanço determinístico do relógio.
    }; // Explicação DevIN: Finaliza os auxiliares dos testes.
} // Explicação DevIN: Fecha o simulador de navegador.

const down = (pointerType = 'touch') => ({ pointerId: 9, pointerType, button: 0, clientX: 100, clientY: 100 }); // Explicação DevIN: Produz um contato consistente para os testes de pressão.

test('o segredo fica no card Time fundador e carrega nos três painéis', () => { // Explicação DevIN: Verifica a posição e a inclusão do módulo compartilhado.
    const about = read('php/helpers.php'); // Explicação DevIN: Abre a fonte comum da seção institucional.
    assert.match(about, /sobre-team-card[\s\S]*data-mobile-arcade-secret[\s\S]*<\/section>/); // Explicação DevIN: O ícone fica dentro do card de fundadores.
    for (const page of ['pessoa', 'empresa', 'adm']) { // Explicação DevIN: Testa as três classes de usuário.
        const source = read('php/' + page + '.php'); // Explicação DevIN: Carrega o dashboard correspondente.
        assert.match(source, /mobile-arcade-secret\.js/); // Explicação DevIN: Todos compartilham o mesmo gesto de acesso.
        assert.match(source, /konami-arcade\.js/); // Explicação DevIN: Nenhuma alteração desativa o Konami de desktop.
    } // Explicação DevIN: Finaliza checagem de inclusão nos painéis.
}); // Explicação DevIN: Encerra teste de posicionamento e compatibilidade.

test('um toque contínuo de 3000ms abre a rota autenticada do Arcade', () => { // Explicação DevIN: Garante a duração e o destino esperado.
    const env = simulate(true); // Explicação DevIN: Liga capacidade de toque do navegador.
    env.handlers.pointerdown(down()); // Explicação DevIN: Inicia contato no botão discreto.
    assert.ok(env.classes.has('is-holding')); // Explicação DevIN: Feedback de pressão aparece durante o gesto.
    env.advance(3000); // Explicação DevIN: Completa os três segundos exigidos.
    assert.deepEqual(env.redirects, []); // Explicação DevIN: O navegador ainda deve mostrar feedback breve da descoberta.
    env.advance(260); // Explicação DevIN: Completa o tempo curto de feedback.
    assert.equal(env.redirects.length, 1); // Explicação DevIN: Só uma navegação é permitida.
    assert.ok(env.redirects[0].endsWith('/php/arcade.php')); // Explicação DevIN: A rota respeita a pasta atual do DevIN.
}); // Explicação DevIN: Encerra o teste de ativação prolongada.

test('toque curto, movimento de rolagem e segundo plano cancelam o segredo', () => { // Explicação DevIN: Evita abertura acidental no uso normal.
    const env = simulate(true); // Explicação DevIN: Utiliza um celular simulado.
    env.handlers.pointerdown(down()); // Explicação DevIN: Começa uma pressão.
    env.handlers.pointerup(down()); // Explicação DevIN: Solta antes do prazo necessário.
    assert.equal(env.timers.size, 0); // Explicação DevIN: Nenhum temporizador pode continuar ativo.
    env.handlers.pointerdown(down()); // Explicação DevIN: Inicia uma nova tentativa.
    env.handlers.pointermove({ pointerId: 9, clientX: 120, clientY: 100 }); // Explicação DevIN: Arrasta além da margem de tolerância.
    assert.equal(env.timers.size, 0); // Explicação DevIN: A intenção de rolar cancela a navegação.
    env.handlers.pointerdown(down()); // Explicação DevIN: Reinicia uma tentativa válida.
    env.windowListeners.scroll(); // Explicação DevIN: Simula rolagem real do navegador.
    assert.equal(env.timers.size, 0); // Explicação DevIN: Rolagem também interrompe o botão.
    env.handlers.pointerdown(down()); // Explicação DevIN: Testa interrupção ao ocultar a aba.
    env.context.document.hidden = true; // Explicação DevIN: Marca documento como não visível.
    env.documentListeners.visibilitychange(); // Explicação DevIN: Simula troca de aplicativo.
    assert.equal(env.timers.size, 0); // Explicação DevIN: Não há abertura tardia após voltar.
    assert.equal(env.redirects.length, 0); // Explicação DevIN: Nenhuma interrupção pode navegar.
}); // Explicação DevIN: Finaliza a verificação de cancelamento.

test('mouse de desktop nunca agenda o segredo e CSS oculta o botão', () => { // Explicação DevIN: Preserva comportamento exclusivo de celular.
    const env = simulate(false); // Explicação DevIN: Representa mouse e tela sem toque.
    env.handlers.pointerdown(down('mouse')); // Explicação DevIN: Simula clique no controle via mouse.
    assert.equal(env.timers.size, 0); // Explicação DevIN: Nenhum agendamento é registrado no desktop.
    const css = read('css/dashboard.css'); // Explicação DevIN: Confere os estilos compartilhados.
    assert.match(css, /\.sobre-secret-arcade\s*\{\s*display:\s*none/); // Explicação DevIN: O botão começa oculto por padrão.
    assert.match(css, /@media\s*\(hover:\s*none\)\s*and\s*\(pointer:\s*coarse\)/); // Explicação DevIN: Só dispositivos touch enxergam o segredo.
    assert.match(css, /\.menu-principal\s+\.menu-arcade\s*\{\s*display:\s*none/); // Explicação DevIN: O link comum não entrega a surpresa no mobile.
}); // Explicação DevIN: Encerra teste de exclusividade para touch.
