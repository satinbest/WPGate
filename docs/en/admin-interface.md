# WPGate Administration Interface Guide

[English](admin-interface.md) | [فارسی](../fa/admin-interface.md)


The WPGate administration panel is built as a modern, reactive Single Page Application (SPA) using **Vue 3 (Composition API)** and **Vite**. It integrates directly into the WordPress admin menu (**Settings > WPGate**) and communicates with the WordPress backend through the authenticated **REST API (`wpgate/v1`)**.

---

## 🖥️ Layout & Navigation

The interface consists of:
1. **Top Header:**
   - WPGate logo and version indicator (`v0.1.1`).
   - **Language Toggle:** Instant switch between English (LTR) and Persian (RTL).
   - **Theme Toggle:** Switch between Light Mode and Dark Mode.
   - **Save Changes Button:** Global save button with live saving spinner and toast notifications.
2. **Navigation Tabs:**
   - **Overview:** Gateway status, metrics, and deployment mode.
   - **HTTP Control:** Whitelist/Blacklist rules, domestic presets, and timeout protection.
   - **Local Assets:** CDN rewriting, strict blocking, and font injection toggles.
   - **WordPress Controls:** Update suppression, pingback blocking, and Gravatar replacement.
   - **Diagnostic Logs:** Searchable, paginated log viewer with secret redaction.
   - **Settings & Tools:** Configuration export/import, factory reset, and environment info.
   - **About WPGate:** Developer details (Hossein Mohammadpour), repository links, and licensing.

---

## 📊 Tab Details

### 1. Overview
- **System Status Card:** Displays the current operating mode (Whitelist, Blacklist, or Disabled) and warns if the emergency kill-switch (`WPGATE_DISABLED`) is active.
- **Metrics Counter:** Displays total requests checked, blocked requests, local rewrites performed, and active domain counts.
- **Deployment Method Card:** Identifies whether WPGate is running as a Must-Use Plugin or Child Theme module.

### 2. HTTP Control
- **Interceptor Mode:**
  - *Disabled:* All outbound requests pass untouched.
  - *Whitelist Only:* Restricts all outbound HTTP/HTTPS requests to approved domains.
  - *Blacklist Mode:* Blocks only specified domains while permitting other traffic.
- **Domain Whitelist / Blacklist Textareas:** Clean multiline text inputs with automated hostname sanitization.
- **Domestic Domain Presets:** One-click chips that instantly append verified domestic domains (PSPs, PaymentYars, SMS, Post, Messengers, Cloud).
- **Timeout Guard:** Toggle and slider (1 to 30 seconds) to prevent slow external APIs from holding PHP processes.

### 3. Local Assets (CDN Gateway)
- **Local Asset Rewrites:** Replaces remote script/style URLs with bundled local assets.
- **Strict Asset Blocking:** Neutralizes uncached third-party CDN scripts to prevent frontend render locking.
- **Mixpanel Tracking Stub:** Injects a safe zero-latency local stub (`mixpanel-stub.js`).
- **Frontend Vazirmatn Font:** Automatically enqueues bundled Vazirmatn for unified typography.

### 4. WordPress Controls
- **Block Core Updates:** Disables background version checks to `api.wordpress.org`.
- **Allow Whitelisted Updates:** Ensures domestic marketplaces (Zhaket, RTL-Theme) can deliver updates even when core updates are blocked.
- **Block Telemetry:** Disables pingbacks and version telemetry.
- **Disable Gravatar:** Replaces `gravatar.com` avatar requests with a fast, local bundled image.

### 5. Diagnostic Logs
- **Search & Filters:** Real-time search across hosts, sanitized URLs, and status filter (All, Blocked, Allowed, Rewritten).
- **Pagination:** Clean Next/Previous and page counter.
- **Sensitive Data Masking:** Parameters matching `token`, `password`, `key`, `secret`, `signature`, `auth` are displayed as `[REDACTED]`.
- **Clear Logs:** Instant purge with confirmation modal.
- **Details Modal:** Click any row to view complete timestamp, target host, and full masked URL with one-click copy.

### 6. Settings & Tools
- **Export Configuration:** Downloads a structured `wpgate-settings-YYYY-MM-DD.json` file.
- **Import Configuration:** Restores configuration from a valid WPGate JSON file.
- **Reset to Defaults:** Restores default safe options with modal confirmation.
- **System Environment Table:** Displays PHP version, WordPress version, loading method, multisite status, and runtime disk write metrics.

### 7. About WPGate
- Detailed product summary, architectural guarantees, developer contact (**Hossein Mohammadpour**, `info@hosseinmohammadpour.ir`), website, repository, and licensing declarations.
