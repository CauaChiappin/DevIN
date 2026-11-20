/* Interpretação local de intenções administrativas, sem dependência de IA remota. */
(function (scope) {
  'use strict';

  // A interpretação é deliberadamente restrita: nunca gera SQL, IDs ou ordens de mutação.
  function normalize(value) {
    return String(value ?? '').normalize('NFD').replace(/[\u0300-\u036f]/g, '')
      .toLowerCase().replace(/[^\p{L}\p{N}#]+/gu, ' ').replace(/\s+/g, ' ').trim();
  }

  function distance(a, b, maximum = 1) {
    if (Math.abs(a.length - b.length) > maximum) return maximum + 1;
    let previous = Array.from({length: b.length + 1}, (_, i) => i);
    for (let i = 1; i <= a.length; i++) {
      const current = [i];
      for (let j = 1; j <= b.length; j++) {
        current[j] = Math.min(current[j - 1] + 1, previous[j] + 1,
          previous[j - 1] + (a[i - 1] === b[j - 1] ? 0 : 1));
      }
      if (Math.min(...current) > maximum) return maximum + 1;
      previous = current;
    }
    return previous[b.length];
  }

  // Corrige só termos de domínio conhecidos; nomes de pessoas e empresas são intocados.
  const corrections = {
    suporte: ['suporte', 'suportes', 'suport', 'supote', 'suports'],
    chamado: ['chamado', 'chamados', 'chamdo', 'chamdos', 'chamda'],
    atendimento: ['atendimento', 'atendimentos', 'atendimeto', 'atendimetos', 'atend'],
    ticket: ['ticket', 'tickets', 'tiket', 'tikets'],
    denuncia: ['denuncia', 'denuncias', 'denuncas', 'denunciaa'],
    report: ['report', 'reports', 'reporte', 'reportes'],
    jurisdicao: ['jurisdicao', 'jurisdicoes', 'jurisdiscao'],
    representante: ['representante', 'representantes', 'represntante', 'representates', 'represento', 'representando'],
    responsabilidade: ['responsabilidade', 'responsabilidades'],
    atribuido: ['atribuido', 'atribuidos', 'atribuida', 'atribuidas', 'atribuid', 'designado', 'designados', 'designada', 'designadas'],
  };
  const safeFuzzy = ['suporte', 'chamado', 'atendimento', 'ticket', 'denuncia', 'report'];
  function canonical(word) {
    for (const [key, aliases] of Object.entries(corrections)) {
      if (aliases.includes(word)) return key;
    }
    if (word.length < 5) return word;
    const candidates = safeFuzzy.filter(key => distance(word, key, 1) <= 1);
    return candidates.length === 1 ? candidates[0] : word;
  }
  function tokens(value) {
    return normalize(value).split(' ').filter(Boolean).map(canonical);
  }
  function classify(value, options = {}) {
    const original = normalize(value);
    if (!original || original.length > 500) return null;
    const words = tokens(value);
    const word = (...choices) => words.some(term => choices.includes(term));
    const text = words.join(' ');
    const hasSupport = word('suporte','chamado','atendimento','ticket','helpdesk','ocorrencia','ocorrencias','solicitacao','solicitacoes','reclamacao','reclamacoes');
    const hasReport = word('denuncia','report','sinalizacao','infracao','moderacao');
    const my = word('meu','meus','minha','minhas','mim','eu','sou','estou','to','tenho','cuido','cuidando','responsavel',
      'representante','responsabilidade','jurisdicao','atribuido','carteira','assumi','assumidos','atendo','atendendo','cuidados','designado')
      || /\b(pra mim|para mim|sob meus cuidados|a meu cargo|sob minha conta|comigo)\b/.test(text);
    const assigned = /\b(tenho para resolver|tenho que atender|tenho pra resolver|me compete)\b/.test(text) || word('jurisdicao','representante','responsavel','responsabilidade','atribuido','carteira','assumi',
      'assumidos','atendo','atendendo','designado','cuidando','cuido','acompanho','acompanhando','peguei','tomei') || /\b(estou atendendo|to atendendo|to cuidando|eu atendo|eu cuido|meu atendimento|meus atendimentos|pra mim|para mim|comigo|sob meus cuidados)\b/.test(text);
    const read = word('qual','quais','quantos','quantas','mostra','mostre','mostrar','liste','lista','listar',
      'veja','ver','consulte','consultar','quero','preciso','quem','procure','buscar','busque','encontre','tenho','tem',
      'estao','estou','fiquei','sao','abertos','pendentes','acompanho','cuidando','abrir','abra',
      'abre','detalhe','detalhes','visualizar','exiba','exibir','puxa','puxar','ajuda','ajude','ajudar')
      || /\b(da uma olhada|me passa|me diz|pra mim|para mim)\b/.test(text);
    const destructive = word('exclua','excluir','delete','deletar','remova','remover','apague','apagar',
      'encerre','encerrar','feche','fechar','publique','aprovar','rejeitar','reprove','responda','enviar');
    const explanation = /\b(como funciona|como fazer|o que e|o que significa|explique|exemplos de|melhorar o|conceito de|tirar duvidas|tenho duvida sobre|dicas para)\b/.test(text);
    const activeCase = options.activeCase || null;
    const isAnalysis = word('analise','analisar','analisa','analisei','avalie','avaliar','investigue','investigar',
      'parecer','veredito','evidencia','evidencias','redija','redigir','escreva','escrever',
      'resposta','responder','sugestao','sugira','ajude','ajuda','print','imagem','interprete','interpretar','investigacao');
    const wantsDraft = word('redija','redigir','escreva','escrever','parecer','veredito','resposta','responder','sugestao','sugira');

    // Operações de vagas/contas usam o parser de segurança existente, nunca este roteador.
    if (destructive && !hasSupport && !hasReport) return null;
    // Consultas abstratas são conversas; não abrir uma fila por mencionar "suporte".
    if (explanation && !assigned && !/\b(id|numero|#)\b/.test(text)) return null;

    const kindByContext = word('perfil','perfis') ? 'profile_report'
      : word('vaga','vagas') && hasReport ? 'job_report' : null;
    const idMatch = /(?:^| )(?:#|id |numero |n |nro )(\d{1,9})(?: |$)/.exec(text)
      || /(?:^| )(?:chamado|suporte|atendimento|ticket|denuncia|report) (\d{1,9})(?: |$)/.exec(text);
    if (idMatch && (hasSupport || hasReport)) {
      if (hasSupport && !hasReport) return {type: 'detail', caseType: 'support', id: Number(idMatch[1])};
      if (hasReport && kindByContext) return {type: 'detail', caseType: kindByContext, id: Number(idMatch[1])};
      return {type:'clarify', message:'Esse número pode identificar diferentes tipos de casos. É suporte, denúncia de vaga ou denúncia de perfil?'};
    }

    // Pedido de análise sobre o caso já selecionado continua protegido por consentimento.
    if (activeCase && isAnalysis && !/\b(todos|todas|liste|listar|outros|outras|quais)\b/.test(text)
      && (!hasSupport || !my || /\b(este|esse|deste|desse|atual|caso)\b/.test(text))) {
      return {type: 'analyze', draft: Boolean(wantsDraft)};
    }

    // Denúncias explícitas não possuem titular individual no banco atual.
    if (hasReport && word('denuncia') && !hasSupport && assigned && my) {
      return {type:'clarify', message:'Atribuição individual existe para chamados de suporte; denúncias são revisadas pela equipe conforme permissão. Quer ver os chamados que representa ou as denúncias pendentes?'};
    }
    // "Meus reports na minha jurisdição" historicamente significam suporte representado.
    if (assigned && my && (hasSupport || hasReport || word('caso','casos','pendencia','pendencias','demanda','demandas') || word('jurisdicao'))) {
      return {type:'list', filter:'mine'};
    }
    if (hasSupport && (read || words.length <= 2 || word('todos','todas','meus','minhas'))) {
      return {type:'list', filter: my && (assigned || word('meu','meus','minha','minhas')) ? 'mine' : 'support'};
    }
    if (hasReport && (read || words.length <= 2 || word('todos','todas'))) {
      return {type:'list', filter:'report'};
    }
    if (assigned && my && read) return {type:'list', filter:'mine'};
    if (my && (word('pendencia','pendencias','demanda','demandas')) && (read || words.length <= 3)) {
      return {type:'clarify', message:'Você quer ver os chamados que representa, a fila geral ou as denúncias pendentes?'};
    }

    const stats = word('estatistica','estatisticas','indicador','indicadores','numeros','resumo','painel')
      || (word('quantos','quantas','total','contagem') && word('empresa','empresas','pessoa','pessoas',
        'vaga','vagas','candidatura','candidaturas','contratacao','contratacoes','usuarios','candidatos'));
    if (stats && !destructive && !explanation) return {type:'overview'};
    if ((hasSupport || hasReport) && destructive) {
      return {type:'clarify', message:'Para responder, encerrar ou decidir sobre um caso, primeiro selecione-o na lista. A ação exigirá sua confirmação.'};
    }
    if ((hasSupport || hasReport) && !explanation && read) {
      return {type:'clarify', message:'Você quer consultar seus atendimentos, a fila geral de suporte ou as denúncias?'};
    }
    return null;
  }

  const api = Object.freeze({normalize, classify});
  scope.DevInJarvisIntents = api;
  if (typeof module !== 'undefined' && module.exports) module.exports = api;
})(typeof window !== 'undefined' ? window : globalThis);
