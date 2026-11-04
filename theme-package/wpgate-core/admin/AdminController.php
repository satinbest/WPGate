<?php
/**
 * WPGate Standalone Core - Admin Controller
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
	 * Initialize admin hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		if ( ! is_admin() ) {
			return;
		}

		add_action( 'admin_menu', [ self::class, 'register_menu' ] );
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
		add_options_page(
			'WPGate — WordPress HTTP Control & Local Asset Manager',
			'WPGate',
			'manage_options',
			'wpgate',
			[ self::class, 'render_settings_page' ]
		);
	}

	/**
	 * Register settings in WordPress Settings API.
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
	 * Handle request to clear logs.
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
	 * Handle request to restore default settings.
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
			delete_option( Settings::OPTION_KEY );
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
			'WPGate — HTTP & Asset Gateway',
			[ self::class, 'render_dashboard_widget' ],
			'dashboard',
			'normal',
			'high'
		);
	}

	/**
	 * Render settings page view.
	 *
	 * @return void
	 */
	public static function render_settings_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings        = Settings::get_all();
		$logs            = Logger::get_logs();
		$presets         = Settings::get_presets();
		$clear_logs_url  = wp_nonce_url( admin_url( 'options-general.php?page=wpgate&clear_logs=1' ), 'wpgate_clear_logs' );
		$reset_url       = wp_nonce_url( admin_url( 'options-general.php?page=wpgate&reset_defaults=1' ), 'wpgate_reset_defaults' );
		$loading_method  = Context::get_loading_method();

		require Context::get_base_dir() . '/admin/views/settings-page.php';
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

		require Context::get_base_dir() . '/admin/views/dashboard-widget.php';
	}
}
