# Contributing to WPGate

[English](CONTRIBUTING.md) | [فارسی](CONTRIBUTING-FA.md)

Thank you for your interest in contributing to **WPGate**! As an enterprise-grade standalone WordPress module, we uphold rigorous standards for code quality, architectural consistency, and security.

---

## 🧭 Code of Conduct

All contributors are expected to maintain a respectful, welcoming, and harassment-free environment for everyone. Please be constructive and considerate in issues and pull requests.

---

## 🌿 Branching Strategy

We follow a structured Git branching workflow:

- **`main`**: The production-ready branch. Only reviewed, tested, and approved releases are merged here. Direct pushes to `main` are strictly forbidden.
- **`develop`**: The primary integration branch for ongoing work.
- **`feature/<feature-name>`**: Dedicated branches for new features or enhancements.
- **`fix/<bug-description>`**: Dedicated branches for bug fixes.
- **`docs/<topic>`**: Documentation revisions and translations.

---

## 🛠️ Development Setup & Workflow

1. **Fork & Clone:**
   ```bash
   git clone https://github.com/satinbest/WPGate.git
   cd WPGate
   ```

2. **Create a Topic Branch:**
   ```bash
   git checkout -b feature/my-enhancement
   ```

3. **Coding Standards:**
   - Adhere to **WordPress Coding Standards (WPCS)** and **PSR-12**.
   - Use PHP 7.4+ type declarations where applicable.
   - Maintain namespace integrity under `WPGate\Core`.
   - Never write or copy files to disk during visitor HTTP requests.
   - Sanitize all input and escape all output.

4. **Run Verification & Tests:**
   Before committing any changes, run syntax linting and the automated test suite:
   ```bash
   # Lint PHP syntax
   php -l mu-plugin-package/mu-plugins/wpgate-core/bootstrap.php

   # Run test suite
   php tests/run-tests.php
   ```
   **All tests must pass with zero failures.**

5. **Commit Conventions:**
   Write clear, imperative commit messages:
   - `feat: add REST API status check probe`
   - `fix: prevent duplicate domain addition in quick preset handler`
   - `docs: update theme loader timing instructions in Persian README`

---

## 📬 Submitting Pull Requests

1. Push your branch to GitHub:
   ```bash
   git push origin feature/my-enhancement
   ```
2. Open a Pull Request targeting the `main` or `develop` branch.
3. Provide a detailed PR description explaining:
   - What problem is solved.
   - What changes were made.
   - Which tests were executed.
   - Any backward compatibility implications.
4. Ensure CI checks pass on GitHub Actions across all supported PHP versions (7.4 to 8.4).

Thank you for helping make WPGate better!
