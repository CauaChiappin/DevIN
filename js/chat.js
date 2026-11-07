(() => {
    'use strict';

    const app = document.querySelector('[data-chat-app]');
    if (!app) return;

    const translate = (value) => window.DevINTranslate?.(value) ?? value;
    const locale = document.documentElement.lang === 'en'
        ? 'en-US'
        : document.documentElement.lang === 'es' ? 'es-ES' : 'pt-BR';
    const apiUrl = app.dataset.api;
    const csrfToken = app.dataset.csrf;
    const contactsElement = app.querySelector('[data-chat-contacts]');
    const contactsStatus = app.querySelector('[data-chat-contacts-status]');
    const searchInput = app.querySelector('[data-chat-search]');
    const unreadTotalElement = app.querySelector('[data-chat-unread-total]');
    const notificationElement = app.querySelector('[data-chat-notification]');
    const titleElement = app.querySelector('[data-chat-title]');
    const subtitleElement = app.querySelector('[data-chat-subtitle]');
    const conversationAvatar = app.querySelector('[data-chat-avatar]');
    const profileDialog = document.querySelector('[data-chat-profile-dialog]');
    const profileNameElement = profileDialog?.querySelector('[data-chat-profile-name]');
    const profileTypeElement = profileDialog?.querySelector('[data-chat-profile-type]');
    const profileAvatarElement = profileDialog?.querySelector('[data-chat-profile-avatar]');
    const profileReportButton = profileDialog?.querySelector('[data-chat-profile-report-open]');
    const profileReportForm = profileDialog?.querySelector('[data-chat-profile-report-form]');
    const profileReportReason = profileDialog?.querySelector('[data-chat-profile-reason]');
    const profileReportDetails = profileDialog?.querySelector('[data-chat-profile-details]');
    const profileStatus = profileDialog?.querySelector('[data-chat-profile-status]');
    const profileFeedbackToast = profileStatus?.closest('[data-feedback-toast]');
    const profileSubmitButton = profileDialog?.querySelector('[data-chat-profile-submit]');
    const backToContactsButton = app.querySelector('[data-chat-back]');
    const profileTrigger = app.querySelector('[data-chat-profile-open]');
    const conversationMenu = app.querySelector('.chat-conversation-menu');
    const conversationMenuToggle = app.querySelector('[data-chat-menu-toggle]');
    const deleteConversationButton = app.querySelector('[data-chat-delete-conversation]');
    const messagesElement = app.querySelector('[data-chat-messages]');
    const form = app.querySelector('[data-chat-form]');
    const messageInput = app.querySelector('[data-chat-input]');
    const defaultMessagePlaceholder = messageInput?.placeholder || '';
    const sendButton = app.querySelector('[data-chat-send]');
    const sendStatus = app.querySelector('[data-chat-send-status]');

    let activeContact = null;
    let lastMessageId = 0;
    let messageViewVersion = 0;
    let requestInProgress = false;
    let contactsRequestInProgress = false;
    let contacts = [];
    let contactsLoaded = false;
    let searchResults = null;
    let searchVersion = 0;
    let unreadTotal = 0;
    let notificationTimeout = 0;
    let searchTimeout = 0;
    let profileReportTarget = null;

    const request = async (query, options = {}) => {
        const url = new URL(apiUrl, window.location.href);
        Object.entries(query).forEach(([key, value]) => url.searchParams.set(key, String(value)));
        const response = await fetch(url, {
            credentials: 'same-origin',
            ...options,
            headers: {
                ...(options.body ? { 'Content-Type': 'application/json' } : {}),
                ...(options.headers || {}),
            },
        });
        const data = await response.json();
        if (!response.ok || data.sucesso !== true) {
            throw new Error(translate(data.mensagem || 'Não foi possível concluir a solicitação.'));
        }
        return data;
    };

    const initials = (name) => String(name).trim().split(/\s+/).slice(0, 2)
        .map((part) => part.charAt(0)).join('').toLocaleUpperCase(locale) || 'DI';

    const renderAvatar = (element, contact) => {
        if (!element) return;

        const fallback = initials(contact.nome);
        element.textContent = fallback;
        if (contact.tipo === 'sistema') return;

        const url = new URL(apiUrl, window.location.href);
        url.searchParams.set('acao', 'foto');
        url.searchParams.set('tipo', contact.tipo);
        url.searchParams.set('id', String(contact.id));

        const image = document.createElement('img');
        image.src = url.toString();
        image.alt = '';
        image.addEventListener('error', () => {
            element.textContent = fallback;
        }, { once: true });
        element.replaceChildren(image);
    };

    const renderContacts = () => {
        contactsElement.replaceChildren();
        const filter = searchInput.value.trim().toLocaleLowerCase(locale);
        const searching = filter.length >= 2;
        const source = searching ? (searchResults || []) : contacts;
        const visibleContacts = searching
            ? source
            : source.filter((contact) =>
                `${contact.nome} ${contact.rotulo}`.toLocaleLowerCase(locale).includes(filter));

        if (visibleContacts.length === 0) {
            contactsStatus.textContent = searching
                ? translate(searchResults === null ? 'Buscando pessoas e empresas...' : 'Nenhum usuário encontrado.')
                : translate(filter.length === 1
                    ? 'Digite pelo menos 2 caracteres para buscar.'
                    : (filter ? 'Nenhum contato encontrado.' : 'Nenhuma conversa por aqui. Busque alguém para começar.'));
            return;
        }

        contactsStatus.textContent = '';
        visibleContacts.forEach((contact) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'chat-contact';
            button.setAttribute('aria-pressed', String(activeContact?.tipo === contact.tipo && activeContact?.id === contact.id));
            button.addEventListener('click', () => openConversation(contact));

            const avatar = document.createElement('span');
            avatar.className = 'chat-contact-avatar';
            renderAvatar(avatar, contact);

            const copy = document.createElement('span');
            copy.className = 'chat-contact-copy';
            const name = document.createElement('strong');
            name.textContent = contact.nome;
            const type = document.createElement('small');
            type.textContent = translate(contact.rotulo);
            copy.append(name, type);
            button.append(avatar, copy);
            if (contact.nao_lidas > 0) {
                const unread = document.createElement('span');
                unread.className = 'chat-contact-unread';
                unread.textContent = String(contact.nao_lidas);
                unread.setAttribute('aria-label', translate(`${contact.nao_lidas} mensagens não lidas`));
                button.appendChild(unread);
            }
            contactsElement.appendChild(button);
        });
    };

    const refreshContacts = async () => {
        if (contactsRequestInProgress) return;
        contactsRequestInProgress = true;
        try {
            const data = await request({ acao: 'contatos' });
            const previousContacts = contacts;
            const previousUnreadTotal = unreadTotal;
            contacts = data.contatos;
            unreadTotal = data.nao_lidas;

            if (unreadTotalElement) {
                unreadTotalElement.hidden = unreadTotal === 0;
                unreadTotalElement.textContent = String(unreadTotal);
                unreadTotalElement.setAttribute('aria-label', translate(`${unreadTotal} mensagens não lidas`));
            }

            if (contactsLoaded && unreadTotal > previousUnreadTotal && notificationElement) {
                const contactWithNewMessages = contacts.find((contact) => {
                    const previous = previousContacts.find((item) =>
                        item.tipo === contact.tipo && item.id === contact.id);
                    return contact.nao_lidas > (previous?.nao_lidas || 0);
                });
                notificationElement.textContent = contactWithNewMessages
                    ? (document.documentElement.lang === 'en'
                        ? `New message from ${contactWithNewMessages.nome}. ${unreadTotal} unread messages.`
                        : document.documentElement.lang === 'es'
                            ? `Nuevo mensaje de ${contactWithNewMessages.nome}. ${unreadTotal} mensajes sin leer.`
                            : `Nova mensagem de ${contactWithNewMessages.nome}. ${unreadTotal} mensagens não lidas.`)
                    : translate(`${unreadTotal} mensagens não lidas.`);
                window.clearTimeout(notificationTimeout);
                notificationTimeout = window.setTimeout(() => {
                    notificationElement.textContent = '';
                }, 6000);
            }

            contactsLoaded = true;
            renderContacts();
        } finally {
            contactsRequestInProgress = false;
        }
    };

    const searchContacts = async () => {
        const term = searchInput.value.trim();
        const version = ++searchVersion;
        window.clearTimeout(searchTimeout);

        if (term.length < 2) {
            searchResults = null;
            renderContacts();
            return;
        }

        searchResults = null;
        renderContacts();
        searchTimeout = window.setTimeout(async () => {
            try {
                const data = await request({ acao: 'buscar_contatos', termo: term });
                if (version !== searchVersion || searchInput.value.trim() !== term) return;
                searchResults = data.contatos;
                renderContacts();
            } catch (error) {
                if (version !== searchVersion) return;
                searchResults = [];
                contactsStatus.textContent = error.message;
                contactsElement.replaceChildren();
            }
        }, 250);
    };

    const renderMessage = (message, existingBubble = null) => {
        const bubble = document.createElement('article');
        bubble.className = `chat-bubble${message.minha ? ' chat-bubble--mine' : ''}`;
        bubble.dataset.chatMessageId = String(message.id);

        const text = document.createElement('p');
        if (message.apagada_para_todos) {
            bubble.classList.add('chat-bubble--deleted');
            text.textContent = translate(message.minha ? 'Você apagou esta mensagem' : 'Esta mensagem foi apagada');
        } else {
            text.textContent = message.mensagem;
        }
        const time = document.createElement('time');
        const date = new Date(message.enviada_em);
        time.dateTime = message.enviada_em;
        if (!Number.isNaN(date.getTime())) {
            time.textContent = date.toLocaleTimeString(locale, { hour: '2-digit', minute: '2-digit' });
        }
        bubble.append(text, time);

        if (!message.apagada_para_todos) {
            const menu = document.createElement('details');
            menu.className = 'chat-message-menu';
            const menuButton = document.createElement('summary');
            menuButton.setAttribute('aria-label', translate('Ações da mensagem'));
            menuButton.textContent = '⋮';
            const menuItems = document.createElement('div');
            menuItems.className = 'chat-message-menu-items';

            menu.addEventListener('toggle', () => {
                if (menu.open) {
                    messagesElement.querySelectorAll('.chat-message-menu[open]').forEach((openMenu) => {
                        if (openMenu !== menu) openMenu.open = false;
                    });
                }
                bubble.classList.toggle('chat-bubble--menu-open', menu.open);
            });

            const addAction = (label, action) => {
                const button = document.createElement('button');
                button.type = 'button';
                button.textContent = label;
                button.addEventListener('click', () => {
                    menu.open = false;
                    void action(button);
                });
                menuItems.appendChild(button);
            };

            if (!message.minha) {
                const translation = document.createElement('section');
                translation.className = 'chat-translation';
                translation.hidden = true;
                const translationLabel = document.createElement('strong');
                translationLabel.textContent = translate('Tradução');
                const translationText = document.createElement('p');
                translation.append(translationLabel, translationText);
                bubble.appendChild(translation);

                addAction(translate('Traduzir mensagem'), async (button) => {
                    button.disabled = true;
                    button.textContent = translate('Traduzindo...');
                    translation.hidden = false;
                    translationLabel.textContent = translate('Tradução');
                    translationText.textContent = '';
                    try {
                        const data = await request({}, {
                            method: 'POST',
                            headers: { 'X-CSRF-Token': csrfToken },
                            body: JSON.stringify({ acao: 'traduzir', texto: message.mensagem }),
                        });
                        translationText.textContent = data.traducao;
                    } catch (error) {
                        translationLabel.textContent = translate('Falha na tradução');
                        translationText.textContent = error.message;
                    } finally {
                        button.disabled = false;
                        button.textContent = translate('Traduzir novamente');
                    }
                });
            }
            addAction(translate('Excluir para mim'), (button) =>
                deleteMessage(message.id, 'excluir_mensagem', button));
            if (message.minha) {
                addAction(translate('Excluir para todos'), (button) =>
                    deleteMessage(message.id, 'excluir_para_todos', button));
            }
            menu.append(menuButton, menuItems);
            bubble.appendChild(menu);
        }

        if (existingBubble) {
            existingBubble.replaceWith(bubble);
        } else {
            const dayKey = Number.isNaN(date.getTime()) ? '' : date.toDateString();
            bubble.dataset.chatDay = dayKey;
            const lastBubble = messagesElement.querySelector('.chat-bubble:last-of-type');
            if (dayKey && (!lastBubble || lastBubble.dataset.chatDay !== dayKey)) {
                const now = new Date();
                const yesterday = new Date(now);
                yesterday.setDate(now.getDate() - 1);
                const separator = document.createElement('div');
                separator.className = 'chat-day-separator';
                const label = document.createElement('span');
                label.textContent = dayKey === now.toDateString()
                    ? translate('Hoje')
                    : dayKey === yesterday.toDateString()
                        ? translate('Ontem')
                        : date.toLocaleDateString(locale, { day: '2-digit', month: 'long', year: 'numeric' });
                separator.appendChild(label);
                messagesElement.appendChild(separator);
            }
            messagesElement.appendChild(bubble);
        }
    };

    const loadMessages = async (reset = false) => {
        if (!activeContact || requestInProgress) return;
        const requestedContact = activeContact;
        const requestedVersion = messageViewVersion;
        requestInProgress = true;
        let shouldReload = false;
        try {
            const data = await request({
                acao: 'conversa',
                tipo: requestedContact.tipo,
                id: requestedContact.id,
                depois_de: reset ? 0 : lastMessageId,
            });
            if (activeContact !== requestedContact || messageViewVersion !== requestedVersion) return;
            sendStatus.textContent = '';
            if (reset) {
                messagesElement.replaceChildren();
                lastMessageId = 0;
            }

            data.mensagens.forEach((message) => {
                if (message.id <= lastMessageId) {
                    const existingBubble = messagesElement.querySelector(`[data-chat-message-id="${message.id}"]`);
                    if (message.apagada_para_todos && existingBubble) renderMessage(message, existingBubble);
                    return;
                }
                renderMessage(message);
                lastMessageId = message.id;
            });

            if (messagesElement.childElementCount === 0) {
                const empty = document.createElement('p');
                empty.className = 'chat-empty';
                empty.textContent = translate('Esta conversa ainda não tem mensagens. Envie a primeira!');
                messagesElement.appendChild(empty);
            } else {
                messagesElement.querySelector('.chat-empty')?.remove();
                messagesElement.scrollTop = messagesElement.scrollHeight;
            }
            deleteConversationButton.disabled = !messagesElement.querySelector('.chat-bubble');
        } catch (error) {
            if (activeContact === requestedContact) sendStatus.textContent = error.message;
        } finally {
            shouldReload = activeContact !== requestedContact || messageViewVersion !== requestedVersion;
            requestInProgress = false;
            if (shouldReload) void loadMessages(true);
        }
    };

    const deleteMessage = async (messageId, action, button) => {
        if (!activeContact) return;
        const excluirParaTodos = action === 'excluir_para_todos';
        const confirmacao = translate(excluirParaTodos
            ? 'Excluir esta mensagem para você e para o outro participante?'
            : 'Excluir esta mensagem só para você?');
        if (!window.confirm(confirmacao)) return;

        button.disabled = true;
        sendStatus.textContent = '';
        try {
            await request({}, {
                method: 'POST',
                headers: { 'X-CSRF-Token': csrfToken },
                body: JSON.stringify({ acao: action, id_mensagem: messageId }),
            });
            messageViewVersion += 1;
            await loadMessages(true);
            try {
                await refreshContacts();
            } catch (error) {
                contactsStatus.textContent = error.message;
            }
        } catch (error) {
            sendStatus.textContent = error.message;
            button.disabled = false;
        }
    };

    profileTrigger?.addEventListener('click', async () => {
        if (!activeContact || activeContact.tipo === 'sistema' || !profileDialog) return;

        const requestedContact = { ...activeContact };
        const requestedVersion = messageViewVersion;
        profileTrigger.disabled = true;
        sendStatus.textContent = '';
        try {
            const data = await request({
                acao: 'perfil',
                tipo: requestedContact.tipo,
                id: requestedContact.id,
            });
            if (
                activeContact?.tipo !== requestedContact.tipo
                || activeContact?.id !== requestedContact.id
            ) {
                return;
            }

            profileReportTarget = data.perfil;
            profileNameElement.textContent = data.perfil.nome;
            profileTypeElement.textContent = translate(data.perfil.rotulo);
            profileAvatarElement.textContent = initials(data.perfil.nome);
            profileFeedbackToast.hidden = true;
            profileReportForm.reset();
            profileReportForm.hidden = true;
            profileReportButton.hidden = false;
            profileSubmitButton.disabled = false;
            profileDialog.showModal();
        } catch (error) {
            sendStatus.textContent = error.message;
        } finally {
            if (messageViewVersion === requestedVersion) {
                profileTrigger.disabled = !activeContact || activeContact.tipo === 'sistema';
            }
        }
    });

    profileReportButton?.addEventListener('click', () => {
        profileReportButton.hidden = true;
        profileReportForm.hidden = false;
        profileFeedbackToast.hidden = true;
        profileReportReason.focus();
    });

    profileDialog?.querySelector('[data-chat-profile-report-cancel]')?.addEventListener('click', () => {
        profileReportForm.hidden = true;
        profileReportButton.hidden = false;
        profileFeedbackToast.hidden = true;
    });

    profileDialog?.querySelector('[data-chat-profile-close]')?.addEventListener('click', () => {
        profileDialog.close();
    });

    profileReportForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!profileReportTarget) return;

        profileSubmitButton.disabled = true;
        profileFeedbackToast.hidden = true;
        try {
            const data = await request({}, {
                method: 'POST',
                headers: { 'X-CSRF-Token': csrfToken },
                body: JSON.stringify({
                    acao: 'denunciar_perfil',
                    tipo: profileReportTarget.tipo,
                    id: profileReportTarget.id,
                    motivo: profileReportReason.value,
                    detalhes: profileReportDetails.value,
                }),
            });
            const warnings = Array.isArray(data.avisos) ? data.avisos.map(translate) : [];
            window.DevINShowToast?.(
                profileFeedbackToast,
                [translate(data.mensagem || 'Denúncia enviada para análise da equipe DevIN.'), ...warnings].join(' '),
                warnings.length > 0 ? 'warning' : 'success'
            );
            profileReportForm.hidden = true;
            profileReportButton.hidden = true;
        } catch (error) {
            window.DevINShowToast?.(profileFeedbackToast, error.message, 'error');
        } finally {
            profileSubmitButton.disabled = false;
        }
    });

    const openConversation = async (contact) => {
        activeContact = { ...contact };
        const requestedContact = activeContact;
        app.classList.add('chat-conversation-open');
        messageViewVersion += 1;
        lastMessageId = 0;
        titleElement.textContent = contact.nome;
        subtitleElement.textContent = `${translate(contact.rotulo)} · ${translate('Atualização automática')}`;
        renderAvatar(conversationAvatar, contact);
        profileTrigger.disabled = true;
        messageInput.disabled = false;
        sendButton.disabled = false;
        deleteConversationButton.disabled = true;
        sendStatus.textContent = '';
        messageInput.focus();
        renderContacts();
        messagesElement.replaceChildren();
        const loading = document.createElement('p');
        loading.className = 'chat-empty';
        loading.textContent = translate('Carregando conversa...');
        messagesElement.appendChild(loading);
        if (requestedContact.tipo === 'sistema' && requestedContact.id === 0) {
            messageInput.disabled = true;
            sendButton.disabled = true;
            messageInput.placeholder = translate('Notificações da plataforma não aceitam respostas.');
            await loadMessages(true);
            try {
                await refreshContacts();
            } catch (error) {
                contactsStatus.textContent = error.message;
            }
            return true;
        }

        messageInput.placeholder = defaultMessagePlaceholder;
        try {
            const data = await request({}, {
                method: 'POST',
                headers: { 'X-CSRF-Token': csrfToken },
                body: JSON.stringify({
                    acao: 'iniciar_conversa',
                    tipo: requestedContact.tipo,
                    id: requestedContact.id,
                }),
            });
            if (activeContact !== requestedContact) return false;
            activeContact = data.contato;
            titleElement.textContent = activeContact.nome;
            subtitleElement.textContent = `${translate(activeContact.rotulo)} · ${translate('Atualização automática')}`;
            renderAvatar(conversationAvatar, activeContact);
            profileTrigger.disabled = activeContact.tipo === 'sistema';
        } catch (error) {
            if (activeContact === requestedContact) sendStatus.textContent = error.message;
            return false;
        }
        await loadMessages(true);
        try {
            await refreshContacts();
        } catch (error) {
            contactsStatus.textContent = error.message;
        }
        return true;
    };

    backToContactsButton?.addEventListener('click', () => {
        app.classList.remove('chat-conversation-open');
        messageInput.blur();
        contactsElement.querySelector('[aria-pressed="true"]')?.focus();
    });

    conversationMenu?.addEventListener('toggle', () => {
        conversationMenuToggle?.setAttribute('aria-expanded', String(conversationMenu.open));
    });

    deleteConversationButton.addEventListener('click', async () => {
        if (!activeContact || !window.confirm(translate('Excluir esta conversa só para você?'))) return;

        conversationMenu.open = false;
        deleteConversationButton.disabled = true;
        sendStatus.textContent = '';
        try {
            await request({}, {
                method: 'POST',
                headers: { 'X-CSRF-Token': csrfToken },
                body: JSON.stringify({
                    acao: 'excluir_conversa',
                    tipo: activeContact.tipo,
                    id: activeContact.id,
                }),
            });
            messageViewVersion += 1;
            lastMessageId = 0;
            await loadMessages(true);
            try {
                await refreshContacts();
            } catch (error) {
                contactsStatus.textContent = error.message;
            }
        } catch (error) {
            sendStatus.textContent = error.message;
            deleteConversationButton.disabled = !activeContact;
        }
    });

    searchInput.addEventListener('input', searchContacts);
    document.addEventListener('click', (event) => {
        if (!(event.target instanceof Element)) return;
        if (!event.target.closest('.chat-message-menu')) {
            messagesElement.querySelectorAll('.chat-message-menu[open]').forEach((menu) => {
                menu.open = false;
            });
        }
        if (conversationMenu?.open && !conversationMenu.contains(event.target)) {
            conversationMenu.open = false;
        }
    });

    const resizeMessageInput = () => {
        messageInput.style.height = 'auto';
        const maxHeight = Number.parseFloat(window.getComputedStyle(messageInput).maxHeight);
        const height = Number.isFinite(maxHeight)
            ? Math.min(messageInput.scrollHeight, maxHeight)
            : messageInput.scrollHeight;
        messageInput.style.height = `${height}px`;
        messageInput.style.overflowY = messageInput.scrollHeight > height ? 'auto' : 'hidden';
    };

    messageInput.addEventListener('input', resizeMessageInput);
    messageInput.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter' || event.shiftKey || event.isComposing || event.keyCode === 229) return;
        event.preventDefault();
        if (activeContact && !sendButton.disabled) form.requestSubmit();
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!activeContact || !messageInput.value.trim()) return;

        sendButton.disabled = true;
        sendButton.textContent = translate('Enviando...');
        sendStatus.textContent = '';
        try {
            await request({}, {
                method: 'POST',
                headers: { 'X-CSRF-Token': csrfToken },
                body: JSON.stringify({
                    acao: 'enviar',
                    tipo: activeContact.tipo,
                    id: activeContact.id,
                    mensagem: messageInput.value,
                }),
            });
            messageInput.value = '';
            resizeMessageInput();
            await loadMessages(false);
            try {
                await refreshContacts();
            } catch (error) {
                contactsStatus.textContent = error.message;
            }
        } catch (error) {
            sendStatus.textContent = error.message;
        } finally {
            sendButton.disabled = !activeContact;
            sendButton.textContent = translate('Enviar');
            messageInput.focus();
        }
    });

    const initializeChat = async () => {
        try {
            await refreshContacts();
            const parameters = new URLSearchParams(window.location.search);
            const tipo = parameters.get('contato_tipo');
            const id = Number(parameters.get('contato_id'));
            if (!['pessoa', 'empresa'].includes(tipo) || !Number.isInteger(id) || id < 1) return;

            const opened = await openConversation({
                tipo,
                id,
                nome: translate('Carregando conversa...'),
                rotulo: tipo === 'pessoa' ? 'Pessoa' : 'Empresa',
                nao_lidas: 0,
                ultima_mensagem_id: 0,
            });
            if (!opened) return;
            parameters.delete('contato_tipo');
            parameters.delete('contato_id');
            const cleanUrl = `${window.location.pathname}?${parameters.toString()}${window.location.hash}`;
            window.history.replaceState({}, '', cleanUrl);
        } catch (error) {
            contactsStatus.textContent = error.message;
        }
    };
    void initializeChat();

    window.setInterval(() => {
        if (document.visibilityState !== 'visible') return;
        loadMessages()
            .then(refreshContacts)
            .catch((error) => {
                contactsStatus.textContent = error.message;
            });
    }, 4000);
})();
