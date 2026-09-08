import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';

const layoutFiles = [
    'css/style.css',
    'css/login.css',
    'css/recuperacao.css',
    'css/cadastrostyle.css',
    'css/curriculo.css',
    'css/politica-privacidade.css',
];

test('responsive styles protect the document from unintended overflow', () => {
    for (const file of layoutFiles) {
        const css = fs.readFileSync(file, 'utf8');
        assert.match(css, /overflow-x\s*:\s*hidden/);
        assert.match(css, /@media\s*\([^)]*max-width/);
    }
});

test('public layout containers can shrink inside responsive grids', () => {
    const css = fs.readFileSync('css/style.css', 'utf8');
    assert.match(css, /\.principal[^}]*min-width\s*:\s*0/s);
    assert.match(css, /\.linha-sobre[^}]*min-width\s*:\s*0/s);
});

test('arcade pages expose the shared hamburger navigation contract', () => {
    for (const file of ['html/jogos/pacman.html', 'html/jogos/doom.html']) {
        const html = fs.readFileSync(file, 'utf8');
        assert.match(html, /site-menu-toggle/);
        assert.match(html, /site-navigation\.css/);
        assert.match(html, /site-navigation\.js/);
    }
});
