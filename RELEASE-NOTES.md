# WPGate v0.1.2 — UI/UX Overhaul & Architecture Release Notes

[English](RELEASE-NOTES.md) | [فارسی](RELEASE-NOTES-FA.md)

**Release Version:** `v0.1.2`  
**Release Date:** November 6, 2026  
**Author & Lead Architect:** Hossein Mohammadpour (<info@hosseinmohammadpour.ir>)  
**Official Repository:** [https://github.com/satinbest/WPGate](https://github.com/satinbest/WPGate)  
**Release Type:** Feature & UX Major Upgrade  

---

## 🌟 Executive Summary

**WPGate v0.1.2** delivers a complete architectural redesign of the administration user interface, transitioning from a basic tabbed panel into an enterprise-grade, high-productivity developer dashboard.

Key innovations in this release include a modern vertical sidebar navigation with desktop collapsible modes and mobile drawer support, inlined local Persian typography via bundled WOFF2 Vazirmatn fonts with zero CDN dependencies, a reusable component architecture (`SettingsPanel`, `SettingToggle`, `StatCard`, `StatusBadge`), complete bilingual parity, responsive viewport optimizations (from 390px mobile to 1440px wide desktop), and keyboard shortcuts (`Ctrl+S` / `Cmd+S`).

---

## 🚀 Key Improvements in v0.1.2

### 1. Vertical Responsive Sidebar Navigation
- **Semantic Grouping:** Organized into two primary sections: *Core Navigation* (Overview, HTTP Control, Local Assets, WordPress Controls) and *System & Insights* (Diagnostic Logs, Tools & Settings, About WPGate).
- **Collapsible Mode:** Desktop collapse button shrinks the sidebar to a 72px icon rail with native hover tooltips. State is automatically remembered via browser `localStorage`.
- **Mobile Slide-Out Drawer:** On viewports under 1024px, navigation collapses into a slide-out drawer accessible via the top-bar hamburger toggle with backdrop blur dismissal.
- **RTL/LTR Precision:** Respects directional layouts natively — sidebar docks on the right in Persian (RTL) and on the left in English (LTR).

### 2. Inlined Local Vazirmatn Typography
- **Zero External Font Requests:** Three weights of the Vazirmatn typeface (`Regular 400`, `Medium 500`, `Bold 700`) are bundled directly into the client package.
- **Vite Asset Inlining:** Through `assetsInlineLimit: 65536`, WOFF2 fonts are base64-inlined into `wpgate-admin.css`. Sites running in intranet or restricted networking environments enjoy 100% offline typography with zero 404 font errors or CORS restrictions.
- **Strict Scope Isolation:** Typography is strictly scoped to `.wpgate-admin-wrap` and `#wpgate-admin-app`, ensuring zero CSS bleed into core WordPress administration screens.

### 3. Reusable Component Design System
- **`AppSidebar.vue` & `AppHeader.vue`:** Clean structural division separating global breadcrumb context, system status pills, language switcher, theme toggle, and primary Save action.
- **`SettingsPanel.vue` & `SettingToggle.vue`:** Unified cards with standardized title headers, status badges, contextual warnings, and accessible switches.
- **`StatCard.vue`:** Operational metric visualization with tinted icon badges, value counters, and honest informative states when diagnostic logging is inactive (replacing misleading zeros).
- **`StatusBadge.vue`:** Standardized status tags (`success`, `warning`, `danger`, `info`, `primary`, `neutral`).

### 4. Tab Views & Operational Polish
- **Overview:** Redesigned operational dashboard highlighting active firewall mode, protection matrix, and quick configuration actions.
- **HTTP Control:** Replaced radio cards with interactive selection cards (Disabled, Allowlist, Blocklist), categorized domestic preset chips, and a responsive request timeout guard slider (1–30s).
- **Local Assets:** Clear distinction between the built-in admin Vazirmatn font and optional frontend visitor font injection, plus local vendor library status cards.
- **WordPress Controls:** Grouped updates management with security notices, telemetry prevention, and custom fallback avatar URL.
- **Diagnostic Logs:** Responsive data table, real-time search input with icon, status filter dropdown, log details modal, and copyable sanitized URLs.
- **Tools & Settings:** One-click JSON backup export and restore with validation, factory reset danger zone, system diagnostics specs, and emergency kill-switch guide.
- **About WPGate:** Modern brand shield hero, developer attribution for Hossein Mohammadpour, and licensing declarations.

### 5. Developer Shortcuts & Productivity
- **`Ctrl+S` / `Cmd+S`:** Quick-save settings from anywhere in the application.
- **`Escape` Key:** Instantly close mobile menus and open modals.
- **Dirty State Tracking:** Real-time detection of unsaved modifications with status indication.

---

## 📦 Verified Release Packages

| Package Filename | Role & Target Directory | Recommended For |
|---|---|---|
| **`WPGate-MU-Plugin-v0.1.2.zip`** | Extract directly into `wp-content/mu-plugins/` | **All Production Sites (Recommended)** |
| **`WPGate-Theme-Module-v0.1.2.zip`** | Extract `wpgate-core/` into active child theme | Sites without `mu-plugins` write access |
| **`WPGate-Complete-Release-v0.1.2.zip`** | Comprehensive archive with full source code & docs | Developers & Offline Audits |

### Package Checksums (SHA-256)
```text
20b40493fa0273e5881f04116c01cfe9102e3db885a6974422ea0dbc1001eb16 *WPGate-MU-Plugin-v0.1.2.zip
ca60cdfa2ae28ebbeb5b2bebd078dc0d7fee7b5f287c9612d74faab3d8c5fdcf *WPGate-Theme-Module-v0.1.2.zip
3452505feca39b9e2f22ee2714c31bf6abf545d6e80db58c768f03acf2c5c692 *WPGate-Complete-Release-v0.1.2.zip
```

---

## 📥 Installation & Upgrade Instructions

### Upgrading an Existing Installation
1. Back up your existing `wp-content/mu-plugins/wpgate-core/` directory.
2. Download `WPGate-MU-Plugin-v0.1.2.zip`.
3. Extract and replace files in `wp-content/mu-plugins/`.
4. Refresh your browser (`Ctrl+F5` / `Cmd+Shift+R`) to load the updated Vue 3 dashboard.
5. All database settings and whitelists are automatically preserved.
