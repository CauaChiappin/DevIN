/*
 * REVISÃO DETALHADA DEVIN — js/chat.js
 * Finalidade: chat.
 * Domínio: interações do navegador.
 * Percurso: entrada -> validação -> regra de negócio -> resposta ou efeito.
 * Os comentários ao lado de cada instrução são mantidos; as notas adicionais
 * explicam as decisões e os efeitos do bloco sem modificar o comportamento.
 */

(() => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    'use strict'; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.

    const app = document.querySelector('[data-chat-app]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    if (!app) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

    const translate = (value) => window.DevINTranslate?.(value) ?? value; // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const locale = document.documentElement.lang === 'en' // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        ? 'en-US' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        : document.documentElement.lang === 'es' ? 'es-ES' : 'pt-BR'; // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const apiUrl = app.dataset.api; // Explicação DevIN: Declara variável JavaScript apiUrl para reutilizar valor ou resultado.
    const csrfToken = app.dataset.csrf; // Explicação DevIN: Declara variável JavaScript csrfToken para reutilizar valor ou resultado.
    const contactsElement = app.querySelector('[data-chat-contacts]'); // Explicação DevIN: Declara variável JavaScript contactsElement para reutilizar valor ou resultado.
    const contactsStatus = app.querySelector('[data-chat-contacts-status]'); // Explicação DevIN: Declara variável JavaScript contactsStatus para reutilizar valor ou resultado.
    const searchInput = app.querySelector('[data-chat-search]'); // Explicação DevIN: Declara variável JavaScript searchInput para reutilizar valor ou resultado.
    const unreadTotalElement = app.querySelector('[data-chat-unread-total]'); // Explicação DevIN: Declara variável JavaScript unreadTotalElement para reutilizar valor ou resultado.
    const notificationElement = app.querySelector('[data-chat-notification]'); // Explicação DevIN: Declara variável JavaScript notificationElement para reutilizar valor ou resultado.
    const titleElement = app.querySelector('[data-chat-title]'); // Explicação DevIN: Declara variável JavaScript titleElement para reutilizar valor ou resultado.
    const subtitleElement = app.querySelector('[data-chat-subtitle]'); // Explicação DevIN: Declara variável JavaScript subtitleElement para reutilizar valor ou resultado.
    const conversationAvatar = app.querySelector('[data-chat-avatar]'); // Explicação DevIN: Declara variável JavaScript conversationAvatar para reutilizar valor ou resultado.
    const profileDialog = document.querySelector('[data-chat-profile-dialog]'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    const profileNameElement = profileDialog?.querySelector('[data-chat-profile-name]'); // Explicação DevIN: Declara variável JavaScript profileNameElement para reutilizar valor ou resultado.
    const profileTypeElement = profileDialog?.querySelector('[data-chat-profile-type]'); // Explicação DevIN: Declara variável JavaScript profileTypeElement para reutilizar valor ou resultado.
    const profileAvatarElement = profileDialog?.querySelector('[data-chat-profile-avatar]'); // Explicação DevIN: Declara variável JavaScript profileAvatarElement para reutilizar valor ou resultado.
    const profileReportButton = profileDialog?.querySelector('[data-chat-profile-report-open]'); // Explicação DevIN: Declara variável JavaScript profileReportButton para reutilizar valor ou resultado.
    const profileReportForm = profileDialog?.querySelector('[data-chat-profile-report-form]'); // Explicação DevIN: Declara variável JavaScript profileReportForm para reutilizar valor ou resultado.
    const profileReportReason = profileDialog?.querySelector('[data-chat-profile-reason]'); // Explicação DevIN: Declara variável JavaScript profileReportReason para reutilizar valor ou resultado.
    const profileReportDetails = profileDialog?.querySelector('[data-chat-profile-details]'); // Explicação DevIN: Declara variável JavaScript profileReportDetails para reutilizar valor ou resultado.
    const profileStatus = profileDialog?.querySelector('[data-chat-profile-status]'); // Explicação DevIN: Declara variável JavaScript profileStatus para reutilizar valor ou resultado.
    const profileFeedbackToast = profileStatus?.closest('[data-feedback-toast]'); // Explicação DevIN: Declara variável JavaScript profileFeedbackToast para reutilizar valor ou resultado.
    const profileSubmitButton = profileDialog?.querySelector('[data-chat-profile-submit]'); // Explicação DevIN: Declara variável JavaScript profileSubmitButton para reutilizar valor ou resultado.
    const backToContactsButton = app.querySelector('[data-chat-back]'); // Explicação DevIN: Declara variável JavaScript backToContactsButton para reutilizar valor ou resultado.
    const profileTrigger = app.querySelector('[data-chat-profile-open]'); // Explicação DevIN: Declara variável JavaScript profileTrigger para reutilizar valor ou resultado.
    const conversationMenu = app.querySelector('.chat-conversation-menu'); // Explicação DevIN: Declara variável JavaScript conversationMenu para reutilizar valor ou resultado.
    const conversationMenuToggle = app.querySelector('[data-chat-menu-toggle]'); // Explicação DevIN: Declara variável JavaScript conversationMenuToggle para reutilizar valor ou resultado.
    const deleteConversationButton = app.querySelector('[data-chat-delete-conversation]'); // Explicação DevIN: Declara variável JavaScript deleteConversationButton para reutilizar valor ou resultado.
    const messagesElement = app.querySelector('[data-chat-messages]'); // Explicação DevIN: Declara variável JavaScript messagesElement para reutilizar valor ou resultado.
    const form = app.querySelector('[data-chat-form]'); // Explicação DevIN: Declara variável JavaScript form para reutilizar valor ou resultado.
    const messageInput = app.querySelector('[data-chat-input]'); // Explicação DevIN: Declara variável JavaScript messageInput para reutilizar valor ou resultado.
    const defaultMessagePlaceholder = messageInput?.placeholder || ''; // Explicação DevIN: Declara variável JavaScript defaultMessagePlaceholder para reutilizar valor ou resultado.
    const sendButton = app.querySelector('[data-chat-send]'); // Explicação DevIN: Declara variável JavaScript sendButton para reutilizar valor ou resultado.
    const sendStatus = app.querySelector('[data-chat-send-status]'); // Explicação DevIN: Declara variável JavaScript sendStatus para reutilizar valor ou resultado.

    let activeContact = null; // Explicação DevIN: Declara variável JavaScript activeContact para reutilizar valor ou resultado.
    let lastMessageId = 0; // Explicação DevIN: Declara variável JavaScript lastMessageId para reutilizar valor ou resultado.
    let messageViewVersion = 0; // Explicação DevIN: Declara variável JavaScript messageViewVersion para reutilizar valor ou resultado.
    let requestInProgress = false; // Explicação DevIN: Declara variável JavaScript requestInProgress para reutilizar valor ou resultado.
    let contactsRequestInProgress = false; // Explicação DevIN: Declara variável JavaScript contactsRequestInProgress para reutilizar valor ou resultado.
    let contacts = []; // Explicação DevIN: Declara variável JavaScript contacts para reutilizar valor ou resultado.
    let contactsLoaded = false; // Explicação DevIN: Declara variável JavaScript contactsLoaded para reutilizar valor ou resultado.
    let searchResults = null; // Explicação DevIN: Declara variável JavaScript searchResults para reutilizar valor ou resultado.
    let searchVersion = 0; // Explicação DevIN: Declara variável JavaScript searchVersion para reutilizar valor ou resultado.
    let unreadTotal = 0; // Explicação DevIN: Declara variável JavaScript unreadTotal para reutilizar valor ou resultado.
    let notificationTimeout = 0; // Explicação DevIN: Declara variável JavaScript notificationTimeout para reutilizar valor ou resultado.
    let searchTimeout = 0; // Explicação DevIN: Declara variável JavaScript searchTimeout para reutilizar valor ou resultado.
    let profileReportTarget = null;
    let autoReportFromDirectory = false; // Explicação DevIN: Guarda a intenção voluntária de denunciar selecionada pela empresa. // Explicação DevIN: Declara variável JavaScript profileReportTarget para reutilizar valor ou resultado.

    const request = async (query, options = {}) => { // Explicação DevIN: Declara variável JavaScript request para reutilizar valor ou resultado.
        const url = new URL(apiUrl, window.location.href); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        Object.entries(query).forEach(([key, value]) => url.searchParams.set(key, String(value))); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        const response = await fetch(url, { // Explicação DevIN: Inicia solicitação HTTP assíncrona a um endereço configurado.
            credentials: 'same-origin', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            ...options, // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            headers: { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                ...(options.body ? { 'Content-Type': 'application/json' } : {}), // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                ...(options.headers || {}), // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            }, // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        const data = await response.json(); // Explicação DevIN: Declara variável JavaScript data para reutilizar valor ou resultado.
        if (!response.ok || data.sucesso !== true) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            throw new Error(translate(data.mensagem || 'Não foi possível concluir a solicitação.')); // Explicação DevIN: Interrompe o fluxo atual levantando exceção ou erro.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return data; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    const initials = (name) => String(name).trim().split(/\s+/).slice(0, 2) // Explicação DevIN: Declara variável JavaScript initials para reutilizar valor ou resultado.
        .map((part) => part.charAt(0)).join('').toLocaleUpperCase(locale) || 'DI'; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.

    const renderAvatar = (element, contact) => { // Explicação DevIN: Declara variável JavaScript renderAvatar para reutilizar valor ou resultado.
        if (!element) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

        const fallback = initials(contact.nome); // Explicação DevIN: Declara variável JavaScript fallback para reutilizar valor ou resultado.
        element.textContent = fallback; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        if (contact.tipo === 'sistema') return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

        const url = new URL(apiUrl, window.location.href); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        url.searchParams.set('acao', 'foto'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        url.searchParams.set('tipo', contact.tipo); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        url.searchParams.set('id', String(contact.id)); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

        const image = document.createElement('img'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        image.src = url.toString(); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        image.alt = ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        image.addEventListener('error', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
            element.textContent = fallback; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        }, { once: true }); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        element.replaceChildren(image); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    const renderContacts = () => { // Explicação DevIN: Declara variável JavaScript renderContacts para reutilizar valor ou resultado.
        contactsElement.replaceChildren(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        const filter = searchInput.value.trim().toLocaleLowerCase(locale); // Explicação DevIN: Declara variável JavaScript filter para reutilizar valor ou resultado.
        const searching = filter.length >= 2; // Explicação DevIN: Declara variável JavaScript searching para reutilizar valor ou resultado.
        const source = searching ? (searchResults || []) : contacts; // Explicação DevIN: Declara variável JavaScript source para reutilizar valor ou resultado.
        const visibleContacts = searching // Explicação DevIN: Declara variável JavaScript visibleContacts para reutilizar valor ou resultado.
            ? source // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            : source.filter((contact) => // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                `${contact.nome} ${contact.rotulo}`.toLocaleLowerCase(locale).includes(filter)); // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.

        if (visibleContacts.length === 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            contactsStatus.textContent = searching // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                ? translate(searchResults === null ? 'Buscando pessoas e empresas...' : 'Nenhum usuário encontrado.') // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                : translate(filter.length === 1 // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    ? 'Digite pelo menos 2 caracteres para buscar.' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    : (filter ? 'Nenhum contato encontrado.' : 'Nenhuma conversa por aqui. Busque alguém para começar.')); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        contactsStatus.textContent = ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        visibleContacts.forEach((contact) => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            const button = document.createElement('button'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            button.type = 'button'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            button.className = 'chat-contact'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            button.setAttribute('aria-pressed', String(activeContact?.tipo === contact.tipo && activeContact?.id === contact.id)); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            button.addEventListener('click', () => openConversation(contact)); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.

            const avatar = document.createElement('span'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            avatar.className = 'chat-contact-avatar'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            renderAvatar(avatar, contact); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

            const copy = document.createElement('span'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            copy.className = 'chat-contact-copy'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            const name = document.createElement('strong'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            name.textContent = contact.nome; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            const type = document.createElement('small'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            type.textContent = translate(contact.rotulo); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            copy.append(name, type); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            button.append(avatar, copy); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            if (contact.nao_lidas > 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                const unread = document.createElement('span'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                unread.className = 'chat-contact-unread'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                unread.textContent = String(contact.nao_lidas); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                unread.setAttribute('aria-label', translate(`${contact.nao_lidas} mensagens não lidas`)); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                button.appendChild(unread); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            contactsElement.appendChild(button); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    const refreshContacts = async () => { // Explicação DevIN: Declara variável JavaScript refreshContacts para reutilizar valor ou resultado.
        if (contactsRequestInProgress) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        contactsRequestInProgress = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            const data = await request({ acao: 'contatos' }); // Explicação DevIN: Declara variável JavaScript data para reutilizar valor ou resultado.
            const previousContacts = contacts; // Explicação DevIN: Declara variável JavaScript previousContacts para reutilizar valor ou resultado.
            const previousUnreadTotal = unreadTotal; // Explicação DevIN: Declara variável JavaScript previousUnreadTotal para reutilizar valor ou resultado.
            contacts = data.contatos; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            unreadTotal = data.nao_lidas; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.

            if (unreadTotalElement) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                unreadTotalElement.hidden = unreadTotal === 0; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                unreadTotalElement.textContent = String(unreadTotal); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                unreadTotalElement.setAttribute('aria-label', translate(`${unreadTotal} mensagens não lidas`)); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            if (contactsLoaded && unreadTotal > previousUnreadTotal && notificationElement) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                const contactWithNewMessages = contacts.find((contact) => { // Explicação DevIN: Declara variável JavaScript contactWithNewMessages para reutilizar valor ou resultado.
                    const previous = previousContacts.find((item) => // Explicação DevIN: Declara variável JavaScript previous para reutilizar valor ou resultado.
                        item.tipo === contact.tipo && item.id === contact.id); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    return contact.nao_lidas > (previous?.nao_lidas || 0); // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
                }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                notificationElement.textContent = contactWithNewMessages // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    ? (document.documentElement.lang === 'en' // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                        ? `New message from ${contactWithNewMessages.nome}. ${unreadTotal} unread messages.` // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                        : document.documentElement.lang === 'es' // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                            ? `Nuevo mensaje de ${contactWithNewMessages.nome}. ${unreadTotal} mensajes sin leer.` // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                            : `Nova mensagem de ${contactWithNewMessages.nome}. ${unreadTotal} mensagens não lidas.`) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    : translate(`${unreadTotal} mensagens não lidas.`); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                window.clearTimeout(notificationTimeout); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                notificationTimeout = window.setTimeout(() => { // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                    notificationElement.textContent = ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                }, 6000); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            contactsLoaded = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            renderContacts(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
            contactsRequestInProgress = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    const searchContacts = async () => { // Explicação DevIN: Declara variável JavaScript searchContacts para reutilizar valor ou resultado.
        const term = searchInput.value.trim(); // Explicação DevIN: Declara variável JavaScript term para reutilizar valor ou resultado.
        const version = ++searchVersion; // Explicação DevIN: Declara variável JavaScript version para reutilizar valor ou resultado.
        window.clearTimeout(searchTimeout); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.

        if (term.length < 2) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            searchResults = null; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            renderContacts(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        searchResults = null; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        renderContacts(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        searchTimeout = window.setTimeout(async () => { // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                const data = await request({ acao: 'buscar_contatos', termo: term }); // Explicação DevIN: Declara variável JavaScript data para reutilizar valor ou resultado.
                if (version !== searchVersion || searchInput.value.trim() !== term) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                searchResults = data.contatos; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                renderContacts(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } catch (error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
                if (version !== searchVersion) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                searchResults = []; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                contactsStatus.textContent = error.message; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                contactsElement.replaceChildren(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        }, 250); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    const renderMessage = (message, existingBubble = null) => { // Explicação DevIN: Declara variável JavaScript renderMessage para reutilizar valor ou resultado.
        const bubble = document.createElement('article'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        bubble.className = `chat-bubble${message.minha ? ' chat-bubble--mine' : ''}`; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        bubble.dataset.chatMessageId = String(message.id); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.

        const text = document.createElement('p'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        if (message.apagada_para_todos) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            bubble.classList.add('chat-bubble--deleted'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            text.textContent = translate(message.minha ? 'Você apagou esta mensagem' : 'Esta mensagem foi apagada'); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
            text.textContent = message.mensagem; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        const time = document.createElement('time'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        const date = new Date(message.enviada_em); // Explicação DevIN: Declara variável JavaScript date para reutilizar valor ou resultado.
        time.dateTime = message.enviada_em; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        if (!Number.isNaN(date.getTime())) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            time.textContent = date.toLocaleTimeString(locale, { hour: '2-digit', minute: '2-digit' }); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        bubble.append(text, time); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

        if (!message.apagada_para_todos) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            const menu = document.createElement('details'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            menu.className = 'chat-message-menu'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            const menuButton = document.createElement('summary'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            menuButton.setAttribute('aria-label', translate('Ações da mensagem')); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            menuButton.textContent = '⋮'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            const menuItems = document.createElement('div'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            menuItems.className = 'chat-message-menu-items'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.

            menu.addEventListener('toggle', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
                if (menu.open) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    messagesElement.querySelectorAll('.chat-message-menu[open]').forEach((openMenu) => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                        if (openMenu !== menu) openMenu.open = false; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                bubble.classList.toggle('chat-bubble--menu-open', menu.open); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            const addAction = (label, action) => { // Explicação DevIN: Declara variável JavaScript addAction para reutilizar valor ou resultado.
                const button = document.createElement('button'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                button.type = 'button'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                button.textContent = label; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                button.addEventListener('click', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
                    menu.open = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    void action(button); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                menuItems.appendChild(button); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            if (!message.minha) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                const translation = document.createElement('section'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                translation.className = 'chat-translation'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                translation.hidden = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                const translationLabel = document.createElement('strong'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                translationLabel.textContent = translate('Tradução'); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                const translationText = document.createElement('p'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                translation.append(translationLabel, translationText); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                bubble.appendChild(translation); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

                addAction(translate('Traduzir mensagem'), async (button) => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                    button.disabled = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    button.textContent = translate('Traduzindo...'); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    translation.hidden = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    translationLabel.textContent = translate('Tradução'); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    translationText.textContent = ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                        const data = await request({}, { // Explicação DevIN: Declara variável JavaScript data para reutilizar valor ou resultado.
                            method: 'POST', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                            headers: { 'X-CSRF-Token': csrfToken }, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                            body: JSON.stringify({ acao: 'traduzir', texto: message.mensagem }), // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
                        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                        translationText.textContent = data.traducao; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    } catch (error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
                        translationLabel.textContent = translate('Falha na tradução'); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                        translationText.textContent = error.message; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
                        button.disabled = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                        button.textContent = translate('Traduzir novamente'); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            addAction(translate('Excluir para mim'), (button) => // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                deleteMessage(message.id, 'excluir_mensagem', button)); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            if (message.minha) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                addAction(translate('Excluir para todos'), (button) => // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                    deleteMessage(message.id, 'excluir_para_todos', button)); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            menu.append(menuButton, menuItems); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            bubble.appendChild(menu); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        if (existingBubble) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            existingBubble.replaceWith(bubble); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
            const dayKey = Number.isNaN(date.getTime()) ? '' : date.toDateString(); // Explicação DevIN: Declara variável JavaScript dayKey para reutilizar valor ou resultado.
            bubble.dataset.chatDay = dayKey; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            const lastBubble = messagesElement.querySelector('.chat-bubble:last-of-type'); // Explicação DevIN: Declara variável JavaScript lastBubble para reutilizar valor ou resultado.
            if (dayKey && (!lastBubble || lastBubble.dataset.chatDay !== dayKey)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                const now = new Date(); // Explicação DevIN: Declara variável JavaScript now para reutilizar valor ou resultado.
                const yesterday = new Date(now); // Explicação DevIN: Declara variável JavaScript yesterday para reutilizar valor ou resultado.
                yesterday.setDate(now.getDate() - 1); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                const separator = document.createElement('div'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                separator.className = 'chat-day-separator'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                const label = document.createElement('span'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                label.textContent = dayKey === now.toDateString() // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                    ? translate('Hoje') // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                    : dayKey === yesterday.toDateString() // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                        ? translate('Ontem') // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                        : date.toLocaleDateString(locale, { day: '2-digit', month: 'long', year: 'numeric' }); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
                separator.appendChild(label); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                messagesElement.appendChild(separator); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            messagesElement.appendChild(bubble); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    const loadMessages = async (reset = false) => { // Explicação DevIN: Declara variável JavaScript loadMessages para reutilizar valor ou resultado.
        if (!activeContact || requestInProgress) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        const requestedContact = activeContact; // Explicação DevIN: Declara variável JavaScript requestedContact para reutilizar valor ou resultado.
        const requestedVersion = messageViewVersion; // Explicação DevIN: Declara variável JavaScript requestedVersion para reutilizar valor ou resultado.
        requestInProgress = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        let shouldReload = false; // Explicação DevIN: Declara variável JavaScript shouldReload para reutilizar valor ou resultado.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            const data = await request({ // Explicação DevIN: Declara variável JavaScript data para reutilizar valor ou resultado.
                acao: 'conversa', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                tipo: requestedContact.tipo, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                id: requestedContact.id, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                depois_de: reset ? 0 : lastMessageId, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            if (activeContact !== requestedContact || messageViewVersion !== requestedVersion) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            sendStatus.textContent = ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            if (reset) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                messagesElement.replaceChildren(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                lastMessageId = 0; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            data.mensagens.forEach((message) => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                if (message.id <= lastMessageId) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    const existingBubble = messagesElement.querySelector(`[data-chat-message-id="${message.id}"]`); // Explicação DevIN: Declara variável JavaScript existingBubble para reutilizar valor ou resultado.
                    if (message.apagada_para_todos && existingBubble) renderMessage(message, existingBubble); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                    return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
                } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
                renderMessage(message); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                lastMessageId = message.id; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            if (messagesElement.childElementCount === 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                const empty = document.createElement('p'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                empty.className = 'chat-empty'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                empty.textContent = translate('Esta conversa ainda não tem mensagens. Envie a primeira!'); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
                messagesElement.appendChild(empty); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } else { // Explicação DevIN: Define ramo alternativo para condição anterior.
                messagesElement.querySelector('.chat-empty')?.remove(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                messagesElement.scrollTop = messagesElement.scrollHeight; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            deleteConversationButton.disabled = !messagesElement.querySelector('.chat-bubble'); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } catch (error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            if (activeContact === requestedContact) sendStatus.textContent = error.message; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
            shouldReload = activeContact !== requestedContact || messageViewVersion !== requestedVersion; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            requestInProgress = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            if (shouldReload) void loadMessages(true); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    const deleteMessage = async (messageId, action, button) => { // Explicação DevIN: Declara variável JavaScript deleteMessage para reutilizar valor ou resultado.
        if (!activeContact) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        const excluirParaTodos = action === 'excluir_para_todos'; // Explicação DevIN: Declara variável JavaScript excluirParaTodos para reutilizar valor ou resultado.
        const confirmacao = translate(excluirParaTodos // Explicação DevIN: Declara variável JavaScript confirmacao para reutilizar valor ou resultado.
            ? 'Excluir esta mensagem para você e para o outro participante?' // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            : 'Excluir esta mensagem só para você?'); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        if (!window.confirm(confirmacao)) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

        button.disabled = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        sendStatus.textContent = ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            await request({}, { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                method: 'POST', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                headers: { 'X-CSRF-Token': csrfToken }, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                body: JSON.stringify({ acao: action, id_mensagem: messageId }), // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            messageViewVersion += 1; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            await loadMessages(true); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                await refreshContacts(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } catch (error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
                contactsStatus.textContent = error.message; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } catch (error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            sendStatus.textContent = error.message; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            button.disabled = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    profileTrigger?.addEventListener('click', async () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        if (!activeContact || activeContact.tipo === 'sistema' || !profileDialog) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

        const requestedContact = { ...activeContact }; // Explicação DevIN: Declara variável JavaScript requestedContact para reutilizar valor ou resultado.
        const requestedVersion = messageViewVersion; // Explicação DevIN: Declara variável JavaScript requestedVersion para reutilizar valor ou resultado.
        profileTrigger.disabled = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        sendStatus.textContent = ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            const data = await request({ // Explicação DevIN: Declara variável JavaScript data para reutilizar valor ou resultado.
                acao: 'perfil', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                tipo: requestedContact.tipo, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                id: requestedContact.id, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            if ( // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                activeContact?.tipo !== requestedContact.tipo // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                || activeContact?.id !== requestedContact.id // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            ) { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                return; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

            profileReportTarget = data.perfil; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            profileNameElement.textContent = data.perfil.nome; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            profileTypeElement.textContent = translate(data.perfil.rotulo); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            renderAvatar(profileAvatarElement, data.perfil); // Explicação DevIN: Apresenta foto real do contato validado no chat. // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            profileFeedbackToast.hidden = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            profileReportForm.reset(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            profileReportForm.hidden = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            profileReportButton.hidden = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            profileSubmitButton.disabled = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            profileDialog.showModal();
            if (autoReportFromDirectory) { autoReportFromDirectory = false; profileReportButton?.click(); } // Explicação DevIN: Abre o formulário existente sob pedido do usuário. // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } catch (error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            autoReportFromDirectory = false; // Explicação DevIN: Impede retomada de denúncia após erro.
            sendStatus.textContent = error.message; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
            if (messageViewVersion === requestedVersion) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
                profileTrigger.disabled = !activeContact || activeContact.tipo === 'sistema'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    profileReportButton?.addEventListener('click', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        profileReportButton.hidden = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        profileReportForm.hidden = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        profileFeedbackToast.hidden = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        profileReportReason.focus(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    profileDialog?.querySelector('[data-chat-profile-report-cancel]')?.addEventListener('click', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        profileReportForm.hidden = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        profileReportButton.hidden = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        profileFeedbackToast.hidden = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    profileDialog?.querySelector('[data-chat-profile-close]')?.addEventListener('click', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        profileDialog.close(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    profileReportForm?.addEventListener('submit', async (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        event.preventDefault(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        if (!profileReportTarget) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

        profileSubmitButton.disabled = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        profileFeedbackToast.hidden = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            const data = await request({}, { // Explicação DevIN: Declara variável JavaScript data para reutilizar valor ou resultado.
                method: 'POST', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                headers: { 'X-CSRF-Token': csrfToken }, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                body: JSON.stringify({ // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
                    acao: 'denunciar_perfil', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                    tipo: profileReportTarget.tipo, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                    id: profileReportTarget.id, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                    motivo: profileReportReason.value, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                    detalhes: profileReportDetails.value, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                }), // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            const warnings = Array.isArray(data.avisos) ? data.avisos.map(translate) : []; // Explicação DevIN: Declara variável JavaScript warnings para reutilizar valor ou resultado.
            window.DevINShowToast?.( // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                profileFeedbackToast, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                [translate(data.mensagem || 'Denúncia enviada para análise da equipe DevIN.'), ...warnings].join(' '), // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                warnings.length > 0 ? 'warning' : 'success' // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            ); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            profileReportForm.hidden = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            profileReportButton.hidden = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } catch (error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            window.DevINShowToast?.(profileFeedbackToast, error.message, 'error'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
            profileSubmitButton.disabled = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    const openConversation = async (contact) => { // Explicação DevIN: Declara variável JavaScript openConversation para reutilizar valor ou resultado.
        activeContact = { ...contact }; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        const requestedContact = activeContact; // Explicação DevIN: Declara variável JavaScript requestedContact para reutilizar valor ou resultado.
        app.classList.add('chat-conversation-open'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        messageViewVersion += 1; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        lastMessageId = 0; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        titleElement.textContent = contact.nome; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        subtitleElement.textContent = `${translate(contact.rotulo)} · ${translate('Atualização automática')}`; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        renderAvatar(conversationAvatar, contact); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        profileTrigger.disabled = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        messageInput.disabled = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        sendButton.disabled = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        deleteConversationButton.disabled = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        sendStatus.textContent = ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        messageInput.focus(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        renderContacts(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        messagesElement.replaceChildren(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        const loading = document.createElement('p'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        loading.className = 'chat-empty'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        loading.textContent = translate('Carregando conversa...'); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        messagesElement.appendChild(loading); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        if (requestedContact.tipo === 'sistema' && requestedContact.id === 0) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            messageInput.disabled = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            sendButton.disabled = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            messageInput.placeholder = translate('Notificações da plataforma não aceitam respostas.'); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            await loadMessages(true); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                await refreshContacts(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } catch (error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
                contactsStatus.textContent = error.message; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            return true; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

        messageInput.placeholder = defaultMessagePlaceholder; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            const data = await request({}, { // Explicação DevIN: Declara variável JavaScript data para reutilizar valor ou resultado.
                method: 'POST', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                headers: { 'X-CSRF-Token': csrfToken }, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                body: JSON.stringify({ // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
                    acao: 'iniciar_conversa', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                    tipo: requestedContact.tipo, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                    id: requestedContact.id, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                }), // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            if (activeContact !== requestedContact) return false; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            activeContact = data.contato; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            titleElement.textContent = activeContact.nome; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            subtitleElement.textContent = `${translate(activeContact.rotulo)} · ${translate('Atualização automática')}`; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            renderAvatar(conversationAvatar, activeContact); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            profileTrigger.disabled = activeContact.tipo === 'sistema'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } catch (error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            if (activeContact === requestedContact) sendStatus.textContent = error.message; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            return false; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        await loadMessages(true); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            await refreshContacts(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } catch (error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            contactsStatus.textContent = error.message; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        return true; // Explicação DevIN: Retorna ou produz resultado da função, conforme a expressão informada.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    backToContactsButton?.addEventListener('click', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        app.classList.remove('chat-conversation-open'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        messageInput.blur(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        contactsElement.querySelector('[aria-pressed="true"]')?.focus(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    conversationMenu?.addEventListener('toggle', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        conversationMenuToggle?.setAttribute('aria-expanded', String(conversationMenu.open)); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    deleteConversationButton.addEventListener('click', async () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        if (!activeContact || !window.confirm(translate('Excluir esta conversa só para você?'))) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

        conversationMenu.open = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        deleteConversationButton.disabled = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        sendStatus.textContent = ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            await request({}, { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                method: 'POST', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                headers: { 'X-CSRF-Token': csrfToken }, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                body: JSON.stringify({ // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
                    acao: 'excluir_conversa', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                    tipo: activeContact.tipo, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                    id: activeContact.id, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                }), // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            messageViewVersion += 1; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            lastMessageId = 0; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            await loadMessages(true); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                await refreshContacts(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } catch (error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
                contactsStatus.textContent = error.message; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } catch (error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            sendStatus.textContent = error.message; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            deleteConversationButton.disabled = !activeContact; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    searchInput.addEventListener('input', searchContacts); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    document.addEventListener('click', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        if (!(event.target instanceof Element)) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if (!event.target.closest('.chat-message-menu')) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            messagesElement.querySelectorAll('.chat-message-menu[open]').forEach((menu) => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                menu.open = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (conversationMenu?.open && !conversationMenu.contains(event.target)) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            conversationMenu.open = false; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    const resizeMessageInput = () => { // Explicação DevIN: Declara variável JavaScript resizeMessageInput para reutilizar valor ou resultado.
        messageInput.style.height = 'auto'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        const maxHeight = Number.parseFloat(window.getComputedStyle(messageInput).maxHeight); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        const height = Number.isFinite(maxHeight) // Explicação DevIN: Declara variável JavaScript height para reutilizar valor ou resultado.
            ? Math.min(messageInput.scrollHeight, maxHeight) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            : messageInput.scrollHeight; // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
        messageInput.style.height = `${height}px`; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        messageInput.style.overflowY = messageInput.scrollHeight > height ? 'auto' : 'hidden'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    messageInput.addEventListener('input', resizeMessageInput); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    messageInput.addEventListener('keydown', (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        if (event.key !== 'Enter' || event.shiftKey || event.isComposing || event.keyCode === 229) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        event.preventDefault(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        if (activeContact && !sendButton.disabled) form.requestSubmit(); // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    form.addEventListener('submit', async (event) => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        event.preventDefault(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        if (!activeContact || !messageInput.value.trim()) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

        sendButton.disabled = true; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        sendButton.textContent = translate('Enviando...'); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        sendStatus.textContent = ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            await request({}, { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                method: 'POST', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                headers: { 'X-CSRF-Token': csrfToken }, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                body: JSON.stringify({ // Explicação DevIN: Converte dados entre estrutura interna e representação JSON.
                    acao: 'enviar', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                    tipo: activeContact.tipo, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                    id: activeContact.id, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                    mensagem: messageInput.value, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                }), // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            messageInput.value = ''; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            resizeMessageInput(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            await loadMessages(false); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
                await refreshContacts(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            } catch (error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
                contactsStatus.textContent = error.message; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } catch (error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            sendStatus.textContent = error.message; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } finally { // Explicação DevIN: Declara limpeza ou finalização executada ao encerrar a tentativa.
            sendButton.disabled = !activeContact; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            sendButton.textContent = translate('Enviar'); // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            messageInput.focus(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    const initializeChat = async () => { // Explicação DevIN: Declara variável JavaScript initializeChat para reutilizar valor ou resultado.
        try { // Explicação DevIN: Inicia bloco de operação com tratamento de possíveis falhas.
            await refreshContacts(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            const parameters = new URLSearchParams(window.location.search); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            const tipo = parameters.get('contato_tipo'); // Explicação DevIN: Declara variável JavaScript tipo para reutilizar valor ou resultado.
            const id = Number(parameters.get('contato_id')); // Explicação DevIN: Declara variável JavaScript id para reutilizar valor ou resultado.
            if (!['pessoa', 'empresa'].includes(tipo) || !Number.isInteger(id) || id < 1) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.

            const opened = await openConversation({ // Explicação DevIN: Declara variável JavaScript opened para reutilizar valor ou resultado.
                tipo, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                id, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                nome: translate('Carregando conversa...'), // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                rotulo: tipo === 'pessoa' ? 'Pessoa' : 'Empresa', // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                nao_lidas: 0, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
                ultima_mensagem_id: 0, // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
            if (!opened) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            if (parameters.get('reportar') === '1' && tipo === 'pessoa') { // Explicação DevIN: Reconhece o pedido explícito para reportar candidato.
                autoReportFromDirectory = true; // Explicação DevIN: Aguarda a autorização de leitura do perfil.
                profileTrigger?.click(); // Explicação DevIN: Reutiliza o modal protegido de denúncias.
            } // Explicação DevIN: Finaliza a abertura opcional da denúncia.
            parameters.delete('reportar'); // Explicação DevIN: Impede repetição involuntária do modal.
            parameters.delete('contato_tipo'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            parameters.delete('contato_id'); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            const cleanUrl = `${window.location.pathname}?${parameters.toString()}${window.location.hash}`; // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
            window.history.replaceState({}, '', cleanUrl); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        } catch (error) { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
            contactsStatus.textContent = error.message; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }; // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    void initializeChat(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.

    window.setInterval(() => { // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        if (document.visibilityState !== 'visible') return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        loadMessages() // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
            .then(refreshContacts) // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.
            .catch((error) => { // Explicação DevIN: Captura exceção ou erro para tratar a falha de execução.
                contactsStatus.textContent = error.message; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }, 4000); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
})(); // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
