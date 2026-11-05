# WPGate — Security Event Details Dialog UI/UX & Data Safety Review

[English](SECURITY-DIALOG-REVIEW.md) | [فارسی](SECURITY-DIALOG-REVIEW-FA.md)

**Project:** WPGate  
**Version:** `v0.1.5`  
**Date:** November 6, 2026  
**Lead Engineer:** Hossein Mohammadpour (<info@hosseinmohammadpour.ir>)  
**Repository:** [https://github.com/satinbest/WPGate](https://github.com/satinbest/WPGate)  
**Status:** PASSED (Comprehensive Redesign, Accessible Modal, Data Redaction Verified)  

---

## 1. Executive Summary

This review documents the complete redesign and security audit of the Security Event Details modal dialog in WPGate (`SecurityEventDialog.vue`).

The previous implementation rendered diagnostic events as an unstyled flat key-value list with plain `<pre>` blocks, lacked structured categorization, severity indicators, and copy utilities, and did not implement proper accessible dialog semantics or focus trapping.

The new `SecurityEventDialog.vue` component provides a modern security operations interface with logical section grouping, directional code isolation, one-click copy utilities, keyboard accessibility, and verified credential redaction.

---

## 2. Deficiencies in the Previous Dialog

1. **Unfinished Presentation:**
   - Displayed plain unstyled rows inside a basic modal box without visual hierarchy or section dividing lines.
   - Long destination URLs were rendered in an overflow-prone `<pre>` block without word breaking, causing horizontal layout distortions.
2. **Absence of Security Context:**
   - No severity classifications (High / Medium / Low).
   - No explicit explanation of the matched security policy (Whitelist Policy vs. Blacklist vs. Telemetry Guard vs. CDN Local Replacement).
   - Missing event signature or reference identifier for diagnostic logging.
3. **Accessibility Gaps:**
   - Lacked `role="dialog"` and `aria-modal="true"`.
   - Did not trap focus or lock background body scrolling.
   - Did not restore user focus to the triggering button upon dismissal.
4. **Localization & Directional Issues:**
   - English URLs and domains mixed into RTL Persian paragraphs caused punctuation flips and reading confusion.

---

## 3. Architecture of the New Security Event Dialog

### 3.1 Header Group
- **Security Shield Icon:** Color-coded according to event severity (Red for blocked threats, Blue for asset rewrites, Green for permitted traffic).
- **Dialog Title:** `t('logs.detailsTitle')` ("Security Event Details" / "جزئیات رویداد امنیتی").
- **Severity Indicator:** Prominent compact badge displaying `HIGH` (سخت‌گیرانه / بالا), `MEDIUM` (متوسط), or `LOW` (عادی).
- **Event Timestamp:** Precise UTC/local event recording time accompanied by a clock icon.
- **Dismiss Control:** Dedicated accessible close button (X).

### 3.2 Logical Information Layout
1. **Section 1: Event Summary (`logs.sectionSummary`):**
   - **Event Type:** `HTTP Request Interception` (رهگیری درخواست HTTP)
   - **Action Taken:** Color-coded `StatusBadge` (Blocked / Allowed / Rewritten)
   - **Matched Policy:** Identifies the rule engine responsible (Whitelist Policy, Blacklist Filter, Telemetry Guard, Local CDN Asset Rewriter)
   - **Target Host:** Domain name rendered in a monospaced badge with an inline mini-copy icon.
2. **Section 2: Request Information (`logs.sectionRequest`):**
   - **Protocol Tag:** Identifies scheme (`HTTPS` or `HTTP`).
   - **Domain Pill:** Isolated in LTR mode.
   - **Sanitized URL Box:** Displayed in a syntax container with `word-break: break-all`, LTR direction, and a dedicated "Copy URL" button.
3. **Section 3: Diagnostics & Data Privacy (`logs.sectionDiagnostics`):**
   - **Resource Path:** Displays the isolated URI path.
   - **Event Identifier:** Generates a deterministic signature hash (e.g., `EVT-7A9B4C12`) for audit tracking.
   - **Redaction Privacy Notice:** A clear, bordered advisory confirming that WPGate automatically redacts private credentials before storage.

### 3.3 Modal Footer Actions
- **Copy Full Event JSON:** Generates a structured JSON object containing all event telemetry for instant copy and sharing with system administrators.
- **Close Button:** Primary action button dismissing the dialog.

---

## 4. Accessibility & Interaction Testing

- **Focus Management:** On mount, caches the triggering element (`document.activeElement`) and restores focus when closed.
- **Background Scroll Locking:** Sets `document.body.style.overflow = 'hidden'` while the modal is open, restoring it on unmount.
- **Keyboard Navigation:**
  - Pressing `Escape` closes the modal immediately.
  - Tab navigation cycles smoothly through interactive buttons inside the modal.
- **Click Outside:** Clicking the backdrop overlay dismisses the dialog safely.

---

## 5. Data Safety & Privacy Considerations

1. **Zero HTML Injection:** All event attributes are rendered via Vue 3 reactive interpolation (`{{ ... }}`). No `v-html` is utilized, preventing XSS vulnerabilities.
2. **Pre-Storage Query Redaction:** URLs logged by `Logger::log` have sensitive query parameters (passwords, tokens, api keys, authorization hashes, nonces) masked with `[REDACTED]` before entering the database.
3. **Direction Isolation:** All domain names, paths, and URLs are strictly wrapped in `dir="ltr"` containers with left alignment to ensure zero text corruption in Persian RTL layouts.
