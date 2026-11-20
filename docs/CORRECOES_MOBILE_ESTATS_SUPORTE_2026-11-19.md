# DevIN — correções pontuais de estatísticas, suporte e rodapé mobile

**Escopo:** apenas `adm.php?pagina=estatisticas`, a experiência de `adm.php?pagina=suporte` e o rodapé da página `pagina=sobre` nos dashboards. Nenhuma alteração nas grades de vagas, candidatos, currículos ou chat.

## Ajustes de interface

- Estatísticas ADM: correção da regra de duas colunas (menu + conteúdo) que prevalecia indevidamente no celular e cortava o conteúdo; cards compactos e adaptáveis; valores indisponíveis tratados como estado, sem a quebra da palavra ao meio; banner menor; ranking legível.
- Sobre nós: rodapé mobile em duas colunas para navegação e contato; marca e ícones sociais acomodados; menor altura sem prejudicar navegação ou acessibilidade. **A versão desktop foi preservada.**
- Suporte ADM: estado de erro explícito quando falta schema, com referência à migração; mensagens de rede separadas; contador não fica preso em "Carregando chamados"; cabeçalho e cartões mais compactos no celular.

## Atenção: suporte não funciona sem a migração MySQL

O erro **"As tabelas do suporte não estão instaladas"** visto no celular vem da API (`SUPPORT_SCHEMA_MISSING`) e **não** é causado por CSS. O código do site **não deve criar tabelas automaticamente** em produção durante uma visita a uma página.

A migração já está versionada: [2026_11_17_005_support_center.sql](../database/migrations/2026_11_17_005_support_center.sql). Ela cria `support_tickets` e `support_replies` com `CREATE TABLE IF NOT EXISTS`; não contém comando para excluir registros.

### Procedimento recomendado na Hostinger

1. No hPanel, selecione **o banco correto do DevIN** e exporte um backup SQL antes de continuar. Guarde o backup fora do diretório público.
2. No phpMyAdmin, use uma consulta **somente de leitura** para confirmar o estado, sem modificar dados:

```sql
SELECT TABLE_NAME
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN ('support_tickets', 'support_replies')
ORDER BY TABLE_NAME;
```

3. Valide a migração primeiro em um banco de homologação. Depois, no phpMyAdmin do banco DevIN da Hostinger, abra **Importar** e selecione o arquivo da migração ou use a aba **SQL** com seu conteúdo.
4. Repita a consulta de diagnóstico para confirmar as duas tabelas. Evite importar dumps do projeto inteiro ou substituir o banco existente.
5. Entre em **ADM → Suporte → Atualizar**. Faça teste com uma conta Pessoa e outra Empresa, crie chamados fictícios, responda e valide notificações. Reconfigure SMTP/storage privado apenas se o diagnóstico apontar necessidade.
6. Se houver erro SQL, **não** repita comandos de alteração às cegas; confira a mensagem do MySQL e compare o schema existente com a migração. Consulte também [docs/SUPPORT_CENTER.md](SUPPORT_CENTER.md).

## Publicação e regressões

- Faça backup dos arquivos hospedados e preserve `.env`, anexos privados e `php/uploads`.
- Publique os arquivos do código `main` somente após os testes do PR. O merge no GitHub **não garante** que o servidor Hostinger tenha atualizado.
- Use atualização forçada do navegador se houver CSS antigo em cache.
- Valide vistas de 360/390/430/768/1024/1440/1920 px, com menu ADM aberto/fechado.
- Ao atualizar suporte, verifique tanto o caso de tabelas ausentes (aviso de configuração) quanto o caso funcional (caixa de entrada e atendimento).
- Testes de contrato e sintaxe não substituem testes no navegador real autenticado na Hostinger.
