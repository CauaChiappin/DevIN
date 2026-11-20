/* JARVIS: atendimento e denúncias exclusivamente pelo chat do administrador. */
(() => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  'use strict'; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  function init({root, api, addMessage, chatActionCard, actionButton}) { // Explicação DevIN: Declara função/método init, com seus parâmetros e comportamento.
    const endpoint = root.dataset.cases || 'api/jarvis/cases.php'; // Explicação DevIN: Declara variável JavaScript endpoint para reutilizar valor ou resultado.
    let activeCase = null; // Explicação DevIN: Declara variável JavaScript activeCase para reutilizar valor ou resultado.
    let currentConsent = null; // Explicação DevIN: Declara variável JavaScript currentConsent para reutilizar valor ou resultado.
    let currentDraft = null; // Explicação DevIN: Declara variável JavaScript currentDraft para reutilizar valor ou resultado.

    function node(tag, text, className = '') { // Explicação DevIN: Declara função/método node, com seus parâmetros e comportamento.
      const el = document.createElement(tag); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
      if (text !== undefined) el.textContent = String(text); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      if (className) el.className = className; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      return el; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    function matches(value) { // Explicação DevIN: Declara função/método matches, com seus parâmetros e comportamento.
      const normalized = value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase(); // Explicação DevIN: Declara variável JavaScript normalized para reutilizar valor ou resultado.
      if (/\b(reports?|denuncias?|chamados?|tickets?|suporte|atendimentos?)\b/.test(normalized)) return true; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      return Boolean(activeCase && /\b(analise|analisar|avalie|investigue|redija|escreva|redigir|parecer|veredito|responder|resposta|me ajuda|ajude|o que acha|print|imagem|evidencia)\b/.test(normalized)); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    function scopeFilter(value) { // Explicação DevIN: Declara função/método scopeFilter, com seus parâmetros e comportamento.
      const plain = value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase(); // Explicação DevIN: Declara variável JavaScript plain para reutilizar valor ou resultado.
      // Minha jurisdição é a carteira de chamados de suporte deste representante.
      if (/\b(jurisdicao|responsabilidade|responsavel|representante|atribuid[oa]s?|estou atendendo|meus chamados|meus tickets)\b/.test(plain) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
          && /\b(minha|meu|meus|sou|estou|atribuid[oa]s?|jurisdicao|responsabilidade)\b/.test(plain)) return 'mine'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.

      if (/\b(suporte|chamado|ticket|atendimento)\b/.test(plain) && // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
          !/\b(report|denuncia)\b/.test(plain)) return 'support'; // Explicação DevIN: Continua expressão ou bloco pertencente a função scopeFilter.
      if (/\b(report|denuncia)\b/.test(plain) && !/\b(suporte|chamado|ticket)\b/.test(plain)) return 'report'; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      return 'all'; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    async function listing(original) { // Explicação DevIN: Exibe novamente a fila completa ao ADM.
      root.classList.remove('is-case-focused'); // Explicação DevIN: Exibe navegação completa ao sair do caso. // Explicação DevIN: Declara função/método listing, com seus parâmetros e comportamento.
      const filter = scopeFilter(original); // Explicação DevIN: Declara variável JavaScript filter para reutilizar valor ou resultado.
      const response = await api(endpoint + '?action=list&scope=' + (filter === 'mine' ? 'mine' : 'all')); // Explicação DevIN: Declara variável JavaScript response para reutilizar valor ou resultado.
      const records = (response.cases || []).filter(item => // Explicação DevIN: Declara variável JavaScript records para reutilizar valor ou resultado.
        filter === 'all' || filter === 'mine' || (filter === 'support' ? item.type === 'support' : item.type !== 'support')); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      const heading = filter === 'mine' // Explicação DevIN: Declara variável JavaScript heading para reutilizar valor ou resultado.
        ? (records.length ? 'Chamados sob sua responsabilidade: ' + records.length + '.' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            : 'Você ainda não representa nenhum chamado. Escolha um na fila compartilhada e assuma o atendimento.') // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        : (records.length ? 'Casos disponíveis: ' + records.length + '. O suporte é compartilhado e possui representantes individuais.' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            : 'Nenhum caso disponível nessa consulta.'); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
      const card = chatActionCard(heading); // Explicação DevIN: Declara variável JavaScript card para reutilizar valor ou resultado.
      const list = node('div', '', 'jarvis-admin__case-list'); // Explicação DevIN: Declara variável JavaScript list para reutilizar valor ou resultado.
      card.append(list); // Explicação DevIN: Continua expressão ou bloco pertencente a função listing.
      for (const item of records.slice(0, 75)) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
        const container = node('article', '', 'jarvis-admin__case-item'); // Explicação DevIN: Declara variável JavaScript container para reutilizar valor ou resultado.
        container.append(node('strong', item.title)); // Explicação DevIN: Continua expressão ou bloco pertencente a função listing.
        container.append(node('small', item.status + ' · ' + (item.date || ''))); // Explicação DevIN: Continua expressão ou bloco pertencente a função listing.
        if (item.type === 'support') container.append(node('small', // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
          item.assigned_admin_id ? (item.assigned_to_me ? 'Representante: você' // Explicação DevIN: Continua expressão ou bloco pertencente a função listing.
            : 'Representante: ADM #' + item.assigned_admin_id) : 'Aguardando representante')); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        container.append(node('p', String(item.preview || '').slice(0, 180))); // Explicação DevIN: Continua expressão ou bloco pertencente a função listing.
        const select = actionButton('Selecionar caso #' + item.id, async button => { // Explicação DevIN: Declara variável JavaScript select para reutilizar valor ou resultado.
          button.disabled = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
          try { await selectCase(item.type, Number(item.id)); } // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
          catch (error) { addMessage('assistant', 'Não foi possível abrir o caso: ' + error.message); } // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
          finally { button.disabled = false; } // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        container.append(select); // Explicação DevIN: Continua expressão ou bloco pertencente a função listing.
        list.append(container); // Explicação DevIN: Continua expressão ou bloco pertencente a função listing.
      } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      if (response.unavailable?.length) card.append(node('small', response.unavailable.join(' '))); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      return true; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    async function analyze(type, id, consent, question, draft, button) { // Explicação DevIN: Declara função/método analyze, com seus parâmetros e comportamento.
      if (!consent?.checked) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        addMessage('assistant', 'Antes de pedir análise por IA, marque a autorização de compartilhamento no cartão do caso. O contexto pode conter dados pessoais que o filtro não identificou.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função analyze.
        consent?.focus(); // Explicação DevIN: Continua expressão ou bloco pertencente a função analyze.
        return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
      } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      button.disabled = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        const result = await api(endpoint, { // Explicação DevIN: Declara variável JavaScript result para reutilizar valor ou resultado.
          action: draft ? 'draft' : 'analyze', type, id, // Explicação DevIN: Continua expressão ou bloco pertencente a função analyze.
          question: String(question || '').slice(0, 450), consent: true // Explicação DevIN: Continua expressão ou bloco pertencente a função analyze.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (!activeCase || activeCase.type !== type || activeCase.id !== id) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        const label = draft ? 'Parecer sugerido pelo JARVIS (ainda NÃO enviado):\n\n' // Explicação DevIN: Declara variável JavaScript label para reutilizar valor ou resultado.
          : 'Análise do JARVIS, sujeita à verificação do ADM:\n\n'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        addMessage('assistant', label + result.reply, {model: result.model}); // Explicação DevIN: Continua expressão ou bloco pertencente a função analyze.
        if (draft && currentDraft) currentDraft.value = String(result.reply).slice(0, 3000); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      } catch (error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        addMessage('assistant', 'JARVIS: ' + error.message); // Explicação DevIN: Continua expressão ou bloco pertencente a função analyze.
      } finally { button.disabled = false; } // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    async function vision(selected, fileInput, consent, button) { // Explicação DevIN: Declara função/método vision, com seus parâmetros e comportamento.
      if (!consent.checked) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        addMessage('assistant', 'Autorize o envio da imagem ao provedor de IA. Prints podem conter dados pessoais.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função vision.
        consent.focus(); // Explicação DevIN: Continua expressão ou bloco pertencente a função vision.
        return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
      } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      const file = fileInput.files[0] || null; // Explicação DevIN: Declara variável JavaScript file para reutilizar valor ou resultado.
      if (!file && !(selected.type === 'support' && selected.has_attachment)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        addMessage('assistant', 'Anexe um print PNG/JPG/WebP de até 4 MB para analisar.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função vision.
        return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
      } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      if (file && (file.size > 4 * 1024 * 1024 || !['image/png','image/jpeg','image/webp'].includes(file.type))) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        addMessage('assistant', 'Envie uma imagem PNG/JPG/WebP de até 4 MB.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função vision.
        return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
      } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      const form = new FormData(); // Explicação DevIN: Declara variável JavaScript form para reutilizar valor ou resultado.
      form.set('action', 'vision'); // Explicação DevIN: Continua expressão ou bloco pertencente a função vision.
      form.set('type', selected.type); // Explicação DevIN: Continua expressão ou bloco pertencente a função vision.
      form.set('id', String(selected.id)); // Explicação DevIN: Continua expressão ou bloco pertencente a função vision.
      form.set('consent', '1'); // Explicação DevIN: Continua expressão ou bloco pertencente a função vision.
      if (file) form.set('image', file, file.name); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      button.disabled = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        const resp = await fetch(new URL(endpoint, location.href), { // Explicação DevIN: Inicia solicitação HTTP assíncrona a um endereço configurado.
          method:'POST', cache:'no-store', credentials:'same-origin', // Explicação DevIN: Continua expressão ou bloco pertencente a função vision.
          headers:{'X-CSRF-Token': root.dataset.csrf}, body:form, // Explicação DevIN: Continua expressão ou bloco pertencente a função vision.
          redirect:'error' // Explicação DevIN: Continua expressão ou bloco pertencente a função vision.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        const result = await resp.json().catch(() => null); // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        if (!resp.ok || !result?.success) throw new Error(result?.message || 'Erro ao analisar a imagem.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if (activeCase && activeCase.type === selected.type && activeCase.id === selected.id) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
          addMessage('assistant', 'Leitura orientativa do print (revisar manualmente):\n\n' + result.reply, // Explicação DevIN: Continua expressão ou bloco pertencente a função vision.
            {model: result.model}); // Explicação DevIN: Continua expressão ou bloco pertencente a função vision.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      } catch (error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        addMessage('assistant', 'Falha na leitura do print: ' + error.message); // Explicação DevIN: Continua expressão ou bloco pertencente a função vision.
      } finally { button.disabled = false; } // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    async function prepare(selected, textInput, verdictInput, button) { // Explicação DevIN: Declara função/método prepare, com seus parâmetros e comportamento.
      const body = textInput.value.trim(); // Explicação DevIN: Declara variável JavaScript body para reutilizar valor ou resultado.
      if (body.length < 5 || body.length > 3000) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        addMessage('assistant', 'Escreva um parecer ou resposta de 5 a 3.000 caracteres.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função prepare.
        textInput.focus(); // Explicação DevIN: Continua expressão ou bloco pertencente a função prepare.
        return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
      } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      const verdict = verdictInput?.value || 'reply'; // Explicação DevIN: Declara variável JavaScript verdict para reutilizar valor ou resultado.
      button.disabled = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        const proposal = await api(endpoint, { // Explicação DevIN: Declara variável JavaScript proposal para reutilizar valor ou resultado.
          action:'prepare', type:selected.type, id:selected.id, body, verdict // Explicação DevIN: Continua expressão ou bloco pertencente a função prepare.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        const preview = proposal.preview; // Explicação DevIN: Declara variável JavaScript preview para reutilizar valor ou resultado.
        const risk = verdict === 'excluir' // Explicação DevIN: Declara variável JavaScript risk para reutilizar valor ou resultado.
          ? '\nATENÇÃO: confirmar removerá a vaga ou perfil da plataforma.' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
          : (selected.type === 'support' ? '\nO usuário receberá uma resposta oficial.' : '\nA denúncia será concluída.'); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        const text = 'Prévia do caso #' + preview.id + '\nDecisão: ' + preview.verdict // Explicação DevIN: Declara variável JavaScript text para reutilizar valor ou resultado.
          + '\n\n' + preview.body + risk; // Explicação DevIN: Continua expressão ou bloco pertencente a função prepare.
        const card = chatActionCard(text); // Explicação DevIN: Declara variável JavaScript card para reutilizar valor ou resultado.
        const controls = node('div', '', 'jarvis-admin__tool-actions'); // Explicação DevIN: Declara variável JavaScript controls para reutilizar valor ou resultado.
        const cancel = actionButton('Cancelar', async () => { // Explicação DevIN: Declara variável JavaScript cancel para reutilizar valor ou resultado.
          controls.remove(); // Explicação DevIN: Continua expressão ou bloco pertencente a função prepare.
          addMessage('assistant', 'Prévia cancelada. Nada foi executado.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função prepare.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        const approve = actionButton('Confirmar ação como ADM', async btn => { // Explicação DevIN: Declara variável JavaScript approve para reutilizar valor ou resultado.
          if (!activeCase || activeCase.type !== selected.type || activeCase.id !== selected.id) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            addMessage('assistant', 'Caso selecionado mudou. Refaça a prévia.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função prepare.
            return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
          } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
          if (!window.confirm('Confirme o caso #' + selected.id + ', a decisão e o texto exibido. ' + risk)) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
          const approval = window.prompt('Para concluir, digite ' + proposal.approval + ':'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
          if (approval !== proposal.approval) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            addMessage('assistant', 'Confirmação cancelada. Nenhuma alteração realizada.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função prepare.
            return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
          } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
          btn.disabled = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
          try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            const result = await api(endpoint, {action:'confirm', // Explicação DevIN: Declara variável JavaScript result para reutilizar valor ou resultado.
              confirmation:proposal.confirmation, approval}); // Explicação DevIN: Continua expressão ou bloco pertencente a função prepare.
            addMessage('assistant', result.message + ' Caso #' + selected.id); // Explicação DevIN: Continua expressão ou bloco pertencente a função prepare.
            controls.remove(); // Explicação DevIN: Continua expressão ou bloco pertencente a função prepare.
            await selectCase(selected.type, selected.id); // Explicação DevIN: Continua expressão ou bloco pertencente a função prepare.
          } catch (error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            addMessage('assistant', 'Não foi possível concluir: ' + error.message); // Explicação DevIN: Continua expressão ou bloco pertencente a função prepare.
          } finally { btn.disabled = false; } // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        controls.append(cancel, approve); // Explicação DevIN: Continua expressão ou bloco pertencente a função prepare.
        card.append(controls); // Explicação DevIN: Continua expressão ou bloco pertencente a função prepare.
      } catch (error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        addMessage('assistant', 'Falha ao preparar a decisão: ' + error.message); // Explicação DevIN: Continua expressão ou bloco pertencente a função prepare.
      } finally { button.disabled = false; } // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    async function selectCase(type, id) { // Explicação DevIN: Declara função/método selectCase, com seus parâmetros e comportamento.
      const result = await api(endpoint + '?action=detail&type=' + encodeURIComponent(type) + '&id=' + id); // Explicação DevIN: Declara variável JavaScript result para reutilizar valor ou resultado.
      const selected = result.case; // Explicação DevIN: Declara variável JavaScript selected para reutilizar valor ou resultado.
      if (!selected) throw new Error('Caso não retornado pela API.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      activeCase = selected; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      root.classList.add('is-case-focused'); // Explicação DevIN: Compacta controles de navegação enquanto o ADM analisa o caso.
      const card = chatActionCard('Caso selecionado: ' + selected.title // Explicação DevIN: Declara variável JavaScript card para reutilizar valor ou resultado.
        + '\nEstado: ' + selected.status + '\nCategoria: ' + selected.category // Explicação DevIN: Continua expressão ou bloco pertencente a função selectCase.
        + '\n\nRelato:\n' + selected.description // Explicação DevIN: Continua expressão ou bloco pertencente a função selectCase.
        + (selected.previous_replies?.length ? '\n\nRespostas anteriores:\n' // Explicação DevIN: Continua expressão ou bloco pertencente a função selectCase.
          + selected.previous_replies.join('\n---\n') : '')); // Explicação DevIN: Continua expressão ou bloco pertencente a função selectCase.
      if (selected.type === 'support') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        const owner = selected.assigned_admin_id || null; // Explicação DevIN: Declara variável JavaScript owner para reutilizar valor ou resultado.
        card.append(node('p', owner // Explicação DevIN: Continua expressão ou bloco pertencente a função selectCase.
          ? (selected.assigned_to_me ? 'Você é o representante deste chamado.' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
              : 'Este chamado é representado pelo ADM #' + owner + '.') // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
          : 'Sem representante: você pode assumir o atendimento.', 'jarvis-admin__case-note')); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        if (selected.status !== 'fechado') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
          const assignment = owner === null ? 'claim' : (selected.assigned_to_me ? 'release' : 'takeover'); // Explicação DevIN: Declara variável JavaScript assignment para reutilizar valor ou resultado.
          const caption = assignment === 'claim' ? 'Assumir suporte' // Explicação DevIN: Declara variável JavaScript caption para reutilizar valor ou resultado.
            : assignment === 'release' ? 'Liberar atendimento' : 'Assumir de outro ADM'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
          const choose = actionButton(caption, async button => { // Explicação DevIN: Declara variável JavaScript choose para reutilizar valor ou resultado.
            if (assignment === 'takeover' && !window.confirm('Assumir este chamado do ADM #' + owner + '? A transferência ficará registrada.')) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            if (assignment === 'release' && !window.confirm('Liberar este chamado para outro ADM?')) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            button.disabled = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
              const result = await api(endpoint, { // Explicação DevIN: Declara variável JavaScript result para reutilizar valor ou resultado.
                action:'assign', type:'support', id:selected.id, assignment_action:assignment // Explicação DevIN: Continua expressão ou bloco pertencente a função selectCase.
              }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
              addMessage('assistant', result.message); // Explicação DevIN: Continua expressão ou bloco pertencente a função selectCase.
              await selectCase('support', selected.id); // Explicação DevIN: Continua expressão ou bloco pertencente a função selectCase.
            } catch(error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
              addMessage('assistant', 'Não foi possível trocar o representante: ' + error.message); // Explicação DevIN: Continua expressão ou bloco pertencente a função selectCase.
              button.disabled = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
          }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
          choose.classList.add('jarvis-admin__case-assignment'); // Explicação DevIN: Aplica o padrão visual às ações de representação.
          card.append(choose); // Explicação DevIN: Continua expressão ou bloco pertencente a função selectCase.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      if (selected.has_attachment) card.append(node('p', // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        'Há anexo protegido neste chamado: ' + selected.attachment_name // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
        + '. Prints podem ser analisados com autorização explícita.', 'jarvis-admin__case-note')); // Explicação DevIN: Continua expressão ou bloco pertencente a função selectCase.
      if (!((selected.type === 'support' && selected.status !== 'fechado') // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        || (selected.type !== 'support' && selected.status === 'pendente'))) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        card.append(node('p', 'Caso concluído: consulta apenas de leitura.')); // Explicação DevIN: Continua expressão ou bloco pertencente a função selectCase.
        return true; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
      } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

      const box = node('section', '', 'jarvis-admin__case-editor'); // Explicação DevIN: Declara variável JavaScript box para reutilizar valor ou resultado.
      const question = node('textarea', '', 'jarvis-admin__case-question'); // Explicação DevIN: Declara variável JavaScript question para reutilizar valor ou resultado.
      question.placeholder = 'Pergunte ao JARVIS sobre esse caso (opcional)...'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      question.maxLength = 450; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      question.rows = 2; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      question.setAttribute('aria-label', 'Pergunta de análise ao JARVIS'); // Explicação DevIN: Continua expressão ou bloco pertencente a função selectCase.
      const consent = node('label', '', 'jarvis-admin__case-consent'); // Explicação DevIN: Declara variável JavaScript consent para reutilizar valor ou resultado.
      const check = document.createElement('input'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
      check.type = 'checkbox'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      consent.append(check, node('span', // Explicação DevIN: Continua expressão ou bloco pertencente a função selectCase.
        'Autorizo enviar o contexto reduzido para IA. A anonimização pode não remover todos os dados pessoais.')); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
      currentConsent = check; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      const options = node('div', '', 'jarvis-admin__case-buttons'); // Explicação DevIN: Declara variável JavaScript options para reutilizar valor ou resultado.
      const analyzeButton = actionButton('Analisar com JARVIS', async button => // Explicação DevIN: Declara variável JavaScript analyzeButton para reutilizar valor ou resultado.
        analyze(selected.type, selected.id, check, question.value, false, button)); // Explicação DevIN: Continua expressão ou bloco pertencente a função selectCase.
      const draftButton = actionButton('Pedir parecer ao JARVIS', async button => // Explicação DevIN: Declara variável JavaScript draftButton para reutilizar valor ou resultado.
        analyze(selected.type, selected.id, check, question.value, true, button)); // Explicação DevIN: Continua expressão ou bloco pertencente a função selectCase.
      options.append(analyzeButton, draftButton); // Explicação DevIN: Continua expressão ou bloco pertencente a função selectCase.
      const screenshot = document.createElement('input'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
      screenshot.type = 'file'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      screenshot.accept = 'image/png,image/jpeg,image/webp'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      screenshot.className = 'jarvis-admin__case-file'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      screenshot.setAttribute('aria-label', 'Selecionar print para análise pelo JARVIS'); // Explicação DevIN: Continua expressão ou bloco pertencente a função selectCase.
      const visionButton = actionButton(selected.has_attachment // Explicação DevIN: Declara variável JavaScript visionButton para reutilizar valor ou resultado.
        ? 'Analisar print do chamado ou arquivo selecionado' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        : 'Analisar print selecionado', async button => vision(selected, screenshot, check, button)); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
      options.append(screenshot, visionButton); // Explicação DevIN: Continua expressão ou bloco pertencente a função selectCase.

      const heading = node('strong', 'Meu parecer / resposta (editável)'); // Explicação DevIN: Declara variável JavaScript heading para reutilizar valor ou resultado.
      const draft = node('textarea', '', 'jarvis-admin__case-draft'); // Explicação DevIN: Declara variável JavaScript draft para reutilizar valor ou resultado.
      draft.rows = 5; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      draft.maxLength = 3000; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      draft.placeholder = 'Escreva você mesmo ou use e revise a sugestão do JARVIS.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      draft.setAttribute('aria-label', 'Texto final a ser confirmado pelo ADM'); // Explicação DevIN: Continua expressão ou bloco pertencente a função selectCase.
      currentDraft = draft; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      let verdict = null; // Explicação DevIN: Declara variável JavaScript verdict para reutilizar valor ou resultado.
      if (selected.type !== 'support') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        verdict = document.createElement('select'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        verdict.setAttribute('aria-label', 'Veredito sobre a denúncia'); // Explicação DevIN: Continua expressão ou bloco pertencente a função selectCase.
        for (const [value,label] of [['manter','Manter vaga/perfil'],['excluir','Excluir vaga/perfil (irreversível)']]) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
          const opt = document.createElement('option'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
          opt.value=value;opt.textContent=label;verdict.append(opt); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      const controls = node('div', '', 'jarvis-admin__case-buttons'); // Explicação DevIN: Declara variável JavaScript controls para reutilizar valor ou resultado.
      const prepareButton = actionButton(selected.type === 'support' // Explicação DevIN: Declara variável JavaScript prepareButton para reutilizar valor ou resultado.
        ? 'Revisar e preparar resposta' : 'Revisar e preparar veredito', // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        async button => prepare(selected, draft, verdict, button)); // Explicação DevIN: Prepara, parametriza ou executa operação SQL por interface do banco.
      controls.append(prepareButton); // Explicação DevIN: Continua expressão ou bloco pertencente a função selectCase.
      box.append(question, consent, options, heading); // Explicação DevIN: Continua expressão ou bloco pertencente a função selectCase.
      if (verdict) box.append(verdict); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      box.append(draft, controls); // Explicação DevIN: Continua expressão ou bloco pertencente a função selectCase.
      card.append(box); // Explicação DevIN: Continua expressão ou bloco pertencente a função selectCase.
      return true; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    async function handle(value) { // Explicação DevIN: Declara função/método handle, com seus parâmetros e comportamento.
      addMessage('user', value); // Explicação DevIN: Continua expressão ou bloco pertencente a função handle.
      const plain = value.normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase(); // Explicação DevIN: Declara variável JavaScript plain para reutilizar valor ou resultado.
      const direct = /(?:chamado|suporte|ticket|report|denuncia)(?:\s+de\s+(vaga|perfil))?\s*#?\s*(\d{1,9})/.exec(plain); // Explicação DevIN: Declara variável JavaScript direct para reutilizar valor ou resultado.
      if (direct) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        const id = Number(direct[2]); // Explicação DevIN: Declara variável JavaScript id para reutilizar valor ou resultado.
        const category = direct[1] === 'vaga' ? 'job_report' : (direct[1] === 'perfil' ? 'profile_report' : (/chamado|suporte|ticket/.test(direct[0]) ? 'support' : null)); // Explicação DevIN: Declara variável JavaScript category para reutilizar valor ou resultado.
        if (category) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
          await selectCase(category, id); // Explicação DevIN: Continua expressão ou bloco pertencente a função handle.
          return true; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (activeCase && activeCase.id === id) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
          await selectCase(activeCase.type, id); // Explicação DevIN: Continua expressão ou bloco pertencente a função handle.
          return true; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      if (activeCase && !/\b(liste|listar|mostre|mostrar|quais|todos|pendentes|jurisdicao)\b/.test(plain) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        && /\b(analise|analisar|avalie|investigue|redija|escreva|redigir|parecer|veredito|resposta|me ajuda|ajude|o que acha)\b/.test(plain)) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        if (!currentConsent?.checked) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
          addMessage('assistant', 'Marque a autorização de contexto no cartão do caso selecionado antes de falar sobre ele com a IA. Também é possível escrever e confirmar manualmente.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função handle.
          return true; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        const draft = /\b(redija|escreva|redigir|parecer|veredito|resposta)\b/.test(plain); // Explicação DevIN: Declara variável JavaScript draft para reutilizar valor ou resultado.
        const button = actionButton('Aguarde',async()=>{}); // Explicação DevIN: Declara variável JavaScript button para reutilizar valor ou resultado.
        await analyze(activeCase.type, activeCase.id, currentConsent, value, draft, button); // Explicação DevIN: Continua expressão ou bloco pertencente a função handle.
        return true; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
      } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      return listing(value); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    const reset = () => { activeCase = null; currentConsent = null; currentDraft = null; root.classList.remove('is-case-focused'); }; // Explicação DevIN: Declara variável JavaScript reset para reutilizar valor ou resultado.
    return {matches,handle,reset}; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  window.DevInJarvisCases = {init}; // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
})(); // Explicação DevIN: Continua expressão ou bloco pertencente a função handle.
