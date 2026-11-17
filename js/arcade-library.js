/**
 * Catálogo de jogos do DevIN.
 * Filtra botões já renderizados no HTML, sem acesso ao banco, sem cookies e
 * sem interferir na validação dos recordes. O botão aleatório escolhe apenas
 * jogos visíveis segundo a pesquisa/categoria aplicada pelo usuário.
 */
(() => { // Explicação DevIN: Participa da instrução atual, ajustando cálculo, condição, dados ou resultado.
    'use strict'; // Explicação DevIN: Declara ou continua um valor literal usado pela instrução em andamento.
    const library = document.querySelector('.portal-library'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
    if (!library) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    const search = library.querySelector('[data-arcade-search]'); // Explicação DevIN: Declara variável JavaScript search para reutilizar valor ou resultado.
    const cards = Array.from(library.querySelectorAll('[data-launch-game]')); // Explicação DevIN: Declara variável JavaScript cards para reutilizar valor ou resultado.
    const filters = Array.from(library.querySelectorAll('[data-arcade-filter]')); // Explicação DevIN: Declara variável JavaScript filters para reutilizar valor ou resultado.
    const counter = library.querySelector('[data-arcade-count]'); // Explicação DevIN: Declara variável JavaScript counter para reutilizar valor ou resultado.
    const empty = library.querySelector('[data-arcade-empty]'); // Explicação DevIN: Declara variável JavaScript empty para reutilizar valor ou resultado.
    const random = library.querySelector('[data-random-game]'); // Explicação DevIN: Declara variável JavaScript random para reutilizar valor ou resultado.
    let category = 'todos'; // Explicação DevIN: Declara variável JavaScript category para reutilizar valor ou resultado.

    // Normalização local permite encontrar palavras com ou sem acentos.
    const normalize = (text) => String(text || '').normalize('NFD') // Explicação DevIN: Declara variável JavaScript normalize para reutilizar valor ou resultado.
        .replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('pt-BR'); // Explicação DevIN: Continua a expressão lógica, encadeamento ou associação do comando anterior.

    function applyFilters() { // Explicação DevIN: Declara função/método applyFilters, com seus parâmetros e comportamento.
        const query = normalize(search?.value.trim()); // Explicação DevIN: Declara variável JavaScript query para reutilizar valor ou resultado.
        let visible = 0; // Explicação DevIN: Declara variável JavaScript visible para reutilizar valor ou resultado.
        cards.forEach((card) => { // Explicação DevIN: Continua expressão ou bloco pertencente a função applyFilters.
            const genre = card.dataset.genre || ''; // Explicação DevIN: Declara variável JavaScript genre para reutilizar valor ou resultado.
            const words = normalize(card.textContent); // Explicação DevIN: Declara variável JavaScript words para reutilizar valor ou resultado.
            const matchesGenre = category === 'todos' || genre === category; // Explicação DevIN: Declara variável JavaScript matchesGenre para reutilizar valor ou resultado.
            const show = matchesGenre && (!query || words.includes(query)); // Explicação DevIN: Declara variável JavaScript show para reutilizar valor ou resultado.
            card.hidden = !show; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
            if (show) visible += 1; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        if (counter) counter.textContent = visible === 1 ? '1 jogo encontrado' : visible + ' jogos encontrados'; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if (empty) empty.hidden = visible !== 0; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        if (random) random.disabled = visible === 0; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
    } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    search?.addEventListener('input', applyFilters); // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
    filters.forEach((filter) => filter.addEventListener('click', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        category = filter.dataset.arcadeFilter || 'todos'; // Explicação DevIN: Atribui propriedade, resultado ou associação de chave e valor.
        filters.forEach((item) => { // Explicação DevIN: Continua expressão ou bloco pertencente a função applyFilters.
            const selected = item === filter; // Explicação DevIN: Declara variável JavaScript selected para reutilizar valor ou resultado.
            item.classList.toggle('is-selected', selected); // Explicação DevIN: Continua expressão ou bloco pertencente a função applyFilters.
            item.setAttribute('aria-pressed', String(selected)); // Explicação DevIN: Continua expressão ou bloco pertencente a função applyFilters.
        }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        applyFilters(); // Explicação DevIN: Continua expressão ou bloco pertencente a função applyFilters.
    })); // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.

    random?.addEventListener('click', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        const options = cards.filter((card) => !card.hidden); // Explicação DevIN: Declara variável JavaScript options para reutilizar valor ou resultado.
        if (!options.length) return; // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
        const chosen = options[Math.floor(Math.random() * options.length)]; // Explicação DevIN: Declara variável JavaScript chosen para reutilizar valor ou resultado.
        chosen.click(); // Explicação DevIN: Continua expressão ou bloco pertencente a função applyFilters.
        chosen.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); // Explicação DevIN: Continua expressão ou bloco pertencente a função applyFilters.
        const player = document.querySelector('.arcade-play-panel'); // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
        if (window.matchMedia('(max-width: 980px)').matches) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            player?.scrollIntoView({ behavior: 'smooth', block: 'start' }); // Explicação DevIN: Continua expressão ou bloco pertencente a função applyFilters.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.

    // Ao selecionar um jogo no celular, revelar imediatamente a prévia,
    // sem obrigar a rolar por todo o catálogo até encontrar o canvas.
    cards.forEach((card) => card.addEventListener('click', () => { // Explicação DevIN: Registra callback para responder a uma interação ou evento do navegador.
        if (window.matchMedia('(max-width: 980px)').matches) { // Explicação DevIN: Inicia desvio condicional: executa ramo somente quando a condição for satisfeita.
            document.querySelector('.arcade-play-panel')?.scrollIntoView({ // Explicação DevIN: Interage com a página, elementos visíveis ou eventos do navegador.
                behavior: 'smooth', block: 'start' // Explicação DevIN: Continua expressão ou bloco pertencente a função applyFilters.
            }); // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
        } // Explicação DevIN: Fecha ou delimita bloco, chamada, lista ou objeto aberto anteriormente.
    })); // Explicação DevIN: Finaliza expressão, lista, função ou bloco sintático.
    applyFilters(); // Explicação DevIN: Continua expressão ou bloco pertencente a função applyFilters.
})(); // Explicação DevIN: Continua expressão ou bloco pertencente a função applyFilters.