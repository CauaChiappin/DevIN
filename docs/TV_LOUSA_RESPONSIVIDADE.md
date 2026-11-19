# DevIN — correção de regressão da responsividade de TVs

Em 19/11/2026, após relato de distorção da interface em computadores e monitores grandes, **as regras globais de Full HD, 4K e touch introduzidas na revisão para TVs foram revertidas**.

## Causa

A revisão anterior havia criado media queries em `css/ui-consistency.css` a partir de 1600 × 800, 2400 × 1000 e 1024 pixels com ponteiro de toque. Estas regras modificavam diretamente `grid-template-columns`, `--sidebar-width`, alturas de cards e tipografia em páginas que já tinham layout definido em `css/dashboard.css`. Como desktops comuns também atendem a esses breakpoints, a revisão impactou Pessoa, Empresa, ADM e outras áreas fora do alvo pretendido.

Além disso, o controle da sidebar em `js/dashboard.js` mantinha uma constante `fechado` com o valor antigo de `localStorage`: ao clicar no menu e redimensionar a janela, o estado podia voltar ao valor anterior.

## Correção aplicada

- Removido **todo** o bloco de regras de TV, 4K e lousas de `css/ui-consistency.css`. As regras de busca, filtros, mobile e indicadores ADM, feitas em outras revisões, permanecem.
- A grade normal do dashboard, os menus de Pessoa/Empresa/ADM, o chat, o suporte e as páginas públicas voltam a usar os estilos de origem, sem ampliação global.
- Corrigido `js/dashboard.js` para atualizar o valor `fechado` sempre que o usuário alterna a sidebar.
- O menu é expandido **uma vez** por navegador após esta atualização para reparar o estado colapsado da regressão. A partir daí, novas preferências continuam sendo salvas normalmente.
- Contratos automatizados foram atualizados para **proibir** a reintrodução de regras globais de TV que sobrescrevam os layouts desktop.

## Publicação e teste

Esta correção precisa ser enviada da `main` à Hostinger; atualizar somente o GitHub não altera o site online.

Depois do deploy, recarregue a página sem cache (Ctrl + F5) e confira os dashboards de Pessoa, Empresa e ADM nos tamanhos 1366 × 768, 1600 × 900, 1920 × 1080 e 2560 × 1440, inclusive com o menu aberto/recolhido e após redimensionar. Teste também tela de estatísticas, Sobre, Currículo, Chat, Suporte, login e cadastro.

Sem captura automatizada ou teste em navegador real, os testes de código não garantem que todas as resoluções estejam visualmente perfeitas.

**Não é necessário executar SQL ou modificar o `.env` para esta correção.**
