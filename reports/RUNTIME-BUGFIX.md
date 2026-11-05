# WPGate — Runtime Bugfix & Stability Engineering Report

**Project:** WPGate  
**Release:** v0.1.3  
**Date:** 2026-11-06  
**Lead Engineer:** Hossein Mohammadpour <info@hosseinmohammadpour.ir>  
**Status:** Resolved, Tested & Verified

---

## 1. Problem Description & Root Causes

During testing on production site `https://shop.inwp.ir` at `wp-admin/options-general.php?page=wpgate`, several severe runtime errors occurred:

### Error A: `tt.getSystemInfo is not a function`
- **Location:** Minified admin bundle `wpgate-admin.js` in `App.vue:loadData()`.
- **Root Cause:** `App.vue` executed `Promise.all([api.getSettings(), api.getStats(), api.getPresets(), api.getSystemInfo()])`. However, the API client `api.js` had no `getSystemInfo()` method implemented. The resulting unhandled JavaScript exception broke data initialization, leaving the `settings` object unpopulated, triggering the API error toast banner, and leaving form controls in an empty or unstable state.

### Error B, C, D, E: WordPress Core & Elementor Runtime Failures
- **Symptoms:**
  - `Uncaught TypeError: Cannot read properties of undefined (reading 'setLocaleData')`
  - `Uncaught TypeError: Cannot read properties of undefined (reading '__')` in `elementor/assets/js/admin.min.js`
  - `Uncaught TypeError: Cannot read properties of undefined (reading 'init')` in `wp-admin/js/svg-painter.js`
- **Root Cause 1 (Blanket Telemetry Blocking):** `TelemetryGuard.php` blocked all requests to `*.wordpress.org` when telemetry was disabled, cutting off core translation queries (`api.wordpress.org/translations/`).
- **Root Cause 2 (Lack of Admin Protection Scope):** `AssetManager.php` lacked an admin/system boundary check. In environments where WordPress core script handles or host mismatches occurred, script rewrites or request drops occurred. When an inline script crashed trying to call `wp.i18n.setLocaleData`, JavaScript execution halted, cascading failures to Elementor's localization `__()` and WordPress `svgPainter.init()`.

### Settings Persistence Regression
- **Symptom:** Saving settings caused immediate UI reset or failed to reflect persisted changes on page reload.
- **Root Cause:** In `App.vue:loadData()`, response extraction did not unpack `settingsRes.data.settings || settingsRes.data` correctly. In `saveSettings()`, dirty state management cleared without verifying HTTP response status codes.

---

## 2. Implemented Solutions

### 2.1 API Client & Data Loading Architecture (`admin-ui/src/api.js` & `App.vue`)
1. **Added `api.getSystemInfo()`:**
   ```javascript
   async getSystemInfo() {
     try {
       const res = await this.getSettings();
       return {
         success: true,
         data: (res && res.data && res.data.system) ? res.data.system : {},
       };
     } catch (err) {
       return { success: false, data: {} };
     }
   }
   ```
2. **Sequential Resilient Data Fetching in `App.vue`:**
   Settings are fetched first via `await api.getSettings()`. Once core settings and system metadata are secured, secondary telemetry data (`stats`, `presets`) are fetched via `Promise.allSettled()`. Failure of any secondary endpoint will never crash or halt the administration UI.
3. **Persisted Settings Data Binding:**
   Unpacked settings directly into reactive state:
   ```javascript
   const rawSettings = settingsRes.data.settings || settingsRes.data;
   settings.value = reactive({ ...defaults, ...rawSettings });
   ```
4. **Verified Save Routine:**
   `saveSettings()` only marks state clean and updates notifications when `res && res.success` is returned by the REST controller.

### 2.2 Strict Admin Exemption in `AssetManager.php`
Added `is_admin_or_system_context()` and `is_protected_handle()`:
```php
public static function replace_script_src( string $src, string $handle = '' ): string {
    if ( self::is_admin_or_system_context() || self::is_protected_handle( $handle ) ) {
        return $src;
    }
    // ... frontend CDN rewrites only ...
}
```

### 2.3 Surgical Telemetry Filtering in `TelemetryGuard.php`
Replaced blanket domain matching with granular path matching. Endpoints matching `/translations/` are explicitly allowed, while tracking domains (`stats.wordpress.com`, `telemetry.elementor.com`) and browser/environment surveys (`browse-happy`, `serve-happy`) are safely blocked.

---

## 3. Verification & Automated Test Results

The full automated PHP test suite was executed across all packages:
- **Total Test Suites:** 18 Suites
- **Total Unit Tests:** 56 Tests
- **Passed:** 56 (100%)
- **Failed:** 0 (0%)

```text
Test Suite 15: WordPress Admin Protection and Scope Detection
  [PASS] Detects admin context when is_admin is true
  [PASS] Detects AJAX context when wp_doing_ajax is true
  [PASS] Detects Cron context when wp_doing_cron is true
  [PASS] Preserves scripts untouched when in admin context

Test Suite 16: Protected WordPress Core and Elementor Handles
  [PASS] Identifies core handles as strictly protected
  [PASS] Identifies elementor prefixed handles as protected
  [PASS] Never modifies protected script handle regardless of URL

Test Suite 17: Telemetry Guard Specificity and Translation Safety
  [PASS] Allows WordPress core translation endpoints through when telemetry guard enabled
  [PASS] Blocks Elementor telemetry endpoint when telemetry guard enabled
  [PASS] Blocks stats.wordpress.com tracking requests

Test Suite 18: API Client Contract and Settings Persistence
  [PASS] GET /wpgate/v1/settings returns system object required by api.js
  [PASS] POST /wpgate/v1/settings preserves saved values accurately
```
