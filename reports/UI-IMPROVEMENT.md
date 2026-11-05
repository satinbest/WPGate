# WPGate — Admin UI Typography & Layout Density Report

**Project:** WPGate  
**Release:** v0.1.3  
**Developer:** Hossein Mohammadpour <info@hosseinmohammadpour.ir>  
**Status:** Approved & Verified

---

## 1. Design System Overview

In release `v0.1.3`, the WPGate Vue 3 Administration UI underwent a comprehensive typography and spacing calibration. The goal was to establish optimal visual hierarchy, compact enterprise density, and flawless Persian / English typography parity across all resolutions and themes (Light & Dark).

---

## 2. Specification Baseline Compliance Matrix

All typography, component heights, and padding have been calibrated to the exact engineering baseline:

| UI Element | Specification Target | Implemented Value in v0.1.3 | Status |
| :--- | :--- | :--- | :--- |
| **Main Page Title** | `20px` (Weight: 700) | `font-size: 20px; font-weight: 700;` (`AppHeader.vue`, `.wpgate-about-name`) | **Compliant** |
| **Section Title** | `16px` (Weight: 600) | `font-size: 16px; font-weight: 600;` (`.wpgate-section-title`, `.wpgate-page-title`) | **Compliant** |
| **Card / Panel Title** | `14px` (Weight: 600) | `font-size: 14px; font-weight: 600;` (`.wpgate-panel-title`, `.wpgate-mode-title`) | **Compliant** |
| **Sidebar Item** | `13px` (Weight: 500) | `font-size: 13px; font-weight: 500;` (`.nav-item-btn`) | **Compliant** |
| **Body Text** | `13px` (Line-height: 1.5) | `font-size: 13px; line-height: 1.5;` (`.wpgate-admin-wrap`, `.wpgate-section-desc`) | **Compliant** |
| **Form Label** | `13px` (Weight: 600) | `font-size: 13px; font-weight: 600;` (`.wpgate-label`, `.toggle-title`) | **Compliant** |
| **Button** | `13px` (Height: 36px) | `font-size: 13px; height: 36px; padding: 0 16px;` (`.wpgate-btn`) | **Compliant** |
| **Button (Compact)** | `12px` (Height: 30px) | `font-size: 12px; height: 30px; padding: 0 10px;` (`.wpgate-btn-sm`) | **Compliant** |
| **Input / Select** | `13px` (Height: 38px) | `font-size: 13px; height: 38px; padding: 0 12px;` (`.wpgate-input`, `.wpgate-select`) | **Compliant** |
| **Help Text / Meta** | `12px` | `font-size: 12px;` (`.wpgate-field-help`, `.toggle-description`, `.matrix-desc`) | **Compliant** |
| **Badge** | `11–12px` | `font-size: 12px;` (`StatusBadge.vue`), `11px` (`.nav-item-badge`, `.wpgate-chip-count`) | **Compliant** |
| **Panel Padding** | `16–20px` | Header: `14px 20px;`, Body: `18px 20px;`, Footer: `12px 20px;` | **Compliant** |
| **Sidebar Width** | `232px` | Fixed `width: 232px; flex: 0 0 232px;` (`AppSidebar.vue`) | **Compliant** |

---

## 3. Persian (RTL) Typography Improvements

1. **Locally Bundled Vazirmatn Font:**
   - Bundled directly in `@font-face` WOFF2 format (Regular 400, Medium 500, Bold 700).
   - Zero external CDN dependencies for font rendering.
2. **OpenType Features:**
   - Active font features: `cv02`, `cv03`, `cv04`, `cv11` for crisp Persian numerals and balanced ligatures.
3. **RTL Directional Flipping:**
   - Presets, search indicators, toggles, and breadcrumb carets dynamically reverse direction under `[dir="rtl"]`.
   - Toggle switch transform: `translateX(20px)` in LTR vs `translateX(-20px)` in RTL.

---

## 4. Production Build Verification

The Vite production build was executed and verified:
- **CSS Bundle:** `wpgate-admin.css` (243.52 kB raw, 162.55 kB gzip, includes all bundled Vazirmatn fonts).
- **JS Bundle:** `wpgate-admin.js` (181.25 kB raw, 59.46 kB gzip).
- **Console Errors:** 0
- **Accessibility:** High contrast compliance in both light and dark themes.
