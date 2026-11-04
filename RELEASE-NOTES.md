# WPGate v0.1.0 — Initial Standalone Release Notes

**Release Version:** `v0.1.0`  
**Release Date:** November 2026  
**Author & Architect:** Hossein Mohammadpour (<info@hosseinmohammadpour.ir>)  
**Official Repository:** [https://github.com/satinbest/WPGate](https://github.com/satinbest/WPGate)

---

## 🌟 Executive Summary

**WPGate** is an independent, enterprise-grade, standalone WordPress software product designed to ensure uninterrupted website availability, privacy, and responsiveness during international internet blackouts, external CDN outages, or third-party service degradation.

WPGate operates completely independently of normal WordPress plugin activation mechanisms. It features a modern **Vue 3 + Vite** single-page administration interface communicating with a robust **WordPress REST API (`wpgate/v1`)**, requires zero runtime disk writes, and introduces negligible execution overhead.

---

## 🚀 Key Highlights & New Architecture

### 1. Dual Deployment Architecture
- **Must-Use Plugin (Recommended):** Deployed into `wp-content/mu-plugins/` for early-stage network interception during `muplugins_loaded`.
- **Child Theme Loader:** Deployed directly within a child theme's `wpgate-core/` and bootstrapped from `functions.php`.

### 2. Vue 3 + Vite Administration Dashboard
- Built with **Vue 3 Composition API (`<script setup>`)** and compiled with Vite.
- Instant toggle between **Dark Mode** and **Light Mode**.
- Native bidirectional support (**Persian RTL** and **English LTR**).
- Ultra-compact bundle size: Minified JS **144.09 KB** (only 49.23 KB gzipped), CSS **14.57 KB** (only 3.11 KB gzipped).
- Zero frontend asset leakage: Vue assets are enqueued strictly on `settings_page_wpgate` and never on visitor-facing pages.

### 3. WordPress REST API Controller (`wpgate/v1`)
- `GET /wpgate/v1/settings` & `POST /wpgate/v1/settings`: Settings read/write with validation.
- `GET /wpgate/v1/logs` & `POST /wpgate/v1/logs/clear`: Searchable, paginated audit logs.
- `GET /wpgate/v1/presets`: Domain preset library retrieval.
- `GET /wpgate/v1/stats`: Live gateway metrics and status summary.
- `POST /wpgate/v1/settings/reset`: Factory reset capability.
- Security: Strictly restricted to `current_user_can('manage_options')` with REST nonce verification (`X-WP-Nonce`).

### 4. Smart Outbound HTTP Interceptor
- Three operational modes: **Whitelist**, **Blacklist**, and **Disabled**.
- Port-agnostic and subdomain-aware matching with strict prevention of substring spoofing attacks.

### 5. Local Asset Gateway & CDN Fallbacks
- Automatic local rewriting for FontAwesome, Google Fonts (Vazirmatn & Roboto), Swiper, Elementor Icons, and Dashicons.
- Non-blocking stubs for uncached third-party CDN assets.
- Mixpanel analytics stub (`mixpanel-stub.js`) preventing client-side console errors.

### 6. Emergency Kill-Switch
- Hard operational bypass via constant in `wp-config.php`:
  ```php
  define( 'WPGATE_DISABLED', true );
  define( 'WPGATE_CORE_DISABLED', true );
  ```

### 7. Secure Logger & Sensitive Data Masking
- Automated sanitization replacing query values for `token`, `password`, `key`, `secret`, `signature`, and `auth` with `[REDACTED]`.
- Automatic deduplication and configurable retention limits (1–30 days, 100–1000 entries).

---

## 📦 Package Artifacts

1. **`WPGate-MU-Plugin-v0.1.0.zip`**: Must-Use plugin package.
2. **`WPGate-Theme-Module-v0.1.0.zip`**: Child theme loader package.
3. **`WPGate-Complete-Release-v0.1.0.zip`**: Comprehensive package with both deployment options, documentation, and source code.
