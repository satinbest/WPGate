# Release Process & Versioning Policy

[English](release-process.md) | [فارسی](../fa/release-process.md)


This document defines the release engineering procedure for WPGate.

---

## 🏷️ Versioning Scheme

WPGate strictly adheres to [Semantic Versioning 2.0.0](https://semver.org/):
- **MAJOR (`x.0.0`):** Incompatible architectural changes or removed public APIs.
- **MINOR (`0.x.0`):** New backward-compatible functionality, presets, or major performance optimizations.
- **PATCH (`0.0.x`):** Backward-compatible bug fixes and security hotfixes.

---

## 📋 Pre-Release QA Checklist

Before any release branch is tagged or merged into `main`, the Release Engineer must complete the following checks:

1. **Clean Syntax Linting:**
   ```bash
   find mu-plugin-package theme-package tests -name "*.php" -exec php -l {} \;
   ```
2. **Automated Test Suite Execution:**
   ```bash
   php tests/run-tests.php
   ```
   *Requirement: 100% pass rate.*
3. **Version Consistency:**
   Confirm that the release version string is identical across:
   - `mu-plugin-package/mu-plugins/wpgate-core/bootstrap.php` (`WPGATE_CORE_VERSION`)
   - `mu-plugin-package/mu-plugins/wpgate-loader.php` (Header `Version`)
   - `CHANGELOG.md`
   - `RELEASE-NOTES.md`
   - Package ZIP archive names
4. **Archive Structural Integrity:**
   Validate that `WPGate-MU-Plugin-v*.zip` and `WPGate-Theme-Module-v*.zip` extract to their correct relative paths.

---

## 🚀 Release Execution

1. **Commit & Branch Review:**
   Ensure all changes are committed to the designated feature branch (`feature/*`).
2. **Pull Request Submission:**
   Create a Pull Request targeting `main`.
3. **Project Manager Sign-Off:**
   Obtain Project Manager approval prior to merge.
4. **Tagging:**
   Create an annotated Git tag:
   ```bash
   git tag -a v0.1.1 -m "Release v0.1.1"
   git push origin v0.1.1
   ```
5. **GitHub Actions Automation:**
   The `release.yml` workflow triggers automatically on tag pushes:
   - Runs validation tests.
   - Compiles versioned release ZIP archives.
   - Publishes the GitHub Release with `RELEASE-NOTES.md` attached.
