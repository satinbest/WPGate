# WPGate v0.1.0 — Initial Standalone Release Notes

**Release Date:** 2026-11-04  
**Git Tag:** `v0.1.0`  
**Target Platform:** WordPress 5.0+ (PHP 7.4 to 8.4)  
**Author:** satinbest  

---

## 🌟 Executive Summary

We are proud to present **WPGate v0.1.0**, the initial open-source release of the standalone WordPress HTTP Control & Local Asset Manager.

WPGate transforms the legacy Ghateino/Shokrino plugin into an enterprise-ready, standalone PHP module. Designed specifically to ensure uninterrupted website availability during international internet outages, WPGate provides an intelligent outbound HTTP firewall, timeout management, automated CDN fallback rewrites, and privacy protection—without requiring traditional WordPress plugin installation or runtime disk access.

---

## 🚀 Key Highlights in v0.1.0

### 1. Dual Standalone Deployment Architecture
- **Must-Use Plugin (Preferred):** Loads via `wp-content/mu-plugins/wpgate-loader.php` in early WordPress bootstrap before regular plugins execute.
- **Theme Loader:** Direct integration into child theme `functions.php` via `require_once get_stylesheet_directory() . '/wpgate-core/bootstrap.php';`.

### 2. Zero Runtime Disk Modifications
- Completely eliminated legacy runtime `copy()` and `file_put_contents()` routines that caused failures in read-only containers (Docker, Kubernetes) and managed WordPress environments (Kinsta, WP Engine, Pantheon).
- All assets are served statically through `Context::get_asset_url()`.

### 3. Outbound Request Firewall & Timeout Guard
- **Whitelist Mode:** Allows only authorized hosts; drops all unapproved outbound traffic.
- **Blacklist Mode:** Drops explicitly targeted endpoints.
- **Subdomain-Aware Matching:** Prevents hostname prefix spoofing while honoring subdomains and normalizing ports and trailing periods.
- **Timeout Ceiling:** Limits slow external HTTP calls to 3 seconds (configurable 1–30s) to avert PHP-FPM worker pool saturation.

### 4. Security & Privacy Hardening
- **Sensitive Parameter Masking:** URL query arguments containing tokens, API keys, and passwords are automatically redacted (`[REDACTED]`) before diagnostic logging.
- **Safe Defaults:** WordPress core security updates default to enabled (`disable_updates = 'no'`), preserving vital security notifications.
- **Emergency Kill-Switch:** Complete functional deactivation via `define('WPGATE_CORE_DISABLED', true);` in `wp-config.php`.

### 5. Seamless Legacy Migration
- Automatically detects and migrates existing settings from legacy `ghateino_http_control_settings` and logs from `ghateino_http_logs`.
- Recognizes legacy `GHATEINO_CORE_DISABLED` constant for zero-friction upgrades.

---

## 📦 Download Packages

| Package | Filename | Description | Destination |
|---|---|---|---|
| **MU-Plugin Package** | `WPGate-MU-Plugin-v0.1.0.zip` | Ready-to-deploy Must-Use plugin | Extract to `wp-content/` |
| **Theme Module Package** | `WPGate-Theme-Module-v0.1.0.zip` | Standalone theme integration module | Extract to `wp-content/themes/<child-theme>/` |

---

## 🧪 Verification & Test Results
- **Automated Tests Executed:** 34 unit and integration scenarios across class loading, domain matching, timeout enforcement, asset rewriting, and logger security.
- **Success Rate:** 100% (34 Passed, 0 Failed).
- **Linter Status:** Clean syntax across all PHP files under PHP 8.4 CLI.

---

## ⚠️ Notes for Project Manager Review
- Pre-release testing completed successfully.
- Deployment packages verified with zero path restructuring required upon extraction.
- Awaiting final PM sign-off before merging feature branch to `main`.
