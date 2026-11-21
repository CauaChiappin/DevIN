/**
 * Regressão ADM: cada área exibe apenas os registros de sua responsabilidade.
 * Analisa o PHP versionado; não executa exclusões nem acessa a Hostinger.
 */
const test = require('node:test'); // Explicação DevIN: Declara variável JavaScript test para reutilizar valor ou resultado.
const assert = require('node:assert/strict'); // Explicação DevIN: Declara variável JavaScript assert para reutilizar valor ou resultado.
const fs = require('node:fs'); // Explicação DevIN: Declara variável JavaScript fs para reutilizar valor ou resultado.
const path = require('node:path'); // Explicação DevIN: Declara variável JavaScript path para reutilizar valor ou resultado.
const read = name => fs.readFileSync(path.join(__dirname, '..', name), 'utf8'); // Explicação DevIN: Declara variável JavaScript read para reutilizar valor ou resultado.
const adm = read('php/adm.php'); // Explicação DevIN: Declara variável JavaScript adm para reutilizar valor ou resultado.

test('Empresas mostra somente contas empresariais, sem vagas duplicadas', () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    const begin = adm.indexOf('<?php if (!$empresasAdmin): ?>'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    const end = adm.indexOf("<?php if ($pagina === 'sobre'): ?>", begin); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    assert.ok(begin > 0 && end > begin, 'Não encontrou a seção Empresas'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    const block = adm.slice(begin, end); // Explicação DevIN: Declara variável JavaScript block para reutilizar valor ou resultado.
    assert.match(block, /foreach \(\$empresasAdmin as \$empresa\)/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.doesNotMatch(block, /foreach \(\$vagasAdmin as \$vaga\)/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.doesNotMatch(block, /name="action" value="admin_delete_vaga"/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(block, /Nenhuma empresa cadastrada\./); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.doesNotMatch(adm, /count\(\$empresasAdmin\) \+ count\(\$vagasAdmin\)/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(adm, /count\(\$empresasAdmin\) \. ' empresas'/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('Vagas permanece como aba exclusiva e consulta sem misturar empresas', () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.equal((adm.match(/foreach \(\$vagasAdmin as \$vaga\)/g) || []).length, 1); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(adm, /if \(\$pagina === 'posts'\) \{/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(adm, /\$pagina === 'posts' \? 'Vagas'/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(adm, /href="adm\.php\?pagina=posts"/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(adm, /count\(\$vagasAdmin\) \. ' vagas'/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(adm, /Nenhuma vaga publicada\./); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('exclusão redireciona para a seção correspondente em sucesso e erro', () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.equal(adm.split("($action === 'admin_delete_empresa' ? 'empresas' : 'candidatos')").length - 1, 2); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(adm, /'admin_delete_empresa'\s*=> \['empresa', 'id_empresa'\]/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    assert.match(adm, /'admin_delete_vaga'\s*=> \['vagas', 'id_vaga'\]/); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

test('buscas e estados vazios continuam em português, inglês e espanhol', () => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    const locale = read('js/dashboard-i18n.js'); // Explicação DevIN: Declara variável JavaScript locale para reutilizar valor ou resultado.
    for (const value of ['Pesquisar empresas por nome ou e-mail...', 'Pesquisar vagas por título ou empresa...', 'Nenhuma empresa cadastrada.']) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
        assert.equal(locale.split("'"+value+"'").length - 1, 2); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        assert.ok(adm.includes(value), 'Texto não usado pelo painel: ' + value); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
}); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
