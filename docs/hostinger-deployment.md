# Publicação do DevIN na Hostinger

## Estado em 09/11/2026

- Produção: https://www.exposoftalcina.com/Trabalhos/3D/DevIN/php/index.php
- Repositório: `CauaChiappin/DevIN`.
- Branch de publicação preparada: `deploy/hostinger`.
- Ponto inicial da branch: `f8a728847ffe7edc5793dd20966274bae9c4e394`, copiado da main. **É somente uma referência inicial: não comprova equivalência com o servidor nem aprovação para publicar.**
- A página pública respondeu HTTP 200. Isso não valida banco, login, área ADM, modelo de IA ou a versão instalada.
- A integração da Hostinger, o caminho físico e o acesso ao painel ainda não foram verificados. Nenhum deploy foi executado por esta preparação.
- PR #7 (Jarvis Admin) permanece draft, depende da segurança do PR #8 e de testes/migrações. Não promover esses PRs sem suas validações.

## Arquitetura escolhida

A automação analisa o JarvisCode, adapta somente o que se aplica ao DevIN e prepara um PR. Mudanças de baixo risco só são integradas após validação. A publicação usa a integração Git nativa da Hostinger com uma branch dedicada; não adicionar um agente público ou uma rota PHP que execute comandos de deploy.

**A branch de publicação é um ponto de controle, não uma proteção técnica por si só.** Quem tiver permissão de escrita pode movimentá-la. A integração nativa publica cada alteração dessa branch depois de conectada. Configure proteção/restrição de escritores pelo proprietário, conforme os recursos do repositório; até lá, não promova automaticamente se não for possível garantir a procedência e exclusividade da promoção.

O objetivo é manter a main disponível para desenvolvimento e mover a branch de publicação somente para um SHA explicitamente validado. Não conectar a Hostinger diretamente à main para este fluxo.

## Configuração no painel

O responsável pelo domínio deve abrir o painel do site existente em Hostinger, seção Git (em geral Avançado → Git), e verificar:

| Campo | Valor |
| --- | --- |
| Domínio existente | exposoftalcina.com |
| Provedor | GitHub |
| Repositório | CauaChiappin/DevIN |
| Branch | deploy/hostinger |
| Diretório | caminho físico **confirmado no painel** da pasta que atende /Trabalhos/3D/DevIN/ |
| Endereço de verificação | https://www.exposoftalcina.com/Trabalhos/3D/DevIN/php/index.php |

`public_html/Trabalhos/3D/DevIN` é apenas um candidato comum, não um caminho verificado. Não usar `public_html` inteira nem as pastas `Trabalhos` ou `3D` como destino do projeto. Não criar um novo site, alterar DNS, nem substituir os outros trabalhos.

A primeira conexão/deploy é uma publicação real. Antes de clicar em Deploy:

1. Faça backup dos arquivos atuais e do banco e confirme como restaurá-los.
2. Compare a versão que está no servidor com o SHA escolhido. Ajustes feitos por FTP/Gerenciador de Arquivos precisam ser preservados/reconciliados. Não sobrescreva a instalação só por ela responder HTTP 200.
3. Confirme a compatibilidade de PHP e extensões, dependências Composer e URLs de subpasta. Preserve o `.env` e sua configuração de banco/SMTP/IA, uploads, sessões e armazenamento do ambiente. O `.gitignore` não garante que o mecanismo de deploy preserve arquivos não versionados: verifique o comportamento.
4. Verifique se a integração pode usar a pasta existente sem limpar arquivos. Se exigir pasta vazia ou limpeza, pare e prepare migração controlada; não apague a instalação.
5. Estabeleça um método de saúde/versão e restauração verificável, e execute primeiro um teste em staging ou cópia isolada.
6. Autorize a extensão Hostinger no GitHub pelo fluxo OAuth, apenas no repositório necessário. Não envie senhas, tokens ou chaves por chat.

Se o recurso Git não estiver disponível para esse site/plano ou para uma subpasta existente, use um pipeline SSH/SFTP específico após confirmar os acessos; ele ainda não foi implementado. Não criar credenciais fictícias ou habilitar FTP sem criptografia como substituto.

## Regras de promoção

Antes de cada promoção automática:

1. Releia o estado atual da origem, da main e de `deploy/hostinger`. Use os SHAs completos.
2. Analise **toda a diferença entre a versão publicada e o alvo**, incluindo commits de outras pessoas que possam estar na main. Um PR pequeno não torna todo o intervalo publicável.
3. Exija que todo o intervalo esteja dentro das categorias autorizadas: documentação sem dados sensíveis, correções estritamente visuais do widget ou de apresentação de respostas, sem mudanças de execução, tráfego, permissões, provedor ou custos.
4. Banco/migrações, autenticação/2FA, CSRF, permissões ADM, ações executáveis, dependências, provedores/modelos, segredos, workflows e mudanças de infraestrutura precisam de aprovação específica. Esta documentação prepara infraestrutura; não aprova alterações futuras nessas áreas.
5. Execute os testes adequados no **SHA exato** que será publicado. UI requer verificação mobile/desktop; integração requer ambiente apropriado. Checks ausentes ou apenas lint não equivalem a aprovação.
6. Preserve o PR revisável e os gates existentes. Não publicar PR draft, PR bloqueado, alterações não integradas do JarvisCode ou o Jarvis Admin ainda sem as validações exigidas.
7. Só promova quando a integração Hostinger, a referência da versão instalada e o procedimento de recuperação estiverem confirmados. Registre o SHA anterior, o alvo e a evidência dos testes.
8. Atualize a referência de publicação de forma condicionada ao SHA anterior e sem force push. Se a referência mudou, pare e reavalie; se não for fast-forward, prepare um caminho revisável em vez de sobrescrever.
9. Confirme no histórico de deploy da Hostinger o SHA instalado e verifique o site e os fluxos relevantes. Não confunda merge ou HTTP 200 com deploy bem-sucedido.

Se houver falha, registre-a e use o procedimento de restauração previamente validado. Uma reversão de código não desfaz automaticamente migrações ou efeitos no banco. Não prometa rollback automático antes de testar esse mecanismo.

## Cobertura atual

A automação do ChatGPT recebe eventos de PR do JarvisCode, inclusive novos commits em PR. **Push direto sem PR não está coberto.** A integração Git da Hostinger cobre a publicação da branch escolhida, mas não realiza análise/adaptação de JarvisCode nem amplia os gatilhos do ChatGPT. Um mecanismo adicional para pushes diretos continua pendente.

## Referência oficial

https://www.hostinger.com/support/1583302-how-to-deploy-a-git-repository-in-hostinger/
