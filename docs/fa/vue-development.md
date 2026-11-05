# راهنمای توسعه فرانت‌اند Vue 3 در WPGate

[English](../en/vue-development.md) | [فارسی](vue-development.md)

این سند معماری، گردش کار توسعه و فرآیند کامپایل رابط کاربری مدیریتی WPGate را برای توسعه‌دهندگان فرانت‌اند تشریح می‌کند.

---

## 🏗️ پشته فناوری و معماری

- **فریم‌ورک:** Vue 3 (استفاده از Composition API با سینتکس `<script setup>`)
- **باندلر و ابزار بیلد:** Vite نسخه 6.x
- **مسیر خروجی کامپایل:** ذخیره مستقیم درون پوشه `wpgate-core/admin/dist/`
- **فایل‌های نهایی بیلد:**
  - `wpgate-admin.js` (باندل جاوااسکریپت، حدود ۱۴۴ کیلوبایت خام / ۴۹ کیلوبایت gzipped)
  - `wpgate-admin.css` (استایل‌های فشرده، حدود ۱۴.۵ کیلوبایت خام / ۳.۱ کیلوبایت gzipped)
- **ارتباط با سرور:** استفاده از API استاندارد `fetch` با هدر اعتبارسنجی `X-WP-Nonce` جهت تعامل با مسیرهای REST API وردپرس (`/wp-json/wpgate/v1`).

---

## 📁 ساختار پوشه‌های سورس فرانت‌اند

```text
admin-ui/
├── package.json               # وابستگی‌های Node (Vue 3, Vite, @vitejs/plugin-vue)
├── vite.config.js             # پیکربندی خروجی بیلد به مقصد wpgate-core/admin/dist
└── src/
    ├── main.js                # نقطه ورود اپلیکیشن (اتصال به المان #wpgate-admin-app)
    ├── App.vue                # قالب اصلی (هدر، تب‌ها، سوییچر زبان و تم)
    ├── api.js                 # کلاینت REST جهت فراخوانی اندپوینت‌های /wpgate/v1
    ├── i18n.js                # دیکشنری دوزبانه (فارسی و انگلیسی)
    ├── assets/
    │   └── styles.css         # متغیرهای CSS (تم تاریک/روشن، قواعد راست‌چین و ریسپانسیو)
    └── components/
        ├── TabOverview.vue    # تب نمای کلی، وضعیت و شاخص‌ها
        ├── TabHttpControl.vue # تب کنترل فایروال، لیست دامنه‌ها و تایم‌اوت
        ├── TabLocalAssets.vue # تب بازنویسی دارایی‌ها و CDN
        ├── TabWpControls.vue  # تب مدیریت بروزرسانی‌ها و تله‌متری
        ├── TabLogs.vue        # تب نمایش، جستجو و فیلتر لاگ‌ها
        ├── TabTools.vue       # تب برون‌بری/درون‌ریزی JSON و ریست کارخانه
        ├── TabAbout.vue       # تب درباره توسعه‌دهنده و مجوزها
        ├── ToastNotice.vue    # کامپوننت پیام‌های اعلان شناور (Toast)
        └── ConfirmModal.vue   # کامپوننت مودال تاییدیه‌های حساس
```

---

## 🛠️ روند توسعه و کامپایل

### ۱. پیش‌نیازها
- محیط اجرایی Node.js نسخه 18 یا بالاتر (تست‌شده روی Node 20 و Node 24).
- مدیر بسته npm نسخه 9 یا بالاتر.

### ۲. نصب وابستگی‌ها
در ریشه پروژه دستورات زیر را وارد نمایید:
```bash
cd admin-ui
npm install
```

### ۳. کامپایل نهایی جهت انتشار (Production Build)
جهت ساخت باندل فشرده و بهینه‌سازی‌شده:
```bash
cd admin-ui
npm run build
```

فایل‌های خروجی به‌صورت خودکار در مسیرهای زیر مستقر می‌شوند:
- `mu-plugin-package/wpgate-core/admin/dist/wpgate-admin.js`
- `mu-plugin-package/wpgate-core/admin/dist/wpgate-admin.css`

جهت همگام‌سازی با بسته قالب:
```powershell
Copy-Item -Path "mu-plugin-package/wpgate-core/admin/dist/*" -Destination "theme-package/wpgate-core/admin/dist/" -Force
```

---

## 🌐 بومی‌سازی و تم‌ها

- **سیستم دوزبانه (i18n):** کلیه عبارات متنی در فایل `src/i18n.js` نگهداری می‌شوند. برای افزودن یک عبارت جدید، آن را در هر دو بخش `fa` و `en` تعریف کرده و درون کامپوننت‌ها با تابع `t('section.key')` فراخوانی کنید.
- **راست‌چین و چپ‌چین (RTL / LTR):** جهت صفحه به صورت پویا با اتریبیوت `:dir="isRtl ? 'rtl' : 'ltr'"` روی کانتینر ریشه تغییر می‌کند.
- **تم تاریک و روشن (Theming):** متغیر `:data-theme="theme"` روی المان والد تنظیم شده و مقادیر رنگی تعریف‌شده در متغیرهای CSS فایل `styles.css` سوییچ رنگی روان و چشم‌نوازی فراهم می‌کنند.
