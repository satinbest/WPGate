# Troubleshooting & Diagnostics

[English](troubleshooting.md) | [فارسی](../fa/troubleshooting.md)


This guide provides troubleshooting steps for common issues encountered when running WPGate.

---

## 1. Outbound Payment Gateway Connection Failing

### Symptom
When customers attempt online checkout via WooCommerce or Easy Digital Downloads, the payment gateway connection fails with a timeout or block error.

### Cause
In **Whitelist Mode**, the bank's PSP or PaymentYar hostname has not been added to your Whitelist.

### Solution
1. Go to **WordPress Admin > Settings > WPGate**.
2. Under Section 1 (Firewall), click the **+ شرکت‌های PSP شاپرک** and **+ پرداخت‌یارها** preset buttons.
3. If your specific payment provider is not in the preset list, add its API hostname manually (e.g. `api.yourgateway.com`).
4. Click **Save Settings**.
5. Test a checkout transaction.

---

## 2. Emergency Site Access Lockout / Fatal Error

### Symptom
A server misconfiguration or PHP conflict caused an unexpected error, preventing access to the WordPress admin panel.

### Solution (Emergency Kill-Switch)
1. Connect to your web server via SFTP, SSH, or cPanel File Manager.
2. Open your WordPress root `wp-config.php` file in a text editor.
3. Add the following line near the top, right after `<?php`:
   ```php
   define( 'WPGATE_CORE_DISABLED', true );
   ```
4. Save the file.
5. All WPGate hooks and filters are instantly deactivated. You can now access your site normally to diagnose the issue.

---

## 3. WordPress Plugin/Theme Updates Failing

### Symptom
WordPress reports that it cannot check for plugin or theme updates.

### Cause
The **Disable Automatic Updates** (`disable_updates`) option is toggled on (`yes`).

### Solution
1. Go to **Settings > WPGate**.
2. Scroll to Section 3 (Stability & Privacy).
3. Ensure **جلوگیری از بررسی بروزرسانی‌ها** (Disable Updates) is unchecked.
4. If you want domestic updates enabled while blocking international updates, check **استثنای آپدیت برای لیست سفید** (Allow Whitelisted Updates) and add your marketplace hostnames (e.g. `api.zhaket.com`, `api.rtl-theme.com`) to the Whitelist.
5. Save settings.

---

## 4. Icons or Sliders Missing After Enabling Strict Asset Block

### Symptom
A custom slider or icon font fails to render on the front end.

### Cause
The plugin enqueued its assets from an external third-party CDN whose hostname is not recognized by WPGate's local asset mapper.

### Solution
1. Temporarily enable **Event Logging** and **Log Asset Events** in Settings Section 4.
2. Reload the affected page.
3. Inspect the WPGate log table to identify which external CDN URL was blocked.
4. Add the CDN hostname to your Whitelist, or download the asset locally into your child theme and enqueue it natively.
