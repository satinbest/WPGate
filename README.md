<div align="center">

![WPGate Banner](assets/banner-github.png)

# WPGate

**Enterprise Outbound HTTP Firewall & Local Asset Gateway for WordPress**

[![CI / Quality & Tests](https://github.com/satinbest/WPGate/actions/workflows/ci.yml/badge.svg)](https://github.com/satinbest/WPGate/actions/workflows/ci.yml)
[![Version](https://img.shields.io/badge/version-0.1.0-blue.svg)](https://github.com/satinbest/WPGate/releases)
[![PHP](https://img.shields.io/badge/PHP-7.4%20|%208.0%20|%208.1%20|%208.2%20|%208.3%20|%208.4-8892BF.svg)](https://www.php.net/)
[![WordPress](https://img.shields.io/badge/WordPress-5.0+-21759B.svg)](https://wordpress.org/)
[![Admin UI](https://img.shields.io/badge/Admin%20UI-Vue%203%20%2B%20Vite-42b883.svg)](https://vuejs.org/)
[![License](https://img.shields.io/badge/license-MIT%20%2F%20GPLv2+-green.svg)](LICENSE-NOTICE.md)

[🇮🇷 مستندات فارسی (README-FA.md)](README-FA.md) | [Admin Interface Guide](docs/admin-interface.md) | [Vue 3 UI Dev Guide](docs/vue-development.md) | [Performance Report](PERFORMANCE-REPORT.md) | [Changelog](CHANGELOG.md)

</div>

---

## 📖 Introduction

**WPGate** is an independent, enterprise-grade, lightweight WordPress software product designed by **Hossein Mohammadpour**. It empowers site owners, sysadmins, and developers to safeguard website availability, privacy, and responsiveness during international connectivity disruptions, CDN network outages, or third-party API downtime.

Unlike traditional WordPress plugins, WPGate operates autonomously without standard plugin registration or database activation dependencies. It features a modern **Vue 3 + Vite** administration single-page application communicating via a secure WordPress REST API (`wpgate/v1`), operates with zero runtime disk writes, and guarantees sub-millisecond execution overhead.

---

## 🚀 Key Capabilities

- **Zero Plugin Dependency:** Runs flawlessly as a Must-Use plugin (`mu-plugins/`) or directly within a child theme without activation hooks or database plugin state entries.
- **Modern Vue 3 + Vite Administration:**
  - Fast Single Page Application (SPA) powered by Vue 3 Composition API (`<script setup>`).
  - Seamless Light and Dark mode switching with persistent user preference.
  - Full bidirectional support (English LTR and Persian RTL).
  - Micro bundle footprint: Minified JavaScript < 150 KB (only ~49 KB gzipped) and CSS < 30 KB (only ~3 KB gzipped).
  - Zero asset leakage: Admin assets are strictly enqueued only on the WPGate settings screen and never loaded on public visitor requests.
- **WordPress REST API (`wpgate/v1`):**
  - Robust endpoints for settings management, paginated diagnostic log viewing, live stats, and preset synchronization.
  - Fully secured with `manage_options` capability checks and WordPress REST nonces (`X-WP-Nonce`).
- **Intelligent Outbound HTTP Interceptor:**
  - **Whitelist Mode:** Restricts outbound HTTP/HTTPS requests to approved, verified hosts only.
  - **Blacklist Mode:** Selectively blocks problematic external trackers or failing external APIs.
  - **Disabled Mode:** Passive monitoring with zero request alteration.
- **Port-Agnostic & Subdomain-Aware Matching:** Normalizes ports and trailing dots, accurately matching subdomains (e.g., `api.zarinpal.com` matching `zarinpal.com`) while preventing substring spoofing (`attacker-zarinpal.com`).
- **Emergency Kill-Switch:** Instantly bypass all filters and hooks directly from `wp-config.php`:
  ```php
  define( 'WPGATE_DISABLED', true );
  // Or backward-compatible:
  define( 'WPGATE_CORE_DISABLED', true );
  ```
- **Local Asset Gateway & CDN Fallbacks:** Automatically replaces remote Google Fonts (Roboto & Vazirmatn), FontAwesome 6, Swiper 11, Elementor Icons, and Dashicons with bundled local assets to eliminate render blocking.
- **Outbound Timeout Ceiling:** Clamps slow external HTTP requests to a configurable limit (1–30 seconds) to avoid PHP worker exhaustion and 504 Gateway Timeouts.
- **Privacy & Telemetry Mitigation:** Blocks background data transmission to telemetry endpoints while preserving essential core security updates.
- **Diagnostic Logging & Sensitive Data Masking:** Logs external HTTP requests with automatic query parameter redaction (`[REDACTED]`) for tokens, passwords, nonces, and API keys.
- **Zero Runtime Disk Modification:** Performs 100% of runtime tasks in memory and core WordPress options, ensuring full compatibility with read-only containers (Docker, Kubernetes) and enterprise cloud hosting.

---

## 📋 Requirements & Compatibility

| Component | Minimum Requirement | Recommended |
|---|---|---|
| **PHP** | 7.4 | 8.2, 8.3, 8.4 |
| **WordPress** | 5.0 | 6.5+ |
| **Node.js** *(Dev only)* | 18+ | 20+ / 24+ |
| **Operating System** | Linux, macOS, Windows | Linux (Ubuntu / Debian / Alpine) |
| **Web Server** | Nginx, Apache, LiteSpeed, Caddy | Nginx / OpenResty |

---

## 📦 Deployment Methods

WPGate offers two standard deployment methods using the same shared core:

### Method A: Must-Use Plugin (Recommended)

Installs in `wp-content/mu-plugins/` to guarantee early execution before standard plugins load.

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
3. WordPress automatically executes `wpgate-loader.php`. Navigate to **Settings > WPGate** in your WordPress dashboard.

For step-by-step instructions, see [docs/installation-mu-plugin.md](docs/installation-mu-plugin.md).

---

### Method B: Child Theme Loader

If access to `mu-plugins` is restricted, embed WPGate directly into your child theme.

1. Download [`WPGate-Theme-Module-v0.1.0.zip`](https://github.com/satinbest/WPGate/releases).
2. Place `wpgate-core/` inside your active child theme folder:
   ```text
   wp-content/themes/your-child-theme/wpgate-core/
   ```
3. Include the bootstrap file in your child theme's `functions.php`:
   ```php
   // Initialize WPGate Standalone Core
   if ( file_exists( get_stylesheet_directory() . '/wpgate-core/bootstrap.php' ) ) {
       require_once get_stylesheet_directory() . '/wpgate-core/bootstrap.php';
   }
   ```

For step-by-step instructions, see [docs/installation-theme-loader.md](docs/installation-theme-loader.md).

---

## ⚙️ Domestic Presets Library

WPGate includes verified, curated presets for domestic infrastructure:
- **PSPs & Shaparak:** PEP, SEP, PNA, PEC, Sadad, Omid, Fanava, SepehrPay, IranKish, Behpardakht, AsanPardakht.
- **Intermediary Gateways (PaymentYar):** ZarinPal, NextPay, IDPay, Pay.ir, Vandar, Jibit, Zibal, PayPing.
- **BNPL & Installments:** DigiPay, AzkiVam, TorobPay, SnappPay, Lendo, Tara360.
- **SMS Infrastructure:** Kavenegar, MeliPayamak, IPPanel, FarazSMS, Ghasedak, sms.ir.
- **Logistics & Postal:** Iran Post, Tipax, Chapar, Postex, AloPeyk, Miare, Tapin.
- **Cloud & Hosting:** ArvanCloud, Liara, ParsPack, Darkube, Hamravesh, Sotoon.
- **Messengers & APIs:** Bale, Eitaa, Rubika, Splus, Gap, iGap.

---

## 🔒 Security & Privacy Architecture

- **Strict Capability Enforcement:** All REST routes and admin settings strictly enforce `manage_options`.
- **Nonce Authentication:** Built-in REST nonces (`X-WP-Nonce`) protect all mutating endpoints.
- **Automatic Secret Redaction:** Sensitive parameters (`token`, `api_key`, `key`, `password`, `secret`, `signature`, `auth`, `nonce`) are replaced with `[REDACTED]` prior to logging.
- **Safe Update Defaults:** WordPress security updates remain accessible by default.

---

## 📚 Documentation Index

- [Admin Interface Guide](docs/admin-interface.md)
- [Vue 3 Development & Build Guide](docs/vue-development.md)
- [Architecture & Runtime Lifecycle](docs/architecture.md)
- [Performance & Benchmark Report](PERFORMANCE-REPORT.md)
- [Brand & Identity Audit](reports/BRAND-AUDIT.md)
- [Configuration Reference](docs/configuration.md)
- [Features Reference](docs/features.md)
- [MU Plugin Installation Guide](docs/installation-mu-plugin.md)
- [Theme Loader Installation Guide](docs/installation-theme-loader.md)
- [Troubleshooting & Diagnostics](docs/troubleshooting.md)
- [Frequently Asked Questions (FAQ)](docs/faq.md)
- [Release Process](docs/release-process.md)

---

## 👨‍💻 Author & Lead Developer

- **Lead Developer & Architect:** [Hossein Mohammadpour](https://hosseinmohammadpour.ir)
- **Contact & Support:** [info@hosseinmohammadpour.ir](mailto:info@hosseinmohammadpour.ir)
- **Official Repository:** [https://github.com/satinbest/WPGate](https://github.com/satinbest/WPGate)

---

## 📄 License & Open-Source Declarations

- **WPGate Software:** Released under the [MIT License](LICENSE-NOTICE.md) and fully compatible with WordPress [GPLv2+](LICENSE-NOTICE.md).
- **Bundled Open-Source Assets:**
  - Vazirmatn Font (SIL Open Font License 1.1)
  - FontAwesome Free (SIL OFL 1.1 & MIT)
  - Swiper JS/CSS (MIT License)
  - Dashicons (GPLv2 & OFL)
  - Elementor Icons (eicons MIT)

See [LICENSE-NOTICE.md](LICENSE-NOTICE.md) for complete details.
