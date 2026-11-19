/**
 * Contratos da tela de indicadores administrativos.
 * Verifica isolamento do ADM e consultas somente de leitura.
 */
const test = require('node:test'); // Explicação DevIN: Declara variável JavaScript test para reutilizar valor ou resultado.
const assert = require('node:assert/strict'); // Explicação DevIN: Declara variável JavaScript assert para reutilizar valor ou resultado.
const fs = require('node:fs'); // Explicação DevIN: Declara variável JavaScript fs para reutilizar valor ou resultado.
const path = require('node:path'); // Explicação DevIN: Declara variável JavaScript path para reutilizar valor ou resultado.
const read = name => fs.readFileSync(path.join(__dirname, '..', name), 'utf8'); // Explicação DevIN: Declara variável JavaScript read para reutilizar valor ou resultado.
test('Estatísticas possui rota ADM e não exige acesso público', () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    const adm = read('php/adm.php'); // Explicação DevIN: Declara variável JavaScript adm para reutilizar valor ou resultado.
    assert.match(adm, /requireWebAuth\('adm'\)/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(adm, /'estatisticas'/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(adm, /href="adm\.php\?pagina=estatisticas"/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(adm, /components\/admin-overview\.php/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(adm, /\$pagina === 'estatisticas'\)/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
test('Métricas são agregadas e permanecem somente de leitura', () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    const sql = read('php/controllers/AdminOverview.php'); // Explicação DevIN: Declara variável JavaScript sql para reutilizar valor ou resultado.
    assert.match(sql, /function devinAdminOverview\(mysqli \$db\)/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    for (const table of ['empresa', 'pessoa', 'vagas', 'candidatura', 'devin_employment_confirmations', 'reports_vagas', 'reports_perfis', 'support_tickets']) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
        assert.ok(sql.includes('FROM ' + table), 'Fonte ausente: ' + table); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    assert.doesNotMatch(sql, /\b(DELETE FROM|INSERT INTO|UPDATE\s+\w+\s+SET|DROP TABLE|TRUNCATE)\b/i); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(sql, /\$result\[\$key\] = null/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    const view = read('php/components/admin-overview.php'); // Explicação DevIN: Declara variável JavaScript view para reutilizar valor ou resultado.
    assert.match(view, /\['pt-BR'\]|'pt-BR'\s*=>/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(view, /'en'\s*=>/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(view, /'es'\s*=>/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
test('Layout de métricas se adapta aos dispositivos', () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    const css = read('css/ui-consistency.css'); // Explicação DevIN: Declara variável JavaScript css para reutilizar valor ou resultado.
    assert.match(css, /\.dashboard-shell\.page-estatisticas\s*\{/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(css, /\.admin-overview-grid/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(css, /\.dashboard-shell\.page-estatisticas \{ grid-template-columns: var\(--sidebar-width\) minmax\(0, 1fr\); \}/); // Explicação DevIN: Mantém estatísticas ADM em duas colunas sem depender das regras de TV.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
