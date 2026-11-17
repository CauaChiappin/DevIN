/**
 * Catálogo de jogos do DevIN.
 * Filtra botões já renderizados no HTML, sem acesso ao banco, sem cookies e
 * sem interferir na validação dos recordes. O botão aleatório escolhe apenas
 * jogos visíveis segundo a pesquisa/categoria aplicada pelo usuário.
 */
(() => {
    'use strict';
    const library = document.querySelector('.portal-library');
    if (!library) return;
    const search = library.querySelector('[data-arcade-search]');
    const cards = Array.from(library.querySelectorAll('[data-launch-game]'));
    const filters = Array.from(library.querySelectorAll('[data-arcade-filter]'));
    const counter = library.querySelector('[data-arcade-count]');
    const empty = library.querySelector('[data-arcade-empty]');
    const random = library.querySelector('[data-random-game]');
    let category = 'todos';

    // Normalização local permite encontrar palavras com ou sem acentos.
    const normalize = (text) => String(text || '').normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('pt-BR');

    function applyFilters() {
        const query = normalize(search?.value.trim());
        let visible = 0;
        cards.forEach((card) => {
            const genre = card.dataset.genre || '';
            const words = normalize(card.textContent);
            const matchesGenre = category === 'todos' || genre === category;
            const show = matchesGenre && (!query || words.includes(query));
            card.hidden = !show;
            if (show) visible += 1;
        });
        if (counter) counter.textContent = visible === 1 ? '1 jogo encontrado' : visible + ' jogos encontrados';
        if (empty) empty.hidden = visible !== 0;
        if (random) random.disabled = visible === 0;
    }

    search?.addEventListener('input', applyFilters);
    filters.forEach((filter) => filter.addEventListener('click', () => {
        category = filter.dataset.arcadeFilter || 'todos';
        filters.forEach((item) => {
            const selected = item === filter;
            item.classList.toggle('is-selected', selected);
            item.setAttribute('aria-pressed', String(selected));
        });
        applyFilters();
    }));

    random?.addEventListener('click', () => {
        const options = cards.filter((card) => !card.hidden);
        if (!options.length) return;
        const chosen = options[Math.floor(Math.random() * options.length)];
        chosen.click();
        chosen.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        const player = document.querySelector('.arcade-play-panel');
        if (window.matchMedia('(max-width: 980px)').matches) {
            player?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });

    // Ao selecionar um jogo no celular, revelar imediatamente a prévia,
    // sem obrigar a rolar por todo o catálogo até encontrar o canvas.
    cards.forEach((card) => card.addEventListener('click', () => {
        if (window.matchMedia('(max-width: 980px)').matches) {
            document.querySelector('.arcade-play-panel')?.scrollIntoView({
                behavior: 'smooth', block: 'start'
            });
        }
    }));
    applyFilters();
})();