<?php
/**
 * WPGate Standalone Core - HTTP Request Timeout Guard
 *
 * @package WPGate\Core
 * @version 0.1.0
 */

namespace WPGate\Core;

defined( 'ABSPATH' ) || exit;

class TimeoutGuard {

	/**
	 * Enforce maximum request timeout limit.
	 *
	 * @param array  $args
	 * @param string $url
	 * @return array
	 */
	public static function filter( array $args, string $url ): array {
		$settings = Settings::get_all();

		if ( ( $settings['enable_timeout_guard'] ?? 'no' ) !== 'yes' ) {
			return $args;
		}

		$timeout_limit = Settings::normalize_timeout_limit( $settings['max_request_timeout'] ?? 3 );
		if ( $timeout_limit <= 0 ) {
			return $args;
		}

		$host = (string) wp_parse_url( $url, PHP_URL_HOST );
		if ( '' !== $host && HttpGuard::is_local_host( $host ) ) {
			return $args;
		}

		$current_timeout = isset( $args['timeout'] ) ? (float) $args['timeout'] : 0;
		if ( $current_timeout > 0 && $current_timeout <= $timeout_limit ) {
			return $args;
		}

		$args['timeout'] = $timeout_limit;

		if ( '' !== $host ) {
			Logger::log( $url, strtolower( $host ), 'timeout_limited_' . $timeout_limit . 's' );
		}

		return $args;
	}
}
