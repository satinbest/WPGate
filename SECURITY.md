# Security Policy

The security of **WPGate** and the WordPress sites running it is our top priority. We appreciate any efforts to responsibly disclose vulnerabilities.

---

## 🛡️ Supported Versions

We provide security updates and patches for the following versions:

| Version | Supported | Status |
|---|---|---|
| **0.1.x** | ✅ Yes | Current Active Release |
| **< 0.1.0 (Legacy Ghateino)** | ❌ No | Deprecated — Please migrate to WPGate v0.1.0+ |

---

## 🔒 Architectural Security Guarantees

WPGate is engineered with defensive programming principles:

1. **Zero Runtime Disk I/O:** The module never writes, creates, or copies files on the web server's filesystem during ordinary front-end or administrative requests.
2. **Credential Redaction:** Sensitive query parameters (`token`, `api_key`, `secret`, `password`, `hash`, etc.) in request URLs are masked with `[REDACTED]` prior to logging.
3. **Capability Gating:** Administrative endpoints and settings modification strictly demand `manage_options`.
4. **CSRF Protection:** State-changing requests (such as log clearing or settings restoration) enforce WordPress nonce verification (`check_admin_referer`).
5. **Exact & Subdomain-Safe Matching:** Domain rules strictly validate exact hostname or dot-prefixed boundaries to eliminate prefix spoofing.
6. **No Remote Code Execution / Downloads:** The module never pulls or evaluates untrusted remote scripts or styles at runtime.

---

## 🚨 Reporting a Vulnerability

If you discover a security vulnerability in WPGate, please report it privately:

- **Email:** `security@satinbest.com`
- **Subject:** `[Vulnerability Report] WPGate - <Brief Summary>`

Please include:
1. Clear steps to reproduce the issue (PoC script or screenshots if possible).
2. The affected module version and PHP/WordPress runtime versions.
3. Potential impact or threat vectors.

### What to Expect:
- We will acknowledge receipt of your report within **48 hours**.
- We will investigate and provide a validation status within **5 business days**.
- Once a fix is verified, a patched release will be published promptly, and you will be credited in our release notes (unless you prefer to remain anonymous).

Please **do not** report security vulnerabilities via public GitHub issues.
