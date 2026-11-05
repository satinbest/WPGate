<?php
/**
 * WPGate Standalone Core - Telemetry Guard
 *
 * Selectively blocks non-essential telemetry and tracking requests
 * without interfering with translation updates, language packs, or critical services.
 *
 * @package WPGate\Core
 * @version 0.1.3
 */

namespace WPGate\Core;

defined( 'ABSPATH' ) || exit;

class TelemetryGuard {

	/**
	 * Check if an outbound HTTP request is targeting genuine telemetry or tracking endpoints.
	 * Explicitly protects WordPress translation lookups and legitimate language data.
	 *
	 * @param string $url
	 * @param string $host
	 * @return bool
	 */
	public static function is_telemetry_request( string $url, string $host ): bool {
		$host = strtolower( trim( $host ) );
		if ( '' === $host ) {
			return false;
		}

		// 1. Known dedicated telemetry and tracking domains
		if ( in_array( $host, [ 'stats.wordpress.com', 'telemetry.elementor.com' ], true ) ) {
			return true;
		}

		$path = (string) wp_parse_url( $url, PHP_URL_PATH );
		$path = strtolower( trim( $path ) );

		// 2. Elementor telemetry / usage-tracking paths
		if ( ( 'elementor.com' === $host || substr( $host, -14 ) === '.elementor.com' ) &&
		     ( false !== strpos( $path, '/telemetry' ) || false !== strpos( $path, '/tracking' ) ) ) {
			return true;
		}

		// 3. WordPress.org genuine telemetry endpoints only
		// IMPORTANT: Never block /translations/, language packs, or downloads!
		if ( 'api.wordpress.org' === $host || 'wordpress.org' === $host ) {
			$telemetry_paths = [
				'/core/browse-happy/',
				'/core/serve-happy/',
				'/events/',
				'/core/credits/',
				'/stats/',
			];

			foreach ( $telemetry_paths as $tpath ) {
				if ( 0 === strpos( $path, $tpath ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Block telemetry requests if enabled in settings.
	 *
	 * @param false|array|\WP_Error $preempt
	 * @param array                 $args
	 * @param string                $url
	 * @return false|array|\WP_Error
	 */
	public static function filter( $preempt, array $args, string $url ) {
		if ( false !== $preempt && null !== $preempt ) {
			return $preempt;
		}

		$settings = Settings::get_all();
		if ( ( $settings['disable_telemetry'] ?? 'no' ) !== 'yes' ) {
			return $preempt;
		}

		$host = (string) wp_parse_url( $url, PHP_URL_HOST );
		if ( '' === $host ) {
			return $preempt;
		}

		if ( self::is_telemetry_request( $url, $host ) ) {
			Logger::log( $url, $host, 'telemetry_blocked' );
			return new \WP_Error( 'wpgate_telemetry_blocked', 'Telemetry blocked by WPGate.' );
		}

		return $preempt;
	}
}
