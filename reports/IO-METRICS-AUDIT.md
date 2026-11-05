# WPGate — Storage I/O Metrics & Disk-Writing Technical Audit Report

[English](IO-METRICS-AUDIT.md) | [فارسی](IO-METRICS-AUDIT-FA.md)

**Project:** WPGate  
**Version:** `v0.1.5`  
**Date:** November 6, 2026  
**Lead Engineer:** Hossein Mohammadpour (<info@hosseinmohammadpour.ir>)  
**Repository:** [https://github.com/satinbest/WPGate](https://github.com/satinbest/WPGate)  
**Status:** COMPLETED (Fabricated Metric Removed, Technical Precision Enforced)  

---

## 1. Executive Summary

This investigation evaluates the origin, implementation, and technical validity of the disk write metric previously displayed in the WPGate administration interface:
```text
Runtime Disk Write IOPS: 0 Bytes (Pure RAM Execution)
نرخ نوشتن روی دیسک (IOPS): ۰ بایت (اجرای کاملاً درون‌حافظه‌ای)
```

The audit reveals that:
1. **IOPS Terminology Mismatch:** IOPS (Input/Output Operations Per Second) measures the rate of discrete storage read/write transaction cycles per second. Expressing IOPS in "Bytes" (or "0 Bytes") is conceptually invalid.
2. **Lack of Kernel-Level Profiling:** Standard PHP-FPM web runtime environments cannot profile host storage controller IOPS without privileged OS kernel instrumentation (`/proc/diskstats`, `iostat`).
3. **Database vs. Disk Distinction:** While WPGate creates zero filesystem log files, logging data into WordPress `wp_options` ultimately involves database transactions (MySQL InnoDB redo logs and tablespaces). Claiming "Pure RAM Execution" for persistent WordPress options is an overstatement.
4. **Corrective Implementation:** The metric has been refactored to accurately reflect the actual software behavior: **Disk File Logging: Disabled (0 Files on Disk)** with full contextual tooltips.

---

## 2. Source Code & Data Flow Investigation

### 2.1 Frontend Component Analysis
- **Components Rendering the Value:**
  - `admin-ui/src/components/TabOverview.vue` (Quick Info Section)
  - `admin-ui/src/components/TabTools.vue` (System Environment Section)
- **State Origin:**
  - The string was statically pulled from the localization dictionary:
    - `t('tools.diskWrites')`
    - `t('tools.diskWritesVal')`
  - In `TabTools.vue`, the string `'0 Writes (Pure Memory)'` was additionally hardcoded in the template.

### 2.2 Backend & REST API Analysis
- **Inspection of `RestController::get_settings()` and `RestController::get_stats()`:**
  - The REST API endpoint `/wpgate/v1/stats` returns: `total_logs`, `blocked_count`, `allowed_count`, `rewritten_count`, `whitelist_count`, and `blacklist_count`.
  - The endpoint `/wpgate/v1/settings` returns: PHP version, WordPress version, multisite boolean, and active mode.
  - **Result:** No REST endpoint in WPGate measures or transmits an IOPS metric. The value was not computed or returned by the backend.

### 2.3 Storage Architecture Analysis (`Logger.php`)
- **Inspection of `WPGate\Core\Logger::log()`:**
  - Requests are written via: `update_option( self::LOG_KEY, $logs, false );`
  - WPGate explicitly configures `autoload = false` to prevent overhead during standard page requests.
  - **Result:** WPGate writes **zero files** to the server's local filesystem (`wp-content/uploads/` or custom log directories). All records reside inside the MySQL database table `wp_options`.

---

## 3. Technical Accuracy Assessment

| Claimed Metric | Actual System Reality | Technical Verdict |
| :--- | :--- | :--- |
| **"IOPS: 0 Bytes"** | IOPS is measured in operations/sec, not bytes. | **INCORRECT UNIT** |
| **"Pure RAM Execution"** | WordPress Options API interacts with MySQL/MariaDB database, which utilizes disk persistence. | **MISLEADING SCOPE** |
| **"Zero File Logging"** | WPGate does not write standalone log files to the disk filesystem. | **ACCURATE FACT** |
| **"Real-Time Disk Sensor"** | PHP cannot monitor physical drive IOPS in shared hosting environments. | **UNSUPPORTED CLAIM** |

---

## 4. Corrective Action & Implementation

To ensure complete technical precision and adhere to project integrity guidelines:

1. **Term Refactoring:**
   - **Old Label:** `Runtime Disk Write IOPS` / `نرخ نوشتن روی دیسک (IOPS)`
   - **New Corrected Label:** `Disk File Logging` / `فایل‌نویسی روی دیسک`
2. **Value Refactoring:**
   - **Old Value:** `0 Bytes (Pure RAM Execution)` / `۰ بایت (اجرای کاملاً درون‌حافظه‌ای)`
   - **New Corrected Value:** `Disabled (0 Files on Disk)` / `غیرفعال (بدون فایل روی دیسک)`
3. **Exploratory Tooltip (`tools.diskWritesHint`):**
   - **English:** `"Logs reside in database options; disk file logging is disabled and hardware IOPS is unmeasured."`
   - **Persian:** `"وقایع در دیتابیس وردپرس ذخیره می‌شوند؛ فایل‌نویسی روی دیسک غیرفعال است و IOPS سخت‌افزاری توسط PHP اندازه‌گیری نمی‌شود."`
4. **Clean Hardcoded Removal:**
   - Removed the static `'0 Writes (Pure Memory)'` string in `TabTools.vue` and bound it to reactive translated properties.

---

## 5. Architectural Limitations & Conclusion

- **Hosting Environment Boundary:** Because WPGate is deployed across diverse shared, VPS, and cloud WordPress environments, attempting to query operating system `/proc` or `sysfs` counters would introduce security risks, permission warnings, and portability failures.
- **Precision Guarantee:** By clearly identifying that WPGate **does not write log files to disk**, while acknowledging that database records are maintained, WPGate provides 100% truthful, verifiable performance documentation.
