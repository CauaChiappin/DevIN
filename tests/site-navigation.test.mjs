import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

class FakeClassList {
    constructor() {
        this.values = new Set();
    }

    toggle(name, force) {
        const shouldAdd = force === undefined ? !this.values.has(name) : force;
        if (shouldAdd) this.values.add(name);
        else this.values.delete(name);
        return shouldAdd;
    }

    contains(name) {
        return this.values.has(name);
    }
}

class FakeNode {
    constructor() {
        this.attributes = {};
        this.classList = new FakeClassList();
        this.listeners = new Map();
    }

    setAttribute(name, value) {
        this.attributes[name] = String(value);
    }

    getAttribute(name) {
        return this.attributes[name] ?? null;
    }

    addEventListener(name, handler) {
        const handlers = this.listeners.get(name) || [];
        handlers.push(handler);
        this.listeners.set(name, handlers);
    }

    dispatch(name, details = {}) {
        const event = {
            type: name,
            target: this,
            ...details,
            preventDefault() {
                this.defaultPrevented = true;
            },
        };
        (this.listeners.get(name) || []).forEach((handler) => handler(event));
    }
}

function createNavigationFixture() {
    const document = new FakeNode();
    const toggle = new FakeNode();
    const menu = new FakeNode();
    const link = new FakeNode();

    document.readyState = 'complete';
    document.querySelector = (selector) => {
        if (selector === '[data-site-menu-toggle]') return toggle;
        if (selector === '[data-site-menu]') return menu;
        return null;
    };
    menu.querySelectorAll = (selector) => selector === 'a' ? [link] : [];
    document.contains = (node) => [document, toggle, menu, link].includes(node);

    return { document, toggle, menu, link };
}

function loadNavigation(fixture) {
    const context = {
        document: fixture.document,
        window: {},
        console,
    };
    vm.runInNewContext(fs.readFileSync('js/site-navigation.js', 'utf8'), context);
}

test('opens and closes the site menu while synchronizing aria-expanded', () => {
    const fixture = createNavigationFixture();
    loadNavigation(fixture);

    fixture.toggle.dispatch('click');
    assert.equal(fixture.toggle.attributes['aria-expanded'], 'true');
    assert.equal(fixture.menu.classList.contains('is-open'), true);

    fixture.document.dispatch('keydown', { key: 'Escape' });
    assert.equal(fixture.toggle.attributes['aria-expanded'], 'false');
    assert.equal(fixture.menu.classList.contains('is-open'), false);
});

test('closes the site menu after selecting a navigation link', () => {
    const fixture = createNavigationFixture();
    loadNavigation(fixture);

    fixture.toggle.dispatch('click');
    fixture.link.dispatch('click');

    assert.equal(fixture.toggle.attributes['aria-expanded'], 'false');
});
