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
    if (show && activeTab === 'chat') input.focus();
    else if (!show) launcher.focus();
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
    else input.focus();
  }
  function addMessage(role, content) {
    $('[data-jarvis-welcome]').hidden = true;
    const item = document.createElement('div');
    item.className = 'jarvis-admin__message' + (role === 'user' ? ' is-user' : '');
    const label = document.createElement('span');
    label.className = 'jarvis-admin__message-label';
    label.textContent = role === 'user' ? 'Você' : 'JARVIS Admin';
    const text = document.createElement('div');
    text.className = 'jarvis-admin__message-body';
    text.textContent = String(content);
    item.append(label, text);
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
        mode, message, page: root.dataset.page || 'inicio', conversation_id: conversationId
      });
      addMessage('assistant', data.reply || 'Sem resposta.');
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

  launcher.addEventListener('click', () => open(panel.hidden));
  $('[data-jarvis-close]').addEventListener('click', () => open(false));
  $('[data-jarvis-expand]').addEventListener('click', () => panel.classList.toggle('is-expanded'));
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
  loadConversations().catch((error) => {status.textContent = error.message;});
})();
