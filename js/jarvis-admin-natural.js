/* Complemento local do JARVIS: intenções administrativas apoiadas por ferramentas reais. */
(function(scope) { // Explicação DevIN: Define função anônima usada como operação ou callback.
  'use strict'; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.

  function normalized(text) { // Explicação DevIN: Declara função/método normalized, com seus parâmetros e comportamento.
    return String(text || '').normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase() // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
      .replace(/\s+/g, ' ').trim(); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  function stripPrefix(text) { // Explicação DevIN: Declara função/método stripPrefix, com seus parâmetros e comportamento.
    return String(text || '').trim().replace(/^(?:(?:jarvis|jarvisdevin)[\s,:!.-]*|(?:por favor|pfv)[\s,:!.-]*)+/i,'').trim(); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  const names = { // Explicação DevIN: Declara variável JavaScript names para reutilizar valor ou resultado.
    vaga: /^(?:vagas?|an[úu]ncios?|oportunidades?)$/i, // Explicação DevIN: Continua expressão ou bloco pertencente a função stripPrefix.
    empresa: /^(?:empresas?|empregadores?)$/i, // Explicação DevIN: Continua expressão ou bloco pertencente a função stripPrefix.
    pessoa: /^(?:candidatos?|candidatas?|pessoas?|usu[áa]rios?|usu[áa]rias?|perfis?)$/i, // Explicação DevIN: Continua expressão ou bloco pertencente a função stripPrefix.
  }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  const kinds = '(vagas?|an[úu]ncios?|oportunidades?|empresas?|empregadores?|candidatos?|candidatas?|pessoas?|usu[áa]rios?|usu[áa]rias?|perfis?)'; // Explicação DevIN: Declara variável JavaScript kinds para reutilizar valor ou resultado.

  // Somente expressões de listagem conhecidas; filtros específicos permanecem intactos.
  function isListingTail(tail) { // Explicação DevIN: Processa a etapa correspondente do fluxo administrativo.
    const value = normalized(tail).replace(/[.!?]+$/g, '').trim(); // Explicação DevIN: Processa a etapa correspondente do fluxo administrativo.
    const withoutOutro = value.replace(/\s+e\s+(?:mostre|liste|exiba|apresente)\s+(?:(?:todos?)\s+)?(?:os|as)\s+resultados?$/, ''); // Explicação DevIN: Processa a etapa correspondente do fluxo administrativo.
    return /^(?:(?:que\s+)?estao\s+)?(?:(?:disponiveis?|cadastrad[oa]s?|existentes?|existem|publicad[oa]s?|ativ[oa]s?|recentes?)\s*)?(?:(?:no|do|da|na)\s+(?:devin|site|plataforma))?$/.test(withoutOutro.trim()); // Explicação DevIN: Processa a etapa correspondente do fluxo administrativo.
  } // Explicação DevIN: Processa a etapa correspondente do fluxo administrativo.

  function kindOf(raw) { // Explicação DevIN: Declara função/método kindOf, com seus parâmetros e comportamento.
    return Object.keys(names).find(k => names[k].test(raw || '')) || null; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  function target(raw, kind) { // Explicação DevIN: Declara função/método target, com seus parâmetros e comportamento.
    const value = String(raw || '').trim().replace(/^[#:]\s*/, '').replace(/[.!?]+$/,'').trim() // Explicação DevIN: Declara variável JavaScript value para reutilizar valor ou resultado.
      .replace(/^(?:com o nome|de nome|intitulado|intitulada|chamado|chamada)\s+/i,''); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    if (!value) return {kind,id:null,name:''}; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    const id = /^(?:(?:id|n[úu]mero|n[ºo])\s*[#:=-]?\s*)?(\d{1,9})$/i.exec(value); // Explicação DevIN: Declara variável JavaScript id para reutilizar valor ou resultado.
    return id ? {kind,id:Number(id[1]),name:''} : {kind,id:null,name:value.slice(0,100)}; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  function classify(message) { // Explicação DevIN: Declara função/método classify, com seus parâmetros e comportamento.
    if (typeof message !== 'string' || !message.trim() || message.length > 1400) return null; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    const input = stripPrefix(message); // Explicação DevIN: Declara variável JavaScript input para reutilizar valor ou resultado.
    const plain = normalized(input); // Explicação DevIN: Declara variável JavaScript plain para reutilizar valor ou resultado.
    // Suporte e moderação pertencem ao roteador de casos, que valida a titularidade.
    // Evita que um comando sobre chamado seja tratado como busca genérica por nome.
    if (/\b(?:suportes?|chamados?|tickets?|atendimentos?|den[uú]ncias?|reports?)\b/i.test(input) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        && !/\b(?:tarefas?|atividades?)\b/i.test(input)) return null; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.

    if (/\b(como funciona|como fazer|o que significa|o que e|explique|tutorial|como posso)\b/.test(plain) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        && !/^(?:me mostre|mostre|liste|consulte)\b/.test(plain)) return null; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.

    // Logs são apenas leitura. Mensagens oficiais e auditoria já possuem endpoints autenticados.
    if (/\b(?:historico|registro|registros|log|logs)\b/.test(plain) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        && /\b(?:conversas?|chats?|mensagens? anteriores)\b/.test(plain)) return {type:'conversation_history'}; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    if (/\b(?:chats? anteriores|conversas? anteriores|conversas? antigas)\b/.test(plain)) return {type:'conversation_history'}; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    if (/\b(?:rascunhos?|mensagens? preparadas?|mensagens? enviadas?|caixa de saida)\b/.test(plain) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        && !/^(?:envie|mande|avise|comunique|escreva)/.test(plain)) return {type:'drafts'}; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    if (/\b(?:historico|registro|registros|log|logs|auditoria)\b/.test(plain) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        && /\b(?:acoes?|administrativas?|atividade|atividades|operacoes?)\b/.test(plain)) return {type:'activity'}; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    if (/^(?:(?:mostra|mostre|liste|listar|consultar?|ver|veja|quais|tenho|quero ver|me passe|me mostra|me mostre)\s+)?(?:(?:as|minhas|meus|os|a)\s+)?(?:tarefas?|atividades?)(?:\s+(?:pendentes?|em andamento|da fila|do jarvis|cadastradas?))?[.!?]?$/.test(plain) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        || /\b(?:minhas tarefas|fila de tarefas|fila de atividades)\b/.test(plain)) return {type:'task_list'}; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    // Uma mudança de estado requer ID, prévia, clique e confirmação humana. Nunca é executada no parser.
    const transition = /^(conclua|concluir|finalize|finalizar|termine|terminar|inicie|iniciar|comece|comecar|cancele|cancelar)\s+(?:(?:a|o|essa|esta)\s+)?(?:tarefa|atividade)\s+(?:(?:id|n[ou]mero|#)\s*)?(\d{1,9})[.!?]?$/.exec(plain); // Explicação DevIN: Declara variável JavaScript transition para reutilizar valor ou resultado.
    if (transition) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      const verb=transition[1]; // Explicação DevIN: Declara variável JavaScript verb para reutilizar valor ou resultado.
      const action=/^(?:inicie|iniciar|comece|comecar)/.test(verb)?'start': // Explicação DevIN: Declara variável JavaScript action para reutilizar valor ou resultado.
        /^(?:cancele|cancelar)/.test(verb)?'cancel':'complete'; // Explicação DevIN: Continua expressão ou bloco pertencente a função classify.
      return {type:'task_update',action,id:Number(transition[2])}; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if (/^(?:conclua|finalize|termine|inicie|comece|cancele)\s+(?:(?:a|o)\s+)?(?:tarefa|atividade)\b/.test(plain)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      return {type:'incomplete',message:'Informe o número da tarefa. Exemplo: "Conclua a tarefa #12".'}; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    const create=/^(?:crie|criar|adicione|adicionar|registre|registrar|coloque)\s+(?:(?:uma|nova|um)\s+)?(?:tarefa|atividade)\s*(?:(?:para|de|chamada|intitulada|com titulo)\s+)?(.+)$/i.exec(input); // Explicação DevIN: Declara variável JavaScript create para reutilizar valor ou resultado.
    if (create && create[1].trim()) return {type:'task_create',title:create[1].trim().slice(0,120)}; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

    // Ações sobre cadastro são sempre submetidas ao fluxo de escolha e confirmação já existente.
    const actionVerb='(?:exclua|excluir|delete|deletar|remova|remover|apague|apagar|elimine|eliminar)'; // Explicação DevIN: Declara variável JavaScript actionVerb para reutilizar valor ou resultado.
    const action = new RegExp('^'+actionVerb+'\\s+(?:(?:a|o|uma|um|as|os)\\s+)?'+kinds+'(?:\\s+(.+))?$','i').exec(input); // Explicação DevIN: Declara variável JavaScript action para reutilizar valor ou resultado.
    if (action) return action[2] ? {type:'delete',...target(action[2],kindOf(action[1]))} : // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      {type:'incomplete',message:'Informe o nome ou ID do registro antes de preparar uma exclusão.'}; // Explicação DevIN: Continua expressão ou bloco pertencente a função classify.
    if (new RegExp('^'+actionVerb+'\\b','i').test(input)) return null; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

    // Não enviar nenhuma mensagem: apenas preencher o rascunho de revisão (outbox).
    const messageVerb='(?:envie|enviar|mande|mandar|avise|avisar|comunique|comunicar|escreva|escrever|prepare|preparar|redija|redigir)'; // Explicação DevIN: Declara variável JavaScript messageVerb para reutilizar valor ou resultado.
    const messagePrefix = new RegExp('^'+messageVerb+'\\s+(?:(?:uma|a|um)\\s+)?(?:mensagem|recado|aviso|comunicado|texto)\\s+(?:(?:para|a|ao|aos|a\\u0300)\\s+)?(?:(?:a|o|ao|a\\u0300)\\s+)?(.+)$','i').exec(input); // Explicação DevIN: Declara variável JavaScript messagePrefix para reutilizar valor ou resultado.
    const directNotice = /^(?:avise|avisar|comunique|comunicar|notifique|notificar)\s+(?:(?:a|o|ao|à)\s+)?(.+)$/i.exec(input); // Explicação DevIN: Declara variável JavaScript directNotice para reutilizar valor ou resultado.
    const selectedMessage = messagePrefix ? messagePrefix[1] : (directNotice ? directNotice[1] : null); // Explicação DevIN: Declara variável JavaScript selectedMessage para reutilizar valor ou resultado.
    if (selectedMessage) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      const pair = /^(.+?)\s+(?:dizendo|falando|que diga|com\s+(?:o\s+)?texto|com\s+(?:a\s+)?mensagem)\s*[:,-]?\s*([\s\S]+)$/i.exec(selectedMessage) // Explicação DevIN: Declara variável JavaScript pair para reutilizar valor ou resultado.
        || /^(.+?)\s*:\s*([\s\S]+)$/.exec(selectedMessage); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
      const destination=(pair ? pair[1] : selectedMessage).trim(); // Explicação DevIN: Declara variável JavaScript destination para reutilizar valor ou resultado.
      const category = new RegExp('^'+kinds+'\\s+(.+)$','i').exec(destination); // Explicação DevIN: Declara variável JavaScript category para reutilizar valor ou resultado.
      const recipient = category ? target(category[2],kindOf(category[1])) : target(destination,null); // Explicação DevIN: Declara variável JavaScript recipient para reutilizar valor ou resultado.
      return {type:recipient.kind==='vaga'?'unsupported':'message',...recipient,body:pair?pair[2].trim():''}; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Busca por nome/ID, detalhes e listagem das 12 correspondências mais recentes permitidas.
    const readPrefix=/^(?:procure|procurar|pesquise|pesquisar|busque|buscar|encontre|encontrar|localize|localizar|mostra|mostre|mostrar|liste|listar|veja|ver|consulte|consultar|ache|achar|exiba|exibir|quero ver|quero achar|quero encontrar|me mostra|me mostre|me passe|traga)\s+/i; // Explicação DevIN: Declara variável JavaScript readPrefix para reutilizar valor ou resultado.
    const read=readPrefix.exec(input); // Explicação DevIN: Declara variável JavaScript read para reutilizar valor ou resultado.
    const rest=read ? input.slice(read[0].length).trim() : input; // Explicação DevIN: Declara variável JavaScript rest para reutilizar valor ou resultado.
    const category = new RegExp('^(?:(?:a|o|as|os|uma|um|todas as|todos os)\\s+)?'+kinds+'(?:\\s+(.+))?$','i').exec(rest); // Explicação DevIN: Declara variável JavaScript category para reutilizar valor ou resultado.
    const explicitQuestion=/^(?:quais|quantas?|tem|existe|existem|ha)\s+(?:(?:as|os|alguma|algum|uma|um|todas|todos)\s+)?/i.exec(input); // Explicação DevIN: Declara variável JavaScript explicitQuestion para reutilizar valor ou resultado.
    const questionRest=explicitQuestion?input.slice(explicitQuestion[0].length):''; // Explicação DevIN: Declara variável JavaScript questionRest para reutilizar valor ou resultado.
    const qCategory = explicitQuestion ? new RegExp('^'+kinds+'(?:\\s+(.+))?$','i').exec(questionRest):null; // Explicação DevIN: Declara variável JavaScript qCategory para reutilizar valor ou resultado.
    const picked = category || qCategory; // Explicação DevIN: Declara variável JavaScript picked para reutilizar valor ou resultado.
    if (picked) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      const kind=kindOf(picked[1]); // Explicação DevIN: Declara variável JavaScript kind para reutilizar valor ou resultado.
      const tail=String(picked[2] || '').trim().replace(/^(?:com\s+(?:o\s+)?nome|de\s+nome|chamad[oa]s?|intitulad[oa]s?|de\s+|por\s+|que\s+se\s+chama)\s+/i,'').trim(); // Explicação DevIN: Declara variável JavaScript tail para reutilizar valor ou resultado.
      if (isListingTail(tail)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        return {type:'browse',kind}; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
      } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      // Evitar interpretar condições complexas como se o backend possuísse filtros arbitrários.
      if (/\b(?:com salario|acima de|mais que|por cidade|perto de|filtrar por)\b/i.test(normalized(tail))) return null; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      return {type:'search',...target(tail,kind)}; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    // Busca por nome da pessoa ou empresa sem indicar categoria conserva a seleção humana.
    if (read && !/\b(?:todos?|todas?|cadastrad[oa]s?)\b/i.test(normalized(rest)) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        && rest.length >= 2 && rest.length <= 100) return {type:'search',...target(rest,null)}; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    return null; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  const api=Object.freeze({classify,normalized}); // Explicação DevIN: Declara variável JavaScript api para reutilizar valor ou resultado.
  scope.DevInJarvisAdminNatural=api; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
  if (typeof module!=='undefined' && module.exports) module.exports=api; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
})(typeof window!=='undefined' ? window : globalThis); // Explicação DevIN: Continua expressão ou bloco pertencente a função classify.
