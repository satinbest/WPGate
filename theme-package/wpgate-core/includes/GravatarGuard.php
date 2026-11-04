<?php
/**
 * WPGate Standalone Core - Gravatar Replacement Guard
 *
 * @package WPGate\Core
 * @version 0.1.0
 */

namespace WPGate\Core;

defined( 'ABSPATH' ) || exit;

class GravatarGuard {

	/**
	 * Replace Gravatar HTML output with local placeholder.
	 *
	 * @param string $avatar
	 * @param mixed  $id_or_email
	 * @param int    $size
	 * @param string $default
	 * @param string $alt
	 * @return string
	 */
	public static function filter( $avatar, $id_or_email, $size, $default, $alt ): string {
		$settings = Settings::get_all();

		if ( ( $settings['disable_gravatar'] ?? 'no' ) !== 'yes' ) {
			return $avatar;
		}

		$custom_url = ! empty( $settings['gravatar_url'] ) ? $settings['gravatar_url'] : Context::get_asset_url( 'user.jpg' );

		return sprintf(
			'<img alt="%s" src="%s" class="avatar avatar-%d photo" height="%d" width="%d" loading="lazy" />',
			esc_attr( (string) $alt ),
			esc_url( $custom_url ),
			(int) $size,
			(int) $size,
			(int) $size
		);
	}
}
