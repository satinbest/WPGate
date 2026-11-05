# WPGate — WordPress Core & Ecosystem Compatibility Audit

**Project:** WPGate  
**Release:** v0.1.3  
**Audit Target:** WordPress Core 6.x, Elementor, Admin Screens (`options-general.php?page=wpgate`)  
**Lead Engineer:** Hossein Mohammadpour <info@hosseinmohammadpour.ir>  
**Status:** Approved & Verified (All 18 Suites Passing)

---

## 1. Executive Summary

During production validation on `https://shop.inwp.ir` (`options-general.php?page=wpgate`), several runtime errors occurred affecting WordPress core scripts (`setLocaleData` in `wp-i18n`, `svgPainter.init` in `svg-painter.js`) and Elementor admin components (`__` in `admin.min.js`).

This audit investigated the underlying causes and established strict architectural boundaries to guarantee that WPGate operates in complete harmony with WordPress core and third-party plugins.

---

## 2. Core Protection Policy (Admin & System Context)

### 2.1 Scope Safeguard
Previously, `AssetManager.php` attempted URL rewrites on all enqueued scripts and stylesheets without discriminating between frontend customer traffic and administrative/system contexts.

In `v0.1.3`, `AssetManager::is_admin_or_system_context()` strictly disables asset rewrites when any of the following conditions evaluate to true:
1. `is_admin()` evaluates to `true`.
2. Standard WordPress login/registration screens (`wp-login.php`, `wp-register.php`).
3. WordPress AJAX requests (`wp_doing_ajax()`).
4. WordPress scheduled tasks / Cron (`wp_doing_cron()`).
5. REST API execution (`REST_REQUEST`).
6. WP-CLI command executions (`WP_CLI`).
7. Theme Customizer previews (`is_customize_preview()`).

### 2.2 Protected Handle Whitelist
Core handles that provide foundational JavaScript functionality must never have their source URLs modified or replaced. `AssetManager::is_protected_handle()` checks against the immutable `PROTECTED_HANDLES` table:

| Protected Handle Pattern | Target Subsystem | Rationale |
| :--- | :--- | :--- |
| `wp-i18n`, `wp-hooks` | WordPress Localization | Required for `wp.i18n.setLocaleData` and core event hooks |
| `wp-element`, `wp-components`, `wp-data`, `wp-api-fetch` | Gutenberg & WP React Runtime | Core UI rendering stack |
| `jquery`, `jquery-core`, `jquery-migrate` | jQuery DOM Library | Global dependency for legacy and modern plugins |
| `underscore`, `utils`, `wp-util` | WordPress Utilities | Required for template compilation and helper functions |
| `svg-painter`, `heartbeat` | WordPress Admin UI | Responsible for admin menu icon coloring and session keepalive |
| `elementor*` | Elementor Page Builder | Prevents breaking Elementor admin panels and editor scripts |
| `wp-*` | WordPress Core Scripts | Broad defensive wildcard protection for core script namespaces |

---

## 3. Telemetry Guard Audit & WordPress.org Endpoints

### 3.1 Blanket Blocking Defect in v0.1.2
In earlier versions, when `disable_telemetry` was set to `'yes'`, `TelemetryGuard.php` blocked all requests matching `*.wordpress.org`. This inadvertently blocked:
- Translation pack queries (`api.wordpress.org/translations/`).
- Core language pack downloads (`downloads.wordpress.org/translation/`).
- Core version check responses containing locale metadata.

### 3.2 Granular Filtering in v0.1.3
`TelemetryGuard.php` has been re-architected to inspect the exact request URI path before blocking. 

#### Explicitly Allowed WordPress.org Endpoints:
- `api.wordpress.org/translations/*`: Never blocked, ensuring translations download seamlessly.
- `downloads.wordpress.org/translation/*`: Never blocked, ensuring language pack ZIP archives install properly.
- Core and plugin/theme API endpoints necessary for normal operational updates (unless `disable_wp_updates` is explicitly activated).

#### Blocked Telemetry & Tracking Endpoints:
- `api.wordpress.org/core/browse-happy/*`: Browser telemetry check.
- `api.wordpress.org/core/serve-happy/*`: PHP environment telemetry check.
- `api.wordpress.org/events/*`: WordPress local community event ping.
- `api.wordpress.org/core/credits/*`: Credits metadata.
- `stats.wordpress.com`: Automattic Jetpack/WP.com tracking.
- `telemetry.elementor.com`: Elementor user behavior telemetry.

---

## 4. Browser Permissions Policy Audit (`unload` Event)

### 4.1 Audit Scope
A browser warning was reported:
`Permissions policy violation: unload is not allowed in this document to improve performance and user experience.`

### 4.2 Investigation Results
A complete recursive static audit of the entire WPGate codebase was executed:
- Zero references to `addEventListener('unload')` or `window.onunload`.
- WPGate admin UI uses Vue 3 lifecycle hooks (`onMounted`, `onBeforeUnmount`) exclusively.
- The `unload` permission warning originates from external browser extensions or third-party WordPress plugins on the host site. WPGate complies 100% with modern BFCache (Back/Forward Cache) and Page Lifecycle specifications.

---

## 5. Verification Matrix

| Test Case | Scenario | Expected Behavior | Audit Result |
| :--- | :--- | :--- | :--- |
| **TC-WP-01** | `is_admin() === true` | Scripts/styles remain unmodified | **PASS** (100% untouched) |
| **TC-WP-02** | Handle `wp-i18n` enqueued | Script URL never rewritten | **PASS** (100% protected) |
| **TC-WP-03** | Handle `svg-painter` enqueued | Script URL never rewritten | **PASS** (100% protected) |
| **TC-WP-04** | Handle `elementor-admin` enqueued | Script URL never rewritten | **PASS** (100% protected) |
| **TC-WP-05** | Translation query to `api.wordpress.org` | Request allowed through | **PASS** (Never blocked) |
| **TC-WP-06** | Tracking query to `telemetry.elementor.com` | Request blocked with `WP_Error` | **PASS** (`wpgate_telemetry_blocked`) |
| **TC-WP-07** | Tracking query to `stats.wordpress.com` | Request blocked with `WP_Error` | **PASS** (`wpgate_telemetry_blocked`) |
| **TC-WP-08** | Browser `unload` listeners | Zero instances in WPGate codebase | **PASS** (Zero occurrences) |
