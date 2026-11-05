<?php
/**
 * WPGate Standalone Core - Admin Settings Page View
 *
 * @package WPGate\Core
 * @version 0.1.0
 */

use WPGate\Core\Settings;

defined( 'ABSPATH' ) || exit;
?>
<div class="wrap wpgate-admin-wrap">
	<style>
		.wpgate-admin-wrap { max-width: 1200px; }
		.wpgate-card {
			background: #ffffff;
			border: 1px solid #d6deeb;
			border-radius: 16px;
			padding: 20px 24px;
			box-shadow: 0 4px 16px rgba(5, 27, 65, 0.05);
			margin-bottom: 20px;
		}
		.wpgate-hero {
			background: linear-gradient(135deg, #051b41 0%, #0d2f67 100%);
			color: #ffffff;
			display: flex;
			align-items: center;
			justify-content: space-between;
			flex-wrap: wrap;
			gap: 16px;
		}
		.wpgate-hero-left {
			display: flex;
			align-items: center;
			gap: 16px;
		}
		.wpgate-hero h1 { color: #ffffff; margin: 0 0 6px; font-size: 24px; line-height: 1.3; }
		.wpgate-hero h2 { color: #60a5fa; margin: 0 0 6px; font-size: 15px; font-weight: 600; }
		.wpgate-hero p { margin: 0; color: rgba(255, 255, 255, 0.88); font-size: 13px; }
		.wpgate-badge {
			display: inline-block;
			padding: 5px 12px;
			border-radius: 20px;
			font-size: 12px;
			font-weight: bold;
			background: rgba(255, 255, 255, 0.15);
			color: #fff;
			border: 1px solid rgba(255, 255, 255, 0.25);
		}
		.wpgate-section-title {
			font-size: 16px;
			padding: 10px 14px;
			margin: 0 0 14px;
			border-radius: 10px;
			background: #051b41;
			color: #ffffff;
		}
		.wpgate-section-title small {
			display: block;
			margin-top: 4px;
			color: #93c5fd;
			font-size: 13px;
			font-weight: 500;
		}
		.wpgate-section-block {
			border: 1px solid #e4eaf4;
			border-radius: 12px;
			padding: 16px;
			margin-bottom: 18px;
			background: #fdfefe;
		}
		.wpgate-quick-actions {
			display: flex;
			flex-wrap: wrap;
			gap: 6px;
			margin-top: 8px;
		}
		.wpgate-quick-whitelist {
			font-size: 11px !important;
			padding: 2px 8px !important;
			border-radius: 6px !important;
		}
		.wpgate-log-table {
			border-radius: 8px;
			overflow: hidden;
			margin-top: 12px;
		}
	</style>

	<div class="wpgate-card wpgate-hero">
		<div class="wpgate-hero-left">
			<div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(59, 130, 246, 0.25); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
				<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#60a5fa" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 18l.01 0" /><path d="M9.172 15.172a4 4 0 0 1 5.656 0" /><path d="M6.343 12.343a7.963 7.963 0 0 1 3.864 -2.14m4.163 .155a7.965 7.965 0 0 1 3.287 2" /><path d="M3.515 9.515a12 12 0 0 1 3.544 -2.455m3.101 -.92a12 12 0 0 1 10.325 3.374" /><path d="M3 3l18 18" />
				</svg>
			</div>
			<div>
				<h1>تنظیمات ماژول WPGate</h1>
				<h2>WordPress HTTP Control & Local Asset Manager</h2>
				<p>نسخه: <?php echo esc_html( WPGATE_CORE_VERSION ); ?> | ماژول مستقل بدون وابستگی به اکتیویشن افزونه</p>
			</div>
		</div>
		<div>
			<span class="wpgate-badge">شیوه بارگذاری: <?php echo esc_html( $loading_method ); ?></span>
		</div>
	</div>

	<?php if ( isset( $_GET['logs_cleared'] ) ) : ?>
		<div class="notice notice-success is-dismissible"><p>لاگ‌های درخواست با موفقیت پاک‌سازی شدند.</p></div>
	<?php endif; ?>

	<?php if ( isset( $_GET['settings_reset'] ) ) : ?>
		<div class="notice notice-info is-dismissible"><p>تمامی تنظیمات با موفقیت به مقادیر پیش‌فرض بازگردانده شدند.</p></div>
	<?php endif; ?>

	<div class="wpgate-card" style="background: #f0f7ff; border-color: #bcd8fb;">
		<p style="margin: 0 0 6px;"><strong>راهنمای سریع:</strong></p>
		<p style="margin: 0 0 4px; font-size: 13px;">۱) در حالت عادی، گزینه <strong>غیرفعال</strong> را انتخاب کنید تا هیچ درخواستی مسدود نشود.</p>
		<p style="margin: 0 0 4px; font-size: 13px;">۲) در زمان اختلال اینترنت بین‌الملل، حالت <strong>لیست سفید</strong> را فعال کرده و دکمه‌های افزودن سریع زیر را برای درگاه‌های پرداخت، پیامک و پست کلیک کنید.</p>
		<p style="margin: 0; font-size: 13px;">۳) برای جلوگیری از توقف و فریز شدن سایت هنگام تلاش برای برقراری تماس با سرورهای خارجی، گزینه <strong>محدودسازی Timeout</strong> (پیشنهاد ۳ ثانیه) را فعال کنید.</p>
	</div>

	<form method="post" action="options.php" class="wpgate-card">
		<?php settings_fields( 'wpgate_group' ); ?>

		<!-- بخش ۱: فایروال و دامنه‌ها -->
		<div class="wpgate-section-block">
			<h3 class="wpgate-section-title">۱) تنظیمات فایروال درخواست‌های خارجی<small>تعیین وضعیت مسدودسازی، لیست سفید و سیاه</small></h3>
			<table class="form-table">
				<tr valign="top">
					<th scope="row" style="width: 240px;">حالت کاری فایروال:</th>
					<td>
						<select name="<?php echo esc_attr( Settings::OPTION_KEY ); ?>[mode]" style="width: 320px;">
							<option value="disabled" <?php selected( $settings['mode'], 'disabled' ); ?>>غیرفعال (بدون محدودیت - عبور تمام درخواست‌ها)</option>
							<option value="whitelist" <?php selected( $settings['mode'], 'whitelist' ); ?>>مسدودسازی همه (فقط دامنه‌های لیست سفید مجازند)</option>
							<option value="blacklist" <?php selected( $settings['mode'], 'blacklist' ); ?>>آزادسازی همه (فقط دامنه‌های لیست سیاه مسدود شوند)</option>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">لیست سفید (Whitelist):<br><small style="color: #646970;">هر دامنه در یک خط</small></th>
					<td>
						<textarea id="wpgate-whitelist" name="<?php echo esc_attr( Settings::OPTION_KEY ); ?>[whitelist]" rows="6" style="width: 100%; max-width: 600px; font-family: monospace; font-size: 13px;"><?php echo esc_textarea( $settings['whitelist'] ); ?></textarea>
						<p class="description" style="margin-top: 6px;"><strong>افزودن سریع دامنه‌های ضروری ایران به لیست سفید:</strong></p>
						<div class="wpgate-quick-actions">
							<?php foreach ( $presets as $preset ) : ?>
								<button type="button" class="button button-secondary wpgate-quick-whitelist" data-domains="<?php echo esc_attr( implode( ',', $preset['domains'] ) ); ?>">
									+ <?php echo esc_html( $preset['label'] ); ?>
								</button>
							<?php endforeach; ?>
						</div>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">لیست سیاه (Blacklist):<br><small style="color: #646970;">هر دامنه در یک خط</small></th>
					<td>
						<textarea name="<?php echo esc_attr( Settings::OPTION_KEY ); ?>[blacklist]" rows="4" style="width: 100%; max-width: 600px; font-family: monospace; font-size: 13px;"><?php echo esc_textarea( $settings['blacklist'] ); ?></textarea>
					</td>
				</tr>
			</table>
		</div>

		<!-- بخش ۲: بهینه‌سازی فایل‌های استاتیک -->
		<div class="wpgate-section-block">
			<h3 class="wpgate-section-title">۲) بهینه‌سازی Assetها و جایگزینی محلی CDN<small>بارگذاری سریع فونت‌ها، FontAwesome، Swiper و مسدودسازی منابع معلق</small></h3>
			<table class="form-table">
				<tr valign="top">
					<th scope="row" style="width: 240px;">جایگزینی CDN با فایل محلی:</th>
					<td>
						<label>
							<input type="checkbox" name="<?php echo esc_attr( Settings::OPTION_KEY ); ?>[local_asset_rewrite]" value="yes" <?php checked( $settings['local_asset_rewrite'], 'yes' ); ?> />
							تلاش برای جایگزینی CDNهای رایج اسکریپت و استایل با نسخه‌های محلی (FontAwesome, Swiper, Google Fonts, WP Core JS)
						</label>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">بلاک سختگیرانه Assetهای خارجی:</th>
					<td>
						<label>
							<input type="checkbox" name="<?php echo esc_attr( Settings::OPTION_KEY ); ?>[strict_asset_block]" value="yes" <?php checked( $settings['strict_asset_block'], 'yes' ); ?> />
							اگر استایل یا اسکریپت خارجی CDN قابل جایگزینی محلی نبود، بلافاصله بلاک شود تا لود صفحه معطل نماند
						</label>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">مسدودسازی Mixpanel:</th>
					<td>
						<label>
							<input type="checkbox" name="<?php echo esc_attr( Settings::OPTION_KEY ); ?>[block_mixpanel]" value="yes" <?php checked( $settings['block_mixpanel'], 'yes' ); ?> />
							مسدودسازی کامل درخواست‌های سرویس رهگیری Mixpanel و جایگزینی با شیء خنثی در فرانت‌اند
						</label>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">لود فونت وزیرمتن در فرانت:</th>
					<td>
						<label>
							<input type="checkbox" name="<?php echo esc_attr( Settings::OPTION_KEY ); ?>[enable_front_vazirmatn]" value="yes" <?php checked( $settings['enable_front_vazirmatn'], 'yes' ); ?> />
							لود خودکار فایل فونت محلی وزیرمتن (Vazirmatn) در بخش کاربری سایت
						</label>
					</td>
				</tr>
			</table>
		</div>

		<!-- بخش ۳: پایداری، آپدیت‌ها و زمان پاسخ -->
		<div class="wpgate-section-block">
			<h3 class="wpgate-section-title">۳) پایداری زمان پاسخ و کنترل آپدیت‌ها<small>سقف Timeout، مسدودسازی تلمتری و گراواتار</small></h3>
			<table class="form-table">
				<tr valign="top">
					<th scope="row" style="width: 240px;">محدودسازی Timeout درخواست‌ها:</th>
					<td>
						<label>
							<input type="checkbox" name="<?php echo esc_attr( Settings::OPTION_KEY ); ?>[enable_timeout_guard]" value="yes" <?php checked( $settings['enable_timeout_guard'], 'yes' ); ?> />
							فعال‌سازی سقف مجاز زمان پاسخگویی برای درخواست‌های HTTP خارجی وردپرس
						</label>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">حداکثر Timeout مجاز (ثانیه):</th>
					<td>
						<input type="number" min="1" max="30" step="1" name="<?php echo esc_attr( Settings::OPTION_KEY ); ?>[max_request_timeout]" value="<?php echo esc_attr( $settings['max_request_timeout'] ); ?>" style="width: 90px;" />
						<p class="description">پیشنهاد: ۳ تا ۵ ثانیه. این کار از هنگ کردن پی‌اچ‌پی در زمان پاسخ ندادن سرورهای خارجی جلوگیری می‌کند.</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">تلمتری و آمارگیر وردپرس:</th>
					<td>
						<label>
							<input type="checkbox" name="<?php echo esc_attr( Settings::OPTION_KEY ); ?>[disable_telemetry]" value="yes" <?php checked( $settings['disable_telemetry'], 'yes' ); ?> />
							مسدودسازی ارسال آمار و اطلاعات به سرورهای وردپرس (wordpress.org)
						</label>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">جلوگیری از بررسی بروزرسانی‌ها:</th>
					<td>
						<label>
							<input type="checkbox" name="<?php echo esc_attr( Settings::OPTION_KEY ); ?>[disable_updates]" value="yes" <?php checked( $settings['disable_updates'], 'yes' ); ?> />
							غیرفعال‌سازی بررسی خودکار آپدیت هسته و مخزن وردپرس (پیش‌فرض: غیرفعال جهت حفظ آپدیت‌های امنیتی)
						</label>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">استثنای آپدیت برای لیست سفید:</th>
					<td>
						<label>
							<input type="checkbox" name="<?php echo esc_attr( Settings::OPTION_KEY ); ?>[allow_whitelisted_updates]" value="yes" <?php checked( $settings['allow_whitelisted_updates'], 'yes' ); ?> />
							اجازه به دریافت آپدیت‌ها از مارکت‌ها و سرورهای داخلی تعریف شده در لیست سفید (مانند ژاکت و راست‌چین)
						</label>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">مسدودسازی گراواتار:</th>
					<td>
						<label>
							<input type="checkbox" name="<?php echo esc_attr( Settings::OPTION_KEY ); ?>[disable_gravatar]" value="yes" <?php checked( $settings['disable_gravatar'], 'yes' ); ?> />
							جایگزینی تصاویر پروفایل کاربر با تصویر محلی ماژول
						</label>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">آدرس تصویر گراواتار سفارشی:</th>
					<td>
						<input type="text" name="<?php echo esc_attr( Settings::OPTION_KEY ); ?>[gravatar_url]" value="<?php echo esc_url( $settings['gravatar_url'] ); ?>" style="width: 100%; max-width: 500px;" />
						<p class="description">در صورت خالی بودن، تصویر پیش‌فرض ماژول قرار می‌گیرد.</p>
					</td>
				</tr>
			</table>
		</div>

		<!-- بخش ۴: لاگ و عیب‌یابی -->
		<div class="wpgate-section-block">
			<h3 class="wpgate-section-title">۴) گزارش‌گیری و تاریخچه درخواست‌ها<small>مدیریت حجم دیتابیس و عیب‌یابی مسدودسازی‌ها</small></h3>
			<table class="form-table">
				<tr valign="top">
					<th scope="row" style="width: 240px;">ثبت لاگ درخواست‌ها:</th>
					<td>
						<label>
							<input type="checkbox" name="<?php echo esc_attr( Settings::OPTION_KEY ); ?>[enable_logging]" value="yes" <?php checked( $settings['enable_logging'], 'yes' ); ?> />
							فعال‌سازی ثبت لاگ درخواست‌های فیلتر شده (جهت بهینگی در حالت عادی خاموش نگه دارید)
						</label>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">مدت زمان نگهداری لاگ‌ها:</th>
					<td>
						<select name="<?php echo esc_attr( Settings::OPTION_KEY ); ?>[log_retention_days]">
							<option value="1" <?php selected( $settings['log_retention_days'], '1' ); ?>>۱ روز</option>
							<option value="3" <?php selected( $settings['log_retention_days'], '3' ); ?>>۳ روز</option>
							<option value="7" <?php selected( $settings['log_retention_days'], '7' ); ?>>۷ روز</option>
							<option value="15" <?php selected( $settings['log_retention_days'], '15' ); ?>>۱۵ روز</option>
							<option value="30" <?php selected( $settings['log_retention_days'], '30' ); ?>>۳۰ روز</option>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">حداکثر تعداد لاگ:</th>
					<td>
						<input type="number" min="50" max="2000" step="10" name="<?php echo esc_attr( Settings::OPTION_KEY ); ?>[log_max_entries]" value="<?php echo esc_attr( $settings['log_max_entries'] ); ?>" style="width: 100px;" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">لاگ رویدادهای بازنویسی Asset:</th>
					<td>
						<label>
							<input type="checkbox" name="<?php echo esc_attr( Settings::OPTION_KEY ); ?>[log_asset_events]" value="yes" <?php checked( $settings['log_asset_events'], 'yes' ); ?> />
							ثبت جزئیات بازنویسی و مسدودسازی فایل‌های استاتیک در گزارش‌ها
						</label>
					</td>
				</tr>
			</table>
		</div>

		<div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-top: 20px;">
			<?php submit_button( 'ذخیره تنظیمات ماژول WPGate', 'primary', 'submit', false ); ?>
			<a href="<?php echo esc_url( $reset_url ); ?>" class="button button-secondary" onclick="return confirm('آیا مطمئن هستید که می‌خواهید تمام تنظیمات به حالت پیش‌فرض بازگردانده شوند؟');">
				بازنشانی به تنظیمات پیش‌فرض
			</a>
		</div>
	</form>

	<!-- جدول لاگ درخواست‌های فیلتر شده -->
	<details class="wpgate-card" <?php echo ! empty( $logs ) ? 'open' : ''; ?>>
		<summary style="font-size: 15px; font-weight: bold; cursor: pointer; color: #051b41;">
			گزارش درخواست‌های مسدود شده و رویدادها (تعداد ثبت شده: <?php echo esc_html( count( $logs ) ); ?>)
		</summary>
		<div style="margin-top: 16px;">
			<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
				<span class="description">برای حفظ سرعت دیتابیس، مقادیر حساس (مانند توکن‌ها و کلیدهای امنیتی) از آدرس‌ها حذف شده‌اند.</span>
				<a href="<?php echo esc_url( $clear_logs_url ); ?>" class="button button-secondary" onclick="return confirm('آیا از پاک کردن تمام لاگ‌ها اطمینان دارید؟');">
					پاک کردن تاریخچه لاگ‌ها
				</a>
			</div>
			<table class="widefat striped wpgate-log-table">
				<thead>
					<tr>
						<th style="width: 15%;">زمان</th>
						<th style="width: 25%;">دلیل / وضعیت</th>
						<th style="width: 20%;">میزبان (Host)</th>
						<th style="width: 40%;">آدرس (URL) کامل</th>
					</tr>
				</thead>
				<tbody>
					<?php if ( empty( $logs ) ) : ?>
						<tr>
							<td colspan="4" style="text-align: center; padding: 20px;">هیچ رویدادی تا این لحظه ثبت نشده است.</td>
						</tr>
					<?php else : ?>
						<?php foreach ( array_reverse( $logs ) as $log ) : ?>
							<tr>
								<td><?php echo esc_html( $log['time'] ); ?></td>
								<td><code><?php echo esc_html( $log['mode'] ); ?></code></td>
								<td><strong><?php echo esc_html( $log['host'] ); ?></strong></td>
								<td style="word-break: break-all;"><code><?php echo esc_url( $log['url'] ); ?></code></td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</details>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
	var textarea = document.getElementById('wpgate-whitelist');
	if (!textarea) return;

	var quickButtons = document.querySelectorAll('.wpgate-quick-whitelist');
	quickButtons.forEach(function (button) {
		button.addEventListener('click', function () {
			var currentLines = textarea.value
				.split(/\r?\n/)
				.map(function (line) { return line.trim().toLowerCase(); })
				.filter(Boolean);

			var map = {};
			currentLines.forEach(function (domain) {
				map[domain] = true;
			});

			var domains = (button.getAttribute('data-domains') || '')
				.split(',')
				.map(function (domain) { return domain.trim().toLowerCase(); })
				.filter(Boolean);

			domains.forEach(function (domain) {
				if (!map[domain]) {
					currentLines.push(domain);
					map[domain] = true;
				}
			});

			textarea.value = currentLines.join('\n');
			textarea.dispatchEvent(new Event('change'));
		});
	});
});
</script>
