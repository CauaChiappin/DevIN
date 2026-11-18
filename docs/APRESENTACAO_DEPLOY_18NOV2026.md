# DevIN — roteiro seguro de publicação e apresentação (18/11/2026)

> **Versão da demonstração:** branch `main`, não a branch comercial. Este documento é um checklist; a implantação na Hostinger **não foi feita automaticamente**.

## 1. Antes de atualizar qualquer arquivo

1. No GitHub, confirme que os workflows da **`main`** terminaram com sucesso: `JARVIS Admin — syntax checks` e `DevIN — comentários explicativos inline`.
2. Registre o SHA do commit que será publicado (`git rev-parse HEAD` ou página de commits no GitHub) e o horário da cópia. Isso permite identificar exatamente qual versão estava instalada.
3. Faça **backup do banco no phpMyAdmin (Exportar → SQL)** e backup dos arquivos atualmente hospedados, incluindo uploads e configurações. Guarde fora do diretório público. Não envie os backups ao repositório Git.
4. Garanta que o arquivo de configuração real da Hostinger (`.env` ou equivalente) não será sobrescrito pelo `.env.example`. Anote somente quais variáveis existem, nunca seus valores secretos.
5. Confirme espaço disponível, permissões para os diretórios de uploads/anexos e um meio de restaurar a versão anterior.

## 2. Qual branch publicar

| Branch | Uso | Publicar amanhã? |
| --- | --- | --- |
| `main` | DevIN escolar: suporte, Arcade, perfis, confirmação de contratação, JARVIS e idiomas | **Sim**, após pré-requisitos e testes |
| `production/monetization` | Catálogo e ferramentas comerciais **experimentais**; contém código além da `main` | **Não** |
| `production/commercial` | Branch criada anteriormente, sem o desenvolvimento comercial atual | **Não** |

A sincronização configurada em `.github/workflows/sync-main-to-monetization.yml` incorpora a `main` em `production/monetization`, sem force push. Se houver conflito, a ação falha e exige revisão. O nome `production` **não é uma autorização para cobrar dinheiro**.

## 3. Atualizar o código na Hostinger

- Se o projeto é publicado por **Git configurado e validado na Hostinger**, atualize **somente `main`** e confira no painel da Hostinger a branch, diretório de destino e commit publicado. `git pull` não atualiza automaticamente um servidor sem integração de deploy.
- Se é publicado por **Gerenciador de Arquivos/SFTP**, envie os arquivos do pacote correspondente ao SHA aprovado da `main`, preservando `.env`, uploads persistentes, permissões e anexos privados. Evite substituir cegamente toda a raiz.
- Teste inicialmente a rota de login e depois o endereço público **https://www.exposoftalcina.com/Trabalhos/3D/DevIN/php/index.php**. Se aparecer erro 500, restaure os arquivos e investigue os logs antes de insistir.
- Em produção, configure `APP_ENV=production` e `APP_URL=https://www.exposoftalcina.com/Trabalhos/3D/DevIN` no arquivo real **somente se esses caminhos corresponderem à instalação atual**. Nunca grave chaves em Git.
- Confira versão de PHP e extensões necessárias (`mysqli`, `mbstring`, `curl`, e as usadas pelo SMTP).

## 4. Banco de dados — **não executar migrations às cegas**

As funcionalidades novas dependem de tabelas adicionais. No banco real, verifique quais migrations **já foram aplicadas** antes de executar alguma. Faça teste em cópia/homologação, backup e revisão de esquema:

- `database/migrations/2026_11_17_005_support_center.sql`: central de suporte; necessária aos chamados e respostas.
- `database/migrations/2026_11_18_006_employment_confirmation.sql`: confirmação bilateral de contratação e retirada consentida de candidaturas.
- `database/migrations/jarvis_admin.sql`, `database/migrations/jarvis_admin_controlled_actions.sql`, `database/migrations/2026_11_16_004_jarvis_moderation.sql`: pré-requisitos correspondentes às funções administrativas do JARVIS.
- `database/migrations/2026_11_09_001_lgpd_foundation.sql`, `database/migrations/2026_11_09_002_admin_stepup.sql` e `database/migrations/2026_11_16_003_admin_roles.sql`: fases de LGPD, autenticação adicional e papéis de ADM; ativar respectivas flags apenas depois de homologar.
- **Não aplicar** `database/migrations/2026_11_18_007_commercial_draft.sql` no banco da apresentação. Esse arquivo pertence à branch comercial e está em rascunho.

Se as tabelas de suporte ou contratação não existirem, **não declare a funcionalidade pronta em produção**. O código na `main` não comprova que a migração foi executada na Hostinger.

## 5. JARVIS e configuração segura

A interface e a lógica de idioma do JARVIS aceitam **português (`pt-BR`), inglês (`en`) e espanhol (`es`)**. A resposta de modelos externos depende da chave, provedor, disponibilidade e cota de API configurados no servidor.

- Teste o chat básico depois de conferir `DEVIN_JARVIS_PROVIDER`, `DEVIN_JARVIS_MODEL` e `DEVIN_JARVIS_API_KEY` no servidor; **não envie os valores para a professora ou prints**.
- Autenticado como ADM, abra `php/api/jarvis/readiness.php` pelo painel de perfil. O diagnóstico é de **somente leitura** e informa flags e tabelas faltantes sem revelar segredos.
- Mantenha `DEVIN_JARVIS_ACTIONS_ENABLED=0` até a instalação e teste de todas as dependências. Habilitar em produção sem migração pode quebrar consultas e mensagens.
- Mantenha `DEVIN_JARVIS_DELETIONS_ENABLED=0` durante a apresentação, salvo se houver homologação explícita de papéis, auditoria, backup, confirmação humana e permissões.
- Não desabilite Token 2/controle de papéis para "desbloquear" ações. Recursos indisponíveis por segurança devem ser demonstrados como **dependentes de configuração**, nunca como executados com sucesso.

## 6. Demonstração guiada para a professora

1. **Página inicial e login:** mostre a navegação, cores, acessibilidade e responsividade.
2. **Pessoa:** abra perfil → Central da minha conta; edite informações, veja currículo, vagas e candidaturas. Use contas fictícias.
3. **Empresa:** publique uma vaga de demonstração com dados fictícios; aprove a candidatura de teste e acione a confirmação da contratação.
4. **Pessoa:** após confirmação da empresa, confirme a contratação; mostre a opção de **manter ou encerrar candidaturas pendentes**. O histórico deve permanecer preservado.
5. **Suporte:** envie um chamado de teste com assunto/descrição e, se desejar, anexo de teste. No ADM, abra, filtre e responda. Mostre mensagem interna e e-mail **somente se SMTP estiver configurado**.
6. **Arcade:** no desktop digite Konami; um erro reinicia sem alerta. No celular, abra `Sobre nós`, segure o controle secreto por 3 segundos; soltar antes cancela.
7. **ADM/JARVIS:** mostre painel, idiomas PT/EN/ES e diagnóstico de ferramentas; execute somente recursos realmente configurados.
8. **Monetização (apenas GitHub/apresentação conceitual):** mostre a branch `production/monetization`, modelos de planos/limites e medidas de segurança. Explique que não existe checkout nem cobrança real.

## 7. Testes logo após atualizar

- Login/logout das três classes (pessoa, empresa e ADM); tentar acessar área ADM sem autenticação deve ser negado.
- Perfil e idioma preservados após recarregar; mensagens de erro não devem revelar chaves nem dados pessoais.
- Vagas abertas, currículo, candidatura, aprovação e confirmação com conta de teste; não excluir registros reais de candidaturas.
- Suporte mobile: criar chamado, histórico, voltar, filtro ADM, resposta; conferir limites de anexos e armazenamento privado.
- Arcade: código Konami com erro/recomeço, botão secreto por toque longo; outros menus/rolagem continuam funcionais.
- JARVIS: teste de chat, leitura do diagnóstico, permissões por ADM; se ferramenta desativada, a tela deve informar o pré-requisito, não liberar operação.
- Inspecionar logs PHP/Hostinger sem publicar credenciais, endereços de usuários ou mensagens internas.
- Confirmar que **CPF/CNPJ/CEP externos, anúncios e assinaturas não foram ativados na `main`**.

## 8. Se algo falhar

1. Interrompa mudanças e registre o erro exato, URL, horário e SHA publicado.
2. Restaure **os arquivos anteriores e a configuração preservada**. Banco só deve ser restaurado de acordo com a necessidade e o impacto em dados gravados desde o backup.
3. Refaça testes de login, banco, suporte e JARVIS. Registre a causa antes de retomar o deploy.
4. Nunca execute `git reset --hard` em diretório com arquivos enviados por usuários, nem faça `git push --force` na `main` ou na branch comercial.

## 9. Critérios mínimos para a apresentação

- Todos os checks CI da `main` verdes;
- Backup restaurável confirmado;
- Login + perfil + vagas operacionais no servidor real;
- Migrações de suporte/contratação verificadas;
- JARVIS sem funções de escrita indevidamente liberadas;
- Sem cobranças, assinaturas ativas, CPF/CNPJ/CEP pagos ou alterações financeiras no ambiente de demonstração.

> **Atenção:** testes automatizados no GitHub validam o código e bancos efêmeros, **não** demonstram que a mesma versão ou as mesmas tabelas estão na Hostinger. A verificação de produção é manual.
