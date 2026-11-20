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
- Há limite de 150 chamados na lista ADM e 60 na lista do usuário. Anexos não são executados, interpretados nem enviados ao modelo de IA.
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

## JARVIS como copiloto de atendimento (sob solicitação)

O painel **ADM → Suporte** oferece **Pedir sugestão ao JARVIS** somente depois de selecionar um chamado aberto. A ferramenta não é um atendimento automático: nunca responde ao usuário, não fecha chamados e não altera contas ou dados do banco.

Fluxo: abrir chamado → ler e revisar o relato → marcar autorização de envio de contexto ao provedor → gerar sugestão → **Usar no campo de resposta** → editar → enviar manualmente como Suporte DevIN. Trocar de chamado cancela a sugestão e limpa a autorização anterior.

Segurança e privacidade:
- Apenas administradores autenticados acessam o endpoint privado php/api/jarvis/support_assist.php. A API valida existência da conta, sessão, CSRF e, quando habilitado, o desbloqueio AI de Token 1/Token 2.
- O servidor lê o ticket autorizado diretamente do banco. O navegador não envia descrição, email, anexo, nome nem histórico arbitrário.
- São enviados somente categoria, trecho da descrição e até cinco mensagens anteriores, com remoção de padrões comuns de e-mail, telefone, links, CPF/CNPJ e credenciais. **A anonimização é limitada e não garante remover todos os dados pessoais**, portanto o ADM deve revisar antes de autorizar. Nenhum arquivo anexo é transmitido ao modelo.
- Não são incluídos nomes, e-mails ou IDs cadastrais do solicitante. Instruções escritas nos chamados são tratadas como conteúdo não confiável.
- Limite: 10 solicitações de rascunho por administrador/hora. O retorno é exibido apenas como rascunho no navegador e não é gravado automaticamente como resposta.
- O sistema registra somente ADM e ID do chamado no log técnico para diagnosticar solicitações, sem copiar o texto enviado à IA.
- O JARVIS depende das configurações existentes DEVIN_JARVIS_PROVIDER, DEVIN_JARVIS_MODEL e DEVIN_JARVIS_API_KEY e do desbloqueio adicional quando estiver ativado.

**Deploy:** essa funcionalidade não exige migração SQL nova. Preserve .env, bancos, fotos e anexos privados. Teste no ambiente de homologação, depois publique o código validado. O envio ao GitHub não publica automaticamente na Hostinger.

**Validação:** como ADM, abra um chamado de teste e confirme que sem marcar o consentimento não ocorre pedido, que uma sugestão não é enviada sem clicar em Responder ao usuário, e que trocar de chamado elimina o rascunho. Teste celulares de 360, 390, 430 e 768 px, teclado aberto e menu recolhido/expandido. Em cada caso, confirme ausência de scroll horizontal.

**Observação:** a fila de chamados já é compartilhada entre ADMs; atribuição exclusiva de responsável e notas internas são uma funcionalidade diferente e não são ativadas por esse copiloto.
