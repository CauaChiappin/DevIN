# DevIN Responsive Layout and Mobile Navigation Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make all DevIN public, PHP-rendered, arcade, and authenticated dashboard pages responsive, with accessible hamburger navigation on mobile and no unintended horizontal overflow.

**Architecture:** Add one reusable public navigation behavior script and one shared navigation stylesheet, then normalize the header markup across static/PHP public pages. Keep page-specific visual rules in their current stylesheets, and adapt the existing dashboard sidebar toggle into a mobile top-bar navigation without changing routes, authentication, forms, or game logic.

**Tech Stack:** HTML, CSS, vanilla JavaScript, PHP templates, Node.js built-in test runner, PHP CLI syntax checker.

**Spec:** `docs/superpowers/specs/2026-09-08-responsive-layout-and-mobile-navigation-design.md`

## Global Constraints

- Preserve existing blue/white branding and current page content.
- Do not change authentication, CSRF, form actions, redirects, database logic, or game logic.
- Mobile horizontal page padding stays within 16–20px and interactive controls have at least a 44px target.
- Mobile navigation uses `aria-expanded`, `aria-controls`, keyboard Escape support, and visible focus styles.
- Document-level horizontal overflow is prevented except for explicit game/data surfaces.
- Respect `prefers-reduced-motion` for navigation and layout transitions.

---

### Task 1: Build and test the shared public mobile navigation

**Files:**
- Create: `js/site-navigation.js`
- Create: `css/site-navigation.css`
- Create: `tests/site-navigation.test.mjs`
- Modify: `html/index.html`
- Modify: `html/login.html`
- Modify: `html/politica_privacidade.html`
- Modify: `html/cadastro_pessoa.html`
- Modify: `html/cadastro_empresa.html`
- Modify: `php/login.php`
- Modify: `php/cadastro_pessoa.php`
- Modify: `php/cadastro_empresa.php`
- Modify: `php/recuperacao.php`
- Modify: `php/redefinir.php`
- Modify: `php/cadastrar_curriculo.php`

**Interfaces:**
- `site-navigation.js` initializes `[data-site-menu-toggle]` and `[data-site-menu]` pairs and exposes `window.DevINSiteNavigation.init(root)` for isolated tests.
- Public headers use `data-site-menu-root`, `data-site-menu-toggle`, and `data-site-menu` as the stable integration contract.

- [ ] **Step 1: Write the failing navigation behavior tests**

Create a small fake DOM in `tests/site-navigation.test.mjs` and assert the real script behavior, not just source text:

```js
import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

test('opens and closes the site menu while synchronizing aria-expanded', () => {
  const fixture = createNavigationFixture();
  const context = { document: fixture.document, window: {}, console };
  vm.runInNewContext(fs.readFileSync('js/site-navigation.js', 'utf8'), context);

  fixture.toggle.dispatch('click');
  assert.equal(fixture.toggle.attributes['aria-expanded'], 'true');
  assert.equal(fixture.menu.classList.has('is-open'), true);

  fixture.document.dispatch('keydown', { key: 'Escape' });
  assert.equal(fixture.toggle.attributes['aria-expanded'], 'false');
  assert.equal(fixture.menu.classList.has('is-open'), false);
});

test('closes the site menu after selecting a navigation link', () => {
  const fixture = createNavigationFixture();
  const context = { document: fixture.document, window: {}, console };
  vm.runInNewContext(fs.readFileSync('js/site-navigation.js', 'utf8'), context);

  fixture.toggle.dispatch('click');
  fixture.link.dispatch('click');
  assert.equal(fixture.toggle.attributes['aria-expanded'], 'false');
});
```

The fixture should provide only the DOM methods the production script needs: `querySelector`, `querySelectorAll`, `addEventListener`, `dispatch`, `classList.toggle`, `setAttribute`, and `contains`.

- [ ] **Step 2: Run the focused test and confirm it fails for the missing script**

Run: `node --test tests/site-navigation.test.mjs`

Expected: FAIL because `js/site-navigation.js` does not yet exist or does not expose the expected behavior.

- [ ] **Step 3: Implement the minimal reusable navigation script**

Implement an IIFE that initializes after `DOMContentLoaded` when necessary and exposes the initializer for tests. The core close/open path must be equivalent to:

```js
const setOpen = (open) => {
    menu.classList.toggle('is-open', open);
    toggle.setAttribute('aria-expanded', String(open));
};

toggle.addEventListener('click', () => setOpen(!menu.classList.contains('is-open')));
menu.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => setOpen(false)));
root.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') setOpen(false);
});
```

Add a document click handler that closes the menu when an outside click occurs, and guard initialization when either control is absent.

- [ ] **Step 4: Run the focused test and confirm it passes**

Run: `node --test tests/site-navigation.test.mjs`

Expected: 2 passing tests and 0 failures.

- [ ] **Step 5: Add shared accessible navigation styles**

In `css/site-navigation.css`, define the desktop inline state, the 44px toggle target, focus-visible styles, and a mobile breakpoint around 720px:

```css
[data-site-menu-toggle] { display: none; min-width: 44px; min-height: 44px; }
[data-site-menu] { display: contents; }

@media (max-width: 720px) {
    [data-site-menu-toggle] { display: inline-grid; place-items: center; }
    [data-site-menu] { display: none; }
    [data-site-menu].is-open { display: grid; }
}

@media (prefers-reduced-motion: reduce) {
    [data-site-menu], [data-site-menu-toggle] { transition: none; }
}
```

Use the existing page colors and add a three-bar icon using child spans or a CSS background; do not add a dependency.

- [ ] **Step 6: Normalize static and PHP public header markup and asset loading**

For each page in this task, add the shared stylesheet and script. Use this contract in headers that have a main site navigation:

```html
<header class="cabecalho-site" data-site-menu-root>
    <a class="marca" href="index.html">Dev<span>IN</span></a>
    <button class="site-menu-toggle" type="button" aria-label="Abrir menu" aria-controls="site-menu" aria-expanded="false" data-site-menu-toggle>
        <span aria-hidden="true"></span><span aria-hidden="true"></span><span aria-hidden="true"></span>
    </button>
    <div class="site-menu" id="site-menu" data-site-menu>
        <!-- existing navigation links and action links -->
    </div>
</header>
```

Keep relative asset paths correct for `html/` versus `php/`. For privacy, recovery, reset, and curriculum pages that do not currently expose the full site navigation, put their existing return/login action inside the same mobile menu contract. For registration pages, keep the desktop artwork panel action in the page-specific layout only if it remains visible; the header menu must retain a reachable login link on mobile.

- [ ] **Step 7: Run static navigation checks**

Run:

```powershell
$pages = @('html/index.html','html/login.html','html/cadastro_pessoa.html','html/cadastro_empresa.html','html/politica_privacidade.html','php/login.php','php/cadastro_pessoa.php','php/cadastro_empresa.php','php/recuperacao.php','php/redefinir.php','php/cadastrar_curriculo.php')
foreach ($page in $pages) {
    $content = Get-Content -Raw $page
    if ($content -notmatch 'name="viewport"') { throw "$page missing viewport" }
    if ($content -notmatch 'site-navigation\.css') { throw "$page missing shared navigation CSS" }
    if ($content -notmatch 'site-navigation\.js') { throw "$page missing shared navigation JS" }
}
```

Expected: the command completes without throwing.

### Task 2: Make public, authentication, recovery, and form layouts fluid

**Files:**
- Modify: `css/style.css`
- Modify: `css/login.css`
- Modify: `css/recuperacao.css`
- Modify: `css/cadastrostyle.css`
- Modify: `css/curriculo.css`
- Modify: `css/politica-privacidade.css`
- Modify: `html/index.html`
- Modify: `html/login.html`
- Modify: `php/login.php`
- Modify: `php/recuperacao.php`
- Modify: `php/redefinir.php`
- Modify: `php/cadastrar_curriculo.php`

**Interfaces:**
- All page layouts consume the shared menu states from Task 1.
- Existing form classes (`.area-login`, `.main-container`, `.form-columns`, `.recovery-page`, `.curriculo-page`) remain the styling API so PHP behavior does not change.

- [ ] **Step 1: Add a failing static layout regression check**

Create `tests/responsive-layout.test.mjs` using Node’s built-in test runner. Read each target stylesheet and assert it contains the required responsive contract selectors before implementation:

```js
import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';

test('responsive styles protect the document from unintended overflow', () => {
  for (const file of ['css/style.css', 'css/login.css', 'css/recuperacao.css', 'css/cadastrostyle.css', 'css/curriculo.css', 'css/politica-privacidade.css']) {
    const css = fs.readFileSync(file, 'utf8');
    assert.match(css, /overflow-x\s*:\s*hidden/);
    assert.match(css, /@media\s*\([^)]*max-width/);
  }
});
```

- [ ] **Step 2: Run the layout regression test and confirm it fails**

Run: `node --test tests/responsive-layout.test.mjs`

Expected: FAIL for at least one stylesheet that lacks the required responsive contract.

- [ ] **Step 3: Implement fluid page layouts and mobile breakpoints**

Apply the approved design with `min-width: 0`, `max-width: 100%`, `clamp()`, and mobile grid/flex changes:

```css
html, body { max-width: 100%; overflow-x: hidden; }
img, iframe, canvas { max-width: 100%; }

@media (max-width: 720px) {
    .conteudo-login { gap: 24px; padding: 24px 16px 40px; }
    .gif-robo { width: min(280px, 80vw); }
    .area-login { max-width: 100%; padding: 32px 22px; }
    .form-columns { flex-direction: column; gap: 18px; }
}
```

Update the home page so hero/features/support/footer grids collapse without absolute/fixed widths forcing overflow. Update registration/curriculum forms to use one column below 950px while keeping controls full width and readable. Update recovery/privacy cards to use 16–20px page gutters and smaller internal padding. Do not remove form error states or alter input names/actions.

- [ ] **Step 4: Run the layout regression test and confirm it passes**

Run: `node --test tests/responsive-layout.test.mjs`

Expected: 1 passing test and 0 failures.

- [ ] **Step 5: Syntax-check every modified PHP template**

Run:

```powershell
foreach ($page in @('php/login.php','php/cadastro_pessoa.php','php/cadastro_empresa.php','php/recuperacao.php','php/redefinir.php','php/cadastrar_curriculo.php')) {
    php -l $page
    if ($LASTEXITCODE -ne 0) { throw "PHP syntax failed: $page" }
}
```

Expected: each page reports no syntax errors.

### Task 3: Make arcade pages responsive without shrinking the games incorrectly

**Files:**
- Modify: `css/jogos.css`
- Modify: `html/jogos/pacman.html`
- Modify: `html/jogos/doom.html`

**Interfaces:**
- Existing game DOM, iframe sources, canvas IDs, and game scripts remain unchanged.
- Arcade navigation uses the same menu behavior contract only if the header has more than one action; otherwise its controls wrap safely without introducing a second incompatible menu system.

- [ ] **Step 1: Add a failing arcade overflow check**

Extend `tests/responsive-layout.test.mjs` with:

```js
test('arcade styles keep emulator frames inside their container', () => {
  const css = fs.readFileSync('css/jogos.css', 'utf8');
  assert.match(css, /\.emulator-frame[^}]*width:\s*100%/s);
  assert.match(css, /\.emulator-frame iframe[^}]*width:\s*100%/s);
  assert.match(css, /@media\s*\([^)]*max-width/);
});
```

- [ ] **Step 2: Run the arcade check and confirm it fails or identifies the missing mobile contract**

Run: `node --test tests/responsive-layout.test.mjs`

Expected: FAIL if the existing arcade CSS does not yet contain the complete contract.

- [ ] **Step 3: Implement arcade header/control and game-surface responsiveness**

Keep the shell fluid, let header links wrap, keep emulator frames at `width: 100%` with their aspect ratio, and make only the Pac-Man board surface horizontally scrollable at narrow widths. Add reduced-motion handling for arcade animations and preserve the existing 18px mobile board cells.

- [ ] **Step 4: Run the arcade check and confirm it passes**

Run: `node --test tests/responsive-layout.test.mjs`

Expected: all layout tests pass.

### Task 4: Adapt authenticated dashboards to a mobile top-bar menu

**Files:**
- Modify: `css/dashboard.css`
- Modify: `js/dashboard.js`
- Modify: `php/pessoa.php`
- Modify: `php/empresa.php`
- Modify: `php/adm.php`

**Interfaces:**
- Existing `[data-toggle-menu]` button and `.dashboard-shell.menu-fechado` state remain supported.
- At mobile widths, `.dashboard-shell.mobile-menu-open` controls visibility of `.menu-principal`, and `aria-expanded` reflects that state.

- [ ] **Step 1: Add failing dashboard behavior tests**

Add a test case to `tests/site-navigation.test.mjs` or a separate `tests/dashboard-menu.test.mjs` that executes the extracted dashboard menu helper with a fake shell/toggle and verifies that clicking at mobile width toggles `.mobile-menu-open` and `aria-expanded`.

Expected behavior:

```js
toggle.dispatch('click');
assert.equal(shell.classList.has('mobile-menu-open'), true);
assert.equal(toggle.attributes['aria-expanded'], 'true');
```

- [ ] **Step 2: Run the dashboard test and confirm it fails**

Run: `node --test tests/dashboard-menu.test.mjs`

Expected: FAIL because the mobile-specific state does not yet exist.

- [ ] **Step 3: Implement responsive dashboard behavior**

Keep desktop local collapse behavior. Add a viewport-aware mobile path in `js/dashboard.js` that initializes the state from the current viewport, toggles `.mobile-menu-open`, updates `aria-expanded`, closes the profile dropdown when the menu opens, and resets the mobile state on resize when crossing the 720px breakpoint. In `css/dashboard.css`, use a single-column shell below 720px, keep a compact `.sidebar-topo`, hide `.menu-principal` until opened, and let cards/detail panels stack with `minmax(0, 1fr)` and safe overflow.

Do not make a desktop preference hide the mobile menu permanently. Keep the existing desktop `devin-menu-fechado` preference for desktop only.

- [ ] **Step 4: Run dashboard behavior and PHP syntax checks**

Run:

```powershell
node --test tests/dashboard-menu.test.mjs
foreach ($page in @('php/pessoa.php','php/empresa.php','php/adm.php')) {
    php -l $page
    if ($LASTEXITCODE -ne 0) { throw "PHP syntax failed: $page" }
}
```

Expected: dashboard test passes and all three PHP files report no syntax errors.

### Task 5: Full verification at target viewport sizes

**Files:**
- Modify only files required by failed verification checks.

- [ ] **Step 1: Run the full JavaScript test suite**

Run: `node --test tests/*.test.mjs`

Expected: every test passes with 0 failures.

- [ ] **Step 2: Run PHP syntax checks for all changed PHP templates**

Run:

```powershell
foreach ($page in @('php/login.php','php/cadastro_pessoa.php','php/cadastro_empresa.php','php/recuperacao.php','php/redefinir.php','php/cadastrar_curriculo.php','php/pessoa.php','php/empresa.php','php/adm.php')) {
    php -l $page
    if ($LASTEXITCODE -ne 0) { throw "PHP syntax failed: $page" }
}
```

Expected: 9 successful syntax checks.

- [ ] **Step 3: Run repository diff and static contract checks**

Run: `git diff --check` and rerun the shared navigation/static viewport script from Task 1.

Expected: no whitespace errors, every covered page has viewport metadata, shared navigation assets, and the required menu attributes.

- [ ] **Step 4: Perform viewport review**

Inspect each page family at approximately 375px, 768px, and 1280px widths. Confirm:

```text
375px: hamburger is visible, menu opens, forms/cards fit, no body scrollbar caused by layout.
768px: intermediate layout has no clipped actions or overlapping cards.
1280px: desktop navigation/sidebar and existing visual hierarchy remain intact.
```

Use browser devtools or the project’s local PHP server; do not modify external service/game URLs.

- [ ] **Step 5: Review the final diff and report exact verification evidence**

Run `git status --short` and `git diff --stat`, then report changed files, test counts, PHP syntax results, and any viewport limitations that could not be checked automatically.

