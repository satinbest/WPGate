# WPGate v0.1.1 — Maintenance & Bugfix Release Notes

**Release Version:** `v0.1.1`  
**Release Date:** November 6, 2026  
**Author & Lead Architect:** Hossein Mohammadpour (<info@hosseinmohammadpour.ir>)  
**Official Repository:** [https://github.com/satinbest/WPGate](https://github.com/satinbest/WPGate)  
**Release Type:** Critical Bugfix / Release Blocker Resolution  

---

## 🌟 Executive Summary

**WPGate v0.1.1** resolves a critical asset resolution bug (P0) where the Vue 3 administration single-page interface failed to load on production installations due to a 404 Not Found error and subsequent browser MIME-type execution block.

This release introduces deterministic asset location (`locate_admin_dist_asset`), dual asset directory availability (`admin/dist/` and `assets/admin/dist/`), an administrator diagnostic fallback notice, flattened MU-Plugin archive packaging, and 44 automated tests.

---

## 🛠️ Critical Bug Fixes in v0.1.1

1. **Admin UI 404 & MIME-Type Block Resolution:**
   - Fixed incorrect `/assets/` prefix injection in `AdminController.php` which caused requests to `wpgate-core/assets/admin/dist/wpgate-admin.js` to 404 on web servers.
   - Assets are now dynamically resolved to their exact physical and public URL paths.
   - Dual-distributed assets guarantee that requests to either URL pattern resolve with HTTP 200 and valid MIME types (`application/javascript` / `text/css`).

2. **Administrator Diagnostic Alert & Safe Fallback:**
   - If compiled SPA assets are missing from disk (e.g., partial manual upload), `render_settings_page()` displays a clear warning banner with exact missing paths and falls back to the server-rendered settings view. Administrators are never left with an empty blank screen.

3. **Flattened MU-Plugin Archive Packaging:**
   - The MU-Plugin ZIP archive structure has been corrected so `wpgate-loader.php` and `wpgate-core/` reside directly at the archive root. Extracting the archive directly into `wp-content/mu-plugins/` works out-of-the-box with zero folder reorganization.

---

## 📦 Verified Release Packages

| Package Filename | Role & Target Directory | Recommended For |
|---|---|---|
| **`WPGate-MU-Plugin-v0.1.1.zip`** | Extract directly into `wp-content/mu-plugins/` | **All Production Sites (Recommended)** |
| **`WPGate-Theme-Module-v0.1.1.zip`** | Extract `wpgate-core/` into active child theme | Sites without `mu-plugins` write access |
| **`WPGate-Complete-Release-v0.1.1.zip`** | Comprehensive archive with full source code & docs | Developers & Offline Audits |

---

## 📥 Installation & Upgrade Instructions

### Fresh Installation (MU-Plugin)
1. Download `WPGate-MU-Plugin-v0.1.1.zip`.
2. Upload and extract directly into `wp-content/mu-plugins/`:
   ```text
   wp-content/mu-plugins/
   ├── wpgate-loader.php
   └── wpgate-core/
       ├── bootstrap.php
       ├── includes/
       ├── config/
       ├── admin/
       │   └── dist/
       └── assets/
   ```
3. Open WordPress dashboard: **Settings > WPGate**.
4. The Vue 3 dashboard mounts immediately.

### Updating from v0.1.0 to v0.1.1
1. Download `WPGate-MU-Plugin-v0.1.1.zip`.
2. Overwrite `wp-content/mu-plugins/wpgate-loader.php` and `wp-content/mu-plugins/wpgate-core/`.
3. Perform a hard browser cache refresh (`Ctrl+F5` on Windows/Linux or `Cmd+Shift+R` on Mac) to ensure the browser loads the new `?ver=0.1.1` assets.

### Emergency Kill-Switch
If you ever need to completely disable all WPGate hooks, add to `wp-config.php`:
```php
define( 'WPGATE_DISABLED', true );
```

---

## 🧪 Quality Assurance & Test Verification

- **Automated CLI Tests:** 44 Tests Executed, **44 Passed, 0 Failed (100%)**.
- **PHP Syntax:** 33 PHP Files Validated with `php -l` (100% OK).
- **Bundle Footprint:** JS 144.09 KB (49.23 KB gzipped), CSS 14.57 KB (3.11 KB gzipped).
- **Security & Privacy:** 100% Query parameter credential redaction verified.
