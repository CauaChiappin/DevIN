/* Complemento local do JARVIS: intenções administrativas apoiadas por ferramentas reais. */
(function(scope) {
  'use strict';

  function normalized(text) {
    return String(text || '').normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase()
      .replace(/\s+/g, ' ').trim();
  }
  function stripPrefix(text) {
    return String(text || '').trim().replace(/^(?:(?:jarvis|jarvisdevin)[\s,:!.-]*|(?:por favor|pfv)[\s,:!.-]*)+/i,'').trim();
  }
  const names = {
    vaga: /^(?:vagas?|an[úu]ncios?|oportunidades?)$/i,
    empresa: /^(?:empresas?|empregadores?)$/i,
    pessoa: /^(?:candidatos?|candidatas?|pessoas?|usu[áa]rios?|usu[áa]rias?|perfis?)$/i,
  };
  const kinds = '(vagas?|an[úu]ncios?|oportunidades?|empresas?|empregadores?|candidatos?|candidatas?|pessoas?|usu[áa]rios?|usu[áa]rias?|perfis?)';

  function kindOf(raw) {
    return Object.keys(names).find(k => names[k].test(raw || '')) || null;
  }
  function target(raw, kind) {
    const value = String(raw || '').trim().replace(/^[#:]\s*/, '').replace(/[.!?]+$/,'').trim()
      .replace(/^(?:com o nome|de nome|intitulado|intitulada|chamado|chamada)\s+/i,'');
    if (!value) return {kind,id:null,name:''};
    const id = /^(?:(?:id|n[úu]mero|n[ºo])\s*[#:=-]?\s*)?(\d{1,9})$/i.exec(value);
    return id ? {kind,id:Number(id[1]),name:''} : {kind,id:null,name:value.slice(0,100)};
  }
  function classify(message) {
    if (typeof message !== 'string' || !message.trim() || message.length > 1400) return null;
    const input = stripPrefix(message);
    const plain = normalized(input);
    if (/\b(como funciona|como fazer|o que significa|o que e|explique|tutorial|como posso)\b/.test(plain)
        && !/^(?:me mostre|mostre|liste|consulte)\b/.test(plain)) return null;

    // Logs são apenas leitura. Mensagens oficiais e auditoria já possuem endpoints autenticados.
    if (/\b(?:historico|registro|registros|log|logs)\b/.test(plain)
        && /\b(?:conversas?|chats?|mensagens? anteriores)\b/.test(plain)) return {type:'conversation_history'};
    if (/\b(?:rascunhos?|mensagens? preparadas?|mensagens? enviadas?|caixa de saida)\b/.test(plain)
        && !/^(?:envie|mande|avise|comunique|escreva)/.test(plain)) return {type:'drafts'};
    if (/\b(?:historico|registro|registros|log|logs|auditoria)\b/.test(plain)
        && /\b(?:acoes?|administrativas?|atividade|atividades|operacoes?)\b/.test(plain)) return {type:'activity'};
    if (/^(?:(?:mostra|mostre|liste|listar|consultar?|ver|veja|quais|tenho|quero ver|me passe|me mostra)\s+)?(?:(?:as|minhas|meus|os|a)\s+)?(?:tarefas?|atividades?)(?:\s+(?:pendentes?|em andamento|da fila|do jarvis|cadastradas?))?[.!?]?$/.test(plain)
        || /\b(?:minhas tarefas|fila de tarefas|fila de atividades)\b/.test(plain)) return {type:'task_list'};
    // Uma mudança de estado requer ID, prévia, clique e confirmação humana. Nunca é executada no parser.
    const transition = /^(conclua|concluir|finalize|finalizar|termine|terminar|inicie|iniciar|comece|comecar|cancele|cancelar)\s+(?:(?:a|o|essa|esta)\s+)?(?:tarefa|atividade)\s+(?:(?:id|n[ou]mero|#)\s*)?(\d{1,9})[.!?]?$/.exec(plain);
    if (transition) {
      const verb=transition[1];
      const action=/^(?:inicie|iniciar|comece|comecar)/.test(verb)?'start':
        /^(?:cancele|cancelar)/.test(verb)?'cancel':'complete';
      return {type:'task_update',action,id:Number(transition[2])};
    }
    if (/^(?:conclua|finalize|termine|inicie|comece|cancele)\s+(?:(?:a|o)\s+)?(?:tarefa|atividade)\b/.test(plain)) {
      return {type:'incomplete',message:'Informe o número da tarefa. Exemplo: "Conclua a tarefa #12".'};
    }
    const create=/^(?:crie|criar|adicione|adicionar|registre|registrar|coloque)\s+(?:(?:uma|nova|um)\s+)?(?:tarefa|atividade)\s*(?:(?:para|de|chamada|intitulada|com titulo)\s+)?(.+)$/i.exec(input);
    if (create && create[1].trim()) return {type:'task_create',title:create[1].trim().slice(0,120)};

    // Ações sobre cadastro são sempre submetidas ao fluxo de escolha e confirmação já existente.
    const actionVerb='(?:exclua|excluir|delete|deletar|remova|remover|apague|apagar|elimine|eliminar)';
    const action = new RegExp('^'+actionVerb+'\\s+(?:(?:a|o|uma|um|as|os)\\s+)?'+kinds+'(?:\\s+(.+))?$','i').exec(input);
    if (action) return action[2] ? {type:'delete',...target(action[2],kindOf(action[1]))} :
      {type:'incomplete',message:'Informe o nome ou ID do registro antes de preparar uma exclusão.'};
    if (new RegExp('^'+actionVerb+'\\b','i').test(input)) return null;

    // Não enviar nenhuma mensagem: apenas preencher o rascunho de revisão (outbox).
    const messageVerb='(?:envie|enviar|mande|mandar|avise|avisar|comunique|comunicar|escreva|escrever|prepare|preparar|redija|redigir)';
    const messagePrefix = new RegExp('^'+messageVerb+'\\s+(?:(?:uma|a|um)\\s+)?(?:mensagem|recado|aviso|comunicado|texto)\\s+(?:(?:para|a|ao|aos|a\\u0300)\\s+)?(?:(?:a|o|ao|a\\u0300)\\s+)?(.+)$','i').exec(input);
    if (messagePrefix) {
      const pair = /^(.+?)\s+(?:dizendo|falando|que diga|com\s+(?:o\s+)?texto|com\s+(?:a\s+)?mensagem)\s*[:,-]?\s*([\s\S]+)$/i.exec(messagePrefix[1])
        || /^(.+?)\s*:\s*([\s\S]+)$/.exec(messagePrefix[1]);
      const destination=(pair ? pair[1] : messagePrefix[1]).trim();
      const category = new RegExp('^'+kinds+'\\s+(.+)$','i').exec(destination);
      const recipient = category ? target(category[2],kindOf(category[1])) : target(destination,null);
      return {type:recipient.kind==='vaga'?'unsupported':'message',...recipient,body:pair?pair[2].trim():''};
    }

    // Busca por nome/ID, detalhes e listagem das 12 correspondências mais recentes permitidas.
    const readPrefix=/^(?:procure|procurar|pesquise|pesquisar|busque|buscar|encontre|encontrar|localize|localizar|mostra|mostre|mostrar|liste|listar|veja|ver|consulte|consultar|ache|achar|exiba|exibir|quero ver|quero achar|quero encontrar|me mostra|me mostre|me passe|traga)\s+/i;
    const read=readPrefix.exec(input);
    const rest=read ? input.slice(read[0].length).trim() : input;
    const category = new RegExp('^(?:(?:a|o|as|os|uma|um|todas as|todos os)\\s+)?'+kinds+'(?:\\s+(.+))?$','i').exec(rest);
    const explicitQuestion=/^(?:quais|quantas?|tem|existe|existem|ha)\s+(?:(?:as|os|alguma|algum|uma|um|todas|todos)\s+)?/i.exec(input);
    const questionRest=explicitQuestion?input.slice(explicitQuestion[0].length):'';
    const qCategory = explicitQuestion ? new RegExp('^'+kinds+'(?:\\s+(.+))?$','i').exec(questionRest):null;
    const picked = category || qCategory;
    if (picked) {
      const kind=kindOf(picked[1]);
      const tail=String(picked[2] || '').trim().replace(/^(?:com\s+(?:o\s+)?nome|de\s+nome|chamad[oa]s?|intitulad[oa]s?|de\s+|por\s+|que\s+se\s+chama)\s+/i,'').trim();
      if (!tail || /^(?:disponiveis?|cadastrad[oa]s?|existentes?|publicad[oa]s?|ativos?|ativas?|recentes?|do site|da plataforma)\s*[.!?]?$/i.test(normalized(tail))) {
        return {type:'browse',kind};
      }
      // Evitar interpretar condições complexas como se o backend possuísse filtros arbitrários.
      if (/\b(?:com salario|acima de|mais que|por cidade|perto de|filtrar por)\b/i.test(normalized(tail))) return null;
      return {type:'search',...target(tail,kind)};
    }
    // Busca por nome da pessoa ou empresa sem indicar categoria conserva a seleção humana.
    if (read && !/\b(?:todos?|todas?|cadastrad[oa]s?)\b/i.test(normalized(rest))
        && rest.length >= 2 && rest.length <= 100) return {type:'search',...target(rest,null)};
    return null;
  }
  const api=Object.freeze({classify,normalized});
  scope.DevInJarvisAdminNatural=api;
  if (typeof module!=='undefined' && module.exports) module.exports=api;
})(typeof window!=='undefined' ? window : globalThis);
