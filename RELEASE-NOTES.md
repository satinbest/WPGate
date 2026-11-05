# WPGate v0.1.3 — WordPress Compatibility & JavaScript Runtime Patch Release Notes

[English](RELEASE-NOTES.md) | [فارسی](RELEASE-NOTES-FA.md)

**Release Version:** `v0.1.3`  
**Release Date:** November 6, 2026  
**Author & Lead Architect:** Hossein Mohammadpour (<info@hosseinmohammadpour.ir>)  
**Official Repository:** [https://github.com/satinbest/WPGate](https://github.com/satinbest/WPGate)  
**Release Type:** Critical Bugfix, Compatibility & Stability Patch  

---

## 🌟 Executive Summary

**WPGate v0.1.3** is a critical stability patch resolving runtime JavaScript exceptions, establishing complete compatibility with WordPress Core 6.x and Elementor page builder administration environments, and perfecting Persian UI typography and enterprise layout density.

This release addresses reported field issues on production installations (specifically `tt.getSystemInfo is not a function`, cascading localization errors `wp.i18n.setLocaleData`, Elementor `__` exceptions, and `svgPainter.init` failures), while ensuring that all user settings persist reliably across reloads.

---

## 🚀 Key Fixes & Architectural Improvements in v0.1.3

### 1. API Client Contract & Resilient Frontend Initialization
- **Implemented `api.getSystemInfo()`:** Resolved `tt.getSystemInfo is not a function` by providing a dedicated contract method in `admin-ui/src/api.js`.
- **Sequential Resilient Loading:** In `App.vue:loadData()`, settings and system information are fetched first. Non-critical telemetry metrics (`stats`, `presets`) are loaded via `Promise.allSettled()`, preventing secondary endpoint failures from interrupting administrative dashboard access.
- **Settings Persistence Lifecycle:** Corrected reactive data unpacking in `App.vue` and ensured form changes are only marked clean upon confirmed server responses (`success: true`).

### 2. Strict WordPress Admin & System Context Protection
- **Scope Detection (`AssetManager::is_admin_or_system_context()`):** Bypasses all script and stylesheet rewriting in `wp-admin`, login screens (`wp-login.php`, `wp-register.php`), AJAX requests (`wp_doing_ajax`), Cron jobs (`wp_doing_cron`), REST API calls (`REST_REQUEST`), WP-CLI, and Customizer previews.
- **Protected Core Handles (`AssetManager::is_protected_handle()`):** Establishes an immutable table preventing asset replacement on core script handles (`wp-i18n`, `wp-hooks`, `wp-element`, `wp-components`, `wp-data`, `wp-api-fetch`, `jquery`, `underscore`, `utils`, `svg-painter`, `heartbeat`, and all `elementor*` handles).
- **Eliminated Localization Cascades:** Completely resolved `setLocaleData`, Elementor `__()`, and `svgPainter.init()` crashes.

### 3. Granular Telemetry Guard & Translation Safety
- **Surgical Path Matching:** Replaced blanket `*.wordpress.org` blocking with targeted URI path matching in `TelemetryGuard.php`.
- **Protected Translation Queries:** Explicitly allows queries to `api.wordpress.org/translations/*` and downloads from `downloads.wordpress.org/translation/*`.
- **Targeted Telemetry Blocking:** Accurately intercepts and blocks tracking endpoints (`telemetry.elementor.com`, `stats.wordpress.com`, `/core/browse-happy/`, `/core/serve-happy/`, `/events/`).

### 4. UI Typography & Layout Density Baseline
- Calibrated all typography and component sizing to the exact project baseline:
  - **Main Title:** `20px` (Weight: 700)
  - **Section Title:** `16px` (Weight: 600)
  - **Card / Panel Title:** `14px` (Weight: 600)
  - **Sidebar Item:** `13px` (Weight: 500)
  - **Body Text & Form Labels:** `13px`
  - **Buttons:** `13px` with fixed `36px` height (Compact: `12px` / `30px`)
  - **Inputs & Selects:** `13px` with fixed `38px` height
  - **Help Text & Metadata:** `12px`
  - **Badges & Chips:** `11–12px`
  - **Panel Padding:** `16–20px`
  - **Sidebar Width:** `232px`
- Optimized Persian Vazirmatn rendering with balanced line heights and native RTL directional adjustments.

### 5. Automated Quality Assurance & Verification
- Added **Test Suites 15–18** to `tests/run-tests.php`:
  - Suite 15: WordPress Admin Protection and Scope Detection.
  - Suite 16: Protected WordPress Core and Elementor Handles.
  - Suite 17: Telemetry Guard Specificity and Translation Safety.
  - Suite 18: API Client Contract and Settings Persistence.
- Total test coverage: **56 unit tests across 18 suites with a 100% pass rate**.

---

## 📦 Verified Deliverables & Downloads

| Package File | Size | SHA-256 Checksum | Description |
| :--- | :--- | :--- | :--- |
| `WPGate-MU-Plugin-v0.1.3.zip` | ~1.4 MB | See `SHA256SUMS.txt` | Recommended Must-Use Plugin installation |
| `WPGate-Theme-Module-v0.1.3.zip` | ~1.4 MB | See `SHA256SUMS.txt` | Alternative theme `functions.php` module |
| `WPGate-Complete-Release-v0.1.3.zip` | ~3.0 MB | See `SHA256SUMS.txt` | Complete release bundle with documentation |
