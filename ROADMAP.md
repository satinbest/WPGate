# WPGate Development Roadmap

This document outlines the planned feature additions and architectural milestones for WPGate.

---

## Current Version: v0.1.0 (Released)

- [x] Standalone decoupled PHP core architecture.
- [x] Dual deployment support (Must-Use Plugin & Child Theme loader).
- [x] Vue 3 + Vite modern Single Page Application (SPA) administration interface.
- [x] REST API endpoints under route `wpgate/v1`.
- [x] Bilingual support (English LTR and Persian RTL) with Light and Dark themes.
- [x] Outbound HTTP interceptor with Whitelist, Blacklist, and Disabled modes.
- [x] Local CDN asset rewriting (FontAwesome, Swiper, Google Fonts, Elementor Icons).
- [x] Telemetry blocking and Gravatar local avatar replacement.
- [x] Emergency kill-switch (`WPGATE_DISABLED`).
- [x] Comprehensive CLI test suite (41 tests, 100% pass).

---

## Planned for v0.2.0

- [ ] **WP-CLI Integration:** Command line management commands (`wp wpgate status`, `wp wpgate mode whitelist`, `wp wpgate logs --clear`).
- [ ] **Custom Header Interception:** Ability to inject custom HTTP headers on outbound requests.
- [ ] **Automated Preset Auto-Update:** Optional background check for updated domestic domain lists from trusted repository releases.

---

## Planned for v1.0.0

- [ ] **Live Latency & Health Probing:** Background probe testing domestic gateway endpoints and reporting network health in the Vue dashboard.
- [ ] **Multisite Network Admin Dashboard:** Centralized control across WordPress Multisite networks.
- [ ] **Advanced Rule Engine:** Path-based and header-based outbound filtering rules.
