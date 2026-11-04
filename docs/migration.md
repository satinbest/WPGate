# Deployment & Migration Guide

This guide explains how to migrate, upgrade, or switch between WPGate deployment architectures on WordPress production sites.

---

## 1. Migrating from Theme Loader to Must-Use Plugin

If you initially installed WPGate inside your child theme and now want early-stage execution via `mu-plugins`:

1. Copy the `wpgate-core/` directory to `wp-content/mu-plugins/wpgate-core/`.
2. Copy `wpgate-loader.php` into `wp-content/mu-plugins/wpgate-loader.php`.
3. Remove or comment out the bootstrap call in your child theme's `functions.php`:
   ```php
   // Remove this:
   // require_once get_stylesheet_directory() . '/wpgate-core/bootstrap.php';
   ```
4. Verify by checking **Settings > WPGate**; the deployment badge in the Overview tab will now display **Must-Use Plugin**.
5. All database settings and logs are preserved seamlessly.

---

## 2. Upgrading WPGate to a Newer Version

When a new release of WPGate is issued:

1. Download the latest `WPGate-MU-Plugin-vX.Y.Z.zip` or `WPGate-Theme-Module-vX.Y.Z.zip`.
2. Overwrite the files inside `wp-content/mu-plugins/wpgate-core/` (or theme folder).
3. The database options (`wpgate_http_control_settings`) remain intact.
4. Hard-refresh your browser (`Ctrl+F5` or `Cmd+Shift+R`) to load any updated Vue 3 admin assets.

---

## 3. Safe Deactivation & Removal

To temporarily disable WPGate:
- Add `define('WPGATE_DISABLED', true);` to `wp-config.php`.

To completely remove WPGate:
1. Delete `wp-content/mu-plugins/wpgate-loader.php`.
2. Delete `wp-content/mu-plugins/wpgate-core/`.
3. (Optional) Run the following in WP-CLI to purge saved options:
   ```bash
   wp option delete wpgate_http_control_settings
   wp option delete wpgate_http_logs
   ```
