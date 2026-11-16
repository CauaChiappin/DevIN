/* JARVIS Admin — widgets e dados de rede são renderizados apenas com textContent. */
(() => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  'use strict'; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
  const root = document.getElementById('jarvisAdmin'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
  if (!root) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
  const $ = (selector) => root.querySelector(selector); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
  const panel = $('#jarvisAdminPanel'); // Explicação DevIN: Declara variável JavaScript panel para reutilizar valor ou resultado.
  const launcher = $('[data-jarvis-toggle]'); // Explicação DevIN: Declara variável JavaScript launcher para reutilizar valor ou resultado.
  const input = $('[data-jarvis-input]'); // Explicação DevIN: Declara variável JavaScript input para reutilizar valor ou resultado.
  const messages = $('[data-jarvis-messages]'); // Explicação DevIN: Declara variável JavaScript messages para reutilizar valor ou resultado.
  const status = $('[data-jarvis-status]'); // Explicação DevIN: Declara variável JavaScript status para reutilizar valor ou resultado.
  const taskStatus = $('[data-jarvis-task-status]'); // Explicação DevIN: Declara variável JavaScript taskStatus para reutilizar valor ou resultado.
  const send = $('[data-jarvis-send]'); // Explicação DevIN: Declara variável JavaScript send para reutilizar valor ou resultado.
  const conversationSelect = $('[data-jarvis-conversation]'); // Explicação DevIN: Declara variável JavaScript conversationSelect para reutilizar valor ou resultado.
  const taskList = $('[data-jarvis-tasks-list]'); // Explicação DevIN: Declara variável JavaScript taskList para reutilizar valor ou resultado.
  let conversationId = null; // Explicação DevIN: Declara variável JavaScript conversationId para reutilizar valor ou resultado.
  let busy = false; // Explicação DevIN: Declara variável JavaScript busy para reutilizar valor ou resultado.
  let activeTab = 'chat'; // Explicação DevIN: Declara variável JavaScript activeTab para reutilizar valor ou resultado.
  let searchTimer = null; // Explicação DevIN: Declara variável JavaScript searchTimer para reutilizar valor ou resultado.

  // Persistência local: salva apenas lado e altura proporcional, nunca credenciais.
  const docking = window.DevInJarvisDock; // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
  const dockStorageKey = 'devin:jarvisdevin:dock:v1'; // Explicação DevIN: Declara variável JavaScript dockStorageKey para reutilizar valor ou resultado.
  let savedDock = null; // Explicação DevIN: Declara variável JavaScript savedDock para reutilizar valor ou resultado.
  let dockSide = 'left'; // Explicação DevIN: Declara variável JavaScript dockSide para reutilizar valor ou resultado.
  let pointerDrag = null; // Explicação DevIN: Declara variável JavaScript pointerDrag para reutilizar valor ou resultado.
  let suppressDragClick = false; // Explicação DevIN: Declara variável JavaScript suppressDragClick para reutilizar valor ou resultado.
  try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    const stored = JSON.parse(window.localStorage.getItem(dockStorageKey) || 'null'); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
    if (stored && ['left', 'right'].includes(stored.side) && Number.isFinite(stored.yRatio)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      savedDock = {side: stored.side, yRatio: stored.yRatio}; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  } catch (_) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    // Navegação anônima/bloqueio de armazenamento: apenas não persiste.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

  function positionPanelNearLauncher() { // Explicação DevIN: Declara função/método positionPanelNearLauncher, com seus parâmetros e comportamento.
    if (panel.hidden) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    if (window.matchMedia('(max-width:600px)').matches) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      panel.style.position = ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      panel.style.left = ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      panel.style.top = ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      panel.style.right = ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      panel.style.bottom = ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    panel.style.position = 'fixed'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    panel.style.right = 'auto'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    panel.style.bottom = 'auto'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    const rect = launcher.getBoundingClientRect(); // Explicação DevIN: Declara variável JavaScript rect para reutilizar valor ou resultado.
    const width = panel.getBoundingClientRect().width; // Explicação DevIN: Declara variável JavaScript width para reutilizar valor ou resultado.
    const height = panel.getBoundingClientRect().height; // Explicação DevIN: Declara variável JavaScript height para reutilizar valor ou resultado.
    const limitX = Math.max(12, window.innerWidth - width - 12); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const limitY = Math.max(12, window.innerHeight - height - 12); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const desiredX = dockSide === 'right' ? rect.right - width : rect.left; // Explicação DevIN: Declara variável JavaScript desiredX para reutilizar valor ou resultado.
    let desiredY = rect.top - height - 10; // Explicação DevIN: Declara variável JavaScript desiredY para reutilizar valor ou resultado.
    if (desiredY < 12) desiredY = rect.bottom + 10; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    panel.style.left = Math.round(Math.max(12, Math.min(limitX, desiredX))) + 'px'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    panel.style.top = Math.round(Math.max(12, Math.min(limitY, desiredY))) + 'px'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

  function placeLauncher(position) { // Explicação DevIN: Declara função/método placeLauncher, com seus parâmetros e comportamento.
    dockSide = position.side; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    root.dataset.dockSide = dockSide; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    root.style.left = Math.round(position.left) + 'px'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    root.style.top = Math.round(position.top) + 'px'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    root.style.right = 'auto'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    root.style.bottom = 'auto'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    positionPanelNearLauncher(); // Explicação DevIN: Continua expressão ou bloco pertencente a função placeLauncher.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

  function restoreLauncher() { // Explicação DevIN: Declara função/método restoreLauncher, com seus parâmetros e comportamento.
    if (!docking || pointerDrag) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    placeLauncher(docking.restore(savedDock, window.innerWidth, window.innerHeight, // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
      launcher.offsetWidth, launcher.offsetHeight)); // Explicação DevIN: Continua expressão ou bloco pertencente a função restoreLauncher.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

  function persistLauncher(position) { // Explicação DevIN: Declara função/método persistLauncher, com seus parâmetros e comportamento.
    savedDock = {side: position.side, yRatio: position.yRatio}; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    try { window.localStorage.setItem(dockStorageKey, JSON.stringify(savedDock)); } // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    catch (_) { /* Disponibilidade de storage não afeta a navegação. */ }
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

  function initLauncherDocking() { // Explicação DevIN: Declara função/método initLauncherDocking, com seus parâmetros e comportamento.
    if (!docking || !window.PointerEvent) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    restoreLauncher(); // Explicação DevIN: Continua expressão ou bloco pertencente a função initLauncherDocking.
    launcher.addEventListener('pointerdown', event => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
      if (event.button !== 0 || event.isPrimary === false) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      suppressDragClick = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      const rect = launcher.getBoundingClientRect(); // Explicação DevIN: Declara variável JavaScript rect para reutilizar valor ou resultado.
      pointerDrag = { // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        id: event.pointerId, startX: event.clientX, startY: event.clientY, // Explicação DevIN: Continua expressão ou bloco pertencente a função initLauncherDocking.
        left: rect.left, top: rect.top, moved: false // Explicação DevIN: Continua expressão ou bloco pertencente a função initLauncherDocking.
      }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      try { launcher.setPointerCapture(event.pointerId); } catch (_) {} // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    launcher.addEventListener('pointermove', event => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
      const state = pointerDrag; // Explicação DevIN: Declara variável JavaScript state para reutilizar valor ou resultado.
      if (!state || state.id !== event.pointerId) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      const dx = event.clientX - state.startX; // Explicação DevIN: Declara variável JavaScript dx para reutilizar valor ou resultado.
      const dy = event.clientY - state.startY; // Explicação DevIN: Declara variável JavaScript dy para reutilizar valor ou resultado.
      if (!state.moved && Math.hypot(dx, dy) < 8) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      state.moved = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      launcher.classList.add('is-dragging'); // Explicação DevIN: Continua expressão ou bloco pertencente a função initLauncherDocking.
      const width = launcher.offsetWidth; // Explicação DevIN: Declara variável JavaScript width para reutilizar valor ou resultado.
      const height = launcher.offsetHeight; // Explicação DevIN: Declara variável JavaScript height para reutilizar valor ou resultado.
      const maxX = Math.max(12, window.innerWidth - width - 12); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
      const maxY = Math.max(12, window.innerHeight - height - 12); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
      root.style.left = Math.round(docking.clamp(state.left + dx, 12, maxX)) + 'px'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      root.style.top = Math.round(docking.clamp(state.top + dy, 12, maxY)) + 'px'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      root.style.right = 'auto'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      root.style.bottom = 'auto'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      positionPanelNearLauncher(); // Explicação DevIN: Continua expressão ou bloco pertencente a função initLauncherDocking.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    function finishDrag(event) { // Explicação DevIN: Declara função/método finishDrag, com seus parâmetros e comportamento.
      const state = pointerDrag; // Explicação DevIN: Declara variável JavaScript state para reutilizar valor ou resultado.
      if (!state || state.id !== event.pointerId) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      pointerDrag = null; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      launcher.classList.remove('is-dragging'); // Explicação DevIN: Continua expressão ou bloco pertencente a função finishDrag.
      try { launcher.releasePointerCapture(event.pointerId); } catch (_) {} // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
      if (!state.moved) return; // Toque simples continua abrindo o chat.
      suppressDragClick = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      const rect = launcher.getBoundingClientRect(); // Explicação DevIN: Declara variável JavaScript rect para reutilizar valor ou resultado.
      const target = docking.snap(rect.left + rect.width / 2, rect.top, // Explicação DevIN: Declara variável JavaScript target para reutilizar valor ou resultado.
        window.innerWidth, window.innerHeight, rect.width, rect.height); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
      placeLauncher(target); // Explicação DevIN: Continua expressão ou bloco pertencente a função finishDrag.
      persistLauncher(target); // Explicação DevIN: Continua expressão ou bloco pertencente a função finishDrag.
      event.preventDefault(); // Explicação DevIN: Continua expressão ou bloco pertencente a função finishDrag.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    launcher.addEventListener('pointerup', finishDrag); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    launcher.addEventListener('pointercancel', finishDrag); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    launcher.addEventListener('keydown', event => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
      if (!event.altKey || !['ArrowLeft','ArrowRight'].includes(event.key)) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      event.preventDefault(); // Explicação DevIN: Continua expressão ou bloco pertencente a função finishDrag.
      const targetSide = event.key === 'ArrowLeft' ? 'left' : 'right'; // Explicação DevIN: Declara variável JavaScript targetSide para reutilizar valor ou resultado.
      const rect = launcher.getBoundingClientRect(); // Explicação DevIN: Declara variável JavaScript rect para reutilizar valor ou resultado.
      const target = docking.snap(targetSide === 'left' ? 0 : window.innerWidth, // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        rect.top, window.innerWidth, window.innerHeight, rect.width, rect.height); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
      placeLauncher(target); // Explicação DevIN: Continua expressão ou bloco pertencente a função finishDrag.
      persistLauncher(target); // Explicação DevIN: Continua expressão ou bloco pertencente a função finishDrag.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    window.addEventListener('resize', restoreLauncher); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    window.addEventListener('orientationchange', restoreLauncher); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  initLauncherDocking(); // Explicação DevIN: Continua expressão ou bloco pertencente a função finishDrag.

  async function api(url, body = null) { // Explicação DevIN: Declara função/método api, com seus parâmetros e comportamento.
    const options = {credentials: 'same-origin', cache: 'no-store', headers: {Accept: 'application/json'}}; // Explicação DevIN: Declara variável JavaScript options para reutilizar valor ou resultado.
    if (body !== null) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      options.method = 'POST'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      options.headers['Content-Type'] = 'application/json'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      options.headers['X-CSRF-Token'] = root.dataset.csrf; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      options.body = JSON.stringify(body); // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    const response = await fetch(url, options); // Explicação DevIN: Inicia solicitação HTTP assíncrona a um endereço configurado.
    const data = await response.json().catch(() => null); // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    if (!data || !response.ok || data.success !== true) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      throw new Error(data?.message || 'Falha ao consultar o servidor. Verifique sua sessão.'); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    return data; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  function open(show) { // Explicação DevIN: Declara função/método open, com seus parâmetros e comportamento.
    panel.hidden = !show; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    launcher.setAttribute('aria-expanded', String(show)); // Explicação DevIN: Continua expressão ou bloco pertencente a função open.
    if (show) positionPanelNearLauncher(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    if (show) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      loadConversations($('[data-jarvis-search]').value.trim()).catch(error => { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        status.textContent = error.message; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      if (activeTab === 'tasks') void loadTasks(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      else if (activeTab === 'outbox') void loadDrafts(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      else if (activeTab === 'audit') void loadAudit(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      else if (activeTab === 'lookup') $('[data-jarvis-record-query]').focus(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      else input.focus(); // Explicação DevIN: Define ramo alternativo para condição anterior.
    } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
      launcher.focus(); // Explicação DevIN: Continua expressão ou bloco pertencente a função open.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  function tab(name) { // Explicação DevIN: Declara função/método tab, com seus parâmetros e comportamento.
    activeTab = name; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    root.querySelectorAll('[data-jarvis-tab]').forEach((button) => { // Explicação DevIN: Continua expressão ou bloco pertencente a função tab.
      const selected = button.dataset.jarvisTab === name; // Explicação DevIN: Declara variável JavaScript selected para reutilizar valor ou resultado.
      button.classList.toggle('is-active', selected); // Explicação DevIN: Continua expressão ou bloco pertencente a função tab.
      button.setAttribute('aria-pressed', String(selected)); // Explicação DevIN: Continua expressão ou bloco pertencente a função tab.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    root.querySelectorAll('[data-jarvis-pane]').forEach((pane) => { // Explicação DevIN: Continua expressão ou bloco pertencente a função tab.
      pane.hidden = pane.dataset.jarvisPane !== name; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if (name === 'tasks') loadTasks(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    else if (name === 'outbox') void loadDrafts(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    else if (name === 'audit') void loadAudit(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    else if (name === 'lookup') $('[data-jarvis-record-query]').focus(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    else input.focus(); // Explicação DevIN: Define ramo alternativo para condição anterior.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  function addMessage(role, content, metadata = null) { // Explicação DevIN: Declara função/método addMessage, com seus parâmetros e comportamento.
    $('[data-jarvis-welcome]').hidden = true; // Explicação DevIN: Continua expressão ou bloco pertencente a função addMessage.
    const item = document.createElement('div'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    item.className = 'jarvis-admin__message' + (role === 'user' ? ' is-user' : ''); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    const label = document.createElement('span'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    label.className = 'jarvis-admin__message-label'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    label.textContent = role === 'user' ? 'Você' : 'JarvisDevIn'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    const text = document.createElement('div'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    text.className = 'jarvis-admin__message-body'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    text.textContent = String(content); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    item.append(label, text); // Explicação DevIN: Continua expressão ou bloco pertencente a função addMessage.
    if (role === 'assistant' && metadata) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      const details = []; // Explicação DevIN: Declara variável JavaScript details para reutilizar valor ou resultado.
      if (metadata.source === 'local') details.push('Fonte: banco DevIN'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      if (typeof metadata.model === 'string') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        details.push('Modelo: ' + String(metadata.provider || 'API') + ' / ' + metadata.model); // Explicação DevIN: Continua expressão ou bloco pertencente a função addMessage.
      } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      if (metadata.fallback_used === true) details.push('Modelo gratuito de reserva'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      if (metadata.key_used === 'backup') details.push('Chave reserva utilizada'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      if (Array.isArray(metadata.skills) && metadata.skills.length) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        details.push('Habilidades: ' + metadata.skills.map(s => String(s.label || '')).filter(Boolean).join(', ')); // Explicação DevIN: Continua expressão ou bloco pertencente a função addMessage.
      } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      if (metadata.history_used === true) details.push('Contexto anterior utilizado'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      if (details.length) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        const meta = document.createElement('small'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        meta.className = 'jarvis-admin__response-meta'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        meta.textContent = details.join(' · '); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        item.append(meta); // Explicação DevIN: Continua expressão ou bloco pertencente a função addMessage.
      } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    messages.append(item); // Explicação DevIN: Continua expressão ou bloco pertencente a função addMessage.
    messages.scrollTop = messages.scrollHeight; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  function clearMessages() { // Explicação DevIN: Declara função/método clearMessages, com seus parâmetros e comportamento.
    messages.querySelectorAll('.jarvis-admin__message').forEach(node => node.remove()); // Explicação DevIN: Continua expressão ou bloco pertencente a função clearMessages.
    $('[data-jarvis-welcome]').hidden = false; // Explicação DevIN: Continua expressão ou bloco pertencente a função clearMessages.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  function resize() { // Explicação DevIN: Declara função/método resize, com seus parâmetros e comportamento.
    input.style.height = 'auto'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    input.style.height = Math.min(input.scrollHeight, 150) + 'px'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  async function loadConversations(search = '') { // Explicação DevIN: Declara função/método loadConversations, com seus parâmetros e comportamento.
    const data = await api(root.dataset.conversations + '?q=' + encodeURIComponent(search)); // Explicação DevIN: Declara variável JavaScript data para reutilizar valor ou resultado.
    const previous = String(conversationId ?? ''); // Explicação DevIN: Declara variável JavaScript previous para reutilizar valor ou resultado.
    conversationSelect.replaceChildren(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadConversations.
    const empty = document.createElement('option'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    empty.value = ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    empty.textContent = 'Nova conversa'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    conversationSelect.append(empty); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadConversations.
    data.conversations.forEach(row => { // Explicação DevIN: Continua expressão ou bloco pertencente a função loadConversations.
      const option = document.createElement('option'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
      option.value = String(row.id); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      option.textContent = String(row.title); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      conversationSelect.append(option); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadConversations.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    conversationSelect.value = previous; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  async function loadConversation(id) { // Explicação DevIN: Declara função/método loadConversation, com seus parâmetros e comportamento.
    if (!id) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      conversationId = null; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      clearMessages(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadConversation.
      status.textContent = ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    const data = await api(root.dataset.conversations + '?id=' + encodeURIComponent(String(id))); // Explicação DevIN: Declara variável JavaScript data para reutilizar valor ou resultado.
    conversationId = Number(id); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    clearMessages(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadConversation.
    data.messages.forEach(m => addMessage(m.role, m.content)); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadConversation.
    conversationSelect.value = String(id); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    status.textContent = ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  async function chat(mode, message = '') { // Explicação DevIN: Declara função/método chat, com seus parâmetros e comportamento.
    if (busy) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    busy = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    send.disabled = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    status.textContent = 'Consultando...'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    if (mode === 'chat') addMessage('user', message); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
      const data = await api(root.dataset.endpoint, { // Explicação DevIN: Declara variável JavaScript data para reutilizar valor ou resultado.
        mode, message, page: root.dataset.page || 'inicio', conversation_id: conversationId, // Explicação DevIN: Continua expressão ou bloco pertencente a função chat.
        use_history: $('[data-jarvis-use-history]').checked // Explicação DevIN: Continua expressão ou bloco pertencente a função chat.
      }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      addMessage('assistant', data.reply || 'Sem resposta.', data); // Explicação DevIN: Continua expressão ou bloco pertencente a função chat.
      if (mode === 'chat') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        conversationId = Number(data.conversation_id); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        await loadConversations($('[data-jarvis-search]').value.trim()).catch(() => {}); // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
      } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      status.textContent = ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    } catch (error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
      // O erro não é salvo no histórico, pois o provedor não confirmou a resposta.
      addMessage('assistant', 'Falha: ' + error.message); // Explicação DevIN: Continua expressão ou bloco pertencente a função chat.
      status.textContent = 'Solicitação não concluída.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
      busy = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      send.disabled = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      if (!panel.hidden && activeTab === 'chat') input.focus(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  const labels = {pending: 'Pendente', in_progress: 'Em análise', done: 'Concluída', cancelled: 'Cancelada'}; // Explicação DevIN: Declara variável JavaScript labels para reutilizar valor ou resultado.
  function taskButton(label, action, id) { // Explicação DevIN: Declara função/método taskButton, com seus parâmetros e comportamento.
    const button = document.createElement('button'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    button.type = 'button'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    button.textContent = label; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    button.dataset.taskAction = action; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    button.dataset.taskId = String(id); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    return button; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  function renderTasks(rows) { // Explicação DevIN: Declara função/método renderTasks, com seus parâmetros e comportamento.
    taskList.replaceChildren(); // Explicação DevIN: Continua expressão ou bloco pertencente a função renderTasks.
    if (!rows.length) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      const info = document.createElement('p'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
      info.className = 'jarvis-admin__tasks-empty'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      info.textContent = 'Nenhuma atividade registrada.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      taskList.append(info); // Explicação DevIN: Continua expressão ou bloco pertencente a função renderTasks.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    rows.forEach(task => { // Explicação DevIN: Continua expressão ou bloco pertencente a função renderTasks.
      const card = document.createElement('article'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
      card.className = 'jarvis-admin__task'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      const heading = document.createElement('strong'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
      heading.textContent = task.title; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      const state = document.createElement('span'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
      state.className = 'jarvis-admin__task-status'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      state.textContent = labels[task.status] || task.status; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      const desc = document.createElement('p'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
      desc.textContent = task.description || 'Sem descrição.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      const actions = document.createElement('div'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
      actions.className = 'jarvis-admin__task-actions'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      if (task.status === 'pending') actions.append(taskButton('Iniciar análise', 'start', task.id)); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      if (task.status === 'in_progress') actions.append(taskButton('Concluir registro', 'complete', task.id)); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      if (['pending', 'in_progress'].includes(task.status)) actions.append(taskButton('Cancelar', 'cancel', task.id)); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      card.append(heading, state, desc, actions); // Explicação DevIN: Continua expressão ou bloco pertencente a função renderTasks.
      taskList.append(card); // Explicação DevIN: Continua expressão ou bloco pertencente a função renderTasks.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  async function loadTasks() { // Explicação DevIN: Declara função/método loadTasks, com seus parâmetros e comportamento.
    taskStatus.textContent = 'Carregando...'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
      const data = await api(root.dataset.tasks); // Explicação DevIN: Declara variável JavaScript data para reutilizar valor ou resultado.
      renderTasks(data.tasks); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadTasks.
      taskStatus.textContent = ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    } catch (error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
      taskStatus.textContent = error.message; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.


  // As ferramentas são uma API separada. Nunca há comando de moderação.
  const toolUrl = root.dataset.tools; // Explicação DevIN: Declara variável JavaScript toolUrl para reutilizar valor ou resultado.
  const lookupStatus = $('[data-jarvis-lookup-status]'); // Explicação DevIN: Declara variável JavaScript lookupStatus para reutilizar valor ou resultado.
  const recordResults = $('[data-jarvis-record-results]'); // Explicação DevIN: Declara variável JavaScript recordResults para reutilizar valor ou resultado.
  const analysisArea = $('[data-jarvis-analysis]'); // Explicação DevIN: Declara variável JavaScript analysisArea para reutilizar valor ou resultado.
  const outboxStatus = $('[data-jarvis-outbox-status]'); // Explicação DevIN: Declara variável JavaScript outboxStatus para reutilizar valor ou resultado.
  const outboxList = $('[data-jarvis-outbox-list]'); // Explicação DevIN: Declara variável JavaScript outboxList para reutilizar valor ou resultado.
  const auditStatus = $('[data-jarvis-audit-status]'); // Explicação DevIN: Declara variável JavaScript auditStatus para reutilizar valor ou resultado.
  const auditList = $('[data-jarvis-audit-list]'); // Explicação DevIN: Declara variável JavaScript auditList para reutilizar valor ou resultado.

  function toolNotice(node, text) { node.textContent = String(text); } // Explicação DevIN: Declara função/método toolNotice, com seus parâmetros e comportamento.

  async function loadDrafts() { // Explicação DevIN: Declara função/método loadDrafts, com seus parâmetros e comportamento.
    toolNotice(outboxStatus, 'Carregando rascunhos...'); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDrafts.
    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
      const data = await api(toolUrl + '?action=drafts'); // Explicação DevIN: Declara variável JavaScript data para reutilizar valor ou resultado.
      outboxList.replaceChildren(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDrafts.
      if (!data.drafts.length) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        const info = document.createElement('p'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        info.textContent = 'Nenhuma mensagem preparada.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        outboxList.append(info); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDrafts.
      } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      data.drafts.forEach(row => { // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDrafts.
        const card = document.createElement('article'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        card.className = 'jarvis-admin__tool-card'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        const heading = document.createElement('strong'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        heading.textContent = (row.recipient_type === 'pessoa' ? 'Candidato' : 'Empresa') // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
          + ' #' + row.recipient_id + ' · ' + row.status + ' · ' + (row.source === 'ai' ? 'IA' : 'ADM'); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDrafts.
        const body = document.createElement('textarea'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        body.rows = 4; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        body.maxLength = 1200; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        body.value = row.body; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        body.readOnly = row.status !== 'draft'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        body.setAttribute('aria-label', 'Conteúdo da mensagem para revisão'); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDrafts.
        const actions = document.createElement('div'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        actions.className = 'jarvis-admin__tool-actions'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        if (row.status === 'draft' || row.status === 'failed') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
          const send = document.createElement('button'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
          send.type = 'button'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
          send.textContent = 'Revisar e enviar'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
          send.addEventListener('click', async () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
            if (send.disabled) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            if (row.status === 'draft' && body.value.trim() !== row.body) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
              try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                await api(toolUrl, {action:'revise', draft_id:Number(row.id), body:body.value.trim()}); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDrafts.
              } catch (error) {toolNotice(outboxStatus,error.message);return;} // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            const recipient = (row.recipient_type === 'pessoa' ? 'candidato' : 'empresa') + ' #' + row.recipient_id; // Explicação DevIN: Declara variável JavaScript recipient para reutilizar valor ou resultado.
            const confirmed = window.prompt('Confira o destinatário (' + recipient // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
              + ') e o texto acima. Digite ENVIAR para confirmar o envio único:'); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDrafts.
            if (confirmed !== 'ENVIAR') return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            send.disabled = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
              await api(toolUrl, {action:'send', draft_id:Number(row.id), confirmed:true, approval:'ENVIAR'}); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDrafts.
              toolNotice(outboxStatus, 'Mensagem enviada como Support DevIN.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDrafts.
              await loadDrafts(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDrafts.
            } catch (error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
              toolNotice(outboxStatus, error.message); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDrafts.
              send.disabled = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
          }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
          actions.append(send); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDrafts.
          const cancel = document.createElement('button'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
          cancel.type = 'button'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
          cancel.textContent = 'Descartar'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
          cancel.addEventListener('click', async () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
            if (!window.confirm('Descartar o rascunho?')) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            cancel.disabled = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
              await api(toolUrl, {action:'cancel', draft_id:Number(row.id)}); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDrafts.
              await loadDrafts(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDrafts.
            } catch (error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
              toolNotice(outboxStatus,error.message);cancel.disabled = false; // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDrafts.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
          }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
          actions.append(cancel); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDrafts.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        card.append(heading,body,actions); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDrafts.
        outboxList.append(card); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadDrafts.
      }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      if (!data.drafts.length) toolNotice(outboxStatus, ''); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      else if (outboxStatus.textContent === 'Carregando rascunhos...') toolNotice(outboxStatus,''); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    } catch (error) {toolNotice(outboxStatus,error.message);} // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

  async function loadAudit() { // Explicação DevIN: Declara função/método loadAudit, com seus parâmetros e comportamento.
    toolNotice(auditStatus,'Carregando histórico...'); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
      const data = await api(toolUrl + '?action=history'); // Explicação DevIN: Declara variável JavaScript data para reutilizar valor ou resultado.
      auditList.replaceChildren(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
      if (!data.events.length) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        const text = document.createElement('p');text.textContent='Ainda não há ações registradas.'; // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        auditList.append(text); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
      } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      data.events.forEach(row => { // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
        const card=document.createElement('article'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        card.className='jarvis-admin__tool-card jarvis-admin__audit-entry'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        const heading=document.createElement('strong'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        heading.textContent=(row.origin === 'ai'?'IA':row.origin === 'admin'?'ADM':'Sistema') // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
          + ' · ADM #' + row.admin_id + ' · ' + row.event_name + ' · ' + row.status; // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
        const detail=document.createElement('small'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        detail.textContent=row.created_at + ' · ' + row.target_type // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
          + (row.target_id ? ' #' + row.target_id : ''); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
        card.append(heading,detail); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
        auditList.append(card); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
      }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      toolNotice(auditStatus,''); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
    } catch (error) {toolNotice(auditStatus,error.message);} // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

  $('[data-jarvis-audit-refresh]').addEventListener('click', () => void loadAudit()); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
  $('[data-jarvis-lookup-form]').addEventListener('submit', async event => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    event.preventDefault(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
    toolNotice(lookupStatus,'Consultando registros...'); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
    analysisArea.textContent=''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    recordResults.replaceChildren(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
    const kind=$('[data-jarvis-record-kind]').value; // Explicação DevIN: Declara variável JavaScript kind para reutilizar valor ou resultado.
    const query=$('[data-jarvis-record-query]').value.trim(); // Explicação DevIN: Declara variável JavaScript query para reutilizar valor ou resultado.
    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
      const data=await api(toolUrl + '?action=search&kind=' + encodeURIComponent(kind) // Explicação DevIN: Declara variável JavaScript data para reutilizar valor ou resultado.
        + '&q=' + encodeURIComponent(query)); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
      data.records.forEach(record => { // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
        const card=document.createElement('article'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        card.className='jarvis-admin__tool-card'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        const title=document.createElement('strong'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        title.textContent='#'+record.id+' · '+(record.title||record.name||kind); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        const subtitle=document.createElement('small'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        subtitle.textContent=[record.subtitle,record.motivo,record.status].filter(Boolean).join(' · '); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        const button=document.createElement('button'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        button.type='button';button.textContent='Analisar com IA'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        button.addEventListener('click',async()=>{ // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
          if (button.disabled) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
          button.disabled=true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
          toolNotice(lookupStatus,'Analisando registro selecionado...'); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
          try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            const result=await api(toolUrl,{ // Explicação DevIN: Declara variável JavaScript result para reutilizar valor ou resultado.
              action:'analyze',kind,id:Number(record.id), // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
              question:$('[data-jarvis-record-question]').value.trim() // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            analysisArea.textContent=result.reply; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            toolNotice(lookupStatus,'Análise concluída. Nenhum dado foi alterado.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
          } catch (error) {toolNotice(lookupStatus,error.message);} // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
          finally {button.disabled=false;} // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        card.append(title,subtitle,button); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
        recordResults.append(card); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
      }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      if (!data.records.length) toolNotice(lookupStatus,'Nenhum resultado encontrado.'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      else toolNotice(lookupStatus,'Registros encontrados: '+data.records.length+'.'); // Explicação DevIN: Define ramo alternativo para condição anterior.
    } catch (error) {toolNotice(lookupStatus,error.message);} // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
  }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

  $('[data-jarvis-compose-form]').addEventListener('submit',async event=>{ // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    event.preventDefault(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
    const submit=event.target.querySelector('button[type="submit"]'); // Explicação DevIN: Declara variável JavaScript submit para reutilizar valor ou resultado.
    if (submit.disabled) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    submit.disabled=true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    toolNotice(outboxStatus,'Gerando rascunho, sem enviar...'); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
      const result=await api(toolUrl,{ // Explicação DevIN: Declara variável JavaScript result para reutilizar valor ou resultado.
        action:'generate', // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
        recipient_type:$('[data-jarvis-recipient-type]').value, // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
        recipient_id:Number($('[data-jarvis-recipient-id]').value), // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
        brief:$('[data-jarvis-brief]').value.trim() // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
      }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      toolNotice(outboxStatus,'Rascunho para '+result.draft.recipient.name+ // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
        ' criado. Revise o texto antes de qualquer envio.'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
      await loadDrafts(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
    } catch(error) {toolNotice(outboxStatus,error.message);} // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    finally {submit.disabled=false;} // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
  }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

  launcher.addEventListener('click', event => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    if (suppressDragClick && event.detail !== 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      suppressDragClick = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      event.preventDefault(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
      return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    suppressDragClick = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    open(panel.hidden); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
  }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  $('[data-jarvis-close]').addEventListener('click', () => open(false)); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
  $('[data-jarvis-expand]').addEventListener('click', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    panel.classList.toggle('is-expanded'); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
    positionPanelNearLauncher(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
  }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  root.querySelectorAll('[data-jarvis-tab]').forEach(button => button.addEventListener('click', () => tab(button.dataset.jarvisTab))); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
  $('[data-jarvis-new]').addEventListener('click', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    conversationId = null; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    conversationSelect.value = ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    clearMessages(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
    status.textContent = 'Nova conversa iniciada.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    input.focus(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
  }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  $('[data-jarvis-delete]').addEventListener('click', async () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    if (busy || !conversationId || !window.confirm('Excluir esta conversa salva?')) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
      await api(root.dataset.conversations, {action: 'delete', conversation_id: conversationId}); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
      conversationId = null; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      clearMessages(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
      await loadConversations($('[data-jarvis-search]').value.trim()); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
      status.textContent = 'Conversa excluída.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    } catch (error) {status.textContent = error.message;} // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
  }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  conversationSelect.addEventListener('change', async () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    if (busy) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    try {await loadConversation(conversationSelect.value);} // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
    catch (error) {status.textContent = error.message;} // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
  }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  $('[data-jarvis-search]').addEventListener('input', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    window.clearTimeout(searchTimer); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const query = event.target.value.trim(); // Explicação DevIN: Declara variável JavaScript query para reutilizar valor ou resultado.
    searchTimer = window.setTimeout(() => loadConversations(query).catch(error => {status.textContent = error.message;}), 350); // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
  }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  $('[data-jarvis-summary]').addEventListener('click', () => chat('summary')); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
  input.addEventListener('input', resize); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
  input.addEventListener('keydown', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      event.preventDefault(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
      $('[data-jarvis-form]').requestSubmit(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  $('[data-jarvis-form]').addEventListener('submit', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    event.preventDefault(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
    const value = input.value.trim(); // Explicação DevIN: Declara variável JavaScript value para reutilizar valor ou resultado.
    if (!value || busy) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    input.value = ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    resize(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
    chat('chat', value); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
  }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  const taskExamples = { // Explicação DevIN: Declara variável JavaScript taskExamples para reutilizar valor ou resultado.
    reports: { // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
      title: 'Analisar denúncias pendentes', // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
      description: 'Abrir a aba Reports, revisar os fatos de cada denúncia e registrar uma decisão humana.' // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
    }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    jobs: { // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
      title: 'Revisar vagas publicadas', // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
      description: 'Verificar descrições incompletas, anúncios repetidos e informações inconsistentes na lista de vagas.' // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
    }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    metrics: { // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
      title: 'Preparar resumo de indicadores', // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
      description: 'Conferir a contagem de candidatos, empresas, vagas e denúncias; preparar um relatório para a equipe.' // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  root.querySelectorAll('[data-jarvis-task-example]').forEach(button => { // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
    button.addEventListener('click', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
      const example = taskExamples[button.dataset.jarvisTaskExample]; // Explicação DevIN: Declara variável JavaScript example para reutilizar valor ou resultado.
      if (!example) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      $('[data-jarvis-task-title]').value = example.title; // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
      $('[data-jarvis-task-description]').value = example.description; // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
      taskStatus.textContent = 'Exemplo preenchido. Revise e toque em Adicionar à fila.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      $('[data-jarvis-task-title]').focus(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  $('[data-jarvis-task-form]').addEventListener('submit', async (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    event.preventDefault(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
    const title = $('[data-jarvis-task-title]').value.trim(); // Explicação DevIN: Declara variável JavaScript title para reutilizar valor ou resultado.
    const description = $('[data-jarvis-task-description]').value.trim(); // Explicação DevIN: Declara variável JavaScript description para reutilizar valor ou resultado.
    if (!title) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    const button = $('[data-jarvis-task-add]'); // Explicação DevIN: Declara variável JavaScript button para reutilizar valor ou resultado.
    button.disabled = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
      await api(root.dataset.tasks, {action: 'create', title, description}); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
      event.target.reset(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
      await loadTasks(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
    } catch (error) {taskStatus.textContent = error.message;} // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
    finally {button.disabled = false;} // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
  }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  taskList.addEventListener('click', async (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    const button = event.target.closest('[data-task-action]'); // Explicação DevIN: Declara variável JavaScript button para reutilizar valor ou resultado.
    if (!button) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    const action = button.dataset.taskAction; // Explicação DevIN: Declara variável JavaScript action para reutilizar valor ou resultado.
    const actionLabel = {start:'iniciar a análise',complete:'concluir o registro',cancel:'cancelar a atividade'}[action]; // Explicação DevIN: Declara variável JavaScript actionLabel para reutilizar valor ou resultado.
    if (!actionLabel || !window.confirm('Confirmar: ' + actionLabel + '? Isto não modifica vagas ou contas.')) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    button.disabled = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
      await api(root.dataset.tasks, {action, task_id: Number(button.dataset.taskId)}); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
      await loadTasks(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
    } catch (error) {taskStatus.textContent = error.message;button.disabled = false;} // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
  }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  document.addEventListener('keydown', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    if (event.key === 'Escape' && !panel.hidden) open(false); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
  }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  // O chat, histórico e a fila exigem sessão ADM no backend.
})(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
