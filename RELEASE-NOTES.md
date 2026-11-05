# WPGate v0.1.5 — Form Controls, Security Event Dialog & I/O Metrics Audit Release Notes

[English](RELEASE-NOTES.md) | [فارسی](RELEASE-NOTES-FA.md)

**Release Version:** `v0.1.5`  
**Release Date:** November 6, 2026  
**Author & Lead Architect:** Hossein Mohammadpour (<info@hosseinmohammadpour.ir>)  
**Official Repository:** [https://github.com/satinbest/WPGate](https://github.com/satinbest/WPGate)  
**Release Type:** UI/UX Quality, Form Controls Architecture & Security Dialog Overhaul  

---

## 🌟 Executive Summary

**WPGate v0.1.5** delivers a comprehensive UX and architectural upgrade across form controls, modal dialogs, and diagnostic metrics:
1. **Search Input Redesign:** Introduces the reusable `SearchInput.vue` component with bidirectional logical styling (`padding-inline-start`, `padding-inline-end`, `inset-inline-end`), integrated clear button, and loading spinner.
2. **Select Component Redesign:** Introduces `AppSelect.vue` and enhanced `.wpgate-select` styling with embedded SVG dropdown arrows placed with logical properties to ensure perfect rendering across RTL and LTR viewports in both light and dark themes.
3. **Security Event Details Dialog Redesign:** Replaces the previous basic modal with `SecurityEventDialog.vue`, featuring a three-tiered layout (Event Summary, Request Information, and Diagnostics & Privacy), severity classifications, deterministic event signatures, and one-click copy utilities.
4. **I/O Storage Metric Audit:** Corrects the technically misleading "Disk Write IOPS: 0 Bytes" claim to an accurate, verifiable metric: `Disk File Logging: Disabled (0 Files on Disk)` with detailed contextual tooltips.
5. **Localization Expansion:** Expands dictionary to 306 keys with 100% parity across English and Persian locales.

---

## 🚀 Key Improvements in v0.1.5

### 1. Modern, Accessible Search Input (`SearchInput.vue`)
- Standardized `38px` control height.
- SVG search icon placed at `inline-start`.
- Dynamic clear button (`X`) at `inline-end` that appears only when text is present.
- Uses CSS logical properties so clear button and search icon mirror automatically in Persian RTL mode without text overlap.
- Accessible keyboard behaviors (`Escape` clears search, `Enter` dispatches search immediately).
- Asynchronous loading spinner state.

### 2. Unified Dropdown System (`AppSelect.vue` & Enhanced `.wpgate-select`)
- `38px` height consistent with inputs and primary buttons.
- `appearance: none` removes disparate OS-native gray arrows.
- Embedded SVG chevrons placed via logical positioning (`inset-inline-end: 10px`) in light and dark modes.
- Accessible native select keyboard support (Tab, Arrows, Space, Enter) and native mobile OS picker support.

### 3. Professional Security Event Details Dialog (`SecurityEventDialog.vue`)
- Modern modal presentation with color-coded severity shield icons (Red for blocked threats, Blue for asset rewrites, Green for permitted traffic).
- Logical information grouping:
  - **Event Summary:** Event type, action taken, matched policy, and target host with inline copy.
  - **Request Information:** Protocol scheme tag, LTR domain pill, and sanitized URL display box with `word-break: break-all` and dedicated copy button.
  - **Diagnostics & Privacy:** Isolated URI path, deterministic event signature hash (e.g. `EVT-7A9B4C12`), and credential redaction privacy notice.
  - **Footer Utilities:** One-click "Copy Full Event JSON" button for diagnostic sharing.
- Modal accessibility: `role="dialog"`, `aria-modal="true"`, background scroll locking (`document.body.style.overflow = 'hidden'`), focus restoration, and `Escape` key dismissal.

### 4. Technical Correction of IOPS & Storage Metrics
- **Root Cause:** IOPS measures storage operations per second, not bytes. PHP cannot profile hardware storage IOPS without privileged kernel sensors. Claiming "Pure RAM Execution" for WordPress options ignored MySQL disk persistence.
- **Correction:** Replaced with verifiable terminology:
  - Label: `Disk File Logging` / `فایل‌نویسی روی دیسک`
  - Value: `Disabled (0 Files on Disk)` / `غیرفعال (بدون فایل روی دیسک)`
  - Explanatory Tooltip: Explains that WPGate does not write standalone log files, events reside in the database, and physical drive IOPS is not profiled.

---

## 🧪 Quality Assurance & Test Verification

- **PHP Unit Tests:** All **56 automated tests across 18 test suites passed with 100% success** (`php tests/run-tests.php`).
- **Localization Verification:** **100% translation dictionary parity** verified across 306 keys (`node scripts/verify-i18n.js`).
- **Documentation Parity:** Full bilingual parity verified across all documentation pairs (`node tests/verify-docs.js`).

---

## 📦 Downloadable Assets (v0.1.5)

Official release packages are available on GitHub Releases:
- `WPGate-MU-Plugin-v0.1.5.zip` — Recommended MU-Plugin package for `wp-content/mu-plugins/`
- `WPGate-Theme-Module-v0.1.5.zip` — Child Theme module package for active themes
- `WPGate-Complete-Release-v0.1.5.zip` — Full development archive with complete source and tests
- `SHA256SUMS.txt` — SHA-256 cryptographic verification checksums
