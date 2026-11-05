# WPGate v0.1.4 — UI Typography, Component Scoping & Persian Localization Audit Release Notes

[English](RELEASE-NOTES.md) | [فارسی](RELEASE-NOTES-FA.md)

**Release Version:** `v0.1.4`  
**Release Date:** November 6, 2026  
**Author & Lead Architect:** Hossein Mohammadpour (<info@hosseinmohammadpour.ir>)  
**Official Repository:** [https://github.com/satinbest/WPGate](https://github.com/satinbest/WPGate)  
**Release Type:** UI Quality, Typography Baseline & Complete Localization Patch  

---

## 🌟 Executive Summary

**WPGate v0.1.4** delivers a comprehensive visual and localization audit of the entire WPGate administration interface. This release directly resolves the reported oversized Persian text issue on the main dashboard (`فقط لیست سفید (محدودسازی سخت‌گیرانه)`), establishes strict typography baseline tokens across all screens, ensures 100% dictionary parity between English and Persian across 276 localized keys, eliminates all hardcoded strings, and prevents WordPress core admin styles from bleeding into the WPGate interface.

---

## 🚀 Key Improvements in v0.1.4

### 1. Resolution of Oversized Dashboard Typography
- **Root Cause:** In previous versions, configuration status strings like `فقط لیست سفید (محدودسازی سخت‌گیرانه)` were passed into `StatCard.vue`'s numeric `:value` prop, which was styled as a large `1.75rem` (28px) bold number without text wrapping constraints or minimum height limits.
- **Architectural Solution:**
  - Added intelligent non-numeric text value detection in `StatCard.vue` (`.stat-value-text`).
  - Text-based status labels are rendered at a refined `15px` font size with semi-bold weight (`600`), line height of `1.35`, and flexible word-break wrapping.
  - Enforced a uniform card minimum height of `145px` to eliminate uneven card expansion across responsive breakpoints.
  - Streamlined Active Firewall Mode values to concise, clear translated labels:
    - Whitelist Mode: `Allowlist Only` / `لیست سفید` (Badge: `Strict` / `سخت‌گیرانه`)
    - Blacklist Mode: `Blocklist Only` / `لیست سیاه` (Badge: `Selective` / `انتخابی`)
    - Disabled Mode: `Disabled` / `غیرفعال` (Badge: `Inactive` / `غیرفعال`)

### 2. Complete Centralized Localization Parity (276 Keys)
- Expanded the centralized translation dictionary in `admin-ui/src/i18n.js` from 199 to **276 keys**.
- Achieved **100% translation parity** between English and Persian with zero missing keys in either language.
- Eliminated all hardcoded English strings across panels, buttons, badges, modals, and toasts.
- Preserved technical terms in their canonical form (`WPGate`, `PHP`, `WordPress`, `REST API`, `URL`, `HTTP`, `JavaScript`, domain names).
- Implemented automated verification script (`scripts/verify-i18n.js`) to guarantee dictionary parity and validate every component `t('...')` reference.

### 3. Strict Scoped Typography Baseline
All typography tokens are strictly scoped within `.wpgate-admin-wrap` and `#wpgate-admin-app`:
- **Main Page Title:** `20px` (Font weight: 700)
- **Section Title:** `16px` (Font weight: 600)
- **Card / Panel Title:** `14px` (Font weight: 600)
- **Sidebar Item:** `13px` (Font weight: 500)
- **Body Text & Form Labels:** `13px`
- **Buttons:** `13px` with fixed `36px` height (Compact: `12px` / `30px`)
- **Inputs & Selects:** `13px` with fixed `38px` height
- **Help Text & Metadata:** `12px`
- **Badges & Chips:** `11–12px`
- **Panel Padding:** `16–20px`
- **Sidebar Width:** `232px`

### 4. Component Attribute Binding Cleanup
- Cleaned up malformed template bindings where literal `t('...')` strings or double colons (`::title`, `::placeholder`) were passed.
- Standardized all component dynamic properties to `:title`, `:badge`, `:aria-label`, and `:placeholder`.

---

## 🧪 Quality Assurance & Test Verification

- **PHP Unit Tests:** All **56 automated tests across 18 test suites passed with 100% success** (`php tests/run-tests.php`).
- **Localization Verification:** **100% translation dictionary parity** verified across 276 keys (`node scripts/verify-i18n.js`).
- **Documentation Parity:** Full bilingual parity verified across all documentation pairs (`node tests/verify-docs.js`).

---

## 📦 Downloadable Assets (v0.1.4)

Official release packages are available on GitHub Releases:
- `WPGate-MU-Plugin-v0.1.4.zip` — Recommended MU-Plugin package for `wp-content/mu-plugins/`
- `WPGate-Theme-Module-v0.1.4.zip` — Child Theme module package for active themes
- `WPGate-Complete-Release-v0.1.4.zip` — Full development archive with complete source and tests
- `SHA256SUMS.txt` — SHA-256 cryptographic verification checksums
