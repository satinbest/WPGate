# Vue 3 Administration UI — Developer Guide

This document describes the structure, development workflow, and compilation process for the WPGate Vue 3 administration interface.

---

## 🏗️ Architecture & Technology Stack

- **Framework:** Vue 3 (Composition API using `<script setup>` syntax)
- **Bundler:** Vite 6.x
- **Build Output:** Compiled directly into `wpgate-core/admin/dist/`
- **Output Files:**
  - `wpgate-admin.js` (JavaScript bundle, ~144 KB minified / ~49 KB gzipped)
  - `wpgate-admin.css` (Tailored CSS, ~14.5 KB minified / ~3.1 KB gzipped)
- **API Communication:** WordPress REST API (`/wp-json/wpgate/v1`) using standard `fetch` with `X-WP-Nonce` header authentication.

---

## 📁 Source Directory Structure

```text
admin-ui/
├── package.json               # Node dependencies (Vue 3, Vite, @vitejs/plugin-vue)
├── vite.config.js             # Vite build configuration targeting wpgate-core/admin/dist
└── src/
    ├── main.js                # App entrypoint (mounts to #wpgate-admin-app)
    ├── App.vue                # Main layout (tabs, header, language & theme switcher)
    ├── api.js                 # REST client consuming /wpgate/v1 endpoints
    ├── i18n.js                # Bilingual dictionary (English & Persian)
    ├── assets/
    │   └── styles.css         # CSS Variables (theme/RTL/responsive rules)
    └── components/
        ├── TabOverview.vue    # Status card, metrics, and deployment mode
        ├── TabHttpControl.vue # Whitelist, blacklist, presets, and timeout
        ├── TabLocalAssets.vue # Asset rewriting toggles and library cards
        ├── TabWpControls.vue  # Update and telemetry blocking toggles
        ├── TabLogs.vue        # Searchable paginated logs viewer
        ├── TabTools.vue       # JSON export/import and factory reset
        ├── TabAbout.vue       # Developer information and license notices
        ├── ToastNotice.vue    # Floating notification toasts
        └── ConfirmModal.vue   # Reusable confirmation modal
```

---

## 🛠️ Development Workflow

### 1. Prerequisites
- Node.js 18+ (tested on Node.js 20+ and 24+)
- npm 9+

### 2. Installing Dependencies
From the repository root:
```bash
cd admin-ui
npm install
```

### 3. Production Compilation
To compile and minify the assets for release:
```bash
cd admin-ui
npm run build
```

The output will automatically be placed into:
- `mu-plugin-package/mu-plugins/wpgate-core/admin/dist/wpgate-admin.js`
- `mu-plugin-package/mu-plugins/wpgate-core/admin/dist/wpgate-admin.css`

Then synchronize to the theme package:
```powershell
Copy-Item -Path "mu-plugin-package/mu-plugins/wpgate-core/admin/dist/*" -Destination "theme-package/wpgate-core/admin/dist/" -Force
```

---

## 🌐 Localization & Theming

- **Localization:** All UI strings are defined in `src/i18n.js`. To add a string, define it under both `en` and `fa` dictionaries and use `t('key.path')` inside components.
- **RTL / LTR:** Direction is dynamically toggled via `:dir="isRtl ? 'rtl' : 'ltr'"` on the root container.
- **Dark Mode:** Toggled via `:data-theme="theme"` on the root container. CSS custom properties (`--wpgate-bg`, `--wpgate-surface`, etc.) in `styles.css` handle smooth color transitions.
