# DevIN — auditoria de layouts, pesquisas e filtros

**Base analisada:** branch `main` de `CauaChiappin/DevIN`, 18/11/2026.
**Objetivo:** reduzir inconsistências de interface preservando as funcionalidades específicas de cada perfil.

## Padrão adotado

O cabeçalho `dashboardListHeader()`, usado pelos painéis Pessoa, Empresa e ADM, foi preservado. O filtro da **Empresa → Candidatos** foi usado como referência de completude: pesquisa global, status, escolaridade, currículo e filtros internos por vaga (curso, habilidade, experiência). Os demais perfis mantêm seus filtros pertinentes aos dados que possuem, sem inventar campos ou opções.

A camada `css/ui-consistency.css` centraliza cores azuis, dimensionamento responsivo de campos, foco pelo teclado, estados `hidden`, alvos de toque e contenção horizontal. Aplicada aos três dashboards, chat, Suporte, Arcade, cadastro, login, recuperação de senha, páginas de currículo, portal institucional e entradas HTML legadas. Mantém intactas as cores e estruturas particulares de jogos e seções específicas.

## Comparação por área

| Área | Recursos existentes | Ajuste desta revisão | Observação |
|---|---|---|---|
| Pessoa: vagas/candidaturas | Busca textual e seleção de tags | Limpar filtros, contador de resultados, painel acessível | A lista trabalha com os cards já carregados |
| Empresa: vagas/candidatos | Busca global; status, escolaridade, currículo; filtros locais por vaga | Mesma ação de limpeza para filtros globais e locais; grade adaptativa | Referência de completude |
| ADM: empresas, pessoas, posts, reports | Busca e filtro por tags, categorias de reports | Cabeçalho e controles coerentes com Pessoa/Empresa | Categorias de reports permanecem separadas |
| Mensagens | Busca de pessoas/empresas, lista, indicador de não lidas | Contenção e foco de campos no celular | Busca por contatos é feita pela API |
| Suporte usuário/ADM | Filtros, busca e histórico, abas no celular | Campos e layout contidos no viewport, foco consistente | A persistência e o SMTP continuam dependendo do servidor |
| Arcade | Busca de jogos e botões de categoria | Categorias quebram linha sem transbordar, foco e área de toque | A entrada secreta não foi modificada |
| Cadastro/login/recuperação/currículo | Formulários responsivos de módulos distintos | Campos sem largura mínima problemática e tipografia móvel legível | Regras de negócio e validação mantidas |
| Site institucional/HTML | Menus públicos e formulários de legado | Camada visual comum importada | Recomenda-se futura consolidação de páginas HTML legadas |

## Comportamento compartilhado de pesquisa e filtros

- Botão **Filtros** anuncia seu estado e a qual painel pertence (`aria-controls` + `aria-expanded`).
- `Esc` fecha o painel e devolve o foco ao botão; clique ou toque fora fecha o painel sem enviar formulários.
- **Limpar filtros** restaura pesquisa, tag/status, escolaridade, currículo e, quando presentes, curso, habilidade e experiência específicos das vagas.
- Contagem da lista exibida aparece quando existe uma pesquisa/filtro ativo; o total original do cabeçalho não é alterado.
- A busca continua local para cards já renderizados; não há chamada ao MySQL a cada tecla.

## Testes e implantação

O contrato automatizado `tests/ui-consistency.test.cjs` verifica integração dos CSS, viewport, busca, filtros, acessibilidade e estados ocultos. `node --check js/dashboard.js` verifica sintaxe.

**Limites desta auditoria:** inspeção estática e CI não são testes visuais. Verificar manualmente em um navegador real nas larguras 320, 360, 390, 720, 768, 980 e 1440 px, tanto Android/iOS como desktop, incluindo teclado aberto, zoom a 200%, rotação e texto longo. Testar telas com listas vazias, centenas de resultados, erros de rede e contas de cada perfil. O sistema de mensagens ainda usa arquivos temporários JSON, uma limitação de persistência independente deste trabalho.

**Implantação:** fazer backup e publicar a `main` testada na Hostinger. Não alterar `.env` nem migrações por causa destas regras visuais. O `.env.example` fornece nomes de configurações, mas valores fictícios não conectam a serviços reais.

## Manutenção

Adicionar novas telas usando os estilos e o cabeçalho compartilhados, mas manter componentes específicos de negócio. Não duplicar controladores de filtros em scripts concorrentes. Não tratar resultados dos testes de sintaxe como validação visual ou de infraestrutura de produção.
