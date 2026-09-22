const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

function createCard(textContent, dataset) {
    return {
        textContent,
        dataset,
        hidden: false,
        classList: { add() {}, remove() {} },
        addEventListener() {},
        querySelector() { return null; },
    };
}

const cards = [
    createCard('Aline Silva\naline@example.com', {
        detailName: 'Aline Silva',
        detailTags: 'PHP|Laravel|Inglês',
        detailExperience: 'Experiência profissional::Desenvolvedora PHP',
    }),
    createCard('Bruno Costa\nbruno@example.com', {
        detailName: 'Bruno Costa',
        detailTags: 'Java|Spring',
        detailExperience: 'Experiência profissional::Desenvolvedor Java',
    }),
];

let onSearchInput;
const searchInput = {
    value: '',
    addEventListener(type, listener) {
        if (type === 'input') onSearchInput = listener;
    },
};

let onFilterToggle;
let onFilterChange;
const filterButton = {
    attributes: { 'aria-expanded': 'false' },
    addEventListener(type, listener) {
        if (type === 'click') onFilterToggle = listener;
    },
    getAttribute(name) { return this.attributes[name] || null; },
    setAttribute(name, value) { this.attributes[name] = value; },
};
const filterMenu = { hidden: true };
const filterSelect = {
    value: '',
    options: [],
    addEventListener(type, listener) {
        if (type === 'change') onFilterChange = listener;
    },
    replaceChildren(...options) { this.options = options; },
};

const document = {
    querySelector(selector) {
        if (selector === '.busca input[type="search"]') return searchInput;
        if (selector === '.lista-area') return { addEventListener() {} };
        if (selector === '.busca') return { addEventListener() {} };
        if (selector === '[data-toggle-filters]') return filterButton;
        if (selector === '[data-filter-menu]') return filterMenu;
        if (selector === '[data-card-filter]') return filterSelect;
        return null;
    },
    querySelectorAll(selector) {
        if (selector === '.item-card[data-detail]' || selector === '.lista-area .item-card') return cards;
        return [];
    },
    createElement() {
        return { value: '', textContent: '' };
    },
};

const context = {
    document,
    window: { location: { search: '' } },
    URLSearchParams,
};

vm.runInNewContext(fs.readFileSync('js/dashboard.js', 'utf8'), context, { filename: 'dashboard.js' });

assert.equal(typeof onSearchInput, 'function', 'A busca registra o evento de digitação.');

searchInput.value = 'php';
onSearchInput();

assert.equal(cards[0].hidden, false, 'A busca encontra uma habilidade vinda do currículo.');
assert.equal(cards[1].hidden, true, 'A busca oculta cards que não correspondem à habilidade pesquisada.');

assert.equal(typeof onFilterToggle, 'function', 'O botão de filtros registra o clique.');
assert.equal(typeof onFilterChange, 'function', 'O seletor de filtros registra a mudança.');

onFilterToggle();

assert.equal(filterMenu.hidden, false, 'O clique em Filtros abre as opções disponíveis.');
assert.equal(filterButton.getAttribute('aria-expanded'), 'true', 'O botão informa que as opções estão abertas.');
assert.deepEqual(
    filterSelect.options.map((option) => option.value),
    ['', 'Inglês', 'Java', 'Laravel', 'PHP', 'Spring'],
    'O filtro oferece as habilidades existentes nos cards.'
);

searchInput.value = '';
onSearchInput();
filterSelect.value = 'PHP';
onFilterChange();

assert.equal(cards[0].hidden, false, 'O filtro mantém o card com a habilidade escolhida.');
assert.equal(cards[1].hidden, true, 'O filtro oculta cards sem a habilidade escolhida.');

console.log('OK: busca do dashboard inclui habilidades do card');
