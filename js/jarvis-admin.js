/* JARVIS Admin — widgets e dados de rede são renderizados apenas com textContent. */
(() => {
  'use strict';
  const root = document.getElementById('jarvisAdmin');
  if (!root) return;
  const $ = (selector) => root.querySelector(selector);
  const panel = $('#jarvisAdminPanel');
  const launcher = $('[data-jarvis-toggle]');
  const input = $('[data-jarvis-input]');
  const messages = $('[data-jarvis-messages]');
  const status = $('[data-jarvis-status]');
  const taskStatus = $('[data-jarvis-task-status]');
  const send = $('[data-jarvis-send]');
  const conversationSelect = $('[data-jarvis-conversation]');
  const taskList = $('[data-jarvis-tasks-list]');
  let conversationId = null;
  let busy = false;
  let activeTab = 'chat';
  let searchTimer = null;

  // Persistência local: salva apenas lado e altura proporcional, nunca credenciais.
  const docking = window.DevInJarvisDock;
  const dockStorageKey = 'devin:jarvisdevin:dock:v1';
  let savedDock = null;
  let dockSide = 'left';
  let pointerDrag = null;
  let suppressDragClick = false;
  try {
    const stored = JSON.parse(window.localStorage.getItem(dockStorageKey) || 'null');
    if (stored && ['left', 'right'].includes(stored.side) && Number.isFinite(stored.yRatio)) {
      savedDock = {side: stored.side, yRatio: stored.yRatio};
    }
  } catch (_) {
    // Navegação anônima/bloqueio de armazenamento: apenas não persiste.
  }

  function positionPanelNearLauncher() {
    if (panel.hidden) return;
    if (window.matchMedia('(max-width:600px)').matches) {
      panel.style.position = '';
      panel.style.left = '';
      panel.style.top = '';
      panel.style.right = '';
      panel.style.bottom = '';
      return;
    }
    panel.style.position = 'fixed';
    panel.style.right = 'auto';
    panel.style.bottom = 'auto';
    const rect = launcher.getBoundingClientRect();
    const width = panel.getBoundingClientRect().width;
    const height = panel.getBoundingClientRect().height;
    const limitX = Math.max(12, window.innerWidth - width - 12);
    const limitY = Math.max(12, window.innerHeight - height - 12);
    const desiredX = dockSide === 'right' ? rect.right - width : rect.left;
    let desiredY = rect.top - height - 10;
    if (desiredY < 12) desiredY = rect.bottom + 10;
    panel.style.left = Math.round(Math.max(12, Math.min(limitX, desiredX))) + 'px';
    panel.style.top = Math.round(Math.max(12, Math.min(limitY, desiredY))) + 'px';
  }

  function placeLauncher(position) {
    dockSide = position.side;
    root.dataset.dockSide = dockSide;
    root.style.left = Math.round(position.left) + 'px';
    root.style.top = Math.round(position.top) + 'px';
    root.style.right = 'auto';
    root.style.bottom = 'auto';
    positionPanelNearLauncher();
  }

  function restoreLauncher() {
    if (!docking || pointerDrag) return;
    placeLauncher(docking.restore(savedDock, window.innerWidth, window.innerHeight,
      launcher.offsetWidth, launcher.offsetHeight));
  }

  function persistLauncher(position) {
    savedDock = {side: position.side, yRatio: position.yRatio};
    try { window.localStorage.setItem(dockStorageKey, JSON.stringify(savedDock)); }
    catch (_) { /* Disponibilidade de storage não afeta a navegação. */ }
  }

  function initLauncherDocking() {
    if (!docking || !window.PointerEvent) return;
    restoreLauncher();
    launcher.addEventListener('pointerdown', event => {
      if (event.button !== 0 || event.isPrimary === false) return;
      suppressDragClick = false;
      const rect = launcher.getBoundingClientRect();
      pointerDrag = {
        id: event.pointerId, startX: event.clientX, startY: event.clientY,
        left: rect.left, top: rect.top, moved: false
      };
      try { launcher.setPointerCapture(event.pointerId); } catch (_) {}
    });
    launcher.addEventListener('pointermove', event => {
      const state = pointerDrag;
      if (!state || state.id !== event.pointerId) return;
      const dx = event.clientX - state.startX;
      const dy = event.clientY - state.startY;
      if (!state.moved && Math.hypot(dx, dy) < 8) return;
      state.moved = true;
      launcher.classList.add('is-dragging');
      const width = launcher.offsetWidth;
      const height = launcher.offsetHeight;
      const maxX = Math.max(12, window.innerWidth - width - 12);
      const maxY = Math.max(12, window.innerHeight - height - 12);
      root.style.left = Math.round(docking.clamp(state.left + dx, 12, maxX)) + 'px';
      root.style.top = Math.round(docking.clamp(state.top + dy, 12, maxY)) + 'px';
      root.style.right = 'auto';
      root.style.bottom = 'auto';
      positionPanelNearLauncher();
    });
    function finishDrag(event) {
      const state = pointerDrag;
      if (!state || state.id !== event.pointerId) return;
      pointerDrag = null;
      launcher.classList.remove('is-dragging');
      try { launcher.releasePointerCapture(event.pointerId); } catch (_) {}
      if (!state.moved) return; // Toque simples continua abrindo o chat.
      suppressDragClick = true;
      const rect = launcher.getBoundingClientRect();
      const target = docking.snap(rect.left + rect.width / 2, rect.top,
        window.innerWidth, window.innerHeight, rect.width, rect.height);
      placeLauncher(target);
      persistLauncher(target);
      event.preventDefault();
    }
    launcher.addEventListener('pointerup', finishDrag);
    launcher.addEventListener('pointercancel', finishDrag);
    launcher.addEventListener('keydown', event => {
      if (!event.altKey || !['ArrowLeft','ArrowRight'].includes(event.key)) return;
      event.preventDefault();
      const targetSide = event.key === 'ArrowLeft' ? 'left' : 'right';
      const rect = launcher.getBoundingClientRect();
      const target = docking.snap(targetSide === 'left' ? 0 : window.innerWidth,
        rect.top, window.innerWidth, window.innerHeight, rect.width, rect.height);
      placeLauncher(target);
      persistLauncher(target);
    });
    window.addEventListener('resize', restoreLauncher);
    window.addEventListener('orientationchange', restoreLauncher);
  }
  initLauncherDocking();

  async function api(url, body = null) {
    const options = {credentials: 'same-origin', cache: 'no-store', headers: {Accept: 'application/json'}};
    if (body !== null) {
      options.method = 'POST';
      options.headers['Content-Type'] = 'application/json';
      options.headers['X-CSRF-Token'] = root.dataset.csrf;
      options.body = JSON.stringify(body);
    }
    const response = await fetch(url, options);
    const data = await response.json().catch(() => null);
    if (!data || !response.ok || data.success !== true) {
      throw new Error(data?.message || 'Falha ao consultar o servidor. Verifique sua sessão.');
    }
    return data;
  }
  function open(show) {
    panel.hidden = !show;
    launcher.setAttribute('aria-expanded', String(show));
    if (show) positionPanelNearLauncher();
    if (show) {
      loadConversations($('[data-jarvis-search]').value.trim()).catch(error => {
        status.textContent = error.message;
      });
      if (activeTab === 'tasks') void loadTasks();
      else if (activeTab === 'outbox') void loadDrafts();
      else if (activeTab === 'audit') void loadAudit();
      else if (activeTab === 'lookup') $('[data-jarvis-record-query]').focus();
      else input.focus();
    } else {
      launcher.focus();
    }
  }
  function tab(name) {
    activeTab = name;
    root.querySelectorAll('[data-jarvis-tab]').forEach((button) => {
      const selected = button.dataset.jarvisTab === name;
      button.classList.toggle('is-active', selected);
      button.setAttribute('aria-pressed', String(selected));
    });
    root.querySelectorAll('[data-jarvis-pane]').forEach((pane) => {
      pane.hidden = pane.dataset.jarvisPane !== name;
    });
    if (name === 'tasks') loadTasks();
    else if (name === 'outbox') void loadDrafts();
    else if (name === 'audit') void loadAudit();
    else if (name === 'lookup') $('[data-jarvis-record-query]').focus();
    else input.focus();
  }
  function addMessage(role, content, metadata = null) {
    $('[data-jarvis-welcome]').hidden = true;
    const item = document.createElement('div');
    item.className = 'jarvis-admin__message' + (role === 'user' ? ' is-user' : '');
    const label = document.createElement('span');
    label.className = 'jarvis-admin__message-label';
    label.textContent = role === 'user' ? 'Você' : 'JarvisDevIn';
    const text = document.createElement('div');
    text.className = 'jarvis-admin__message-body';
    text.textContent = String(content);
    item.append(label, text);
    if (role === 'assistant' && metadata) {
      const details = [];
      if (metadata.source === 'local') details.push('Fonte: banco DevIN');
      if (typeof metadata.model === 'string') {
        details.push('Modelo: ' + String(metadata.provider || 'API') + ' / ' + metadata.model);
      }
      if (metadata.fallback_used === true) details.push('Modelo gratuito de reserva');
      if (metadata.key_used === 'backup') details.push('Chave reserva utilizada');
      if (Array.isArray(metadata.skills) && metadata.skills.length) {
        details.push('Habilidades: ' + metadata.skills.map(s => String(s.label || '')).filter(Boolean).join(', '));
      }
      if (metadata.history_used === true) details.push('Contexto anterior utilizado');
      if (details.length) {
        const meta = document.createElement('small');
        meta.className = 'jarvis-admin__response-meta';
        meta.textContent = details.join(' · ');
        item.append(meta);
      }
    }
    messages.append(item);
    messages.scrollTop = messages.scrollHeight;
  }
  function clearMessages() {
    messages.querySelectorAll('.jarvis-admin__message').forEach(node => node.remove());
    $('[data-jarvis-welcome]').hidden = false;
  }
  function resize() {
    input.style.height = 'auto';
    input.style.height = Math.min(input.scrollHeight, 150) + 'px';
  }
  async function loadConversations(search = '') {
    const data = await api(root.dataset.conversations + '?q=' + encodeURIComponent(search));
    const previous = String(conversationId ?? '');
    conversationSelect.replaceChildren();
    const empty = document.createElement('option');
    empty.value = '';
    empty.textContent = 'Nova conversa';
    conversationSelect.append(empty);
    data.conversations.forEach(row => {
      const option = document.createElement('option');
      option.value = String(row.id);
      option.textContent = String(row.title);
      conversationSelect.append(option);
    });
    conversationSelect.value = previous;
  }
  async function loadConversation(id) {
    if (!id) {
      conversationId = null;
      clearMessages();
      status.textContent = '';
      return;
    }
    const data = await api(root.dataset.conversations + '?id=' + encodeURIComponent(String(id)));
    conversationId = Number(id);
    clearMessages();
    data.messages.forEach(m => addMessage(m.role, m.content));
    conversationSelect.value = String(id);
    status.textContent = '';
  }
  async function chat(mode, message = '') {
    if (busy) return;
    busy = true;
    send.disabled = true;
    status.textContent = 'Consultando...';
    if (mode === 'chat') addMessage('user', message);
    try {
      const data = await api(root.dataset.endpoint, {
        mode, message, page: root.dataset.page || 'inicio', conversation_id: conversationId,
        use_history: $('[data-jarvis-use-history]').checked
      });
      addMessage('assistant', data.reply || 'Sem resposta.', data);
      if (mode === 'chat') {
        conversationId = Number(data.conversation_id);
        await loadConversations($('[data-jarvis-search]').value.trim()).catch(() => {});
      }
      status.textContent = '';
    } catch (error) {
      // O erro não é salvo no histórico, pois o provedor não confirmou a resposta.
      addMessage('assistant', 'Falha: ' + error.message);
      status.textContent = 'Solicitação não concluída.';
    } finally {
      busy = false;
      send.disabled = false;
      if (!panel.hidden && activeTab === 'chat') input.focus();
    }
  }
  const labels = {pending: 'Pendente', in_progress: 'Em análise', done: 'Concluída', cancelled: 'Cancelada'};
  function taskButton(label, action, id) {
    const button = document.createElement('button');
    button.type = 'button';
    button.textContent = label;
    button.dataset.taskAction = action;
    button.dataset.taskId = String(id);
    return button;
  }
  function renderTasks(rows) {
    taskList.replaceChildren();
    if (!rows.length) {
      const info = document.createElement('p');
      info.className = 'jarvis-admin__tasks-empty';
      info.textContent = 'Nenhuma atividade registrada.';
      taskList.append(info);
    }
    rows.forEach(task => {
      const card = document.createElement('article');
      card.className = 'jarvis-admin__task';
      const heading = document.createElement('strong');
      heading.textContent = task.title;
      const state = document.createElement('span');
      state.className = 'jarvis-admin__task-status';
      state.textContent = labels[task.status] || task.status;
      const desc = document.createElement('p');
      desc.textContent = task.description || 'Sem descrição.';
      const actions = document.createElement('div');
      actions.className = 'jarvis-admin__task-actions';
      if (task.status === 'pending') actions.append(taskButton('Iniciar análise', 'start', task.id));
      if (task.status === 'in_progress') actions.append(taskButton('Concluir registro', 'complete', task.id));
      if (['pending', 'in_progress'].includes(task.status)) actions.append(taskButton('Cancelar', 'cancel', task.id));
      card.append(heading, state, desc, actions);
      taskList.append(card);
    });
  }
  async function loadTasks() {
    taskStatus.textContent = 'Carregando...';
    try {
      const data = await api(root.dataset.tasks);
      renderTasks(data.tasks);
      taskStatus.textContent = '';
    } catch (error) {
      taskStatus.textContent = error.message;
    }
  }


  // As ferramentas são uma API separada. Nunca há comando de moderação.
  const toolUrl = root.dataset.tools;
  const lookupStatus = $('[data-jarvis-lookup-status]');
  const recordResults = $('[data-jarvis-record-results]');
  const analysisArea = $('[data-jarvis-analysis]');
  const outboxStatus = $('[data-jarvis-outbox-status]');
  const outboxList = $('[data-jarvis-outbox-list]');
  const auditStatus = $('[data-jarvis-audit-status]');
  const auditList = $('[data-jarvis-audit-list]');

  function toolNotice(node, text) { node.textContent = String(text); }

  async function loadDrafts() {
    toolNotice(outboxStatus, 'Carregando rascunhos...');
    try {
      const data = await api(toolUrl + '?action=drafts');
      outboxList.replaceChildren();
      if (!data.drafts.length) {
        const info = document.createElement('p');
        info.textContent = 'Nenhuma mensagem preparada.';
        outboxList.append(info);
      }
      data.drafts.forEach(row => {
        const card = document.createElement('article');
        card.className = 'jarvis-admin__tool-card';
        const heading = document.createElement('strong');
        heading.textContent = (row.recipient_type === 'pessoa' ? 'Candidato' : 'Empresa')
          + ' #' + row.recipient_id + ' · ' + row.status + ' · ' + (row.source === 'ai' ? 'IA' : 'ADM');
        const body = document.createElement('textarea');
        body.rows = 4;
        body.maxLength = 1200;
        body.value = row.body;
        body.readOnly = row.status !== 'draft';
        body.setAttribute('aria-label', 'Conteúdo da mensagem para revisão');
        const actions = document.createElement('div');
        actions.className = 'jarvis-admin__tool-actions';
        if (row.status === 'draft' || row.status === 'failed') {
          const send = document.createElement('button');
          send.type = 'button';
          send.textContent = 'Revisar e enviar';
          send.addEventListener('click', async () => {
            if (send.disabled) return;
            if (row.status === 'draft' && body.value.trim() !== row.body) {
              try {
                await api(toolUrl, {action:'revise', draft_id:Number(row.id), body:body.value.trim()});
              } catch (error) {toolNotice(outboxStatus,error.message);return;}
            }
            const recipient = (row.recipient_type === 'pessoa' ? 'candidato' : 'empresa') + ' #' + row.recipient_id;
            const confirmed = window.prompt('Confira o destinatário (' + recipient
              + ') e o texto acima. Digite ENVIAR para confirmar o envio único:');
            if (confirmed !== 'ENVIAR') return;
            send.disabled = true;
            try {
              await api(toolUrl, {action:'send', draft_id:Number(row.id), confirmed:true, approval:'ENVIAR'});
              toolNotice(outboxStatus, 'Mensagem enviada como Support DevIN.');
              await loadDrafts();
            } catch (error) {
              toolNotice(outboxStatus, error.message);
              send.disabled = false;
            }
          });
          actions.append(send);
          const cancel = document.createElement('button');
          cancel.type = 'button';
          cancel.textContent = 'Descartar';
          cancel.addEventListener('click', async () => {
            if (!window.confirm('Descartar o rascunho?')) return;
            cancel.disabled = true;
            try {
              await api(toolUrl, {action:'cancel', draft_id:Number(row.id)});
              await loadDrafts();
            } catch (error) {
              toolNotice(outboxStatus,error.message);cancel.disabled = false;
            }
          });
          actions.append(cancel);
        }
        card.append(heading,body,actions);
        outboxList.append(card);
      });
      if (!data.drafts.length) toolNotice(outboxStatus, '');
      else if (outboxStatus.textContent === 'Carregando rascunhos...') toolNotice(outboxStatus,'');
    } catch (error) {toolNotice(outboxStatus,error.message);}
  }

  async function loadAudit() {
    toolNotice(auditStatus,'Carregando histórico...');
    try {
      const data = await api(toolUrl + '?action=history');
      auditList.replaceChildren();
      if (!data.events.length) {
        const text = document.createElement('p');text.textContent='Ainda não há ações registradas.';
        auditList.append(text);
      }
      data.events.forEach(row => {
        const card=document.createElement('article');
        card.className='jarvis-admin__tool-card jarvis-admin__audit-entry';
        const heading=document.createElement('strong');
        heading.textContent=(row.origin === 'ai'?'IA':row.origin === 'admin'?'ADM':'Sistema')
          + ' · ADM #' + row.admin_id + ' · ' + row.event_name + ' · ' + row.status;
        const detail=document.createElement('small');
        detail.textContent=row.created_at + ' · ' + row.target_type
          + (row.target_id ? ' #' + row.target_id : '');
        card.append(heading,detail);
        auditList.append(card);
      });
      toolNotice(auditStatus,'');
    } catch (error) {toolNotice(auditStatus,error.message);}
  }

  $('[data-jarvis-audit-refresh]').addEventListener('click', () => void loadAudit());
  $('[data-jarvis-lookup-form]').addEventListener('submit', async event => {
    event.preventDefault();
    toolNotice(lookupStatus,'Consultando registros...');
    analysisArea.textContent='';
    recordResults.replaceChildren();
    const kind=$('[data-jarvis-record-kind]').value;
    const query=$('[data-jarvis-record-query]').value.trim();
    try {
      const data=await api(toolUrl + '?action=search&kind=' + encodeURIComponent(kind)
        + '&q=' + encodeURIComponent(query));
      data.records.forEach(record => {
        const card=document.createElement('article');
        card.className='jarvis-admin__tool-card';
        const title=document.createElement('strong');
        title.textContent='#'+record.id+' · '+(record.title||record.name||kind);
        const subtitle=document.createElement('small');
        subtitle.textContent=[record.subtitle,record.motivo,record.status].filter(Boolean).join(' · ');
        const button=document.createElement('button');
        button.type='button';button.textContent='Analisar com IA';
        button.addEventListener('click',async()=>{
          if (button.disabled) return;
          button.disabled=true;
          toolNotice(lookupStatus,'Analisando registro selecionado...');
          try {
            const result=await api(toolUrl,{
              action:'analyze',kind,id:Number(record.id),
              question:$('[data-jarvis-record-question]').value.trim()
            });
            analysisArea.textContent=result.reply;
            toolNotice(lookupStatus,'Análise concluída. Nenhum dado foi alterado.');
          } catch (error) {toolNotice(lookupStatus,error.message);}
          finally {button.disabled=false;}
        });
        card.append(title,subtitle,button);
        recordResults.append(card);
      });
      if (!data.records.length) toolNotice(lookupStatus,'Nenhum resultado encontrado.');
      else toolNotice(lookupStatus,'Registros encontrados: '+data.records.length+'.');
    } catch (error) {toolNotice(lookupStatus,error.message);}
  });

  $('[data-jarvis-compose-form]').addEventListener('submit',async event=>{
    event.preventDefault();
    const submit=event.target.querySelector('button[type="submit"]');
    if (submit.disabled) return;
    submit.disabled=true;
    toolNotice(outboxStatus,'Gerando rascunho, sem enviar...');
    try {
      const result=await api(toolUrl,{
        action:'generate',
        recipient_type:$('[data-jarvis-recipient-type]').value,
        recipient_id:Number($('[data-jarvis-recipient-id]').value),
        brief:$('[data-jarvis-brief]').value.trim()
      });
      toolNotice(outboxStatus,'Rascunho para '+result.draft.recipient.name+
        ' criado. Revise o texto antes de qualquer envio.');
      await loadDrafts();
    } catch(error) {toolNotice(outboxStatus,error.message);}
    finally {submit.disabled=false;}
  });

  launcher.addEventListener('click', event => {
    if (suppressDragClick && event.detail !== 0) {
      suppressDragClick = false;
      event.preventDefault();
      return;
    }
    suppressDragClick = false;
    open(panel.hidden);
  });
  $('[data-jarvis-close]').addEventListener('click', () => open(false));
  $('[data-jarvis-expand]').addEventListener('click', () => {
    panel.classList.toggle('is-expanded');
    positionPanelNearLauncher();
  });
  root.querySelectorAll('[data-jarvis-tab]').forEach(button => button.addEventListener('click', () => tab(button.dataset.jarvisTab)));
  $('[data-jarvis-new]').addEventListener('click', () => {
    conversationId = null;
    conversationSelect.value = '';
    clearMessages();
    status.textContent = 'Nova conversa iniciada.';
    input.focus();
  });
  $('[data-jarvis-delete]').addEventListener('click', async () => {
    if (busy || !conversationId || !window.confirm('Excluir esta conversa salva?')) return;
    try {
      await api(root.dataset.conversations, {action: 'delete', conversation_id: conversationId});
      conversationId = null;
      clearMessages();
      await loadConversations($('[data-jarvis-search]').value.trim());
      status.textContent = 'Conversa excluída.';
    } catch (error) {status.textContent = error.message;}
  });
  conversationSelect.addEventListener('change', async () => {
    if (busy) return;
    try {await loadConversation(conversationSelect.value);}
    catch (error) {status.textContent = error.message;}
  });
  $('[data-jarvis-search]').addEventListener('input', (event) => {
    window.clearTimeout(searchTimer);
    const query = event.target.value.trim();
    searchTimer = window.setTimeout(() => loadConversations(query).catch(error => {status.textContent = error.message;}), 350);
  });
  $('[data-jarvis-summary]').addEventListener('click', () => chat('summary'));
  input.addEventListener('input', resize);
  input.addEventListener('keydown', (event) => {
    if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) {
      event.preventDefault();
      $('[data-jarvis-form]').requestSubmit();
    }
  });
  $('[data-jarvis-form]').addEventListener('submit', (event) => {
    event.preventDefault();
    const value = input.value.trim();
    if (!value || busy) return;
    input.value = '';
    resize();
    chat('chat', value);
  });
  const taskExamples = {
    reports: {
      title: 'Analisar denúncias pendentes',
      description: 'Abrir a aba Reports, revisar os fatos de cada denúncia e registrar uma decisão humana.'
    },
    jobs: {
      title: 'Revisar vagas publicadas',
      description: 'Verificar descrições incompletas, anúncios repetidos e informações inconsistentes na lista de vagas.'
    },
    metrics: {
      title: 'Preparar resumo de indicadores',
      description: 'Conferir a contagem de candidatos, empresas, vagas e denúncias; preparar um relatório para a equipe.'
    }
  };
  root.querySelectorAll('[data-jarvis-task-example]').forEach(button => {
    button.addEventListener('click', () => {
      const example = taskExamples[button.dataset.jarvisTaskExample];
      if (!example) return;
      $('[data-jarvis-task-title]').value = example.title;
      $('[data-jarvis-task-description]').value = example.description;
      taskStatus.textContent = 'Exemplo preenchido. Revise e toque em Adicionar à fila.';
      $('[data-jarvis-task-title]').focus();
    });
  });
  $('[data-jarvis-task-form]').addEventListener('submit', async (event) => {
    event.preventDefault();
    const title = $('[data-jarvis-task-title]').value.trim();
    const description = $('[data-jarvis-task-description]').value.trim();
    if (!title) return;
    const button = $('[data-jarvis-task-add]');
    button.disabled = true;
    try {
      await api(root.dataset.tasks, {action: 'create', title, description});
      event.target.reset();
      await loadTasks();
    } catch (error) {taskStatus.textContent = error.message;}
    finally {button.disabled = false;}
  });
  taskList.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-task-action]');
    if (!button) return;
    const action = button.dataset.taskAction;
    const actionLabel = {start:'iniciar a análise',complete:'concluir o registro',cancel:'cancelar a atividade'}[action];
    if (!actionLabel || !window.confirm('Confirmar: ' + actionLabel + '? Isto não modifica vagas ou contas.')) return;
    button.disabled = true;
    try {
      await api(root.dataset.tasks, {action, task_id: Number(button.dataset.taskId)});
      await loadTasks();
    } catch (error) {taskStatus.textContent = error.message;button.disabled = false;}
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && !panel.hidden) open(false);
  });
  // O chat, histórico e a fila exigem sessão ADM no backend.
})();
