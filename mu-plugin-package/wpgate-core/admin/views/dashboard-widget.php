<?php
/**
 * WPGate Standalone Core - Dashboard Widget View
 *
 * @package WPGate\Core
 * @version 0.1.0
 */

defined( 'ABSPATH' ) || exit;

$mode = $settings['mode'] ?? 'disabled';
$mode_labels = [
	'disabled'  => 'غیرفعال (بدون محدودیت)',
	'whitelist' => 'حالت لیست سفید (فقط دامنه‌های مجاز)',
	'blacklist' => 'حالت لیست سیاه (مسدودسازی دامنه‌های خاص)',
];
$mode_colors = [
	'disabled'  => '#646970',
	'whitelist' => '#008a20',
	'blacklist' => '#d63638',
];
?>
<div class="wpgate-widget-content" style="border: 1px solid #d6deeb; border-radius: 12px; padding: 14px; background: #fafcff;">
	<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; background: #051b41; border-radius: 10px; padding: 10px 14px; color: #fff;">
		<div>
			<strong style="font-size: 15px; color: #ffffff; display: block;">WPGate (ماژول مستقل)</strong>
			<small style="color: #60a5fa;">نسخه <?php echo esc_html( WPGATE_CORE_VERSION ); ?> | شیوه لود: <?php echo esc_html( $loading_method ); ?></small>
		</div>
		<span style="display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; background: <?php echo esc_attr( $mode_colors[ $mode ] ?? '#646970' ); ?>; color: #fff;">
			<?php echo esc_html( $mode_labels[ $mode ] ?? $mode ); ?>
		</span>
	</div>

	<p style="margin: 0 0 10px; font-size: 13px; line-height: 1.6;">
		این ماژول درخواست‌های HTTP خارجی وردپرس را مدیریت می‌کند تا سایت در شرایط کندی یا اختلال اینترنت بین‌الملل بدون توقف و افت سرعت پاسخ دهد.
	</p>

	<div style="display: flex; gap: 8px; margin-bottom: 12px; flex-wrap: wrap;">
		<span style="font-size: 12px; background: #eef2f7; padding: 4px 8px; border-radius: 6px;">
			تلمتری: <strong><?php echo ( $settings['disable_telemetry'] ?? 'no' ) === 'yes' ? 'مسدود' : 'فعال'; ?></strong>
		</span>
		<span style="font-size: 12px; background: #eef2f7; padding: 4px 8px; border-radius: 6px;">
			تایم‌اوت هوشمند: <strong><?php echo ( $settings['enable_timeout_guard'] ?? 'no' ) === 'yes' ? esc_html( $settings['max_request_timeout'] ?? 3 ) . ' ثانیه' : 'غیرفعال'; ?></strong>
		</span>
		<span style="font-size: 12px; background: #eef2f7; padding: 4px 8px; border-radius: 6px;">
			بازنویسی لوکال: <strong><?php echo ( $settings['local_asset_rewrite'] ?? 'no' ) === 'yes' ? 'فعال' : 'غیرفعال'; ?></strong>
		</span>
	</div>

	<p style="margin: 0;">
		<a class="button button-primary" href="<?php echo esc_url( $settings_url ); ?>">تنظیمات پیشرفته WPGate</a>
	</p>
</div>
