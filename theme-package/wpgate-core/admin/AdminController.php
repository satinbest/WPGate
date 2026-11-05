<?php
/**
 * WPGate Standalone Core - Admin Controller
 *
 * Manages WordPress admin menu, script enqueueing for the Vue 3 SPA,
 * and dashboard widget integration with reliable asset resolution.
 *
 * @package WPGate\Core
 * @version 0.1.2
 */

namespace WPGate\Core\Admin;

use WPGate\Core\Context;
use WPGate\Core\Settings;
use WPGate\Core\Logger;

defined( 'ABSPATH' ) || exit;

class AdminController {

	/**
	 * Page hook suffix for WPGate settings page.
	 *
	 * @var string
	 */
	private static $page_hook = '';

	/**
	 * Initialize admin hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_menu', [ self::class, 'register_menu' ] );
		add_action( 'admin_enqueue_scripts', [ self::class, 'enqueue_admin_assets' ] );
		add_action( 'admin_init', [ self::class, 'register_settings' ] );
		add_action( 'admin_init', [ self::class, 'handle_clear_logs' ] );
		add_action( 'admin_init', [ self::class, 'handle_reset_defaults' ] );
		add_action( 'wp_dashboard_setup', [ self::class, 'register_dashboard_widget' ] );
	}

	/**
	 * Register settings menu item under "Settings".
	 *
	 * @return void
	 */
	public static function register_menu(): void {
		self::$page_hook = add_options_page(
			'WPGate — WordPress HTTP Control & Local Asset Gateway',
			'WPGate',
			'manage_options',
			'wpgate',
			[ self::class, 'render_settings_page' ]
		);
	}

	/**
	 * Locate compiled admin SPA asset (JS or CSS) deterministically.
	 * Checks both primary location (admin/dist/) and secondary location (assets/admin/dist/).
	 * Returns array with physical filesystem path and exact matching public URL, or null if missing.
	 *
	 * @param string $filename e.g. 'wpgate-admin.js' or 'wpgate-admin.css'
	 * @return array{path: string, url: string}|null
	 */
	public static function locate_admin_dist_asset( string $filename ): ?array {
		$base_dir = Context::get_base_dir();
		$base_url = Context::get_base_url();

		$candidates = [
			// Primary structure: wpgate-core/admin/dist/
			[
				'path' => $base_dir . '/admin/dist/' . $filename,
				'url'  => $base_url . '/admin/dist/' . $filename,
			],
			// Secondary structure: wpgate-core/assets/admin/dist/
			[
				'path' => $base_dir . '/assets/admin/dist/' . $filename,
				'url'  => $base_url . '/assets/admin/dist/' . $filename,
			],
		];

		foreach ( $candidates as $candidate ) {
			if ( file_exists( $candidate['path'] ) && filesize( $candidate['path'] ) > 0 ) {
				return $candidate;
			}
		}

		return null;
	}

	/**
	 * Enqueue Vue 3 SPA assets ONLY on WPGate administration page.
	 *
	 * @param string $hook_suffix
	 * @return void
	 */
	public static function enqueue_admin_assets( string $hook_suffix ): void {
		if ( 'settings_page_wpgate' !== $hook_suffix && self::$page_hook !== $hook_suffix ) {
			return;
		}

		$version = defined( 'WPGATE_CORE_VERSION' ) ? WPGATE_CORE_VERSION : '0.1.1';

		// Locate and enqueue compiled CSS
		$css_asset = self::locate_admin_dist_asset( 'wpgate-admin.css' );
		if ( null !== $css_asset ) {
			$css_ver = file_exists( $css_asset['path'] ) ? (string) filemtime( $css_asset['path'] ) : $version;
			wp_enqueue_style(
				'wpgate-admin-spa',
				$css_asset['url'],
				[],
				$css_ver
			);
		}

		// Locate and enqueue compiled JS
		$js_asset = self::locate_admin_dist_asset( 'wpgate-admin.js' );
		if ( null !== $js_asset ) {
			$js_ver = file_exists( $js_asset['path'] ) ? (string) filemtime( $js_asset['path'] ) : $version;
			wp_enqueue_script(
				'wpgate-admin-spa',
				$js_asset['url'],
				[],
				$js_ver,
				true
			);

			wp_localize_script(
				'wpgate-admin-spa',
				'wpgateApiSettings',
				[
					'root'         => esc_url_raw( rest_url() ),
					'nonce'        => wp_create_nonce( 'wp_rest' ),
					'version'      => $version,
					'isRtl'        => is_rtl(),
					'locale'       => get_user_locale(),
					'author'       => 'Hossein Mohammadpour',
					'authorUri'    => 'https://hosseinmohammadpour.ir',
					'supportEmail' => 'info@hosseinmohammadpour.ir',
				]
			);
		}
	}

	/**
	 * Register settings in WordPress Settings API (fallback support).
	 *
	 * @return void
	 */
	public static function register_settings(): void {
		register_setting(
			'wpgate_group',
			Settings::OPTION_KEY,
			[
				'sanitize_callback' => [ Settings::class, 'sanitize' ],
			]
		);
	}

	/**
	 * Handle legacy/direct HTTP request to clear logs.
	 *
	 * @return void
	 */
	public static function handle_clear_logs(): void {
		if (
			isset( $_GET['page'] ) &&
			'wpgate' === $_GET['page'] &&
			isset( $_GET['clear_logs'] ) &&
			current_user_can( 'manage_options' )
		) {
			check_admin_referer( 'wpgate_clear_logs' );
			Logger::clear_logs();
			wp_safe_redirect( admin_url( 'options-general.php?page=wpgate&logs_cleared=1' ) );
			exit;
		}
	}

	/**
	 * Handle legacy/direct HTTP request to restore default settings.
	 *
	 * @return void
	 */
	public static function handle_reset_defaults(): void {
		if (
			isset( $_GET['page'] ) &&
			'wpgate' === $_GET['page'] &&
			isset( $_GET['reset_defaults'] ) &&
			current_user_can( 'manage_options' )
		) {
			check_admin_referer( 'wpgate_reset_defaults' );
			Settings::reset();
			wp_safe_redirect( admin_url( 'options-general.php?page=wpgate&settings_reset=1' ) );
			exit;
		}
	}

	/**
	 * Register dashboard widget.
	 *
	 * @return void
	 */
	public static function register_dashboard_widget(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		add_meta_box(
			'wpgate_dashboard_widget',
			'WPGate — Outbound HTTP & Asset Gateway',
			[ self::class, 'render_dashboard_widget' ],
			'dashboard',
			'normal',
			'high'
		);
	}

	/**
	 * Render settings page view. Mounts Vue 3 SPA or provides an informative diagnostic
	 * message with graceful fallback to server-rendered view if assets are missing.
	 *
	 * @return void
	 */
	public static function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$js_asset = self::locate_admin_dist_asset( 'wpgate-admin.js' );

		// If Vue SPA bundle is present on disk, mount the SPA container
		if ( null !== $js_asset ) {
			echo '<div class="wrap wpgate-admin-wrap">';
			echo '<div id="wpgate-admin-app"></div>';
			echo '<noscript><p class="notice notice-error" style="padding:12px; margin:20px 0;">' . esc_html__( 'WPGate Administration Dashboard requires JavaScript to be enabled in your browser.', 'wpgate' ) . '</p></noscript>';
			echo '</div>';
			return;
		}

		// Diagnostic Notice: Assets not found on disk
		$version = defined( 'WPGATE_CORE_VERSION' ) ? WPGATE_CORE_VERSION : '0.1.1';
		echo '<div class="wrap">';
		echo '<div class="notice notice-warning is-dismissible" style="padding:16px; margin:20px 0; border-inline-start-width:4px;">';
		echo '<h3 style="margin-top:0; color:#b45309;">⚠️ WPGate: SPA Administration Dashboard Assets Not Found</h3>';
		echo '<p>The compiled Vue 3 Single Page Application assets (<code>wpgate-admin.js</code> / <code>wpgate-admin.css</code>) could not be located in:</p>';
		echo '<ul style="list-style:disc; margin-left:24px; font-family:monospace; font-size:0.85em;">';
		echo '<li>' . esc_html( Context::get_base_dir() . '/admin/dist/wpgate-admin.js' ) . '</li>';
		echo '<li>' . esc_html( Context::get_base_dir() . '/assets/admin/dist/wpgate-admin.js' ) . '</li>';
		echo '</ul>';
		echo '<p><strong>Safe Fallback Mode Active:</strong> Displaying the standard server-rendered configuration interface below. All settings remain 100% accessible and editable.</p>';
		echo '<p><em>To restore the modern Vue 3 interface, please ensure you downloaded and extracted the complete official release package (<code>WPGate-MU-Plugin-v' . esc_html( $version ) . '.zip</code>).</em></p>';
		echo '</div>';
		echo '</div>';

		// Fallback server template so settings remain 100% accessible!
		$settings        = Settings::get_all();
		$logs            = Logger::get_logs();
		$presets         = Settings::get_presets();
		$clear_logs_url  = wp_nonce_url( admin_url( 'options-general.php?page=wpgate&clear_logs=1' ), 'wpgate_clear_logs' );
		$reset_url       = wp_nonce_url( admin_url( 'options-general.php?page=wpgate&reset_defaults=1' ), 'wpgate_reset_defaults' );
		$loading_method  = Context::get_loading_method();

		if ( file_exists( Context::get_base_dir() . '/admin/views/settings-page.php' ) ) {
			require Context::get_base_dir() . '/admin/views/settings-page.php';
		}
	}

	/**
	 * Render dashboard widget view.
	 *
	 * @return void
	 */
	public static function render_dashboard_widget(): void {
		$settings_url   = admin_url( 'options-general.php?page=wpgate' );
		$settings       = Settings::get_all();
		$loading_method = Context::get_loading_method();

		if ( file_exists( Context::get_base_dir() . '/admin/views/dashboard-widget.php' ) ) {
			require Context::get_base_dir() . '/admin/views/dashboard-widget.php';
		}
	}
}
