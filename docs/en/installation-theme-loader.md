# Installation Guide: Theme Loader

[English](installation-theme-loader.md) | [فارسی](../fa/installation-theme-loader.md)


Use this method when you do not have write access to `wp-content/mu-plugins/` or when managing WordPress via custom child themes.

---

## Prerequisites
- A WordPress child theme (never modify parent themes directly, as updates will overwrite your changes).
- File manager or SFTP access to `wp-content/themes/<your-child-theme>/`.

---

## Step-by-Step Installation

### 1. Download Package
Download `WPGate-Theme-Module-v0.1.1.zip` from the [GitHub Releases](https://github.com/satinbest/WPGate/releases) page.

### 2. Extract into Child Theme
Extract the archive directly into your active child theme directory.

The resulting folder structure should be:
```text
wp-content/themes/your-child-theme/
├── functions.php
├── style.css
└── wpgate-core/
    ├── bootstrap.php
    ├── includes/
    ├── config/
    ├── admin/
    └── assets/
```

### 3. Require Bootstrap in `functions.php`
Open your child theme's `functions.php` file and add the following code snippet at the very top:

```php
<?php
// Load WPGate Standalone Core
if ( file_exists( get_stylesheet_directory() . '/wpgate-core/bootstrap.php' ) ) {
    require_once get_stylesheet_directory() . '/wpgate-core/bootstrap.php';
}
```

---

## Timing Considerations & Limitations

> [!WARNING]
> **Initialization Timing Difference:**
> In WordPress, themes are loaded *after* Must-Use plugins and after standard active plugins have initialized.
> 
> Therefore:
> - Outbound HTTP requests made by third-party plugins during early actions (such as `muplugins_loaded` or `plugins_loaded`) will **not** be intercepted by the theme loader.
> - Outbound HTTP requests executed during `init`, `wp_loaded`, page rendering, cron jobs, and admin requests **will** be intercepted cleanly.
> 
> If you need guaranteed 100% early intercept coverage, deploy via the [MU-Plugin method](installation-mu-plugin.md).

---

## Deactivation & Removal
1. Remove or comment out the `require_once` line in `functions.php`.
2. Delete the `wpgate-core/` folder from your theme directory.
