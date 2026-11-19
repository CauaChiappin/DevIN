/**
 * Contratos da tela de indicadores administrativos.
 * Verifica isolamento do ADM e consultas somente de leitura.
 */
const test = require('node:test'); // Explicação DevIN: Inicializa testes automatizados do Node.
const assert = require('node:assert/strict'); // Explicação DevIN: Disponibiliza comparações estritas.
const fs = require('node:fs'); // Explicação DevIN: Lê código versionado.
const path = require('node:path'); // Explicação DevIN: Resolve caminho do repositório.
const read = name => fs.readFileSync(path.join(__dirname, '..', name), 'utf8'); // Explicação DevIN: Centraliza leitura dos arquivos testados.
test('Estatísticas possui rota ADM e não exige acesso público', () => { // Explicação DevIN: Garante que a tela esteja sob a autenticação administrativa.
    const adm = read('php/adm.php'); // Explicação DevIN: Analisa a página real do ADM.
    assert.match(adm, /requireWebAuth\('adm'\)/); // Explicação DevIN: Exige o controle de acesso existente.
    assert.match(adm, /'estatisticas'/); // Explicação DevIN: Confere rota permitida.
    assert.match(adm, /href="adm\.php\?pagina=estatisticas"/); // Explicação DevIN: Confere menu acessível.
    assert.match(adm, /components\/admin-overview\.php/); // Explicação DevIN: Exige componente dos indicadores.
    assert.match(adm, /\$pagina === 'estatisticas'\)/); // Explicação DevIN: Confere comportamento da rota.
}); // Explicação DevIN: Encerra teste de acesso e navegação.
test('Métricas são agregadas e permanecem somente de leitura', () => { // Explicação DevIN: Protege a visão administrativa contra alterações involuntárias.
    const sql = read('php/controllers/AdminOverview.php'); // Explicação DevIN: Inspeciona consultas do módulo.
    assert.match(sql, /function devinAdminOverview\(mysqli \$db\)/); // Explicação DevIN: Confere API agregada.
    for (const table of ['empresa', 'pessoa', 'vagas', 'candidatura', 'devin_employment_confirmations', 'reports_vagas', 'reports_perfis', 'support_tickets']) { // Explicação DevIN: Percorre fontes de métricas.
        assert.ok(sql.includes('FROM ' + table), 'Fonte ausente: ' + table); // Explicação DevIN: Confere coleta de métricas.
    } // Explicação DevIN: Finaliza inspeção de tabelas.
    assert.doesNotMatch(sql, /\b(DELETE FROM|INSERT INTO|UPDATE\s+\w+\s+SET|DROP TABLE|TRUNCATE)\b/i); // Explicação DevIN: Não permite gravações acidentais na camada agregada.
    assert.match(sql, /\$result\[\$key\] = null/); // Explicação DevIN: Não disfarça tabela ausente com valor zero.
    const view = read('php/components/admin-overview.php'); // Explicação DevIN: Inspeciona o template de métricas.
    assert.match(view, /\['pt-BR'\]|'pt-BR'\s*=>/); // Explicação DevIN: Exige português como idioma base.
    assert.match(view, /'en'\s*=>/); // Explicação DevIN: Exige tradução para inglês.
    assert.match(view, /'es'\s*=>/); // Explicação DevIN: Exige tradução para espanhol.
}); // Explicação DevIN: Encerra testes de leitura.
test('Layout de métricas se adapta aos dispositivos', () => { // Explicação DevIN: Protege o padrão responsivo.
    const css = read('css/ui-consistency.css'); // Explicação DevIN: Lê estilos compartilhados.
    assert.match(css, /\.dashboard-shell\.page-estatisticas\s*\{/); // Explicação DevIN: Exige grade sem coluna de detalhes.
    assert.match(css, /\.admin-overview-grid/); // Explicação DevIN: Exige cartões responsivos.
    assert.match(css, /:not\(\.page-estatisticas\)/); // Explicação DevIN: Evita substituição por grade de três colunas em TVs.
}); // Explicação DevIN: Finaliza contrato da interface.
