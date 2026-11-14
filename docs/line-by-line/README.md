# Documentação de código linha por linha — DevIN

Este diretório registra **uma explicação vinculada a cada linha de cada arquivo de código** do projeto, sem inserir comentários automaticamente dentro das instruções PHP, JavaScript, CSS, HTML ou SQL que poderiam alterar a execução.

## Onde acessar

Abra [INDEX.md](INDEX.md) e escolha o arquivo desejado. Cada documento inclui um registro por linha, link direto para a linha original e SHA-256 do arquivo para detectar documentação desatualizada.

### Cobertura

Abrange arquivos rastreados no Git com as extensões PHP, JS/CJS/MJS, CSS, HTML, SQL, JSON, YAML, SVG/XML, shell e TypeScript, além dos arquivos especiais de configuração `.env.example`, `.htaccess`, `.gitignore`, `Dockerfile` e `Makefile`.

Não inclui imagens binárias, PDFs, dependências de terceiros, diretórios de build nem arquivos lock gerados automaticamente. Esses materiais não são código-fonte autoral linha a linha; se necessário, podem ser tratados à parte.

## Qualidade e revisão

As primeiras descrições são **geradas por regras de sintaxe** e deliberadamente não copiam literais sensíveis da origem. Elas explicam a função estrutural de cada linha. **Não equivalem a uma interpretação humana profunda de toda expressão nem comprovam o comportamento do software.** Para lógica complexa, chamadas encadeadas, validação, acesso a dados e regras de segurança, o responsável técnico deve revisar o código e complementar o projeto com documentação detalhada de funções, regras e testes.

É importante não transformar descrições automatizadas em documentação falsa: quando a sintaxe não permite determinar a semântica com segurança, o sistema indica que a linha continua uma expressão ou um bloco. A documentação de cada linha existe, mas algumas explicações permanecem explicitamente **pendentes de revisão**.

## Manutenção obrigatória

Ao criar, corrigir ou remover código, execute:

```bash
python3 scripts/line_explanations.py --write
python3 scripts/line_explanations.py --check
```

O workflow do GitHub `.github/workflows/line-documentation.yml` executa esse gerador automaticamente em pushes, registra alterações na mesma branch quando autorizado e verifica em pull requests se **todos os arquivos e linhas** continuam documentados. O hash por arquivo força a atualização quando uma única linha muda, mesmo que o número de linhas permaneça igual.

**Se a automação não conseguir gravar na branch protegida ou se o PR vier de um fork**, o check exibirá a pendência; o desenvolvedor deverá executar o gerador localmente e incluir o resultado no PR. É melhor falhar do que declarar documentação sincronizada indevidamente.

### Segurança

- Não guardar `.env` real, senhas, credenciais SMTP, banco, APIs ou tokens.
- Não copiar dados pessoais para os comentários ou explicações.
- Não alterar código executável apenas para gerar comentários.
- Não interpretar uma alteração documentada como aprovada: testes, revisão de código e autorização de deploy continuam necessários.

A documentação não altera nenhuma rota do site, banco, sessão de ADM, integração da IA ou configuração de produção.
