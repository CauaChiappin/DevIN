/*
 * REVISÃO DETALHADA DEVIN — js/jarvis-admin.js
 * Finalidade: tratar comandos do JARVIS e apresentar confirmações.
 * Domínio: assistência administrativa.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */

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
  let historyRestored = false; // Restaura o último chat salvo uma única vez por abertura de página.
  let localCaseLog = null; // Fila de mensagens locais associadas à conversa atualmente aberta.
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
  // Os cartões de casos e mensagens de erro eram apenas elementos temporários do navegador.
  // Este registro é um transcript do chat, NÃO uma trilha de auditoria de decisões oficiais.
  function saveVisibleMessage(role, content) {
    const value = String(content || '').trim().slice(0, 5000);
    if (!value || (role !== 'user' && role !== 'assistant')) return;
    historyRestored = true;
    const session = localCaseLog || (localCaseLog = {
      id: conversationId,
      title: role === 'user' ? value.slice(0, 80) : 'Atendimento JARVIS',
      pending: Promise.resolve()
    });
    session.pending = session.pending.then(async () => {
      const saved = await api(root.dataset.conversations, {
        action: 'append', conversation_id: session.id, title: session.title,
        entries: [{role, content: value}]
      });
      session.id = Number(saved.conversation_id);
      if (localCaseLog === session) {
        conversationId = session.id;
        await loadConversations('');
      }
    }).catch(error => {
      if (localCaseLog === session) status.textContent = 'Histórico não salvo: ' + error.message;
    });
  }

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
    const restore = !historyRestored && !search && !conversationId && data.conversations.length > 0;
    historyRestored = true;
    if (restore) await loadConversation(String(data.conversations[0].id));
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
  async function loadConversation(id) { // Explicação DevIN: Declara função/método loadConversation, com seus parâmetros e comportamento.
    if (localCaseLog) await localCaseLog.pending;
    localCaseLog = null;
    caseChat?.reset(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadConversation.
    if (!id) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      conversationId = null; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      caseChat?.reset(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadConversation.
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
  // Commands use explicit numerical IDs; the model never executes SQL.
  // Interpretar apenas comandos explícitos; conteúdo de cadastro jamais é instrução.
  // Nomes e títulos não identificam registros univocamente: sempre mostrar opções.
  /**
   * Reconhece comandos administrativos literais e retorna intenção estruturada:
   * type (search/message/delete), kind, id, name e body.
   * Sem ID, a intenção não identifica por si só uma linha do banco.
   * Texto livre é mantido no navegador; a IA externa não decide os alvos.
   */
  // Detalhe DevIN: Transforma texto escrito no chat em intenção controlada; não consulta o banco nem permite que um modelo decida qual registro modificar.
  function parseAdminCommand(message) { // Explicação DevIN: Declara função/método parseAdminCommand, com seus parâmetros e comportamento.
    // O pedido explícito de suporte permite ao JARVIS consultar dados
    // autorizados, sem passar nomes/e-mails de usuários ao modelo remoto.
    const supportRequest = message.normalize('NFD').replace(/[\u0300-\u036f]/g, '') // Explicação DevIN: Declara variável JavaScript supportRequest para reutilizar valor ou resultado.
      .toLowerCase().replace(/^jarvis[,!: ]*/, '').trim(); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    if (/^(?:(?:mostre|mostrar|liste|listar|veja|ver|consulte|consultar|busque|buscar|quais sao)\s+)?(?:(?:os|as|meus)\s+)?(?:chamados?|tickets?|mensagens?)\s+(?:do\s+|de\s+)?suporte\b/.test(supportRequest) // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        || /^(?:suporte|chamados de suporte)[.!?\s]*$/.test(supportRequest)) { // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
      return {type:'support',name:'suporte',id:0}; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
 // Explicação DevIN: Declara função/método parseAdminCommand, com seus parâmetros e comportamento.
    // Detalhe DevIN: Define expressões aceitas para vagas, pessoas e empresas, incluindo plural e variações com acentos; não permite nomes arbitrários de tabelas.
    const kindPattern='(vagas?|an[uú]ncios?|candidatos?|candidatas?|pessoas?|usu[aá]rios?|usu[aá]rias?|empresas?)'; // Explicação DevIN: Declara variável JavaScript kindPattern para reutilizar valor ou resultado.
    // Detalhe DevIN: Aceita formas educadas como Jarvis e por favor sem obrigar o ADM a decorar uma sintaxe rígida.
    const prefix='^(?:(?:jarvis)[,:!\\s]*|(?:por favor)[,\\s]*)?'; // Explicação DevIN: Declara variável JavaScript prefix para reutilizar valor ou resultado.
    // Detalhe DevIN: Converte sinônimos para as três categorias internas autorizadas; por exemplo, candidato e pessoa usam a mesma tabela.
    const kinds={ // Explicação DevIN: Declara variável JavaScript kinds para reutilizar valor ou resultado.
      vaga:'vaga',vagas:'vaga',anuncio:'vaga','anúncio':'vaga', // Explicação DevIN: Continua expressão ou bloco pertencente a função parseAdminCommand.
      anuncios:'vaga','anúncios':'vaga',candidato:'pessoa',candidatos:'pessoa', // Explicação DevIN: Continua expressão ou bloco pertencente a função parseAdminCommand.
      candidata:'pessoa',candidatas:'pessoa',pessoa:'pessoa',pessoas:'pessoa', // Explicação DevIN: Continua expressão ou bloco pertencente a função parseAdminCommand.
      usuario:'pessoa','usuário':'pessoa',usuarios:'pessoa','usuários':'pessoa', // Explicação DevIN: Continua expressão ou bloco pertencente a função parseAdminCommand.
      usuaria:'pessoa','usuária':'pessoa',usuarias:'pessoa','usuárias':'pessoa', // Explicação DevIN: Continua expressão ou bloco pertencente a função parseAdminCommand.
      empresa:'empresa',empresas:'empresa' // Explicação DevIN: Continua expressão ou bloco pertencente a função parseAdminCommand.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    // Detalhe DevIN: Separa tipo e identificador informado; pode retornar ID ou nome, nunca os dois como indicação inequívoca de uma conta.
    function target(kind, raw) { // Explicação DevIN: Declara função/método target, com seus parâmetros e comportamento.
      // Detalhe DevIN: Retira marcadores como #, espaços e pontuação no fim sem alterar o cadastro armazenado no MySQL.
      const cleaned=raw.trim().replace(/^[#:]\s*/, '').replace(/[!?\.]+$/, '').trim(); // Explicação DevIN: Declara variável JavaScript cleaned para reutilizar valor ou resultado.
      // Detalhe DevIN: Reconhece apenas IDs inteiros explícitos ou números isolados; nomes como Empresa 123 Ltda continuam texto para busca.
      const numeric=/^(?:(?:id|n[uú]mero|n[ºo])\s*[:#-]?\s*|#\s*)?(\d{1,10})$/i.exec(cleaned); // Explicação DevIN: Declara variável JavaScript numeric para reutilizar valor ou resultado.
      // Detalhe DevIN: Registra se houve correspondência ao padrão de ID para impedir conversão de nomes misturados com algarismos.
      const exact=Boolean(numeric); // Explicação DevIN: Declara variável JavaScript exact para reutilizar valor ou resultado.
      // Detalhe DevIN: Quando o termo representa um ID, ele pode seguir diretamente para prévia; nos demais casos exige consulta e seleção.
      const id=exact ? Number(numeric[1]) : null; // Explicação DevIN: Declara variável JavaScript id para reutilizar valor ou resultado.
      // Detalhe DevIN: Preserva a expressão textual para procurar registros no servidor, removendo apenas prefixos descritivos comuns.
      const name=exact ? '' : cleaned.replace(/^(?:com\s+o\s+nome|de\s+nome|chamad[oa]|intitulad[oa])\s+/i,'').trim(); // Explicação DevIN: Declara variável JavaScript name para reutilizar valor ou resultado.
      return {kind:kinds[kind.toLowerCase()],id,name}; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    // Detalhe DevIN: Interpreta pedidos explícitos de exclusão; não executa DELETE neste ponto, apenas identifica tipo e alvo pretendido.
    const deletion=new RegExp(prefix+'(?:exclua|excluir|delete|deletar|remova|remover|apague|apagar)\\s+(?:(?:a|o|uma|um)\\s+)?'+kindPattern+'\\s+(.+)$','i').exec(message.trim()); // Explicação DevIN: Declara variável JavaScript deletion para reutilizar valor ou resultado.
    // Detalhe DevIN: Devolve a intenção estruturada ao fluxo de confirmação; uma frase jamais serve como autorização final.
    if(deletion) return {type:'delete',...target(deletion[1],deletion[2])}; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    // Detalhe DevIN: Reconhece comandos de criar ou enviar mensagem com destinatário indicado, sem transmitir imediatamente a mensagem.
    const sendPrefix=new RegExp(prefix+'(?:envie|enviar|mande|mandar|escreva|escrever)\\s+(?:uma\\s+)?mensagem\\s+(?:para\\s+)?(?:(?:a|o|ao|à)\\s+)?'+kindPattern+'\\s+(.+)$','i').exec(message.trim()); // Explicação DevIN: Declara variável JavaScript sendPrefix para reutilizar valor ou resultado.
    if(sendPrefix) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      const rest=sendPrefix[2]; // Explicação DevIN: Declara variável JavaScript rest para reutilizar valor ou resultado.
      // Detalhe DevIN: Divide a frase entre destinatário e texto após dizendo, que diga ou com o texto, preservando a mensagem original.
      const withText=/^(.+?)\s+(?:dizendo|que\s+diga|com\s+(?:o\s+)?texto)\s*[:,-]?\s*([\s\S]+)$/i.exec(rest) // Explicação DevIN: Declara variável JavaScript withText para reutilizar valor ou resultado.
        || /^(.+?)\s*:\s*([\s\S]+)$/.exec(rest); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
      const recipient=target(sendPrefix[1],withText ? withText[1] : rest); // Explicação DevIN: Declara variável JavaScript recipient para reutilizar valor ou resultado.
      // Detalhe DevIN: Impede mensagem para uma vaga, pois apenas contas de candidatos e empresas são destinatários válidos.
      return {type:recipient.kind==='vaga'?'unsupported':'message',...recipient, // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        body:withText ? withText[2].trim() : ''}; // Explicação DevIN: Continua expressão ou bloco pertencente a função target.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    // Detalhe DevIN: Interpreta consultas como procure vagas Desenvolvedor PHP, solicitando lista de correspondências sem mutação.
    const find=new RegExp(prefix+'(?:procure|procurar|pesquise|pesquisar|encontre|buscar|busque|localize|mostre)\\s+(?:(?:por|a|o|as|os|uma|um)\\s+)*'+kindPattern+'\\s+(?:com\\s+(?:o\\s+)?nome\\s+|chamad[oa]s?\\s+|intitulad[oa]s?\\s+)?(.+)$','i').exec(message.trim()); // Explicação DevIN: Declara variável JavaScript find para reutilizar valor ou resultado.
    if(find) return {type:'search',...target(find[1],find[2])}; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    // If the ADM omitted a category, show possible people, companies or jobs.
    // Detalhe DevIN: Cria versão sem vocativo para aceitar buscas e ações quando a categoria do usuário não foi mencionada.
    const simple=message.trim().replace(/^jarvis[,:!\s]*|^por favor[,\s]*/i,''); // Explicação DevIN: Declara variável JavaScript simple para reutilizar valor ou resultado.
    const gsend=/^(?:envie|enviar|mande|mandar|escreva|escrever)\s+/i.exec(simple); // Explicação DevIN: Declara variável JavaScript gsend para reutilizar valor ou resultado.
    // Detalhe DevIN: Quando o pedido é enviar mensagem sem dizer candidato ou empresa, pesquisa ambas as categorias e pede escolha.
    if(gsend && /\bmensagem\b/i.test(simple)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      const raw=simple.replace(/^(?:envie|enviar|mande|mandar|escreva|escrever)\s+/i,''); // Explicação DevIN: Declara variável JavaScript raw para reutilizar valor ou resultado.
      const rest=raw.replace(/^(?:uma\s+)?mensagem\s+(?:para\s+)?(?:a\s+|o\s+|ao\s+|à\s+)?/i,''); // Explicação DevIN: Declara variável JavaScript rest para reutilizar valor ou resultado.
      // Detalhe DevIN: Extrai o corpo para revisão pelo ADM: a confirmação de envio continua sendo uma etapa separada.
      const bodyMatch=/^(.+?)\s+(?:dizendo|que\s+diga|com\s+o?\s*texto)\s*[:,-]?\s*([\s\S]+)$/i.exec(rest); // Explicação DevIN: Declara variável JavaScript bodyMatch para reutilizar valor ou resultado.
      const second=bodyMatch || /^(.+?)\s*:\s*([\s\S]+)$/.exec(rest); // Explicação DevIN: Declara variável JavaScript second para reutilizar valor ou resultado.
      return { // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        type:'message',kind:null,id:null, // Explicação DevIN: Continua expressão ou bloco pertencente a função target.
        name:(second ? second[1] : rest).trim(), // Explicação DevIN: Continua expressão ou bloco pertencente a função target.
        body:second ? second[2].trim() : '' // Explicação DevIN: Continua expressão ou bloco pertencente a função target.
      }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    const gdelete=/^(?:exclua|excluir|delete|deletar|remova|remover|apague|apagar)\s+(.+)$/i.exec(simple); // Explicação DevIN: Declara variável JavaScript gdelete para reutilizar valor ou resultado.
    // Detalhe DevIN: Exclusões sem categoria são tratadas como consultas para seleção, nunca como remoção do primeiro resultado.
    if(gdelete) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      const what=gdelete[1].trim(); // Explicação DevIN: Declara variável JavaScript what para reutilizar valor ou resultado.
      const categoryOnly=/^(?:a|o|uma|um)\s+(?:empresa|vaga|pessoa|candidato|candidata)$/i; // Explicação DevIN: Declara variável JavaScript categoryOnly para reutilizar valor ou resultado.
      // Detalhe DevIN: Caso seja digitado apenas Exclua a empresa, exige o nome ou ID em vez de agir sem alvo definido.
      if(categoryOnly.test(what)) return {type:'incomplete'}; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      return {type:'delete',kind:null,id:null,name:what}; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    // Detalhe DevIN: Habilita busca geral por nome sem exigir termos técnicos como candidato, empresa ou vaga.
    const gfind=/^(?:procure|pesquise|encontre|busque|localize|mostre)\s+(.+)$/i.exec(simple); // Explicação DevIN: Declara variável JavaScript gfind para reutilizar valor ou resultado.
    if(gfind) return {type:'search',kind:null,id:null,name:gfind[1].trim()}; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    if(new RegExp(prefix+'(?:exclua|excluir|delete|deletar|remova|remover|apague|apagar|envie|enviar|mande|mandar)\\b','i').test(message)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      return {type:'incomplete'}; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    // Detalhe DevIN: Se não existe comando administrativo reconhecido, a mensagem segue para o chat convencional e não aciona ferramentas.
    return null; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

  // Detalhe DevIN: Cria uma resposta visual no chat para prévia e seleção; o texto exibido não é uma autorização de backend.
  function chatActionCard(text) { // Explicação DevIN: Declara função/método chatActionCard, com seus parâmetros e comportamento.
    addMessage('assistant',text); // Explicação DevIN: Continua expressão ou bloco pertencente a função chatActionCard.
    return messages.lastElementChild; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

  // Detalhe DevIN: Gera um botão de ação com callback específico; apenas o clique real do ADM inicia a próxima etapa.
  function actionButton(label,callback) { // Explicação DevIN: Declara função/método actionButton, com seus parâmetros e comportamento.
    const button=document.createElement('button'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    button.type='button'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    button.textContent=label; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    button.addEventListener('click',()=>void callback(button)); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    return button; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

  /**
   * Controla o fluxo da conversa administrativa sem executar SQL no browser.
   * selected é preenchido somente ao clicar em um resultado identificado.
   * Requisições usam o endpoint autenticado com limite de uso e CSRF.
   * A aprovação de uma ação ocorre em outra chamada, depois da prévia.
   */
  // Detalhe DevIN: Orquestra consulta, seleção e confirmação no frontend, utilizando apenas endpoints autenticados da aplicação.
  // Todas as mensagens dos casos são exibidas e persistidas em ordem no MySQL do ADM.
  const addCaseMessage = (role, text, metadata = null) => {
    addMessage(role, text, metadata);
    saveVisibleMessage(role, text);
  };
  const caseCard = text => {
    addCaseMessage('assistant', text);
    return messages.lastElementChild;
  };
  const caseChat = window.DevInJarvisCases?.init({
    root, api, addMessage: addCaseMessage, chatActionCard: caseCard, actionButton
  }) || null; // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.

  async function handleAdminCommand(message, selected=null) { // Explicação DevIN: Declara função/método handleAdminCommand, com seus parâmetros e comportamento.
    // Detalhe DevIN: Um resultado já escolhido prevalece sobre a interpretação original da frase: usa o ID da seleção para evitar ambiguidade.
    const command=selected || parseAdminCommand(message); // Explicação DevIN: Declara variável JavaScript command para reutilizar valor ou resultado.
    if(!command) return false; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    // Detalhe DevIN: Exibe a pergunta do ADM somente uma vez; a seleção posterior não duplica a mensagem no histórico visual.
    if(!selected) addMessage('user',message); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    // Detalhe DevIN: Recusa categorias incompatíveis com a operação, como tentar enviar mensagem diretamente a uma vaga.
    if(command.type==='support') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      // Esta consulta não executa ações de escrita nem expõe anexos ao modelo.
      try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        const data=await api(root.dataset.tools+'?action=support'); // Explicação DevIN: Declara variável JavaScript data para reutilizar valor ou resultado.
        const rows=data.tickets || []; // Explicação DevIN: Declara variável JavaScript rows para reutilizar valor ou resultado.
        const card=chatActionCard(rows.length // Explicação DevIN: Declara variável JavaScript card para reutilizar valor ou resultado.
          ? 'Chamados de suporte recebidos: '+rows.length+' registros recentes.' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
          : 'Não há chamados recentes no suporte.'); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        const list=document.createElement('div'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        list.className='jarvis-admin__tool-results'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        card.append(list); // Explicação DevIN: Continua expressão ou bloco pertencente a função handleAdminCommand.
        for(const ticket of rows){ // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
          const item=document.createElement('article'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
          item.className='jarvis-admin__tool-card'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
          const name=document.createElement('strong'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
          name.textContent='#'+ticket.id+' · '+ticket.requester_name+' · '+ticket.requester_email; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
          const description=document.createElement('p'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
          description.textContent='Categoria: '+ticket.category+' · '+ticket.status+' · '+ticket.description; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
          const details=actionButton('Ver respostas',async button=>{ // Explicação DevIN: Declara variável JavaScript details para reutilizar valor ou resultado.
            button.disabled=true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
              const response=await api(root.dataset.tools+'?action=support&id='+encodeURIComponent(ticket.id)); // Explicação DevIN: Declara variável JavaScript response para reutilizar valor ou resultado.
              const replies=(response.replies||[]).map(r=>'ADM: '+r.message).join('\n\n'); // Explicação DevIN: Declara variável JavaScript replies para reutilizar valor ou resultado.
              addMessage('assistant','Chamado #'+ticket.id+'\n'+response.ticket.description+ // Explicação DevIN: Continua expressão ou bloco pertencente a função handleAdminCommand.
                '\n\n'+(replies || 'Ainda sem respostas.')); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
            } catch(error) { addMessage('assistant',error.message); } // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            finally { button.disabled=false; } // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
          }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
          item.append(name,description,details);list.append(item); // Explicação DevIN: Continua expressão ou bloco pertencente a função handleAdminCommand.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      } catch(error) { addMessage('assistant','Não foi possível consultar o suporte: '+error.message); } // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
      return true; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if(command.type==='unsupported') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      addMessage('assistant','Mensagens podem ser enviadas a candidatos ou empresas, não a vagas.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função handleAdminCommand.
      return true; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    // Detalhe DevIN: Impede continuar quando faltam nome/ID ou quando um suposto ID não é número inteiro positivo seguro.
    if(command.type==='incomplete' || (!command.name && (!Number.isSafeInteger(command.id) || command.id<1))) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      addMessage('assistant','Informe o tipo e um nome/título ou ID. Exemplos: "Exclua a vaga Desenvolvedor PHP" ou "Envie mensagem para empresa Acme dizendo: Olá".'); // Explicação DevIN: Continua expressão ou bloco pertencente a função handleAdminCommand.
      return true; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    // Se veio um nome sem resultado selecionado, consultar o MySQL.
    // No modo "search" somente exibir resultados, sem ações.
    // Cada opção conserva seu ID verdadeiro e a classificação textual.
    // Até 12 candidatos são mostrados; o ADM escolhe manualmente.
    // Detalhe DevIN: Nomes e títulos podem se repetir: mostra alternativas em vez de escolher automaticamente a primeira.
    if(command.name && !selected || command.type==='search') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      // Detalhe DevIN: Explica que a ordenação por similaridade é heurística; não é uma porcentagem confiável de identificação.
      const resultsCard=chatActionCard('Pesquisando no banco DevIN: "'+(command.name || command.id)+'". Os resultados abaixo são ordenados por similaridade, não representam uma probabilidade de acerto.'); // Explicação DevIN: Declara variável JavaScript resultsCard para reutilizar valor ou resultado.
      const results=document.createElement('div'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
      results.className='jarvis-admin__tool-results'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      resultsCard.append(results); // Explicação DevIN: Continua expressão ou bloco pertencente a função handleAdminCommand.
      // Detalhe DevIN: Escolhe categorias a consultar: para mensagens só pessoas/empresas; para buscas/exclusões, inclui vagas.
      const kinds=command.kind ? [command.kind] : (command.type==='message' ? ['pessoa','empresa'] : ['pessoa','empresa','vaga']); // Explicação DevIN: Declara variável JavaScript kinds para reutilizar valor ou resultado.
      // Detalhe DevIN: Agrega os candidatos retornados pelo banco; cada item conserva categoria, ID, título e motivo da correspondência.
      const choices=[]; // Explicação DevIN: Declara variável JavaScript choices para reutilizar valor ou resultado.
      try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        // Detalhe DevIN: Consulta cada categoria aprovada separadamente para impedir consulta SQL genérica construída a partir da frase do ADM.
        for(const kind of kinds) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
          const answer=await api(root.dataset.tools+'?action=suggest&kind='+encodeURIComponent(kind)+'&q='+encodeURIComponent(command.name || String(command.id))); // Explicação DevIN: Declara variável JavaScript answer para reutilizar valor ou resultado.
          choices.push(...answer.records); // Explicação DevIN: Continua expressão ou bloco pertencente a função handleAdminCommand.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      }catch(error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        addMessage('assistant','Falha na pesquisa de nomes: '+error.message); // Explicação DevIN: Continua expressão ou bloco pertencente a função handleAdminCommand.
        return true; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
      } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      const typeNames={vaga:'Vaga',pessoa:'Candidato',empresa:'Empresa'}; // Explicação DevIN: Declara variável JavaScript typeNames para reutilizar valor ou resultado.
      // Detalhe DevIN: Ordena por relevância e depois ID; empate não elimina candidatos nem dispensa confirmação da identidade.
      choices.sort((x,y)=>y.score-x.score || y.id-x.id); // Explicação DevIN: Continua expressão ou bloco pertencente a função handleAdminCommand.
      // Detalhe DevIN: Quando não há resultados, informa que nada foi encontrado e não tenta inventar ou inferir IDs.
      if(!choices.length) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        const none=document.createElement('p'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        none.textContent='Nenhum registro encontrado. Tente outro nome, parte do título ou pesquise pelo ID.'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        results.append(none); // Explicação DevIN: Continua expressão ou bloco pertencente a função handleAdminCommand.
        return true; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
      } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      // Detalhe DevIN: Mostra até doze cartões para o ADM comparar registros homônimos, incluindo empresa associada quando disponível.
      for(const record of choices.slice(0,12)) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
        const card=document.createElement('article'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        card.className='jarvis-admin__tool-card'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        const title=document.createElement('strong'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        title.textContent=typeNames[record.kind]+' #'+record.id+' — '+record.title; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        const desc=document.createElement('small'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        desc.textContent=[record.subtitle,record.match].filter(Boolean).join(' · '); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        const controls=document.createElement('div'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        controls.className='jarvis-admin__tool-actions'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        // Detalhe DevIN: Ver detalhes abre um resumo do registro específico selecionado, sem executar ações de escrita no banco.
        const inspect=actionButton('Ver detalhes',async button=>{ // Explicação DevIN: Declara variável JavaScript inspect para reutilizar valor ou resultado.
          button.disabled=true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
          try{ // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            // Detalhe DevIN: Consulta dados limitados do registro pelo ID real; o frontend apenas apresenta o resumo retornado pelo backend.
            const data=await api(root.dataset.tools+'?action=inspect&kind='+encodeURIComponent(record.kind)+'&id='+encodeURIComponent(record.id)); // Explicação DevIN: Declara variável JavaScript data para reutilizar valor ou resultado.
            const details=document.createElement('p'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            details.textContent=Object.entries(data.record).filter(([key])=>!['id'].includes(key)).map(([key,value])=>key+': '+String(value)).join(' · ').slice(0,850); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            const old=card.querySelector('[data-jarvis-suggest-details]'); // Explicação DevIN: Declara variável JavaScript old para reutilizar valor ou resultado.
            if(old)old.remove(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            details.dataset.jarvisSuggestDetails=''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            card.append(details); // Explicação DevIN: Continua expressão ou bloco pertencente a função handleAdminCommand.
          }catch(error){addMessage('assistant','Não foi possível consultar detalhes: '+error.message);} // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
          finally{button.disabled=false;} // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        controls.append(inspect); // Explicação DevIN: Continua expressão ou bloco pertencente a função handleAdminCommand.
        if(command.type!=='search') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
          // Detalhe DevIN: Selecionar transforma um resultado de pesquisa em alvo concreto para a ação original, mas ainda não envia nem exclui.
          const choose=actionButton('Selecionar '+typeNames[record.kind].toLowerCase(),async button=>{ // Explicação DevIN: Declara variável JavaScript choose para reutilizar valor ou resultado.
            button.disabled=true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
              await handleAdminCommand(message,{...command,id:record.id,kind:record.kind,name:''}); // Explicação DevIN: Continua expressão ou bloco pertencente a função handleAdminCommand.
              results.querySelectorAll('button').forEach(item=>{item.disabled=true;}); // Explicação DevIN: Continua expressão ou bloco pertencente a função handleAdminCommand.
            }catch(error){addMessage('assistant','Falha na seleção: '+error.message);button.disabled=false;} // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
          }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
          controls.append(choose); // Explicação DevIN: Continua expressão ou bloco pertencente a função handleAdminCommand.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        card.append(title,desc,controls);results.append(card); // Explicação DevIN: Continua expressão ou bloco pertencente a função handleAdminCommand.
      } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      return true; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    const toolUrl=root.dataset.tools; // Explicação DevIN: Declara variável JavaScript toolUrl para reutilizar valor ou resultado.
    // Nunca excluir pelo nome recebido em texto: o backend prepara uma
    // prévia do registro já escolhido por ID, com nonce temporário.
    // Só enviar confirm_delete após confirmação e palavra EXCLUIR.
    // A API revalida o registro e registra a operação na auditoria.
    // Detalhe DevIN: Após a escolha, pede ao backend uma prévia vinculada ao administrador e ao registro, sem executar DELETE.
    if(command.type==='delete') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      let preview; // Explicação DevIN: Declara variável JavaScript preview para reutilizar valor ou resultado.
      // Detalhe DevIN: A API gera um nonce efêmero e confirma que o registro ainda existe antes de exibir a opção de exclusão.
      try {preview=(await api(toolUrl,{action:'preview_delete',kind:command.kind,id:command.id})).proposal;} // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
      catch(error) {addMessage('assistant','Não foi possível preparar a exclusão: '+error.message);return true;} // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
      const record=preview.record; // Explicação DevIN: Declara variável JavaScript record para reutilizar valor ou resultado.
      const card=chatActionCard('Confirme o registro: '+record.kind+' #'+record.id+' — '+record.title+ // Explicação DevIN: Declara variável JavaScript card para reutilizar valor ou resultado.
        '. Exclusão definitiva, sujeita a vínculos no banco. Nenhuma alteração foi feita.'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
      const controls=document.createElement('div'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
      controls.className='jarvis-admin__tool-actions'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      const cancel=actionButton('Cancelar',async()=>{controls.remove();status.textContent='Exclusão cancelada.';}); // Explicação DevIN: Declara variável JavaScript cancel para reutilizar valor ou resultado.
      // Detalhe DevIN: A exclusão requer clique no botão, janela de confirmação e palavra EXCLUIR; nenhuma resposta da IA pula essas etapas.
      const approve=actionButton('Confirmar exclusão',async button=>{ // Explicação DevIN: Declara variável JavaScript approve para reutilizar valor ou resultado.
        if(!window.confirm('Excluir definitivamente '+record.kind+' #'+record.id+' — '+record.title+'?'))return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if(window.prompt('Digite EXCLUIR para confirmar a exclusão:')!=='EXCLUIR')return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        button.disabled=true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
          // Detalhe DevIN: Apenas a chamada final pode alterar o banco; o servidor valida nonce, prazo, ID e registra auditoria.
          const result=await api(toolUrl,{action:'confirm_delete',kind:record.kind,id:record.id,confirmation:preview.confirmation,approval:'EXCLUIR'}); // Explicação DevIN: Declara variável JavaScript result para reutilizar valor ou resultado.
          controls.remove(); // Explicação DevIN: Continua expressão ou bloco pertencente a função handleAdminCommand.
          addMessage('assistant',result.message+' ID #'+record.id+'.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função handleAdminCommand.
        }catch(error){addMessage('assistant','Exclusão não realizada: '+error.message);controls.remove();} // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
      }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      controls.append(approve,cancel);card.append(controls); // Explicação DevIN: Continua expressão ou bloco pertencente a função handleAdminCommand.
      return true; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    // Primeiro criar um rascunho para um destinatário de ID confirmado.
    // O ADM pode editar o conteúdo; o envio só ocorre em chamada
    // separada com confirmação explícita ENVIAR.
    // A mensagem não é enviada automaticamente quando o texto é escrito.
    // Detalhe DevIN: Mensagens seguem fluxo próprio: preparar rascunho, mostrar nome da conta, revisar o texto e confirmar o envio.
    if(command.type==='message') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      // Detalhe DevIN: Não cria rascunho vazio nem com mais de 1200 caracteres; solicita um texto válido ao ADM.
      if(!command.body || command.body.length>1200) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        addMessage('assistant','Informe o texto completo da mensagem, com até 1.200 caracteres. Exemplo: "Envie mensagem para pessoa ID 12 dizendo: Olá, precisamos revisar sua vaga."'); // Explicação DevIN: Continua expressão ou bloco pertencente a função handleAdminCommand.
        return true; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
      } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      let draft; // Explicação DevIN: Declara variável JavaScript draft para reutilizar valor ou resultado.
      // Detalhe DevIN: Cria rascunho para um destinatário confirmado, mas não envia a mensagem nem dispara notificação.
      try {draft=(await api(toolUrl,{action:'manual_draft',recipient_type:command.kind,recipient_id:command.id,body:command.body})).draft;} // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
      catch(error){addMessage('assistant','Não foi possível preparar a mensagem: '+error.message);return true;} // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
      const recipient=draft.recipient; // Explicação DevIN: Declara variável JavaScript recipient para reutilizar valor ou resultado.
      const card=chatActionCard('Rascunho para '+recipient.type+' #'+recipient.id+' — '+recipient.name+ // Explicação DevIN: Declara variável JavaScript card para reutilizar valor ou resultado.
        '. Confira o texto antes de enviar. Nada foi enviado ainda.'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
      // Detalhe DevIN: Oferece edição final do conteúdo pelo ADM; este texto é o efetivamente revisado antes de enviar.
      const editor=document.createElement('textarea'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
      editor.rows=4;editor.maxLength=1200;editor.value=draft.body; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      editor.setAttribute('aria-label','Revise a mensagem antes do envio'); // Explicação DevIN: Continua expressão ou bloco pertencente a função handleAdminCommand.
      const controls=document.createElement('div');controls.className='jarvis-admin__tool-actions'; // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
      const cancel=actionButton('Descartar',async button=>{ // Explicação DevIN: Declara variável JavaScript cancel para reutilizar valor ou resultado.
        button.disabled=true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        try{await api(toolUrl,{action:'cancel',draft_id:Number(draft.id)});controls.remove();editor.remove();} // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
        catch(error){addMessage('assistant','Falha ao descartar: '+error.message);button.disabled=false;} // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
      }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      // Detalhe DevIN: Mostra conteúdo e identidade do destinatário, exigindo confirmação humana explícita para uma entrega única.
      const approve=actionButton('Revisar e enviar',async button=>{ // Explicação DevIN: Declara variável JavaScript approve para reutilizar valor ou resultado.
        const body=editor.value.trim(); // Explicação DevIN: Declara variável JavaScript body para reutilizar valor ou resultado.
        if(!body || body.length>1200){addMessage('assistant','Texto inválido.');return;} // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if(!window.confirm('Enviar esta mensagem para '+recipient.name+' (#'+recipient.id+')?\n\n'+body))return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if(window.prompt('Digite ENVIAR para confirmar o envio único:')!=='ENVIAR')return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        button.disabled=true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        try{ // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
          if(body!==draft.body) await api(toolUrl,{action:'revise',draft_id:Number(draft.id),body}); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
          // Detalhe DevIN: Envia a mensagem ao outbox do DevIN somente depois de revisão e ENVIAR; erros não são apresentados como sucesso.
          await api(toolUrl,{action:'send',draft_id:Number(draft.id),confirmed:true,approval:'ENVIAR'}); // Explicação DevIN: Continua expressão ou bloco pertencente a função handleAdminCommand.
          controls.remove();editor.readOnly=true; // Explicação DevIN: Continua expressão ou bloco pertencente a função handleAdminCommand.
          addMessage('assistant','Mensagem enviada pelo DevIN a '+recipient.name+' (#'+recipient.id+').'); // Explicação DevIN: Continua expressão ou bloco pertencente a função handleAdminCommand.
        }catch(error){addMessage('assistant','Envio não confirmado: '+error.message);button.disabled=false;} // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
      }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
      controls.append(approve,cancel);card.append(editor,controls); // Explicação DevIN: Continua expressão ou bloco pertencente a função handleAdminCommand.
      return true; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    return false; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
  } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

  async function chat(mode, message = '') { // Explicação DevIN: Declara função/método chat, com seus parâmetros e comportamento.
    if (busy) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    busy = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    send.disabled = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    status.textContent = 'Consultando...'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    if (mode === 'chat') addMessage('user', message); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
      if (mode === 'chat' && localCaseLog) await localCaseLog.pending;
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
      const visibleError = 'Falha: ' + error.message;
      addMessage('assistant', visibleError);
      if (mode === 'chat') {
        saveVisibleMessage('user', message);
        saveVisibleMessage('assistant', visibleError);
      } // Explicação DevIN: Continua expressão ou bloco pertencente a função chat.
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
        if (['vaga','pessoa','empresa'].includes(kind)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
          const remove=document.createElement('button'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
          remove.type='button'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
          remove.textContent='Solicitar exclusão'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
          remove.addEventListener('click',async()=>{ // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
            if (remove.disabled) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            remove.disabled=true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
              const result=await api(toolUrl,{ // Explicação DevIN: Declara variável JavaScript result para reutilizar valor ou resultado.
                action:'preview_delete',kind,id:Number(record.id) // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
              }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
              const proposal=result.proposal; // Explicação DevIN: Declara variável JavaScript proposal para reutilizar valor ou resultado.
              const label=proposal.record.kind+' #'+proposal.record.id+' — '+proposal.record.title; // Explicação DevIN: Declara variável JavaScript label para reutilizar valor ou resultado.
              const agreed=window.confirm('Confirme o registro que deseja excluir:\n'+label+ // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                '\n\nEsta exclusão é definitiva e pode afetar dados relacionados. Continuar?'); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
              if (!agreed) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                toolNotice(lookupStatus,'Exclusão cancelada; nenhuma alteração realizada.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
                return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
              } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
              const typed=window.prompt('Para excluir '+label+', digite EXCLUIR:'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
              if (typed !== 'EXCLUIR') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                toolNotice(lookupStatus,'Confirmação recusada. Nenhuma alteração realizada.'); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
                return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
              } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
              const done=await api(toolUrl,{ // Explicação DevIN: Declara variável JavaScript done para reutilizar valor ou resultado.
                action:'confirm_delete',kind,id:Number(record.id), // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
                confirmation:proposal.confirmation,approval:typed // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
              }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
              toolNotice(lookupStatus,done.message); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
              card.remove(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
            } catch(error) {toolNotice(lookupStatus,error.message);} // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            finally {remove.disabled=false;} // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
          }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
          card.append(title,subtitle,button,remove); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
        } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
          card.append(title,subtitle,button); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
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
    historyRestored = true;
    localCaseLog = null;
    caseChat?.reset(); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
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
      localCaseLog = null; // Isola os registros da conversa recém-excluída.
      caseChat?.reset(); // Não reutiliza o caso selecionado após excluir a conversa.
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
  $('[data-jarvis-summary]').addEventListener('click', async () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    if (busy) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    if (root.dataset.toolsEnabled !== '1') { void chat('summary'); return; } // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    busy = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
      const data = await api(root.dataset.tools + '?action=overview'); // Explicação DevIN: Declara variável JavaScript data para reutilizar valor ou resultado.
      const overview = data.overview || {}; // Explicação DevIN: Declara variável JavaScript overview para reutilizar valor ou resultado.
      const captions = [['empresas','Empresas'], ['pessoas','Pessoas'], ['vagas','Vagas'], ['candidaturas','Candidaturas'], ['contratacoes','Contratações confirmadas'], ['denuncias_pendentes','Denúncias pendentes'], ['suporte_aberto','Chamados abertos']]; // Explicação DevIN: Declara variável JavaScript captions para reutilizar valor ou resultado.
      const text = 'Resumo verificado do DevIN (MySQL):\n' + captions.map(([key,label]) => label + ': ' + (Number.isFinite(overview[key]) ? new Intl.NumberFormat('pt-BR').format(overview[key]) : 'indisponível')).join('\n'); // Explicação DevIN: Declara variável JavaScript text para reutilizar valor ou resultado.
      addMessage('assistant', text, {source:'local'}); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
    } catch (error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
      addMessage('assistant', 'Não foi possível consultar agora as estatísticas do MySQL: ' + error.message); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
    } finally { busy = false; } // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
  }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
 // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
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
    if (caseChat?.matches(value)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      busy = true; send.disabled = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      void caseChat.handle(value).catch(error => { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        addMessage('assistant', 'Não foi possível consultar o caso: ' + error.message); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
      }).finally(() => { busy = false; send.disabled = false; }); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
    } else if (parseAdminCommand(value)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      if (busy) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
      busy=true;send.disabled=true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
      void handleAdminCommand(value).catch(error=>{ // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        addMessage('assistant','Não foi possível processar o comando: '+error.message); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
      }).finally(()=>{busy=false;send.disabled=false;}); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
    } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
      chat('chat', value); // Explicação DevIN: Continua expressão ou bloco pertencente a função loadAudit.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
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
