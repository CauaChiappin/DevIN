/*
 * REVISÃO DETALHADA DEVIN — js/dashboard.js
 * Finalidade: dashboard.
 * Domínio: interações do navegador.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */

(() => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    'use strict'; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.

    const dashboardShell = document.querySelector('.dashboard-shell'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const menuToggle = document.querySelector('[data-toggle-menu]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const detailText = document.querySelector('#detailText'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const detailTitle = document.querySelector('#detailTitle'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const settingsModal = document.querySelector('#settingsModal'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const profileModal = document.querySelector('#profileModal'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const jobReportDialog = document.querySelector('[data-job-report-dialog]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const jobReportForm = jobReportDialog?.querySelector('[data-job-report-form]'); // Explicação DevIN: Declara variável JavaScript jobReportForm para reutilizar valor ou resultado.
    const jobReportButton = document.querySelector('[data-report-job]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const jobReportStatus = jobReportDialog?.querySelector('[data-job-report-status]'); // Explicação DevIN: Declara variável JavaScript jobReportStatus para reutilizar valor ou resultado.
    const jobReportSubmit = jobReportDialog?.querySelector('[data-job-report-submit]'); // Explicação DevIN: Declara variável JavaScript jobReportSubmit para reutilizar valor ou resultado.
    const reportConfirmDialog = document.querySelector('[data-report-confirm-dialog]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    let pendingReportExclusionForm = null; // Explicação DevIN: Declara variável JavaScript pendingReportExclusionForm para reutilizar valor ou resultado.
    const rotatingWord = document.querySelector('[data-rotating-word]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const interfaceLocale = document.documentElement.lang === 'en' ? 'en-US' : document.documentElement.lang === 'es' ? 'es-ES' : 'pt-BR'; // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const translate = (value) => window.DevINTranslate?.(value) ?? value; // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const toastTimers = new WeakMap(); // Explicação DevIN: Declara variável JavaScript toastTimers para reutilizar valor ou resultado.
    const dismissToast = (toast) => { // Explicação DevIN: Declara variável JavaScript dismissToast para reutilizar valor ou resultado.
        const timer = toastTimers.get(toast); // Explicação DevIN: Declara variável JavaScript timer para reutilizar valor ou resultado.
        if (timer) window.clearTimeout(timer); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        toastTimers.delete(toast); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        toast.hidden = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    const showToast = (toast, message, variant = 'success') => { // Explicação DevIN: Declara variável JavaScript showToast para reutilizar valor ou resultado.
        if (!toast) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        const messageElement = toast.querySelector('[data-feedback-message], .dashboard-toast__msg'); // Explicação DevIN: Declara variável JavaScript messageElement para reutilizar valor ou resultado.
        if (messageElement) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            messageElement.textContent = message; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            messageElement.classList.remove('dashboard-toast__msg--success', 'dashboard-toast__msg--error', 'dashboard-toast__msg--warning'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            messageElement.classList.add(`dashboard-toast__msg--${variant}`); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        toast.hidden = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        const previousTimer = toastTimers.get(toast); // Explicação DevIN: Declara variável JavaScript previousTimer para reutilizar valor ou resultado.
        if (previousTimer) window.clearTimeout(previousTimer); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        toastTimers.set(toast, window.setTimeout(() => dismissToast(toast), 5000)); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    document.querySelectorAll('[data-auto-dismiss-toast]').forEach((toast) => { // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        toastTimers.set(toast, window.setTimeout(() => dismissToast(toast), 5000)); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    document.addEventListener('click', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        if (!(event.target instanceof Element)) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        const closeButton = event.target.closest('[data-toast-close]'); // Explicação DevIN: Declara variável JavaScript closeButton para reutilizar valor ou resultado.
        const toast = closeButton?.closest('[data-auto-dismiss-toast], [data-feedback-toast]'); // Explicação DevIN: Declara variável JavaScript toast para reutilizar valor ou resultado.
        if (toast) dismissToast(toast); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    window.DevINShowToast = showToast; // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.

    document.querySelectorAll('[data-job-closure-mode]').forEach((modeSelect) => { // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        const form = modeSelect.closest('form'); // Explicação DevIN: Declara variável JavaScript form para reutilizar valor ou resultado.
        const dateInput = form?.querySelector('[data-job-closure-date]'); // Explicação DevIN: Declara variável JavaScript dateInput para reutilizar valor ou resultado.
        const approvedLimit = form?.querySelector('[data-job-approved-limit]'); // Explicação DevIN: Declara variável JavaScript approvedLimit para reutilizar valor ou resultado.
        if (!form || !dateInput || !approvedLimit) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

        const updateClosureFields = () => { // Explicação DevIN: Declara variável JavaScript updateClosureFields para reutilizar valor ou resultado.
            const requiresDate = ['prazo', 'qualquer'].includes(modeSelect.value); // Explicação DevIN: Declara variável JavaScript requiresDate para reutilizar valor ou resultado.
            dateInput.required = requiresDate; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            const dateLabel = dateInput.closest('label'); // Explicação DevIN: Declara variável JavaScript dateLabel para reutilizar valor ou resultado.
            if (dateLabel) dateLabel.hidden = !requiresDate; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            approvedLimit.min = ['limite', 'qualquer'].includes(modeSelect.value) ? '1' : '0'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        modeSelect.addEventListener('change', updateClosureFields); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        updateClosureFields(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    const messagesLink = document.querySelector('[data-messages-api]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const unreadBadge = messagesLink?.querySelector('[data-message-unread-badge]'); // Explicação DevIN: Declara variável JavaScript unreadBadge para reutilizar valor ou resultado.
    const refreshMessageCount = async () => { // Explicação DevIN: Declara variável JavaScript refreshMessageCount para reutilizar valor ou resultado.
        if (!messagesLink || !unreadBadge) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        const url = new URL(messagesLink.dataset.messagesApi, window.location.href); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        url.searchParams.set('acao', 'nao_lidas'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        const response = await fetch(url, { credentials: 'same-origin' }); // Explicação DevIN: Inicia solicitação HTTP assíncrona a um endereço configurado.
        const data = await response.json(); // Explicação DevIN: Declara variável JavaScript data para reutilizar valor ou resultado.
        if (!response.ok || data.sucesso !== true) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new Error(translate(data.mensagem || 'Não foi possível atualizar as mensagens não lidas.')); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        unreadBadge.hidden = data.nao_lidas === 0; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        unreadBadge.textContent = String(data.nao_lidas); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        unreadBadge.setAttribute('aria-label', translate(`${data.nao_lidas} mensagens não lidas`)); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    if (messagesLink) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        refreshMessageCount().catch((error) => console.error(error)); // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
        window.setInterval(() => { // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            if (document.visibilityState === 'visible') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                refreshMessageCount().catch((error) => console.error(error)); // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        }, 15000); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    document.querySelectorAll('[data-report-exclusion-form]').forEach((form) => { // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        form.addEventListener('submit', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
            if (form.dataset.confirmed === 'true') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                delete form.dataset.confirmed; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            event.preventDefault(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            pendingReportExclusionForm = form; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            reportConfirmDialog?.showModal(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    document.querySelector('[data-cancel-report-exclusion]')?.addEventListener('click', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        reportConfirmDialog?.close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        pendingReportExclusionForm = null; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    document.querySelector('[data-confirm-report-exclusion]')?.addEventListener('click', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        if (!pendingReportExclusionForm) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        const form = pendingReportExclusionForm; // Explicação DevIN: Declara variável JavaScript form para reutilizar valor ou resultado.
        pendingReportExclusionForm = null; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        form.dataset.confirmed = 'true'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        reportConfirmDialog?.close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        form.requestSubmit(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    reportConfirmDialog?.addEventListener('click', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        if (event.target === reportConfirmDialog) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            reportConfirmDialog.close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            pendingReportExclusionForm = null; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    reportConfirmDialog?.addEventListener('close', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        pendingReportExclusionForm = null; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Sidebar: preferência fica salva somente no navegador deste usuário.
    if (dashboardShell && menuToggle) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        const fechado = localStorage.getItem('devin-menu-fechado') === 'true'; // Explicação DevIN: Lê ou atualiza preferências guardadas no armazenamento local do navegador.
        const mobileMenu = window.DevINDashboardMenu?.init({ // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            shell: dashboardShell, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            toggle: menuToggle, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            windowRef: window, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        const isMobile = () => mobileMenu?.isMobile?.() === true; // Explicação DevIN: Declara variável JavaScript isMobile para reutilizar valor ou resultado.
        const syncLayout = () => { // Explicação DevIN: Declara variável JavaScript syncLayout para reutilizar valor ou resultado.
            if (isMobile()) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                dashboardShell.classList.remove('menu-fechado'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                menuToggle.setAttribute('aria-expanded', String(dashboardShell.classList.contains('mobile-menu-open'))); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            dashboardShell.classList.remove('mobile-menu-open'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            dashboardShell.classList.toggle('menu-fechado', fechado); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            menuToggle.setAttribute('aria-expanded', String(!fechado)); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        syncLayout(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        window.addEventListener('resize', syncLayout); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.

        menuToggle.addEventListener('click', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
            if (isMobile()) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                document.querySelector('.perfil-dropdown')?.removeAttribute('open'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            const novoEstado = dashboardShell.classList.toggle('menu-fechado'); // Explicação DevIN: Declara variável JavaScript novoEstado para reutilizar valor ou resultado.
            menuToggle.setAttribute('aria-expanded', String(!novoEstado)); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            localStorage.setItem('devin-menu-fechado', String(novoEstado)); // Explicação DevIN: Lê ou atualiza preferências guardadas no armazenamento local do navegador.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Cards: impede que botões/formulários internos sejam tratados como seleção do card.
    const detailPanel = document.querySelector('.profile-detail-panel'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const detailCards = [...document.querySelectorAll('.item-card[data-detail]')]; // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    let selectedActionTarget = null; // Explicação DevIN: Declara variável JavaScript selectedActionTarget para reutilizar valor ou resultado.

    const splitItems = (value, fallback) => { // Explicação DevIN: Declara variável JavaScript splitItems para reutilizar valor ou resultado.
        const items = String(value || '').split('|').map((item) => item.trim()).filter(Boolean); // Explicação DevIN: Declara variável JavaScript items para reutilizar valor ou resultado.
        return items.length ? items : fallback; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    const getInitials = (value) => value.split(/\s+/).filter(Boolean).slice(0, 2) // Explicação DevIN: Declara variável JavaScript getInitials para reutilizar valor ou resultado.
        .map((word) => word[0]).join('').toUpperCase() || 'DI'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.

    const jobReportSummary = (card) => { // Explicação DevIN: Declara variável JavaScript jobReportSummary para reutilizar valor ou resultado.
        if (!card.hasAttribute('data-job-report-card')) return ''; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        return card.dataset.reportJobDescription || translate('Sem descrição informada.'); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    const renderList = (element, items, timeline = false) => { // Explicação DevIN: Declara variável JavaScript renderList para reutilizar valor ou resultado.
        /* Reconstrói listas a partir dos atributos do card. Usa textContent e nós de texto,
         * não innerHTML, para que nomes e descrições sejam exibidos como texto literal. */
        if (!element) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        element.replaceChildren(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        items.forEach((item) => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            const row = document.createElement('li'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            if (timeline) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                const [title, description] = typeof item === 'string' // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                    ? item.split('::').map((part) => part.trim()) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    : [item.title, item.description]; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                const heading = document.createElement('strong'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                heading.textContent = translate(title || 'DevIN'); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                row.appendChild(heading); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                if (description) row.append(document.createTextNode(translate(description))); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
                row.textContent = item; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            element.appendChild(row); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    const renderDetail = (card) => { // Explicação DevIN: Declara variável JavaScript renderDetail para reutilizar valor ou resultado.
        /* O card concentra resumo, etiquetas e experiência em data-*. Ao selecioná-lo, estes valores
         * preenchem o painel lateral sem uma nova consulta ao servidor. */
        const content = detailPanel?.querySelector('[data-detail-content]'); // Explicação DevIN: Declara variável JavaScript content para reutilizar valor ou resultado.
        if (!detailPanel || !content) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

        const title = card.querySelector('h2')?.textContent?.trim() || translate('Detalhes do registro'); // Explicação DevIN: Declara variável JavaScript title para reutilizar valor ou resultado.
        const subtitle = card.querySelector('p')?.textContent?.trim() || translate('Informacoes da plataforma'); // Explicação DevIN: Declara variável JavaScript subtitle para reutilizar valor ou resultado.
        const name = card.dataset.detailName || card.dataset.jobTitle || title; // Explicação DevIN: Declara variável JavaScript name para reutilizar valor ou resultado.
        const jobReport = card.hasAttribute('data-job-report-card'); // Explicação DevIN: Declara variável JavaScript jobReport para reutilizar valor ou resultado.
        const profileReport = card.hasAttribute('data-profile-report-card'); // Explicação DevIN: Declara variável JavaScript profileReport para reutilizar valor ou resultado.
        const role = jobReport // Explicação DevIN: Declara variável JavaScript role para reutilizar valor ou resultado.
            ? `${translate('Empresa:')} ${card.dataset.reportCompany || ''}` // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            : profileReport // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                ? `${translate('Perfil denunciado')} · ${translate(card.dataset.profileReportRole || '')}` // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            : card.dataset.detailRole || subtitle; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        const summary = jobReportSummary(card) || card.dataset.detailSummary || card.dataset.detail || subtitle; // Explicação DevIN: Declara variável JavaScript summary para reutilizar valor ou resultado.
        const tags = splitItems(card.dataset.detailTags, ['DevIN', translate('Informacao disponivel')]).map(translate); // Explicação DevIN: Declara variável JavaScript tags para reutilizar valor ou resultado.
        const experience = profileReport // Explicação DevIN: Declara variável JavaScript experience para reutilizar valor ou resultado.
            ? [ // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                { title: 'Denúncia #', description: card.dataset.reportId || '' }, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                { title: 'Enviada em', description: card.dataset.reportCreatedAt || '' }, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                    title: 'Denunciante', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                    description: `${card.dataset.profileReportReporter || ''} (${translate(card.dataset.profileReportReporterType || '')} #${card.dataset.profileReportReporterId || ''})` // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                ...(card.dataset.profileReportTargetEmail // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    ? [{ title: 'E-mail do perfil', description: card.dataset.profileReportTargetEmail }] // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    : []), // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                ...(card.dataset.profileReportTargetCnpj // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    ? [{ title: 'CNPJ', description: card.dataset.profileReportTargetCnpj }] // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    : []), // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                ...(card.dataset.profileReportReporterEmail // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    ? [{ title: 'E-mail do denunciante', description: card.dataset.profileReportReporterEmail }] // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    : []), // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                { title: 'Motivo', description: translate(card.dataset.profileReportReason || '') }, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                { title: 'Análise', description: translate(card.dataset.profileReportAnalysis || '') } // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            ] // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            : jobReport // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                ? [ // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    { title: 'Denúncia #', description: card.dataset.reportId || '' }, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                    { title: 'Vaga #', description: card.dataset.reportTargetId || '' }, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                    { title: 'Enviada em', description: card.dataset.reportCreatedAt || '' }, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                        title: 'Denunciante', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                        description: `${card.dataset.reportReporter || ''} (${translate(card.dataset.reportReporterType || '')} #${card.dataset.reportReporterId || ''})` // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                    }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                    ...(card.dataset.reportReporterEmail // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                        ? [{ title: 'E-mail do denunciante', description: card.dataset.reportReporterEmail }] // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                        : []), // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    ...(card.dataset.reportCompanyEmail // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                        ? [{ title: 'E-mail da empresa', description: card.dataset.reportCompanyEmail }] // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                        : []), // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    { // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                        title: 'Mensagem da denúncia', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                        description: `${translate('Motivo:')} ${translate(card.dataset.reportReason || '')}\n${card.dataset.reportMessage || ''}` // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                    }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                    { title: 'Análise', description: translate(card.dataset.reportAnalysis || '') } // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                ] // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                : splitItems(card.dataset.detailExperience, [`${translate('Registro selecionado')}::${role}`]); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.

        detailPanel.querySelector('[data-detail-placeholder]')?.setAttribute('hidden', ''); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        content.hidden = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        const avatar = detailPanel.querySelector('[data-detail-avatar]'); // Explicação DevIN: Declara variável JavaScript avatar para reutilizar valor ou resultado.
        const nameElement = detailPanel.querySelector('[data-detail-name]'); // Explicação DevIN: Declara variável JavaScript nameElement para reutilizar valor ou resultado.
        const roleElement = detailPanel.querySelector('[data-detail-role]'); // Explicação DevIN: Declara variável JavaScript roleElement para reutilizar valor ou resultado.
        const metaElement = detailPanel.querySelector('[data-detail-meta]'); // Explicação DevIN: Declara variável JavaScript metaElement para reutilizar valor ou resultado.
        const summaryElement = detailPanel.querySelector('[data-detail-summary]'); // Explicação DevIN: Declara variável JavaScript summaryElement para reutilizar valor ou resultado.
        const experienceList = detailPanel.querySelector('[data-detail-experience]'); // Explicação DevIN: Declara variável JavaScript experienceList para reutilizar valor ou resultado.
        const reportJobFacts = detailPanel.querySelector('[data-report-job-facts]'); // Explicação DevIN: Declara variável JavaScript reportJobFacts para reutilizar valor ou resultado.
        const decisionBlock = detailPanel.querySelector('[data-report-decision-block]'); // Explicação DevIN: Declara variável JavaScript decisionBlock para reutilizar valor ou resultado.
        const applicationSummary = card.dataset.applicationSummary === 'true'; // Explicação DevIN: Declara variável JavaScript applicationSummary para reutilizar valor ou resultado.
        const experienceHeading = detailPanel.querySelector('[data-detail-experience-heading]'); // Explicação DevIN: Declara variável JavaScript experienceHeading para reutilizar valor ou resultado.

        if (avatar) avatar.textContent = getInitials(name); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if (nameElement) nameElement.textContent = name; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if (roleElement) roleElement.textContent = role; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if (metaElement) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            metaElement.textContent = jobReport // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                ? `${translate('Denúncia #')}${card.dataset.reportId || ''} · ${translate('Enviada em').toLocaleLowerCase(interfaceLocale)} ${card.dataset.reportCreatedAt || ''}` // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                : profileReport // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    ? `${translate('Denúncia #')}${card.dataset.reportId || ''} · ${translate('Enviada em').toLocaleLowerCase(interfaceLocale)} ${card.dataset.reportCreatedAt || ''}` // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                : card.dataset.detailMeta || translate('Disponivel na plataforma'); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (summaryElement) summaryElement.textContent = summary; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if (reportJobFacts) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            reportJobFacts.hidden = !jobReport; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            reportJobFacts.querySelector('[data-report-job-salary]').textContent = // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                card.dataset.reportJobSalary || translate('A combinar'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            reportJobFacts.querySelector('[data-report-job-limit]').textContent = // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                card.dataset.reportJobLimit || translate('Sem limite'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (decisionBlock) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            const shouldShowDecision = card.dataset.reportDecision === 'true'; // Explicação DevIN: Declara variável JavaScript shouldShowDecision para reutilizar valor ou resultado.
            decisionBlock.hidden = !shouldShowDecision; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        const summaryLabel = detailPanel.querySelector('[data-detail-summary-label]'); // Explicação DevIN: Declara variável JavaScript summaryLabel para reutilizar valor ou resultado.
        if (summaryLabel) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            summaryLabel.textContent = translate(jobReport // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                ? 'Detalhes da vaga denunciada' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                : profileReport ? 'Relato da denúncia' : 'Sobre'); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (experienceHeading) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            experienceHeading.textContent = translate(jobReport // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                ? 'Detalhes da denúncia' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                : profileReport ? 'Dados da denúncia' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    : applicationSummary ? 'Informações da vaga' : 'Experiência'); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        detailPanel.querySelector('[data-detail-tags-section]')?.toggleAttribute('hidden', jobReport); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        renderList(detailPanel.querySelector('[data-detail-tags]'), tags); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        renderList(experienceList, experience, true); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        detailPanel.classList.toggle('has-application-summary', applicationSummary); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        experienceList?.classList.toggle('application-summary', applicationSummary); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        experienceList?.closest('.detail-section')?.classList.toggle('application-summary-section', applicationSummary); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

        selectedActionTarget = card.querySelector('[data-detail-action-target]'); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        const action = detailPanel.querySelector('[data-detail-action]'); // Explicação DevIN: Declara variável JavaScript action para reutilizar valor ou resultado.
        if (action) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            action.hidden = !selectedActionTarget; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            if (selectedActionTarget) action.textContent = translate(card.dataset.detailActionLabel || selectedActionTarget.textContent.trim()); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        const chatAction = detailPanel.querySelector('[data-start-chat]'); // Explicação DevIN: Declara variável JavaScript chatAction para reutilizar valor ou resultado.
        if (chatAction) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            chatAction.hidden = !card.dataset.chatType || !card.dataset.chatId; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            chatAction.dataset.chatType = card.dataset.chatType || ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            chatAction.dataset.chatId = card.dataset.chatId || ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (jobReportButton) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            const canReportJob = Boolean(card.dataset.reportJobId); // Explicação DevIN: Declara variável JavaScript canReportJob para reutilizar valor ou resultado.
            detailPanel.querySelector('[data-report-job-section]')?.toggleAttribute('hidden', !canReportJob); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            jobReportButton.disabled = card.dataset.reportSubmitted === 'true'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            jobReportButton.dataset.jobId = card.dataset.reportJobId || ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            jobReportButton.textContent = translate(card.dataset.reportSubmitted === 'true' // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                ? 'Denúncia enviada' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                : 'Denunciar esta vaga'); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    detailPanel?.querySelectorAll('[data-report-decision]').forEach((button) => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        button.addEventListener('click', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
            const selectedCard = document.querySelector('.item-card.selecionado'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            if (!selectedCard) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

            const verdict = button.dataset.reportDecision; // Explicação DevIN: Declara variável JavaScript verdict para reutilizar valor ou resultado.
            const form = selectedCard.querySelector(verdict === 'manter' ? '[data-report-keep-form]' : '[data-report-delete-form]'); // Explicação DevIN: Declara variável JavaScript form para reutilizar valor ou resultado.
            if (!form) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            form.requestSubmit?.(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            if (!form.requestSubmit) form.submit(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    detailPanel?.querySelector('[data-detail-action]')?.addEventListener('click', () => selectedActionTarget?.click()); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    document.addEventListener('click', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        if (!(event.target instanceof Element)) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        const button = event.target.closest('[data-start-chat]:not([hidden])'); // Explicação DevIN: Declara variável JavaScript button para reutilizar valor ou resultado.
        if (!button || !button.dataset.chatType || !button.dataset.chatId) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        const destination = new URL(window.location.href); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        destination.searchParams.set('pagina', 'mensagens'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        destination.searchParams.set('contato_tipo', button.dataset.chatType); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        destination.searchParams.set('contato_id', button.dataset.chatId); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        window.location.assign(destination); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    const clearDetail = () => { // Explicação DevIN: Declara variável JavaScript clearDetail para reutilizar valor ou resultado.
        detailCards.forEach((card) => card.classList.remove('selecionado')); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        selectedActionTarget = null; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.

        if (!detailPanel) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

        detailPanel.querySelector('[data-detail-content]')?.setAttribute('hidden', ''); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        detailPanel.querySelector('[data-detail-placeholder]')?.removeAttribute('hidden'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        detailPanel.querySelector('[data-detail-action]')?.setAttribute('hidden', ''); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        detailPanel.querySelector('[data-start-chat]')?.setAttribute('hidden', ''); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        detailPanel.querySelector('[data-report-decision-block]')?.setAttribute('hidden', ''); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    const mobileDetailMedia = window.matchMedia('(max-width: 720px)'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const closeMobileDetail = () => { // Explicação DevIN: Declara variável JavaScript closeMobileDetail para reutilizar valor ou resultado.
        if (!detailPanel) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        const selectedCard = detailCards.find((card) => card.classList.contains('selecionado')); // Explicação DevIN: Declara variável JavaScript selectedCard para reutilizar valor ou resultado.
        detailPanel.classList.remove('mobile-detail-open'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        detailPanel.removeAttribute('role'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        detailPanel.removeAttribute('aria-modal'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        clearDetail(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        if (selectedCard) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            selectedCard.setAttribute('tabindex', '-1'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            selectedCard.focus({ preventScroll: true }); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    detailPanel?.querySelector('[data-close-detail]')?.addEventListener('click', closeMobileDetail); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    detailPanel?.addEventListener('click', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        if (event.target === detailPanel) closeMobileDetail(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    document.addEventListener('keydown', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        if (!detailPanel?.classList.contains('mobile-detail-open')) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if (event.key === 'Escape') { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            event.preventDefault(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            closeMobileDetail(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (event.key !== 'Tab') return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

        const focusableElements = [...detailPanel.querySelectorAll('button:not([hidden]):not(:disabled), a[href]')] // Explicação DevIN: Declara variável JavaScript focusableElements para reutilizar valor ou resultado.
            .filter((element) => element.getClientRects().length > 0); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        if (!focusableElements.length) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        const firstElement = focusableElements[0]; // Explicação DevIN: Declara variável JavaScript firstElement para reutilizar valor ou resultado.
        const lastElement = focusableElements[focusableElements.length - 1]; // Explicação DevIN: Declara variável JavaScript lastElement para reutilizar valor ou resultado.
        if (event.shiftKey && document.activeElement === firstElement) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            event.preventDefault(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            lastElement.focus(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } else if (!event.shiftKey && document.activeElement === lastElement) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            event.preventDefault(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            firstElement.focus(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    mobileDetailMedia.addEventListener?.('change', (event) => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        if (!event.matches && detailPanel?.classList.contains('mobile-detail-open')) closeMobileDetail(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    document.querySelectorAll('[data-open-report-analysis]').forEach((button) => { // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        button.addEventListener('click', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
            event.preventDefault(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            event.stopPropagation(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            const card = button.closest('.item-card[data-detail]'); // Explicação DevIN: Declara variável JavaScript card para reutilizar valor ou resultado.
            if (!card) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

            document.querySelectorAll('.item-card[data-detail]').forEach((item) => { // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                item.classList.remove('selecionado'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            card.classList.add('selecionado'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            renderDetail(card); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

            if (detailPanel && mobileDetailMedia.matches) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                detailPanel.classList.add('mobile-detail-open'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                detailPanel.setAttribute('role', 'dialog'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                detailPanel.setAttribute('aria-modal', 'true'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                detailPanel.querySelector('[data-close-detail]')?.focus({ preventScroll: true }); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    document.querySelectorAll('.item-card[data-detail]').forEach((card) => { // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        card.addEventListener('click', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
            // Cliques em controles internos executam sua ação própria e não trocam a seleção do card.
            if (event.target.closest('button, a, form, summary, input, textarea, select')) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

            document.querySelectorAll('.item-card[data-detail]').forEach((item) => { // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                item.classList.remove('selecionado'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            card.classList.add('selecionado'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            renderDetail(card); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

            if (detailPanel && mobileDetailMedia.matches) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                detailPanel.classList.add('mobile-detail-open'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                detailPanel.setAttribute('role', 'dialog'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                detailPanel.setAttribute('aria-modal', 'true'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                detailPanel.querySelector('[data-close-detail]')?.focus({ preventScroll: true }); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            if (detailText) detailText.textContent = card.dataset.detail || 'Sem detalhes disponíveis.'; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            if (detailTitle && card.dataset.jobTitle) detailTitle.textContent = card.dataset.jobTitle; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Confirmação de saída para pessoa, empresa e administrador.
    document.querySelector('.lista-area')?.addEventListener('click', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        if (event.target.closest('.item-card[data-detail], button, a, form, summary, input, textarea, select, label')) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        clearDetail(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    document.querySelectorAll('[data-confirm-logout]').forEach((link) => { // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        link.addEventListener('click', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
            const message = link.dataset.confirmLogout || translate('Tem certeza que deseja sair da sua conta?'); // Explicação DevIN: Declara variável JavaScript message para reutilizar valor ou resultado.
            if (!window.confirm(message)) event.preventDefault(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    const deleteForms = new Set([...document.querySelectorAll('[data-delete-account]')] // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        .map((button) => button.closest('form')) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        .filter(Boolean)); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    deleteForms.forEach((form) => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        form.addEventListener('submit', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
            if (form.dataset.deleteConfirmed === 'true') return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            if (!window.confirm(translate('Tem certeza? Esta ação exclui sua conta permanentemente.'))) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                event.preventDefault(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            event.preventDefault(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            form.closest('dialog')?.close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            document.body.classList.add('account-deleting'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            const delay = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 150 : 380; // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            window.setTimeout(() => HTMLFormElement.prototype.submit.call(form), delay); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Fecha dialogs sem depender de IDs específicos.
    document.querySelectorAll('[data-close-modal]').forEach((button) => { // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        button.addEventListener('click', () => button.closest('dialog')?.close()); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    document.querySelector('[data-close-job-report]')?.addEventListener('click', () => jobReportDialog?.close()); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    jobReportDialog?.addEventListener('click', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        if (event.target === jobReportDialog) jobReportDialog.close(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    jobReportButton?.addEventListener('click', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        const reportId = jobReportButton.dataset.jobId; // Explicação DevIN: Declara variável JavaScript reportId para reutilizar valor ou resultado.
        if (!reportId || !jobReportForm || !jobReportDialog) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        jobReportForm.reset(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        jobReportForm.querySelectorAll('select, textarea').forEach((field) => { field.disabled = false; }); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        jobReportForm.querySelector('[data-job-report-id]').value = reportId; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        jobReportStatus.closest('[data-feedback-toast]').hidden = true; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        jobReportStatus.classList.remove('form-success'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        jobReportStatus.textContent = ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        jobReportSubmit.disabled = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        jobReportSubmit.textContent = translate('Enviar denúncia'); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        jobReportDialog.showModal(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    jobReportForm?.addEventListener('submit', async (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        event.preventDefault(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        if (!jobReportSubmit || !jobReportStatus) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

        jobReportSubmit.disabled = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        jobReportSubmit.textContent = translate('Enviando...'); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        jobReportStatus.closest('[data-feedback-toast]').hidden = true; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        jobReportStatus.classList.remove('form-success'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            const csrfToken = document.querySelector('input[name="csrf_token"]')?.value || ''; // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            const response = await fetch(new URL('../php/denunciar_vaga_api.php', window.location.href), { // Explicação DevIN: Inicia solicitação HTTP assíncrona a um endereço configurado.
                method: 'POST', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                credentials: 'same-origin', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                headers: { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                    'Content-Type': 'application/json', // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                    'X-CSRF-Token': csrfToken, // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
                }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                body: JSON.stringify({ // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
                    id_vaga: jobReportForm.querySelector('[data-job-report-id]').value, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                    motivo: jobReportForm.querySelector('[name="motivo"]').value, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                    detalhes: jobReportForm.querySelector('[name="detalhes"]').value.trim(), // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                }), // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            const data = await response.json(); // Explicação DevIN: Declara variável JavaScript data para reutilizar valor ou resultado.
            if (!response.ok || data.sucesso !== true) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                throw new Error(translate(data.mensagem || 'Não foi possível enviar a denúncia.')); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            const warnings = Array.isArray(data.avisos) ? data.avisos.map(translate) : []; // Explicação DevIN: Declara variável JavaScript warnings para reutilizar valor ou resultado.
            showToast( // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                jobReportStatus.closest('[data-feedback-toast]'), // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                [translate(data.mensagem), ...warnings].join(' '), // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                warnings.length > 0 ? 'warning' : 'success' // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            jobReportSubmit.textContent = translate('Denúncia enviada'); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            jobReportForm.querySelectorAll('select, textarea').forEach((field) => { field.disabled = true; }); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            const reportedCard = detailCards.find((card) => card.dataset.reportJobId === jobReportButton.dataset.jobId); // Explicação DevIN: Declara variável JavaScript reportedCard para reutilizar valor ou resultado.
            if (reportedCard) reportedCard.dataset.reportSubmitted = 'true'; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            jobReportButton.disabled = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            jobReportButton.textContent = translate('Denúncia enviada'); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } catch (error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            showToast(jobReportStatus.closest('[data-feedback-toast]'), error.message, 'error'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            jobReportSubmit.disabled = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            jobReportSubmit.textContent = translate('Enviar denúncia'); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    profileModal?.addEventListener('click', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        if (event.target === profileModal) profileModal.close(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    settingsModal?.addEventListener('click', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        if (event.target === settingsModal) settingsModal.close(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    document.querySelectorAll('[data-open-settings]').forEach((button) => { // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        button.addEventListener('click', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
            document.querySelector('.perfil-dropdown')?.removeAttribute('open'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            if (settingsModal?.showModal) settingsModal.showModal(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    document.querySelectorAll('[data-open-profile]').forEach((button) => { // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        button.addEventListener('click', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
            document.querySelector('.perfil-dropdown')?.removeAttribute('open'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            if (profileModal?.showModal) profileModal.showModal(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    document.querySelectorAll('.perfil-dropdown').forEach((dropdown) => { // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        document.addEventListener('click', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
            if (event.target instanceof Node && !dropdown.contains(event.target)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                dropdown.removeAttribute('open'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Pré-visualização segura da foto.
    document.querySelectorAll(".profile-photo input[type='file']").forEach((input) => { // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        input.addEventListener('change', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
            const photo = event.currentTarget.files?.[0]; // Explicação DevIN: Declara variável JavaScript photo para reutilizar valor ou resultado.
            if (!photo) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

            const allowed = ['image/jpeg', 'image/png', 'image/webp']; // Explicação DevIN: Declara variável JavaScript allowed para reutilizar valor ou resultado.
            if (!allowed.includes(photo.type) || photo.size > 5 * 1024 * 1024) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                event.currentTarget.value = ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                window.alert(translate('Selecione uma imagem JPG, PNG ou WEBP de até 5 MB.')); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            const previewUrl = URL.createObjectURL(photo); // Explicação DevIN: Declara variável JavaScript previewUrl para reutilizar valor ou resultado.
            document.querySelectorAll('.current-user-avatar').forEach((preview) => { // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                preview.replaceChildren(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                const image = document.createElement('img'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                image.src = previewUrl; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                image.alt = translate('Prévia da foto de perfil'); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                preview.appendChild(image); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Loading para qualquer formulário que realmente será enviado.
    document.querySelectorAll('form').forEach((form) => { // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        form.addEventListener('submit', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
            if (form.matches('[data-chat-form]') || form.closest('.jarvis-admin')) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            const submitter = form.querySelector('button[type="submit"]:focus, button[type="submit"]:last-of-type'); // Explicação DevIN: Declara variável JavaScript submitter para reutilizar valor ou resultado.
            if (!submitter || submitter.dataset.loading === 'true') return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            submitter.dataset.loading = 'true'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            submitter.disabled = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            submitter.dataset.originalText = submitter.textContent; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            submitter.textContent = translate('Processando...'); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Busca local sem enviar uma nova requisição a cada tecla.
    const searchInput = document.querySelector('.busca input[type="search"]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const filterButton = document.querySelector('[data-toggle-filters]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const filterMenu = document.querySelector('[data-filter-menu]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const resetFiltersButton = document.querySelector('[data-reset-filters]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const resultLive = document.querySelector('[data-visible-results]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const filterSelect = document.querySelector('[data-card-filter]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const candidateListSearch = searchInput?.hasAttribute('data-candidate-list-search') === true; // Explicação DevIN: Declara variável JavaScript candidateListSearch para reutilizar valor ou resultado.
    const globalEducationFilter = document.querySelector('[data-candidate-global-education]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const globalCurriculumFilter = document.querySelector('[data-candidate-global-curriculum]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const candidatePanels = [...document.querySelectorAll('.candidate-vacancy-panel')]; // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const searchEmptyState = detailCards.length // Explicação DevIN: Declara variável JavaScript searchEmptyState para reutilizar valor ou resultado.
        ? document.createElement('p') // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        : null; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    if (searchEmptyState) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        searchEmptyState.className = 'empty-state search-empty-state'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        searchEmptyState.setAttribute('role', 'status'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        searchEmptyState.setAttribute('aria-live', 'polite'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        searchEmptyState.textContent = translate('Nenhum resultado encontrado.'); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        searchEmptyState.hidden = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        (candidatePanels[0] || detailCards[0]).before(searchEmptyState); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    const normalizeSearch = (value) => String(value || '').trim().toLocaleLowerCase(interfaceLocale) // Explicação DevIN: Declara variável JavaScript normalizeSearch para reutilizar valor ou resultado.
        .normalize('NFD').replace(/[\u0300-\u036f]/g, ''); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    const splitCandidateFilterItems = (value) => String(value || '') // Explicação DevIN: Declara variável JavaScript splitCandidateFilterItems para reutilizar valor ou resultado.
        .split(/[\r\n,;|]+/).map((item) => item.trim()).filter(Boolean); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    const cardTags = (card) => String(card.dataset.detailTags || '') // Explicação DevIN: Declara variável JavaScript cardTags para reutilizar valor ou resultado.
        .split('|').map((tag) => tag.trim()).filter(Boolean); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
    const updateCardVisibility = () => { // Explicação DevIN: Declara variável JavaScript updateCardVisibility para reutilizar valor ou resultado.
        /* Compara o termo com texto visível e metadados do card, além da categoria selecionada.
         * A busca é local: não faz uma requisição a cada tecla nem altera dados armazenados. */
        const term = normalizeSearch(searchInput?.value); // Explicação DevIN: Declara variável JavaScript term para reutilizar valor ou resultado.
        const activeFilter = filterSelect?.value || ''; // Explicação DevIN: Declara variável JavaScript activeFilter para reutilizar valor ou resultado.
        const globalCurriculumTerm = normalizeSearch(globalCurriculumFilter?.value); // Explicação DevIN: Declara variável JavaScript globalCurriculumTerm para reutilizar valor ou resultado.
        const globalEducation = normalizeSearch(globalEducationFilter?.value); // Explicação DevIN: Declara variável JavaScript globalEducation para reutilizar valor ou resultado.
        const hasGlobalCandidateFilters = candidateListSearch // Explicação DevIN: Declara variável JavaScript hasGlobalCandidateFilters para reutilizar valor ou resultado.
            && (term !== '' || activeFilter !== '' || globalCurriculumTerm !== '' || globalEducation !== ''); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.

        detailCards.forEach((card) => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            const searchableContent = [ // Explicação DevIN: Declara variável JavaScript searchableContent para reutilizar valor ou resultado.
                card.textContent, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                card.dataset.detailName, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                card.dataset.detailSummary, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                card.dataset.detailRole, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                card.dataset.detailTags, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                card.dataset.detailExperience, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                card.dataset.candidateNameEmail, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                card.dataset.candidateCurriculum, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            ].join(' '); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            const candidateNameEmail = normalizeSearch(card.dataset.candidateNameEmail); // Explicação DevIN: Declara variável JavaScript candidateNameEmail para reutilizar valor ou resultado.
            const normalizedSearchableContent = normalizeSearch(searchableContent); // Explicação DevIN: Declara variável JavaScript normalizedSearchableContent para reutilizar valor ou resultado.
            const matchesSearch = term === '' || (candidateListSearch // Explicação DevIN: Declara variável JavaScript matchesSearch para reutilizar valor ou resultado.
                ? candidateNameEmail.includes(term) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                : normalizedSearchableContent.includes(term)); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            const matchesFilter = activeFilter === '' || cardTags(card).includes(activeFilter); // Explicação DevIN: Declara variável JavaScript matchesFilter para reutilizar valor ou resultado.
            const matchesGlobalCurriculum = globalCurriculumTerm === '' // Explicação DevIN: Declara variável JavaScript matchesGlobalCurriculum para reutilizar valor ou resultado.
                || normalizeSearch(card.dataset.candidateCurriculum).includes(globalCurriculumTerm); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            const matchesGlobalEducation = globalEducation === '' // Explicação DevIN: Declara variável JavaScript matchesGlobalEducation para reutilizar valor ou resultado.
                || normalizeSearch(card.dataset.candidateEducation) === globalEducation; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            const panel = card.closest('.candidate-vacancy-panel'); // Explicação DevIN: Declara variável JavaScript panel para reutilizar valor ou resultado.
            const candidateTerm = normalizeSearch(panel?.querySelector('[data-candidate-search]')?.value); // Explicação DevIN: Declara variável JavaScript candidateTerm para reutilizar valor ou resultado.
            const education = normalizeSearch(panel?.querySelector('[data-candidate-education]')?.value); // Explicação DevIN: Declara variável JavaScript education para reutilizar valor ou resultado.
            const matchesCandidateSearch = candidateTerm === '' // Explicação DevIN: Declara variável JavaScript matchesCandidateSearch para reutilizar valor ou resultado.
                || candidateNameEmail.includes(candidateTerm); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            const matchesEducation = education === '' // Explicação DevIN: Declara variável JavaScript matchesEducation para reutilizar valor ou resultado.
                || normalizeSearch(card.dataset.candidateEducation) === education; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            const course = normalizeSearch(panel?.querySelector('[data-candidate-course]')?.value); // Explicação DevIN: Declara variável JavaScript course para reutilizar valor ou resultado.
            const skill = normalizeSearch(panel?.querySelector('[data-candidate-skill]')?.value); // Explicação DevIN: Declara variável JavaScript skill para reutilizar valor ou resultado.
            const experience = normalizeSearch(panel?.querySelector('[data-candidate-experience]')?.value); // Explicação DevIN: Declara variável JavaScript experience para reutilizar valor ou resultado.
            const matchesCourse = course === '' // Explicação DevIN: Declara variável JavaScript matchesCourse para reutilizar valor ou resultado.
                || splitCandidateFilterItems(card.dataset.candidateCourses).some((item) => normalizeSearch(item) === course); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            const matchesSkill = skill === '' // Explicação DevIN: Declara variável JavaScript matchesSkill para reutilizar valor ou resultado.
                || splitCandidateFilterItems(card.dataset.candidateSkills).some((item) => normalizeSearch(item) === skill); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            const matchesExperience = experience === '' // Explicação DevIN: Declara variável JavaScript matchesExperience para reutilizar valor ou resultado.
                || splitCandidateFilterItems(card.dataset.candidateExperiences).some((item) => normalizeSearch(item) === experience); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            card.hidden = !matchesSearch || !matchesFilter // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                || !matchesGlobalCurriculum || !matchesGlobalEducation // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                || !matchesCandidateSearch || !matchesEducation // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                || !matchesCourse || !matchesSkill || !matchesExperience; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        const hasActiveSearch = hasGlobalCandidateFilters || (!candidateListSearch && (term !== '' || activeFilter !== '')); // Explicação DevIN: Declara variável JavaScript hasActiveSearch para reutilizar valor ou resultado.
        const hasVisibleCards = detailCards.some((card) => !card.hidden); // Explicação DevIN: Declara variável JavaScript hasVisibleCards para reutilizar valor ou resultado.
        const visibleCardCount = detailCards.filter((card) => !card.hidden).length; // Explicação DevIN: Declara variável JavaScript visibleCardCount para reutilizar valor ou resultado.
        const localCandidateFilterActive = candidatePanels.some((panel) => [...panel.querySelectorAll('[data-candidate-search], [data-candidate-education], [data-candidate-course], [data-candidate-skill], [data-candidate-experience]')].some((field) => String(field.value || '').trim() !== '')); // Explicação DevIN: Declara variável JavaScript localCandidateFilterActive para reutilizar valor ou resultado.
        const anyFilterActive = term !== '' || activeFilter !== '' || globalCurriculumTerm !== '' || globalEducation !== '' || localCandidateFilterActive; // Explicação DevIN: Declara variável JavaScript anyFilterActive para reutilizar valor ou resultado.
        if (resetFiltersButton) resetFiltersButton.disabled = !anyFilterActive; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if (resultLive) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            const lang = document.documentElement.lang; // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            const word = lang === 'en' ? (visibleCardCount === 1 ? 'result' : 'results') : (visibleCardCount === 1 ? 'resultado' : 'resultados'); // Explicação DevIN: Declara variável JavaScript word para reutilizar valor ou resultado.
            resultLive.textContent = anyFilterActive ? visibleCardCount + ' ' + word : ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (searchEmptyState) searchEmptyState.hidden = !hasActiveSearch || hasVisibleCards; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        candidatePanels.forEach((panel) => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            const candidateCards = [...panel.querySelectorAll('.item-card[data-candidate-name-email]')]; // Explicação DevIN: Declara variável JavaScript candidateCards para reutilizar valor ou resultado.
            const localSearch = normalizeSearch(panel.querySelector('[data-candidate-search]')?.value); // Explicação DevIN: Declara variável JavaScript localSearch para reutilizar valor ou resultado.
            const education = normalizeSearch(panel.querySelector('[data-candidate-education]')?.value); // Explicação DevIN: Declara variável JavaScript education para reutilizar valor ou resultado.
            const course = normalizeSearch(panel.querySelector('[data-candidate-course]')?.value); // Explicação DevIN: Declara variável JavaScript course para reutilizar valor ou resultado.
            const skill = normalizeSearch(panel.querySelector('[data-candidate-skill]')?.value); // Explicação DevIN: Declara variável JavaScript skill para reutilizar valor ou resultado.
            const experience = normalizeSearch(panel.querySelector('[data-candidate-experience]')?.value); // Explicação DevIN: Declara variável JavaScript experience para reutilizar valor ou resultado.
            const hasLocalFilter = localSearch !== '' || education !== '' // Explicação DevIN: Declara variável JavaScript hasLocalFilter para reutilizar valor ou resultado.
                || course !== '' || skill !== '' || experience !== ''; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            const hasVisibleCandidates = candidateCards.some((card) => !card.hidden); // Explicação DevIN: Declara variável JavaScript hasVisibleCandidates para reutilizar valor ou resultado.
            const emptyState = panel.querySelector('[data-candidate-filter-empty]'); // Explicação DevIN: Declara variável JavaScript emptyState para reutilizar valor ou resultado.
            if (emptyState) emptyState.hidden = !hasLocalFilter || hasVisibleCandidates; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

            if (hasGlobalCandidateFilters) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                if (!Object.prototype.hasOwnProperty.call(panel.dataset, 'filterPreviousOpen')) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    panel.dataset.filterPreviousOpen = String(panel.open); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                panel.open = hasVisibleCandidates; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            } else if (Object.prototype.hasOwnProperty.call(panel.dataset, 'filterPreviousOpen')) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                panel.open = panel.dataset.filterPreviousOpen === 'true'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                delete panel.dataset.filterPreviousOpen; // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    candidatePanels.forEach((panel) => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        panel.querySelectorAll('input[data-candidate-search]') // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            .forEach((input) => input.addEventListener('input', updateCardVisibility)); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        const candidateCards = [...panel.querySelectorAll('.item-card[data-candidate-name-email]')]; // Explicação DevIN: Declara variável JavaScript candidateCards para reutilizar valor ou resultado.
        const filterDefinitions = [ // Explicação DevIN: Declara variável JavaScript filterDefinitions para reutilizar valor ou resultado.
            { selector: '[data-candidate-course]', attribute: 'candidateCourses', emptyLabel: 'Todos os cursos' }, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            { selector: '[data-candidate-skill]', attribute: 'candidateSkills', emptyLabel: 'Todas as habilidades' }, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            { selector: '[data-candidate-experience]', attribute: 'candidateExperiences', emptyLabel: 'Todas as experiências' }, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        ]; // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.

        filterDefinitions.forEach(({ selector, attribute, emptyLabel }) => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            const select = panel.querySelector(selector); // Explicação DevIN: Declara variável JavaScript select para reutilizar valor ou resultado.
            if (!select) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

            const items = [...new Set(candidateCards.flatMap((card) => splitCandidateFilterItems(card.dataset[attribute])))] // Explicação DevIN: Declara variável JavaScript items para reutilizar valor ou resultado.
                .sort((first, second) => first.localeCompare(second, interfaceLocale)); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            const defaultOption = document.createElement('option'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            defaultOption.value = ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            defaultOption.textContent = emptyLabel; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            select.replaceChildren(defaultOption, ...items.map((item) => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                const option = document.createElement('option'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                option.value = item; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                option.textContent = item; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                return option; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            })); // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
            select.addEventListener('change', updateCardVisibility); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        panel.querySelector('[data-candidate-education]')?.addEventListener('change', updateCardVisibility); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    if (filterSelect) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        // Monta as opções de filtro com as tags existentes nos cartões renderizados.
        const tags = [...new Set(detailCards.flatMap(cardTags))] // Explicação DevIN: Declara variável JavaScript tags para reutilizar valor ou resultado.
            .filter((tag) => !candidateListSearch || tag !== 'Candidatura') // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            .sort((first, second) => first.localeCompare(second, 'pt-BR')); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        const options = ['', ...tags].map((tag) => { // Explicação DevIN: Declara variável JavaScript options para reutilizar valor ou resultado.
            const option = document.createElement('option'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            option.value = tag; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            option.textContent = translate(tag || 'Todos'); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            return option; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        filterSelect.replaceChildren(...options); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        filterSelect.addEventListener('change', updateCardVisibility); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    if (globalEducationFilter) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        const educationLevels = [...new Set(detailCards // Explicação DevIN: Declara variável JavaScript educationLevels para reutilizar valor ou resultado.
            .map((card) => card.dataset.candidateEducation?.trim()) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            .filter(Boolean))] // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            .sort((first, second) => first.localeCompare(second, 'pt-BR')); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        const options = educationLevels.map((education) => { // Explicação DevIN: Declara variável JavaScript options para reutilizar valor ou resultado.
            const option = document.createElement('option'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            option.value = education; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            option.textContent = education; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            return option; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        globalEducationFilter.append(...options); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        globalEducationFilter.addEventListener('change', updateCardVisibility); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    globalCurriculumFilter?.addEventListener('input', updateCardVisibility); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    const setFilterMenuOpen = (open) => { // Explicação DevIN: Declara variável JavaScript setFilterMenuOpen para reutilizar valor ou resultado.
        if (!filterMenu || !filterButton) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        filterMenu.hidden = !open; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        filterButton.setAttribute('aria-expanded', String(open)); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    filterButton?.addEventListener('click', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        setFilterMenuOpen(Boolean(filterMenu?.hidden)); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    document.addEventListener('keydown', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        if (event.key !== 'Escape' || !filterMenu || filterMenu.hidden) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        setFilterMenuOpen(false); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        filterButton?.focus(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    document.addEventListener('pointerdown', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        if (!filterMenu || filterMenu.hidden) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if (event.target instanceof Node && !filterMenu.contains(event.target) && !filterButton?.contains(event.target)) setFilterMenuOpen(false); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    resetFiltersButton?.addEventListener('click', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        for (const field of [searchInput, filterSelect, globalEducationFilter, globalCurriculumFilter]) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            if (field) field.value = ''; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        for (const panel of candidatePanels) { // Explicação DevIN: Inicia repetição ou seleção de casos a partir de expressão de controle.
            panel.querySelectorAll('[data-candidate-search], [data-candidate-education], [data-candidate-course], [data-candidate-skill], [data-candidate-experience]').forEach((field) => { field.value = ''; }); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        updateCardVisibility(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        setFilterMenuOpen(false); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        searchInput?.focus(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    searchInput?.addEventListener('input', updateCardVisibility); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    updateCardVisibility(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

    document.querySelector('.busca')?.addEventListener('submit', (event) => event.preventDefault()); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.

    // Palavra animada da página institucional.
    if (rotatingWord) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        const words = document.documentElement.lang === 'en' // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            ? ['future', 'world', 'universe', 'progress', 'market', 'tomorrow'] // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            : document.documentElement.lang === 'es' // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                ? ['futuro', 'mundo', 'universo', 'progreso', 'mercado', 'mañana'] // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                : ['futuro', 'mundo', 'universo', 'avanço', 'mercado', 'amanhã']; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        let currentWord = 0; // Explicação DevIN: Declara variável JavaScript currentWord para reutilizar valor ou resultado.
        let changing = false; // Explicação DevIN: Declara variável JavaScript changing para reutilizar valor ou resultado.

        const showWord = (word, entering = false) => { // Explicação DevIN: Declara variável JavaScript showWord para reutilizar valor ou resultado.
            rotatingWord.replaceChildren(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            [...word].forEach((character, index) => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                const letter = document.createElement('span'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                const distance = index - (word.length - 1) / 2; // Explicação DevIN: Declara variável JavaScript distance para reutilizar valor ou resultado.
                letter.className = 'palavra-letra'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                letter.textContent = character; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                letter.style.setProperty('--rotacao', `${distance * 12}deg`); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                letter.style.setProperty('--queda', `${28 + (index % 3) * 7}px`); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                letter.style.setProperty('--atraso', `${index * 38}ms`); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                if (entering) letter.classList.add('entrando'); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                rotatingWord.appendChild(letter); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            if (entering) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                requestAnimationFrame(() => requestAnimationFrame(() => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                    rotatingWord.querySelectorAll('.palavra-letra').forEach((letter) => letter.classList.remove('entrando')); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                })); // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        showWord(words[currentWord]); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

        window.setInterval(() => { // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            if (changing) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            changing = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            rotatingWord.querySelectorAll('.palavra-letra').forEach((letter) => letter.classList.add('saindo')); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            window.setTimeout(() => { // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                currentWord = (currentWord + 1) % words.length; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                showWord(words[currentWord], true); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                changing = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            }, 800); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        }, 3000); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    const url = new URL(window.location.href); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    if (url.searchParams.get('configuracoes') === '1' && settingsModal?.showModal) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        settingsModal.showModal(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        url.searchParams.delete('configuracoes'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        window.history.replaceState(null, '', `${url.pathname}${url.search}${url.hash}`); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    if (new URLSearchParams(window.location.search).get('perfil') === 'meu' && profileModal?.showModal) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        profileModal.showModal(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
})(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
