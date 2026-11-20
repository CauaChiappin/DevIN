/* Mapeamento de sugestões de IA para consultas locais somente de leitura. */
(function(scope) {
  'use strict';
  const intents = Object.freeze({
    support_mine: {target:'case', type:'list', filter:'mine'},
    support_queue: {target:'case', type:'list', filter:'support'},
    reports_queue: {target:'case', type:'list', filter:'report'},
    overview: {target:'case', type:'overview'},
    jobs: {target:'admin', type:'browse', kind:'vaga'},
    people: {target:'admin', type:'browse', kind:'pessoa'},
    companies: {target:'admin', type:'browse', kind:'empresa'},
    tasks: {target:'admin', type:'task_list'},
    drafts: {target:'admin', type:'drafts'},
    audit: {target:'admin', type:'activity'},
    conversations: {target:'admin', type:'conversation_history'},
  });
  function normalize(value) {
    return String(value || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '')
      .toLowerCase().replace(/\s+/g,' ').trim();
  }
  function eligible(message) {
    if (typeof message !== 'string' || message.length < 12 || message.length > 500) return false;
    const text = normalize(message);
    // Não interpretar por modelo textos com dados sensíveis nem pedidos de escrita.
    if (/\b(?:senha|api.?key|token|cpf|cnpj|codigo de acesso|chave secreta|exclua|excluir|delete|deletar|apague|remova|remover|envie|enviar|mande|mandar|responda|responder|encerre|encerrar|feche|fechar|publique|aprovar|aprove|rejeitar|rejeite|crie|criar|adicione|adicionar|cancele|cancelar)\b/.test(text)
      || /[\w.+-]+@[\w.-]+\.[a-z]{2,}/.test(text)) return false;
    if (/\b(?:como funciona|como fazer|como melhorar|explique|o que significa|por que|porque|qual sua opiniao|me aconselhe)\b/.test(text)) return false;
    const domain = /\b(?:devin|portal|plataforma|sistema|banco de dados|empresa|empresas|vaga|vagas|candidato|candidatos|pessoa|pessoas|usuario|usuarios|recrutamento|suporte|chamados|atendimento|atendimentos|tickets|denuncias|reports|estatisticas|indicadores|painel|tarefas|atividades|rascunhos|mensagens|conversas|historico|auditoria|registro|registros)\b/.test(text);
    const reading = /\b(?:qual|quais|quem|quantos|quantas|mostre|mostrar|mostra|consulte|consultar|listar|liste|lista|busque|buscar|veja|ver|encontre|encontrar|encontre|ache|achar|procure|procurar|pesquise|pesquisar|tenho|temos|estao|sao|estou|existem|existe|sera|pode|poderia|preciso|queria|quero|consegue|fazer um levantamento|panorama|situacao|resumo)\b/.test(text);
    return domain && reading;
  }
  function proposed(name) {
    if (typeof name !== 'string' || !Object.hasOwn(intents, name)) return null;
    return {...intents[name]};
  }
  const api=Object.freeze({eligible, proposed});
  scope.DevInJarvisSemantic = api;
  if (typeof module!=='undefined' && module.exports) module.exports=api;
})(typeof window!=='undefined' ? window : globalThis);
