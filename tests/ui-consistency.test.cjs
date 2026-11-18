/**
 * Contratos de layout DevIN para páginas públicas, pessoa, empresa, ADM e Arcade.
 * Estes testes verificam a integração de CSS, cabeçalho, busca e filtros.
 * Não substituem teste visual com navegadores reais e diferentes tamanhos de tela.
 */
const test = require('node:test'); // Explicação DevIN: Usa os testes nativos do Node.
const assert = require('node:assert/strict'); // Explicação DevIN: Exige condições exatas.
const fs = require('node:fs'); // Explicação DevIN: Permite inspecionar arquivos do repositório.
const path = require('node:path'); // Explicação DevIN: Resolve caminhos portáveis.
const root = path.resolve(__dirname, '..'); // Explicação DevIN: Aponta para a raiz do DevIN.
const read = file => fs.readFileSync(path.join(root, file), 'utf8'); // Explicação DevIN: Lê o conteúdo versionado para testes de contrato.
const dashboards = ['php/pessoa.php', 'php/empresa.php', 'php/adm.php']; // Explicação DevIN: Define os três perfis autenticados.
const publicPages = ['php/login.php', 'php/cadastro_pessoa.php', 'php/cadastro_empresa.php', 'php/arcade.php', 'php/curriculo_abnt.php', 'php/recuperacao.php', 'php/redefinir.php', 'php/curriculo_onboarding.php']; // Explicação DevIN: Abrange as páginas PHP principais.
const htmlPages = ['html/index.html', 'html/login.html', 'html/cadastro_pessoa.html', 'html/cadastro_empresa.html', 'html/politica_privacidade.html']; // Explicação DevIN: Abrange as entradas HTML legadas.

test('páginas principais compartilham camada visual e viewport móvel', () => { // Explicação DevIN: Evita estilos isolados sem atualização responsiva.
    for (const file of [...dashboards, ...publicPages, ...htmlPages]) { // Explicação DevIN: Percorre as telas críticas.
        const markup = read(file); // Explicação DevIN: Lê o documento real.
        assert.match(markup, /ui-consistency\.css/, file + ' sem CSS comum'); // Explicação DevIN: Exige uma fonte de estilo compartilhada.
        assert.match(markup, /name=["']viewport["']/, file + ' sem viewport'); // Explicação DevIN: Confere layout correto no celular.
        assert.match(markup, /<\/head>/, file + ' sem cabeçalho HTML'); // Explicação DevIN: Assegura estrutura da página.
    } // Explicação DevIN: Finaliza inspeção de todas as entradas.
}); // Explicação DevIN: Encerra teste de cobertura.

test('busca e filtros do dashboard oferecem painel acessível, limpeza e resultados', () => { // Explicação DevIN: Reusa o cabeçalho mais completo de empresa.
    const helper = read('php/helpers.php'); // Explicação DevIN: Inspeciona a saída compartilhada.
    const js = read('js/dashboard.js'); // Explicação DevIN: Inspeciona a interação em todos os perfis.
    assert.match(helper, /role="search"/); // Explicação DevIN: Campo de busca com semântica própria.
    assert.match(helper, /data-toggle-filters aria-controls=/); // Explicação DevIN: Associa botão ao painel.
    assert.match(helper, /data-reset-filters disabled/); // Explicação DevIN: Limpa filtros sem gravar dados no banco.
    assert.match(helper, /data-visible-results role="status" aria-live="polite"/); // Explicação DevIN: Anuncia contagem para leitores de tela.
    assert.match(js, /setFilterMenuOpen/); // Explicação DevIN: Uma função controla estados visuais.
    assert.match(js, /event\.key !== 'Escape'/); // Explicação DevIN: Pode fechar filtros pelo teclado.
    assert.match(js, /addEventListener\('pointerdown'/); // Explicação DevIN: Pode fechar filtros ao tocar fora.
    assert.match(js, /resetFiltersButton\?\.addEventListener\('click'/); // Explicação DevIN: Implementa limpeza pelo botão.
    assert.match(js, /localCandidateFilterActive/); // Explicação DevIN: Limpeza abrange os filtros internos de candidatos.
    assert.match(js, /resultLive\.textContent/); // Explicação DevIN: Contador muda com os resultados da busca.
    for (const file of dashboards) { // Explicação DevIN: Verifica o cabeçalho em cada tipo de conta.
        assert.match(read(file), /dashboardListHeader\(/, file + ' sem cabeçalho comum'); // Explicação DevIN: Mantém padronização central.
    } // Explicação DevIN: Finaliza a inspeção dos perfis.
}); // Explicação DevIN: Encerra teste de filtros.

test('camada responsiva preserva campos sem scroll lateral e estados ocultos', () => { // Explicação DevIN: Verifica regras essenciais para a viewport.
    const css = read('css/ui-consistency.css'); // Explicação DevIN: Inspeciona estilo compartilhado.
    assert.match(css, /--devin-ui-accent/); // Explicação DevIN: Mantém identidade azul do DevIN.
    assert.match(css, /max-width: 100%/); // Explicação DevIN: Impede expansão de controles além do contêiner.
    assert.match(css, /\[hidden\]\s*\{ display: none !important; \}/); // Explicação DevIN: Mantém histórico e menus fechados no CSS.
    assert.match(css, /max-width: 720px/); // Explicação DevIN: Verifica o breakpoint principal de celular.
    assert.match(css, /max-width: 400px/); // Explicação DevIN: Verifica celular de largura reduzida.
    assert.match(css, /font-size: 16px/); // Explicação DevIN: Evita zoom automático em campos.
    assert.match(css, /prefers-reduced-motion/); // Explicação DevIN: Respeita preferência por movimento reduzido.
    assert.match(css, /chat-app/); // Explicação DevIN: Inclui mensagens.
    assert.match(css, /support-main/); // Explicação DevIN: Inclui Suporte de usuário.
    assert.match(css, /support-admin-view/); // Explicação DevIN: Inclui Suporte ADM.
    assert.match(css, /arcade-portal/); // Explicação DevIN: Inclui busca e categorias dos jogos.
    assert.match(css, /candidate-filters/); // Explicação DevIN: Inclui filtros ricos da empresa.
}); // Explicação DevIN: Encerra teste dos estilos.
