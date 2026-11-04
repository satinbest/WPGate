# Frequently Asked Questions (FAQ)

---

### Q1: Is WPGate a standard WordPress plugin?
**A:** No. WPGate is a standalone PHP module. It operates without standard plugin activation and does not depend on plugin database entries. It is deployed as a Must-Use plugin (`mu-plugins`) or directly inside a child theme.

---

### Q2: Why doesn't WPGate appear under the regular "Plugins" menu?
**A:** Because it is designed as a Must-Use plugin or theme library. When deployed in `wp-content/mu-plugins/`, it appears under **Plugins > Must-Use**, which prevents accidental deactivation by other administrators.

---

### Q3: Will WPGate slow down my website?
**A:** No. WPGate actually **speeds up** your site. By limiting slow outbound HTTP request timeouts and serving CDN assets locally from your own server or local CDN, it prevents browser delays and PHP worker pool exhaustion. Furthermore, admin code is only loaded in the dashboard (`is_admin() === true`), leaving front-end execution completely lean.

---

### Q4: Does WPGate block WordPress Cron or loopback requests?
**A:** No. WPGate explicitly detects and exempts `localhost`, `127.0.0.1`, `::1`, and your site's own home/site domain name. Scheduled WordPress cron tasks (`wp-cron.php`) and internal REST API calls are never blocked.

---

### Q5: Can I run WPGate on Docker / Kubernetes / Read-Only filesystems?
**A:** Yes. Unlike legacy plugins that attempt to write or copy files to disk at runtime, WPGate performs **zero runtime disk modifications**. All bundled assets are pre-packaged and addressed statically.

---

### Q6: How do I completely uninstall WPGate?
**A:** 
- In **MU-Plugin mode**, delete `wp-content/mu-plugins/wpgate-loader.php` and `wp-content/mu-plugins/wpgate-core/`.
- In **Theme mode**, remove the `require_once` call from `functions.php` and delete `wpgate-core/`.
- (Optional) Clear options `wpgate_http_control_settings` and `wpgate_http_logs` from `wp_options`.
