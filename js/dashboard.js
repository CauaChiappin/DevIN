(() => {
    'use strict';

    const dashboardShell = document.querySelector('.dashboard-shell');
    const menuToggle = document.querySelector('[data-toggle-menu]');
    const detailText = document.querySelector('#detailText');
    const detailTitle = document.querySelector('#detailTitle');
    const settingsModal = document.querySelector('#settingsModal');
    const profileModal = document.querySelector('#profileModal');
    const rotatingWord = document.querySelector('[data-rotating-word]');
    const interfaceLocale = document.documentElement.lang === 'en' ? 'en-US' : document.documentElement.lang === 'es' ? 'es-ES' : 'pt-BR';
    const translate = (value) => window.DevINTranslate?.(value) ?? value;

    const messagesLink = document.querySelector('[data-messages-api]');
    const unreadBadge = messagesLink?.querySelector('[data-message-unread-badge]');
    const refreshMessageCount = async () => {
        if (!messagesLink || !unreadBadge) return;
        const url = new URL(messagesLink.dataset.messagesApi, window.location.href);
        url.searchParams.set('acao', 'nao_lidas');
        const response = await fetch(url, { credentials: 'same-origin' });
        const data = await response.json();
        if (!response.ok || data.sucesso !== true) {
            throw new Error(translate(data.mensagem || 'Não foi possível atualizar as mensagens não lidas.'));
        }

        unreadBadge.hidden = data.nao_lidas === 0;
        unreadBadge.textContent = String(data.nao_lidas);
        unreadBadge.setAttribute('aria-label', translate(`${data.nao_lidas} mensagens não lidas`));
    };
    if (messagesLink) {
        refreshMessageCount().catch((error) => console.error(error));
        window.setInterval(() => {
            if (document.visibilityState === 'visible') {
                refreshMessageCount().catch((error) => console.error(error));
            }
        }, 15000);
    }

    // Sidebar: preferência fica salva somente no navegador deste usuário.
    if (dashboardShell && menuToggle) {
        const fechado = localStorage.getItem('devin-menu-fechado') === 'true';
        const mobileMenu = window.DevINDashboardMenu?.init({
            shell: dashboardShell,
            toggle: menuToggle,
            windowRef: window,
        });
        const isMobile = () => mobileMenu?.isMobile?.() === true;
        const syncLayout = () => {
            if (isMobile()) {
                dashboardShell.classList.remove('menu-fechado');
                menuToggle.setAttribute('aria-expanded', String(dashboardShell.classList.contains('mobile-menu-open')));
                return;
            }

            dashboardShell.classList.remove('mobile-menu-open');
            dashboardShell.classList.toggle('menu-fechado', fechado);
            menuToggle.setAttribute('aria-expanded', String(!fechado));
        };

        syncLayout();
        window.addEventListener('resize', syncLayout);

        menuToggle.addEventListener('click', () => {
            if (isMobile()) {
                document.querySelector('.perfil-dropdown')?.removeAttribute('open');
                return;
            }

            const novoEstado = dashboardShell.classList.toggle('menu-fechado');
            menuToggle.setAttribute('aria-expanded', String(!novoEstado));
            localStorage.setItem('devin-menu-fechado', String(novoEstado));
        });
    }

    // Cards: impede que botões/formulários internos sejam tratados como seleção do card.
    const detailPanel = document.querySelector('.profile-detail-panel');
    const detailCards = [...document.querySelectorAll('.item-card[data-detail]')];
    let selectedActionTarget = null;

    const splitItems = (value, fallback) => {
        const items = String(value || '').split('|').map((item) => item.trim()).filter(Boolean);
        return items.length ? items : fallback;
    };

    const getInitials = (value) => value.split(/\s+/).filter(Boolean).slice(0, 2)
        .map((word) => word[0]).join('').toUpperCase() || 'DI';

    const renderList = (element, items, timeline = false) => {
        if (!element) return;
        element.replaceChildren();
        items.forEach((item) => {
            const row = document.createElement('li');
            if (timeline) {
                const [title, description] = item.split('::').map((part) => part.trim());
                const heading = document.createElement('strong');
                heading.textContent = translate(title || 'DevIN');
                row.appendChild(heading);
                if (description) row.append(document.createTextNode(translate(description)));
            } else {
                row.textContent = item;
            }
            element.appendChild(row);
        });
    };

    const renderDetail = (card) => {
        const content = detailPanel?.querySelector('[data-detail-content]');
        if (!detailPanel || !content) return;

        const title = card.querySelector('h2')?.textContent?.trim() || translate('Detalhes do registro');
        const subtitle = card.querySelector('p')?.textContent?.trim() || translate('Informacoes da plataforma');
        const name = card.dataset.detailName || card.dataset.jobTitle || title;
        const role = card.dataset.detailRole || subtitle;
        const summary = card.dataset.detailSummary || card.dataset.detail || subtitle;
        const tags = splitItems(card.dataset.detailTags, ['DevIN', translate('Informacao disponivel')]).map(translate);
        const experience = splitItems(card.dataset.detailExperience, [`${translate('Registro selecionado')}::${role}`]);

        detailPanel.querySelector('[data-detail-placeholder]')?.setAttribute('hidden', '');
        content.hidden = false;
        const avatar = detailPanel.querySelector('[data-detail-avatar]');
        const nameElement = detailPanel.querySelector('[data-detail-name]');
        const roleElement = detailPanel.querySelector('[data-detail-role]');
        const metaElement = detailPanel.querySelector('[data-detail-meta]');
        const summaryElement = detailPanel.querySelector('[data-detail-summary]');

        if (avatar) avatar.textContent = getInitials(name);
        if (nameElement) nameElement.textContent = name;
        if (roleElement) roleElement.textContent = role;
        if (metaElement) metaElement.textContent = card.dataset.detailMeta || translate('Disponivel na plataforma');
        if (summaryElement) summaryElement.textContent = summary;
        renderList(detailPanel.querySelector('[data-detail-tags]'), tags);
        renderList(detailPanel.querySelector('[data-detail-experience]'), experience, true);

        selectedActionTarget = card.querySelector('[data-detail-action-target]');
        const action = detailPanel.querySelector('[data-detail-action]');
        if (action) {
            action.hidden = !selectedActionTarget;
            if (selectedActionTarget) action.textContent = translate(card.dataset.detailActionLabel || selectedActionTarget.textContent.trim());
        }

        const chatAction = detailPanel.querySelector('[data-start-chat]');
        if (chatAction) {
            chatAction.hidden = !card.dataset.chatType || !card.dataset.chatId;
            chatAction.dataset.chatType = card.dataset.chatType || '';
            chatAction.dataset.chatId = card.dataset.chatId || '';
        }
    };

    detailPanel?.querySelector('[data-detail-action]')?.addEventListener('click', () => selectedActionTarget?.click());
    document.addEventListener('click', (event) => {
        if (!(event.target instanceof Element)) return;
        const button = event.target.closest('[data-start-chat]:not([hidden])');
        if (!button || !button.dataset.chatType || !button.dataset.chatId) return;
        const destination = new URL(window.location.href);
        destination.searchParams.set('pagina', 'mensagens');
        destination.searchParams.set('contato_tipo', button.dataset.chatType);
        destination.searchParams.set('contato_id', button.dataset.chatId);
        window.location.assign(destination);
    });

    const clearDetail = () => {
        detailCards.forEach((card) => card.classList.remove('selecionado'));
        selectedActionTarget = null;

        if (!detailPanel) return;

        detailPanel.querySelector('[data-detail-content]')?.setAttribute('hidden', '');
        detailPanel.querySelector('[data-detail-placeholder]')?.removeAttribute('hidden');
        detailPanel.querySelector('[data-detail-action]')?.setAttribute('hidden', '');
        detailPanel.querySelector('[data-start-chat]')?.setAttribute('hidden', '');
    };

    document.querySelectorAll('.item-card[data-detail]').forEach((card) => {
        card.addEventListener('click', (event) => {
            if (event.target.closest('button, a, form, summary, input, textarea, select')) return;

            document.querySelectorAll('.item-card[data-detail]').forEach((item) => {
                item.classList.remove('selecionado');
            });

            card.classList.add('selecionado');
            renderDetail(card);

            if (detailText) detailText.textContent = card.dataset.detail || 'Sem detalhes disponíveis.';
            if (detailTitle && card.dataset.jobTitle) detailTitle.textContent = card.dataset.jobTitle;
        });
    });

    // Confirmação de saída para pessoa, empresa e administrador.
    document.querySelector('.lista-area')?.addEventListener('click', (event) => {
        if (event.target.closest('.item-card[data-detail], button, a, form, summary, input, textarea, select, label')) return;
        clearDetail();
    });

    document.querySelectorAll('[data-confirm-logout]').forEach((link) => {
        link.addEventListener('click', (event) => {
            const message = link.dataset.confirmLogout || translate('Tem certeza que deseja sair da sua conta?');
            if (!window.confirm(message)) event.preventDefault();
        });
    });

    // Confirmação de exclusão da conta.
    document.querySelectorAll('[data-delete-account]').forEach((button) => {
        button.addEventListener('click', (event) => {
            if (!window.confirm(translate('Tem certeza? Esta ação exclui sua conta permanentemente.'))) {
                event.preventDefault();
            }
        });
    });

    // Fecha dialogs sem depender de IDs específicos.
    document.querySelectorAll('[data-close-modal]').forEach((button) => {
        button.addEventListener('click', () => button.closest('dialog')?.close());
    });

    profileModal?.addEventListener('click', (event) => {
        if (event.target === profileModal) profileModal.close();
    });

    document.querySelectorAll('[data-open-settings]').forEach((button) => {
        button.addEventListener('click', () => {
            document.querySelector('.perfil-dropdown')?.removeAttribute('open');
            if (settingsModal?.showModal) settingsModal.showModal();
        });
    });

    document.querySelectorAll('[data-open-profile]').forEach((button) => {
        button.addEventListener('click', () => {
            document.querySelector('.perfil-dropdown')?.removeAttribute('open');
            if (profileModal?.showModal) profileModal.showModal();
        });
    });

    document.querySelectorAll('.perfil-dropdown').forEach((dropdown) => {
        document.addEventListener('click', (event) => {
            if (event.target instanceof Node && !dropdown.contains(event.target)) {
                dropdown.removeAttribute('open');
            }
        });
    });

    // Pré-visualização segura da foto.
    document.querySelectorAll(".profile-photo input[type='file']").forEach((input) => {
        input.addEventListener('change', (event) => {
            const photo = event.currentTarget.files?.[0];
            if (!photo) return;

            const allowed = ['image/jpeg', 'image/png', 'image/webp'];
            if (!allowed.includes(photo.type) || photo.size > 5 * 1024 * 1024) {
                event.currentTarget.value = '';
                window.alert(translate('Selecione uma imagem JPG, PNG ou WEBP de até 5 MB.'));
                return;
            }

            const previewUrl = URL.createObjectURL(photo);
            document.querySelectorAll('.current-user-avatar').forEach((preview) => {
                preview.replaceChildren();
                const image = document.createElement('img');
                image.src = previewUrl;
                image.alt = translate('Prévia da foto de perfil');
                preview.appendChild(image);
            });
        });
    });

    // Loading para qualquer formulário que realmente será enviado.
    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', () => {
            if (form.matches('[data-chat-form]')) return;
            const submitter = form.querySelector('button[type="submit"]:focus, button[type="submit"]:last-of-type');
            if (!submitter || submitter.dataset.loading === 'true') return;
            submitter.dataset.loading = 'true';
            submitter.disabled = true;
            submitter.dataset.originalText = submitter.textContent;
            submitter.textContent = translate('Processando...');
        });
    });

    // Busca local sem enviar uma nova requisição a cada tecla.
    const searchInput = document.querySelector('.busca input[type="search"]');
    const filterButton = document.querySelector('[data-toggle-filters]');
    const filterMenu = document.querySelector('[data-filter-menu]');
    const filterSelect = document.querySelector('[data-card-filter]');
    const searchEmptyState = detailCards.length
        ? document.createElement('p')
        : null;
    if (searchEmptyState) {
        searchEmptyState.className = 'empty-state search-empty-state';
        searchEmptyState.setAttribute('role', 'status');
        searchEmptyState.setAttribute('aria-live', 'polite');
        searchEmptyState.textContent = translate('Nenhum resultado encontrado.');
        searchEmptyState.hidden = true;
        detailCards[0].before(searchEmptyState);
    }
    const normalizeSearch = (value) => String(value || '').trim().toLocaleLowerCase(interfaceLocale);
    const cardTags = (card) => String(card.dataset.detailTags || '')
        .split('|').map((tag) => tag.trim()).filter(Boolean);
    const updateCardVisibility = () => {
        const term = normalizeSearch(searchInput?.value);
        const activeFilter = filterSelect?.value || '';

        detailCards.forEach((card) => {
            const searchableContent = [
                card.textContent,
                card.dataset.detailName,
                card.dataset.detailSummary,
                card.dataset.detailRole,
                card.dataset.detailTags,
                card.dataset.detailExperience,
            ].join(' ').toLocaleLowerCase(interfaceLocale);
            const matchesSearch = term === '' || searchableContent.includes(term);
            const matchesFilter = activeFilter === '' || cardTags(card).includes(activeFilter);
            card.hidden = !matchesSearch || !matchesFilter;
        });

        const hasActiveSearch = term !== '' || activeFilter !== '';
        const hasVisibleCards = detailCards.some((card) => !card.hidden);
        if (searchEmptyState) searchEmptyState.hidden = !hasActiveSearch || hasVisibleCards;
    };

    if (filterSelect) {
        const tags = [...new Set(detailCards.flatMap(cardTags))]
            .sort((first, second) => first.localeCompare(second, 'pt-BR'));
        const options = ['', ...tags].map((tag) => {
            const option = document.createElement('option');
            option.value = tag;
            option.textContent = translate(tag || 'Todos');
            return option;
        });
        filterSelect.replaceChildren(...options);
        filterSelect.addEventListener('change', updateCardVisibility);
    }

    filterButton?.addEventListener('click', () => {
        const isOpen = filterMenu?.hidden !== false;
        if (filterMenu) filterMenu.hidden = !isOpen;
        filterButton.setAttribute('aria-expanded', String(isOpen));
    });

    searchInput?.addEventListener('input', updateCardVisibility);
    updateCardVisibility();

    document.querySelector('.busca')?.addEventListener('submit', (event) => event.preventDefault());

    // Palavra animada da página institucional.
    if (rotatingWord) {
        const words = document.documentElement.lang === 'en'
            ? ['future', 'world', 'universe', 'progress', 'market', 'tomorrow']
            : document.documentElement.lang === 'es'
                ? ['futuro', 'mundo', 'universo', 'progreso', 'mercado', 'mañana']
                : ['futuro', 'mundo', 'universo', 'avanço', 'mercado', 'amanhã'];
        let currentWord = 0;
        let changing = false;

        const showWord = (word, entering = false) => {
            rotatingWord.replaceChildren();
            [...word].forEach((character, index) => {
                const letter = document.createElement('span');
                const distance = index - (word.length - 1) / 2;
                letter.className = 'palavra-letra';
                letter.textContent = character;
                letter.style.setProperty('--rotacao', `${distance * 12}deg`);
                letter.style.setProperty('--queda', `${28 + (index % 3) * 7}px`);
                letter.style.setProperty('--atraso', `${index * 38}ms`);
                if (entering) letter.classList.add('entrando');
                rotatingWord.appendChild(letter);
            });

            if (entering) {
                requestAnimationFrame(() => requestAnimationFrame(() => {
                    rotatingWord.querySelectorAll('.palavra-letra').forEach((letter) => letter.classList.remove('entrando'));
                }));
            }
        };

        showWord(words[currentWord]);

        window.setInterval(() => {
            if (changing) return;
            changing = true;
            rotatingWord.querySelectorAll('.palavra-letra').forEach((letter) => letter.classList.add('saindo'));
            window.setTimeout(() => {
                currentWord = (currentWord + 1) % words.length;
                showWord(words[currentWord], true);
                changing = false;
            }, 800);
        }, 3000);
    }

    const url = new URL(window.location.href);
    if (url.searchParams.get('configuracoes') === '1' && settingsModal?.showModal) {
        settingsModal.showModal();
        url.searchParams.delete('configuracoes');
        window.history.replaceState(null, '', `${url.pathname}${url.search}${url.hash}`);
    }

    if (new URLSearchParams(window.location.search).get('perfil') === 'meu' && profileModal?.showModal) {
        profileModal.showModal();
    }
})();
