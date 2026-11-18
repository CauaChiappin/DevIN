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

test('Full HD e 4K sem quebrar as grades especiais dos dashboards', () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    const css = read('css/ui-consistency.css'); // Explicação DevIN: Declara variável JavaScript css para reutilizar valor ou resultado.
    assert.match(css, /@media screen and \(min-width: 1600px\) and \(min-height: 800px\)/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(css, /@media screen and \(min-width: 2400px\) and \(min-height: 1000px\)/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(css, /\.dashboard-shell:not\(\.menu-fechado\)/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(css, /:not\(\.page-mensagens\):not\(\.page-suporte\):not\(\.page-curriculo\)/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(css, /\.dashboard-shell\.page-mensagens \.chat-app/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(css, /\.dashboard-shell\.page-suporte \.support-main/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(css, /\.dashboard-shell \.compact-list-header \.filter-menu/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(css, /\.dashboard-shell \.item-card h2/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(css, /\.arcade-portal \.portal-layout/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(css, /\.jarvis-admin \.jarvis-admin__panel\.is-expanded/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('lousas sensíveis ao toque têm áreas acionáveis e foco legível', () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    const css = read('css/ui-consistency.css'); // Explicação DevIN: Declara variável JavaScript css para reutilizar valor ou resultado.
    assert.match(css, /@media screen and \(min-width: 1024px\) and \(pointer: coarse\)/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(css, /min-height: 56px/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(css, /\.chat-message-menu summary/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(css, /\.jarvis-admin \.jarvis-admin__icon-button/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(css, /touch-action: manipulation/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(css, /:focus-visible \{ outline: 3px solid/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(css, /max-height: min\(72dvh, 760px\)/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
