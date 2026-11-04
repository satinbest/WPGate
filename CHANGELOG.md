# Changelog

All notable changes to the WPGate project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [0.1.0] - 2026-11-04

### Added
- **Standalone Modular Architecture:** Re-architected monolithic plugin into a clean, object-oriented PHP library under the `WPGate\Core` namespace.
- **Dual Deployment Loaders:**
  - Must-Use Plugin loader (`wpgate-loader.php`) for early bootstrap intercept before standard plugins load.
  - Theme loader for clean embedding inside child theme `functions.php`.
- **Emergency Kill-Switch:** Complete functional bypass via `WPGATE_CORE_DISABLED` (or legacy `GHATEINO_CORE_DISABLED`) in `wp-config.php`.
- **Automated Settings Migration:** Built-in seamless migration from legacy `ghateino_http_control_settings` and `ghateino_http_logs`.
- **Outbound HTTP Firewall:** Subdomain-aware domain matching engine with Whitelist and Blacklist modes.
- **Request Timeout Ceiling:** Clamps excessive outbound request timeouts to a user-configured limit (1–30 seconds, default: 3s).
- **Curated Domain Presets:** Over 100 verified domestic Iranian endpoints organized across 13 service categories (PSPs, PaymentYars, BNPL, SMS panels, shipping, CDNs, local messengers).
- **Local Asset Rewriting:** Automatic fallback rewriting for Google Fonts (Vazirmatn & Roboto), FontAwesome (v4 & v5), Swiper JS/CSS, Dashicons, and Elementor icons without external requests.
- **Mixpanel Stub:** Local JavaScript proxy (`mixpanel-stub.js`) preventing front-end console errors when analytics tracking is blocked.
- **CI / CD Workflows:** GitHub Actions automated matrix testing across PHP 7.4 through 8.4 (`ci.yml`) and automated release packager (`release.yml`).
- **Automated Test Suite:** Comprehensive CLI test suite (`tests/run-tests.php`) covering 34 unit and integration scenarios.

### Changed
- **Safe Update Defaults:** WordPress core security updates now default to enabled (`disable_updates = 'no'`), ensuring security advisories and critical patches are never silently blocked.
- **Performance Isolation:** Administration controllers, views, and settings registration are conditionally loaded only when `is_admin()` evaluates to true, resulting in zero overhead on public visitor requests.
- **In-Memory Caching:** Settings are cached in static memory during request execution to eliminate redundant database reads against `wp_options`.
- **Standardized Versioning:** Adopted Semantic Versioning (`v0.1.0`) across all code constants, documentation, and release archives.

### Security
- **Credential Redaction in Logs:** Sensitive query parameters (`token`, `api_key`, `password`, `secret`, `auth`, `hash`, etc.) are automatically stripped and replaced with `[REDACTED]` before saving to disk or database.
- **Strict Capabilities & Nonces:** Enforced `manage_options` capability checks on all administrative views and validated action nonces on state-changing handlers (`wpgate_clear_logs`, `wpgate_reset_defaults`).
- **Domain Spoofing Mitigation:** Strengthened domain matching algorithm to require exact hostname or explicit dot-separated subdomain boundaries, preventing prefix attack domains (e.g. `attacker-bank.com` matching `bank.com`).

### Removed
- **Runtime Filesystem Operations:** Completely removed legacy `ensure_*` functions that attempted to `copy()` and `file_put_contents()` assets to the web root during visitor requests.
- **Third-Party Theme Hardcodes:** Removed arbitrary legacy references to external themes (`bonyadco`) in Gravatar replacement routines.
- **Legacy Plugin Action Hooks:** Removed redundant `plugin_action_links` filters incompatible with standalone MU-plugin or theme deployment.
