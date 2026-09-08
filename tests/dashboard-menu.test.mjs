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

    addEventListener(name, handler) {
        const handlers = this.listeners.get(name) || [];
        handlers.push(handler);
        this.listeners.set(name, handlers);
    }

    dispatch(name, details = {}) {
        const event = { type: name, target: this, ...details };
        (this.listeners.get(name) || []).forEach((handler) => handler(event));
    }
}

function loadMenu(shell, toggle, windowRef) {
    const context = { window: windowRef, console };
    vm.runInNewContext(fs.readFileSync('js/dashboard-menu.js', 'utf8'), context);
    return context.window.DevINDashboardMenu.init({ shell, toggle, windowRef });
}

test('mobile dashboard menu toggles its open state and aria-expanded', () => {
    const shell = new FakeNode();
    const toggle = new FakeNode();
    const windowRef = new FakeNode();
    windowRef.innerWidth = 375;

    loadMenu(shell, toggle, windowRef);
    assert.equal(toggle.attributes['aria-expanded'], 'false');

    toggle.dispatch('click');
    assert.equal(shell.classList.contains('mobile-menu-open'), true);
    assert.equal(toggle.attributes['aria-expanded'], 'true');

    toggle.dispatch('click');
    assert.equal(shell.classList.contains('mobile-menu-open'), false);
    assert.equal(toggle.attributes['aria-expanded'], 'false');
});

test('dashboard mobile menu closes when the viewport crosses to desktop', () => {
    const shell = new FakeNode();
    const toggle = new FakeNode();
    const windowRef = new FakeNode();
    windowRef.innerWidth = 375;

    loadMenu(shell, toggle, windowRef);
    toggle.dispatch('click');
    windowRef.innerWidth = 1024;
    windowRef.dispatch('resize');

    assert.equal(shell.classList.contains('mobile-menu-open'), false);
    assert.equal(toggle.attributes['aria-expanded'], 'false');
});
