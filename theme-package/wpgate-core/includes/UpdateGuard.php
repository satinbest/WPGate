<?php
/**
 * WPGate Standalone Core - Update Guard
 *
 * @package WPGate\Core
 * @version 0.1.0
 */

namespace WPGate\Core;

defined( 'ABSPATH' ) || exit;

class UpdateGuard {

	/**
	 * Register update blocking hooks if enabled in settings.
	 *
	 * @return void
	 */
	public static function init(): void {
		$settings = Settings::get_all();

		if ( ( $settings['disable_updates'] ?? 'no' ) !== 'yes' ) {
			return; // Preserves core and security updates by default!
		}

		$allow_whitelisted = ( $settings['allow_whitelisted_updates'] ?? 'no' ) === 'yes';

		// Block core update checks
		add_filter( 'pre_site_transient_update_core', '__return_null' );
		remove_action( 'admin_init', 'wp_version_check' );

		// If whitelisted updates are not allowed, block all theme and plugin checks too
		if ( ! $allow_whitelisted ) {
			add_filter( 'pre_site_transient_update_plugins', '__return_null' );
			add_filter( 'pre_site_transient_update_themes', '__return_null' );
			remove_action( 'admin_init', 'wp_update_plugins' );
			remove_action( 'admin_init', 'wp_update_themes' );
		}
	}
}
