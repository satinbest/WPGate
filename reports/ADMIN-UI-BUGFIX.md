# WPGate v0.1.1 — Critical Admin UI Asset Loading Bugfix Report

**Priority:** P0 — Critical Bug / Release Blocker  
**Audit Date:** 2026-11-06  
**Author & Lead Architect:** Hossein Mohammadpour (<info@hosseinmohammadpour.ir>)  
**Affected Production Environment:** `https://shop.inwp.ir` (WordPress 6.x)  
**Affected Screen:** `wp-admin/options-general.php?page=wpgate`  
**Fixed Version:** `v0.1.1`  

---

## 1. Incident Description & Symptoms

When accessing the WPGate administration screen on production/staging sites (`options-general.php?page=wpgate`), the page rendered as an empty blank canvas. The browser developer console reported:

```text
Refused to apply style from: 'https://shop.inwp.ir/wp-content/mu-plugins/wpgate-core/assets/admin/dist/wpgate-admin.css?ver=0.1.0'
Reason: MIME type ('text/html') is not a supported stylesheet MIME type.
wpgate-admin.js:1 Failed to load resource: server responded with status 404.
Refused to execute script from: 'https://shop.inwp.ir/wp-content/mu-plugins/wpgate-core/assets/admin/dist/wpgate-admin.js?ver=0.1.0'
Reason: MIME type ('text/html') is not executable.
Permissions policy violation: unload
```

---

## 2. Root Cause Analysis

Investigation revealed two compounding defects:

1. **Path-to-URL Resolution Mismatch in PHP (`AdminController.php` & `Context.php`):**
   - The compiled Vite production bundles were output to:
     `wpgate-core/admin/dist/wpgate-admin.js`
     `wpgate-core/admin/dist/wpgate-admin.css`
   - However, `AdminController::enqueue_admin_assets()` enqueued them via:
     `Context::get_asset_url( 'admin/dist/wpgate-admin.css' )`
   - In `Context::get_asset_url( $path )`, the method automatically injected `/assets/`:
     `self::get_base_url() . '/assets/' . ltrim( $path, '/' );`
   - This caused WordPress to request:
     `.../wpgate-core/assets/admin/dist/wpgate-admin.css` (WITH `/assets/`)
   - Because the physical files were located at `.../wpgate-core/admin/dist/` (WITHOUT `/assets/`), the web server (Nginx/OpenResty) returned a **404 Not Found HTML page**.
   - Because the 404 response has Content-Type `text/html`, modern browsers blocked execution:
     - The stylesheet was rejected (`MIME type ('text/html') is not a supported stylesheet MIME type`).
     - The script execution was aborted (`MIME type ('text/html') is not executable`).
     - Consequently, the Vue application failed to mount, leaving `#wpgate-admin-app` blank.

2. **Package Folder Nesting in Previous MU Plugin Archive:**
   - The previous packaging script compressed `mu-plugin-package/*` which had an extra nested `mu-plugins/` directory, causing extracts into `wp-content/mu-plugins/` to create `wp-content/mu-plugins/mu-plugins/wpgate-loader.php`.

3. **Chromium `unload` Warning Assessment:**
   - The reported warning `Permissions policy violation: unload` is an advisory deprecation warning emitted by modern Chromium browsers when core WordPress or browser extensions access the legacy `unload` event. It had zero role in the blank interface.

---

## 3. Affected Files

| File Path | Nature of Modification |
|---|---|
| `mu-plugin-package/wpgate-core/admin/AdminController.php` | Implemented deterministic `locate_admin_dist_asset()` and safe fallback diagnostics |
| `mu-plugin-package/wpgate-core/includes/Context.php` | Added `get_module_url()` and corrected asset URL resolution |
| `mu-plugin-package/wpgate-core/bootstrap.php` | Version bump to `v0.1.1` |
| `mu-plugin-package/wpgate-loader.php` | Version bump to `v0.1.1` |
| `mu-plugin-package/wpgate-core/includes/RestController.php` | Version bump to `v0.1.1` |
| `theme-package/wpgate-core/...` | Synchronized all core files and compiled assets |
| `admin-ui/package.json` | Version bump to `v0.1.1` |
| `admin-ui/vite.config.js` | Updated outDir to flattened package path |
| `admin-ui/src/api.js` & `App.vue` & `TabAbout.vue` | Version bump to `v0.1.1` |
| `tests/run-tests.php` | Added Test Suite 14 (3 new automated tests) |

---

## 4. Fix Implemented

### A. Deterministic Asset Location (`locate_admin_dist_asset`)
In `AdminController.php`:
```php
public static function locate_admin_dist_asset( string $filename ): ?array {
    $base_dir = Context::get_base_dir();
    $base_url = Context::get_base_url();

    $candidates = [
        [
            'path' => $base_dir . '/admin/dist/' . $filename,
            'url'  => $base_url . '/admin/dist/' . $filename,
        ],
        [
            'path' => $base_dir . '/assets/admin/dist/' . $filename,
            'url'  => $base_url . '/assets/admin/dist/' . $filename,
        ],
    ];

    foreach ( $candidates as $candidate ) {
        if ( file_exists( $candidate['path'] ) && filesize( $candidate['path'] ) > 0 ) {
            return $candidate;
        }
    }

    return null;
}
```

### B. Dual Asset Placement
Compiled production assets (`wpgate-admin.js` and `wpgate-admin.css`) are now automatically placed in:
1. `wpgate-core/admin/dist/`
2. `wpgate-core/assets/admin/dist/`
This guarantees that any URL requested by current or cached browser sessions resolves with HTTP 200 and valid MIME type (`application/javascript` / `text/css`).

### C. Administrator Diagnostic Alert & Safe Fallback
If compiled SPA assets are ever missing from disk (e.g., partial manual upload):
1. `AdminController` refuses to enqueue nonexistent URLs (preventing 404s and browser console errors).
2. `render_settings_page()` renders an informative WordPress administrator alert explaining which paths were checked.
3. It seamlessly renders the server-side configuration template below the alert, ensuring the administrator is **never locked out of settings**.

### D. Direct Root-Level MU Plugin Packaging
The MU Plugin release ZIP structure was flattened to ensure `wpgate-loader.php` is located directly at the root of the ZIP file:
```text
WPGate-MU-Plugin-v0.1.1.zip
├── wpgate-loader.php
└── wpgate-core/
    ├── bootstrap.php
    ├── includes/
    ├── config/
    ├── admin/
    │   └── dist/
    │       ├── wpgate-admin.js
    │       └── wpgate-admin.css
    └── assets/
        └── admin/dist/
            ├── wpgate-admin.js
            └── wpgate-admin.css
```

---

## 5. Verification & Tests Performed

### Automated Test Suite Execution
Executed `php tests/run-tests.php`:
- **Test Suite 1 to 13:** 41/41 unit & integration tests passed.
- **Test Suite 14 (Admin UI Asset Resolution & Diagnostics):**
  - `[PASS]` Locates admin JS and CSS assets without incorrect `/assets/` duplication.
  - `[PASS]` Returns null for nonexistent asset files without throwing errors.
  - `[PASS]` Settings page renders diagnostic notice if admin JS asset is missing.
- **Total:** **44 Passed, 0 Failed (100% PASS)**.

### Build Verification
- Vite compiled in 811ms:
  - `wpgate-admin.js`: 144.09 KB (49.23 KB gzipped) — strictly < 150 KB.
  - `wpgate-admin.css`: 14.57 KB (3.11 KB gzipped) — strictly < 30 KB.

---

## 6. Before / After Behavior

| Scenario | Before (v0.1.0) | After (v0.1.1) |
|---|---|---|
| Admin Page Open | Blank screen | Modern Vue 3 SPA mounted instantly |
| CSS Request | 404 Not Found (`text/html`) | 200 OK (`text/css`, 14.5 KB) |
| JS Request | 404 Not Found (`text/html`) | 200 OK (`application/javascript`, 144 KB) |
| Missing Assets (Partial upload) | Silent failure / blank page | Warning notice + server-rendered fallback view |
| MU Plugin Zip Extract | Nested subfolder risk | Direct extraction into `wp-content/mu-plugins/` |

---

## 7. Remaining Limitations & Environment Scope

- **Live Production Deployment:** The fix was developed, built, and verified in the local workspace test harness. Applying this fix to `https://shop.inwp.ir` requires extracting the generated `WPGate-MU-Plugin-v0.1.1.zip` directly into `wp-content/mu-plugins/` on the server and performing a hard browser cache refresh (`Ctrl+F5` / `Cmd+Shift+R`).
