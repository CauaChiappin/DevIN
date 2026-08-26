(() => {
    'use strict';

    const dashboardShell = document.querySelector('.dashboard-shell');
    const menuToggle = document.querySelector('[data-toggle-menu]');
    const detailText = document.querySelector('#detailText');
    const detailTitle = document.querySelector('#detailTitle');
    const settingsModal = document.querySelector('#settingsModal');
    const profileModal = document.querySelector('#profileModal');
    const rotatingWord = document.querySelector('[data-rotating-word]');

    // Sidebar: preferência fica salva somente no navegador deste usuário.
    if (dashboardShell && menuToggle) {
        const fechado = localStorage.getItem('devin-menu-fechado') === 'true';
        dashboardShell.classList.toggle('menu-fechado', fechado);
        menuToggle.setAttribute('aria-expanded', String(!fechado));

        menuToggle.addEventListener('click', () => {
            const novoEstado = dashboardShell.classList.toggle('menu-fechado');
            menuToggle.setAttribute('aria-expanded', String(!novoEstado));
            localStorage.setItem('devin-menu-fechado', String(novoEstado));
        });
    }

    // Cards: impede que botões/formulários internos sejam tratados como seleção do card.
    document.querySelectorAll('.item-card[data-detail]').forEach((card) => {
        card.addEventListener('click', (event) => {
            if (event.target.closest('button, a, form, summary, input, textarea, select')) return;

            document.querySelectorAll('.item-card[data-detail]').forEach((item) => {
                item.classList.remove('selecionado');
            });

            card.classList.add('selecionado');

            if (detailText) detailText.textContent = card.dataset.detail || 'Sem detalhes disponíveis.';
            if (detailTitle && card.dataset.jobTitle) detailTitle.textContent = card.dataset.jobTitle;
        });
    });

    // Confirmação de saída para pessoa, empresa e administrador.
    document.querySelectorAll('[data-confirm-logout]').forEach((link) => {
        link.addEventListener('click', (event) => {
            const message = link.dataset.confirmLogout || 'Tem certeza que deseja sair da sua conta?';
            if (!window.confirm(message)) event.preventDefault();
        });
    });

    // Confirmação de exclusão da conta.
    document.querySelectorAll('[data-delete-account]').forEach((button) => {
        button.addEventListener('click', (event) => {
            if (!window.confirm('Tem certeza? Esta ação exclui sua conta permanentemente.')) {
                event.preventDefault();
            }
        });
    });

    // Fecha dialogs sem depender de IDs específicos.
    document.querySelectorAll('[data-close-modal]').forEach((button) => {
        button.addEventListener('click', () => button.closest('dialog')?.close());
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

    // Pré-visualização segura da foto.
    document.querySelectorAll(".profile-photo input[type='file']").forEach((input) => {
        input.addEventListener('change', (event) => {
            const photo = event.currentTarget.files?.[0];
            if (!photo) return;

            const allowed = ['image/jpeg', 'image/png', 'image/webp'];
            if (!allowed.includes(photo.type) || photo.size > 5 * 1024 * 1024) {
                event.currentTarget.value = '';
                window.alert('Selecione uma imagem JPG, PNG ou WEBP de até 5 MB.');
                return;
            }

            const previewUrl = URL.createObjectURL(photo);
            document.querySelectorAll('.current-user-avatar').forEach((preview) => {
                preview.replaceChildren();
                const image = document.createElement('img');
                image.src = previewUrl;
                image.alt = 'Prévia da foto de perfil';
                preview.appendChild(image);
            });
        });
    });

    // Loading para qualquer formulário que realmente será enviado.
    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', () => {
            const submitter = form.querySelector('button[type="submit"]:focus, button[type="submit"]:last-of-type');
            if (!submitter || submitter.dataset.loading === 'true') return;
            submitter.dataset.loading = 'true';
            submitter.disabled = true;
            submitter.dataset.originalText = submitter.textContent;
            submitter.textContent = 'Processando...';
        });
    });

    // Busca local sem enviar uma nova requisição a cada tecla.
    const searchInput = document.querySelector('.busca input[type="search"]');
    searchInput?.addEventListener('input', () => {
        const term = searchInput.value.trim().toLocaleLowerCase('pt-BR');
        document.querySelectorAll('.lista-area .item-card').forEach((card) => {
            card.hidden = term !== '' && !card.textContent.toLocaleLowerCase('pt-BR').includes(term);
        });
    });

    document.querySelector('.busca')?.addEventListener('submit', (event) => event.preventDefault());

    // Palavra animada da página institucional.
    if (rotatingWord) {
        const words = ['futuro', 'mundo', 'universo', 'avanço', 'mercado', 'amanhã'];
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

    if (new URLSearchParams(window.location.search).get('perfil') === 'meu' && profileModal?.showModal) {
        profileModal.showModal();
    }
})();
