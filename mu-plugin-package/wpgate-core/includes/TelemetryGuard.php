<?php
/**
 * WPGate Standalone Core - Telemetry Guard
 *
 * @package WPGate\Core
 * @version 0.1.0
 */

namespace WPGate\Core;

defined( 'ABSPATH' ) || exit;

class TelemetryGuard {

	/**
	 * Block telemetry requests to wordpress.org if enabled.
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

		$host = strtolower( trim( $host ) );

		if ( 'wordpress.org' === $host || substr( $host, -14 ) === '.wordpress.org' ) {
			Logger::log( $url, $host, 'telemetry_blocked' );
			return new \WP_Error( 'wpgate_telemetry_blocked', 'Telemetry blocked by WPGate.' );
		}

		return $preempt;
	}
}
