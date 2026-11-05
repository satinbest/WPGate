# Detailed Feature Specifications

[English](features.md) | [فارسی](../fa/features.md)


This document provides in-depth technical details on WPGate's core features.

---

## 1. Outbound HTTP Request Interception

WPGate hooks into the WordPress HTTP API filter `pre_http_request` with priority `10`.

```text
Outbound HTTP Call (wp_remote_get/post)
  └── pre_http_request hook (Priority 10)
        ├── 1. Parse Hostname via wp_parse_url()
        ├── 2. Check Localhost Exemption (localhost, 127.0.0.1, ::1, site_url) -> Allow
        ├── 3. Evaluate Whitelisted Update Rule -> Block if non-whitelisted update
        ├── 4. Evaluate Mixpanel Target -> Block with WP_Error if target
        ├── 5. Mode Check:
        │     ├── Mode == 'disabled' -> Allow
        │     ├── Mode == 'whitelist' -> Allow if host in list; Else Block with WP_Error
        │     └── Mode == 'blacklist' -> Block with WP_Error if host in list; Else Allow
        └── Pass-through to cURL/Streams Transport
```

---

## 2. Subdomain-Aware Domain Matching Algorithm

WPGate enforces exact boundary validation in `HttpGuard::host_in_list()`:
- **Exact Match:** `$host === $domain`
- **Subdomain Match:** `substr($host, -strlen('.' . $domain)) === '.' . $domain`
- **Normalization:**
  - Strips trailing FQDN dots (`example.com.` -> `example.com`).
  - Strips port indicators (`example.com:8080` -> `example.com`).
  - Normalizes `www.` prefixes so `www.domain.com` matches `domain.com`.

This prevents **prefix spoofing** where an attacker creates `attacker-bank.com` to bypass a rule targeting `bank.com`.

---

## 3. Clamped Timeout Guard

Many plugins specify 15 to 60-second timeouts on HTTP requests. During internet brownouts, these calls hang until server timeout, consuming PHP-FPM workers and triggering HTTP 504 Gateway Timeouts.

WPGate's `TimeoutGuard` hooks into `http_request_args` (priority `20`):
- Inspects `$args['timeout']`.
- If `$args['timeout'] > $max_request_timeout`, clamps it to the threshold (default: 3 seconds).
- If `$args['timeout']` is already lower, leaves it unchanged.
- Automatically bypasses local/loopback requests.

---

## 4. Local Asset Replacement & Fallbacks

WPGate hooks into `script_loader_src` and `style_loader_src` (priority `20`):
- **Google Fonts:**
  Parses query parameters (e.g. `family=Vazirmatn|Roboto`). Maps to bundled CSS and webfonts in `assets/vendor/google-fonts/`.
- **FontAwesome:**
  Detects `all.min.css` and `v4-shims.min.css` from CDN providers (Cloudflare, jsDelivr, MaxCDN). Maps to bundled files in `assets/vendor/fontawesome/`.
- **Swiper:**
  Maps `swiper-bundle.min.js` and `swiper-bundle.min.css` to bundled assets.
- **Mixpanel:**
  Maps `cdn.mxpnl.com` to `assets/js/mixpanel-stub.js` providing dummy `window.mixpanel` methods (`init`, `track`, `identify`, `people.set`).
- **Strict Block:**
  Any remaining unmapped external CDN scripts/styles are replaced with `blocked-asset.js` / `blocked-asset.css`.

---

## 5. Secure Credential Masking in Diagnostics

The `Logger::sanitize_url()` method parses request URLs and scans query arguments against known sensitive keys:
- `token`, `key`, `api_key`, `apikey`, `secret`, `password`, `passwd`, `pass`, `auth`, `hash`, `signature`, `sig`, `nonce`, `card`, `cvv`, `pin`, `code`, `session`.

Matching values are replaced with `[REDACTED]`. The query string is safely rebuilt, protecting site secrets from database exposure.
