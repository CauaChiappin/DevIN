/* JARVIS: atendimento e denúncias exclusivamente pelo chat do administrador. */
(() => {
  'use strict';
  function init({root, api, addMessage, chatActionCard, actionButton}) {
    const endpoint = root.dataset.cases || 'api/jarvis/cases.php';
    let activeCase = null;
    let currentConsent = null;
    let currentDraft = null;

    function node(tag, text, className = '') {
      const el = document.createElement(tag);
      if (text !== undefined) el.textContent = String(text);
      if (className) el.className = className;
      return el;
    }

    function matches(value) {
      const normalized = value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
      if (/\b(reports?|denuncias?|chamados?|tickets?|suporte|atendimentos?)\b/.test(normalized)) return true;
      return Boolean(activeCase && /\b(analise|analisar|avalie|investigue|redija|escreva|redigir|parecer|veredito|responder|resposta|me ajuda|ajude|o que acha|print|imagem|evidencia)\b/.test(normalized));
    }

    function scopeFilter(value) {
      const plain = value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
      if (/\b(suporte|chamado|ticket|atendimento)\b/.test(plain) &&
          !/\b(report|denuncia)\b/.test(plain)) return 'support';
      if (/\b(report|denuncia)\b/.test(plain) && !/\b(suporte|chamado|ticket)\b/.test(plain)) return 'report';
      return 'all';
    }

    async function listing(original) {
      const response = await api(endpoint + '?action=list');
      const filter = scopeFilter(original);
      const records = (response.cases || []).filter(item =>
        filter === 'all' || (filter === 'support' ? item.type === 'support' : item.type !== 'support'));
      const heading = records.length
        ? 'Casos disponíveis conforme suas permissões: ' + records.length + '. A fila é compartilhada; não há atribuição individual por ADM.'
        : 'Nenhum caso pendente corresponde a essa consulta.';
      const card = chatActionCard(heading);
      const list = node('div', '', 'jarvis-admin__case-list');
      card.append(list);
      for (const item of records.slice(0, 75)) {
        const container = node('article', '', 'jarvis-admin__case-item');
        container.append(node('strong', item.title));
        container.append(node('small', item.status + ' · ' + (item.date || '')));
        container.append(node('p', String(item.preview || '').slice(0, 180)));
        const select = actionButton('Selecionar caso #' + item.id, async button => {
          button.disabled = true;
          try { await selectCase(item.type, Number(item.id)); }
          catch (error) { addMessage('assistant', 'Não foi possível abrir o caso: ' + error.message); }
          finally { button.disabled = false; }
        });
        container.append(select);
        list.append(container);
      }
      if (response.unavailable?.length) card.append(node('small', response.unavailable.join(' ')));
      return true;
    }

    async function analyze(type, id, consent, question, draft, button) {
      if (!consent?.checked) {
        addMessage('assistant', 'Antes de pedir análise por IA, marque a autorização de compartilhamento no cartão do caso. O contexto pode conter dados pessoais que o filtro não identificou.');
        consent?.focus();
        return;
      }
      button.disabled = true;
      try {
        const result = await api(endpoint, {
          action: draft ? 'draft' : 'analyze', type, id,
          question: String(question || '').slice(0, 450), consent: true
        });
        if (!activeCase || activeCase.type !== type || activeCase.id !== id) return;
        const label = draft ? 'Parecer sugerido pelo JARVIS (ainda NÃO enviado):\n\n'
          : 'Análise do JARVIS, sujeita à verificação do ADM:\n\n';
        addMessage('assistant', label + result.reply, {model: result.model});
        if (draft && currentDraft) currentDraft.value = String(result.reply).slice(0, 3000);
      } catch (error) {
        addMessage('assistant', 'JARVIS: ' + error.message);
      } finally { button.disabled = false; }
    }

    async function vision(selected, fileInput, consent, button) {
      if (!consent.checked) {
        addMessage('assistant', 'Autorize o envio da imagem ao provedor de IA. Prints podem conter dados pessoais.');
        consent.focus();
        return;
      }
      const file = fileInput.files[0] || null;
      if (!file && !(selected.type === 'support' && selected.has_attachment)) {
        addMessage('assistant', 'Anexe um print PNG/JPG/WebP de até 4 MB para analisar.');
        return;
      }
      if (file && (file.size > 4 * 1024 * 1024 || !['image/png','image/jpeg','image/webp'].includes(file.type))) {
        addMessage('assistant', 'Envie uma imagem PNG/JPG/WebP de até 4 MB.');
        return;
      }
      const form = new FormData();
      form.set('action', 'vision');
      form.set('type', selected.type);
      form.set('id', String(selected.id));
      form.set('consent', '1');
      if (file) form.set('image', file, file.name);
      button.disabled = true;
      try {
        const resp = await fetch(new URL(endpoint, location.href), {
          method:'POST', cache:'no-store', credentials:'same-origin',
          headers:{'X-CSRF-Token': root.dataset.csrf}, body:form,
          redirect:'error'
        });
        const result = await resp.json().catch(() => null);
        if (!resp.ok || !result?.success) throw new Error(result?.message || 'Erro ao analisar a imagem.');
        if (activeCase && activeCase.type === selected.type && activeCase.id === selected.id) {
          addMessage('assistant', 'Leitura orientativa do print (revisar manualmente):\n\n' + result.reply,
            {model: result.model});
        }
      } catch (error) {
        addMessage('assistant', 'Falha na leitura do print: ' + error.message);
      } finally { button.disabled = false; }
    }

    async function prepare(selected, textInput, verdictInput, button) {
      const body = textInput.value.trim();
      if (body.length < 5 || body.length > 3000) {
        addMessage('assistant', 'Escreva um parecer ou resposta de 5 a 3.000 caracteres.');
        textInput.focus();
        return;
      }
      const verdict = verdictInput?.value || 'reply';
      button.disabled = true;
      try {
        const proposal = await api(endpoint, {
          action:'prepare', type:selected.type, id:selected.id, body, verdict
        });
        const preview = proposal.preview;
        const risk = verdict === 'excluir'
          ? '\nATENÇÃO: confirmar removerá a vaga ou perfil da plataforma.'
          : (selected.type === 'support' ? '\nO usuário receberá uma resposta oficial.' : '\nA denúncia será concluída.');
        const text = 'Prévia do caso #' + preview.id + '\nDecisão: ' + preview.verdict
          + '\n\n' + preview.body + risk;
        const card = chatActionCard(text);
        const controls = node('div', '', 'jarvis-admin__tool-actions');
        const cancel = actionButton('Cancelar', async () => {
          controls.remove();
          addMessage('assistant', 'Prévia cancelada. Nada foi executado.');
        });
        const approve = actionButton('Confirmar ação como ADM', async btn => {
          if (!activeCase || activeCase.type !== selected.type || activeCase.id !== selected.id) {
            addMessage('assistant', 'Caso selecionado mudou. Refaça a prévia.');
            return;
          }
          if (!window.confirm('Confirme o caso #' + selected.id + ', a decisão e o texto exibido. ' + risk)) return;
          const approval = window.prompt('Para concluir, digite ' + proposal.approval + ':');
          if (approval !== proposal.approval) {
            addMessage('assistant', 'Confirmação cancelada. Nenhuma alteração realizada.');
            return;
          }
          btn.disabled = true;
          try {
            const result = await api(endpoint, {action:'confirm',
              confirmation:proposal.confirmation, approval});
            addMessage('assistant', result.message + ' Caso #' + selected.id);
            controls.remove();
            await selectCase(selected.type, selected.id);
          } catch (error) {
            addMessage('assistant', 'Não foi possível concluir: ' + error.message);
          } finally { btn.disabled = false; }
        });
        controls.append(cancel, approve);
        card.append(controls);
      } catch (error) {
        addMessage('assistant', 'Falha ao preparar a decisão: ' + error.message);
      } finally { button.disabled = false; }
    }

    async function selectCase(type, id) {
      const result = await api(endpoint + '?action=detail&type=' + encodeURIComponent(type) + '&id=' + id);
      const selected = result.case;
      if (!selected) throw new Error('Caso não retornado pela API.');
      activeCase = selected;
      const card = chatActionCard('Caso selecionado: ' + selected.title
        + '\nEstado: ' + selected.status + '\nCategoria: ' + selected.category
        + '\n\nRelato:\n' + selected.description
        + (selected.previous_replies?.length ? '\n\nRespostas anteriores:\n'
          + selected.previous_replies.join('\n---\n') : ''));
      if (selected.has_attachment) card.append(node('p',
        'Há anexo protegido neste chamado: ' + selected.attachment_name
        + '. Prints podem ser analisados com autorização explícita.', 'jarvis-admin__case-note'));
      if (!((selected.type === 'support' && selected.status !== 'fechado')
        || (selected.type !== 'support' && selected.status === 'pendente'))) {
        card.append(node('p', 'Caso concluído: consulta apenas de leitura.'));
        return true;
      }

      const box = node('section', '', 'jarvis-admin__case-editor');
      const question = node('textarea', '', 'jarvis-admin__case-question');
      question.placeholder = 'Pergunte ao JARVIS sobre esse caso (opcional)...';
      question.maxLength = 450;
      question.rows = 2;
      question.setAttribute('aria-label', 'Pergunta de análise ao JARVIS');
      const consent = node('label', '', 'jarvis-admin__case-consent');
      const check = document.createElement('input');
      check.type = 'checkbox';
      consent.append(check, node('span',
        'Autorizo enviar o contexto reduzido para IA. A anonimização pode não remover todos os dados pessoais.'));
      currentConsent = check;
      const options = node('div', '', 'jarvis-admin__case-buttons');
      const analyzeButton = actionButton('Analisar com JARVIS', async button =>
        analyze(selected.type, selected.id, check, question.value, false, button));
      const draftButton = actionButton('Pedir parecer ao JARVIS', async button =>
        analyze(selected.type, selected.id, check, question.value, true, button));
      options.append(analyzeButton, draftButton);
      const screenshot = document.createElement('input');
      screenshot.type = 'file';
      screenshot.accept = 'image/png,image/jpeg,image/webp';
      screenshot.className = 'jarvis-admin__case-file';
      screenshot.setAttribute('aria-label', 'Selecionar print para análise pelo JARVIS');
      const visionButton = actionButton(selected.has_attachment
        ? 'Analisar print do chamado ou arquivo selecionado'
        : 'Analisar print selecionado', async button => vision(selected, screenshot, check, button));
      options.append(screenshot, visionButton);

      const heading = node('strong', 'Meu parecer / resposta (editável)');
      const draft = node('textarea', '', 'jarvis-admin__case-draft');
      draft.rows = 5;
      draft.maxLength = 3000;
      draft.placeholder = 'Escreva você mesmo ou use e revise a sugestão do JARVIS.';
      draft.setAttribute('aria-label', 'Texto final a ser confirmado pelo ADM');
      currentDraft = draft;
      let verdict = null;
      if (selected.type !== 'support') {
        verdict = document.createElement('select');
        verdict.setAttribute('aria-label', 'Veredito sobre a denúncia');
        for (const [value,label] of [['manter','Manter vaga/perfil'],['excluir','Excluir vaga/perfil (irreversível)']]) {
          const opt = document.createElement('option');
          opt.value=value;opt.textContent=label;verdict.append(opt);
        }
      }
      const controls = node('div', '', 'jarvis-admin__case-buttons');
      const prepareButton = actionButton(selected.type === 'support'
        ? 'Revisar e preparar resposta' : 'Revisar e preparar veredito',
        async button => prepare(selected, draft, verdict, button));
      controls.append(prepareButton);
      box.append(question, consent, options, heading);
      if (verdict) box.append(verdict);
      box.append(draft, controls);
      card.append(box);
      return true;
    }

    async function handle(value) {
      addMessage('user', value);
      const plain = value.normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase();
      const direct = /(?:chamado|suporte|ticket|report|denuncia)\s*#?\s*(\d{1,9})/.exec(plain);
      if (direct && activeCase && activeCase.id === Number(direct[1])) {
        await selectCase(activeCase.type, activeCase.id);
        return true;
      }
      if (activeCase && !/\b(liste|listar|mostre|mostrar|quais|todos|pendentes|jurisdicao)\b/.test(plain)
        && /\b(analise|analisar|avalie|investigue|redija|escreva|redigir|parecer|veredito|resposta|me ajuda|ajude|o que acha)\b/.test(plain)) {
        if (!currentConsent?.checked) {
          addMessage('assistant', 'Marque a autorização de contexto no cartão do caso selecionado antes de falar sobre ele com a IA. Também é possível escrever e confirmar manualmente.');
          return true;
        }
        const draft = /\b(redija|escreva|redigir|parecer|veredito|resposta)\b/.test(plain);
        const button = actionButton('Aguarde',async()=>{});
        await analyze(activeCase.type, activeCase.id, currentConsent, value, draft, button);
        return true;
      }
      return listing(value);
    }
    return {matches,handle};
  }
  window.DevInJarvisCases = {init};
})();
