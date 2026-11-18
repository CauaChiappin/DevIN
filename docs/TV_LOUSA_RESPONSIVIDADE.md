# DevIN — responsividade para TVs e lousas digitais

Revisão de interface de 18/11/2026. A fonte dos ajustes é `css/ui-consistency.css`. Não altera banco, APIs, autenticação ou `.env`.

## Breakpoints

- **A partir de 1600 × 800 pixels CSS:** tipografia, sidebar, cards, campos, chat, suporte e Arcade adequados a telas grandes.
- **A partir de 2400 × 1000 pixels CSS:** distribuição aprimorada de colunas, botões e cards para telas 4K e ultra-wide.
- **A partir de 1024 pixels CSS com `pointer: coarse`:** áreas clicáveis maiores para lousas sensíveis ao toque.

Os pixels CSS dependem de zoom, escala e navegador. Não são uma medição da dimensão física da TV.

## Componentes

| Área | Ajuste | Preservado |
|---|---|---|
| Pessoa, Empresa e ADM | Sidebar, busca, filtros, cards, detalhes e tipografia ampliados | Sidebar recolhida e filtros próprios |
| Mensagens | Contatos, texto, busca e compositor maiores | Estrutura de duas colunas |
| Suporte | Formulários, tickets e fila ADM mais legíveis | Dados, permissões e envio |
| JARVIS | Painel flutuante ampliado, respostas e entrada de texto maiores | Janela limitada pelo viewport |
| Arcade | Biblioteca, categorias, cartões e busca maiores | Lógica e acesso secreto |
| Login/cadastro | Formulários centralizados e legíveis | Validação e regras de conta |
| Lousas touch | Controles de 56–60 px, foco, áreas de interação | Mouse, teclado e controle remoto |

## Matriz de homologação

| Cenário | Viewport de referência | O que verificar |
|---|---|---|
| Notebook | 1366 × 768 | Nenhuma regressão da interface existente |
| Monitor | 1600 × 900 | Sidebar, cards e filtros |
| TV/lousa Full HD | 1920 × 1080 | Leitura à distância, toque e rolagem |
| Ultra-wide | 2560 × 1440 | Limites de largura e colunas |
| TV/lousa 4K | 3840 × 2160 | Tamanhos de fontes, botões, modais |
| Lousa com zoom 150–200% | Viewport CSS variável | Quebra de linhas e controles acessíveis |
| Controle remoto/teclado | Variável | Tab, Enter, Esc, foco visível |
| Lousa com dedo/caneta | Ponteiro grosso quando disponível | Ação sem hover e botões de toque |

Verificar logins dos três perfis, filtros, modais, suporte, chat, JARVIS e Arcade, inclusive com muitos resultados e textos longos.

## Testes e limitações

O teste `tests/ui-consistency.test.cjs` verifica os contratos CSS de Full HD, 4K, telas touch, foco e preservação de layout. Os testes não são screenshots e não certificam aparelhos reais. Ainda é necessária uma revisão visual em TV e lousa digital de verdade. Navegadores antigos podem interpretar `dvh`, `clamp()` ou media queries de modo diferente.

## Publicação

Após CI aprovado, publicar a `main` na Hostinger com backup e atualizar o cache do CSS. Nenhuma migration ou mudança de `.env` é necessária para esta revisão.
