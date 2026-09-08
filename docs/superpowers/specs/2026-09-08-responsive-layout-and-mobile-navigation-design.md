# DevIN Responsive Layout and Mobile Navigation Design

## Goal

Make every user-facing DevIN page responsive across mobile, tablet, and desktop widths, with a consistent hamburger navigation pattern on mobile while preserving the existing blue/white visual identity.

## Scope

The change covers:

- Public pages: `html/index.html`, `html/login.html`, `html/cadastro_pessoa.html`, `html/cadastro_empresa.html`, and `html/politica_privacidade.html`.
- PHP-rendered public/auth pages: `php/login.php`, `php/cadastro_pessoa.php`, `php/cadastro_empresa.php`, `php/recuperacao.php`, `php/redefinir.php`, and `php/cadastrar_curriculo.php`.
- Arcade pages: `html/jogos/pacman.html` and `html/jogos/doom.html`.
- Authenticated dashboards: `php/pessoa.php`, `php/empresa.php`, and `php/adm.php`.
- Existing stylesheets and client-side scripts needed to support the shared navigation and responsive layout.

The request does not change authentication, forms, database behavior, game logic, routes, or page content beyond navigation labels/semantics needed for the responsive layout.

## Design

### 1. Shared public navigation

Public pages that currently expose the main site navigation will use the same semantic structure: a brand link, a navigation container, action links, and a button with `aria-controls`/`aria-expanded` for mobile. The desktop layout remains inline. Below the mobile breakpoint, the button becomes visible and the navigation becomes a hidden panel that opens below the header.

The shared behavior will live in one small script loaded by pages that use the public header. It will:

- Toggle the menu from the hamburger button.
- Keep `aria-expanded` and the open state synchronized.
- Close the menu when a navigation link is selected, when Escape is pressed, or when focus leaves through a safe interaction.
- Avoid changing the page when JavaScript is unavailable by keeping the navigation readable through CSS fallback rules.

The header will retain visible focus styles, a minimum touch target of 44px, and no reliance on hover for navigation.

### 2. Page-specific responsive behavior

- The home page will switch its hero, feature rows, support block, footer columns, and process visualization to single-column or compact layouts without fixed-width overflow. Text line breaks currently forced with `<br>` will not create horizontal scrolling.
- Login and recovery-style form layouts will use a fluid card with safe horizontal padding, and the robot artwork will scale or move below the form on narrow screens.
- Registration pages will keep the two-column form on larger screens and stack fields on smaller screens; the blue artwork panel remains a desktop enhancement and is hidden or reduced on narrow screens without hiding form actions.
- PHP-rendered login, registration, recovery, reset-password, and curriculum forms will reuse the same responsive form rules as their static counterparts, without changing their POST actions, validation, error alerts, CSRF handling, or redirects.
- The privacy page will keep its readable content width and reduce card padding/type scale at small widths.
- Arcade headers and controls will wrap cleanly; emulator frames will maintain their aspect ratio and remain inside the viewport. The Pac-Man board will keep its playable grid in a horizontally scrollable game surface rather than expanding the entire document.

### 3. Dashboard mobile navigation

The existing dashboard sidebar toggle becomes a true mobile navigation control:

- Desktop keeps the current collapsible sidebar behavior.
- At mobile widths, the sidebar is collapsed by default into a compact top bar and opens as a vertical menu when the hamburger is pressed.
- Opening/closing updates `aria-expanded`, preserves the existing local preference without letting a desktop collapsed preference make mobile navigation unusable, and closes the profile dropdown when appropriate.
- Dashboard content grids, cards, tables, modals, and detail panels will use `minmax(0, 1fr)`, wrapping, and safe overflow rules so content stays readable rather than shrinking below usable widths.

### 4. Shared visual rules

Use the existing color palette and typography, with a small set of consistent responsive tokens where helpful:

- Mobile horizontal page padding: 16–20px.
- Minimum interactive target: 44px.
- Breakpoints centered around 720px for mobile navigation and 980px for dashboard/layout changes, while allowing fluid `clamp()` sizing between them.
- Respect `prefers-reduced-motion` for menu and layout transitions.
- Prevent horizontal overflow at the document level except inside explicitly scrollable game surfaces or data regions.

## Files and responsibilities

- Add a reusable navigation script under `js/` for public page menu behavior.
- Update public HTML headers and load the script where needed.
- Extend `css/style.css`, `css/login.css`, `css/cadastrostyle.css`, `css/politica-privacidade.css`, and `css/jogos.css` with responsive header/layout rules.
- Refine `css/dashboard.css` and `js/dashboard.js` for the authenticated mobile sidebar.
- Keep PHP route/authentication code intact except for markup classes/attributes required by the navigation.

## Error handling and accessibility

The menu must remain usable with keyboard navigation and screen readers. The toggle will have an accessible name, `aria-expanded`, and `aria-controls`; focus-visible styles must remain visible. Escape closes an open menu. No layout-critical behavior may depend solely on JavaScript. Existing form validation and server-side security behavior must be unchanged.

## Verification

Verification will include:

1. Static checks that every covered page contains the viewport meta tag, the mobile navigation control where applicable, and the expected script/style references.
2. JavaScript syntax checks for the new/changed client script.
3. PHP syntax checks for modified PHP templates.
4. Browser-based or equivalent viewport checks at approximately 375px, 768px, and 1280px widths for public, arcade, and dashboard page families.
5. Keyboard checks for opening/closing menus and preserving accessible names/expanded state.
