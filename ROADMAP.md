# WPGate Roadmap

This roadmap outlines the development direction, upcoming milestones, and architectural vision for **WPGate**.

> [!NOTE]
> All milestones follow [Semantic Versioning](https://semver.org/). Items listed under future versions represent planned work and are subject to refinement based on community feedback and Project Manager review.

---

## 📍 Version Milestones

### 🟢 v0.1.0 — Initial Standalone Release (Current)
- [x] Complete refactoring from monolithic codebase into clean OOP architecture (`WPGate\Core`).
- [x] Dual standalone deployment loaders (Must-Use Plugin & Theme Loader).
- [x] Emergency kill-switch constant (`WPGATE_CORE_DISABLED`).
- [x] Automatic backward compatibility migration from legacy Ghateino settings.
- [x] Subdomain-aware outbound HTTP firewall (Whitelist & Blacklist modes).
- [x] Clamped outbound HTTP request timeout guard (1–30s).
- [x] Local asset fallback rewriting for Google Fonts, FontAwesome, Swiper, and Dashicons.
- [x] Mixpanel neutralization stub (`mixpanel-stub.js`).
- [x] Secure logger with sensitive parameter redaction (`[REDACTED]`).
- [x] Automated test harness with 34 verification test scenarios.
- [x] GitHub Actions CI & automated release packaging workflows.

---

### 🟡 v0.2.0 — CLI & Health Inspection (Planned)
- [ ] **WP-CLI Command Integration:**
  - `wp wpgate status` — View active firewall mode, rule counts, and health.
  - `wp wpgate whitelist add <domain>` — Add domain directly from command line.
  - `wp wpgate logs --tail=20` — Stream real-time blocked requests from server shell.
- [ ] **REST API Diagnostic Endpoint:**
  - Secure `/wp-json/wpgate/v1/health` endpoint for remote monitoring by external uptime dashboards.
- [ ] **Dynamic Preset Synchronization:**
  - Optional signed updates for Iranian banking and PSP endpoints without requiring full module updates.

---

### 🟡 v0.3.0 — Advanced Observability & Telemetry (Planned)
- [ ] **Live Latency Benchmark Probe:**
  - Periodic background test measuring DNS resolution and TLS handshake time to essential domestic gateways.
- [ ] **Granular Route Filtering:**
  - Path-level exception rules in addition to hostname matching (e.g., allow `api.example.com/webhooks/` while restricting `/analytics/`).
- [ ] **CSV / JSON Log Export:**
  - One-click export of blocked request history from the WordPress admin panel.

---

### 🔵 v1.0.0 — Enterprise Production Ready (Long-Term Vision)
- [ ] Full Multisite (WP Network) governance panel.
- [ ] High-throughput Redis / Memcached caching layer for high-concurrency enterprise clusters.
- [ ] Multi-lingual admin UI localization (English, Persian, Arabic).
- [ ] Comprehensive E2E test coverage across diverse WordPress hosting architectures.
