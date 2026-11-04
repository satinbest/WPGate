<?php
/**
 * WPGate Standalone Core - Admin Controller
 *
 * Manages WordPress admin menu, script enqueueing for the Vue 3 SPA,
 * and dashboard widget integration.
 *
 * @package WPGate\Core
 * @version 0.1.0
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
	 * Enqueue Vue 3 SPA assets ONLY on WPGate administration page.
	 *
	 * @param string $hook_suffix
	 * @return void
	 */
	public static function enqueue_admin_assets( string $hook_suffix ): void {
		if ( 'settings_page_wpgate' !== $hook_suffix && self::$page_hook !== $hook_suffix ) {
			return;
		}

		$dist_dir = Context::get_base_dir() . '/admin/dist';
		$js_file  = $dist_dir . '/wpgate-admin.js';
		$css_file = $dist_dir . '/wpgate-admin.css';

		if ( file_exists( $css_file ) ) {
			wp_enqueue_style(
				'wpgate-admin-spa',
				Context::get_asset_url( 'admin/dist/wpgate-admin.css' ),
				[],
				defined( 'WPGATE_CORE_VERSION' ) ? WPGATE_CORE_VERSION : '0.1.0'
			);
		}

		if ( file_exists( $js_file ) ) {
			wp_enqueue_script(
				'wpgate-admin-spa',
				Context::get_asset_url( 'admin/dist/wpgate-admin.js' ),
				[],
				defined( 'WPGATE_CORE_VERSION' ) ? WPGATE_CORE_VERSION : '0.1.0',
				true
			);

			wp_localize_script(
				'wpgate-admin-spa',
				'wpgateApiSettings',
				[
					'root'         => esc_url_raw( rest_url() ),
					'nonce'        => wp_create_nonce( 'wp_rest' ),
					'version'      => defined( 'WPGATE_CORE_VERSION' ) ? WPGATE_CORE_VERSION : '0.1.0',
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
	 * Render settings page view. Mounts Vue 3 SPA or falls back to server-rendered template.
	 *
	 * @return void
	 */
	public static function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$dist_js = Context::get_base_dir() . '/admin/dist/wpgate-admin.js';

		if ( file_exists( $dist_js ) ) {
			echo '<div class="wrap wpgate-admin-wrap">';
			echo '<div id="wpgate-admin-app"></div>';
			echo '<noscript><p class="notice notice-error">' . esc_html__( 'WPGate Administration Dashboard requires JavaScript to be enabled in your browser.', 'wpgate' ) . '</p></noscript>';
			echo '</div>';
			return;
		}

		// Fallback server template if compiled SPA assets are not found
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
