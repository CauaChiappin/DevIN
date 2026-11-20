/* Mapeamento de sugestões de IA para consultas locais somente de leitura. */
(function(scope) { // Explicação DevIN: Define função anônima usada como operação ou callback.
  'use strict'; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  const intents = Object.freeze({ // Explicação DevIN: Declara variável JavaScript intents para reutilizar valor ou resultado.
    support_mine: {target:'case', type:'list', filter:'mine'}, // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    support_queue: {target:'case', type:'list', filter:'support'}, // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    reports_queue: {target:'case', type:'list', filter:'report'}, // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    overview: {target:'case', type:'overview'}, // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    jobs: {target:'admin', type:'browse', kind:'vaga'}, // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    people: {target:'admin', type:'browse', kind:'pessoa'}, // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    companies: {target:'admin', type:'browse', kind:'empresa'}, // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    tasks: {target:'admin', type:'task_list'}, // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    drafts: {target:'admin', type:'drafts'}, // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    audit: {target:'admin', type:'activity'}, // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
    conversations: {target:'admin', type:'conversation_history'}, // Explicação DevIN: Continua expressão ou bloco pertencente a função anônima.
  }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  function normalize(value) { // Explicação DevIN: Declara função/método normalize, com seus parâmetros e comportamento.
    return String(value || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '') // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
      .toLowerCase().replace(/\s+/g,' ').trim(); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  function eligible(message) { // Explicação DevIN: Declara função/método eligible, com seus parâmetros e comportamento.
    if (typeof message !== 'string' || message.length < 12 || message.length > 500) return false; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    const text = normalize(message); // Explicação DevIN: Declara variável JavaScript text para reutilizar valor ou resultado.
    // Não interpretar por modelo textos com dados sensíveis nem pedidos de escrita.
    if (/\b(?:senha|api.?key|token|cpf|cnpj|codigo de acesso|chave secreta|exclua|excluir|delete|deletar|apague|remova|remover|envie|enviar|mande|mandar|responda|responder|encerre|encerrar|feche|fechar|publique|aprovar|aprove|rejeitar|rejeite|crie|criar|adicione|adicionar|cancele|cancelar)\b/.test(text) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      || /[\w.+-]+@[\w.-]+\.[a-z]{2,}/.test(text)) return false; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    if (/\b(?:como funciona|como fazer|como melhorar|explique|o que significa|por que|porque|qual sua opiniao|me aconselhe)\b/.test(text)) return false; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    const domain = /\b(?:devin|portal|plataforma|sistema|banco de dados|empresa|empresas|vaga|vagas|candidato|candidatos|pessoa|pessoas|usuario|usuarios|recrutamento|suporte|chamados|atendimento|atendimentos|tickets|denuncias|reports|estatisticas|indicadores|painel|tarefas|atividades|rascunhos|mensagens|conversas|historico|auditoria|registro|registros)\b/.test(text); // Explicação DevIN: Declara variável JavaScript domain para reutilizar valor ou resultado.
    const reading = /\b(?:qual|quais|quem|quantos|quantas|mostre|mostrar|mostra|consulte|consultar|listar|liste|lista|busque|buscar|veja|ver|encontre|encontrar|encontre|ache|achar|procure|procurar|pesquise|pesquisar|tenho|temos|estao|sao|estou|existem|existe|sera|pode|poderia|preciso|queria|quero|consegue|fazer um levantamento|panorama|situacao|resumo)\b/.test(text); // Explicação DevIN: Declara variável JavaScript reading para reutilizar valor ou resultado.
    return domain && reading; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  function proposed(name) { // Explicação DevIN: Declara função/método proposed, com seus parâmetros e comportamento.
    if (typeof name !== 'string' || !Object.hasOwn(intents, name)) return null; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    return {...intents[name]}; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  const api=Object.freeze({eligible, proposed}); // Explicação DevIN: Declara variável JavaScript api para reutilizar valor ou resultado.
  scope.DevInJarvisSemantic = api; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
  if (typeof module!=='undefined' && module.exports) module.exports=api; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
})(typeof window!=='undefined' ? window : globalThis); // Explicação DevIN: Continua expressão ou bloco pertencente a função proposed.
