# Migration Guide: From Ghateino to WPGate

This guide explains how to migrate existing WordPress installations from legacy **Ghateino / Shokrino (v1.2.x)** to **WPGate v0.1.0+**.

---

## 🔄 Automatic Settings Migration

WPGate includes automatic migration logic in `WPGate\Core\Settings`:
- When WPGate boots, it checks if `wpgate_http_control_settings` exists.
- If not found, it automatically reads and imports your existing `ghateino_http_control_settings` (including custom Whitelist, Blacklist, timeout, and font settings).
- Diagnostic logs are likewise migrated from `ghateino_http_logs`.
- You do **not** need to manually re-enter your whitelisted domains or configurations.

---

## ⚡ Backward Compatibility Guarantees

WPGate maintains complete backward compatibility with legacy hooks and constants:

1. **Kill-Switch Constant:**
   If your `wp-config.php` contains:
   ```php
   define( 'GHATEINO_CORE_DISABLED', true );
   ```
   WPGate recognizes it and halts execution immediately. You can transition to `WPGATE_CORE_DISABLED` at your convenience.

2. **Rewrite Filters:**
   Legacy filters `ghateino_local_script_rewrite` and `ghateino_local_style_rewrite` are still evaluated as fallbacks if custom themes hooked into them.

---

## 📋 Migration Steps

### Step 1: Deactivate Legacy Plugin
1. Go to **WordPress Admin > Plugins**.
2. Locate **Ghateino** and click **Deactivate**.
3. (Optional) Delete the `ghateino` plugin folder from `wp-content/plugins/`.

### Step 2: Deploy WPGate (MU-Plugin Mode)
1. Download `WPGate-MU-Plugin-v0.1.0.zip`.
2. Extract the archive into `wp-content/`.
3. Confirm that `wp-content/mu-plugins/wpgate-loader.php` and `wp-content/mu-plugins/wpgate-core/` are in place.

### Step 3: Verify Settings
1. Navigate to **Settings > WPGate**.
2. Confirm that your previously configured Whitelist domains are populated.
3. Click **Save Settings** to confirm the migration.
