<?php
/**
 * WPGate Standalone Core - Secure Logger
 *
 * @package WPGate\Core
 * @version 0.1.0
 */

namespace WPGate\Core;

defined( 'ABSPATH' ) || exit;

class Logger {

	public const LOG_KEY = 'wpgate_http_logs';

	/**
	 * Log an event if logging is enabled.
	 *
	 * @param string $url
	 * @param string $host
	 * @param string $mode
	 * @return void
	 */
	public static function log( string $url, string $host, string $mode ): void {
		$settings = Settings::get_all();

		if ( ( $settings['enable_logging'] ?? 'no' ) !== 'yes' ) {
			return;
		}

		$mode = sanitize_key( $mode );
		if ( '' === $mode ) {
			return;
		}

		// Check if asset rewrite event and asset logging is disabled
		if ( self::is_asset_rewrite_mode( $mode ) && ( $settings['log_asset_events'] ?? 'no' ) !== 'yes' ) {
			return;
		}

		$host = sanitize_text_field( strtolower( trim( $host ) ) );
		$url  = self::sanitize_url( $url );

		$logs = self::get_logs();

		$current_time      = (int) ( function_exists( 'current_time' ) ? current_time( 'timestamp' ) : time() );
		$retention_days    = (int) ( $settings['log_retention_days'] ?? 7 );
		$retention_seconds = $retention_days * DAY_IN_SECONDS;

		// Deduplicate: avoid logging the same event within 60 seconds
		$last_log = end( $logs );
		if ( is_array( $last_log ) && isset( $last_log['time'], $last_log['host'], $last_log['url'], $last_log['mode'] ) ) {
			$last_time = strtotime( (string) $last_log['time'] );
			if ( false !== $last_time && ( $current_time - $last_time ) <= MINUTE_IN_SECONDS ) {
				if ( $last_log['host'] === $host && $last_log['url'] === $url && $last_log['mode'] === $mode ) {
					return;
				}
			}
		}

		// Prune expired entries
		if ( ! empty( $logs ) ) {
			$logs = array_filter(
				$logs,
				function( $log ) use ( $current_time, $retention_seconds ) {
					if ( ! is_array( $log ) || empty( $log['time'] ) ) {
						return false;
					}
					$log_time = strtotime( (string) $log['time'] );
					if ( false === $log_time ) {
						return false;
					}
					return ( $current_time - $log_time ) <= $retention_seconds;
				}
			);
		}

		$time_str = function_exists( 'current_time' ) ? current_time( 'mysql' ) : gmdate( 'Y-m-d H:i:s' );

		$logs[] = [
			'time' => $time_str,
			'host' => $host,
			'url'  => $url,
			'mode' => $mode,
		];

		$max_entries = (int) ( $settings['log_max_entries'] ?? 300 );
		if ( count( $logs ) > $max_entries ) {
			$logs = array_slice( $logs, -$max_entries );
		}

		$logs = array_values( $logs );

		update_option( self::LOG_KEY, $logs, false );
	}

	/**
	 * Sanitize URL and redact sensitive query parameters.
	 *
	 * @param string $url
	 * @return string
	 */
	public static function sanitize_url( string $url ): string {
		$url = trim( $url );
		if ( '' === $url ) {
			return '';
		}

		$parts = wp_parse_url( $url );
		if ( ! is_array( $parts ) ) {
			return substr( sanitize_text_field( $url ), 0, 512 );
		}

		$scheme = isset( $parts['scheme'] ) ? strtolower( (string) $parts['scheme'] ) . '://' : '';
		$host   = isset( $parts['host'] ) ? strtolower( (string) $parts['host'] ) : '';
		$port   = isset( $parts['port'] ) ? ':' . (int) $parts['port'] : '';
		$path   = isset( $parts['path'] ) ? (string) $parts['path'] : '';

		$clean_query = '';
		if ( isset( $parts['query'] ) && '' !== $parts['query'] ) {
			$clean_query = '?' . self::redact_query_string( (string) $parts['query'] );
		}

		$result = $scheme . $host . $port . $path . $clean_query;

		return substr( sanitize_text_field( $result ), 0, 512 );
	}

	/**
	 * Redact sensitive query parameter values.
	 *
	 * @param string $query_str
	 * @return string
	 */
	public static function redact_query_string( string $query_str ): string {
		$sensitive_keys = [
			'token', 'key', 'api_key', 'apikey', 'secret', 'password',
			'passwd', 'pass', 'auth', 'hash', 'signature', 'sig',
			'nonce', 'card', 'cvv', 'pin', 'code', 'session',
		];

		parse_str( $query_str, $params );
		if ( ! is_array( $params ) || empty( $params ) ) {
			return '';
		}

		foreach ( $params as $key => $value ) {
			$low_key = strtolower( (string) $key );
			foreach ( $sensitive_keys as $pattern ) {
				if ( false !== strpos( $low_key, $pattern ) ) {
					$params[ $key ] = '[REDACTED]';
					break;
				}
			}
		}

		$query = http_build_query( $params );
		return str_replace( [ '%5BREDACTED%5D', '%5Bredacted%5D' ], '[REDACTED]', $query );
	}

	/**
	 * Check whether a log mode represents an asset rewrite/stubbing event.
	 *
	 * @param string $mode
	 * @return bool
	 */
	public static function is_asset_rewrite_mode( string $mode ): bool {
		return false !== strpos( $mode, 'rewritten_to_local' ) || false !== strpos( $mode, 'local_asset_bypassed' );
	}

	/**
	 * Retrieve all current logs.
	 *
	 * @return array
	 */
	public static function get_logs(): array {
		$logs = get_option( self::LOG_KEY, [] );
		return is_array( $logs ) ? $logs : [];
	}

	/**
	 * Clear all logs.
	 *
	 * @return bool
	 */
	public static function clear_logs(): bool {
		return delete_option( self::LOG_KEY );
	}
}
