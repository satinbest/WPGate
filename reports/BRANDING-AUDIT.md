# WPGate Official Branding & Banner Redesign Audit Report

**Date:** November 6, 2026  
**Author:** Hossein Mohammadpour (<info@hosseinmohammadpour.ir>)  
**Product:** WPGate  
**Authoritative Version:** v0.1.5  
**Repository:** https://github.com/satinbest/WPGate  

---

## 1. Executive Summary

This report documents the official redesign and production asset generation of the WPGate visual identity, release banner, social preview, and project emblem. 

Following the directives for a premium developer-tool and security software aesthetic, the visual assets have been transitioned away from generic WordPress plugin advertisements toward an enterprise-grade SaaS and infrastructure security design language.

---

## 2. Visual Identity & Design System

### 2.1 Palette & Motif
- **Primary Canvas:** Deep navy / obsidian space (`#070a12`, `#0b1329`, `#0f172a`).
- **Cyber Accent Accents:** Electric cyan (`#38bdf8`), deep azure (`#0284c7`), and gateway emerald (`#10b981`).
- **Typography:** Inter, -apple-system, BlinkMacSystemFont, Segoe UI with crisp contrast ratios exceeding WCAG AAA standards.
- **Architectural Motif:** Subtle isometric network grids, outbound gateway vector shields, and live dashboard telemetry mockups highlighting HTTP control, whitelist filtering, and local CDN caching.

### 2.2 Core Capabilities Communicated
1. **Outbound HTTP Control:** Strict whitelist, log-only inspection, and zero-day threat interception.
2. **Local Asset Management:** Automatic localization of external Google fonts, CDNs, and scripts.
3. **Reactive Admin Dashboard:** Powered by Vue 3 & Vite, running seamlessly in `wp-admin`.
4. **Early MU Plugin Execution:** Prioritized loading in `wp-content/mu-plugins/` before third-party code executes.

---

## 3. Generated Official Assets

All primary release assets are located under `assets/branding/` with legacy backward-compatibility mirrors maintained in `assets/`:

| Asset File | Target Purpose | Dimensions | File Size | Format |
|---|---|---|---|---|
| [`assets/branding/wpgate-banner.png`](../assets/branding/wpgate-banner.png) | GitHub README Header & Releases | 1600 × 600 px | ~158 KB | PNG (RGB) |
| [`assets/branding/wpgate-social-preview.png`](../assets/branding/wpgate-social-preview.png) | GitHub Repository OpenGraph Preview | 1280 × 640 px | ~127 KB | PNG (RGB) |
| [`assets/branding/wpgate-logo.png`](../assets/branding/wpgate-logo.png) | Official Shield Icon / Favicon / Avatar | 512 × 512 px | ~179 KB | PNG (RGB) |
| [`assets/banner-github.png`](../assets/banner-github.png) | Backward Compatibility Mirror | 1600 × 600 px | ~158 KB | PNG (RGB) |
| [`assets/social-preview.png`](../assets/social-preview.png) | Backward Compatibility Mirror | 1280 × 640 px | ~127 KB | PNG (RGB) |
| [`assets/logo.png`](../assets/logo.png) | Backward Compatibility Mirror | 512 × 512 px | ~179 KB | PNG (RGB) |

---

## 4. Automated Version Synchronization Engine

To eliminate human error and prevent stale version numbers in documentation banners during future releases:
- An automated generation script was implemented at [`scripts/generate-branding.js`](../scripts/generate-branding.js).
- The script dynamically parses the authoritative version from `mu-plugin-package/wpgate-core/bootstrap.php` (`WPGATE_CORE_VERSION = '0.1.5'`).
- The script uses the system's headless Chromium/Edge engine (`--headless=new`, `--force-device-scale-factor=1`) to render vector-sharp SVG/HTML templates into exact pixel-dimension PNG graphics.
- Zero heavyweight runtime dependencies (e.g. `puppeteer` or native `canvas`) are imposed on WordPress or development environments.
- Packaging script [`scripts/build-packages.js`](../scripts/build-packages.js) verifies branding asset existence and includes `assets/` in distribution packages.

---

## 5. GitHub Social Preview & Documentation Integration

1. **README Integration:**
   - Both `README.md` and `README-FA.md` have been updated to point to `assets/branding/wpgate-banner.png`.
2. **Social Preview Setting:**
   - The graphic `assets/branding/wpgate-social-preview.png` adheres strictly to GitHub's 1280×640 px (2:1 aspect ratio) social card requirement.
   - *Deployment Note:* GitHub requires repository administrators to set or upload social preview cards via `https://github.com/satinbest/WPGate/settings` under **Social preview**. The generated file is ready for one-click upload.

---

## 6. Verification & Sign-off

- [x] Dimension specifications verified (1600×600, 1280×640, 512×512).
- [x] Authoritative version badge correctly displays `v0.1.5`.
- [x] Backward-compatibility links validated.
- [x] Full bilingual documentation parity maintained.
- [x] Complete PHP and JavaScript automated tests passing.
