# Arabic Theme Asset Audit

## Source inspected

- Theme source directory: `Arabic Theame/`
- Copied public assets: `public/css`, `public/js`, `public/vendor`, `public/assets`

## Public asset paths used by Blade

- `asset('css/bootstrap-rtl-lite.css')` — local Bootstrap-compatible RTL utility layer used because the theme examples reference Bootstrap CDN assets, while application Blade files must use public assets only.
- `asset('css/Theme.css')` — Arabic Theme variables, colors, cards, sidebar/topbar, auth, and dark/light theme tokens.
- `asset('css/Style.css')` — Arabic Theme page/layout component styling.
- `asset('css/phase1-layout.css')` — existing Phase 1 layout refinements retained for compatibility with current cards, stat cards, page headers, and workspace pages.
- `asset('js/app.js')` — Arabic Theme JavaScript for theme/direction/sidebar behavior.
- `asset('js/phase1-layout.js')` — existing Phase 1 shell behavior retained for workspace/admin pages.
- `asset('assets/logos/logo2.svg')` — primary logo used in guest, landing, app, and workspace shells.
- `asset('assets/logos/logo.svg')`, `asset('assets/logos/logo.png')`, and `asset('assets/logos/logo2.png')` remain available public logo alternatives.
- `asset('assets/favicon/*')` — copied favicon set available for future head metadata.

## Theme examples mapped

- `Arabic Theame/Login.html` informed `resources/views/layouts/guest.blade.php` and auth views: auth card, auth cover panel, logo block, Arabic RTL copy, and theme toggle placement.
- `Arabic Theame/index.html` and `Arabic Theame/sidebar.html` informed `resources/views/layouts/app.blade.php` and `resources/views/layouts/company-workspace.blade.php`: `layout-shell`, `sidebar-original`, `content-shell`, `topbar-original`, `topbar-pill`, cards, and KPI/stat presentation.

## Verification expectations

- Blade must not reference `Arabic Theame` directly.
- Blade must not use `@vite`.
- Blade must not require `public/build/manifest.json`.
- Layouts should use `asset()` paths only.
