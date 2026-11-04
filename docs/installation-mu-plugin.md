# Installation Guide: Must-Use Plugin (MU-Plugin)

This is the **preferred and recommended** deployment method for WPGate in production WordPress environments.

---

## Why Must-Use Plugin?

Must-Use plugins (`mu-plugins`) are executed automatically by WordPress very early in the bootstrap sequence, prior to active themes and standard plugins. This guarantees that:
- WPGate registers its HTTP filters before Elementor, Rank Math, WooCommerce, or any other plugin triggers outbound HTTP calls.
- The module cannot be accidentally disabled or deactivated by another site administrator from the Plugins dashboard.
- Outbound timeout protection is enforced for all background cron jobs and early REST requests.

---

## Step-by-Step Installation

### 1. Download Package
Download the latest `WPGate-MU-Plugin-v0.1.0.zip` from the [GitHub Releases](https://github.com/satinbest/WPGate/releases) page.

### 2. Extract Files
Extract the archive directly into your WordPress `wp-content/` directory.

The resulting folder structure on your web server must be:
```text
wp-content/
└── mu-plugins/
    ├── wpgate-loader.php
    └── wpgate-core/
        ├── bootstrap.php
        ├── includes/
        ├── config/
        ├── admin/
        └── assets/
```

> [!NOTE]
> If the `mu-plugins` folder does not already exist inside `wp-content`, your unzip utility will automatically create it. Ensure the folder permissions are set to `755` (or appropriate for your web server user, e.g., `www-data`).

### 3. Verify Activation
1. Log in to your WordPress Administration Dashboard.
2. Navigate to **Plugins > Must-Use**. You will see **WPGate Loader (Must-Use)** listed.
3. Navigate to **Settings > WPGate** to configure your outbound firewall rules.

---

## Upgrading MU-Plugin

To upgrade WPGate to a newer version:
1. Download the new release ZIP.
2. Replace the contents of `wp-content/mu-plugins/wpgate-core/` and the `wpgate-loader.php` file.
3. Refresh your WordPress admin dashboard. Settings are preserved in `wp_options`.

---

## Deactivation & Removal

To deactivate or remove WPGate:
1. Delete `wp-content/mu-plugins/wpgate-loader.php`.
2. Delete the `wp-content/mu-plugins/wpgate-core/` directory.
3. No lingering database tables remain.
