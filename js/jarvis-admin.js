/* JARVIS Admin — somente para o dashboard ADM. Dados de API como texto, nunca HTML. */
(() => {
  'use strict';
  const root = document.getElementById('jarvisAdmin');
  if (!root) return;
  const $ = (s) => root.querySelector(s);
  const panel = $('#jarvisAdminPanel');
  const launcher = $('[data-jarvis-toggle]');
  const messages = $('[data-jarvis-messages]');
  const input = $('[data-jarvis-input]');
  const form = $('[data-jarvis-form]');
  const send = $('[data-jarvis-send]');
  const status = $('[data-jarvis-status]');
  const history = []; // Históricos persistentes por ADM serão implementados em fase posterior.
  let busy = false;
  const page = root.dataset.page || 'inicio';
  function open(value) {
    panel.hidden = !value;
    launcher.setAttribute('aria-expanded', String(value));
    if (value) input.focus();
    else launcher.focus();
  }
  function addMessage(role, content) {
    const item = document.createElement('div');
    item.className = 'jarvis-admin__message' + (role === 'user' ? ' is-user' : '');
    const label = document.createElement('span');
    label.className = 'jarvis-admin__message-label';
    label.textContent = role === 'user' ? 'Você' : 'JARVIS Admin';
    const body = document.createElement('div');
    body.className = 'jarvis-admin__message-body';
    body.textContent = content;
    item.append(label, body);
    messages.append(item);
    messages.scrollTop = messages.scrollHeight;
  }
  function resize() {
    input.style.height = 'auto';
    input.style.height = Math.min(input.scrollHeight, 150) + 'px';
  }
  async function request(mode, message = '') {
    if (busy) return;
    busy = true;
    send.disabled = true;
    status.textContent = 'Consultando...';
    if (mode === 'chat') addMessage('user', message);
    try {
      const response = await fetch(root.dataset.endpoint, {
        method: 'POST',
        credentials: 'same-origin',
        cache: 'no-store',
        headers: {'Content-Type':'application/json','X-CSRF-Token':root.dataset.csrf,'Accept':'application/json'},
        body: JSON.stringify({mode, message, page, history:history.slice(-10)})
      });
      const data = await response.json().catch(() => null);
      if (!data || !response.ok || data.success !== true) {
        throw new Error(data?.message || 'Não foi possível consultar o JARVIS. Confira sua sessão.');
      }
      const answer = String(data.reply || '');
      addMessage('assistant', answer);
      if (mode === 'chat') {
        history.push({role:'user',content:message}, {role:'assistant',content:answer});
        if (history.length > 10) history.splice(0, history.length - 10);
      }
      status.textContent = '';
    } catch (error) {
      const message = error instanceof Error ? error.message : 'Falha inesperada.';
      addMessage('assistant', 'Não consegui responder agora. ' + message);
      status.textContent = 'Falha na solicitação.';
    } finally {
      busy = false;
      send.disabled = false;
      input.focus();
    }
  }
  launcher.addEventListener('click', () => open(panel.hidden));
  $('[data-jarvis-close]').addEventListener('click', () => open(false));
  $('[data-jarvis-expand]').addEventListener('click', () => panel.classList.toggle('is-expanded'));
  $('[data-jarvis-clear]').addEventListener('click', () => {
    history.length = 0;
    messages.querySelectorAll('.jarvis-admin__message').forEach(n => n.remove());
    status.textContent = 'Conversa local limpa.';
  });
  $('[data-jarvis-summary]').addEventListener('click', () => request('summary'));
  input.addEventListener('input', resize);
  input.addEventListener('keydown', (event) => {
    if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) {
      event.preventDefault();
      form.requestSubmit();
    }
  });
  form.addEventListener('submit', (event) => {
    event.preventDefault();
    const text = input.value.trim();
    if (!text || busy) return;
    input.value = '';
    resize();
    request('chat', text);
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && !panel.hidden) open(false);
  });
})();
