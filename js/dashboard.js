(() => {
    'use strict';

    const dashboardShell = document.querySelector('.dashboard-shell');
    const menuToggle = document.querySelector('[data-toggle-menu]');
    const detailText = document.querySelector('#detailText');
    const detailTitle = document.querySelector('#detailTitle');
    const settingsModal = document.querySelector('#settingsModal');
    const profileModal = document.querySelector('#profileModal');
    const jobReportDialog = document.querySelector('[data-job-report-dialog]');
    const jobReportForm = jobReportDialog?.querySelector('[data-job-report-form]');
    const jobReportButton = document.querySelector('[data-report-job]');
    const jobReportStatus = jobReportDialog?.querySelector('[data-job-report-status]');
    const jobReportSubmit = jobReportDialog?.querySelector('[data-job-report-submit]');
    const reportConfirmDialog = document.querySelector('[data-report-confirm-dialog]');
    let pendingReportExclusionForm = null;
    const rotatingWord = document.querySelector('[data-rotating-word]');
    const interfaceLocale = document.documentElement.lang === 'en' ? 'en-US' : document.documentElement.lang === 'es' ? 'es-ES' : 'pt-BR';
    const translate = (value) => window.DevINTranslate?.(value) ?? value;

    document.querySelectorAll('[data-job-closure-mode]').forEach((modeSelect) => {
        const form = modeSelect.closest('form');
        const dateInput = form?.querySelector('[data-job-closure-date]');
        const approvedLimit = form?.querySelector('[data-job-approved-limit]');
        if (!form || !dateInput || !approvedLimit) return;

        const updateClosureFields = () => {
            const requiresDate = ['prazo', 'qualquer'].includes(modeSelect.value);
            dateInput.required = requiresDate;
            const dateLabel = dateInput.closest('label');
            if (dateLabel) dateLabel.hidden = !requiresDate;
            approvedLimit.min = ['limite', 'qualquer'].includes(modeSelect.value) ? '1' : '0';
        };

        modeSelect.addEventListener('change', updateClosureFields);
        updateClosureFields();
    });

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

    document.querySelectorAll('[data-report-exclusion-form]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.confirmed === 'true') {
                delete form.dataset.confirmed;
                return;
            }

            event.preventDefault();
            pendingReportExclusionForm = form;
            reportConfirmDialog?.showModal();
        });
    });

    document.querySelector('[data-cancel-report-exclusion]')?.addEventListener('click', () => {
        reportConfirmDialog?.close();
        pendingReportExclusionForm = null;
    });

    document.querySelector('[data-confirm-report-exclusion]')?.addEventListener('click', () => {
        if (!pendingReportExclusionForm) return;
        const form = pendingReportExclusionForm;
        pendingReportExclusionForm = null;
        form.dataset.confirmed = 'true';
        reportConfirmDialog?.close();
        form.requestSubmit();
    });

    reportConfirmDialog?.addEventListener('click', (event) => {
        if (event.target === reportConfirmDialog) {
            reportConfirmDialog.close();
            pendingReportExclusionForm = null;
        }
    });
    reportConfirmDialog?.addEventListener('close', () => {
        pendingReportExclusionForm = null;
    });

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

    const jobReportSummary = (card) => {
        if (!card.hasAttribute('data-job-report-card')) return '';
        return [
            `${translate('Descrição')}: ${card.dataset.reportJobDescription || translate('Sem descrição informada.')}`,
            `${translate('Salário mensal')}: ${card.dataset.reportJobSalary || translate('A combinar')}`,
            `${translate('Máximo de aprovados')}: ${card.dataset.reportJobLimit || translate('Sem limite')}`
        ].join('\n');
    };

    const renderList = (element, items, timeline = false) => {
        /* Reconstrói listas a partir dos atributos do card. Usa textContent e nós de texto,
         * não innerHTML, para que nomes e descrições sejam exibidos como texto literal. */
        if (!element) return;
        element.replaceChildren();
        items.forEach((item) => {
            const row = document.createElement('li');
            if (timeline) {
                const [title, description] = typeof item === 'string'
                    ? item.split('::').map((part) => part.trim())
                    : [item.title, item.description];
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
        /* O card concentra resumo, etiquetas e experiência em data-*. Ao selecioná-lo, estes valores
         * preenchem o painel lateral sem uma nova consulta ao servidor. */
        const content = detailPanel?.querySelector('[data-detail-content]');
        if (!detailPanel || !content) return;

        const title = card.querySelector('h2')?.textContent?.trim() || translate('Detalhes do registro');
        const subtitle = card.querySelector('p')?.textContent?.trim() || translate('Informacoes da plataforma');
        const name = card.dataset.detailName || card.dataset.jobTitle || title;
        const jobReport = card.hasAttribute('data-job-report-card');
        const role = jobReport
            ? `${translate('Empresa:')} ${card.dataset.reportCompany || ''}`
            : card.dataset.detailRole || subtitle;
        const summary = jobReportSummary(card) || card.dataset.detailSummary || card.dataset.detail || subtitle;
        const tags = splitItems(card.dataset.detailTags, ['DevIN', translate('Informacao disponivel')]).map(translate);
        const experience = splitItems(card.dataset.detailExperience, [`${translate('Registro selecionado')}::${role}`]);

        detailPanel.querySelector('[data-detail-placeholder]')?.setAttribute('hidden', '');
        content.hidden = false;
        const avatar = detailPanel.querySelector('[data-detail-avatar]');
        const nameElement = detailPanel.querySelector('[data-detail-name]');
        const roleElement = detailPanel.querySelector('[data-detail-role]');
        const metaElement = detailPanel.querySelector('[data-detail-meta]');
        const summaryElement = detailPanel.querySelector('[data-detail-summary]');
        const experienceList = detailPanel.querySelector('[data-detail-experience]');
        const applicationSummary = card.dataset.applicationSummary === 'true';
        const reporterPrefix = translate('Denúncia enviada por');

        if (avatar) avatar.textContent = getInitials(name);
        if (nameElement) nameElement.textContent = name;
        if (roleElement) roleElement.textContent = role;
        if (metaElement) {
            metaElement.textContent = jobReport
                ? `${reporterPrefix} ${card.dataset.reportReporter || ''}`
                : card.dataset.detailMeta || translate('Disponivel na plataforma');
        }
        if (summaryElement) summaryElement.textContent = summary;
        const summaryLabel = detailPanel.querySelector('[data-detail-summary-label]');
        if (summaryLabel) summaryLabel.textContent = translate(jobReport ? 'Detalhes da vaga denunciada' : 'Sobre');
        detailPanel.querySelector('[data-detail-tags-section]')?.toggleAttribute('hidden', jobReport);
        renderList(detailPanel.querySelector('[data-detail-tags]'), tags);
        renderList(experienceList, jobReport
            ? [
                {
                    title: 'Mensagem da denúncia',
                    description: `${translate('Motivo:')} ${translate(card.dataset.reportReason || '')}\n${card.dataset.reportMessage || ''}`
                },
                { title: 'Análise', description: translate(card.dataset.reportAnalysis || '') }
            ]
            : experience, true);
        detailPanel.classList.toggle('has-application-summary', applicationSummary);
        experienceList?.classList.toggle('application-summary', applicationSummary);
        experienceList?.closest('.detail-section')?.classList.toggle('application-summary-section', applicationSummary);

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
        if (jobReportButton) {
            jobReportButton.hidden = !card.dataset.reportJobId;
            jobReportButton.disabled = card.dataset.reportSubmitted === 'true';
            jobReportButton.dataset.jobId = card.dataset.reportJobId || '';
            jobReportButton.textContent = translate(card.dataset.reportSubmitted === 'true'
                ? 'Denúncia enviada'
                : 'Denunciar esta vaga');
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
            // Cliques em controles internos executam sua ação própria e não trocam a seleção do card.
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

    const deleteForms = new Set([...document.querySelectorAll('[data-delete-account]')]
        .map((button) => button.closest('form'))
        .filter(Boolean));
    deleteForms.forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.deleteConfirmed === 'true') return;
            if (!window.confirm(translate('Tem certeza? Esta ação exclui sua conta permanentemente.'))) {
                event.preventDefault();
                return;
            }

            event.preventDefault();
            form.closest('dialog')?.close();
            document.body.classList.add('account-deleting');
            const delay = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 150 : 380;
            window.setTimeout(() => HTMLFormElement.prototype.submit.call(form), delay);
        });
    });

    // Fecha dialogs sem depender de IDs específicos.
    document.querySelectorAll('[data-close-modal]').forEach((button) => {
        button.addEventListener('click', () => button.closest('dialog')?.close());
    });

    document.querySelector('[data-close-job-report]')?.addEventListener('click', () => jobReportDialog?.close());
    jobReportDialog?.addEventListener('click', (event) => {
        if (event.target === jobReportDialog) jobReportDialog.close();
    });
    jobReportButton?.addEventListener('click', () => {
        const reportId = jobReportButton.dataset.jobId;
        if (!reportId || !jobReportForm || !jobReportDialog) return;
        jobReportForm.reset();
        jobReportForm.querySelectorAll('select, textarea').forEach((field) => { field.disabled = false; });
        jobReportForm.querySelector('[data-job-report-id]').value = reportId;
        jobReportStatus.hidden = true;
        jobReportStatus.classList.remove('form-success');
        jobReportStatus.textContent = '';
        jobReportSubmit.disabled = false;
        jobReportSubmit.textContent = translate('Enviar denúncia');
        jobReportDialog.showModal();
    });

    jobReportForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!jobReportSubmit || !jobReportStatus) return;

        jobReportSubmit.disabled = true;
        jobReportSubmit.textContent = translate('Enviando...');
        jobReportStatus.hidden = true;
        jobReportStatus.classList.remove('form-success');
        try {
            const csrfToken = document.querySelector('input[name="csrf_token"]')?.value || '';
            const response = await fetch(new URL('../php/denunciar_vaga_api.php', window.location.href), {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken,
                },
                body: JSON.stringify({
                    id_vaga: jobReportForm.querySelector('[data-job-report-id]').value,
                    motivo: jobReportForm.querySelector('[name="motivo"]').value,
                    detalhes: jobReportForm.querySelector('[name="detalhes"]').value.trim(),
                }),
            });
            const data = await response.json();
            if (!response.ok || data.sucesso !== true) {
                throw new Error(translate(data.mensagem || 'Não foi possível enviar a denúncia.'));
            }

            jobReportStatus.textContent = translate(data.mensagem);
            jobReportStatus.classList.add('form-success');
            jobReportStatus.hidden = false;
            jobReportSubmit.textContent = translate('Denúncia enviada');
            jobReportForm.querySelectorAll('select, textarea').forEach((field) => { field.disabled = true; });
            const reportedCard = detailCards.find((card) => card.dataset.reportJobId === jobReportButton.dataset.jobId);
            if (reportedCard) reportedCard.dataset.reportSubmitted = 'true';
            jobReportButton.disabled = true;
            jobReportButton.textContent = translate('Denúncia enviada');
        } catch (error) {
            jobReportStatus.textContent = error.message;
            jobReportStatus.hidden = false;
            jobReportSubmit.disabled = false;
            jobReportSubmit.textContent = translate('Enviar denúncia');
        }
    });

    profileModal?.addEventListener('click', (event) => {
        if (event.target === profileModal) profileModal.close();
    });

    settingsModal?.addEventListener('click', (event) => {
        if (event.target === settingsModal) settingsModal.close();
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
    const candidateListSearch = searchInput?.hasAttribute('data-candidate-list-search') === true;
    const globalEducationFilter = document.querySelector('[data-candidate-global-education]');
    const globalCurriculumFilter = document.querySelector('[data-candidate-global-curriculum]');
    const candidatePanels = [...document.querySelectorAll('.candidate-vacancy-panel')];
    const searchEmptyState = detailCards.length
        ? document.createElement('p')
        : null;
    if (searchEmptyState) {
        searchEmptyState.className = 'empty-state search-empty-state';
        searchEmptyState.setAttribute('role', 'status');
        searchEmptyState.setAttribute('aria-live', 'polite');
        searchEmptyState.textContent = translate('Nenhum resultado encontrado.');
        searchEmptyState.hidden = true;
        (candidatePanels[0] || detailCards[0]).before(searchEmptyState);
    }
    const normalizeSearch = (value) => String(value || '').trim().toLocaleLowerCase(interfaceLocale)
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    const splitCandidateFilterItems = (value) => String(value || '')
        .split(/[\r\n,;|]+/).map((item) => item.trim()).filter(Boolean);
    const cardTags = (card) => String(card.dataset.detailTags || '')
        .split('|').map((tag) => tag.trim()).filter(Boolean);
    const updateCardVisibility = () => {
        /* Compara o termo com texto visível e metadados do card, além da categoria selecionada.
         * A busca é local: não faz uma requisição a cada tecla nem altera dados armazenados. */
        const term = normalizeSearch(searchInput?.value);
        const activeFilter = filterSelect?.value || '';
        const globalCurriculumTerm = normalizeSearch(globalCurriculumFilter?.value);
        const globalEducation = normalizeSearch(globalEducationFilter?.value);
        const hasGlobalCandidateFilters = candidateListSearch
            && (term !== '' || activeFilter !== '' || globalCurriculumTerm !== '' || globalEducation !== '');

        detailCards.forEach((card) => {
            const searchableContent = [
                card.textContent,
                card.dataset.detailName,
                card.dataset.detailSummary,
                card.dataset.detailRole,
                card.dataset.detailTags,
                card.dataset.detailExperience,
                card.dataset.candidateNameEmail,
                card.dataset.candidateCurriculum,
            ].join(' ');
            const candidateNameEmail = normalizeSearch(card.dataset.candidateNameEmail);
            const normalizedSearchableContent = normalizeSearch(searchableContent);
            const matchesSearch = term === '' || (candidateListSearch
                ? candidateNameEmail.includes(term)
                : normalizedSearchableContent.includes(term));
            const matchesFilter = activeFilter === '' || cardTags(card).includes(activeFilter);
            const matchesGlobalCurriculum = globalCurriculumTerm === ''
                || normalizeSearch(card.dataset.candidateCurriculum).includes(globalCurriculumTerm);
            const matchesGlobalEducation = globalEducation === ''
                || normalizeSearch(card.dataset.candidateEducation) === globalEducation;
            const panel = card.closest('.candidate-vacancy-panel');
            const candidateTerm = normalizeSearch(panel?.querySelector('[data-candidate-search]')?.value);
            const education = normalizeSearch(panel?.querySelector('[data-candidate-education]')?.value);
            const matchesCandidateSearch = candidateTerm === ''
                || candidateNameEmail.includes(candidateTerm);
            const matchesEducation = education === ''
                || normalizeSearch(card.dataset.candidateEducation) === education;
            const course = normalizeSearch(panel?.querySelector('[data-candidate-course]')?.value);
            const skill = normalizeSearch(panel?.querySelector('[data-candidate-skill]')?.value);
            const experience = normalizeSearch(panel?.querySelector('[data-candidate-experience]')?.value);
            const matchesCourse = course === ''
                || splitCandidateFilterItems(card.dataset.candidateCourses).some((item) => normalizeSearch(item) === course);
            const matchesSkill = skill === ''
                || splitCandidateFilterItems(card.dataset.candidateSkills).some((item) => normalizeSearch(item) === skill);
            const matchesExperience = experience === ''
                || splitCandidateFilterItems(card.dataset.candidateExperiences).some((item) => normalizeSearch(item) === experience);
            card.hidden = !matchesSearch || !matchesFilter
                || !matchesGlobalCurriculum || !matchesGlobalEducation
                || !matchesCandidateSearch || !matchesEducation
                || !matchesCourse || !matchesSkill || !matchesExperience;
        });

        const hasActiveSearch = hasGlobalCandidateFilters || (!candidateListSearch && (term !== '' || activeFilter !== ''));
        const hasVisibleCards = detailCards.some((card) => !card.hidden);
        if (searchEmptyState) searchEmptyState.hidden = !hasActiveSearch || hasVisibleCards;
        candidatePanels.forEach((panel) => {
            const candidateCards = [...panel.querySelectorAll('.item-card[data-candidate-name-email]')];
            const localSearch = normalizeSearch(panel.querySelector('[data-candidate-search]')?.value);
            const education = normalizeSearch(panel.querySelector('[data-candidate-education]')?.value);
            const course = normalizeSearch(panel.querySelector('[data-candidate-course]')?.value);
            const skill = normalizeSearch(panel.querySelector('[data-candidate-skill]')?.value);
            const experience = normalizeSearch(panel.querySelector('[data-candidate-experience]')?.value);
            const hasLocalFilter = localSearch !== '' || education !== ''
                || course !== '' || skill !== '' || experience !== '';
            const hasVisibleCandidates = candidateCards.some((card) => !card.hidden);
            const emptyState = panel.querySelector('[data-candidate-filter-empty]');
            if (emptyState) emptyState.hidden = !hasLocalFilter || hasVisibleCandidates;

            if (hasGlobalCandidateFilters) {
                if (!Object.prototype.hasOwnProperty.call(panel.dataset, 'filterPreviousOpen')) {
                    panel.dataset.filterPreviousOpen = String(panel.open);
                }
                panel.open = hasVisibleCandidates;
            } else if (Object.prototype.hasOwnProperty.call(panel.dataset, 'filterPreviousOpen')) {
                panel.open = panel.dataset.filterPreviousOpen === 'true';
                delete panel.dataset.filterPreviousOpen;
            }
        });
    };

    candidatePanels.forEach((panel) => {
        panel.querySelectorAll('input[data-candidate-search]')
            .forEach((input) => input.addEventListener('input', updateCardVisibility));
        const candidateCards = [...panel.querySelectorAll('.item-card[data-candidate-name-email]')];
        const filterDefinitions = [
            { selector: '[data-candidate-course]', attribute: 'candidateCourses', emptyLabel: 'Todos os cursos' },
            { selector: '[data-candidate-skill]', attribute: 'candidateSkills', emptyLabel: 'Todas as habilidades' },
            { selector: '[data-candidate-experience]', attribute: 'candidateExperiences', emptyLabel: 'Todas as experiências' },
        ];

        filterDefinitions.forEach(({ selector, attribute, emptyLabel }) => {
            const select = panel.querySelector(selector);
            if (!select) return;

            const items = [...new Set(candidateCards.flatMap((card) => splitCandidateFilterItems(card.dataset[attribute])))]
                .sort((first, second) => first.localeCompare(second, interfaceLocale));
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = emptyLabel;
            select.replaceChildren(defaultOption, ...items.map((item) => {
                const option = document.createElement('option');
                option.value = item;
                option.textContent = item;
                return option;
            }));
            select.addEventListener('change', updateCardVisibility);
        });

        panel.querySelector('[data-candidate-education]')?.addEventListener('change', updateCardVisibility);
    });

    if (filterSelect) {
        // Monta as opções de filtro com as tags existentes nos cartões renderizados.
        const tags = [...new Set(detailCards.flatMap(cardTags))]
            .filter((tag) => !candidateListSearch || tag !== 'Candidatura')
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

    if (globalEducationFilter) {
        const educationLevels = [...new Set(detailCards
            .map((card) => card.dataset.candidateEducation?.trim())
            .filter(Boolean))]
            .sort((first, second) => first.localeCompare(second, 'pt-BR'));
        const options = educationLevels.map((education) => {
            const option = document.createElement('option');
            option.value = education;
            option.textContent = education;
            return option;
        });
        globalEducationFilter.append(...options);
        globalEducationFilter.addEventListener('change', updateCardVisibility);
    }

    globalCurriculumFilter?.addEventListener('input', updateCardVisibility);
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
