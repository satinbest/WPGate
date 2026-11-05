# WPGate Configuration Guide

[English](configuration.md) | [فارسی](../fa/configuration.md)


This document details all available configuration settings in WPGate, their default values, and operational recommendations.

---

## Accessing the Settings Interface

Navigate to **WordPress Admin > Settings > WPGate**.  
*(Requires the `manage_options` capability).*

---

## 1. Outbound Firewall Settings

### Work Mode (`mode`)
- **`disabled` (Default):** Normal WordPress operation. No external HTTP requests are preempted or blocked.
- **`whitelist` (Recommended during network instability):** Strict firewall. Every external HTTP request is blocked unless its destination hostname matches an entry in the Whitelist.
- **`blacklist`:** Permissive firewall. All requests are allowed except destinations explicitly listed in the Blacklist.

### Whitelist (`whitelist`)
- Accepts a multiline string of hostnames or domains (one domain per line).
- Leading protocols (`http://`, `https://`) and trailing ports/paths are automatically sanitized.
- Supports exact matching and subdomain matching. For instance, entering `zarinpal.com` automatically permits `api.zarinpal.com` and `payment.zarinpal.com`.
- **Exemptions:** Requests to `localhost`, `127.0.0.1`, `::1`, and the site's own domain are unconditionally allowed.

### Blacklist (`blacklist`)
- Multiline string of prohibited hostnames or domains.
- When `mode = blacklist`, any match results in an immediate `wpgate_blocked` error.

---

## 2. Asset & Front-End Optimizations

### Local CDN Replacement (`local_asset_rewrite`)
- **Default:** `yes`
- Intercepts remote script and stylesheet tags and rewrites them to bundled local assets:
  - Google Fonts (`fonts.googleapis.com`) -> Local Vazirmatn or Roboto CSS
  - FontAwesome Free -> Bundled local FontAwesome CSS and Webfonts
  - Swiper Slider -> Bundled local Swiper JS & CSS
  - Dashicons & WP Core JS -> Core includes or bundled fallbacks
- **Customizer Immunity:** Automatically bypassed in the WordPress Customizer to preserve live theme styling.

### Strict External Asset Blocking (`strict_asset_block`)
- **Default:** `yes`
- If an external CDN asset cannot be mapped to a local equivalent and its host is not in the whitelist, WPGate replaces it with an empty stub (`blocked-asset.js` or `blocked-asset.css`) to prevent front-end browser hanging.

### Mixpanel Neutralization (`block_mixpanel`)
- **Default:** `yes`
- Blocks telemetry requests to `api-eu.mixpanel.com`, `api.mixpanel.com`, etc., and injects `mixpanel-stub.js` to define safe `window.mixpanel` no-op functions.

### Front-End Vazirmatn Font (`enable_front_vazirmatn`)
- **Default:** `no`
- When enabled, enqueues the local Vazirmatn font stylesheet on all public pages.

---

## 3. Stability, Updates & Privacy

### Timeout Guard (`enable_timeout_guard` & `max_request_timeout`)
- **Default:** `no` (Timeout: `3` seconds)
- When enabled, clamps the `timeout` parameter of outbound `wp_remote_get()` and `wp_remote_post()` requests to the configured ceiling (1–30 seconds).

### WordPress Telemetry (`disable_telemetry`)
- **Default:** `yes`
- Preempts requests directed to `wordpress.org` telemetry servers, preventing background metric reporting.

### WordPress Updates (`disable_updates` & `allow_whitelisted_updates`)
- **`disable_updates`:** Default is `no` (Keeps WordPress core and security updates enabled).
- **`allow_whitelisted_updates`:** Default is `yes`. When updates are disabled, this allows plugin and theme updates from whitelisted domestic marketplaces (such as Zhaket or RTL-Theme).

### Gravatar Masking (`disable_gravatar` & `gravatar_url`)
- **Default:** `yes`
- Replaces Gravatar calls with the module's bundled `user.jpg` or a custom URL.

---

## 4. Diagnostics & Logging

### Event Logging (`enable_logging`)
- **Default:** `no` (Disabled in production for zero DB overhead).
- When enabled, logs blocked requests and rewrites into `wp_options`.

### Retention & Limits
- **`log_retention_days`:** Auto-prunes entries older than 1, 3, 7, 15, or 30 days.
- **`log_max_entries`:** Caps total stored log records (50 to 2000, default: 300).
- **`log_asset_events`:** Controls whether asset rewrites are logged alongside network blocks.
