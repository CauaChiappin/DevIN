/* Interpretação local de intenções administrativas, sem dependência de IA remota. */
(function (scope) { // Explicação DevIN: Define função anônima usada como operação ou callback.
  'use strict'; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.

  // A interpretação é deliberadamente restrita: nunca gera SQL, IDs ou ordens de mutação.
  function normalize(value) { // Explicação DevIN: Declara função/método normalize, com seus parâmetros e comportamento.
    return String(value ?? '').normalize('NFD').replace(/[\u0300-\u036f]/g, '') // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
      .toLowerCase().replace(/[^\p{L}\p{N}#]+/gu, ' ').replace(/\s+/g, ' ').trim(); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

  function distance(a, b, maximum = 1) { // Explicação DevIN: Declara função/método distance, com seus parâmetros e comportamento.
    if (Math.abs(a.length - b.length) > maximum) return maximum + 1; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    let previous = Array.from({length: b.length + 1}, (_, i) => i); // Explicação DevIN: Declara variável JavaScript previous para reutilizar valor ou resultado.
    for (let i = 1; i <= a.length; i++) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
      const current = [i]; // Explicação DevIN: Declara variável JavaScript current para reutilizar valor ou resultado.
      for (let j = 1; j <= b.length; j++) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
        current[j] = Math.min(current[j - 1] + 1, previous[j] + 1, // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
          previous[j - 1] + (a[i - 1] === b[j - 1] ? 0 : 1)); // Explicação DevIN: Continua expressão ou bloco pertencente a função distance.
      } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      if (Math.min(...current) > maximum) return maximum + 1; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      previous = current; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    return previous[b.length]; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

  // Corrige só termos de domínio conhecidos; nomes de pessoas e empresas são intocados.
  const corrections = { // Explicação DevIN: Declara variável JavaScript corrections para reutilizar valor ou resultado.
    suporte: ['suporte', 'suportes', 'suport', 'supote', 'suports'], // Explicação DevIN: Continua expressão ou bloco pertencente a função distance.
    chamado: ['chamado', 'chamados', 'chamdo', 'chamdos', 'chamda'], // Explicação DevIN: Continua expressão ou bloco pertencente a função distance.
    atendimento: ['atendimento', 'atendimentos', 'atendimeto', 'atendimetos', 'atend'], // Explicação DevIN: Continua expressão ou bloco pertencente a função distance.
    ticket: ['ticket', 'tickets', 'tiket', 'tikets'], // Explicação DevIN: Continua expressão ou bloco pertencente a função distance.
    denuncia: ['denuncia', 'denuncias', 'denuncas', 'denunciaa'], // Explicação DevIN: Continua expressão ou bloco pertencente a função distance.
    report: ['report', 'reports', 'reporte', 'reportes'], // Explicação DevIN: Continua expressão ou bloco pertencente a função distance.
    jurisdicao: ['jurisdicao', 'jurisdicoes', 'jurisdiscao'], // Explicação DevIN: Continua expressão ou bloco pertencente a função distance.
    representante: ['representante', 'representantes', 'represntante', 'representates', 'represento', 'representando'], // Explicação DevIN: Continua expressão ou bloco pertencente a função distance.
    responsabilidade: ['responsabilidade', 'responsabilidades'], // Explicação DevIN: Continua expressão ou bloco pertencente a função distance.
    atribuido: ['atribuido', 'atribuidos', 'atribuida', 'atribuidas', 'atribuid', 'designado', 'designados', 'designada', 'designadas'], // Explicação DevIN: Continua expressão ou bloco pertencente a função distance.
  }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  const safeFuzzy = ['suporte', 'chamado', 'atendimento', 'ticket', 'denuncia', 'report']; // Explicação DevIN: Declara variável JavaScript safeFuzzy para reutilizar valor ou resultado.
  function canonical(word) { // Explicação DevIN: Declara função/método canonical, com seus parâmetros e comportamento.
    for (const [key, aliases] of Object.entries(corrections)) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
      if (aliases.includes(word)) return key; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if (word.length < 5) return word; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    const candidates = safeFuzzy.filter(key => distance(word, key, 1) <= 1); // Explicação DevIN: Declara variável JavaScript candidates para reutilizar valor ou resultado.
    return candidates.length === 1 ? candidates[0] : word; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  function tokens(value) { // Explicação DevIN: Declara função/método tokens, com seus parâmetros e comportamento.
    return normalize(value).split(' ').filter(Boolean).map(canonical); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  function classify(value, options = {}) { // Explicação DevIN: Declara função/método classify, com seus parâmetros e comportamento.
    const original = normalize(value); // Explicação DevIN: Declara variável JavaScript original para reutilizar valor ou resultado.
    if (!original || original.length > 500) return null; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    const words = tokens(value); // Explicação DevIN: Declara variável JavaScript words para reutilizar valor ou resultado.
    const word = (...choices) => words.some(term => choices.includes(term)); // Explicação DevIN: Declara variável JavaScript word para reutilizar valor ou resultado.
    const text = words.join(' '); // Explicação DevIN: Declara variável JavaScript text para reutilizar valor ou resultado.
    const hasSupport = word('suporte','chamado','atendimento','ticket','helpdesk','ocorrencia','ocorrencias','solicitacao','solicitacoes','reclamacao','reclamacoes'); // Explicação DevIN: Declara variável JavaScript hasSupport para reutilizar valor ou resultado.
    const hasReport = word('denuncia','report','sinalizacao','infracao','moderacao'); // Explicação DevIN: Declara variável JavaScript hasReport para reutilizar valor ou resultado.
    const my = word('meu','meus','minha','minhas','mim','eu','sou','estou','to','tenho','cuido','cuidando','responsavel', // Explicação DevIN: Declara variável JavaScript my para reutilizar valor ou resultado.
      'representante','responsabilidade','jurisdicao','atribuido','carteira','assumi','assumidos','atendo','atendendo','cuidados','designado') // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
      || /\b(pra mim|para mim|sob meus cuidados|a meu cargo|sob minha conta|comigo)\b/.test(text); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    const assigned = /\b(tenho para resolver|tenho que atender|tenho pra resolver|me compete)\b/.test(text) || word('jurisdicao','representante','responsavel','responsabilidade','atribuido','carteira','assumi', // Explicação DevIN: Declara variável JavaScript assigned para reutilizar valor ou resultado.
      'assumidos','atendo','atendendo','designado','cuidando','cuido','acompanho','acompanhando','peguei','tomei') || /\b(estou atendendo|to atendendo|to cuidando|eu atendo|eu cuido|meu atendimento|meus atendimentos|pra mim|para mim|comigo|sob meus cuidados)\b/.test(text); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    const read = word('qual','quais','quantos','quantas','mostra','mostre','mostrar','liste','lista','listar', // Explicação DevIN: Declara variável JavaScript read para reutilizar valor ou resultado.
      'veja','ver','consulte','consultar','quero','preciso','quem','procure','buscar','busque','encontre','tenho','tem', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
      'estao','estou','fiquei','sao','abertos','pendentes','acompanho','cuidando','abrir','abra', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
      'abre','detalhe','detalhes','visualizar','exiba','exibir','puxa','puxar','traga','traz','fila','ajuda','ajude','ajudar') // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
      || /\b(da uma olhada|me passa|me diz|pra mim|para mim)\b/.test(text); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    const destructive = word('exclua','excluir','delete','deletar','remova','remover','apague','apagar', // Explicação DevIN: Declara variável JavaScript destructive para reutilizar valor ou resultado.
      'encerre','encerrar','feche','fechar','publique','aprovar','rejeitar','reprove','responda','enviar'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    const explanation = /\b(como funciona|como fazer|o que e|o que significa|explique|exemplos de|melhorar o|conceito de|tirar duvidas|tenho duvida sobre|dicas para)\b/.test(text); // Explicação DevIN: Declara variável JavaScript explanation para reutilizar valor ou resultado.
    const activeCase = options.activeCase || null; // Explicação DevIN: Declara variável JavaScript activeCase para reutilizar valor ou resultado.
    const isAnalysis = word('analise','analisar','analisa','analisei','avalie','avaliar','investigue','investigar', // Explicação DevIN: Declara variável JavaScript isAnalysis para reutilizar valor ou resultado.
      'parecer','veredito','evidencia','evidencias','redija','redigir','escreva','escrever', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
      'resposta','responder','sugestao','sugira','ajude','ajuda','print','imagem','interprete','interpretar','investigacao'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    const wantsDraft = word('redija','redigir','escreva','escrever','parecer','veredito','resposta','responder','sugestao','sugira'); // Explicação DevIN: Declara variável JavaScript wantsDraft para reutilizar valor ou resultado.

    // Operações de vagas/contas usam o parser de segurança existente, nunca este roteador.
    if (destructive && !hasSupport && !hasReport) return null; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    // Consultas abstratas são conversas; não abrir uma fila por mencionar "suporte".
    if (explanation && !assigned && !/\b(id|numero|#)\b/.test(text)) return null; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

    const kindByContext = word('perfil','perfis') ? 'profile_report' // Explicação DevIN: Declara variável JavaScript kindByContext para reutilizar valor ou resultado.
      : word('vaga','vagas') && hasReport ? 'job_report' : null; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    const idMatch = /(?:^| )(?:#|id |numero |n |nro )(\d{1,9})(?: |$)/.exec(text) // Explicação DevIN: Declara variável JavaScript idMatch para reutilizar valor ou resultado.
      || /(?:^| )(?:chamado|suporte|atendimento|ticket|denuncia|report) (\d{1,9})(?: |$)/.exec(text); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    if (idMatch && (hasSupport || hasReport)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      if (hasSupport && !hasReport) return {type: 'detail', caseType: 'support', id: Number(idMatch[1])}; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      if (hasReport && kindByContext) return {type: 'detail', caseType: kindByContext, id: Number(idMatch[1])}; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      return {type:'clarify', message:'Esse número pode identificar diferentes tipos de casos. É suporte, denúncia de vaga ou denúncia de perfil?'}; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Pedido de análise sobre o caso já selecionado continua protegido por consentimento.
    if (activeCase && isAnalysis && !(assigned && my) && (!hasReport || /\b(este|esta|essa|esse|atual|caso)\b/.test(text)) && !/\b(todos|todas|liste|listar|outros|outras|quais)\b/.test(text) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      && (!hasSupport || !my || /\b(este|esse|deste|desse|atual|caso)\b/.test(text))) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
      return {type: 'analyze', draft: Boolean(wantsDraft)}; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Denúncias explícitas não possuem titular individual no banco atual.
    if (hasReport && word('denuncia') && !hasSupport && assigned && my) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      return {type:'clarify', message:'Atribuição individual existe para chamados de suporte; denúncias são revisadas pela equipe conforme permissão. Quer ver os chamados que representa ou as denúncias pendentes?'}; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    // "Meus reports na minha jurisdição" historicamente significam suporte representado.
    if (assigned && my && (hasSupport || hasReport || word('caso','casos','pendencia','pendencias','demanda','demandas') || word('jurisdicao'))) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      return {type:'list', filter:'mine'}; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if (hasSupport && (read || words.length <= 2 || word('todos','todas','meus','minhas') // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      || /^(?:os |as )?(?:chamado|suporte|atendimento|ticket)(?: de (?:suporte|atendimento))?$/.test(text))) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
      return {type:'list', filter: my && (assigned || word('meu','meus','minha','minhas')) ? 'mine' : 'support'}; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if (hasReport && (read || words.length <= 2 || word('todos','todas'))) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      return {type:'list', filter:'report'}; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if (assigned && my && read) return {type:'list', filter:'mine'}; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    if (my && (word('pendencia','pendencias','demanda','demandas')) && (read || words.length <= 3)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      return {type:'clarify', message:'Você quer ver os chamados que representa, a fila geral ou as denúncias pendentes?'}; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    const stats = word('estatistica','estatisticas','indicador','indicadores','numeros','resumo','painel') // Explicação DevIN: Declara variável JavaScript stats para reutilizar valor ou resultado.
      || (word('quantos','quantas','total','contagem') && word('empresa','empresas','pessoa','pessoas', // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        'vaga','vagas','candidatura','candidaturas','contratacao','contratacoes','usuarios','candidatos')); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    if (stats && !destructive && !explanation) return {type:'overview'}; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    if ((hasSupport || hasReport) && destructive) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      return {type:'clarify', message:'Para responder, encerrar ou decidir sobre um caso, primeiro selecione-o na lista. A ação exigirá sua confirmação.'}; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if ((hasSupport || hasReport) && !explanation && read) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      return {type:'clarify', message:'Você quer consultar seus atendimentos, a fila geral de suporte ou as denúncias?'}; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    return null; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

  const api = Object.freeze({normalize, classify}); // Explicação DevIN: Declara variável JavaScript api para reutilizar valor ou resultado.
  scope.DevInJarvisIntents = api; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
  if (typeof module !== 'undefined' && module.exports) module.exports = api; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
})(typeof window !== 'undefined' ? window : globalThis); // Explicação DevIN: Continua expressão ou bloco pertencente a função classify.
