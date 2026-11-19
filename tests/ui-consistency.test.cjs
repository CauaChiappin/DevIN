/**
 * Contratos de layout DevIN para páginas públicas, pessoa, empresa, ADM e Arcade.
 * Estes testes verificam a integração de CSS, cabeçalho, busca e filtros.
 * Não substituem teste visual com navegadores reais e diferentes tamanhos de tela.
 */
const test = require('node:test'); // Explicação DevIN: Declara variável JavaScript test para reutilizar valor ou resultado.
const assert = require('node:assert/strict'); // Explicação DevIN: Declara variável JavaScript assert para reutilizar valor ou resultado.
const fs = require('node:fs'); // Explicação DevIN: Declara variável JavaScript fs para reutilizar valor ou resultado.
const path = require('node:path'); // Explicação DevIN: Declara variável JavaScript path para reutilizar valor ou resultado.
const root = path.resolve(__dirname, '..'); // Explicação DevIN: Declara variável JavaScript root para reutilizar valor ou resultado.
const read = file => fs.readFileSync(path.join(root, file), 'utf8'); // Explicação DevIN: Declara variável JavaScript read para reutilizar valor ou resultado.
const dashboards = ['php/pessoa.php', 'php/empresa.php', 'php/adm.php']; // Explicação DevIN: Declara variável JavaScript dashboards para reutilizar valor ou resultado.
const publicPages = ['php/login.php', 'php/cadastro_pessoa.php', 'php/cadastro_empresa.php', 'php/arcade.php', 'php/curriculo_abnt.php', 'php/recuperacao.php', 'php/redefinir.php', 'php/curriculo_onboarding.php']; // Explicação DevIN: Declara variável JavaScript publicPages para reutilizar valor ou resultado.
const htmlPages = ['html/index.html', 'html/login.html', 'html/cadastro_pessoa.html', 'html/cadastro_empresa.html', 'html/politica_privacidade.html']; // Explicação DevIN: Declara variável JavaScript htmlPages para reutilizar valor ou resultado.

test('páginas principais compartilham camada visual e viewport móvel', () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    for (const file of [...dashboards, ...publicPages, ...htmlPages]) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
        const markup = read(file); // Explicação DevIN: Declara variável JavaScript markup para reutilizar valor ou resultado.
        assert.match(markup, /ui-consistency\.css/, file + ' sem CSS comum'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        assert.match(markup, /name=["']viewport["']/, file + ' sem viewport'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        assert.match(markup, /<\/head>/, file + ' sem cabeçalho HTML'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('busca e filtros do dashboard oferecem painel acessível, limpeza e resultados', () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    const helper = read('php/helpers.php'); // Explicação DevIN: Declara variável JavaScript helper para reutilizar valor ou resultado.
    const js = read('js/dashboard.js'); // Explicação DevIN: Declara variável JavaScript js para reutilizar valor ou resultado.
    assert.match(helper, /role="search"/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(helper, /data-toggle-filters aria-controls=/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(helper, /data-reset-filters disabled/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(helper, /data-visible-results role="status" aria-live="polite"/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(js, /setFilterMenuOpen/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(js, /event\.key !== 'Escape'/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(js, /addEventListener\('pointerdown'/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(js, /resetFiltersButton\?\.addEventListener\('click'/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(js, /localCandidateFilterActive/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(js, /resultLive\.textContent/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    for (const file of dashboards) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
        assert.match(read(file), /dashboardListHeader\(/, file + ' sem cabeçalho comum'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('camada responsiva preserva campos sem scroll lateral e estados ocultos', () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    const css = read('css/ui-consistency.css'); // Explicação DevIN: Declara variável JavaScript css para reutilizar valor ou resultado.
    assert.match(css, /--devin-ui-accent/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(css, /max-width: 100%/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(css, /\[hidden\]\s*\{ display: none !important; \}/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(css, /max-width: 720px/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(css, /max-width: 400px/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(css, /font-size: 16px/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(css, /prefers-reduced-motion/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(css, /chat-app/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(css, /support-main/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(css, /support-admin-view/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(css, /arcade-portal/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(css, /candidate-filters/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('layout desktop usa grade original e preserva painel de estatísticas', () => { // Explicação DevIN: Protege o tamanho padrão dos dashboards em notebooks e PCs.
    const css = read('css/ui-consistency.css'); // Explicação DevIN: Examina a camada compartilhada.
    const dashboardCss = read('css/dashboard.css'); // Explicação DevIN: Examina a folha original do dashboard.
    assert.match(dashboardCss, /--sidebar-width:\s*240px/); // Explicação DevIN: Mantém sidebar desktop original de 240px.
    assert.match(dashboardCss, /--sidebar-width:\s*82px/); // Explicação DevIN: Preserva opção de sidebar compacta quando escolhida.
    assert.match(dashboardCss, /@media \(min-width: 981px\)/); // Explicação DevIN: Mantém distribuição de colunas do computador.
    assert.match(css, /\.dashboard-shell\.page-estatisticas \{ grid-template-columns: var\(--sidebar-width\) minmax\(0, 1fr\); \}/); // Explicação DevIN: Preserva tela especial de métricas ADM.
    assert.doesNotMatch(css, /DEVIN LARGE DISPLAYS 2026/); // Explicação DevIN: Evita reintroduzir estilos que distorceram a tela.
    assert.doesNotMatch(css, /@media screen and \(min-width: 1600px\)/); // Explicação DevIN: Não força escala de TV no desktop comum.
    assert.doesNotMatch(css, /@media screen and \(min-width: 2400px\)/); // Explicação DevIN: Não altera grades de páginas em resolução 4K.
}); // Explicação DevIN: Finaliza contrato de layout original.

test('sidebar mantém o estado após redimensionar e restaura expansão inicial', () => { // Explicação DevIN: Impede reativação involuntária do menu colapsado.
    const script = read('js/dashboard.js'); // Explicação DevIN: Examina o código de interação do menu.
    assert.match(script, /devin-menu-layout-restaurado-20261119/); // Explicação DevIN: Migra uma única vez a preferência de layout anterior.
    assert.match(script, /let fechado = localStorage\.getItem\('devin-menu-fechado'\)/); // Explicação DevIN: Permite atualizar o estado.
    assert.match(script, /fechado = novoEstado/); // Explicação DevIN: Impede que resize volte ao estado anterior ao clique.
    assert.match(script, /dashboardShell\.classList\.toggle\('menu-fechado', fechado\)/); // Explicação DevIN: Usa o valor sincronizado na mudança de viewport.
    assert.match(script, /localStorage\.setItem\('devin-menu-fechado', String\(novoEstado\)\)/); // Explicação DevIN: Preserva novas escolhas depois da correção.
}); // Explicação DevIN: Finaliza teste de estado do menu.

test('ajustes móveis não modificam grades globais nem dependem de lousas', () => { // Explicação DevIN: Preserva correções úteis sem a regressão em monitores.
    const css = read('css/ui-consistency.css'); // Explicação DevIN: Examina regras de campos e responsividade móvel.
    assert.match(css, /@media \(max-width: 720px\)/); // Explicação DevIN: Mantém campos adequados a celulares.
    assert.match(css, /@media \(max-width: 400px\)/); // Explicação DevIN: Mantém suporte a telas estreitas.
    assert.match(css, /\[hidden\] \{ display: none !important; \}/); // Explicação DevIN: Mantém menus ocultos quando fechados.
    assert.doesNotMatch(css, /@media screen and \(min-width: 1024px\) and \(pointer: coarse\)/); // Explicação DevIN: Remove ajustes amplos que afetavam desktops com touchscreen.
}); // Explicação DevIN: Finaliza contrato móvel.
