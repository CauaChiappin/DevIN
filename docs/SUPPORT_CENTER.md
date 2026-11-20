# Central de Suporte DevIN — implantação e validação

## Funcionalidades

- Pessoas e empresas logadas: **Suporte** no menu e botão **Ajuda** flutuante na página Sobre nós.
- Formulário: assunto e descrição obrigatórios (10–4.000 caracteres); **um arquivo opcional de até 20 MiB**.
- Formatos aceitos: PDF, PNG, JPG/JPEG, WEBP, TXT, LOG, DOCX e ZIP. Nunca PHP, HTML, SVG ou executáveis.
- Administradores: menu **Suporte** com contador, caixa de entrada com nome, e-mail, categoria, descrição e anexo; resposta individual e encerramento.
- Usuários: acompanham chamados e respostas na Central. Cada resposta também tenta entregar aviso na conversa **Suporte DevIN** e por SMTP.
- JARVIS ADM: o comando *"Jarvis, mostre os chamados de suporte"* lista e lê chamados usando a ferramenta **somente de leitura**; o JARVIS não responde automaticamente nem recebe anexos.
- Menu **Reports**: ícone trocado para bandeira; **Suporte**: ícone exclusivo de boia.

## Implantação na Hostinger — não executada automaticamente

1. Faça **backup** do MySQL e dos arquivos e valide restauração em ambiente de teste.
2. Atualize a `main` somente após os testes do PR passarem; **não substitua o .env privado**.
3. No banco DevIN de homologação, execute uma vez `database/migrations/2026_11_17_005_support_center.sql` e valide as tabelas `support_tickets` e `support_replies`. Depois repita no banco correto da Hostinger com backup.
4. Crie uma pasta **fora de `public_html`** com permissão de escrita exclusiva ao PHP; configure no .env privado `DEVIN_SUPPORT_STORAGE_DIR=/caminho/absoluto/privado`.
5. Configure PHP `upload_max_filesize=20M` e `post_max_size=23M` ou mais; lembre que limites PHP, proxy ou Hostinger podem restringir uploads. No backend o limite continua **20 × 1024 × 1024 bytes**.
6. Confirme `DEVIN_SMTP_*` no .env. O suporte registra respostas mesmo se o SMTP falhar e informa o estado da entrega; o ADM pode tentar reenviar.
7. Teste com uma conta de pessoa e uma de empresa criadas exclusivamente para homologação. Verifique login, CSRF, assunto e descrição obrigatórios, arquivos 19 MB/21 MB, formato inválido, lista, download com permissão, resposta e bloqueio entre contas.
8. Acesse o painel de ADM, confira o contador, a caixa de entrada e a resposta por e-mail + Mensagens. Teste o JARVIS com `DEVIN_JARVIS_ACTIONS_ENABLED=1` **apenas depois** de migrações e permissões existentes validadas.
9. Verifique que nenhuma URL pública em `/storage/` ou nos arquivos físicos entregue anexos diretamente. A regra existente no `.htaccess` raiz bloqueia a pasta; um `.htaccess` adicional é criado pelo PHP na pasta de anexos.

## Experiência no celular

- O formulário do candidato ou da empresa abre na aba **Novo chamado**; a aba **Meus chamados** mostra histórico e respostas.
- Depois de enviar, o histórico do chamado recém-criado aparece automaticamente. A confirmação de envio permanece visível acima das abas, evitando que se perca ao esconder o formulário.
- O arquivo opcional mostra nome e tamanho antes do envio e tem um botão **Remover**. O limite real continua em 20 MiB no backend, sem leitura local do conteúdo.
- Há contador de até 4.000 caracteres no relato para facilitar a escrita no teclado móvel.
- ADMs usam **Caixa de entrada** e **Atendimento** em abas separadas no celular, com retorno **Voltar à lista** e sem exigir rolagem horizontal.
- Campos textuais possuem fonte de 16 px (evita zoom involuntário do iOS), alvos de toque de 48 px e espaçamento para barra de gestos Android.
- No desktop, a divisão em duas colunas permanece. As abas são ocultadas acima de 767 px de largura.
- Testar manualmente com viewport de 320 px, 360 px, 390 px, 768 px e desktop, incluindo teclado aberto, nomes de anexos longos e rotação de tela. Testes automatizados verificam o contrato, mas não equivalem a teste visual de navegador.

## Segurança e limitações

- Identificação (nome e e-mail) vem do banco vinculado à sessão; nunca do formulário.
- SQL utiliza declarações preparadas; anexos usam token aleatório de 48 caracteres sem extensão e download com autenticação de proprietário/ADM.
- A interface apresenta conteúdo fornecido pelo usuário com `textContent`, evitando interpretação como HTML.
- A abertura é limitada a 8 chamados por dia por conta e 1 chamado a cada 60 segundos.
- Há limite de 150 chamados na lista ADM e 60 na lista do usuário. Anexos não são executados; prints elegíveis podem ser enviados ao modelo de visão apenas com autorização específica no chat do JARVIS ADM.
- Sem integração com antivírus, a equipe deve tratar ZIP, PDF e DOCX como arquivos potencialmente perigosos e nunca executar conteúdo recebido.
- Notificações são persistentes no MySQL, com horário de entrega por canal e até cinco tentativas SMTP por resposta. Não há agendador de reentrega automática; usar o botão de reenvio no painel.
- Os chamados podem conter dados pessoais. Aplicar política de retenção/expurgo, controle de acesso e direitos LGPD antes de uso real amplo.
- Token 2 e RBAC não são ativados automaticamente por esta funcionalidade. Sessão ADM e CSRF continuam obrigatórios.
- **A implantação em produção e o teste real de SMTP não fazem parte da CI**; precisam de homologação específica.

## Arquivos do recurso

| Arquivo | Responsabilidade |
| --- | --- |
| `database/migrations/2026_11_17_005_support_center.sql` | Estrutura MySQL de chamados, respostas e histórico de entrega |
| `php/controllers/SupportDesk.php` | Validação, propriedade, anexos, persistência e notificação |
| `php/support_api.php` | Endpoints GET/POST autenticados e CSRF |
| `php/suporte.php` | Formulário de pessoa/empresa e acompanhamento |
| `php/components/support-admin.php` | Caixa de atendimento e respostas ADM |
| `js/support-ui.js` | Comportamento da interface, upload e resposta |
| `js/support-badge.js` | Contador de chamados abertos no menu ADM |
| `css/support.css` | Identidade visual, acessibilidade e responsividade |
| `php/api/jarvis/admin_tools.php` | Consulta autenticada e somente de leitura para JARVIS |
| `tests/support_center_mysql.php` | Integração com MySQL descartável, permissões e notificações simuladas |
| `tests/support-ui.test.cjs` | Contratos de UI, proteção e ícones |

## JARVIS: suporte e denúncias exclusivamente pelo chat

O botão "Ajuda do JARVIS" foi removido do formulário ADM → Suporte. O atendimento manual continua funcionando normalmente. A assistência agora fica somente na janela de conversas do JARVIS ADM.

Exemplos de pedidos: "Mostre os reports na minha jurisdição", "Liste os chamados de suporte", "Analise esta denúncia" depois de selecionar um caso. O JARVIS apresenta cartões do MySQL, o ADM escolhe pelo ID e lê o contexto. O ADM pode escrever o parecer sozinho, solicitar uma análise ou um rascunho (com consentimento) e preparar uma decisão no próprio chat.

**Jurisdição:** atualmente não há um campo de atribuição por ADM. Chamados de suporte são compartilhados entre ADMs. Quando RBAC está habilitado, denúncias são mostradas apenas aos perfis administrativos que podem moderá-las; sem RBAC, vale o acesso ADM existente. Isso NÃO significa que os reports estejam distribuídos individualmente.

**Aprovação humana:** uma ação só é realizada após o ADM revisar o texto, solicitar a prévia e confirmar em um segundo POST autenticado com token de uso único de até 180 s e palavra ENVIAR, DECIDIR ou EXCLUIR. A decisão pode manter ou remover uma vaga/perfil; a remoção é uma ação destrutiva e exige confirmação reforçada. O parecer final é armazenado nas colunas já existentes dos reports. O JARVIS não escolhe sozinho um veredito e não faz mudanças por meio da resposta do modelo. Respostas de suporte usam a infraestrutura existente de mensagens e e-mail.

**Prints:** no cartão do caso há um seletor opcional PNG/JPG/WebP (até 4 MB), ou um anexo de imagem já existente do chamado de suporte, para analisar com um modelo compatível com visão. O ADM deve autorizar expressamente a transmissão do print a um provedor externo. O servidor reconverte a imagem com PHP GD para remover metadados (como EXIF/GPS), reduz a resolução e não salva o arquivo anexado no chat. A imagem ainda pode conter dados pessoais visíveis e a anonimização visual não é garantida. Arquivos PDF, DOCX, ZIP e outros formatos não são tratados como print pelo JARVIS.

**Limitações:** as análises e pareceres dependem da API JARVIS existente e do modelo configurado. Se ele não aceitar imagens, a leitura visual falhará com aviso. Selecionar o caso e conferir seu conteúdo não exige enviar dados ao provedor. O contexto textual enviado ao modelo é reduzido e remove padrões comuns de contatos e credenciais; essa remoção não garante anonimização completa. A UI não mantém um histórico separado de pareceres não enviados após recarregamento. Casos já concluídos ficam disponíveis somente para leitura.

**Implantação:** não há migração SQL nova para este fluxo. Preserve .env, uploads, armazenamento privado e banco da Hostinger. Os endpoints exigem sessão ADM, CSRF, limite de uso e Token 2 se a etapa de IA estiver habilitada. Validar o desktop e celulares 360/390/430 px, especialmente cards de chat, teclado, seleção de prints e atualização depois de um veredito.

Arquivos principais: php/api/jarvis/cases.php, php/controllers/Jarvis/JarvisCaseDesk.php, js/jarvis-cases.js, css/jarvis-admin.css e testes/jarvis-cases.test.cjs.


## Responsável por chamado — migração 008 (20/11/2026)

**Definição:** quando um ADM diz no chat do JARVIS "me ajuda com os chamados da minha jurisdição" ou "sou representante desse suporte", a lista deve mostrar somente `support_tickets.assigned_admin_id = id_administrador` da sessão atual. Não confundir esse vínculo com RBAC ou denúncias de vagas/perfis. Tais denúncias não têm representante individual nesta implementação.

**Fila compartilhada e titular:** todos os ADMs continuam vendo e lendo os chamados. Um chamado novo ou antigo começa sem representante. Na lista/atendimento, o ADM toca em **Atender chamado**; isso se torna vínculo persistente. A primeira resposta ao chamado também o assume automaticamente se estiver livre. Se outro ADM já é responsável, só se pode responder ou encerrar após escolher explicitamente **Assumir atendimento**, com confirmação visual. O titular pode liberar o chamado. As transições e o ADM que realizou cada troca são salvos em `support_assignment_events`.

**Como implantar sem quebrar a hospedagem:** crie backup do MySQL e execute **uma vez** `database/migrations/2026_11_20_008_support_representatives.sql` no banco DevIN da Hostinger antes de atualizar o PHP. O script não preenche titulares antigos automaticamente e pode ser executado novamente se necessário; contém verificações de coluna e índice existentes. Enquanto a migration ainda não tiver sido aplicada, o suporte legado segue operante, mas não será possível usar as atribuições; o JARVIS avisará que falta a migration. Após executar, atualize a `main` na hospedagem. Não é necessário adicionar variáveis novas ao `.env`.

**Validação no celular/desktop:** dois ADMs autenticados devem enxergar o mesmo chamado. ADM A assume; ADM B deve ver "Responsável: ADM #..." e não pode enviar resposta sem antes confirmar a transferência. Após B assumir, o JARVIS de A deve deixar de listar esse chamado em "minha jurisdição", enquanto o JARVIS de B passa a exibi-lo. Verifique histórico e nome do responsável sem expor ID ADM para a pessoa/empresa solicitante. Confirme que as outras telas e dashboards não mudaram de tamanho.


## Correção visual JARVIS: seleção do caso e representante (20/11/2026)

A fila de suporte e o detalhe do mesmo chamado devem indicar o **mesmo ADM representante**. O serviço `JarvisCaseDesk::detail` agora retorna `assigned_admin_id` e `assigned_to_me` calculados pelo backend, como já acontece na lista. A seleção do chamado deve oferecer **Liberar atendimento** quando o titular é o ADM da sessão; **Assumir suporte** apenas quando estiver livre; e **Assumir de outro ADM** somente quando houver outro titular, com confirmação e auditoria.

No celular, o modo de análise de casos do JARVIS ocupa a altura útil da tela com abas em uma linha rolável horizontalmente e esconde temporariamente busca de histórico, atalho de resumo e opção genérica de histórico. O chat e o botão de envio continuam disponíveis, e a navegação completa retorna ao listar novamente os casos ou trocar de conversa. Nenhum outro dashboard é alterado.

Teste na Hostinger após atualizar o código: abra no Suporte o chamado cujo representante já é você, confira o botão **Liberar chamado**, depois selecione o mesmo caso pelo JARVIS. Ele deve informar "Você é o representante" e oferecer **Liberar atendimento** (não **Assumir suporte**). Compare com um segundo ADM que visualiza, mas não pode responder sem transferência. Não conclua envio nem transferência durante o teste de layout.
