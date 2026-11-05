# Changelog

[English](CHANGELOG.md) | [فارسی](CHANGELOG-FA.md)

All notable changes to the **WPGate** project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

---

## [0.1.3] - 2026-11-06

### Fixed
- **API Client Method Error (Error A):**
  - Resolved `tt.getSystemInfo is not a function` by implementing `api.getSystemInfo()` in `admin-ui/src/api.js`.
  - Refactored `App.vue:loadData()` to load core settings and system info reliably first, then load non-critical metrics (`stats`, `presets`) via `Promise.allSettled()`, ensuring telemetry failures never halt or break the admin interface.
- **WordPress Core & Elementor Runtime Cascade (Errors B, C, D, E):**
  - **Strict Admin & System Context Protection:** Added `AssetManager::is_admin_or_system_context()`, ensuring asset URL rewrites are strictly bypassed inside `wp-admin`, login/registration screens, AJAX (`wp_doing_ajax`), Cron (`wp_doing_cron`), REST API (`REST_REQUEST`), WP-CLI, and Customizer previews.
  - **Immutable Core Handle Protection:** Added `AssetManager::is_protected_handle()` to protect foundational script handles (`wp-i18n`, `wp-hooks`, `wp-element`, `svg-painter`, `jquery`, `underscore`, and `elementor*`) from URL modifications.
  - Resolved secondary cascading errors (`Cannot read properties of undefined (reading 'setLocaleData')`, Elementor localization `__()`, and `svgPainter.init()`).
- **Telemetry Guard Specificity & Translation Safety:**
  - Refactored `TelemetryGuard.php` from blanket domain blocking to granular path matching.
  - Explicitly permits WordPress translation queries (`api.wordpress.org/translations/*`) and language pack downloads (`downloads.wordpress.org/translation/*`), while strictly blocking actual telemetry and tracking endpoints (`telemetry.elementor.com`, `stats.wordpress.com`, `browse-happy`, `serve-happy`, `events`).
- **Settings Persistence Lifecycle:**
  - Resolved reactive state overwrite issue in `App.vue:loadData()` by unpacking `settingsRes.data.settings || settingsRes.data`.
  - Updated `saveSettings()` to retain dirty state unless the backend returns an explicit `success: true` response.

### Changed
- **UI Typography & Layout Density Calibration:**
  - Calibrated typography and component sizing to exact project baseline:
    - Main Page Title: `20px` (Font weight: 700)
    - Section Title: `16px` (Font weight: 600)
    - Card / Panel Title: `14px` (Font weight: 600)
    - Sidebar Item: `13px` (Font weight: 500)
    - Body Text & Form Labels: `13px`
    - Buttons: `13px` with fixed `36px` height (Compact: `12px` / `30px`)
    - Inputs & Selects: `13px` with fixed `38px` height
    - Help Text & Metadata: `12px`
    - Badges & Chips: `11–12px`
    - Panel Padding: `16–20px`
    - Sidebar Width: `232px`
  - Refined Persian typography with Vazirmatn font metrics, balanced line heights, and directional adjustments.

### Added
- **Automated Test Suites 15–18:**
  - Added Test Suite 15: WordPress Admin Protection & Scope Detection (verifies admin, AJAX, and Cron bypass).
  - Added Test Suite 16: Protected Core and Elementor Handles.
  - Added Test Suite 17: Telemetry Guard Specificity & Translation Safety.
  - Added Test Suite 18: API Client Contract & Settings Persistence.
  - Total automated unit test suite now stands at 56 tests across 18 suites (100% passing).
- **Engineering & Audit Reports:**
  - Published `reports/WORDPRESS-COMPATIBILITY-AUDIT.md`.
  - Published `reports/RUNTIME-BUGFIX.md`.
  - Published `reports/UI-IMPROVEMENT.md`.

## [0.1.2] - 2026-11-06

### Added
- **Modern Vertical Responsive Sidebar Navigation:**
  - Replaced horizontal tabs with an ergonomic two-tier vertical sidebar featuring semantic group headers (`Core Navigation` and `System & Insights`).
  - Native RTL right-sidebar and LTR left-sidebar positioning using CSS flexbox and logical properties.
  - Collapsible desktop state (72px compact icon-only mode with tooltips, persisted via `localStorage`).
  - Mobile slide-out drawer with blur backdrop overlay and responsive hamburger toggle for screens < 1024px.
  - Sidebar footer displaying active package version, deployment mode pill (`Must-Use Mode` / `Theme Module`), and collapse toggle.
- **Locally Bundled Offline Vazirmatn Typography:**
  - Embedded local Vazirmatn font files (`Regular 400`, `Medium 500`, `Bold 700` in WOFF2 format) directly inside `admin-ui/src/assets/fonts/`.
  - Base64 inlined via Vite (`assetsInlineLimit: 65536`) directly into `wpgate-admin.css`, guaranteeing 100% offline rendering, zero 404 font errors, zero external CDN calls, and zero CORS blocks.
  - Scoped strictly to `.wpgate-admin-wrap` and `#wpgate-admin-app` to prevent font leakage into WordPress core admin screens.
- **Unified Reusable Component Design System:**
  - `AppSidebar.vue`: Semantic grouped vertical navigation with badges and collapse controls.
  - `AppHeader.vue`: Top navigation bar with breadcrumb path, system shield status pill, language switcher, theme toggle, and primary Save Changes action.
  - `SettingsPanel.vue`: Standardized card containers with header titles, subtitles, status badges, and action slots.
  - `SettingToggle.vue`: Accessible toggle switches with status badges, descriptions, security warning callouts, and nested options.
  - `StatCard.vue`: Telemetry metric cards with tinted icon containers, value counters, and honest informative states when logging is inactive (replacing misleading zeros).
  - `StatusBadge.vue`: Semantic status badges (`success`, `warning`, `danger`, `info`, `primary`, `neutral`) with status dots and dark mode variants.
  - `EmptyState.vue`: Informative empty state graphics with call-to-action prompts.
- **Complete Persian & English Localization:**
  - 100% parity across all labels, tooltips, placeholders, and descriptions.
  - Zero hardcoded English strings in Persian mode.
  - Bidirectional CSS logical properties (`margin-inline-start`, `padding-inline-end`, `border-inline-start`) ensuring natural RTL reading flow.
- **Enhanced Tab Views:**
  - **Overview:** Operational status card, real-time protection matrix, active interceptor policy indicator, and quick-action shortcuts.
  - **HTTP Control:** Replaced basic radio buttons with interactive mode selection cards (Disabled, Allowlist, Blocklist) and organized preset chips.
  - **Local Assets:** Distinct separation between Admin UI bundled Vazirmatn and optional public site visitor font injection.
  - **WordPress Controls:** Grouped updates management with security notices, telemetry prevention, and custom fallback avatar URL.
  - **Diagnostic Logs:** Clean responsive data table, search with icon, action filters, log event details modal, and copyable sanitized URLs.
  - **Tools & Settings:** Export/import JSON backups, factory reset danger zone, system diagnostics specs, and emergency kill-switch guide.
  - **About:** Brand presentation banner, developer attribution card for Hossein Mohammadpour, and licensing notices.
- **Workflow & Productivity Shortcuts:**
  - `Ctrl+S` / `Cmd+S` keyboard shortcut to save settings from anywhere in the application.
  - `Escape` shortcut to dismiss mobile drawer and modals.
  - Real-time unsaved changes detection (dirty state tracking).

---

## [0.1.1] - 2026-11-06

### Fixed
- **Admin UI Asset Resolution (P0 Bug Fix):** Resolved 404 Not Found error on `wpgate-admin.js` and `wpgate-admin.css` caused by incorrect `/assets/` path prefix injection in `AdminController.php`.
- **MIME Type Blocking:** Eliminated browser execution block `MIME type ('text/html') is not executable` by ensuring web server returns 200 OK with correct JavaScript/CSS MIME types.
- **Deterministic Asset Discovery:** Added `locate_admin_dist_asset()` to inspect both primary (`admin/dist/`) and fallback (`assets/admin/dist/`) directories and return exact matching URLs.
- **Admin Diagnostic Safeguard:** Added informative administrator alert and server-rendered view fallback when compiled SPA assets are absent, preventing silent blank screens.
- **MU Plugin Package Structure:** Flattened `mu-plugin-package/` to ensure `wpgate-loader.php` resides directly at the root of the installable ZIP for 1-click extraction into `wp-content/mu-plugins/`.

### Added
- **Automated Test Suite 14:** Added unit and integration tests for asset resolution, null handling, and diagnostic fallbacks (44 total tests, 100% pass).

---

## [0.1.0] - 2026-11-05

### Added
- **Standalone Core Architecture:** Decoupled PHP core running independently of WordPress plugin activation hooks.
- **Dual Deployment Options:**
  - Must-Use Plugin (`wp-content/mu-plugins/wpgate-loader.php` + `wpgate-core/`).
  - Child Theme Loader (`wp-content/themes/<child>/wpgate-core/bootstrap.php`).
- **Vue 3 + Vite Admin Dashboard:**
  - Fast Single Page Application (SPA) powered by Vue 3 Composition API (`<script setup>`).
  - Seamless Dark and Light theme switcher with persistent localStorage preference.
  - Full bidirectional support (Persian RTL and English LTR).
  - Compact production footprint: JS 144 KB (49 KB gzipped), CSS 14 KB (3 KB gzipped).
  - Zero asset leakage: Admin assets are enqueued strictly on WPGate settings screen.
- **WordPress REST API (`wpgate/v1`):**
  - Secure endpoints for settings, paginated logs, presets, and diagnostic metrics.
  - Permissions check: `manage_options` capability and `X-WP-Nonce` header.
- **Smart Outbound HTTP Interceptor:**
  - Whitelist, Blacklist, and Disabled modes.
  - Port-agnostic, scheme-agnostic, and subdomain-aware domain matching.
- **Local Asset Gateway & CDN Fallbacks:**
  - Bundled local libraries: FontAwesome 6 (with v4-shims), Swiper 11, Dashicons, Elementor Icons, and Google Fonts (Vazirmatn & Roboto).
  - Safe local Mixpanel tracking stub script.
  - Strict external asset blocking mode.
- **Timeout Guard:**
  - Configurable maximum request timeout ceiling (1–30 seconds) via `http_request_args`.
- **WordPress Core & Privacy Controls:**
  - Selective update blocking with support for whitelisted domestic marketplace updates.
  - Pingback and background telemetry suppression.
  - Gravatar replacement with local bundled avatar.
- **Secure Diagnostics Logger:**
  - Query parameter credential masking replacing sensitive keys with `[REDACTED]`.
  - In-memory event deduplication within 60-second windows.
  - Automatic retention pruning by days and entry limit.
- **Emergency Kill-Switch:**
  - Functional bypass via `WPGATE_DISABLED` or `WPGATE_CORE_DISABLED` in `wp-config.php`.
- **Automated CLI Test Suite:**
  - 41 automated unit and integration tests covering all subsystems.
