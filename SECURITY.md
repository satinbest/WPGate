# Security Policy

[English](SECURITY.md) | [فارسی](SECURITY-FA.md)

## Supported Versions

Only the latest release branch of WPGate receives active security updates:

| Version | Supported | Notes |
|---|---|---|
| **0.1.x** | ✅ Yes | Current active stable release |

---

## Reporting a Vulnerability

If you discover a potential security issue in WPGate, please report it responsibly:

- **Security Contact:** [info@hosseinmohammadpour.ir](mailto:info@hosseinmohammadpour.ir)
- **Subject:** `[SECURITY] Potential vulnerability in WPGate`

Please include:
1. Detailed description of the vulnerability.
2. Steps to reproduce the issue (including sample code or payloads).
3. The affected environment (PHP version, WordPress version, server configuration).

Please do **NOT** disclose the issue publicly until a patch has been published and released.

---

## Security Architecture Highlights

WPGate implements defense-in-depth security principles:

1. **Strict Capability Checks:** Every admin action and REST API endpoint requires `manage_options`.
2. **REST Nonce Verification:** Every mutating request validates the `X-WP-Nonce` header.
3. **Sensitive Data Redaction:** Outbound request logging automatically redacts tokens, passwords, nonces, API keys, and hashes (`[REDACTED]`).
4. **Zero Runtime File Writes:** WPGate never executes dynamic file writes or code generation at runtime.
5. **Safe Kill-Switch:** Administrators can immediately bypass all module hooks by defining `WPGATE_DISABLED` in `wp-config.php`.
