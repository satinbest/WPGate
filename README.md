<div align="center">

![WPGate Banner](assets/banner-github.png)

# WPGate

**WordPress HTTP Control & Local Asset Manager**

[![CI / Quality & Tests](https://github.com/satinbest/WPGate/actions/workflows/ci.yml/badge.svg)](https://github.com/satinbest/WPGate/actions/workflows/ci.yml)
[![Version](https://img.shields.io/badge/version-0.1.0-blue.svg)](https://github.com/satinbest/WPGate/releases)
[![PHP](https://img.shields.io/badge/PHP-7.4%20|%208.0%20|%208.1%20|%208.2%20|%208.3%20|%208.4-8892BF.svg)](https://www.php.net/)
[![WordPress](https://img.shields.io/badge/WordPress-5.0+-21759B.svg)](https://wordpress.org/)
[![License](https://img.shields.io/badge/license-GPLv2+-green.svg)](LICENSE-NOTICE.md)

[🇮🇷 مستندات فارسی (README-FA.md)](README-FA.md) | [Documentation (docs/)](docs/architecture.md) | [Contributing](CONTRIBUTING.md) | [Changelog](CHANGELOG.md)

</div>

---

## 📖 Introduction

**WPGate** is a standalone, ultra-lightweight, and secure PHP module engineered for WordPress production environments. It provides enterprise-grade outbound HTTP traffic filtering, request timeout enforcement, local CDN asset rewriting, and telemetry mitigation.

Originally refactored and re-architected from the Ghateino/Shokrino concept, WPGate removes all dependencies on standard WordPress plugin activation, eliminates runtime disk I/O, prevents sensitive credential leaks in diagnostic logs, and guarantees rock-solid site uptime even during international internet blackouts or third-party service outages.

---

## 🚀 Key Features

- **Zero Plugin Dependency:** Works seamlessly as a Must-Use plugin (`mu-plugins`) or directly inside a child theme without relying on `wp_options` plugin registration or activation hooks.
- **Smart Outbound HTTP Firewall:**
  - **Whitelist Mode:** Blocks all external outbound requests except explicitly approved hosts.
  - **Blacklist Mode:** Selectively restricts problem hosts or telemetry endpoints.
  - **Disabled Mode:** Passive monitoring with zero request blocking.
- **Subdomain-Aware & Port-Agnostic Matching:** Prevents prefix spoofing (`attacker-domain.com` cannot match `domain.com`) while accurately recognizing subdomains and normalizing trailing dots and ports.
- **Emergency Kill-Switch:** Disable all functional hooks instantly from `wp-config.php` using:
  ```php
  define('WPGATE_CORE_DISABLED', true);
  ```
- **Local Asset Rewriting & CDN Fallbacks:** Automatically replaces remote Google Fonts (Roboto & Vazirmatn), FontAwesome, Swiper, and Dashicons with bundled local assets to prevent render blocking.
- **Mixpanel Neutralization:** Intercepts Mixpanel requests and serves a local stub script (`mixpanel-stub.js`) to avoid client-side JavaScript console errors.
- **Strict Asset Protection:** Unmapped third-party CDN scripts and stylesheets are replaced with non-blocking stubs during network partitions.
- **Outbound Timeout Ceiling:** Clamps slow external HTTP requests to a configurable threshold (default: 3 seconds) to prevent PHP execution exhaustion and gateway timeouts.
- **Privacy & Telemetry Mitigation:** Blocks background data transmission to `wordpress.org` telemetry servers while preserving essential core security updates.
- **Secure Diagnostics Logging:** Automatically redacts sensitive query parameters (e.g., `token`, `api_key`, `password`, `secret`) before recording events to database options. Includes deduplication and automatic retention pruning.
- **Zero Runtime Disk Modification:** Never writes or copies files on disk during HTTP requests, ensuring 100% compatibility with read-only containers (Docker, Kubernetes) and enterprise cloud hosting (Pantheon, Kinsta, WP Engine).

---

## 📋 Requirements & Compatibility

| Component | Minimum Requirement | Recommended |
|---|---|---|
| **PHP** | 7.4 | 8.2, 8.3, 8.4 |
| **WordPress** | 5.0 | 6.5+ |
| **Operating System** | Linux, macOS, Windows | Linux (Ubuntu / Debian / Alpine) |
| **Web Server** | Nginx, Apache, LiteSpeed, Caddy | Nginx / OpenResty |

### Tested Plugin Compatibility
WPGate has been verified to coexist cleanly with:
- **Elementor & Elementor Pro** (Template library and asset overrides handled gracefully)
- **WooCommerce** (PSP gateways and PaymentYar webhooks completely exempt via whitelist presets)
- **Rank Math & Yoast SEO** (Local analytical tools preserved)
- **WP Rocket & LiteSpeed Cache** (Local assets are cleanly combined and minified without CDN blocking)

---

## 📦 Deployment Methods

WPGate supports two independent, mutually exclusive deployment options using the exact same underlying core codebase.

### Method A: Must-Use Plugin (Recommended)

Installing as an MU-Plugin registers hooks in early bootstrap before standard plugins and themes load.

1. Download [`WPGate-MU-Plugin-v0.1.0.zip`](https://github.com/satinbest/WPGate/releases).
2. Extract the archive into your WordPress `wp-content/` directory:
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
3. WordPress automatically loads `wpgate-loader.php`. Navigate to **Settings > WPGate** in your WordPress dashboard.

For detailed instructions, see [docs/installation-mu-plugin.md](docs/installation-mu-plugin.md).

---

### Method B: Child Theme Loader

If you do not have write access to `wp-content/mu-plugins/`, bundle WPGate inside your child theme.

1. Download [`WPGate-Theme-Module-v0.1.0.zip`](https://github.com/satinbest/WPGate/releases).
2. Copy `wpgate-core/` into your active child theme directory:
   ```text
   wp-content/themes/your-child-theme/wpgate-core/
   ```
3. Add the following line at the top of your child theme's `functions.php`:
   ```php
   // Load WPGate Standalone Core
   if ( file_exists( get_stylesheet_directory() . '/wpgate-core/bootstrap.php' ) ) {
       require_once get_stylesheet_directory() . '/wpgate-core/bootstrap.php';
   }
   ```

> [!NOTE]
> **Timing Consideration:** Theme loading occurs when `functions.php` is evaluated. Outbound HTTP requests made by MU-plugins or early standard plugins during initialization cannot be intercepted by the theme loader. Use Method A for complete coverage.

For detailed instructions, see [docs/installation-theme-loader.md](docs/installation-theme-loader.md).

---

## ⚙️ Configuration & Quick Presets

WPGate includes a comprehensive set of curated presets for domestic services:
- **Banking & PSPs:** Shaparak, Behpardakht Mellat, Sadad, Saman Kish (SEP), Asan Pardakht, Parsian.
- **Intermediary Gateways (PaymentYar):** ZarinPal, IDPay, NextPay, Vandar, Zibal, PayPing.
- **BNPL & Installments:** DigiPay, SnappPay, AzkiVam, Lendo, Tara360.
- **SMS Gateways:** Kavenegar, MeliPayamak, IPPanel, FarazSMS, Ghasedak, sms.ir.
- **Logistics & Postal:** Iran Post, Tipax, Chapar, Postex, AloPeyk, Miare.
- **Local CDNs & Cloud:** ArvanCloud, Liara, ParsPack.
- **Local Messengers & APIs:** Bale, Eitaa, Rubika, Splus, Gap, iGap.

For full option specifications, see [docs/configuration.md](docs/configuration.md).

---

## 🔒 Security Architecture

WPGate applies strict security standards:
- **Capability Verification:** All administration screens and mutating actions require `manage_options`.
- **CSRF Defense:** Nonces are enforced for clearing logs (`wpgate_clear_logs`) and resetting defaults (`wpgate_reset_defaults`).
- **Data Protection:** Query parameters containing secrets, passwords, tokens, or hashes are redacted before logging.
- **Safe Defaults:** Core WordPress security update notifications remain enabled unless explicitly toggled by the administrator.
- **Safe Fallbacks:** Missing optional assets will never throw fatal exceptions or interrupt visitor requests.

Review our security policies in [SECURITY.md](SECURITY.md).

---

## 📚 Documentation Index

- [Architecture & Lifecycle](docs/architecture.md)
- [Feature Details](docs/features.md)
- [Configuration Reference](docs/configuration.md)
- [MU Plugin Installation Guide](docs/installation-mu-plugin.md)
- [Theme Loader Installation Guide](docs/installation-theme-loader.md)
- [Troubleshooting & Diagnostics](docs/troubleshooting.md)
- [Frequently Asked Questions (FAQ)](docs/faq.md)
- [Migration from Ghateino / Legacy Plugins](docs/migration.md)
- [Release Process](docs/release-process.md)

---

## 🗺️ Roadmap & Semantic Versioning

WPGate follows [Semantic Versioning 2.0.0](https://semver.org/).
- **Current Version:** `v0.1.0` (Initial standalone release)
- **Planned for v0.2.0:** REST API endpoints for remote status monitoring, WP-CLI command integration (`wp wpgate status`).
- **Planned for v1.0.0:** Automated latency benchmark diagnostics and domain health check probes.

See [ROADMAP.md](ROADMAP.md) for full details.

---

## 🤝 Contributing

Contributions, bug reports, and suggestions are welcome! Please read [CONTRIBUTING.md](CONTRIBUTING.md) for our pull request process and code standards.

---

## 📄 License & Attribution

- **WPGate Core:** Licensed under the [GNU General Public License v2.0 or later (GPLv2+)](LICENSE-NOTICE.md).
- **Attribution:** Based upon the foundational architectural concepts of Ghateino by Shokrino Team (Matin Shokri), re-engineered into an enterprise-ready standalone module by satinbest.
- **Bundled Third-Party Assets:** Dashicons (GPLv2/OFL), Vazirmatn Font (OFL 1.1), FontAwesome (SIL OFL / MIT), Swiper (MIT), Ace Editor (BSD).

See [LICENSE-NOTICE.md](LICENSE-NOTICE.md) for complete legal and copyright declarations.
