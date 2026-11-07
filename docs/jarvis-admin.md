# JARVIS Admin — Fase 1 (experimental)

Este módulo está isolado na branch `feature/jarvis-admin`. O painel da `main` não muda até revisão e merge do PR.

## Capacidades disponíveis
- Widget flutuante responsivo em todas as páginas de `php/adm.php`.
- Chat via provedor compatível com Chat Completions usando API key exclusiva no servidor.
- Resumo agregado de candidatos, empresas, vagas e denúncias pendentes.
- Proteção pela sessão ADM, CSRF, limite de 30 solicitações/hora e endpoints HTTPS fixos.
- Histórico somente na memória da aba; limpe o chat para apagá-lo localmente.

**Ainda não implementado:** execução de ações administrativas, confirmação/auditoria transacional, memória persistente no MySQL, ferramenta de consulta por entidade, anexos, streaming, fallback e testes automatizados. O chat não executa nenhuma alteração.

## Variáveis de ambiente (não incluir segredos no Git)
```dotenv
DEVIN_JARVIS_PROVIDER=groq
DEVIN_JARVIS_MODEL=
DEVIN_JARVIS_API_KEY=
```
Provedores aceitos inicialmente: `groq`, `openrouter`, `openai`. Escolha um modelo realmente disponível para a conta e suas condições de uso. A chave do currículo Gemini é independente desta chave.

## Validação manual recomendada
1. Entrar como ADM, abrir `adm.php?pagina=inicio` e abrir o launcher no canto inferior esquerdo.
2. Navegar para `posts`, `candidatos` e `reports` e verificar o widget.
3. Testar tela de celular, textarea de múltiplas linhas, fechar, expandir e limpar.
4. Clicar em **Resumo do DevIN** e conferir contagens com o banco.
5. Sem chave, o chat deve retornar erro de configuração sem mostrar segredos.
6. Sem sessão ADM, o endpoint não pode devolver indicadores nem iniciar chamadas a provedores.
7. Enviar POST sem CSRF (403), payload grande (413), modo não permitido (400) e exceder limite (429).
8. Configurar chave válida e testar chat; confirmar que respostas não executam ações.

## Próximas fases
Extração dos serviços administrativos existentes; consultas com ferramentas limitadas; operações pendentes assinadas e de uso único; confirmação explícita e log de auditoria; persistência e testes automatizados. Não habilitar ações destrutivas antes dessas entregas.
