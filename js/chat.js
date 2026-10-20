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
    const backToContactsButton = app.querySelector('[data-chat-back]');
    const deleteConversationButton = app.querySelector('[data-chat-delete-conversation]');
    const messagesElement = app.querySelector('[data-chat-messages]');
    const form = app.querySelector('[data-chat-form]');
    const messageInput = app.querySelector('[data-chat-input]');
    const sendButton = app.querySelector('[data-chat-send]');
    const sendStatus = app.querySelector('[data-chat-send-status]');

    let activeContact = null;
    let lastMessageId = 0;
    let messageViewVersion = 0;
    let requestInProgress = false;
    let contactsRequestInProgress = false;
    let contacts = [];
    let contactsLoaded = false;
    let unreadTotal = 0;
    let notificationTimeout = 0;

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

    const renderContacts = () => {
        contactsElement.replaceChildren();
        const filter = searchInput.value.trim().toLocaleLowerCase(locale);
        const visibleContacts = contacts.filter((contact) =>
            `${contact.nome} ${contact.rotulo}`.toLocaleLowerCase(locale).includes(filter));

        if (visibleContacts.length === 0) {
            contactsStatus.textContent = translate(contacts.length ? 'Nenhum contato encontrado.' : 'Ainda não há outros usuários cadastrados.');
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
            avatar.textContent = initials(contact.nome);

            const copy = document.createElement('span');
            copy.className = 'chat-contact-copy';
            const name = document.createElement('strong');
            name.textContent = contact.nome;
            const type = document.createElement('small');
            type.textContent = contact.rotulo;
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
        time.textContent = Number.isNaN(date.getTime())
            ? ''
            : date.toLocaleTimeString(locale, { hour: '2-digit', minute: '2-digit' });
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
                    void deleteMessage(message.id, action, button);
                });
                menuItems.appendChild(button);
            };

            addAction(translate('Excluir para mim'), 'excluir_mensagem');
            if (message.minha) addAction(translate('Excluir para todos'), 'excluir_para_todos');
            menu.append(menuButton, menuItems);
            bubble.appendChild(menu);
        }

        if (existingBubble) {
            existingBubble.replaceWith(bubble);
        } else {
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

    const openConversation = async (contact) => {
        activeContact = contact;
        app.classList.add('chat-conversation-open');
        messageViewVersion += 1;
        lastMessageId = 0;
        titleElement.textContent = contact.nome;
        subtitleElement.textContent = `${translate(contact.rotulo)} · ${translate('Atualização automática')}`;
        messageInput.disabled = false;
        sendButton.disabled = false;
        deleteConversationButton.disabled = true;
        sendStatus.textContent = '';
        renderContacts();
        messagesElement.replaceChildren();
        const loading = document.createElement('p');
        loading.className = 'chat-empty';
        loading.textContent = translate('Carregando conversa...');
        messagesElement.appendChild(loading);
        await loadMessages(true);
        try {
            await refreshContacts();
        } catch (error) {
            contactsStatus.textContent = error.message;
        }
        messageInput.focus();
    };

    backToContactsButton?.addEventListener('click', () => {
        app.classList.remove('chat-conversation-open');
        messageInput.blur();
        contactsElement.querySelector('[aria-pressed="true"]')?.focus();
    });

    deleteConversationButton.addEventListener('click', async () => {
        if (!activeContact || !window.confirm(translate('Excluir esta conversa só para você?'))) return;

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

    searchInput.addEventListener('input', renderContacts);
    document.addEventListener('click', (event) => {
        if (event.target instanceof Element && event.target.closest('.chat-message-menu')) return;
        messagesElement.querySelectorAll('.chat-message-menu[open]').forEach((menu) => {
            menu.open = false;
        });
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
        if (event.key !== 'Enter' || !event.ctrlKey || event.isComposing) return;
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

    refreshContacts()
        .catch((error) => {
            contactsStatus.textContent = error.message;
        });

    window.setInterval(() => {
        if (document.visibilityState !== 'visible') return;
        loadMessages()
            .then(refreshContacts)
            .catch((error) => {
                contactsStatus.textContent = error.message;
            });
    }, 4000);
})();
